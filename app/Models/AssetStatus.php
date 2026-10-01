<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetStatus extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'asset_statuses';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public const AVAILABLE = 'Available';
    public const ASSIGNED = 'Assigned';
    public const BROKEN = 'Broken';
    public const REPAIR = 'Out for Repair';

    public const LABELS = [
        'available'      => 'Disponible',
        'assigned'       => 'Affecté',
        'not available'  => 'Indisponible',
        'broken'         => 'En panne',
        'out for repair' => 'En réparation',
    ];

    private static array $idCache = [];

    public static function label(?string $name): string
    {
        if ($name === null || $name === '') {
            return '—';
        }

        return self::LABELS[mb_strtolower($name)] ?? $name;
    }

    public function getLabelAttribute(): string
    {
        return self::label($this->name);
    }

    public function getToneAttribute(): string
    {
        return match (mb_strtolower((string) $this->name)) {
            'available', 'disponible', 'neuf', 'bon état', 'réparé' => 'good',
            'assigned', 'affecté', 'affectée' => 'info',
            'broken', 'out for repair', 'en panne', 'hors service', 'endommagé', 'en réparation', 'en attente de réparation', 'perdu' => 'critical',
            'usagé', 'pas disponible' => 'warning',
            default => 'neutral',
        };
    }

    /** Noms équivalents saisis en français dans la base. */
    public const SYNONYMS = [
        self::AVAILABLE => ['Disponible', 'Neuf', 'Bon état', 'Réparé'],
        self::ASSIGNED  => ['Affecté', 'Affectée'],
        self::BROKEN    => ['En panne', 'Hors service', 'Endommagé'],
        self::REPAIR    => ['En réparation', 'En attente de réparation'],
    ];

    public static function idFor(string $name): ?int
    {
        if (! array_key_exists($name, self::$idCache)) {
            $id = static::where('name', $name)->value('id');

            foreach (self::SYNONYMS[$name] ?? [] as $alias) {
                $id ??= static::where('name', $alias)->value('id');
            }

            self::$idCache[$name] = $id;
        }

        return self::$idCache[$name];
    }

    /** Identifiants de tous les statuts équivalents (ex. « Available » + « Disponible »). */
    public static function idsFor(string $name): array
    {
        return static::whereIn('name', array_merge([$name], self::SYNONYMS[$name] ?? []))->pluck('id')->all();
    }
}
