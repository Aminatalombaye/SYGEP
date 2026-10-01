<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyProjectRequest;
use App\Models\ChefProjet;
use App\Models\Infrastructure;
use App\Models\Intervenant;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('project_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $filter = $request->query('statut', 'actifs');

        $query = Project::with(['infrastructures', 'chef_projets'])
            ->withCount(['milestones', 'milestones as milestones_done_count' => fn ($q) => $q->whereNotNull('done_at')])
            ->latest('start_date')
            ->latest('id');

        match ($filter) {
            'actifs'    => $query->active(),
            'en_retard' => $query->late(),
            'tous'      => null,
            default     => array_key_exists($filter, Project::STATUSES) ? $query->where('status', $filter) : null,
        };

        $counts = [
            'actifs'    => Project::active()->count(),
            'en_retard' => Project::late()->count(),
            'termine'   => Project::where('status', 'termine')->count(),
            'tous'      => Project::count(),
        ];

        return view('admin.projects.index', [
            'projects' => $query->get(),
            'filter'   => $filter,
            'counts'   => $counts,
        ]);
    }

    public function create(Request $request)
    {
        abort_if(Gate::denies('project_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $project = new Project([
            'status' => 'planifie',
            'type'   => 'construction',
        ]);

        if ($infra = $request->integer('infrastructure')) {
            $project->setRelation('infrastructures', Infrastructure::whereKey($infra)->get());
        }

        return view('admin.projects.create', $this->formData() + compact('project'));
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('project_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->validated($request);

        $project = Project::create($data + [
            'reference'     => Project::nextReference(),
            'created_by_id' => auth()->id(),
        ]);
        $this->syncRelations($project, $request);

        return redirect()->route('admin.projects.show', $project)
            ->with('message', 'Projet '.$project->reference.' créé. Ajoutez maintenant ses jalons pour suivre l\'avancement.');
    }

    public function show(Project $project)
    {
        abort_if(Gate::denies('project_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $project->load(['infrastructures', 'chef_projets', 'intervenants', 'milestones.intervenant', 'reports', 'createdBy']);

        return view('admin.projects.show', [
            'project'      => $project,
            'intervenants' => Intervenant::orderBy('organisation')->orderBy('nom')->get()->pluck('name', 'id'),
        ]);
    }

    public function edit(Project $project)
    {
        abort_if(Gate::denies('project_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $project->load('infrastructures', 'chef_projets', 'intervenants', 'milestones');

        return view('admin.projects.edit', $this->formData() + compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        abort_if(Gate::denies('project_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->validated($request, $project);

        if ($data['status'] === 'termine' && ! $project->completed_at) {
            $data['completed_at'] = today();
        } elseif ($data['status'] !== 'termine') {
            $data['completed_at'] = null;
        }

        $project->update($data);
        $this->syncRelations($project, $request);

        return redirect()->route('admin.projects.show', $project)->with('message', 'Projet mis à jour.');
    }

    public function destroy(Project $project)
    {
        abort_if(Gate::denies('project_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $project->delete();

        return redirect()->route('admin.projects.index')->with('message', 'Projet supprimé.');
    }

    public function massDestroy(MassDestroyProjectRequest $request)
    {
        Project::whereIn('id', request('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /* Jalons */

    public function storeMilestone(Request $request, Project $project)
    {
        abort_if(Gate::denies('project_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'due_date'       => ['nullable', 'date'],
            'weight'         => ['nullable', 'integer', 'min:1', 'max:10'],
            'intervenant_id' => ['nullable', 'integer', 'exists:intervenants,id'],
            'description'    => ['nullable', 'string', 'max:2000'],
        ]);

        $project->milestones()->create($data + [
            'weight'   => $data['weight'] ?? 1,
            'position' => (int) $project->milestones()->max('position') + 1,
        ]);
        $project->refreshProgress();

        return redirect()->to(route('admin.projects.show', $project).'#jalons')->with('message', 'Jalon ajouté.');
    }

    public function toggleMilestone(Project $project, ProjectMilestone $milestone)
    {
        abort_if(Gate::denies('project_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_unless($milestone->project_id === $project->id, Response::HTTP_NOT_FOUND);

        $milestone->update(['done_at' => $milestone->done_at ? null : today()]);
        $project->refreshProgress();

        return redirect()->to(route('admin.projects.show', $project).'#jalons')
            ->with('message', $milestone->done_at ? 'Jalon « '.$milestone->title.' » marqué comme atteint.' : 'Jalon rouvert.');
    }

    public function destroyMilestone(Project $project, ProjectMilestone $milestone)
    {
        abort_if(Gate::denies('project_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_unless($milestone->project_id === $project->id, Response::HTTP_NOT_FOUND);

        $milestone->delete();
        $project->refreshProgress();

        return redirect()->to(route('admin.projects.show', $project).'#jalons')->with('message', 'Jalon supprimé.');
    }

    /* Intervenants */

    public function attachIntervenant(Request $request, Project $project)
    {
        abort_if(Gate::denies('project_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'intervenant_id' => ['required', 'integer', 'exists:intervenants,id'],
            'mission'        => ['nullable', 'string', 'max:255'],
        ]);

        $project->intervenants()->syncWithoutDetaching([$data['intervenant_id'] => ['mission' => $data['mission'] ?? null]]);

        return redirect()->to(route('admin.projects.show', $project).'#intervenants')->with('message', 'Intervenant ajouté au projet.');
    }

    public function detachIntervenant(Project $project, Intervenant $intervenant)
    {
        abort_if(Gate::denies('project_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $project->intervenants()->detach($intervenant->id);

        return redirect()->to(route('admin.projects.show', $project).'#intervenants')->with('message', 'Intervenant retiré du projet.');
    }

    /* Outils */

    private function validated(Request $request, ?Project $project = null): array
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'type'        => ['required', Rule::in(array_keys(Project::TYPES))],
            'status'      => ['required', Rule::in(array_keys(Project::STATUSES))],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_date'  => [Rule::requiredIf(fn () => in_array($request->input('status'), ['en_cours', 'suspendu', 'termine'], true)), 'nullable', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget'      => ['nullable', 'numeric', 'min:0'],
            'spent'       => ['nullable', 'numeric', 'min:0'],
            'progress'    => ['nullable', 'integer', 'min:0', 'max:100'],
            'infrastructures'   => ['nullable', 'array'],
            'infrastructures.*' => ['integer', 'exists:infrastructures,id'],
            'chef_projets'      => ['nullable', 'array'],
            'chef_projets.*'    => ['integer', 'exists:chef_projets,id'],
        ], [
            'start_date.required'     => 'Indiquez la date de démarrage : le projet est déjà lancé.',
            'end_date.after_or_equal' => 'La date de fin doit suivre la date de début.',
        ]);

        // L'avancement est calculé par les jalons quand il y en a.
        if ($project && $project->milestones()->exists()) {
            unset($data['progress']);
        } else {
            $data['progress'] = $data['status'] === 'termine' ? 100 : (int) ($data['progress'] ?? 0);
        }

        return collect($data)->except(['infrastructures', 'chef_projets'])->all();
    }

    private function syncRelations(Project $project, Request $request): void
    {
        $project->infrastructures()->sync($request->input('infrastructures', []));
        $project->chef_projets()->sync($request->input('chef_projets', []));
    }

    private function formData(): array
    {
        return [
            'infrastructures' => Infrastructure::orderBy('name')->pluck('name', 'id'),
            'chef_projets'    => ChefProjet::orderBy('nom')->get()->mapWithKeys(fn ($c) => [$c->id => trim($c->prenom.' '.$c->nom) ?: '#'.$c->id]),
        ];
    }
}
