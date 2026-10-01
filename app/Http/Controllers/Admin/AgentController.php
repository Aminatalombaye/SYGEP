<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyAgentRequest;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentRequest;
use App\Models\Agent;
use App\Models\Service;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('agent_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $agents = Agent::with('service')->withCount('assets')->orderBy('nom')->get();

        return view('admin.agents.index', compact('agents'));
    }

    public function create()
    {
        abort_if(Gate::denies('agent_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $services = Service::orderBy('name')->pluck('name', 'id');

        return view('admin.agents.create', compact('services'));
    }

    public function store(StoreAgentRequest $request)
    {
        $agent = Agent::create($request->only(['nom', 'prenom', 'adresse', 'email', 'telephone', 'service_id']));

        return redirect()->route('admin.agents.show', $agent)
            ->with('message', 'Agent '.$agent->full_name.' enregistré.');
    }

    public function edit(Agent $agent)
    {
        abort_if(Gate::denies('agent_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $services = Service::orderBy('name')->pluck('name', 'id');

        return view('admin.agents.edit', compact('agent', 'services'));
    }

    public function update(UpdateAgentRequest $request, Agent $agent)
    {
        $agent->update($request->only(['nom', 'prenom', 'adresse', 'email', 'telephone', 'service_id']));

        return redirect()->route('admin.agents.show', $agent)
            ->with('message', 'Fiche de '.$agent->full_name.' mise à jour.');
    }

    public function show(Agent $agent)
    {
        abort_if(Gate::denies('agent_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $agent->load([
            'service',
            'assets' => fn ($q) => $q->with(['category', 'status'])->orderBy('name'),
            'assignments' => fn ($q) => $q->withCount([
                'matieres',
                'matieres as outstanding_count' => fn ($q) => $q->whereNull('asset_assignment.returned_at'),
            ])->latest('assigned_at')->latest('id'),
            'histories' => fn ($q) => $q->with(['asset', 'assignment', 'user'])->latest()->limit(30),
        ]);

        return view('admin.agents.show', compact('agent'));
    }

    public function destroy(Agent $agent)
    {
        abort_if(Gate::denies('agent_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $agent->delete();

        return back();
    }

    public function massDestroy(MassDestroyAgentRequest $request)
    {
        $agents = Agent::find(request('ids'));

        foreach ($agents as $agent) {
            $agent->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
