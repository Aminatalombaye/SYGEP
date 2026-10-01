<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use SoftDeletes, HasFactory;
    use \App\Models\Concerns\ScopedByService;


    public const STATUS_OPEN = 'en_cours';
    public const STATUS_PARTIAL = 'partiel';
    public const STATUS_CLOSED = 'restitue';

    public const STATUSES = [
        self::STATUS_OPEN    => 'En cours',
        self::STATUS_PARTIAL => 'Restitution partielle',
        self::STATUS_CLOSED  => 'Restituée',
    ];

    public const CONDITIONS = [
        'bon'          => 'Bon état',
        'use'          => 'Usé',
        'endommage'    => 'Endommagé',
        'hors_service' => 'Hors service',
        'transfert'    => 'Transféré',
    ];

    public const TYPE_DEFAULT = 'dotation';

    public const TYPES = [
        'dotation'    => 'Dotation (affectation durable)',
        'temporaire'  => 'Mise à disposition temporaire',
        'reservation' => 'Réservation',
        'formation'   => 'Formation',
        'programme'   => 'Programme / projet',
        'maintenance' => 'Envoi en maintenance',
    ];

    public $table = 'assignments';

    protected $fillable = [
        'reference',
        'type',
        'agent_id',
        'service_id',
        'location_id',
        'assigned_at',
        'expected_return_at',
        'closed_at',
        'status',
        'notes',
        'created_by_id',
        'quantity',
        'utilisateur',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at'        => 'date',
            'expected_return_at' => 'date',
            'closed_at'          => 'datetime',
        ];
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class)->withTrashed();
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'location_id')->withTrashed();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function matieres()
    {
        return $this->belongsToMany(Asset::class)
            ->withPivot(['returned_at', 'return_condition', 'return_notes', 'returned_by_id']);
    }

    public function outstandingAssets()
    {
        return $this->matieres()->wherePivotNull('returned_at');
    }

    public function atributions()
    {
        return $this->belongsToMany(Attribution::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    public function histories()
    {
        return $this->hasMany(AssetsHistory::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_OPEN, self::STATUS_PARTIAL]);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_PARTIAL], true);
    }

    public function isOverdue(): bool
    {
        return $this->isOpen() && $this->expected_return_at && $this->expected_return_at->isPast();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getBeneficiaryAttribute(): string
    {
        if ($this->agent) {
            return $this->agent->full_name;
        }

        return $this->service->name ?? ($this->utilisateur ?: '—');
    }

    public static function nextReference(): string
    {
        $prefix = 'AFF-'.now()->format('Y').'-';
        $last = static::withTrashed()->withoutGlobalScope('perimetre')
            ->where('reference', 'like', $prefix.'%')
            ->orderByDesc('reference')
            ->value('reference');

        $number = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? self::TYPES[self::TYPE_DEFAULT];
    }
}
