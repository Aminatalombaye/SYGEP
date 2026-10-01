<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyAssignmentRequest;
use App\Http\Requests\ReturnAssignmentRequest;
use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\TransferAssetRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Models\Agent;
use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\Assignment;
use App\Models\Service;
use App\Services\AffectationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class AssignmentController extends Controller
{
    public function __construct(private readonly AffectationService $affectations)
    {
    }

    public function index(Request $request)
    {
        abort_if(Gate::denies('assignment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $filter = $request->query('statut', 'en_cours');

        $query = Assignment::with(['agent.service', 'service'])
            ->withCount([
                'matieres',
                'matieres as outstanding_count' => fn ($q) => $q->whereNull('asset_assignment.returned_at'),
            ])
            ->latest('assigned_at')
            ->latest('id');

        match ($filter) {
            'en_cours' => $query->open(),
            'en_retard' => $query->open()->whereNotNull('expected_return_at')->whereDate('expected_return_at', '<', today()),
            'restitue' => $query->where('status', Assignment::STATUS_CLOSED),
            default => null,
        };

        if ($agentId = $request->integer('agent')) {
            $query->where('agent_id', $agentId);
        }
        if ($serviceId = $request->integer('service')) {
            $query->where('service_id', $serviceId);
        }

        $counts = [
            'en_cours'  => Assignment::open()->count(),
            'en_retard' => Assignment::open()->whereNotNull('expected_return_at')->whereDate('expected_return_at', '<', today())->count(),
            'restitue'  => Assignment::where('status', Assignment::STATUS_CLOSED)->count(),
            'tous'      => Assignment::count(),
        ];

        return view('admin.assignments.index', [
            'assignments' => $query->get(),
            'filter'      => $filter,
            'counts'      => $counts,
        ]);
    }

    public function create(Request $request)
    {
        abort_if(Gate::denies('assignment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.assignments.create', $this->formData() + [
            'availableAssets' => $this->availableAssets(),
            'selectedAgent'   => $request->integer('agent') ?: null,
            'selectedService' => $request->integer('service') ?: null,
            'selectedAssets'  => array_filter([(int) $request->query('asset')]),
        ]);
    }

    public function store(StoreAssignmentRequest $request)
    {
        $assignment = $this->affectations->assign($request->validated(), $request->input('assets', []));

        return redirect()
            ->route('admin.assignments.show', $assignment)
            ->with('message', 'Bon '.$assignment->reference.' enregistré : '.$assignment->matieres()->count().' matière(s) affectée(s).');
    }

    public function show(Assignment $assignment)
    {
        abort_if(Gate::denies('assignment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $assignment->load([
            'agent.service', 'service', 'location', 'createdBy',
            'matieres.category', 'matieres.status',
            'histories' => fn ($q) => $q->with(['asset', 'user'])->latest(),
        ]);

        return view('admin.assignments.show', compact('assignment'));
    }

    public function bon(Assignment $assignment)
    {
        abort_if(Gate::denies('assignment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $assignment->load(['agent.service', 'service', 'location', 'createdBy', 'matieres.category']);

        return view('admin.assignments.print', [
            'assignment' => $assignment,
            'contact'    => config('panel.contact'),
        ]);
    }

    public function edit(Assignment $assignment)
    {
        abort_if(Gate::denies('assignment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $assignment->load(['agent', 'service', 'matieres']);

        return view('admin.assignments.edit', $this->formData() + compact('assignment'));
    }

    public function update(UpdateAssignmentRequest $request, Assignment $assignment)
    {
        $assignment->update($request->validated());

        return redirect()
            ->route('admin.assignments.show', $assignment)
            ->with('message', 'Bon '.$assignment->reference.' mis à jour.');
    }

    public function returnForm(Assignment $assignment)
    {
        abort_if(Gate::denies('assignment_return'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if (! $assignment->isOpen()) {
            return redirect()->route('admin.assignments.show', $assignment)
                ->with('message', 'Toutes les matières de ce bon ont déjà été restituées.');
        }

        $assignment->load(['agent.service', 'service']);

        return view('admin.assignments.return', [
            'assignment'  => $assignment,
            'outstanding' => $assignment->outstandingAssets()->with('category')->get(),
            'conditions'  => array_diff_key(Assignment::CONDITIONS, ['transfert' => true]),
            'locations'   => AssetLocation::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function returnStore(ReturnAssignmentRequest $request, Assignment $assignment)
    {
        $data = $request->validated();

        $assignment = $this->affectations->returnAssets(
            $assignment,
            $data['assets'],
            $data['condition'],
            $data['notes'] ?? null,
            $data['returned_at'],
            $data['location_id'] ?? null,
        );

        $message = $assignment->status === Assignment::STATUS_CLOSED
            ? 'Restitution enregistrée. Le bon '.$assignment->reference.' est clôturé.'
            : 'Restitution partielle enregistrée sur le bon '.$assignment->reference.'.';

        return redirect()->route('admin.assignments.show', $assignment)->with('message', $message);
    }

    public function transferForm(Asset $asset)
    {
        abort_if(Gate::denies('assignment_create') || Gate::denies('assignment_return'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $asset->load(['agent.service', 'service', 'category']);

        return view('admin.assignments.transfer', $this->formData() + [
            'asset'   => $asset,
            'current' => $asset->currentAssignment(),
        ]);
    }

    public function transfer(TransferAssetRequest $request, Asset $asset)
    {
        $assignment = $this->affectations->transfer($asset, $request->validated());

        return redirect()
            ->route('admin.assignments.show', $assignment)
            ->with('message', $asset->name.' transférée. Nouveau bon : '.$assignment->reference.'.');
    }

    public function destroy(Assignment $assignment)
    {
        abort_if(Gate::denies('assignment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->affectations->release($assignment);
        $assignment->delete();

        return redirect()->route('admin.assignments.index')
            ->with('message', 'Bon '.$assignment->reference.' supprimé. Les matières encore détenues ont été libérées.');
    }

    public function massDestroy(MassDestroyAssignmentRequest $request)
    {
        foreach (Assignment::whereIn('id', $request->input('ids', []))->get() as $assignment) {
            $this->affectations->release($assignment);
            $assignment->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function formData(): array
    {
        return [
            'agents'       => Agent::with('service')->orderBy('nom')->orderBy('prenom')->get(),
            'services'     => Service::orderBy('name')->pluck('name', 'id'),
            'locations'    => AssetLocation::orderBy('name')->pluck('name', 'id'),
            'types'        => Assignment::TYPES,
        ];
    }

    private function availableAssets()
    {
        $blocked = array_filter([
            AssetStatus::idFor(AssetStatus::BROKEN),
            AssetStatus::idFor(AssetStatus::REPAIR),
        ]);

        return Asset::with(['category', 'status'])
            ->whereNull('agent_id')
            ->whereNull('service_id')
            ->when($blocked, fn ($q) => $q->where(fn ($q) => $q->whereNull('status_id')->orWhereNotIn('status_id', $blocked)))
            ->orderBy('name')
            ->get();
    }
}
