<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetStatus;
use App\Models\AssetsHistory;
use App\Models\Assignment;
use App\Models\ContactMessage;
use App\Models\Infrastructure;
use App\Models\MaintenanceRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    private const STATUS_FR = [
        'available'      => 'Disponible',
        'assigned'       => 'Affecté',
        'not available'  => 'Indisponible',
        'broken'         => 'En panne',
        'out for repair' => 'En réparation',
        'open'           => 'Ouvert',
        'in progress'    => 'En cours',
        'closed'         => 'Clôturé',
    ];

    public function index()
    {
        $statusCounts = AssetStatus::query()
            ->leftJoin('assets', function ($join) {
                $join->on('assets.status_id', '=', 'asset_statuses.id')->whereNull('assets.deleted_at');
            })
            ->groupBy('asset_statuses.id', 'asset_statuses.name')
            ->orderBy('asset_statuses.id')
            ->get(['asset_statuses.name', DB::raw('COUNT(assets.id) as total')]);

        $kpis = [
            'assets'          => Asset::count(),
            'available'       => Asset::whereIn('status_id', AssetStatus::idsFor(AssetStatus::AVAILABLE))->count(),
            'assigned'        => Asset::where(fn ($q) => $q->whereNotNull('agent_id')->orWhereNotNull('service_id'))->count(),
            'overdue'         => Assignment::open()->whereNotNull('expected_return_at')->whereDate('expected_return_at', '<', today())->count(),
            'out_of_service'  => Asset::whereIn('status_id', array_merge(AssetStatus::idsFor(AssetStatus::BROKEN), AssetStatus::idsFor(AssetStatus::REPAIR)))->count(),
            'infrastructures' => Infrastructure::count(),
            'projects'        => Project::active()->count(),
            'projects_late'   => Project::late()->count(),
            'maintenance'     => MaintenanceRequest::open()->count(),
            'maintenance_pending' => MaintenanceRequest::whereIn('status', MaintenanceRequest::PENDING_STATUSES)->count(),
            'stock_low'       => Schema::hasTable('stock_items') ? \App\Models\StockItem::low()->count() : 0,
        ];

        $charts = [
            'status' => [
                'labels' => $statusCounts->map(fn ($s) => $this->fr($s->name))->values(),
                'values' => $statusCounts->pluck('total')->map(fn ($v) => (int) $v)->values(),
            ],
            'category' => $this->assetsByCategory(),
            'monthly'  => $this->assetsPerMonth(12),
            'tasks'    => $this->tasksByStatus(),
        ];

        $latestMovements = AssetsHistory::with(['asset', 'status', 'location', 'agent', 'service', 'assignment'])
            ->latest()
            ->take(8)
            ->get();

        $latestProjects = Project::active()->orderBy('end_date')->take(5)->get();
        $latestRequests = MaintenanceRequest::latest()->take(5)->get();
        $unreadMessages = Schema::hasTable('contact_messages')
            ? ContactMessage::whereNull('read_at')->count()
            : 0;

        return view('home', [
            'kpis'            => $kpis,
            'charts'          => $charts,
            'latestMovements' => $latestMovements,
            'latestProjects'  => $latestProjects,
            'latestRequests'  => $latestRequests,
            'unreadMessages'  => $unreadMessages,
            'statusLabel'     => fn ($name) => $this->fr($name),
        ]);
    }

    private function fr(?string $name): string
    {
        if ($name === null || $name === '') {
            return '—';
        }

        return self::STATUS_FR[mb_strtolower($name)] ?? $name;
    }

    private function assetsByCategory(int $limit = 8): array
    {
        $rows = AssetCategory::query()
            ->join('assets', 'assets.category_id', '=', 'asset_categories.id')
            ->whereNull('assets.deleted_at')
            ->groupBy('asset_categories.id', 'asset_categories.name')
            ->orderByDesc('total')
            ->get(['asset_categories.name', DB::raw('COUNT(assets.id) as total')]);

        $top = $rows->take($limit);
        $others = $rows->slice($limit)->sum('total');

        $labels = $top->pluck('name')->map(fn ($n) => $n ?: 'Sans nom')->values()->all();
        $values = $top->pluck('total')->map(fn ($v) => (int) $v)->values()->all();

        if ($others > 0) {
            $labels[] = 'Autres';
            $values[] = (int) $others;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function assetsPerMonth(int $months): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths($months - 1);

        $counts = Asset::where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn ($a) => $a->created_at->format('Y-m'))
            ->map->count();

        $labels = [];
        $values = [];
        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $labels[] = ucfirst($month->locale('fr')->translatedFormat('M Y'));
            $values[] = (int) ($counts[$month->format('Y-m')] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function tasksByStatus(): array
    {
        $rows = TaskStatus::query()
            ->leftJoin('tasks', function ($join) {
                $join->on('tasks.status_id', '=', 'task_statuses.id')->whereNull('tasks.deleted_at');
            })
            ->groupBy('task_statuses.id', 'task_statuses.name')
            ->orderBy('task_statuses.id')
            ->get(['task_statuses.name', DB::raw('COUNT(tasks.id) as total')]);

        return [
            'labels' => $rows->map(fn ($r) => $this->fr($r->name))->values()->all(),
            'values' => $rows->pluck('total')->map(fn ($v) => (int) $v)->values()->all(),
        ];
    }
}
