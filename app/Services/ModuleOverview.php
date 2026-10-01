<?php

namespace App\Services;

use App\Models\AssetStatus;
use App\Models\AssetsHistory;
use App\Models\Assignment;
use App\Support\Tone;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleOverview
{
    private array $softDeletes = [];

    public const VIEWS = [
        'admin.assets.index'              => 'assets',
        'admin.assetCategories.index'     => 'categories',
        'admin.assetLocations.index'      => 'locations',
        'admin.assetStatuses.index'       => 'statuses',
        'admin.assetsHistories.index'     => 'histories',
        'admin.inventaires.index'         => 'inventaires',
        'admin.suppliers.index'           => 'suppliers',
        'admin.bons.index'                => 'bons',
        'admin.assignments.index'         => 'assignments',
        'admin.agents.index'              => 'agents',
        'admin.services.index'            => 'services',
        'admin.infrastructures.index'     => 'infrastructures',
        'admin.projects.index'            => 'projects',
        'admin.reports.index'             => 'reports',
        'admin.chefProjets.index'         => 'chefProjets',
        'admin.tasks.index'               => 'tasks',
        'admin.taskStatuses.index'        => 'taskStatuses',
        'admin.taskTags.index'            => 'taskTags',
        'admin.maintenanceRequests.index' => 'maintenanceRequests',
        'admin.maintenancePlans.index'    => 'maintenancePlans',
        'admin.intervenants.index'        => 'intervenants',
        'admin.stockItems.index'          => 'stockItems',
        'admin.users.index'               => 'users',
        'admin.roles.index'               => 'roles',
        'admin.permissions.index'         => 'permissions',
        'admin.userAlerts.index'          => 'userAlerts',
        'admin.contactMessages.index'     => 'contactMessages',
    ];

    public function for(string $view): ?array
    {
        $method = self::VIEWS[$view] ?? null;

        return $method ? $this->{$method}() : null;
    }

    /* Matières & inventaire */

    public function assets(): array
    {
        $total = $this->table('assets')->count();
        $available = $this->assetsWithStatus([AssetStatus::AVAILABLE]);
        $broken = $this->assetsWithStatus([AssetStatus::BROKEN, AssetStatus::REPAIR]);
        $assigned = $this->assignedAssets();
        $noCategory = $this->table('assets')->whereNull('category_id')->count();
        $noSerial = $this->table('assets')->where(fn ($q) => $q->whereNull('serial_number')->orWhere('serial_number', ''))->count();

        return [
            'subtitle' => 'Parc de matières du ministère : répartition, état et évolution.',
            'kpis' => [
                $this->kpi('Matières', $total, 'bi-box-seam'),
                $this->kpi('Disponibles', $available, 'bi-check-circle', 'good', $this->share($available, $total)),
                $this->kpi('Affectées', $assigned, 'bi-person-check', null, $this->share($assigned, $total)),
                $this->kpi('En panne / réparation', $broken, 'bi-exclamation-triangle', $broken ? 'critical' : null, $this->share($broken, $total)),
            ],
            'charts' => [
                $this->chart('assets-month', 'Nouvelles matières', 'Enregistrements par mois, 12 derniers mois', $this->perMonth('assets', 'created_at'), 'bar', true),
                $this->chart('assets-category', 'Par catégorie', null, $this->countByRelation('assets', 'category_id', 'asset_categories')),
                $this->chart('assets-status', 'Par statut', null, $this->statusBreakdown()),
                $this->chart('assets-location', 'Par emplacement', null, $this->countByRelation('assets', 'location_id', 'asset_locations')),
            ],
            'alerts' => array_values(array_filter([
                $broken ? $this->alert('critical', 'bi-exclamation-triangle', "$broken matière(s) en panne ou en réparation.") : null,
                $noCategory ? $this->alert('warning', 'bi-tags', "$noCategory matière(s) sans catégorie.") : null,
                $noSerial ? $this->alert('info', 'bi-upc', "$noSerial matière(s) sans numéro de série.") : null,
            ])),
        ];
    }

    public function categories(): array
    {
        $empty = $this->table('asset_categories')
            ->whereNotExists(fn ($q) => $this->scoped($q->from('assets')->whereColumn('assets.category_id', 'asset_categories.id'), 'assets'))
            ->count();

        return [
            'subtitle' => 'Catégories de matières et volume de chacune.',
            'kpis' => [
                $this->kpi('Catégories', $this->table('asset_categories')->count(), 'bi-tags'),
                $this->kpi('Matières classées', $this->table('assets')->whereNotNull('category_id')->count(), 'bi-box-seam'),
                $this->kpi('Catégories vides', $empty, 'bi-inbox', $empty ? 'warning' : null),
            ],
            'charts' => [
                $this->chart('categories-assets', 'Matières par catégorie', null, $this->countByRelation('assets', 'category_id', 'asset_categories', 12, false), 'hbar', true),
            ],
            'alerts' => [],
        ];
    }

    public function locations(): array
    {
        $noLocation = $this->table('assets')->whereNull('location_id')->count();

        return [
            'subtitle' => 'Emplacements de rangement et d\'utilisation des matières.',
            'kpis' => [
                $this->kpi('Emplacements', $this->table('asset_locations')->count(), 'bi-geo-alt'),
                $this->kpi('Matières localisées', $this->table('assets')->whereNotNull('location_id')->count(), 'bi-box-seam'),
                $this->kpi('Sans emplacement', $noLocation, 'bi-question-circle', $noLocation ? 'warning' : null),
            ],
            'charts' => [
                $this->chart('locations-assets', 'Matières par emplacement', null, $this->countByRelation('assets', 'location_id', 'asset_locations', 12), 'hbar', true),
            ],
            'alerts' => [],
        ];
    }

    public function statuses(): array
    {
        $breakdown = $this->statusBreakdown();
        $kpis = [];

        foreach (array_slice($breakdown['labels'], 0, 4) as $i => $label) {
            $tone = Tone::of($label);
            $kpis[] = $this->kpi($label, $breakdown['values'][$i], 'bi-circle-fill', $tone === 'neutral' ? null : $tone);
        }

        return [
            'subtitle' => 'États possibles d\'une matière et répartition actuelle du parc.',
            'kpis' => $kpis,
            'charts' => [$this->chart('statuses-assets', 'Matières par statut', null, $breakdown, 'hbar', true)],
            'alerts' => [],
        ];
    }

    public function histories(): array
    {
        $monthStart = now()->startOfMonth();
        $thisMonth = fn (?string $action = null) => DB::table('assets_histories')
            ->where('created_at', '>=', $monthStart)
            ->when($action, fn ($q) => $q->where('action', $action))
            ->count();

        $byAction = DB::table('assets_histories')
            ->select('action', DB::raw('COUNT(*) as total'))
            ->groupBy('action')
            ->orderByDesc('total')
            ->get();

        return [
            'subtitle' => 'Traçabilité de tous les mouvements du matériel.',
            'kpis' => [
                $this->kpi('Mouvements ce mois', $thisMonth(), 'bi-arrow-left-right'),
                $this->kpi('Affectations', $thisMonth('affectation'), 'bi-person-plus', null, 'ce mois'),
                $this->kpi('Restitutions', $thisMonth('restitution'), 'bi-box-arrow-in-down', 'good', 'ce mois'),
                $this->kpi('Transferts', $thisMonth('transfert'), 'bi-shuffle', null, 'ce mois'),
            ],
            'charts' => [
                $this->chart('histories-month', 'Mouvements par mois', '12 derniers mois', $this->perMonth('assets_histories', 'created_at'), 'bar', true),
                $this->chart('histories-type', 'Par type de mouvement', 'Depuis le début', [
                    'labels' => $byAction->map(fn ($r) => AssetsHistory::ACTIONS[$r->action] ?? 'Autre')->all(),
                    'values' => $byAction->pluck('total')->map(fn ($v) => (int) $v)->all(),
                ], 'hbar', true),
            ],
            'alerts' => [],
        ];
    }

    public function inventaires(): array
    {
        $totalAssets = $this->table('assets')->count();
        $checked = DB::table('asset_inventaire')->whereIn('status', ['vu', 'hors_perimetre'])->distinct()->count('asset_id');
        $never = max($totalAssets - $checked, 0);
        $running = $this->table('inventaires')->where('status', 'en_cours')->count();
        $lastClosed = \App\Models\Inventaire::where('status', 'cloture')->latest('closed_at')->first();
        $lastMissing = $lastClosed
            ? DB::table('asset_inventaire')->where('inventaire_id', $lastClosed->id)->where('status', 'manquant')->count()
            : 0;

        return [
            'subtitle' => 'Campagnes de contrôle physique du parc, par scan des étiquettes.',
            'kpis' => [
                $this->kpi('Campagnes en cours', $running, 'bi-play-circle', $running ? 'info' : null),
                $this->kpi('Matières déjà contrôlées', $checked, 'bi-check2-circle', 'good', $this->share($checked, $totalAssets)),
                $this->kpi('Jamais contrôlées', $never, 'bi-question-circle', $never ? 'warning' : null),
                $this->kpi('Manquantes (dernière campagne)', $lastMissing, 'bi-exclamation-octagon', $lastMissing ? 'critical' : null, $lastClosed?->reference),
            ],
            'charts' => [
                $this->chart('inventaires-checks', 'Contrôles par mois', '12 derniers mois', $this->perMonth('asset_inventaire', 'checked_at'), 'bar', true),
            ],
            'alerts' => array_values(array_filter([
                $lastMissing
                    ? $this->alert('critical', 'bi-exclamation-octagon', "$lastMissing matière(s) manquante(s) lors de la campagne {$lastClosed->reference}.", route('admin.inventaires.show', ['inventaire' => $lastClosed, 'vue' => 'manquantes']))
                    : null,
                $running ? $this->alert('info', 'bi-qr-code-scan', "$running campagne(s) en cours : l'équipe peut scanner les étiquettes.") : null,
            ])),
        ];
    }

    public function suppliers(): array
    {
        $suppliers = $this->table('suppliers')->count();
        $withAssets = DB::table('asset_supplier')->distinct()->count('supplier_id');

        return [
            'subtitle' => 'Fournisseurs et volume de matières fournies.',
            'kpis' => [
                $this->kpi('Fournisseurs', $suppliers, 'bi-truck'),
                $this->kpi('Matières fournies', DB::table('asset_supplier')->distinct()->count('asset_id'), 'bi-box-seam'),
                $this->kpi('Sans matière liée', max($suppliers - $withAssets, 0), 'bi-inbox'),
            ],
            'charts' => [
                $this->chart('suppliers-assets', 'Matières par fournisseur', 'Principaux fournisseurs', $this->countPivot('asset_supplier', 'supplier_id', 'suppliers', 'name', 10), 'hbar', true),
            ],
            'alerts' => [],
        ];
    }

    public function bons(): array
    {
        $today = today()->toDateString();
        $delivered = $this->table('bons')->whereNotNull('date_livraison')->where('date_livraison', '<=', $today)->count();
        $pending = $this->table('bons')->where(fn ($q) => $q->whereNull('date_livraison')->orWhere('date_livraison', '>', $today))->count();

        return [
            'subtitle' => 'Bons de commande et de livraison.',
            'kpis' => [
                $this->kpi('Bons', $this->table('bons')->count(), 'bi-receipt'),
                $this->kpi('Livrés', $delivered, 'bi-check-circle', 'good'),
                $this->kpi('En attente de livraison', $pending, 'bi-hourglass-split', $pending ? 'warning' : null),
                $this->kpi('Émis ce mois', $this->table('bons')->where('date_emission', '>=', now()->startOfMonth()->toDateString())->count(), 'bi-calendar3'),
            ],
            'charts' => [
                $this->chart('bons-month', 'Bons émis par mois', '12 derniers mois', $this->perMonth('bons', 'date_emission'), 'bar', true),
                $this->chart('bons-org', 'Par organisation', null, $this->countByColumn('bons', 'organisation')),
                $this->chart('bons-assets', 'Matières par bon', 'Les plus fournis', $this->countPivot('asset_bon', 'bon_id', 'bons', 'bon')),
            ],
            'alerts' => array_values(array_filter([
                $pending ? $this->alert('warning', 'bi-hourglass-split', "$pending bon(s) en attente de livraison.") : null,
            ])),
        ];
    }

    public function assignments(): array
    {
        $overdue = Assignment::open()->whereNotNull('expected_return_at')->whereDate('expected_return_at', '<', today())->count();

        return [
            'subtitle' => null,
            'kpis' => [
                $this->kpi('Bons en cours', Assignment::open()->count(), 'bi-file-earmark-text'),
                $this->kpi('Matières affectées', $this->assignedAssets(), 'bi-box-seam'),
                $this->kpi('Retours en retard', $overdue, 'bi-clock-history', $overdue ? 'critical' : null),
                $this->kpi('Restitués ce mois', Assignment::where('status', Assignment::STATUS_CLOSED)->where('closed_at', '>=', now()->startOfMonth())->count(), 'bi-box-arrow-in-down', 'good'),
            ],
            'charts' => [
                $this->chart('assignments-month', 'Affectations par mois', '12 derniers mois', $this->perMonth('assignments', 'assigned_at'), 'bar', true),
                $this->chart('assignments-type', 'Par type d\'affectation', null, $this->labelled($this->countByColumn('assignments', 'type'), Assignment::TYPES)),
            ],
            'alerts' => array_values(array_filter([
                $overdue ? $this->alert('critical', 'bi-clock-history', "$overdue bon(s) dont la date de retour est dépassée.", route('admin.assignments.index', ['statut' => 'en_retard'])) : null,
            ])),
        ];
    }

    /* Personnel */

    public function agents(): array
    {
        $agents = $this->table('agents')->count();
        $holders = $this->table('assets')->whereNotNull('agent_id')->distinct()->count('agent_id');
        $overdue = Assignment::open()
            ->whereNotNull('agent_id')
            ->whereNotNull('expected_return_at')
            ->whereDate('expected_return_at', '<', today())
            ->distinct()
            ->count('agent_id');

        $top = $this->scoped(DB::table('assets')->join('agents', 'agents.id', '=', 'assets.agent_id'), 'assets')
            ->whereNull('agents.deleted_at')
            ->select(DB::raw("TRIM(CONCAT(COALESCE(agents.prenom, ''), ' ', COALESCE(agents.nom, ''))) as label"), DB::raw('COUNT(*) as total'))
            ->groupBy('agents.id', 'agents.prenom', 'agents.nom')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'subtitle' => 'Personnel bénéficiaire du matériel et détention actuelle.',
            'kpis' => [
                $this->kpi('Agents', $agents, 'bi-people'),
                $this->kpi('Détiennent du matériel', $holders, 'bi-person-check', null, $this->share($holders, $agents)),
                $this->kpi('Sans matériel', max($agents - $holders, 0), 'bi-person'),
                $this->kpi('Avec un retour en retard', $overdue, 'bi-clock-history', $overdue ? 'critical' : null),
            ],
            'charts' => [
                $this->chart('agents-service', 'Agents par service', null, $this->countByRelation('agents', 'service_id', 'services')),
                $this->chart('agents-top', 'Principaux détenteurs', 'Nombre de matières détenues', $this->rows($top)),
            ],
            'alerts' => [],
        ];
    }

    public function services(): array
    {
        $withoutAgent = $this->table('services')
            ->whereNotExists(fn ($q) => $this->scoped($q->from('agents')->whereColumn('agents.service_id', 'services.id'), 'agents'))
            ->count();

        return [
            'subtitle' => 'Services du ministère, effectifs et matériel affecté.',
            'kpis' => [
                $this->kpi('Services', $this->table('services')->count(), 'bi-diagram-3'),
                $this->kpi('Agents rattachés', $this->table('agents')->whereNotNull('service_id')->count(), 'bi-people'),
                $this->kpi('Matières affectées', $this->table('assets')->whereNotNull('service_id')->count(), 'bi-box-seam'),
                $this->kpi('Services sans agent', $withoutAgent, 'bi-inbox', $withoutAgent ? 'warning' : null),
            ],
            'charts' => [
                $this->chart('services-assets', 'Matériel par service', null, $this->countByRelation('assets', 'service_id', 'services', 8, false)),
                $this->chart('services-agents', 'Agents par service', null, $this->countByRelation('agents', 'service_id', 'services', 8, false)),
            ],
            'alerts' => [],
        ];
    }

    /* Infrastructures & projets */

    public function infrastructures(): array
    {
        $all = \App\Models\Infrastructure::all();
        $valued = $all->filter->canDepreciate();
        $degraded = $all->whereIn('condition', ['degrade', 'critique'])->count();
        $notEvaluated = $all->whereNull('condition')->count();
        $ending = $valued->filter(fn ($i) => $i->depreciation_end->between(today(), today()->addYear()))->count();

        return [
            'subtitle' => 'Structures, bâtiments et blocs : situation, état et valeur.',
            'kpis' => [
                $this->kpi('Infrastructures', $all->count(), 'bi-building'),
                $this->kpi('État dégradé ou critique', $degraded, 'bi-exclamation-triangle', $degraded ? 'critical' : null),
                $this->kpi('Valeur nette', (int) round($valued->sum('net_book_value') / 1000000), 'bi-cash-coin', null, 'millions FCFA · '.(int) round($valued->sum('acquisition_value') / 1000000).' M d\'origine'),
                $this->kpi('En travaux', $all->whereIn('status', ['en_construction', 'en_rehabilitation'])->count(), 'bi-cone-striped', 'info'),
            ],
            'charts' => [
                $this->chart('infra-status', 'Par situation', null, $this->fromCollection($all->groupBy('status_label')->map->count())),
                $this->chart('infra-condition', 'Par état constaté', null, $this->fromCollection($all->groupBy(fn ($i) => $i->condition_label ?? 'Non évalué')->map->count())),
                $this->chart('infra-location', 'Par localité', null, $this->countByColumn('infrastructures', 'location', 10), 'hbar', true),
            ],
            'alerts' => array_values(array_filter([
                $degraded ? $this->alert('critical', 'bi-exclamation-triangle', "$degraded infrastructure(s) en état dégradé ou critique.") : null,
                $notEvaluated ? $this->alert('warning', 'bi-clipboard2-pulse', "$notEvaluated infrastructure(s) sans état constaté : planifiez une visite.") : null,
                $ending ? $this->alert('info', 'bi-calculator', "$ending infrastructure(s) arrivent en fin d'amortissement dans l'année.") : null,
            ])),
        ];
    }

    public function projects(): array
    {
        $active = \App\Models\Project::active()->get();
        $late = $active->filter->is_late->count();
        $behind = $active->filter(fn ($p) => $p->expected_progress !== null && $p->progress < $p->expected_progress - 15)->count();
        $done = \App\Models\Project::where('status', 'termine')->count();
        $total = \App\Models\Project::count();

        return [
            'subtitle' => 'Construction et réhabilitation des structures de formation.',
            'kpis' => [
                $this->kpi('Projets actifs', $active->count(), 'bi-kanban', 'info'),
                $this->kpi('Avancement moyen', (int) round($active->avg('progress') ?? 0), 'bi-speedometer2', null, '% sur les projets actifs'),
                $this->kpi('Terminés', $done, 'bi-check-circle', 'good', $this->share($done, $total)),
                $this->kpi('En retard', $late, 'bi-exclamation-octagon', $late ? 'critical' : null),
            ],
            'charts' => [
                $this->chart('projects-progress', 'Avancement des projets actifs', '%', [
                    'labels' => $active->sortByDesc('progress')->take(10)->pluck('name')->map(fn ($n) => \Illuminate\Support\Str::limit($n, 40))->values()->all(),
                    'values' => $active->sortByDesc('progress')->take(10)->pluck('progress')->values()->all(),
                ], 'hbar', true),
                $this->chart('projects-status', 'Par statut', null, $this->labelled($this->countByColumn('projects', 'status'), \App\Models\Project::STATUSES)),
                $this->chart('projects-type', 'Par nature', null, $this->labelled($this->countByColumn('projects', 'type'), \App\Models\Project::TYPES)),
            ],
            'alerts' => array_values(array_filter([
                $late ? $this->alert('critical', 'bi-exclamation-octagon', "$late projet(s) ont dépassé leur date de fin.", route('admin.projects.index', ['statut' => 'en_retard'])) : null,
                $behind ? $this->alert('warning', 'bi-speedometer2', "$behind projet(s) avancent nettement moins vite que prévu.") : null,
            ])),
        ];
    }

    public function intervenants(): array
    {
        return [
            'subtitle' => 'Entreprises, bureaux d\'études et contrôleurs mobilisés sur les projets.',
            'kpis' => [
                $this->kpi('Intervenants', $this->table('intervenants')->count(), 'bi-people'),
                $this->kpi('Sur un projet actif', DB::table('intervenant_project')->join('projects', 'projects.id', '=', 'intervenant_project.project_id')
                    ->whereIn('projects.status', ['planifie', 'en_cours', 'suspendu'])->whereNull('projects.deleted_at')->distinct()->count('intervenant_id'), 'bi-kanban', 'info'),
            ],
            'charts' => [
                $this->chart('intervenants-role', 'Par type', null, $this->labelled($this->countByColumn('intervenants', 'role'), \App\Models\Intervenant::ROLES)),
            ],
            'alerts' => [],
        ];
    }


    public function reports(): array
    {
        return [
            'subtitle' => 'Rapports de suivi produits pour la hiérarchie.',
            'kpis' => [
                $this->kpi('Rapports', $this->table('reports')->count(), 'bi-file-earmark-bar-graph'),
                $this->kpi('Ce mois', $this->table('reports')->where('report_date', '>=', now()->startOfMonth()->toDateString())->count(), 'bi-calendar3'),
                $this->kpi('Cette année', $this->table('reports')->whereYear('report_date', now()->year)->count(), 'bi-calendar-range'),
            ],
            'charts' => [
                $this->chart('reports-month', 'Rapports par mois', '12 derniers mois', $this->perMonth('reports', 'report_date'), 'bar'),
                $this->chart('reports-project', 'Projets les plus documentés', null, $this->countPivot('project_report', 'project_id', 'projects', 'name')),
            ],
            'alerts' => [],
        ];
    }

    public function chefProjets(): array
    {
        $chefs = $this->table('chef_projets')->count();
        $active = DB::table('chef_projet_project')->distinct()->count('chef_projet_id');

        $top = $this->scoped(DB::table('chef_projet_project')->join('chef_projets', 'chef_projets.id', '=', 'chef_projet_project.chef_projet_id'), 'chef_projets')
            ->select(DB::raw("TRIM(CONCAT(COALESCE(chef_projets.prenom, ''), ' ', COALESCE(chef_projets.nom, ''))) as label"), DB::raw('COUNT(*) as total'))
            ->groupBy('chef_projets.id', 'chef_projets.prenom', 'chef_projets.nom')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'subtitle' => 'Responsables du pilotage des projets.',
            'kpis' => [
                $this->kpi('Chefs de projet', $chefs, 'bi-person-badge'),
                $this->kpi('Avec projet', $active, 'bi-kanban'),
                $this->kpi('Sans projet', max($chefs - $active, 0), 'bi-person'),
            ],
            'charts' => [$this->chart('chefs-projects', 'Projets par chef de projet', null, $this->rows($top), 'hbar', true)],
            'alerts' => [],
        ];
    }

    /* Maintenance & tâches */

    public function tasks(): array
    {
        $today = today()->toDateString();
        $closedId = DB::table('task_statuses')->whereRaw('LOWER(name) in (?, ?)', ['closed', 'clôturé'])->value('id');
        $open = fn () => $this->table('tasks')->when($closedId, fn ($q) => $q->where(fn ($q) => $q->whereNull('status_id')->orWhere('status_id', '!=', $closedId)));

        $total = $this->table('tasks')->count();
        $late = $open()->whereNotNull('due_date')->where('due_date', '<', $today)->count();
        $week = $open()->whereBetween('due_date', [$today, today()->addDays(7)->toDateString()])->count();
        $closed = $closedId ? $this->table('tasks')->where('status_id', $closedId)->count() : 0;

        $byUser = $this->scoped(DB::table('tasks')->join('users', 'users.id', '=', 'tasks.assigned_to_id'), 'tasks')
            ->select('users.name as label', DB::raw('COUNT(*) as total'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'subtitle' => 'Interventions planifiées et leur avancement.',
            'kpis' => [
                $this->kpi('Tâches', $total, 'bi-list-check'),
                $this->kpi('Clôturées', $closed, 'bi-check-circle', 'good', $this->share($closed, $total)),
                $this->kpi('Échéance sous 7 jours', $week, 'bi-calendar-event', $week ? 'warning' : null),
                $this->kpi('En retard', $late, 'bi-exclamation-octagon', $late ? 'critical' : null),
            ],
            'charts' => [
                $this->chart('tasks-month', 'Échéances par mois', '12 derniers mois', $this->perMonth('tasks', 'due_date'), 'bar', true),
                $this->chart('tasks-status', 'Par statut', null, $this->statusLabels($this->countByRelation('tasks', 'status_id', 'task_statuses'))),
                $this->chart('tasks-user', 'Par responsable', null, $this->rows($byUser)),
            ],
            'alerts' => array_values(array_filter([
                $late ? $this->alert('critical', 'bi-exclamation-octagon', "$late tâche(s) en retard.") : null,
                $week ? $this->alert('warning', 'bi-calendar-event', "$week tâche(s) arrivent à échéance cette semaine.") : null,
            ])),
        ];
    }

    public function taskStatuses(): array
    {
        return [
            'subtitle' => 'États d\'avancement des tâches.',
            'kpis' => [
                $this->kpi('Statuts', $this->table('task_statuses')->count(), 'bi-flag'),
                $this->kpi('Tâches', $this->table('tasks')->count(), 'bi-list-check'),
            ],
            'charts' => [
                $this->chart('taskstatus-tasks', 'Tâches par statut', null, $this->statusLabels($this->countByRelation('tasks', 'status_id', 'task_statuses', 12, false)), 'hbar', true),
            ],
            'alerts' => [],
        ];
    }

    public function taskTags(): array
    {
        return [
            'subtitle' => 'Étiquettes de classement des tâches.',
            'kpis' => [
                $this->kpi('Étiquettes', $this->table('task_tags')->count(), 'bi-tag'),
                $this->kpi('Tâches étiquetées', DB::table('task_task_tag')->distinct()->count('task_id'), 'bi-list-check'),
            ],
            'charts' => [
                $this->chart('tags-tasks', 'Tâches par étiquette', null, $this->countPivot('task_task_tag', 'task_tag_id', 'task_tags', 'name', 12), 'hbar', true),
            ],
            'alerts' => [],
        ];
    }

    public function maintenanceRequests(): array
    {
        $open = \App\Models\MaintenanceRequest::open()->count();
        $toValidate = \App\Models\MaintenanceRequest::whereIn('status', \App\Models\MaintenanceRequest::PENDING_STATUSES)->count();
        $urgent = \App\Models\MaintenanceRequest::open()->where('priority', 'urgente')->count();
        $doneMonth = \App\Models\MaintenanceRequest::where('status', 'terminee')->where('completed_at', '>=', now()->startOfMonth())->get();
        $lead = \App\Models\MaintenanceRequest::where('status', 'terminee')->where('completed_at', '>=', now()->subMonths(6))->get()->avg(fn ($r) => $r->lead_time ?? 0);
        $plansLate = \App\Models\MaintenancePlan::where('active', true)->whereDate('next_due_at', '<', today())->count();

        return [
            'subtitle' => null,
            'kpis' => [
                $this->kpi('En attente de décision', $toValidate, 'bi-hourglass-split', $toValidate ? 'warning' : null),
                $this->kpi('En cours de traitement', $open, 'bi-tools', 'info', $urgent ? "$urgent urgente(s)" : null),
                $this->kpi('Terminées ce mois', $doneMonth->count(), 'bi-check-circle', 'good'),
                $this->kpi('Délai moyen', (int) round($lead ?? 0), 'bi-stopwatch', null, 'jours (6 derniers mois)'),
            ],
            'charts' => [
                $this->chart('maint-month', 'Demandes par mois', '12 derniers mois', $this->perMonth('maintenance_requests', 'created_at'), 'bar', true),
                $this->chart('maint-status', 'Par statut', null, $this->labelled($this->countByColumn('maintenance_requests', 'status'), \App\Models\MaintenanceRequest::STATUSES)),
                $this->chart('maint-kind', 'Corrective / préventive', null, $this->labelled($this->countByColumn('maintenance_requests', 'kind'), ['corrective' => 'Corrective', 'preventive' => 'Préventive'])),
            ],
            'alerts' => array_values(array_filter([
                $toValidate ? $this->alert('warning', 'bi-hourglass-split', "$toValidate demande(s) attendent une décision (avis technique ou Directeur).", route('admin.maintenance-requests.index', ['statut' => 'a_valider'])) : null,
                $urgent ? $this->alert('critical', 'bi-exclamation-octagon', "$urgent demande(s) urgente(s) en cours.") : null,
                $plansLate ? $this->alert('critical', 'bi-arrow-repeat', "$plansLate entretien(s) préventif(s) en retard.", route('admin.maintenance-plans.index')) : null,
            ])),
        ];
    }

    public function maintenancePlans(): array
    {
        $plans = \App\Models\MaintenancePlan::where('active', true)->get();
        $late = $plans->filter->is_overdue->count();
        $month = $plans->filter(fn ($p) => $p->next_due_at->between(today(), today()->addDays(30)))->count();

        return [
            'subtitle' => null,
            'kpis' => [
                $this->kpi('Plans actifs', $plans->count(), 'bi-arrow-repeat'),
                $this->kpi('Échéances sous 30 jours', $month, 'bi-calendar-event', $month ? 'warning' : null),
                $this->kpi('En retard', $late, 'bi-exclamation-octagon', $late ? 'critical' : null),
                $this->kpi('Interventions préventives', $this->table('maintenance_requests')->where('kind', 'preventive')->where('status', 'terminee')->count(), 'bi-check-circle', 'good', 'réalisées'),
            ],
            'charts' => [],
            'alerts' => [],
        ];
    }

    /* Stock des consommables */

    public function stockItems(): array
    {
        $items = \App\Models\StockItem::all();
        $low = $items->filter(fn ($i) => $i->level === 'bas')->count();
        $out = $items->filter(fn ($i) => $i->level === 'rupture')->count();
        $monthStart = now()->startOfMonth()->toDateString();

        $outByCategory = DB::table('stock_movements')
            ->join('stock_items', 'stock_items.id', '=', 'stock_movements.stock_item_id')
            ->where('stock_movements.type', 'sortie')
            ->where('stock_movements.moved_at', '>=', now()->subMonths(6)->toDateString())
            ->select(DB::raw("COALESCE(stock_items.category, 'autre') as label"), DB::raw('COUNT(*) as total'))
            ->groupBy('stock_items.category')
            ->orderByDesc('total')
            ->get();

        return [
            'subtitle' => null,
            'kpis' => [
                $this->kpi('Articles', $items->count(), 'bi-boxes'),
                $this->kpi('Sous le seuil', $low, 'bi-exclamation-triangle', $low ? 'warning' : null),
                $this->kpi('En rupture', $out, 'bi-x-octagon', $out ? 'critical' : null),
                $this->kpi('Sorties ce mois', $this->table('stock_movements')->where('type', 'sortie')->where('moved_at', '>=', $monthStart)->count(), 'bi-box-arrow-up', null,
                    \App\Support\Fmt::money($items->sum(fn ($i) => $i->stock_value ?? 0)).' en stock'),
            ],
            'charts' => [
                $this->chart('stock-moves', 'Mouvements par mois', '12 derniers mois', $this->perMonth('stock_movements', 'moved_at'), 'bar', true),
                $this->chart('stock-family', 'Consommation par famille', '6 derniers mois', $this->labelled($this->rows($outByCategory), \App\Models\StockItem::CATEGORIES)),
            ],
            'alerts' => array_values(array_filter([
                $out ? $this->alert('critical', 'bi-x-octagon', "$out article(s) en rupture de stock.", route('admin.stock-items.index', ['niveau' => 'rupture'])) : null,
                $low ? $this->alert('warning', 'bi-exclamation-triangle', "$low article(s) sous le seuil d'alerte : prévoir un réapprovisionnement.", route('admin.stock-items.index', ['niveau' => 'bas'])) : null,
            ])),
        ];
    }

    /* Administration */

    public function users(): array
    {
        $total = $this->table('users')->count();
        $pending = $this->table('users')->where(fn ($q) => $q->whereNull('approved')->orWhere('approved', false))->count();
        $unverified = $this->table('users')->whereNull('email_verified_at')->count();

        $byRole = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->when($this->hasSoftDeletes('users'), fn ($q) => $q->whereNull('users.deleted_at'))
            ->whereNull('roles.deleted_at')
            ->select('roles.title as label', DB::raw('COUNT(*) as total'))
            ->groupBy('roles.id', 'roles.title')
            ->orderByDesc('total')
            ->get();

        return [
            'subtitle' => 'Comptes ayant accès à l\'espace de gestion.',
            'kpis' => [
                $this->kpi('Utilisateurs', $total, 'bi-people'),
                $this->kpi('Comptes validés', $total - $pending, 'bi-person-check', 'good'),
                $this->kpi('En attente de validation', $pending, 'bi-hourglass-split', $pending ? 'warning' : null),
                $this->kpi('E-mail non vérifié', $unverified, 'bi-envelope-exclamation'),
            ],
            'charts' => [
                $this->chart('users-role', 'Utilisateurs par rôle', null, $this->rows($byRole)),
                $this->chart('users-month', 'Nouveaux comptes', '12 derniers mois', $this->perMonth('users', 'created_at'), 'bar'),
            ],
            'alerts' => array_values(array_filter([
                $pending ? $this->alert('warning', 'bi-hourglass-split', "$pending compte(s) attendent une validation avant de pouvoir se connecter.") : null,
            ])),
        ];
    }

    public function roles(): array
    {
        $byRole = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->whereNull('roles.deleted_at')
            ->whereNull('users.deleted_at')
            ->select('roles.title as label', DB::raw('COUNT(*) as total'))
            ->groupBy('roles.id', 'roles.title')
            ->orderByDesc('total')
            ->get();

        $permsByRole = DB::table('permission_role')
            ->join('roles', 'roles.id', '=', 'permission_role.role_id')
            ->whereNull('roles.deleted_at')
            ->select('roles.title as label', DB::raw('COUNT(*) as total'))
            ->groupBy('roles.id', 'roles.title')
            ->orderByDesc('total')
            ->get();

        return [
            'subtitle' => 'Profils d\'accès et droits associés.',
            'kpis' => [
                $this->kpi('Rôles', $this->table('roles')->count(), 'bi-person-badge'),
                $this->kpi('Permissions', $this->table('permissions')->count(), 'bi-shield-lock'),
                $this->kpi('Utilisateurs avec rôle', DB::table('role_user')->join('roles', 'roles.id', '=', 'role_user.role_id')->whereNull('roles.deleted_at')->distinct()->count('role_user.user_id'), 'bi-people'),
            ],
            'charts' => [
                $this->chart('roles-users', 'Utilisateurs par rôle', null, $this->rows($byRole)),
                $this->chart('roles-perms', 'Permissions par rôle', null, $this->rows($permsByRole)),
            ],
            'alerts' => [],
        ];
    }

    public function permissions(): array
    {
        $titles = $this->table('permissions')->pluck('title');
        $catalog = \App\Support\PermissionCatalog::class;

        $bySection = $titles->countBy(fn ($t) => $catalog::sectionLabel((string) $t));
        $sections = collect($catalog::SECTIONS)->map(fn ($s) => $s[0])
            ->filter(fn ($label) => $bySection->has($label));
        $modules = $titles->map(fn ($t) => $catalog::parse((string) $t)[0])->unique()->count();
        $unused = $this->table('permissions')->whereNotExists(fn ($q) => $q->from('permission_role')
            ->join('roles', 'roles.id', '=', 'permission_role.role_id')->whereNull('roles.deleted_at')
            ->whereColumn('permission_role.permission_id', 'permissions.id'))->count();

        return [
            'subtitle' => 'Droits élémentaires attribués aux rôles, classés par domaine.',
            'kpis' => [
                $this->kpi('Droits', $titles->count(), 'bi-shield-lock'),
                $this->kpi('Domaines', $sections->count(), 'bi-grid', null, $modules.' modules'),
                $this->kpi('Non attribués', $unused, 'bi-slash-circle', $unused ? 'warning' : null, $unused ? 'aucun rôle ne les possède' : null),
            ],
            'charts' => [
                $this->chart('perms-section', 'Droits par domaine', 'Nombre de droits disponibles dans chaque partie de l\'application', [
                    'labels' => $sections->values()->all(),
                    'values' => $sections->map(fn ($label) => (int) $bySection[$label])->values()->all(),
                ], 'hbar', true),
            ],
            'alerts' => [],
        ];
    }

    public function userAlerts(): array
    {
        $unread = DB::table('user_user_alert')->where('read', false)->count();

        return [
            'subtitle' => 'Messages internes envoyés aux utilisateurs.',
            'kpis' => [
                $this->kpi('Alertes', $this->table('user_alerts')->count(), 'bi-bell'),
                $this->kpi('Non lues', $unread, 'bi-envelope', $unread ? 'warning' : null),
                $this->kpi('Envoyées ce mois', $this->table('user_alerts')->where('created_at', '>=', now()->startOfMonth())->count(), 'bi-calendar3'),
            ],
            'charts' => [
                $this->chart('alerts-month', 'Alertes envoyées', '12 derniers mois', $this->perMonth('user_alerts', 'created_at'), 'bar', true),
            ],
            'alerts' => [],
        ];
    }

    public function contactMessages(): array
    {
        if (! Schema::hasTable('contact_messages')) {
            return ['subtitle' => null, 'kpis' => [], 'charts' => [], 'alerts' => []];
        }

        $unread = $this->table('contact_messages')->whereNull('read_at')->count();
        $subjects = \App\Models\ContactMessage::SUBJECTS;
        $bySubject = $this->table('contact_messages')->select('subject', DB::raw('COUNT(*) as total'))->groupBy('subject')->orderByDesc('total')->get();

        return [
            'subtitle' => 'Messages reçus depuis la page Contact du site public.',
            'kpis' => [
                $this->kpi('Messages', $this->table('contact_messages')->count(), 'bi-envelope'),
                $this->kpi('Non lus', $unread, 'bi-envelope-exclamation', $unread ? 'warning' : null),
                $this->kpi('Reçus ce mois', $this->table('contact_messages')->where('created_at', '>=', now()->startOfMonth())->count(), 'bi-calendar3'),
            ],
            'charts' => [
                $this->chart('contact-month', 'Messages par mois', '12 derniers mois', $this->perMonth('contact_messages', 'created_at'), 'bar'),
                $this->chart('contact-subject', 'Par objet', null, [
                    'labels' => $bySubject->map(fn ($r) => $subjects[$r->subject] ?? $r->subject)->all(),
                    'values' => $bySubject->pluck('total')->map(fn ($v) => (int) $v)->all(),
                ]),
            ],
            'alerts' => array_values(array_filter([
                $unread ? $this->alert('warning', 'bi-envelope-exclamation', "$unread message(s) en attente de lecture.") : null,
            ])),
        ];
    }

    /* Outils */

    private function table(string $table): Builder
    {
        $query = $this->scoped(DB::table($table), $table);

        // Comptes limités à leur service : les indicateurs ne portent que sur ce service.
        if (($serviceId = \App\Support\Perimetre::serviceId()) !== null) {
            if ($table === 'services') {
                $query->where('services.id', $serviceId);
            } elseif ($table === 'maintenance_requests') {
                $query->where(fn ($q) => $q->where('requested_by_id', auth()->id())
                    ->orWhereIn('asset_id', DB::table('assets')->where('service_id', $serviceId)->select('id')));
            } elseif ($this->hasColumn($table, 'service_id')) {
                $query->where($table.'.service_id', $serviceId);
            }
        }

        return $query;
    }

    private array $columns = [];

    private function hasColumn(string $table, string $column): bool
    {
        return $this->columns[$table.'.'.$column] ??= Schema::hasColumn($table, $column);
    }

    private function hasSoftDeletes(string $table): bool
    {
        return $this->softDeletes[$table] ??= Schema::hasColumn($table, 'deleted_at');
    }

    private function scoped(Builder $query, string $table): Builder
    {
        return $this->hasSoftDeletes($table) ? $query->whereNull($table.'.deleted_at') : $query;
    }

    private function assignedAssets(): int
    {
        return $this->table('assets')->where(fn ($q) => $q->whereNotNull('agent_id')->orWhereNotNull('service_id'))->count();
    }

    private function notDone(Builder $query, string $column = 'status'): Builder
    {
        return $query->where(function ($q) use ($column) {
            $q->whereNull($column)->orWhere(function ($q) use ($column) {
                foreach (Tone::DONE_KEYWORDS as $keyword) {
                    $q->whereRaw("LOWER($column) NOT LIKE ?", ['%'.$keyword.'%']);
                }
            });
        });
    }

    private function doneCount(string $table, string $column = 'status'): int
    {
        return $this->table($table)->where(function ($q) use ($column) {
            foreach (Tone::DONE_KEYWORDS as $keyword) {
                $q->orWhereRaw("LOWER($column) LIKE ?", ['%'.$keyword.'%']);
            }
        })->count();
    }

    private function assetsWithStatus(array $names): int
    {
        $ids = array_values(array_unique(array_merge(...array_map(fn ($n) => AssetStatus::idsFor($n), $names))));

        return $ids ? $this->table('assets')->whereIn('status_id', $ids)->count() : 0;
    }

    private function statusBreakdown(): array
    {
        return $this->statusLabels($this->countByRelation('assets', 'status_id', 'asset_statuses', 12, false));
    }

    private function statusLabels(array $data): array
    {
        $tasks = ['open' => 'Ouvert', 'in progress' => 'En cours', 'closed' => 'Clôturé'];

        $data['labels'] = array_map(
            fn ($l) => $tasks[mb_strtolower($l)] ?? AssetStatus::label($l),
            $data['labels']
        );

        return $data;
    }

    private function countByRelation(string $table, string $foreignKey, string $related, int $limit = 8, bool $withEmpty = true): array
    {
        $rows = $this->scoped(DB::table($table), $table)
            ->leftJoin($related, "$related.id", '=', "$table.$foreignKey")
            ->when(! $withEmpty, fn ($q) => $q->whereNotNull("$table.$foreignKey"))
            ->select(DB::raw("COALESCE($related.name, 'Non renseigné') as label"), DB::raw('COUNT(*) as total'))
            ->groupBy("$related.name")
            ->orderByDesc('total')
            ->get();

        return $this->topN($rows, $limit);
    }

    private function countByColumn(string $table, string $column, int $limit = 8): array
    {
        $rows = $this->table($table)
            ->select(DB::raw("COALESCE(NULLIF(TRIM($column), ''), 'Non renseigné') as label"), DB::raw('COUNT(*) as total'))
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();

        return $this->topN($rows, $limit);
    }

    private function countPivot(string $pivot, string $foreignKey, string $related, string $labelColumn, int $limit = 8): array
    {
        $rows = $this->scoped(DB::table($pivot)->join($related, "$related.id", '=', "$pivot.$foreignKey"), $related)
            ->select(DB::raw("COALESCE(NULLIF($related.$labelColumn, ''), CONCAT('#', $related.id)) as label"), DB::raw('COUNT(*) as total'))
            ->groupBy("$related.id", "$related.$labelColumn")
            ->orderByDesc('total')
            ->get();

        return $this->topN($rows, $limit);
    }

    private function perMonth(string $table, string $column, int $months = 12): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        $counts = $this->table($table)
            ->where($column, '>=', $start->toDateString())
            ->select(DB::raw("DATE_FORMAT($column, '%Y-%m') as ym"), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw("DATE_FORMAT($column, '%Y-%m')"))
            ->pluck('total', 'ym');

        $labels = $values = [];
        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $labels[] = ucfirst($month->locale('fr')->translatedFormat('M y'));
            $values[] = (int) ($counts[$month->format('Y-m')] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function perYear(string $table, string $column, int $years = 6): array
    {
        $from = now()->year - $years + 1;

        $counts = $this->table($table)
            ->whereNotNull($column)
            ->whereYear($column, '>=', $from)
            ->select(DB::raw("YEAR($column) as y"), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw("YEAR($column)"))
            ->pluck('total', 'y');

        $labels = $values = [];
        for ($y = $from; $y <= now()->year; $y++) {
            $labels[] = (string) $y;
            $values[] = (int) ($counts[$y] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function topN($rows, int $limit): array
    {
        $top = $rows->take($limit);
        $others = $rows->slice($limit)->sum('total');

        $labels = $top->pluck('label')->map(fn ($l) => (string) $l)->all();
        $values = $top->pluck('total')->map(fn ($v) => (int) $v)->all();

        if ($others > 0) {
            $labels[] = 'Autres';
            $values[] = (int) $others;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function rows($rows): array
    {
        return [
            'labels' => $rows->pluck('label')->map(fn ($l) => trim((string) $l) ?: '—')->all(),
            'values' => $rows->pluck('total')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    private function fromCollection($counts): array
    {
        $sorted = collect($counts)->sortDesc();

        return [
            'labels' => $sorted->keys()->map(fn ($k) => (string) $k)->values()->all(),
            'values' => $sorted->values()->map(fn ($v) => (int) $v)->all(),
        ];
    }

    private function labelled(array $data, array $labels): array
    {
        $data['labels'] = array_map(fn ($l) => $labels[$l] ?? $l, $data['labels']);

        return $data;
    }

    private function share(int $part, int $total): ?string
    {
        return $total > 0 ? round($part * 100 / $total).' % du total' : null;
    }

    private function kpi(string $label, int $value, string $icon, ?string $tone = null, ?string $hint = null): array
    {
        return compact('label', 'value', 'icon', 'tone', 'hint');
    }

    private function chart(string $id, string $title, ?string $subtitle, array $data, string $type = 'hbar', bool $wide = false): array
    {
        return compact('id', 'title', 'subtitle', 'type', 'wide') + $data;
    }

    private function alert(string $tone, string $icon, string $text, ?string $url = null): array
    {
        return compact('tone', 'icon', 'text', 'url');
    }
}
