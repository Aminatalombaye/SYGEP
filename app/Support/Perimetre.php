<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Périmètre de travail de l'utilisateur connecté.
 *
 * Les rôles qui ont le droit « perimetre_service » (comptable matière secondaire,
 * agent demandeur) ne voient que les données de leur propre service.
 * Les autres rôles travaillent sur l'ensemble du ministère.
 */
class Perimetre
{
    private static array $cache = [];

    /** Identifiant du service imposé, ou null si l'utilisateur voit tout. */
    public static function serviceId(): ?int
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        if (! array_key_exists($user->id, self::$cache)) {
            self::$cache[$user->id] = Gate::forUser($user)->allows('perimetre_service')
                ? (int) ($user->service_id ?: 0)   // 0 : aucun service rattaché → ne voit rien
                : null;
        }

        return self::$cache[$user->id];
    }

    public static function isLocal(): bool
    {
        return self::serviceId() !== null;
    }

    public static function label(): ?string
    {
        if (! self::isLocal()) {
            return null;
        }

        return Auth::user()->service?->name ?? 'Aucun service rattaché';
    }

    public static function flush(): void
    {
        self::$cache = [];
    }
}
