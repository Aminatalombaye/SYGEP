<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use App\Observers\AssetsHistoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[ObservedBy([AssetsHistoryObserver::class])]
class Asset extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, HasFactory;
    use \App\Models\Concerns\ScopedByService;


    public $table = 'assets';

    protected $appends = [
        'photos',
    ];

    public static $searchable = [
        'date_achat',
        'date_mise_en_service',
        'modele',
        'assigned_to',
    ];

    protected $dates = [
        'date_achat',
        'date_mise_en_service',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'category_id',
        'serial_number',
        'name',
        'status_id',
        'location_id',
        'agent_id',
        'service_id',
        'notes',
        'type',
        'date_achat',
        'date_mise_en_service',
        'modele',
        'assigned_to',
        'qr_code',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit(Fit::Crop, 50, 50);
        $this->addMediaConversion('preview')->fit(Fit::Crop, 120, 120);
    }

    protected static function booted(): void
    {
        static::created(function (self $asset) {
            if (blank($asset->qr_code)) {
                $asset->qr_code = self::codeFor($asset->id);
                $asset->saveQuietly();
            }
        });
    }

    public static function codeFor(int $id): string
    {
        return 'SYGEP-MAT-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    public function getScanUrlAttribute(): ?string
    {
        return $this->qr_code ? route('qr.scan', $this->qr_code) : null;
    }

    public function qrSvg(int $size = 220): ?\Illuminate\Support\HtmlString
    {
        if (! $this->scan_url) {
            return null;
        }

        $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size($size)
            ->margin(1)
            ->errorCorrection('M')
            ->color(194,97,15)
            ->generate($this->scan_url);

        return new \Illuminate\Support\HtmlString((string) $svg);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class)->withTrashed();
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function assignments()
    {
        return $this->belongsToMany(Assignment::class)
            ->withPivot(['returned_at', 'return_condition', 'return_notes', 'returned_by_id']);
    }

    public function currentAssignment()
    {
        return $this->assignments()->wherePivotNull('returned_at')->latest('assignments.assigned_at')->first();
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class)->latest();
    }

    public function histories()
    {
        return $this->hasMany(AssetsHistory::class)->latest();
    }

    public function isAssigned(): bool
    {
        return $this->agent_id !== null || $this->service_id !== null;
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function getPhotosAttribute()
    {
        return $this->getMedia('photos');
    }

    public function status()
    {
        return $this->belongsTo(AssetStatus::class, 'status_id');
    }

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'location_id');
    }

    public function getDateAchatAttribute($value)
    {
        return $value ? Carbon::parse($value)->format(config('panel.date_format')) : null;
    }

    public function setDateAchatAttribute($value)
    {
        $this->attributes['date_achat'] = $value ? Carbon::createFromFormat(config('panel.date_format'), $value)->format('Y-m-d') : null;
    }

    public function getDateMiseEnServiceAttribute($value)
    {
        return $value ? Carbon::parse($value)->format(config('panel.date_format')) : null;
    }

    public function setDateMiseEnServiceAttribute($value)
    {
        $this->attributes['date_mise_en_service'] = $value ? Carbon::createFromFormat(config('panel.date_format'), $value)->format('Y-m-d') : null;
    }

    public function fournisseurs()
    {
        return $this->belongsToMany(Supplier::class);
    }

    public function bons()
    {
        return $this->belongsToMany(Bon::class);
    }

    public function inventaire_codes()
    {
        return $this->belongsToMany(Inventaire::class);
    }
}
