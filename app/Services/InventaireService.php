<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetStatus;
use App\Models\AssetsHistory;
use App\Models\Inventaire;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventaireService
{
    /**
     * Ouvre la campagne : la liste des matières attendues est figée selon le périmètre.
     */
    public function start(Inventaire $inventaire): int
    {
        if (! $inventaire->isDraft()) {
            throw ValidationException::withMessages(['status' => 'Cette campagne a déjà été démarrée.']);
        }

        return DB::transaction(function () use ($inventaire) {
            $assets = Asset::query()
                ->when($inventaire->location_id, fn ($q) => $q->where('location_id', $inventaire->location_id))
                ->when($inventaire->service_id, fn ($q) => $q->where('service_id', $inventaire->service_id))
                ->when($inventaire->category_id, fn ($q) => $q->where('category_id', $inventaire->category_id))
                ->get(['id', 'location_id']);

            $rows = $assets->mapWithKeys(fn ($a) => [$a->id => [
                'status'               => 'attendu',
                'expected_location_id' => $a->location_id,
            ]])->all();

            $inventaire->assets()->syncWithoutDetaching($rows);

            $inventaire->update([
                'status'     => Inventaire::RUNNING,
                'started_at' => now(),
                'starts_at'  => $inventaire->starts_at ?? today(),
            ]);

            return count($rows);
        });
    }

    /**
     * Enregistre le contrôle d'une matière (scan ou pointage manuel).
     *
     * @return array{result:string, message:string, asset:?array}
     */
    public function record(Inventaire $inventaire, string $code, string $condition = 'bon', ?int $foundLocationId = null, ?string $notes = null): array
    {
        if (! $inventaire->isRunning()) {
            return ['result' => 'error', 'message' => 'La campagne n\'est pas en cours.', 'asset' => null];
        }

        $code = $this->normalize($code);

        $asset = Asset::with('category')
            ->where('qr_code', $code)
            ->orWhere('serial_number', $code)
            ->orWhere('id', ctype_digit($code) ? (int) $code : 0)
            ->first();

        if (! $asset) {
            return ['result' => 'unknown', 'message' => 'Code inconnu : '.$code, 'asset' => null];
        }

        $summary = [
            'id'       => $asset->id,
            'name'     => $asset->name,
            'code'     => $asset->qr_code,
            'category' => $asset->category->name ?? null,
        ];

        $existing = $inventaire->assets()->where('assets.id', $asset->id)->first();

        if ($existing && $existing->pivot->status === 'vu') {
            return ['result' => 'duplicate', 'message' => 'Déjà contrôlée : '.$asset->name, 'asset' => $summary];
        }

        $data = [
            'status'            => $existing ? 'vu' : 'hors_perimetre',
            'condition'         => array_key_exists($condition, Inventaire::CONDITIONS) ? $condition : 'bon',
            'found_location_id' => $foundLocationId ?: ($inventaire->location_id ?: $asset->location_id),
            'checked_at'        => now(),
            'checked_by_id'     => auth()->id(),
            'notes'             => $notes,
        ];

        if ($existing) {
            $inventaire->assets()->updateExistingPivot($asset->id, $data);
        } else {
            $inventaire->assets()->attach($asset->id, $data + ['expected_location_id' => $asset->location_id]);
        }

        return $existing
            ? ['result' => 'ok', 'message' => 'Contrôlée : '.$asset->name, 'asset' => $summary]
            : ['result' => 'outside', 'message' => 'Hors périmètre, ajoutée à la campagne : '.$asset->name, 'asset' => $summary];
    }

    public function undo(Inventaire $inventaire, int $assetId): void
    {
        $row = $inventaire->assets()->where('assets.id', $assetId)->first();

        if (! $row || ! $inventaire->isRunning()) {
            return;
        }

        if ($row->pivot->status === 'hors_perimetre') {
            $inventaire->assets()->detach($assetId);
        } else {
            $inventaire->assets()->updateExistingPivot($assetId, [
                'status' => 'attendu', 'condition' => null, 'found_location_id' => null,
                'checked_at' => null, 'checked_by_id' => null, 'notes' => null,
            ]);
        }
    }

    /**
     * Clôture : les matières non contrôlées deviennent manquantes ; l'emplacement et
     * l'état constatés sont reportés sur les fiches si demandé.
     */
    public function close(Inventaire $inventaire, bool $applyFindings = true): void
    {
        if (! $inventaire->isRunning()) {
            throw ValidationException::withMessages(['status' => 'Seule une campagne en cours peut être clôturée.']);
        }

        DB::transaction(function () use ($inventaire, $applyFindings) {
            $inventaire->assets()->newPivotQuery()->where('status', 'attendu')->update(['status' => 'manquant']);

            if ($applyFindings) {
                $broken = AssetStatus::idFor(AssetStatus::BROKEN);

                $inventaire->assets()->wherePivotIn('status', ['vu', 'hors_perimetre'])->get()->each(function (Asset $asset) use ($inventaire, $broken) {
                    $changed = false;

                    if ($asset->pivot->found_location_id && (int) $asset->pivot->found_location_id !== (int) $asset->location_id) {
                        $asset->location_id = $asset->pivot->found_location_id;
                        $changed = true;
                    }

                    if ($broken && $asset->pivot->condition === 'hors_service' && (int) $asset->status_id !== $broken) {
                        $asset->status_id = $broken;
                        $changed = true;
                    }

                    if ($changed) {
                        $asset->saveQuietly();
                        AssetsHistory::create([
                            'asset_id'    => $asset->id,
                            'action'      => 'inventaire',
                            'status_id'   => $asset->status_id,
                            'location_id' => $asset->location_id,
                            'agent_id'    => $asset->agent_id,
                            'service_id'  => $asset->service_id,
                            'user_id'     => auth()->id(),
                            'notes'       => 'Mise à jour suite à l\'inventaire '.$inventaire->reference,
                        ]);
                    }
                });
            }

            $inventaire->update([
                'status'       => Inventaire::CLOSED,
                'closed_at'    => now(),
                'closed_by_id' => auth()->id(),
                'ends_at'      => $inventaire->ends_at ?? today(),
            ]);
        });
    }

    private function normalize(string $code): string
    {
        $code = trim($code);

        if (preg_match('#/q/([^/?\s]+)#', $code, $m)) {
            return urldecode($m[1]);
        }

        return $code;
    }
}
