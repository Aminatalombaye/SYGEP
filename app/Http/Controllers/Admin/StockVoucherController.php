<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Service;
use App\Models\StockItem;
use App\Models\StockVoucher;
use App\Models\Supplier;
use App\Services\StockVoucherService;
use App\Support\Perimetre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StockVoucherController extends Controller
{
    public function __construct(private readonly StockVoucherService $vouchers)
    {
    }

    public function index(Request $request)
    {
        abort_if(Gate::denies('stock_movement_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $type = $request->query('type');
        $from = $request->date('du') ?? now()->subMonths(3)->startOfMonth();
        $to = $request->date('au') ?? today();

        $vouchers = StockVoucher::with(['supplier', 'service', 'agent', 'createdBy'])
            ->withCount('movements')
            ->when(array_key_exists((string) $type, StockVoucher::TYPES), fn ($q) => $q->where('type', $type))
            ->whereBetween('moved_at', [$from->toDateString(), $to->toDateString()])
            ->latest('moved_at')->latest('id')
            ->get();

        return view('admin.stockVouchers.index', compact('vouchers', 'type', 'from', 'to'));
    }

    public function create(Request $request)
    {
        abort_if(Gate::denies('stock_movement_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $type = $request->query('type') === 'entree' && ! Perimetre::isLocal() ? 'entree' : 'sortie';

        return view('admin.stockVouchers.create', [
            'type'      => $type,
            'items'     => StockItem::orderBy('name')->get(['id', 'name', 'unit', 'quantity', 'unit_price', 'perishable']),
            'suppliers' => Supplier::orderBy('name')->pluck('name', 'id'),
            'services'  => Service::orderBy('name')->pluck('name', 'id'),
            'agents'    => Agent::with('service')->orderBy('nom')->orderBy('prenom')->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('stock_movement_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'type'                    => ['required', Rule::in(array_keys(StockVoucher::TYPES))],
            'moved_at'                => ['required', 'date', 'before_or_equal:today'],
            'supplier_id'             => ['nullable', 'integer', 'exists:suppliers,id'],
            'service_id'              => [Rule::requiredIf(fn () => $request->input('type') === 'sortie' && ! $request->filled('agent_id')), 'nullable', 'integer', 'exists:services,id'],
            'agent_id'                => ['nullable', 'integer', 'exists:agents,id'],
            'document'                => ['nullable', 'string', 'max:255'],
            'notes'                   => ['nullable', 'string', 'max:2000'],
            'lines'                   => ['required', 'array', 'min:1'],
            'lines.*.stock_item_id'   => ['required', 'integer', 'distinct', 'exists:stock_items,id'],
            'lines.*.quantity'        => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_price'      => ['nullable', 'numeric', 'min:0'],
            'lines.*.expires_at'      => ['nullable', 'date', 'after:moved_at'],
        ], [
            'service_id.required'            => 'Indiquez le service bénéficiaire du bon de sortie (ou l\'agent demandeur).',
            'moved_at.before_or_equal'       => 'La date du bon ne peut pas être dans le futur.',
            'lines.required'                 => 'Ajoutez au moins un article au bon.',
            'lines.min'                      => 'Ajoutez au moins un article au bon.',
            'lines.*.stock_item_id.required' => 'Choisissez l\'article.',
            'lines.*.stock_item_id.distinct' => 'Cet article figure déjà sur le bon : regroupez les quantités sur une seule ligne.',
            'lines.*.quantity.required'      => 'Indiquez la quantité.',
            'lines.*.quantity.gt'            => 'La quantité doit être supérieure à zéro.',
            'lines.*.expires_at.after'       => 'La péremption doit suivre la date du bon.',
        ]);

        abort_if($data['type'] !== 'sortie' && Perimetre::isLocal(), Response::HTTP_FORBIDDEN, 'Le magasin central enregistre les entrées : vous pouvez seulement enregistrer les sorties de votre service.');

        $voucher = $this->vouchers->create($data, $data['lines']);

        return redirect()->route('admin.stock-vouchers.show', $voucher)
            ->with('message', $voucher->type_label.' '.$voucher->reference.' enregistré : '.$voucher->movements->count().' article(s) mis à jour.');
    }

    public function show(StockVoucher $stockVoucher)
    {
        abort_if(Gate::denies('stock_movement_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stockVoucher->load(['movements.item', 'supplier', 'service', 'agent', 'createdBy']);

        return view('admin.stockVouchers.show', ['voucher' => $stockVoucher]);
    }

    public function bon(StockVoucher $stockVoucher)
    {
        abort_if(Gate::denies('stock_movement_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stockVoucher->load(['movements.item', 'supplier', 'service', 'agent', 'agent.service', 'createdBy']);

        return view('admin.stockVouchers.print', [
            'voucher' => $stockVoucher,
            'contact' => config('panel.contact'),
        ]);
    }
}
