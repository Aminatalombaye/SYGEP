<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetStatus;
use App\Models\Assignment;
use App\Models\Infrastructure;
use App\Models\Inventaire;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceRequest;
use App\Models\Project;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Données des rapports périodiques destinés à la hiérarchie
 * (patrimoine, stock, maintenance, projets, infrastructures).
 */
class PeriodicReport
{
    public const SECTIONS = [
        'patrimoine'      => 'Matières et affectations',
        'stock'           => 'Stock des consommables',
        'maintenance'     => 'Maintenance',
        'projets'         => 'Projets',
        'infrastructures' => 'Infrastructures et amortissement',
    ];

    public const PERIODS = [
        'mois'                => 'Mois en cours',
        'mois_precedent'      => 'Mois précédent',
        'trimestre'           => 'Trimestre en cours',
        'trimestre_precedent' => 'Trimestre précédent',
        'semestre'            => 'Semestre en cours',
        'annee'               => 'Année en cours',
        'annee_precedente'    => 'Année précédente',
        'personnalisee'       => 'Période personnalisée',
    ];

    /** @return array{0: Carbon, 1: Carbon, 2: string} */
    public static function range(string $period, ?string $from = null, ?string $to = null): array
    {
        $now = now();

        [$start, $end] = match ($period) {
            'mois_precedent'      => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'trimestre'           => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            'trimestre_precedent' => [$now->copy()->subQuarterNoOverflow()->startOfQuarter(), $now->copy()->subQuarterNoOverflow()->endOfQuarter()],
            'semestre'            => $now->month <= 6
                ? [$now->copy()->startOfYear(), $now->copy()->month(6)->endOfMonth()]
                : [$now->copy()->month(7)->startOfMonth(), $now->copy()->endOfYear()],
            'annee'               => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'annee_precedente'    => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            'personnalisee'       => [
                $from ? Carbon::parse($from)->startOfDay() : $now->copy()->startOfMonth(),
                $to ? Carbon::parse($to)->endOfDay() : $now->copy()->endOfDay(),
            ],
            default               => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };

        if ($end->lt($start)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        $label = $start->isSameDay($start->copy()->startOfMonth()) && $end->isSameDay($start->copy()->endOfMonth())
            ? ucfirst($start->locale('fr')->translatedFormat('F Y'))
            : 'Du '.$start->format('d/m/Y').' au '.$end->format('d/m/Y');

        return [$start, $end, $label];
    }

    public function build(Carbon $from, Carbon $to, array $sections): array
    {
        $data = [];

        foreach (array_keys(self::SECTIONS) as $section) {
            if (in_array($section, $sections, true)) {
                $data[$section] = $this->{$section}($from, $to);
            }
        }

        return $data;
    }

    private function patrimoine(Carbon $from, Carbon $to): array
    {
        $between = [$from, $to];

        $byStatus = Asset::query()
            ->leftJoin('asset_statuses', 'asset_statuses.id', '=', 'assets.status_id')
            ->select(DB::raw("COALESCE(asset_statuses.name, 'Non renseigné') as label"), DB::raw('COUNT(*) as total'))
            ->groupBy('asset_statuses.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['label' => AssetStatus::label($r->label), 'total' => (int) $r->total]);

        $byCategory = Asset::query()
            ->leftJoin('asset_categories', 'asset_categories.id', '=', 'assets.category_id')
            ->select(DB::raw("COALESCE(asset_categories.name, 'Sans catégorie') as label"), DB::raw('COUNT(*) as total'))
            ->groupBy('asset_categories.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $brokenIds = array_merge(AssetStatus::idsFor(AssetStatus::BROKEN), AssetStatus::idsFor(AssetStatus::REPAIR));

        return [
            'total'        => Asset::count(),
            'new'          => Asset::whereBetween('created_at', $between)->count(),
            'assigned'     => Asset::where(fn ($q) => $q->whereNotNull('agent_id')->orWhereNotNull('service_id'))->count(),
            'broken'       => $brokenIds ? Asset::whereIn('status_id', $brokenIds)->count() : 0,
            'assignments'  => Assignment::whereBetween('assigned_at', [$from->toDateString(), $to->toDateString()])->count(),
            'returns'      => DB::table('asset_assignment')->whereBetween('returned_at', $between)->where(fn ($q) => $q->whereNull('return_condition')->orWhere('return_condition', '!=', 'transfert'))->count(),
            'transfers'    => DB::table('assets_histories')->where('action', 'transfert')->whereBetween('created_at', $between)->count(),
            'overdue'      => Assignment::open()->whereNotNull('expected_return_at')->whereDate('expected_return_at', '<', today())->count(),
            'by_status'    => $byStatus,
            'by_category'  => $byCategory,
            'inventaires'  => Schema::hasColumn('inventaires', 'closed_at')
                ? Inventaire::where('status', 'cloture')->whereBetween('closed_at', $between)->get()->map(fn ($i) => [
                    'reference' => $i->reference,
                    'nom'       => $i->nom,
                    'stats'     => $i->stats,
                ])
                : collect(),
        ];
    }

    private function stock(Carbon $from, Carbon $to): array
    {
        $dates = [$from->toDateString(), $to->toDateString()];

        $flows = StockMovement::whereBetween('moved_at', $dates)
            ->select('stock_item_id', 'type', DB::raw('SUM(quantity) as total'))
            ->groupBy('stock_item_id', 'type')
            ->get()
            ->groupBy('stock_item_id');

        $items = StockItem::orderBy('name')->get()->map(function ($item) use ($flows) {
            $f = $flows->get($item->id, collect())->keyBy('type');

            return [
                'item'   => $item,
                'in'     => (float) ($f['entree']->total ?? 0),
                'out'    => (float) ($f['sortie']->total ?? 0),
                'adjust' => (float) ($f['ajustement']->total ?? 0),
            ];
        });

        $byService = StockMovement::where('type', 'sortie')->whereBetween('moved_at', $dates)
            ->leftJoin('services', 'services.id', '=', 'stock_movements.service_id')
            ->select(DB::raw("COALESCE(services.name, 'Non renseigné') as label"), DB::raw('COUNT(*) as total'))
            ->groupBy('services.name')
            ->orderByDesc('total')
            ->get();

        return [
            'items'      => $items,
            'moving'     => $items->filter(fn ($r) => $r['in'] || $r['out'] || $r['adjust'])->values(),
            'low'        => StockItem::low()->orderBy('name')->get(),
            'value'      => StockItem::whereNotNull('unit_price')->get()->sum(fn ($i) => $i->stock_value),
            'in_value'   => (float) StockMovement::where('type', 'entree')->whereBetween('moved_at', $dates)->sum(DB::raw('quantity * COALESCE(unit_price, 0)')),
            'by_service' => $byService,
        ];
    }

    private function maintenance(Carbon $from, Carbon $to): array
    {
        $between = [$from, $to];
        $created = MaintenanceRequest::whereBetween('created_at', $between);
        $completed = MaintenanceRequest::where('status', 'terminee')->whereBetween('completed_at', $between)->get();

        return [
            'received'    => (clone $created)->count(),
            'by_status'   => (clone $created)->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status'),
            'by_priority' => (clone $created)->select('priority', DB::raw('COUNT(*) as total'))->groupBy('priority')->pluck('total', 'priority'),
            'corrective'  => (clone $created)->where('kind', 'corrective')->count(),
            'preventive'  => (clone $created)->where('kind', 'preventive')->count(),
            'completed'   => $completed->count(),
            'lead_time'   => $completed->isNotEmpty() ? round($completed->avg(fn ($r) => $r->lead_time ?? 0), 1) : null,
            'cost'        => (float) $completed->sum('cost'),
            'backlog'     => MaintenanceRequest::open()->count(),
            'to_validate' => MaintenanceRequest::whereIn('status', MaintenanceRequest::PENDING_STATUSES)->count(),
            'plans_late'  => MaintenancePlan::where('active', true)->whereDate('next_due_at', '<', today())->count(),
            'list'        => MaintenanceRequest::with(['infrastructure', 'asset'])->whereBetween('created_at', $between)->latest()->limit(40)->get(),
        ];
    }

    private function projets(Carbon $from, Carbon $to): array
    {
        $active = Project::active()->with('infrastructures')->orderBy('end_date')->get();

        return [
            'active'    => $active,
            'late'      => $active->filter->is_late->count(),
            'completed' => Project::where('status', 'termine')->whereBetween('completed_at', [$from->toDateString(), $to->toDateString()])->get(),
            'milestones'=> DB::table('project_milestones')->whereBetween('done_at', [$from->toDateString(), $to->toDateString()])->count(),
            'budget'    => (float) $active->sum('budget'),
            'spent'     => (float) $active->sum('spent'),
        ];
    }

    private function infrastructures(Carbon $from, Carbon $to): array
    {
        $all = Infrastructure::orderBy('name')->get();
        $valued = $all->filter->canDepreciate();

        // Dotation de la période : part amortie à la fin moins part amortie au début.
        $dotation = $valued->sum(fn ($i) => $i->acquisition_value * (($i->depreciatedShare($to->copy()) ?? 0) - ($i->depreciatedShare($from->copy()) ?? 0)));

        return [
            'total'        => $all->count(),
            'by_status'    => $all->groupBy('status_label')->map->count(),
            'by_condition' => $all->groupBy(fn ($i) => $i->condition_label ?? 'Non évalué')->map->count(),
            'degraded'     => $all->filter(fn ($i) => in_array($i->condition, ['degrade', 'critique'], true))->values(),
            'gross'        => (float) $valued->sum('acquisition_value'),
            'net'          => (float) $valued->sum('net_book_value'),
            'dotation'     => round($dotation, 2),
            'ending'       => $valued->filter(fn ($i) => $i->depreciation_end->between(today(), today()->addYears(2)))->values(),
        ];
    }
}
