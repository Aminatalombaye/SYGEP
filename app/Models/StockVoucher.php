<?php

namespace App\Models;

use App\Models\Concerns\ScopedByService;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Bon d'entrée (réception en magasin) ou bon de sortie (dotation d'un service).
 * Chaque ligne du bon est un mouvement de stock.
 */
class StockVoucher extends Model
{
    use ScopedByService;

    public const TYPES = [
        'entree' => 'Bon d\'entrée',
        'sortie' => 'Bon de sortie',
    ];

    private const PREFIXES = ['entree' => 'BE', 'sortie' => 'BS'];

    public $table = 'stock_vouchers';

    protected $fillable = [
        'reference',
        'type',
        'moved_at',
        'supplier_id',
        'service_id',
        'agent_id',
        'document',
        'notes',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return ['moved_at' => 'date'];
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public static function nextReference(string $type): string
    {
        $prefix = (self::PREFIXES[$type] ?? 'BS').'-'.now()->year.'-';
        $last = static::withoutGlobalScope('perimetre')->where('reference', 'like', $prefix.'%')->orderByDesc('reference')->value('reference');
        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? (string) $this->type;
    }

    public function getPartyAttribute(): string
    {
        if ($this->type === 'entree') {
            return $this->supplier?->name ?? '—';
        }

        $parts = array_filter([$this->service?->name, $this->agent?->full_name]);

        return $parts ? implode(' · ', $parts) : '—';
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class, 'voucher_id')->orderBy('id');
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
