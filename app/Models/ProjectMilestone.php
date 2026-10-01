<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMilestone extends Model
{
    public $table = 'project_milestones';

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'due_date',
        'weight',
        'done_at',
        'intervenant_id',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'done_at'  => 'date',
            'weight'   => 'integer',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function intervenant()
    {
        return $this->belongsTo(Intervenant::class)->withTrashed();
    }

    public function getIsLateAttribute(): bool
    {
        return ! $this->done_at && $this->due_date && $this->due_date->lt(today());
    }
}
