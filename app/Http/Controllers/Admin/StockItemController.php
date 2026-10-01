<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AssetLocation;
use App\Models\Service;
use App\Models\StockItem;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StockItemController extends Controller
{
    public function __construct(private readonly StockService $stock)
    {
    }

    public function index(Request $request)
    {
        abort_if(Gate::denies('stock_item_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $filter = $request->query('niveau', 'tous');

        $query = StockItem::with(['location', 'supplier'])->orderBy('name');

        match ($filter) {
            'bas'     => $query->low(),
            'rupture' => $query->out(),
            default   => null,
        };

        if ($category = $request->query('famille')) {
            $query->where('category', $category);
        }

        return view('admin.stockItems.index', [
            'items'    => $query->get(),
            'filter'   => $filter,
            'category' => $category,
            'counts'   => [
                'tous'    => StockItem::count(),
                'bas'     => StockItem::low()->count(),
                'rupture' => StockItem::out()->count(),
            ],
        ]);
    }

    public function create()
    {
        abort_if(Gate::denies('stock_item_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.stockItems.create', $this->formData() + ['item' => new StockItem(['unit' => 'unité', 'min_quantity' => 0])]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('stock_item_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->validated($request);
        $initial = (float) ($request->validate(['initial_quantity' => ['nullable', 'numeric', 'min:0']])['initial_quantity'] ?? 0);

        $item = DB::transaction(function () use ($data, $initial) {
            $item = StockItem::create($data + ['reference' => StockItem::nextReference(), 'quantity' => 0]);

            if ($initial > 0) {
                $this->stock->record($item, 'entree', $initial, [
                    'supplier_id' => $item->supplier_id,
                    'unit_price'  => $item->unit_price,
                    'notes'       => 'Stock initial à l\'enregistrement de l\'article.',
                ]);
            }

            return $item;
        });

        return redirect()->route('admin.stock-items.show', $item)->with('message', 'Article '.$item->reference.' enregistré.');
    }

    public function show(StockItem $stockItem)
    {
        abort_if(Gate::denies('stock_item_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stockItem->load(['location', 'supplier']);

        $movements = $stockItem->movements()->with(['supplier', 'service', 'agent', 'user'])->limit(100)->get();

        $monthStart = now()->startOfMonth()->toDateString();
        $stats = [
            'in_month'  => (float) $stockItem->movements()->getQuery()->where('type', 'entree')->where('moved_at', '>=', $monthStart)->sum('quantity'),
            'out_month' => (float) $stockItem->movements()->getQuery()->where('type', 'sortie')->where('moved_at', '>=', $monthStart)->sum('quantity'),
            'out_year'  => (float) $stockItem->movements()->getQuery()->where('type', 'sortie')->whereYear('moved_at', now()->year)->sum('quantity'),
        ];
        $stats['avg_month'] = round($stats['out_year'] / max(now()->month, 1), 2);
        $stats['coverage'] = $stats['avg_month'] > 0 ? (int) floor((float) $stockItem->quantity / $stats['avg_month'] * 30) : null;

        $expiring = $stockItem->perishable
            ? $stockItem->movements()->getQuery()->where('type', 'entree')->whereNotNull('expires_at')
                ->where('expires_at', '<=', today()->addDays(60))->orderBy('expires_at')->get()
            : collect();

        return view('admin.stockItems.show', $this->movementFormData() + [
            'item'      => $stockItem,
            'movements' => $movements,
            'stats'     => $stats,
            'expiring'  => $expiring,
        ]);
    }

    public function edit(StockItem $stockItem)
    {
        abort_if(Gate::denies('stock_item_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.stockItems.edit', $this->formData() + ['item' => $stockItem]);
    }

    public function update(Request $request, StockItem $stockItem)
    {
        abort_if(Gate::denies('stock_item_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stockItem->update($this->validated($request, $stockItem));

        return redirect()->route('admin.stock-items.show', $stockItem)->with('message', 'Article mis à jour.');
    }

    public function destroy(StockItem $stockItem)
    {
        abort_if(Gate::denies('stock_item_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stockItem->delete();

        return redirect()->route('admin.stock-items.index')->with('message', 'Article supprimé.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('stock_item_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        StockItem::whereIn('id', (array) $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function validated(Request $request, ?StockItem $item = null): array
    {
        return $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'category'     => ['nullable', Rule::in(array_keys(StockItem::CATEGORIES))],
            'unit'         => ['required', 'string', 'max:20'],
            'min_quantity' => ['required', 'numeric', 'min:0'],
            'unit_price'   => ['nullable', 'numeric', 'min:0'],
            'perishable'   => ['nullable', 'boolean'],
            'location_id'  => ['nullable', 'integer', 'exists:asset_locations,id'],
            'supplier_id'  => ['nullable', 'integer', 'exists:suppliers,id'],
            'notes'        => ['nullable', 'string', 'max:2000'],
        ]) + ['perishable' => $request->boolean('perishable')];
    }

    private function formData(): array
    {
        return [
            'locations' => AssetLocation::orderBy('name')->pluck('name', 'id'),
            'suppliers' => Supplier::orderBy('name')->pluck('name', 'id'),
        ];
    }

    private function movementFormData(): array
    {
        return [
            'suppliers' => Supplier::orderBy('name')->pluck('name', 'id'),
            'services'  => Service::orderBy('name')->pluck('name', 'id'),
            'agents'    => Agent::with('service')->orderBy('nom')->orderBy('prenom')->get(),
        ];
    }
}
