<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Asset;
use App\Models\AssetStatus;
use App\Models\AssetsHistory;
use App\Models\Assignment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AffectationService
{
    /**
     * Crée un bon d'affectation et remet les matières à un agent et/ou un service.
     *
     * @param  array{agent_id?:int|null, service_id?:int|null, location_id?:int|null, assigned_at?:string|null,
     *               expected_return_at?:string|null, notes?:string|null, type?:string|null}  $data
     * @param  array<int>  $assetIds
     */
    public function assign(array $data, array $assetIds, string $action = 'affectation'): Assignment
    {
        return DB::transaction(function () use ($data, $assetIds, $action) {
            $assets = $this->lockAssets($assetIds);
            $this->ensureAssignable($assets);

            $agent = ! empty($data['agent_id']) ? Agent::findOrFail($data['agent_id']) : null;
            $serviceId = ($data['service_id'] ?? null) ?: $agent?->service_id;

            if (! $agent && ! $serviceId) {
                throw ValidationException::withMessages([
                    'agent_id' => 'Choisissez un agent ou un service bénéficiaire.',
                ]);
            }

            $assignment = Assignment::create([
                'reference'          => Assignment::nextReference(),
                'type'               => ($data['type'] ?? null) ?: Assignment::TYPE_DEFAULT,
                'agent_id'           => $agent?->id,
                'service_id'         => $serviceId,
                'location_id'        => $data['location_id'] ?? null,
                'assigned_at'        => $data['assigned_at'] ?? now()->toDateString(),
                'expected_return_at' => $data['expected_return_at'] ?? null,
                'status'             => Assignment::STATUS_OPEN,
                'notes'              => $data['notes'] ?? null,
                'created_by_id'      => auth()->id(),
                'quantity'           => (string) $assets->count(),
            ]);

            $assignment->matieres()->attach($assets->pluck('id'));

            $assignedStatus = AssetStatus::idFor(AssetStatus::ASSIGNED);

            foreach ($assets as $asset) {
                $asset->agent_id = $agent?->id;
                $asset->service_id = $serviceId;
                $asset->status_id = $assignedStatus ?? $asset->status_id;
                if (! empty($data['location_id'])) {
                    $asset->location_id = $data['location_id'];
                }
                $asset->saveQuietly();

                $this->log($asset, $action, $assignment, $data['notes'] ?? null);
            }

            return $assignment;
        });
    }

    /**
     * Enregistre le retour de tout ou partie des matières d'un bon d'affectation.
     *
     * @param  array<int>|null  $assetIds  null = toutes les matières encore détenues
     */
    public function returnAssets(
        Assignment $assignment,
        ?array $assetIds,
        string $condition = 'bon',
        ?string $notes = null,
        ?string $returnedAt = null,
        ?int $locationId = null,
        string $action = 'restitution',
    ): Assignment {
        return DB::transaction(function () use ($assignment, $assetIds, $condition, $notes, $returnedAt, $locationId, $action) {
            $outstanding = $assignment->outstandingAssets()->lockForUpdate()->get();

            $toReturn = $assetIds === null
                ? $outstanding
                : $outstanding->whereIn('id', array_map('intval', $assetIds));

            if ($toReturn->isEmpty()) {
                throw ValidationException::withMessages([
                    'assets' => 'Aucune matière à restituer sur ce bon.',
                ]);
            }

            $date = $returnedAt ? Carbon::parse($returnedAt) : now();
            $newStatus = in_array($condition, ['endommage', 'hors_service'], true)
                ? AssetStatus::idFor(AssetStatus::BROKEN)
                : AssetStatus::idFor(AssetStatus::AVAILABLE);

            foreach ($toReturn as $asset) {
                $assignment->matieres()->updateExistingPivot($asset->id, [
                    'returned_at'      => $date,
                    'return_condition' => $condition,
                    'return_notes'     => $notes,
                    'returned_by_id'   => auth()->id(),
                ]);

                if ($action !== 'transfert') {
                    $asset->agent_id = null;
                    $asset->service_id = null;
                    $asset->status_id = $newStatus ?? $asset->status_id;
                    if ($locationId) {
                        $asset->location_id = $locationId;
                    }
                    $asset->saveQuietly();

                    $this->log($asset, 'restitution', $assignment, $notes, withHolder: false);
                }
            }

            $this->refreshStatus($assignment);

            return $assignment->fresh();
        });
    }

    /**
     * Transfère une matière de son détenteur actuel vers un autre agent ou service.
     */
    public function transfer(Asset $asset, array $data): Assignment
    {
        return DB::transaction(function () use ($asset, $data) {
            $current = $asset->currentAssignment();

            if ($current) {
                $this->returnAssets($current, [$asset->id], 'transfert', $data['notes'] ?? null, action: 'transfert');
            }

            $asset->agent_id = null;
            $asset->service_id = null;
            $asset->saveQuietly();

            $note = trim(($current ? 'Transfert depuis '.$current->reference.'. ' : '').($data['notes'] ?? ''));

            return $this->assign(array_merge($data, ['notes' => $note ?: null]), [$asset->id], 'transfert');
        });
    }

    public function refreshStatus(Assignment $assignment): void
    {
        $remaining = $assignment->outstandingAssets()->count();
        $total = $assignment->matieres()->count();

        if ($remaining === 0) {
            $assignment->status = Assignment::STATUS_CLOSED;
            $assignment->closed_at = $assignment->closed_at ?? now();
        } elseif ($remaining < $total) {
            $assignment->status = Assignment::STATUS_PARTIAL;
            $assignment->closed_at = null;
        } else {
            $assignment->status = Assignment::STATUS_OPEN;
            $assignment->closed_at = null;
        }

        $assignment->save();
    }

    /**
     * Libère les matières encore détenues avant la suppression d'un bon.
     */
    public function release(Assignment $assignment): void
    {
        DB::transaction(function () use ($assignment) {
            if ($assignment->outstandingAssets()->exists()) {
                $this->returnAssets($assignment, null, 'bon', 'Bon d\'affectation annulé');
            }
        });
    }

    private function lockAssets(array $assetIds): Collection
    {
        $ids = array_values(array_unique(array_map('intval', $assetIds)));

        if (! $ids) {
            throw ValidationException::withMessages([
                'assets' => 'Sélectionnez au moins une matière.',
            ]);
        }

        $assets = Asset::whereIn('id', $ids)->lockForUpdate()->get();

        if ($assets->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'assets' => 'Une ou plusieurs matières sélectionnées n\'existent plus.',
            ]);
        }

        return $assets;
    }

    private function ensureAssignable(Collection $assets): void
    {
        $blocked = [AssetStatus::idFor(AssetStatus::BROKEN), AssetStatus::idFor(AssetStatus::REPAIR)];
        $errors = [];

        foreach ($assets as $asset) {
            $label = $asset->name.($asset->serial_number ? ' ('.$asset->serial_number.')' : '');

            if ($asset->isAssigned()) {
                $errors[] = $label.' est déjà affectée.';
            } elseif ($asset->status_id && in_array($asset->status_id, array_filter($blocked), true)) {
                $errors[] = $label.' est en panne ou en réparation.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages(['assets' => $errors]);
        }
    }

    private function log(Asset $asset, string $action, ?Assignment $assignment, ?string $notes, bool $withHolder = true): void
    {
        AssetsHistory::create([
            'asset_id'      => $asset->id,
            'action'        => $action,
            'status_id'     => $asset->status_id,
            'location_id'   => $asset->location_id,
            'agent_id'      => $withHolder ? $asset->agent_id : $assignment?->agent_id,
            'service_id'    => $withHolder ? $asset->service_id : $assignment?->service_id,
            'assignment_id' => $assignment?->id,
            'user_id'       => auth()->id(),
            'notes'         => $notes,
        ]);
    }
}
