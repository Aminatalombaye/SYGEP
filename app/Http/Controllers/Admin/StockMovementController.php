<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Service;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StockMovementController extends Controller
{
    public function __construct(private readonly StockService $stock)
    {
    }

    public function index(Request $request)
    {
        abort_if(Gate::denies('stock_movement_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $type = $request->query('type');
        $from = $request->date('du') ?? now()->subMonths(3)->startOfMonth();
        $to = $request->date('au') ?? today();

        $movements = StockMovement::with(['item', 'supplier', 'service', 'agent', 'user'])
            ->when(array_key_exists((string) $type, StockMovement::TYPES), fn ($q) => $q->where('type', $type))
            ->when($request->integer('article'), fn ($q, $id) => $q->where('stock_item_id', $id))
            ->when($request->integer('service'), fn ($q, $id) => $q->where('service_id', $id))
            ->whereBetween('moved_at', [$from->toDateString(), $to->toDateString()])
            ->latest('moved_at')->latest('id')
            ->get();

        return view('admin.stockMovements.index', [
            'movements' => $movements,
            'type'      => $type,
            'from'      => $from,
            'to'        => $to,
            'items'     => StockItem::orderBy('name')->pluck('name', 'id'),
            'services'  => Service::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create(Request $request)
    {
        abort_if(Gate::denies('stock_movement_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $type = $request->query('type', 'sortie');
        if (($type === 'ajustement' && Gate::denies('stock_movement_adjust')) || \App\Support\Perimetre::isLocal()) {
            $type = 'sortie';
        }

        return view('admin.stockMovements.create', [
            'type'      => $type,
            'selected'  => $request->integer('article') ?: null,
            'items'     => StockItem::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->pluck('name', 'id'),
            'services'  => Service::orderBy('name')->pluck('name', 'id'),
            'agents'    => Agent::with('service')->orderBy('nom')->orderBy('prenom')->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('stock_movement_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'type'          => ['required', Rule::in(array_keys(StockMovement::TYPES))],
            'stock_item_id' => ['required', 'integer', 'exists:stock_items,id'],
            'quantity'      => ['required', 'numeric', 'min:0'],
            'moved_at'      => ['required', 'date', 'before_or_equal:today'],
            'supplier_id'   => ['nullable', 'integer', 'exists:suppliers,id'],
            'service_id'    => ['nullable', 'required_if:type,sortie', 'integer', 'exists:services,id'],
            'agent_id'      => ['nullable', 'integer', 'exists:agents,id'],
            'document'      => ['nullable', 'string', 'max:255'],
            'unit_price'    => ['nullable', 'numeric', 'min:0'],
            'expires_at'    => ['nullable', 'date', 'after:moved_at'],
            'notes'         => ['nullable', 'required_if:type,ajustement', 'string', 'max:2000'],
            'retour'        => ['nullable', 'string'],
        ], [
            'service_id.required_if' => 'Indiquez le service bénéficiaire de la sortie.',
            'notes.required_if'      => 'Expliquez l\'écart constaté (comptage, casse, péremption…).',
            'moved_at.before_or_equal' => 'La date du mouvement ne peut pas être dans le futur.',
        ]);

        abort_if($data['type'] === 'ajustement' && Gate::denies('stock_movement_adjust'), Response::HTTP_FORBIDDEN, 'Seul le comptable principal peut ajuster le stock.');
        abort_if($data['type'] !== 'sortie' && \App\Support\Perimetre::isLocal(), Response::HTTP_FORBIDDEN, 'Le magasin central enregistre les entrées : vous pouvez seulement enregistrer les sorties de votre service.');

        if ($data['type'] === 'sortie' && empty($data['service_id']) && ! empty($data['agent_id'])) {
            $data['service_id'] = Agent::find($data['agent_id'])?->service_id;
        }

        $item = StockItem::findOrFail($data['stock_item_id']);
        $movement = $this->stock->record($item, $data['type'], (float) $data['quantity'], $data);

        $message = $movement->type_label.' '.$movement->reference.' enregistrée : '.$item->name.' — nouveau solde '
            .\App\Support\Fmt::qty($movement->balance_after).' '.$item->unit.'.';

        return $request->input('retour') === 'article'
            ? redirect()->route('admin.stock-items.show', $item)->with('message', $message)
            : redirect()->route('admin.stock-movements.index')->with('message', $message);
    }
}
