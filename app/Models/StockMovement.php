<?php

namespace App\Models;

use App\Models\Concerns\ScopedByService;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use ScopedByService;

    public const TYPES = [
        'entree'     => 'Entrée',
        'sortie'     => 'Sortie',
        'ajustement' => 'Ajustement',
    ];

    public $table = 'stock_movements';

    protected $fillable = [
        'reference',
        'stock_item_id',
        'voucher_id',
        'type',
        'quantity',
        'balance_after',
        'moved_at',
        'supplier_id',
        'service_id',
        'agent_id',
        'document',
        'unit_price',
        'expires_at',
        'user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity'      => 'decimal:2',
            'balance_after' => 'decimal:2',
            'unit_price'    => 'decimal:2',
            'moved_at'      => 'date',
            'expires_at'    => 'date',
        ];
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public static function nextReference(): string
    {
        $prefix = 'MVT-'.now()->year.'-';
        $last = static::withoutGlobalScope('perimetre')->where('reference', 'like', $prefix.'%')->orderByDesc('id')->value('reference');
        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    public function voucher()
    {
        return $this->belongsTo(StockVoucher::class, 'voucher_id');
    }

    public function item()
    {
        return $this->belongsTo(StockItem::class, 'stock_item_id')->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /** Variation signée de la quantité. */
    public function getDeltaAttribute(): float
    {
        return $this->type === 'sortie' ? -(float) $this->quantity : (float) $this->quantity;
    }

    public function getPartyAttribute(): string
    {
        return match ($this->type) {
            'entree' => $this->supplier->name ?? '—',
            'sortie' => $this->agent ? $this->agent->full_name.($this->service ? ' — '.$this->service->name : '') : ($this->service->name ?? '—'),
            default  => 'Comptage physique',
        };
    }
}
