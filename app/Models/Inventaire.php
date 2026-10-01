<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventaire extends Model
{
    use HasFactory;
    use \App\Models\Concerns\ScopedByService;


    public const DRAFT = 'brouillon';
    public const RUNNING = 'en_cours';
    public const CLOSED = 'cloture';

    public const STATUSES = [
        self::DRAFT   => 'En préparation',
        self::RUNNING => 'En cours',
        self::CLOSED  => 'Clôturé',
    ];

    public const RESULTS = [
        'attendu'        => 'À contrôler',
        'vu'             => 'Contrôlée',
        'hors_perimetre' => 'Trouvée hors périmètre',
        'manquant'       => 'Manquante',
    ];

    public const CONDITIONS = [
        'bon'          => 'Bon état',
        'use'          => 'Usé',
        'abime'        => 'Abîmé',
        'hors_service' => 'Hors service',
    ];

    public $table = 'inventaires';

    public static $searchable = [
        'nom',
        'reference',
    ];

    protected $fillable = [
        'nom',
        'reference',
        'status',
        'starts_at',
        'ends_at',
        'started_at',
        'closed_at',
        'location_id',
        'service_id',
        'category_id',
        'notes',
        'created_by_id',
        'closed_by_id',
        'in',
        'out',
        'balance',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'  => 'date',
            'ends_at'    => 'date',
            'started_at' => 'datetime',
            'closed_at'  => 'datetime',
        ];
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function assets()
    {
        return $this->belongsToMany(Asset::class)
            ->withPivot(['status', 'condition', 'expected_location_id', 'found_location_id', 'checked_at', 'checked_by_id', 'notes']);
    }

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'location_id')->withTrashed();
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id')->withTrashed();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by_id');
    }

    public function isDraft(): bool
    {
        return $this->status === self::DRAFT;
    }

    public function isRunning(): bool
    {
        return $this->status === self::RUNNING;
    }

    public function isClosed(): bool
    {
        return $this->status === self::CLOSED;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getScopeLabelAttribute(): string
    {
        $parts = array_filter([
            $this->location?->name ? 'Emplacement : '.$this->location->name : null,
            $this->service?->name ? 'Service : '.$this->service->name : null,
            $this->category?->name ? 'Catégorie : '.$this->category->name : null,
        ]);

        return $parts ? implode(' · ', $parts) : 'Tout le parc';
    }

    /**
     * Compteurs de la campagne : attendues, contrôlées, restantes, écarts…
     */
    public function stats(): array
    {
        $rows = $this->assets()->newPivotQuery()
            ->selectRaw('status, `condition`, expected_location_id, found_location_id')
            ->get();

        $expected = $rows->whereIn('status', ['attendu', 'vu', 'manquant'])->count();
        $checked = $rows->where('status', 'vu')->count();
        $outside = $rows->where('status', 'hors_perimetre')->count();
        $missing = $rows->where('status', 'manquant')->count();
        $damaged = $rows->whereIn('condition', ['abime', 'hors_service'])->count();
        $moved = $rows->filter(fn ($r) => $r->found_location_id && $r->expected_location_id && (int) $r->found_location_id !== (int) $r->expected_location_id)->count();

        return [
            'expected' => $expected,
            'checked'  => $checked,
            'pending'  => $rows->where('status', 'attendu')->count(),
            'missing'  => $missing,
            'outside'  => $outside,
            'damaged'  => $damaged,
            'moved'    => $moved,
            'progress' => $expected ? (int) round($checked * 100 / $expected) : 0,
        ];
    }

    public static function nextReference(): string
    {
        $prefix = 'INV-'.now()->format('Y').'-';
        $last = static::withoutGlobalScope('perimetre')->where('reference', 'like', $prefix.'%')->orderByDesc('reference')->value('reference');
        $number = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $number, 2, '0', STR_PAD_LEFT);
    }
}
