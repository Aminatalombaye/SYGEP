<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetsHistory extends Model
{
    use HasFactory;
    use \App\Models\Concerns\ScopedByService;


    public const ACTIONS = [
        'creation'     => 'Enregistrement',
        'affectation'  => 'Affectation',
        'restitution'  => 'Restitution',
        'transfert'    => 'Transfert',
        'inventaire'   => 'Inventaire',
        'maintenance'  => 'Maintenance',
        'modification' => 'Modification',
    ];

    public $table = 'assets_histories';

    protected $fillable = [
        'asset_id',
        'status_id',
        'location_id',
        'assigned_user_id',
        'action',
        'agent_id',
        'service_id',
        'assignment_id',
        'user_id',
        'notes',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id')->withTrashed();
    }

    public function status()
    {
        return $this->belongsTo(AssetStatus::class, 'status_id')->withTrashed();
    }

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'location_id')->withTrashed();
    }

    public function assigned_user()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class)->withTrashed();
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTIONS[$this->action] ?? 'Mouvement';
    }
}
