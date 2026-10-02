<?php

namespace App\Support;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

/**
 * Classement lisible des permissions : sections (comme le menu), modules et actions.
 * Utilisé par la matrice des rôles et par la liste des permissions.
 */
class PermissionCatalog
{
    public const ACTIONS = [
        'access' => 'Accès',
        'show'   => 'Voir',
        'create' => 'Créer',
        'edit'   => 'Modifier',
        'delete' => 'Supprimer',
    ];

    public const SPECIAL = [
        'validate' => 'Avis technique',
        'approve'  => 'Approuver',
        'close'    => 'Clôturer',
        'adjust'   => 'Ajuster',
        'return'   => 'Restituer',
    ];

    /** [libellé, icône, modules dans l'ordre d'affichage] */
    public const SECTIONS = [
        'admin'           => ['Administration', 'bi-shield-lock', ['user_management', 'user', 'role', 'permission', 'user_alert', 'contact_message', 'profile_password', 'perimetre']],
        'matieres'        => ['Matières et affectations', 'bi-box-seam', ['asset_management', 'asset', 'asset_category', 'asset_location', 'asset_status', 'assets_history', 'assignment', 'agent', 'service', 'inventaire', 'supplier', 'bon']],
        'stock'           => ['Stock des consommables', 'bi-box2', ['stock_management', 'stock_item', 'stock_movement']],
        'infrastructures' => ['Infrastructures et projets', 'bi-buildings', ['infrastructure_management', 'infrastructure', 'project', 'report', 'chef_projet', 'intervenant']],
        'maintenance'     => ['Maintenance et tâches', 'bi-tools', ['task_management', 'task', 'task_status', 'task_tag', 'tasks_calendar', 'maintenance_request', 'maintenance_plan']],
        'rapports'        => ['Rapports', 'bi-file-earmark-bar-graph', ['periodic_report']],
        'autres'          => ['Autres', 'bi-three-dots', []],
    ];

    private const MODULE_LABELS = [
        'perimetre'                 => 'Périmètre limité au service',
        'profile_password'          => 'Profil (mot de passe)',
        'user_management'           => 'Menu « Gestion des utilisateurs »',
        'asset_management'          => 'Menu « Gestion des matières »',
        'stock_management'          => 'Menu « Stock consommables »',
        'infrastructure_management' => 'Menu « Infrastructures »',
        'task_management'           => 'Menu « Maintenance »',
        'tasks_calendar'            => 'Calendrier des tâches',
        'user_alert'                => 'Notifications',
        'contact_message'           => 'Messages de contact',
    ];

    /** @return array{0: string, 1: string} [module, action] — action vaut « other » pour un droit particulier */
    public static function parse(string $title): array
    {
        if ($title === 'perimetre_service') {
            return ['perimetre', 'other'];
        }
        if (preg_match('/^(.+)_('.implode('|', array_keys(self::ACTIONS)).')$/', $title, $m)) {
            return [$m[1], $m[2]];
        }
        if (preg_match('/^(.+)_('.implode('|', array_keys(self::SPECIAL)).')$/', $title, $m)) {
            return [$m[1], 'other'];
        }

        return [$title, 'other'];
    }

    public static function sectionOf(string $module): string
    {
        foreach (self::SECTIONS as $key => [, , $modules]) {
            if (in_array($module, $modules, true)) {
                return $key;
            }
        }

        return 'autres';
    }

    public static function moduleLabel(string $module): string
    {
        if (isset(self::MODULE_LABELS[$module])) {
            return self::MODULE_LABELS[$module];
        }

        $key = 'cruds.'.Str::camel($module).'.title';

        return Lang::has($key) ? trans($key) : ucfirst(str_replace('_', ' ', $module));
    }

    /** Libellé court de l'action : « Modifier », « Approuver », « Ne voit que son service »… */
    public static function actionLabel(string $title): string
    {
        if ($title === 'perimetre_service') {
            return 'Ne voit que son service';
        }

        [, $action] = self::parse($title);
        if (isset(self::ACTIONS[$action])) {
            return self::ACTIONS[$action];
        }
        foreach (self::SPECIAL as $suffix => $label) {
            if (str_ends_with($title, '_'.$suffix)) {
                return $label;
            }
        }

        return str_replace('_', ' ', $title);
    }

    /** Libellé complet : « Matières · Modifier ». */
    public static function label(string $title): string
    {
        [$module] = self::parse($title);

        return self::moduleLabel($module).' · '.self::actionLabel($title);
    }

    public static function sectionLabel(string $title): string
    {
        [$module] = self::parse($title);

        return self::SECTIONS[self::sectionOf($module)][0];
    }

    /**
     * Regroupe des permissions par section puis par module.
     *
     * @param  iterable  $permissions  objets ou tableaux avec « id » et « title » (ou id => title)
     * @return array<string, array{label: string, icon: string, modules: array<string, array{label: string, actions: array<string, array>}>}>
     */
    public static function group(iterable $permissions): array
    {
        $grid = [];

        foreach ($permissions as $key => $permission) {
            $item = is_string($permission) ? ['id' => $key, 'title' => $permission] : $permission;
            $title = is_array($item) ? $item['title'] : $item->title;
            [$module, $action] = self::parse($title);
            $grid[self::sectionOf($module)][$module][$action][] = $item;
        }

        $result = [];
        foreach (self::SECTIONS as $sectionKey => [$label, $icon, $order]) {
            if (empty($grid[$sectionKey])) {
                continue;
            }
            $modules = $grid[$sectionKey];
            uksort($modules, function ($a, $b) use ($order) {
                $ia = array_search($a, $order, true);
                $ib = array_search($b, $order, true);

                return ($ia === false ? 99 : $ia) <=> ($ib === false ? 99 : $ib) ?: strcmp($a, $b);
            });

            $result[$sectionKey] = [
                'label'   => $label,
                'icon'    => $icon,
                'modules' => collect($modules)->map(fn ($actions, $module) => [
                    'label'   => self::moduleLabel($module),
                    'actions' => $actions,
                ])->all(),
            ];
        }

        return $result;
    }
}
