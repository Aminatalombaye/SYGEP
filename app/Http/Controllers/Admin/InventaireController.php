<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyInventaireRequest;
use App\Http\Requests\StoreInventaireRequest;
use App\Http\Requests\UpdateInventaireRequest;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\Inventaire;
use App\Models\Service;
use App\Services\InventaireService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class InventaireController extends Controller
{
    public function __construct(private readonly InventaireService $service)
    {
    }

    public function index()
    {
        abort_if(Gate::denies('inventaire_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $inventaires = Inventaire::with(['location', 'service', 'category'])->latest()->get()
            ->each(fn ($i) => $i->setAttribute('stats', $i->stats()));

        return view('admin.inventaires.index', compact('inventaires'));
    }

    public function create()
    {
        abort_if(Gate::denies('inventaire_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.inventaires.create', $this->formData() + ['inventaire' => new Inventaire()]);
    }

    public function store(StoreInventaireRequest $request)
    {
        $inventaire = Inventaire::create($request->validated() + [
            'reference'     => Inventaire::nextReference(),
            'status'        => Inventaire::DRAFT,
            'created_by_id' => auth()->id(),
        ]);

        return redirect()->route('admin.inventaires.show', $inventaire)
            ->with('message', 'Campagne '.$inventaire->reference.' créée. Démarrez-la quand l\'équipe est prête.');
    }

    public function edit(Inventaire $inventaire)
    {
        abort_if(Gate::denies('inventaire_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.inventaires.edit', $this->formData() + compact('inventaire'));
    }

    public function update(UpdateInventaireRequest $request, Inventaire $inventaire)
    {
        $data = $request->validated();

        if (! $inventaire->isDraft()) {
            unset($data['location_id'], $data['service_id'], $data['category_id']);
        }

        $inventaire->update($data);

        return redirect()->route('admin.inventaires.show', $inventaire)->with('message', 'Campagne mise à jour.');
    }

    public function show(Request $request, Inventaire $inventaire)
    {
        abort_if(Gate::denies('inventaire_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $inventaire->load(['location', 'service', 'category', 'createdBy', 'closedBy']);

        $tab = $request->query('vue', $inventaire->isClosed() ? 'ecarts' : 'a-controler');

        $query = $inventaire->assets()->with(['category', 'location', 'agent', 'service']);

        match ($tab) {
            'controlees' => $query->wherePivotIn('status', ['vu', 'hors_perimetre']),
            'manquantes' => $query->wherePivot('status', 'manquant'),
            'ecarts'     => $query->where(fn ($q) => $q
                ->whereIn('asset_inventaire.status', ['manquant', 'hors_perimetre'])
                ->orWhereIn('asset_inventaire.condition', ['abime', 'hors_service'])
                ->orWhereColumn('asset_inventaire.found_location_id', '!=', 'asset_inventaire.expected_location_id')),
            default      => $query->wherePivot('status', 'attendu'),
        };

        return view('admin.inventaires.show', [
            'inventaire' => $inventaire,
            'stats'      => $inventaire->stats(),
            'tab'        => $tab,
            'items'      => $query->orderBy('assets.name')->get(),
            'locations'  => AssetLocation::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function start(Inventaire $inventaire)
    {
        abort_if(Gate::denies('inventaire_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $count = $this->service->start($inventaire);

        return redirect()->route('admin.inventaires.show', $inventaire)
            ->with('message', "Campagne démarrée : $count matière(s) à contrôler.");
    }

    public function scan(Inventaire $inventaire)
    {
        abort_if(Gate::denies('inventaire_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if (! $inventaire->isRunning()) {
            return redirect()->route('admin.inventaires.show', $inventaire)
                ->with('message', 'Le scan n\'est possible que pendant une campagne en cours.');
        }

        return view('admin.inventaires.scan', [
            'inventaire' => $inventaire->load('location'),
            'stats'      => $inventaire->stats(),
            'locations'  => AssetLocation::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function record(Request $request, Inventaire $inventaire)
    {
        abort_if(Gate::denies('inventaire_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'code'        => ['required', 'string', 'max:255'],
            'condition'   => ['nullable', 'in:'.implode(',', array_keys(Inventaire::CONDITIONS))],
            'location_id' => ['nullable', 'integer', 'exists:asset_locations,id'],
            'notes'       => ['nullable', 'string', 'max:255'],
        ]);

        $result = $this->service->record(
            $inventaire,
            $data['code'],
            $data['condition'] ?? 'bon',
            $data['location_id'] ?? null,
            $data['notes'] ?? null,
        );

        if ($request->expectsJson()) {
            return response()->json($result + ['stats' => $inventaire->stats()]);
        }

        return back()->with('message', $result['message']);
    }

    public function undo(Inventaire $inventaire, int $asset)
    {
        abort_if(Gate::denies('inventaire_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->service->undo($inventaire, $asset);

        return back()->with('message', 'Contrôle annulé.');
    }

    public function close(Request $request, Inventaire $inventaire)
    {
        abort_if(Gate::denies('inventaire_close'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->service->close($inventaire, $request->boolean('appliquer', true));

        return redirect()->route('admin.inventaires.show', ['inventaire' => $inventaire, 'vue' => 'ecarts'])
            ->with('message', 'Campagne clôturée.');
    }

    public function report(Inventaire $inventaire)
    {
        abort_if(Gate::denies('inventaire_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $inventaire->load(['location', 'service', 'category', 'createdBy', 'closedBy']);

        $assets = $inventaire->assets()->with(['category'])->orderBy('assets.name')->get();

        return view('admin.inventaires.report', [
            'inventaire' => $inventaire,
            'stats'      => $inventaire->stats(),
            'assets'     => $assets,
            'locations'  => AssetLocation::withTrashed()->pluck('name', 'id'),
            'contact'    => config('panel.contact'),
        ]);
    }

    public function destroy(Inventaire $inventaire)
    {
        abort_if(Gate::denies('inventaire_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $inventaire->assets()->detach();
        $inventaire->delete();

        return redirect()->route('admin.inventaires.index')->with('message', 'Campagne supprimée.');
    }

    public function massDestroy(MassDestroyInventaireRequest $request)
    {
        Inventaire::whereIn('id', $request->input('ids', []))->get()->each(function (Inventaire $inventaire) {
            $inventaire->assets()->detach();
            $inventaire->delete();
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function formData(): array
    {
        return [
            'locations'  => AssetLocation::orderBy('name')->pluck('name', 'id'),
            'services'   => Service::orderBy('name')->pluck('name', 'id'),
            'categories' => AssetCategory::orderBy('name')->pluck('name', 'id'),
        ];
    }
}
