<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockItem extends Model
{
    use SoftDeletes;

    public const CATEGORIES = [
        'bureau'       => 'Fournitures de bureau',
        'informatique' => 'Consommables informatiques',
        'pedagogique'  => 'Matières d\'œuvre / pédagogiques',
        'entretien'    => 'Produits d\'entretien',
        'pieces'       => 'Pièces détachées',
        'carburant'    => 'Carburant et lubrifiants',
        'alimentaire'  => 'Denrées périssables',
        'autre'        => 'Autre',
    ];

    public const UNITS = ['unité', 'ramette', 'boîte', 'carton', 'paquet', 'rouleau', 'litre', 'kg', 'mètre', 'lot'];

    public $table = 'stock_items';

    protected $fillable = [
        'reference',
        'name',
        'category',
        'unit',
        'quantity',
        'min_quantity',
        'unit_price',
        'perishable',
        'location_id',
        'supplier_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity'     => 'decimal:2',
            'min_quantity' => 'decimal:2',
            'unit_price'   => 'decimal:2',
            'perishable'   => 'boolean',
        ];
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public static function nextReference(): string
    {
        $last = static::withTrashed()->where('reference', 'like', 'ART-%')->orderByDesc('id')->value('reference');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;

        do {
            $reference = 'ART-'.str_pad((string) $next++, 4, '0', STR_PAD_LEFT);
        } while (static::withTrashed()->where('reference', $reference)->exists());

        return $reference;
    }

    public function scopeLow(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '<=', 'min_quantity');
    }

    public function scopeOut(Builder $query): Builder
    {
        return $query->where('quantity', '<=', 0);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class)->latest('moved_at')->latest('id');
    }

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'location_id')->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function getLevelAttribute(): string
    {
        return match (true) {
            $this->quantity <= 0                  => 'rupture',
            $this->quantity <= $this->min_quantity => 'bas',
            default                               => 'ok',
        };
    }

    public function getLevelLabelAttribute(): string
    {
        return ['rupture' => 'Rupture', 'bas' => 'Sous le seuil', 'ok' => 'Disponible'][$this->level];
    }

    public function getLevelToneAttribute(): string
    {
        return ['rupture' => 'critical', 'bas' => 'warning', 'ok' => 'good'][$this->level];
    }

    /** Remplissage indicatif : le seuil d'alerte vaut 25 % de la jauge. */
    public function getGaugeAttribute(): int
    {
        $max = max((float) $this->min_quantity * 4, 1);

        return (int) max(0, min(100, round((float) $this->quantity * 100 / $max)));
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ($this->category ?: '—');
    }

    public function getStockValueAttribute(): ?float
    {
        return $this->unit_price !== null ? round((float) $this->quantity * (float) $this->unit_price, 2) : null;
    }
}
