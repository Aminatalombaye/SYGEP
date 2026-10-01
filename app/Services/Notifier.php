<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAlert;
use Illuminate\Support\Collection;

/**
 * Envoie les notifications internes (cloche de l'espace de gestion).
 */
class Notifier
{
    /** Notifie tous les utilisateurs dont un rôle possède la permission donnée. */
    public static function permission(string $permission, string $text, ?string $link = null, ?int $except = null): void
    {
        $ids = User::whereHas('roles.permissions', fn ($q) => $q->where('title', $permission))
            ->when($except, fn ($q) => $q->whereKeyNot($except))
            ->pluck('id');

        self::users($ids, $text, $link);
    }

    /** @param  iterable<int|null>|int|null  $userIds */
    public static function users($userIds, string $text, ?string $link = null): void
    {
        $ids = Collection::wrap($userIds)->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return;
        }

        $alert = UserAlert::create([
            'alert_text' => mb_substr($text, 0, 250),
            'alert_link' => $link,
        ]);

        $alert->users()->sync($ids->all());
    }
}
