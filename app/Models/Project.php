<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes, HasFactory;

    public const STATUSES = [
        'planifie' => 'Planifié',
        'en_cours' => 'En cours',
        'suspendu' => 'Suspendu',
        'termine'  => 'Terminé',
        'annule'   => 'Annulé',
    ];

    public const STATUS_TONES = [
        'planifie' => 'warning',
        'en_cours' => 'info',
        'suspendu' => 'critical',
        'termine'  => 'good',
        'annule'   => 'neutral',
    ];

    public const TYPES = [
        'construction'   => 'Construction',
        'rehabilitation' => 'Réhabilitation',
        'extension'      => 'Extension',
        'equipement'     => 'Équipement / modernisation',
    ];

    public $table = 'projects';

    protected $fillable = [
        'reference',
        'name',
        'type',
        'description',
        'start_date',
        'end_date',
        'budget',
        'spent',
        'progress',
        'completed_at',
        'status',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date'   => 'date',
            'end_date'     => 'date',
            'completed_at' => 'date',
            'budget'       => 'decimal:2',
            'spent'        => 'decimal:2',
            'progress'     => 'integer',
        ];
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public static function nextReference(): string
    {
        $prefix = 'PRJ-'.now()->year.'-';
        $last = static::withTrashed()->where('reference', 'like', $prefix.'%')->orderByDesc('reference')->value('reference');
        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 2, '0', STR_PAD_LEFT);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['planifie', 'en_cours', 'suspendu']);
    }

    public function scopeLate(Builder $query): Builder
    {
        return $query->active()->whereNotNull('end_date')->whereDate('end_date', '<', today());
    }

    public function infrastructures()
    {
        return $this->belongsToMany(Infrastructure::class);
    }

    public function chef_projets()
    {
        return $this->belongsToMany(ChefProjet::class);
    }

    public function intervenants()
    {
        return $this->belongsToMany(Intervenant::class)->withPivot('mission');
    }

    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class)->orderBy('position')->orderBy('due_date')->orderBy('id');
    }

    public function reports()
    {
        return $this->belongsToMany(Report::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /** Recalcule l'avancement à partir des jalons pondérés (s'il y en a). */
    public function refreshProgress(): void
    {
        $milestones = $this->milestones()->get(['weight', 'done_at']);

        if ($milestones->isEmpty()) {
            return;
        }

        $total = max($milestones->sum('weight'), 1);
        $done = $milestones->whereNotNull('done_at')->sum('weight');
        $progress = (int) round($done * 100 / $total);

        $attributes = ['progress' => $progress];
        if ($progress === 100 && in_array($this->status, ['planifie', 'en_cours'], true)) {
            $attributes['status'] = 'termine';
            $attributes['completed_at'] = $this->completed_at ?? today();
        } elseif ($progress > 0 && $this->status === 'planifie') {
            $attributes['status'] = 'en_cours';
        } elseif ($progress < 100 && $this->status === 'termine') {
            $attributes['status'] = 'en_cours';
            $attributes['completed_at'] = null;
        }

        $this->forceFill($attributes)->save();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? (string) $this->status;
    }

    public function getStatusToneAttribute(): string
    {
        return $this->is_late ? 'critical' : (self::STATUS_TONES[$this->status] ?? 'neutral');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? (string) $this->type;
    }

    public function getIsLateAttribute(): bool
    {
        return in_array($this->status, ['planifie', 'en_cours', 'suspendu'], true)
            && $this->end_date && $this->end_date->lt(today());
    }

    /** Avancement attendu à date (temps écoulé / durée prévue). */
    public function getExpectedProgressAttribute(): ?int
    {
        if (! $this->start_date || ! $this->end_date || $this->end_date->lte($this->start_date)) {
            return null;
        }

        $total = $this->start_date->diffInDays($this->end_date);
        $elapsed = $this->start_date->diffInDays(min(today(), $this->end_date), false);

        return (int) max(0, min(100, round($elapsed * 100 / $total)));
    }

    public function getBudgetRateAttribute(): ?int
    {
        return $this->budget > 0 && $this->spent !== null ? (int) round($this->spent * 100 / $this->budget) : null;
    }
}
