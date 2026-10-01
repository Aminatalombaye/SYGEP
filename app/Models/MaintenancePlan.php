<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenancePlan extends Model
{
    use SoftDeletes;

    public const FREQUENCIES = [
        1  => 'Mensuelle',
        3  => 'Trimestrielle',
        6  => 'Semestrielle',
        12 => 'Annuelle',
        24 => 'Tous les 2 ans',
    ];

    public $table = 'maintenance_plans';

    protected $fillable = [
        'title',
        'description',
        'target_type',
        'infrastructure_id',
        'asset_id',
        'frequency_months',
        'next_due_at',
        'last_done_at',
        'lead_days',
        'responsible_id',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'next_due_at'      => 'date',
            'last_done_at'     => 'date',
            'active'           => 'boolean',
            'frequency_months' => 'integer',
            'lead_days'        => 'integer',
        ];
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->where('active', true)
            ->whereRaw('DATE_SUB(next_due_at, INTERVAL lead_days DAY) <= ?', [today()->toDateString()]);
    }

    public function infrastructure()
    {
        return $this->belongsTo(Infrastructure::class)->withTrashed();
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class)->withTrashed();
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function requests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function getFrequencyLabelAttribute(): string
    {
        return self::FREQUENCIES[$this->frequency_months] ?? 'Tous les '.$this->frequency_months.' mois';
    }

    public function getIsDueAttribute(): bool
    {
        return $this->active && $this->next_due_at && $this->next_due_at->copy()->subDays($this->lead_days)->lte(today());
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->active && $this->next_due_at && $this->next_due_at->lt(today());
    }

    public function getTargetLabelAttribute(): string
    {
        return match ($this->target_type) {
            'infrastructure' => $this->infrastructure->name ?? '—',
            'asset'          => $this->asset->name ?? '—',
            default          => 'Autre',
        };
    }
}
