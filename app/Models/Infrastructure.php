<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Infrastructure extends Model
{
    use SoftDeletes, HasFactory;

    public const NATURES = [
        'structure' => 'Structure (centre, lycée, institut…)',
        'batiment'  => 'Bâtiment',
        'bloc'      => 'Bloc / salle / atelier',
    ];

    public const STATUSES = [
        'en_service'        => 'En service',
        'en_construction'   => 'En construction',
        'en_rehabilitation' => 'En réhabilitation',
        'en_maintenance'    => 'En maintenance',
        'hors_service'      => 'Hors service',
    ];

    public const STATUS_TONES = [
        'en_service'        => 'good',
        'en_construction'   => 'info',
        'en_rehabilitation' => 'info',
        'en_maintenance'    => 'warning',
        'hors_service'      => 'critical',
    ];

    public const CONDITIONS = [
        'bon'      => 'Bon',
        'moyen'    => 'Moyen',
        'degrade'  => 'Dégradé',
        'critique' => 'Critique',
    ];

    public const CONDITION_TONES = [
        'bon'      => 'good',
        'moyen'    => 'info',
        'degrade'  => 'warning',
        'critique' => 'critical',
    ];

    public $table = 'infrastructures';

    public static $searchable = [
        'name',
        'location',
    ];

    protected $fillable = [
        'name',
        'nature',
        'parent_id',
        'description',
        'status',
        'condition',
        'last_inspection_at',
        'location',
        'surface',
        'construction_date',
        'acquisition_value',
        'depreciation_years',
        'depreciation_plan',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'construction_date'  => 'date',
            'last_inspection_at' => 'date',
            'acquisition_value'  => 'decimal:2',
            'surface'            => 'decimal:2',
            'depreciation_years' => 'integer',
        ];
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id')->withTrashed();
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function maintenancePlans()
    {
        return $this->hasMany(MaintenancePlan::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? (string) $this->status;
    }

    public function getStatusToneAttribute(): string
    {
        return self::STATUS_TONES[$this->status] ?? 'neutral';
    }

    public function getNatureLabelAttribute(): string
    {
        return [
            'structure' => 'Structure',
            'batiment'  => 'Bâtiment',
            'bloc'      => 'Bloc',
        ][$this->nature] ?? (string) $this->nature;
    }

    public function getConditionLabelAttribute(): ?string
    {
        return $this->condition ? (self::CONDITIONS[$this->condition] ?? $this->condition) : null;
    }

    public function getConditionToneAttribute(): string
    {
        return self::CONDITION_TONES[$this->condition] ?? 'neutral';
    }

    /* Amortissement linéaire à partir de la date de mise en service */

    public function canDepreciate(): bool
    {
        return $this->acquisition_value > 0 && $this->depreciation_years > 0 && $this->construction_date;
    }

    public function getAnnualDepreciationAttribute(): ?float
    {
        return $this->canDepreciate() ? round($this->acquisition_value / $this->depreciation_years, 2) : null;
    }

    public function getDepreciationEndAttribute(): ?Carbon
    {
        return $this->construction_date && $this->depreciation_years
            ? $this->construction_date->copy()->addYears($this->depreciation_years)
            : null;
    }

    /** Part déjà amortie (0 à 1) à une date donnée. */
    public function depreciatedShare(?Carbon $at = null): ?float
    {
        if (! $this->canDepreciate()) {
            return null;
        }

        $at ??= today();
        $start = $this->construction_date;
        $end = $this->depreciation_end;

        if ($at->lte($start)) {
            return 0.0;
        }
        if ($at->gte($end)) {
            return 1.0;
        }

        return $start->diffInDays($at) / $start->diffInDays($end);
    }

    public function getNetBookValueAttribute(): ?float
    {
        $share = $this->depreciatedShare();

        return $share === null ? null : round($this->acquisition_value * (1 - $share), 2);
    }

    /** Plan d'amortissement par annuité. */
    public function depreciationSchedule(): array
    {
        if (! $this->canDepreciate()) {
            return [];
        }

        $rows = [];
        $annuity = $this->annual_depreciation;
        $cumulative = 0.0;

        for ($i = 1; $i <= $this->depreciation_years; $i++) {
            $from = $this->construction_date->copy()->addYears($i - 1);
            $to = $this->construction_date->copy()->addYears($i)->subDay();
            $amount = $i === $this->depreciation_years
                ? round($this->acquisition_value - $cumulative, 2)
                : $annuity;
            $cumulative = round($cumulative + $amount, 2);

            $rows[] = [
                'rank'       => $i,
                'from'       => $from,
                'to'         => $to,
                'amount'     => $amount,
                'cumulative' => $cumulative,
                'net'        => round($this->acquisition_value - $cumulative, 2),
                'current'    => today()->between($from, $to),
                'past'       => $to->lt(today()),
            ];
        }

        return $rows;
    }
}
