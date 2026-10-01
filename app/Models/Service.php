<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes, HasFactory;
    use \App\Models\Concerns\ScopedByService;

    /** Un utilisateur limité à son service ne voit que celui-ci. */
    public const SERVICE_COLUMN = 'id';


    public $table = 'services';

    public static $searchable = [
        'name',
    ];

    protected $fillable = [
        'name',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function agents()
    {
        return $this->hasMany(Agent::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
