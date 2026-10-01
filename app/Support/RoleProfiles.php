<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Profils d'accès des acteurs du système.
 *
 * | Rôle                          | Niveau               | Périmètre                          |
 * |-------------------------------|----------------------|------------------------------------|
 * | Super administrateur          | Total                | Tout, sans restriction             |
 * | Administrateur système        | Technique            | Comptes, rôles, notifications      |
 * | Directeur                     | Direction            | Consultation globale + approbation |
 * | Comptable matière principal   | Supervision métier   | Tous les services                  |
 * | Administrateur des matières   | Opérationnel central | Matières, référentiels, stock      |
 * | Comptable matière secondaire  | Opérationnel local   | Son service uniquement             |
 * | Responsable maintenance       | Spécialisé           | Maintenance                        |
 * | Responsable infrastructures   | Spécialisé           | Infrastructures et projets         |
 * | Agent / demandeur             | Utilisateur          | Son service, ses demandes          |
 */
class RoleProfiles
{
    private const COMMON = ['profile_password_edit', 'user_alert_access', 'user_alert_show'];

    private const REQUESTER = ['task_management_access', 'maintenance_request_access', 'maintenance_request_show', 'maintenance_request_create'];

    private const READ_ASSETS = [
        'asset_management_access', 'asset_access', 'asset_show', 'assets_history_access',
        'asset_category_access', 'asset_category_show', 'asset_location_access', 'asset_location_show',
        'asset_status_access', 'asset_status_show',
    ];

    private const REFERENTIALS = ['asset_category_*', 'asset_location_*', 'asset_status_*', 'supplier_*', 'bon_*'];

    public const PROFILES = [
        'super_admin' => [
            'title' => 'Super administrateur',
            'match' => ['^admin$', 'super'],
            'allow' => ['*'],
            'deny'  => ['perimetre_service'],
        ],
        'admin_systeme' => [
            'title' => 'Administrateur système',
            'match' => ['administrateur syst', 'admin.*syst'],
            'allow' => [
                'user_management_access', 'user_*', 'role_*', 'permission_*', 'user_alert_*',
                'contact_message_*', 'service_access', 'service_show',
            ],
            'deny'  => ['perimetre_service'],
        ],
        'directeur' => [
            'title' => 'Directeur',
            'match' => ['directeur', 'direction'],
            'allow' => ['*_access', '*_show', 'maintenance_request_approve', 'periodic_report_access'],
            'deny'  => ['perimetre_service', 'permission_*', 'role_*', 'user_access', 'user_show', 'user_management_access'],
        ],
        'comptable_principal' => [
            'title' => 'Comptable matière principal',
            'match' => ['comptable.*principal'],
            'allow' => [
                ...self::READ_ASSETS, 'supplier_access', 'supplier_show', 'bon_access', 'bon_show',
                'asset_*', 'assignment_*', 'agent_*', 'service_*',
                'inventaire_*', 'stock_*', 'periodic_report_access', ...self::REQUESTER,
            ],
            'deny'  => ['perimetre_service', 'asset_category_create', 'asset_category_edit', 'asset_category_delete',
                'asset_location_create', 'asset_location_edit', 'asset_location_delete',
                'asset_status_create', 'asset_status_edit', 'asset_status_delete'],
        ],
        'administrateur_matieres' => [
            'title' => 'Administrateur des matières',
            'match' => ['mati[eè]re'],
            'allow' => [
                ...self::READ_ASSETS, ...self::REFERENTIALS,
                'asset_*', 'assignment_*', 'agent_*', 'service_access', 'service_show',
                'inventaire_access', 'inventaire_show', 'inventaire_create', 'inventaire_edit',
                'stock_management_access', 'stock_item_*', 'stock_movement_access', 'stock_movement_create',
                ...self::REQUESTER,
            ],
            'deny'  => ['perimetre_service', 'inventaire_close', 'stock_movement_adjust'],
        ],
        'comptable_secondaire' => [
            'title' => 'Comptable matière secondaire',
            'match' => ['comptable.*secondaire'],
            'allow' => [
                'perimetre_service', ...self::READ_ASSETS, 'asset_edit',
                'assignment_access', 'assignment_show', 'assignment_create', 'assignment_return',
                'agent_access', 'agent_show', 'agent_create', 'agent_edit',
                'inventaire_access', 'inventaire_show', 'inventaire_create', 'inventaire_edit',
                'stock_management_access', 'stock_item_access', 'stock_item_show', 'stock_movement_access', 'stock_movement_create',
                ...self::REQUESTER,
            ],
            'deny'  => [],
        ],
        'responsable_maintenance' => [
            'title' => 'Responsable maintenance',
            'match' => ['maintenance'],
            'allow' => [
                'task_management_access', 'task_*', 'tasks_calendar_access', 'maintenance_request_*', 'maintenance_plan_*',
                'infrastructure_management_access', 'infrastructure_access', 'infrastructure_show',
                ...self::READ_ASSETS, 'periodic_report_access',
            ],
            'deny'  => ['perimetre_service', 'maintenance_request_approve'],
        ],
        'responsable_infrastructures' => [
            'title' => 'Responsable infrastructures',
            'match' => ['infrastructure'],
            'allow' => [
                'infrastructure_management_access', 'infrastructure_*', 'project_*', 'report_*', 'chef_projet_*', 'intervenant_*',
                'maintenance_plan_access', 'maintenance_plan_show', 'periodic_report_access', ...self::REQUESTER,
            ],
            'deny'  => ['perimetre_service'],
        ],
        'agent' => [
            'title' => 'Agent / demandeur',
            'match' => ['agent', 'demandeur'],
            'allow' => ['perimetre_service', 'asset_management_access', 'asset_access', 'asset_show', ...self::REQUESTER],
            'deny'  => [],
        ],
    ];

