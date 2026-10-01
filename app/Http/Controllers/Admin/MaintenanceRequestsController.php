<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyMaintenanceRequestRequest;
use App\Models\Asset;
use App\Models\Infrastructure;
use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Services\MaintenanceWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceRequestsController extends Controller
{
    public function __construct(private readonly MaintenanceWorkflow $workflow)
    {
    }

    public function index(Request $request)
    {
        abort_if(Gate::denies('maintenance_request_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Filet de sécurité si le planificateur n'est pas configuré sur le serveur.
        $this->workflow->generatePreventive();

        $filter = $request->query('statut', 'ouvertes');

        $query = MaintenanceRequest::with(['infrastructure', 'asset', 'requestedBy', 'assignedTo'])->latest()->latest('id');

        match ($filter) {
            'ouvertes'  => $query->open(),
            'a_valider' => $query->whereIn('status', MaintenanceRequest::PENDING_STATUSES),
            'terminee'  => $query->where('status', 'terminee'),
            'rejetee'   => $query->where('status', 'rejetee'),
            default     => null,
        };

        $counts = [
            'a_valider' => MaintenanceRequest::whereIn('status', MaintenanceRequest::PENDING_STATUSES)->count(),
            'ouvertes'  => MaintenanceRequest::open()->count(),
            'terminee'  => MaintenanceRequest::where('status', 'terminee')->count(),
            'rejetee'   => MaintenanceRequest::where('status', 'rejetee')->count(),
            'toutes'    => MaintenanceRequest::count(),
        ];

        return view('admin.maintenanceRequests.index', [
            'requests' => $query->get(),
            'filter'   => $filter,
            'counts'   => $counts,
        ]);
    }

    public function create(Request $request)
    {
        abort_if(Gate::denies('maintenance_request_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $maintenanceRequest = new MaintenanceRequest([
            'kind'              => 'corrective',
            'priority'          => 'normale',
            'target_type'       => $request->query('asset') ? 'asset' : ($request->query('infrastructure') ? 'infrastructure' : 'infrastructure'),
            'infrastructure_id' => $request->integer('infrastructure') ?: null,
            'asset_id'          => $request->integer('asset') ?: null,
        ]);

        return view('admin.maintenanceRequests.create', $this->formData() + compact('maintenanceRequest'));
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('maintenance_request_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $maintenanceRequest = $this->workflow->submit($this->validated($request));

        return redirect()->route('admin.maintenance-requests.show', $maintenanceRequest)
            ->with('message', 'Demande '.$maintenanceRequest->reference.' transmise pour validation.');
    }

    public function show(MaintenanceRequest $maintenanceRequest)
    {
        abort_if(Gate::denies('maintenance_request_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $maintenanceRequest->load(['infrastructure', 'asset.category', 'asset.location', 'requestedBy', 'validatedBy', 'approvedBy', 'assignedTo', 'plan', 'tasks.status']);

        return view('admin.maintenanceRequests.show', [
            'maintenanceRequest' => $maintenanceRequest,
            'technicians'        => User::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function edit(MaintenanceRequest $maintenanceRequest)
    {
        abort_if(Gate::denies('maintenance_request_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.maintenanceRequests.edit', $this->formData() + compact('maintenanceRequest'));
    }

    public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        abort_if(Gate::denies('maintenance_request_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->workflow->update($maintenanceRequest, $this->validated($request));

        return redirect()->route('admin.maintenance-requests.show', $maintenanceRequest)->with('message', 'Demande mise à jour.');
    }

    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        abort_if(Gate::denies('maintenance_request_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $maintenanceRequest->delete();

        return redirect()->route('admin.maintenance-requests.index')->with('message', 'Demande supprimée.');
    }

    public function massDestroy(MassDestroyMaintenanceRequestRequest $request)
    {
        MaintenanceRequest::whereIn('id', request('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /* Circuit de traitement */

    public function decide(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $direction = $maintenanceRequest->status === 'en_attente_direction';
        abort_if(Gate::denies($direction ? 'maintenance_request_approve' : 'maintenance_request_validate'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'decision'       => ['required', Rule::in(['valider', 'rejeter'])],
            'decision_notes' => ['nullable', 'required_if:decision,rejeter', 'string', 'max:2000'],
        ], [
            'decision_notes.required_if' => 'Indiquez le motif du rejet.',
        ]);

        if ($data['decision'] === 'valider' && $direction) {
            $this->workflow->approve($maintenanceRequest, $data['decision_notes'] ?? null);
            $message = 'Demande approuvée. Le responsable de la maintenance peut planifier l\'intervention.';
        } elseif ($data['decision'] === 'valider') {
            $this->workflow->validate($maintenanceRequest, $data['decision_notes'] ?? null);
            $message = 'Avis technique favorable enregistré. La demande est transmise au Directeur pour approbation.';
        } else {
            $this->workflow->reject($maintenanceRequest, $data['decision_notes']);
            $message = 'Demande rejetée.';
        }

        return redirect()->route('admin.maintenance-requests.show', $maintenanceRequest)->with('message', $message);
    }

    public function plan(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        abort_if(Gate::denies('maintenance_request_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'planned_for'    => ['required', 'date'],
            'due_date'       => ['nullable', 'date', 'after_or_equal:planned_for'],
            'assigned_to_id' => ['nullable', 'integer', 'exists:users,id'],
            'instructions'   => ['nullable', 'string', 'max:2000'],
        ]);

        $this->workflow->plan($maintenanceRequest, $data);

        return redirect()->route('admin.maintenance-requests.show', $maintenanceRequest)
            ->with('message', 'Intervention planifiée le '.$maintenanceRequest->planned_for->format('d/m/Y').' et ajoutée au calendrier des tâches.');
    }

    public function start(MaintenanceRequest $maintenanceRequest)
    {
        abort_if(Gate::denies('maintenance_request_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->workflow->start($maintenanceRequest);

        return redirect()->route('admin.maintenance-requests.show', $maintenanceRequest)->with('message', 'Intervention démarrée.');
    }

    public function complete(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        abort_if(Gate::denies('maintenance_request_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'resolution'      => ['required', 'string', 'max:5000'],
            'cost'            => ['nullable', 'numeric', 'min:0'],
            'back_in_service' => ['nullable', 'boolean'],
            'condition'       => ['nullable', Rule::in(array_keys(Infrastructure::CONDITIONS))],
        ], [
            'resolution.required' => 'Décrivez l\'intervention réalisée.',
        ]);

        $this->workflow->complete($maintenanceRequest, $data);

        return redirect()->route('admin.maintenance-requests.show', $maintenanceRequest)->with('message', 'Intervention clôturée.');
    }

    /* Outils */

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'             => ['required', 'string', 'max:255'],
            'kind'              => ['required', Rule::in(array_keys(MaintenanceRequest::KINDS))],
            'priority'          => ['required', Rule::in(array_keys(MaintenanceRequest::PRIORITIES))],
            'target_type'       => ['required', Rule::in(array_keys(MaintenanceRequest::TARGETS))],
            'infrastructure_id' => ['nullable', 'required_if:target_type,infrastructure', 'integer', 'exists:infrastructures,id'],
            'asset_id'          => ['nullable', 'required_if:target_type,asset', 'integer', 'exists:assets,id'],
            'establishment'     => ['nullable', 'string', 'max:255'],
            'description'       => ['nullable', 'string', 'max:5000'],
        ], [
            'infrastructure_id.required_if' => 'Choisissez l\'infrastructure concernée.',
            'asset_id.required_if'          => 'Choisissez la matière concernée.',
        ]);
    }

    private function formData(): array
    {
        return [
            'infrastructures' => Infrastructure::orderBy('name')->get(['id', 'name', 'location'])
                ->mapWithKeys(fn ($i) => [$i->id => $i->name.($i->location ? ' — '.$i->location : '')]),
            'assets' => Asset::with('location')->orderBy('name')->get(['id', 'name', 'serial_number', 'qr_code', 'location_id'])
                ->mapWithKeys(fn ($a) => [$a->id => trim(($a->name ?: 'Matière #'.$a->id).' · '.($a->qr_code ?: $a->serial_number).($a->location ? ' · '.$a->location->name : ''), ' ·')]),
        ];
    }
}
