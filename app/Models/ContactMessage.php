<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    use SoftDeletes;

    public $table = 'contact_messages';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'structure',
        'subject',
        'message',
        'ip_address',
        'read_at',
    ];

    public const SUBJECTS = [
        'information'   => "Demande d'information",
        'acces'         => "Demande d'accès / compte",
        'assistance'    => 'Assistance technique',
        'signalement'   => 'Signalement (bien, panne, anomalie)',
        'autre'         => 'Autre',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function getSubjectLabelAttribute(): string
    {
        return self::SUBJECTS[$this->subject] ?? $this->subject;
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