    /**
     * Applique les profils aux rôles (création des rôles manquants).
     *
     * @param  bool  $reset  true : les droits du rôle sont remplacés par ceux du profil ;
     *                       false : les droits manquants sont seulement ajoutés.
     * @return array<string, string> résumé par rôle
     */
    public static function apply(bool $reset = false): array
    {
        $permissions = DB::table('permissions')->whereNull('deleted_at')->pluck('id', 'title');
        $roles = DB::table('roles')->whereNull('deleted_at')->orderBy('id')->get(['id', 'title']);
        $used = [];
        $report = [];

        foreach (self::PROFILES as $profile) {
            $role = $roles->first(fn ($r) => ! in_array($r->id, $used, true) && self::matches($r->title, $profile['match']));

            if (! $role) {
                $id = DB::table('roles')->insertGetId(['title' => $profile['title'], 'created_at' => now(), 'updated_at' => now()]);
                $role = (object) ['id' => $id, 'title' => $profile['title']];
            } elseif ($role->title !== $profile['title']) {
                DB::table('roles')->where('id', $role->id)->update(['title' => $profile['title'], 'updated_at' => now()]);
            }
            $used[] = $role->id;

            $wanted = self::permissionsFor($profile, $permissions);

            if ($reset) {
                DB::table('permission_role')->where('role_id', $role->id)->delete();
                $missing = $wanted;
            } else {
                $missing = $wanted->diff(DB::table('permission_role')->where('role_id', $role->id)->pluck('permission_id'));
            }

            foreach ($missing->chunk(200) as $chunk) {
                DB::table('permission_role')->insert($chunk->map(fn ($pid) => ['role_id' => $role->id, 'permission_id' => $pid])->values()->all());
            }

            $report[$profile['title']] = $reset ? $wanted->count().' droit(s)' : ($missing->count() ? '+'.$missing->count().' droit(s)' : 'à jour');
        }

        return $report;
    }

    private static function permissionsFor(array $profile, $permissions)
    {
        return $permissions->filter(function ($id, $title) use ($profile) {
            foreach ($profile['deny'] as $pattern) {
                if (fnmatch($pattern, $title)) {
                    return false;
                }
            }
            foreach (array_merge($profile['allow'], self::COMMON) as $pattern) {
                if (fnmatch($pattern, $title)) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    private static function matches(string $title, array $patterns): bool
    {
        $title = mb_strtolower(trim($title));

        foreach ($patterns as $pattern) {
            if (preg_match('/'.$pattern.'/u', $title)) {
                return true;
            }
        }

        return false;
    }
}
