<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            self::$cache[$user->id] = self::hasServicePerimeter($user->id)
                ? (int) ($user->service_id ?: 0)   // 0 : aucun service rattaché → ne voit rien
                : null;
        }

        return self::$cache[$user->id];
    }

    /**
     * Le compte a-t-il un rôle limité à son service ?
     *
     * La lecture se fait directement en base, sans passer par les « gates » : elles ne sont définies
     * qu'en cours de requête, alors que le filtre doit déjà s'appliquer quand une adresse est liée à
     * un enregistrement (fiche, modification…). Un calcul trop précoce laissait passer l'accès.
     */
    private static function hasServicePerimeter(int $userId): bool
    {
        return DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('permission_role', 'permission_role.role_id', '=', 'roles.id')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('role_user.user_id', $userId)
            ->where('permissions.title', 'perimetre_service')
            ->whereNull('roles.deleted_at')
            ->whereNull('permissions.deleted_at')
            ->exists();
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
