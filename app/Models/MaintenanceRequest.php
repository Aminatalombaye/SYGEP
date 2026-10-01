<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use SoftDeletes, HasFactory;

    public const STATUSES = [
        'soumise'              => 'Soumise',
        'en_attente_direction' => 'Attente du Directeur',
        'validee'   => 'Approuvée',
        'rejetee'   => 'Rejetée',
        'planifiee' => 'Planifiée',
        'en_cours'  => 'En cours',
        'terminee'  => 'Terminée',
    ];

    public const STATUS_TONES = [
        'soumise'              => 'warning',
        'en_attente_direction' => 'warning',
        'validee'   => 'info',
        'rejetee'   => 'critical',
        'planifiee' => 'info',
        'en_cours'  => 'info',
        'terminee'  => 'good',
    ];

    public const OPEN_STATUSES = ['soumise', 'en_attente_direction', 'validee', 'planifiee', 'en_cours'];

    /** Statuts en attente d'une décision (responsable maintenance puis Directeur). */
    public const PENDING_STATUSES = ['soumise', 'en_attente_direction'];

    public const PRIORITIES = [
        'basse'   => 'Basse',
        'normale' => 'Normale',
        'haute'   => 'Haute',
        'urgente' => 'Urgente',
    ];

    public const PRIORITY_TONES = [
        'basse'   => 'neutral',
        'normale' => 'info',
        'haute'   => 'warning',
        'urgente' => 'critical',
    ];

    public const KINDS = [
        'corrective' => 'Corrective (panne, dégradation)',
        'preventive' => 'Préventive (entretien planifié)',
    ];

    public const TARGETS = [
        'infrastructure' => 'Bâtiment / infrastructure',
        'asset'          => 'Matière / équipement',
        'autre'          => 'Autre',
    ];

    public $table = 'maintenance_requests';

    protected $fillable = [
        'reference',
        'title',
        'kind',
        'target_type',
        'infrastructure_id',
        'asset_id',
        'establishment',
        'priority',
        'description',
        'status',
        'created_by',
        'requested_by_id',
        'validated_by_id',
        'validated_at',
        'decision_notes',
        'approved_by_id',
        'approved_at',
        'approval_notes',
        'planned_for',
        'assigned_to_id',
        'started_at',
        'completed_at',
        'resolution',
        'cost',
        'maintenance_plan_id',
    ];

    protected function casts(): array
    {
        return [
            'validated_at' => 'datetime',
            'approved_at'  => 'datetime',
            'planned_for'  => 'date',
            'started_at'   => 'datetime',
            'completed_at' => 'datetime',
            'cost'         => 'decimal:2',
        ];
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected static function booted(): void
    {
        // Un agent ou un comptable secondaire ne voit que ses demandes et celles du matériel de son service.
        static::addGlobalScope('perimetre', function (Builder $query) {
            $serviceId = \App\Support\Perimetre::serviceId();

            if ($serviceId !== null) {
                $query->where(function ($q) use ($serviceId) {
                    $q->where('maintenance_requests.requested_by_id', auth()->id())
                        ->orWhereIn('maintenance_requests.asset_id', \Illuminate\Support\Facades\DB::table('assets')->where('service_id', $serviceId)->select('id'));
                });
            }
        });
    }

    public static function nextReference(): string
    {
        $prefix = 'DMT-'.now()->year.'-';
        $last = static::withTrashed()->withoutGlobalScope('perimetre')->where('reference', 'like', $prefix.'%')->orderByDesc('reference')->value('reference');
        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', self::OPEN_STATUSES);
    }

    /* Relations */

    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }

    /** @deprecated nom historique de la relation vers les tâches */
    public function requests()
    {
        return $this->tasks();
    }

    public function infrastructure()
    {
        return $this->belongsTo(Infrastructure::class)->withTrashed();
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class)->withTrashed();
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function plan()
    {
        return $this->belongsTo(MaintenancePlan::class, 'maintenance_plan_id')->withTrashed();
    }

    /* États */

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    /** Avis technique du responsable maintenance. */
    public function canBeValidated(): bool
    {
        return $this->status === 'soumise';
    }

    /** Approbation du Directeur, après l'avis technique. */
    public function canBeApproved(): bool
    {
        return $this->status === 'en_attente_direction';
    }

    public function isPending(): bool
    {
        return in_array($this->status, self::PENDING_STATUSES, true);
    }

    public function canBePlanned(): bool
    {
        return in_array($this->status, ['validee', 'planifiee'], true);
    }

    public function canBeStarted(): bool
    {
        return in_array($this->status, ['validee', 'planifiee'], true);
    }

    public function canBeCompleted(): bool
    {
        return in_array($this->status, ['validee', 'planifiee', 'en_cours'], true);
    }

    /* Libellés */

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? (string) $this->status;
    }

    public function getStatusToneAttribute(): string
    {
        return self::STATUS_TONES[$this->status] ?? 'neutral';
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? (string) $this->priority;
    }

    public function getPriorityToneAttribute(): string
    {
        return self::PRIORITY_TONES[$this->priority] ?? 'neutral';
    }

    public function getKindLabelAttribute(): string
    {
        return $this->kind === 'preventive' ? 'Préventive' : 'Corrective';
    }

    public function getTargetLabelAttribute(): string
    {
        return match ($this->target_type) {
            'infrastructure' => $this->infrastructure->name ?? 'Infrastructure supprimée',
            'asset'          => $this->asset ? ($this->asset->name ?: 'Matière #'.$this->asset->id) : 'Matière supprimée',
            default          => 'Autre',
        };
    }

    public function getRequesterNameAttribute(): string
    {
        return $this->requestedBy->name ?? $this->created_by ?? '—';
    }

    /** Délai de traitement en jours (création → fin). */
    public function getLeadTimeAttribute(): ?int
    {
        return $this->completed_at && $this->created_at ? (int) $this->created_at->diffInDays($this->completed_at) : null;
    }
}
