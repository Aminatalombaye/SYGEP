<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'assignments';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'quantity',
        'utilisateur',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function matieres()
    {
        return $this->belongsToMany(Asset::class);
    }

    public function atributions()
    {
        return $this->belongsToMany(Attribution::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }
}
