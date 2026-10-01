<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAlert extends Model
{
    use HasFactory;

    public $table = 'user_alerts';

    protected $fillable = [
        'alert_text',
        'alert_link',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('read');
    }

    public function getKindAttribute(): string
    {
        $link = (string) $this->alert_link;

        return match (true) {
            str_contains($link, '/contact-messages') => 'contact',
            str_contains($link, '/assignments') => 'affectation',
            str_contains($link, '/maintenance-requests'), str_contains($link, '/maintenance-plans'), str_contains($link, '/tasks') => 'maintenance',
            str_contains($link, '/stock-') => 'stock',
            str_contains($link, '/projects') => 'projet',
            default => 'info',
        };
    }

    public function getKindLabelAttribute(): string
    {
        return [
            'contact'     => 'Message de contact',
            'affectation' => 'Affectation',
            'maintenance' => 'Maintenance',
            'stock'       => 'Stock',
            'projet'      => 'Projet',
            'info'        => 'Information',
        ][$this->kind];
    }

    public function getIconAttribute(): string
    {
        return [
            'contact'     => 'bi-envelope',
            'affectation' => 'bi-person-check',
            'maintenance' => 'bi-tools',
            'stock'       => 'bi-box2',
            'projet'      => 'bi-kanban',
            'info'        => 'bi-megaphone',
        ][$this->kind];
    }

    public function isInternal(): bool
    {
        return $this->alert_link && str_starts_with($this->alert_link, url('/'));
    }
}
