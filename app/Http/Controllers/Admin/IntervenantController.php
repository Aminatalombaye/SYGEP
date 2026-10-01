<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Intervenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class IntervenantController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('intervenant_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $intervenants = Intervenant::withCount([
            'projects',
            'projects as active_projects_count' => fn ($q) => $q->whereIn('status', ['planifie', 'en_cours', 'suspendu']),
        ])->orderBy('organisation')->orderBy('nom')->get();

        return view('admin.intervenants.index', compact('intervenants'));
    }

    public function create()
    {
        abort_if(Gate::denies('intervenant_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.intervenants.create', ['intervenant' => new Intervenant(['role' => 'entreprise'])]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('intervenant_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $intervenant = Intervenant::create($this->validated($request));

        if ($projectId = $request->integer('project')) {
            $intervenant->projects()->syncWithoutDetaching([$projectId]);

            return redirect()->to(route('admin.projects.show', $projectId).'#intervenants')->with('message', 'Intervenant créé et ajouté au projet.');
        }

        return redirect()->route('admin.intervenants.show', $intervenant)->with('message', 'Intervenant enregistré.');
    }

    public function show(Intervenant $intervenant)
    {
        abort_if(Gate::denies('intervenant_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $intervenant->load(['projects', 'milestones.project']);

        return view('admin.intervenants.show', compact('intervenant'));
    }

    public function edit(Intervenant $intervenant)
    {
        abort_if(Gate::denies('intervenant_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.intervenants.edit', compact('intervenant'));
    }

    public function update(Request $request, Intervenant $intervenant)
    {
        abort_if(Gate::denies('intervenant_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $intervenant->update($this->validated($request));

        return redirect()->route('admin.intervenants.show', $intervenant)->with('message', 'Intervenant mis à jour.');
    }

    public function destroy(Intervenant $intervenant)
    {
        abort_if(Gate::denies('intervenant_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $intervenant->delete();

        return redirect()->route('admin.intervenants.index')->with('message', 'Intervenant supprimé.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('intervenant_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Intervenant::whereIn('id', (array) $request->input('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nom'          => ['required', 'string', 'max:255'],
            'prenom'       => ['nullable', 'string', 'max:255'],
            'organisation' => ['nullable', 'string', 'max:255'],
            'role'         => ['required', Rule::in(array_keys(Intervenant::ROLES))],
            'telephone'    => ['nullable', 'string', 'max:40'],
            'email'        => ['nullable', 'email', 'max:255'],
            'adresse'      => ['nullable', 'string', 'max:255'],
            'notes'        => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
