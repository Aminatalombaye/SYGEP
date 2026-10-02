<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\StockItem;
use App\Models\StockVoucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Création des bons d'entrée / de sortie : un bon, plusieurs lignes, un mouvement de stock par ligne.
 * Tout est enregistré ensemble : si une ligne est refusée (stock insuffisant…), rien n'est créé.
 */
class StockVoucherService
{
    public function __construct(private readonly StockService $stock)
    {
    }

    /**
     * @param  array{type:string, moved_at?:string|null, supplier_id?:int|null, service_id?:int|null, agent_id?:int|null, document?:string|null, notes?:string|null}  $header
     * @param  array<int, array{stock_item_id:int, quantity:float|int|string, unit_price?:mixed, expires_at?:string|null}>  $lines
     */
    public function create(array $header, array $lines): StockVoucher
    {
        $type = $header['type'];

        if ($type === 'sortie' && empty($header['service_id']) && ! empty($header['agent_id'])) {
            $header['service_id'] = Agent::find($header['agent_id'])?->service_id;
        }

        return DB::transaction(function () use ($type, $header, $lines) {
            $voucher = StockVoucher::create([
                'reference'     => StockVoucher::nextReference($type),
                'type'          => $type,
                'moved_at'      => $header['moved_at'] ?? today()->toDateString(),
                'supplier_id'   => $type === 'entree' ? ($header['supplier_id'] ?? null) : null,
                'service_id'    => $type === 'sortie' ? ($header['service_id'] ?? null) : null,
                'agent_id'      => $type === 'sortie' ? ($header['agent_id'] ?? null) : null,
                'document'      => $header['document'] ?? null,
                'notes'         => $header['notes'] ?? null,
                'created_by_id' => auth()->id(),
            ]);

            foreach (array_values($lines) as $i => $line) {
                $item = StockItem::findOrFail($line['stock_item_id']);

                if ($type === 'entree' && $item->perishable && empty($line['expires_at'])) {
                    throw ValidationException::withMessages([
                        "lines.$i.expires_at" => 'Article périssable : indiquez la date de péremption du lot.',
                    ]);
                }

                try {
                    $this->stock->record($item, $type, (float) $line['quantity'], [
                        'voucher_id'  => $voucher->id,
                        'moved_at'    => $voucher->moved_at->toDateString(),
                        'supplier_id' => $voucher->supplier_id,
                        'service_id'  => $voucher->service_id,
                        'agent_id'    => $voucher->agent_id,
                        'document'    => $voucher->document ?: $voucher->reference,
                        'unit_price'  => $type === 'entree' ? ($line['unit_price'] ?? null) : null,
                        'expires_at'  => $type === 'entree' ? ($line['expires_at'] ?? null) : null,
                        'notes'       => $voucher->notes,
                    ]);
                } catch (ValidationException $e) {
                    // Rattache le message à la ligne concernée du formulaire.
                    throw ValidationException::withMessages(["lines.$i.quantity" => collect($e->errors())->flatten()->first()]);
                }
            }

            return $voucher->load(['movements.item', 'supplier', 'service', 'agent', 'createdBy']);
        });
    }
}
