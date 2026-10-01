<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use SoftDeletes, HasFactory;
    use \App\Models\Concerns\ScopedByService;


    public $table = 'agents';

    public static $searchable = [
        'nom',
        'prenom',
        'email',
    ];

    protected $fillable = [
        'nom',
        'prenom',
        'adresse',
        'email',
        'telephone',
        'service_id',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function histories()
    {
        return $this->hasMany(AssetsHistory::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->prenom.' '.$this->nom) ?: 'Agent #'.$this->id;
    }
}
