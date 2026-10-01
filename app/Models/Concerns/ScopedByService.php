<?php

namespace App\Models\Concerns;

use App\Support\Perimetre;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Limite automatiquement les enregistrements au service de l'utilisateur
 * lorsque son rôle est restreint à son service (voir App\Support\Perimetre).
 *
 * Le modèle peut définir la constante SERVICE_COLUMN (par défaut « service_id »).
 */
trait ScopedByService
{
    public static function bootScopedByService(): void
    {
        static::addGlobalScope('perimetre', function (Builder $query) {
            $serviceId = Perimetre::serviceId();

            if ($serviceId !== null) {
                $query->where($query->getModel()->qualifyColumn(static::serviceColumn()), $serviceId);
            }
        });

        // Un utilisateur limité ne peut créer ou déplacer un enregistrement que dans son service.
        static::saving(function ($model) {
            $serviceId = Perimetre::serviceId();
            $column = static::serviceColumn();

            if ($serviceId === null || $column === $model->getKeyName()) {
                return;
            }

            if (! $model->exists && empty($model->{$column})) {
                $model->{$column} = $serviceId;
            }

            if (! empty($model->{$column}) && (int) $model->{$column} !== $serviceId) {
                throw new HttpException(403, 'Cette opération concerne un autre service que le vôtre.');
            }
        });
    }

    protected static function serviceColumn(): string
    {
        return defined(static::class.'::SERVICE_COLUMN') ? static::SERVICE_COLUMN : 'service_id';
    }
}
