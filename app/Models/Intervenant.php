<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Intervenant extends Model
{
    use SoftDeletes;

    public const ROLES = [
        'entreprise'    => 'Entreprise de travaux',
        'bureau_etudes' => 'Bureau d\'études',
        'maitre_oeuvre' => 'Maître d\'œuvre',
        'controle'      => 'Bureau de contrôle',
        'fournisseur'   => 'Fournisseur',
        'technicien'    => 'Technicien / artisan',
        'autre'         => 'Autre',
    ];

    public $table = 'intervenants';

    protected $fillable = [
        'nom',
        'prenom',
        'organisation',
        'role',
        'telephone',
        'email',
        'adresse',
        'notes',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)->withPivot('mission');
    }

    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function getNameAttribute(): string
    {
        $person = trim($this->prenom.' '.$this->nom);

        return $this->organisation ? ($person ? $this->organisation.' — '.$person : $this->organisation) : $person;
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? (string) $this->role;
    }
}
