<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bon extends Model
{
    use HasFactory;

    public $table = 'bons';

    public static $searchable = [
        'bon',
    ];

    protected $dates = [
        'date_emission',
        'date_livraison',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'date_emission',
        'organisation',
        'reference_commande',
        'nom_destinataire',
        'bon',
        'date_livraison',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getDateEmissionAttribute($value)
    {
        return $value ? Carbon::parse($value)->format(config('panel.date_format')) : null;
    }

    public function setDateEmissionAttribute($value)
    {
        $this->attributes['date_emission'] = $value ? Carbon::createFromFormat(config('panel.date_format'), $value)->format('Y-m-d') : null;
    }

    public function getDateLivraisonAttribute($value)
    {
        return $value ? Carbon::parse($value)->format(config('panel.date_format')) : null;
    }

    public function setDateLivraisonAttribute($value)
    {
        $this->attributes['date_livraison'] = $value ? Carbon::createFromFormat(config('panel.date_format'), $value)->format('Y-m-d') : null;
    }
}
