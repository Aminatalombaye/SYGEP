<?php

namespace App\Services;

use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Tenue du stock des matières consommables.
 * Toute variation de quantité passe par un mouvement : le solde reste ainsi justifié.
 */
class StockService
{
    /**
     * @param  string  $type  entree | sortie | ajustement
     * @param  float  $quantity  quantité entrée/sortie, ou quantité comptée pour un ajustement
     */
    public function record(StockItem $item, string $type, float $quantity, array $data = []): StockMovement
    {
        [$movement, $crossed] = DB::transaction(function () use ($item, $type, $quantity, $data) {
            $item = StockItem::whereKey($item->id)->lockForUpdate()->firstOrFail();
            $before = (float) $item->quantity;

            [$moved, $after] = match ($type) {
                'entree'     => [$quantity, $before + $quantity],
                'sortie'     => [$quantity, $before - $quantity],
                'ajustement' => [$quantity - $before, $quantity],
            };

            if ($type !== 'ajustement' && $quantity <= 0) {
                throw ValidationException::withMessages(['quantity' => 'La quantité doit être supérieure à zéro.']);
            }

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stock insuffisant : il reste '.$this->fmt($before).' '.$item->unit.' de « '.$item->name.' ».',
                ]);
            }

            if ($type === 'ajustement' && abs($moved) < 0.005) {
                throw ValidationException::withMessages(['quantity' => 'La quantité comptée est identique au stock théorique : aucun ajustement nécessaire.']);
            }

            $movement = StockMovement::create([
                'reference'     => StockMovement::nextReference(),
                'stock_item_id' => $item->id,
                'type'          => $type,
                'quantity'      => $moved,
                'balance_after' => $after,
                'moved_at'      => $data['moved_at'] ?? today(),
                'supplier_id'   => $type === 'entree' ? ($data['supplier_id'] ?? null) : null,
                'service_id'    => $type === 'sortie' ? ($data['service_id'] ?? null) : null,
                'agent_id'      => $type === 'sortie' ? ($data['agent_id'] ?? null) : null,
                'document'      => $data['document'] ?? null,
                'unit_price'    => $type === 'entree' ? ($data['unit_price'] ?? null) : null,
                'expires_at'    => $type === 'entree' ? ($data['expires_at'] ?? null) : null,
                'user_id'       => auth()->id(),
                'notes'         => $data['notes'] ?? null,
            ]);

            $updates = ['quantity' => $after];
            if ($type === 'entree' && ! empty($data['unit_price'])) {
                $updates['unit_price'] = $data['unit_price'];
            }
            if ($type === 'entree' && ! empty($data['supplier_id']) && ! $item->supplier_id) {
                $updates['supplier_id'] = $data['supplier_id'];
            }
            $item->update($updates);

            $movement->setRelation('item', $item);

            return [$movement, $before > (float) $item->min_quantity && $after <= (float) $item->min_quantity];
        });

        if ($crossed) {
            $item = $movement->item;
            Notifier::permission(
                'stock_item_edit',
                ($item->quantity <= 0 ? 'Rupture de stock' : 'Stock bas').' : '.$item->name.' ('.$this->fmt($item->quantity).' '.$item->unit.' restant(s), seuil '.$this->fmt($item->min_quantity).')',
                route('admin.stock-items.show', $item)
            );
        }

        return $movement;
    }

    private function fmt($value): string
    {
        return \App\Support\Fmt::qty($value);
    }
}
