<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Nettoyage des rôles :
 * - fusion des rôles portant le même nom (les comptes passent sur le plus ancien) ;
 * - suppression du rôle générique « User », remplacé par « Agent / demandeur ».
 */
return new class extends Migration
{
    public function up(): void
    {
        $roles = DB::table('roles')->whereNull('deleted_at')->orderBy('id')->get(['id', 'title']);

        // 1. Doublons (même nom, à la casse et aux espaces près)
        foreach ($roles->groupBy(fn ($r) => $this->key($r->title)) as $same) {
            if ($same->count() < 2) {
                continue;
            }
            $keep = $same->first()->id;
            foreach ($same->slice(1) as $duplicate) {
                $this->merge($duplicate->id, $keep);
            }
        }

        // 2. Rôle générique « User » / « Utilisateur »
        $agent = DB::table('roles')->whereNull('deleted_at')->where('title', 'Agent / demandeur')->value('id');
        $generic = DB::table('roles')->whereNull('deleted_at')
            ->whereIn(DB::raw('LOWER(TRIM(title))'), ['user', 'users', 'utilisateur', 'utilisateurs', 'simple user'])
            ->pluck('id');

        foreach ($generic as $id) {
            if ($agent) {
                $this->merge($id, $agent);
            } else {
                DB::table('roles')->where('id', $id)->update(['title' => 'Agent / demandeur']);
                $agent = $id;
            }
        }

        // Noms et descriptions harmonisés pour les rôles restants.
        \App\Support\RoleProfiles::apply();
    }

    /** Nom normalisé : sans accents ni casse, avec les anciens intitulés ramenés au nom actuel. */
    private function key(string $title): string
    {
        $key = trim(preg_replace('/\s+/', ' ', mb_strtolower(\Illuminate\Support\Str::ascii($title))));

        return [
            'responsable matieres'          => 'administrateur des matieres',
            'administrateur matieres'       => 'administrateur des matieres',
            'comptable principal'           => 'comptable matiere principal',
            'comptable secondaire'          => 'comptable matiere secondaire',
            'admin'                         => 'super administrateur',
        ][$key] ?? $key;
    }

    /** Transfère les comptes du rôle $from vers $to, puis supprime $from. */
    private function merge(int $from, int $to): void
    {
        $users = DB::table('role_user')->where('role_id', $from)->pluck('user_id');

        foreach ($users as $userId) {
            if (! DB::table('role_user')->where(['role_id' => $to, 'user_id' => $userId])->exists()) {
                DB::table('role_user')->insert(['role_id' => $to, 'user_id' => $userId]);
            }
        }

        DB::table('role_user')->where('role_id', $from)->delete();
        DB::table('permission_role')->where('role_id', $from)->delete();
        DB::table('roles')->where('id', $from)->update(['deleted_at' => now()]);
    }

    public function down(): void
    {
        // Les rôles supprimés restent dans la table (suppression logique) ; rien à restaurer automatiquement.
    }
};
