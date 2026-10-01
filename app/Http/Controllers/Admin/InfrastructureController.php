<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyInfrastructureRequest;
use App\Models\Infrastructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class InfrastructureController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('infrastructure_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $nature = $request->query('nature');

        $infrastructures = Infrastructure::with('parent')
            ->withCount(['projects', 'maintenanceRequests as open_requests_count' => fn ($q) => $q->open()])
            ->when(array_key_exists((string) $nature, Infrastructure::NATURES), fn ($q) => $q->where('nature', $nature))
            ->orderBy('name')
            ->get();

        $counts = Infrastructure::selectRaw('nature, COUNT(*) as total')->groupBy('nature')->pluck('total', 'nature');

        return view('admin.infrastructures.index', compact('infrastructures', 'nature', 'counts'));
    }

    public function create(Request $request)
    {
        abort_if(Gate::denies('infrastructure_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $infrastructure = new Infrastructure([
            'nature'    => $request->query('parent') ? 'bloc' : 'batiment',
            'status'    => 'en_service',
            'parent_id' => $request->integer('parent') ?: null,
        ]);

        return view('admin.infrastructures.create', $this->formData() + compact('infrastructure'));
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('infrastructure_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $infrastructure = Infrastructure::create($this->validated($request));

        return redirect()->route('admin.infrastructures.show', $infrastructure)->with('message', 'Infrastructure enregistrée.');
    }

    public function show(Infrastructure $infrastructure)
    {
        abort_if(Gate::denies('infrastructure_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $infrastructure->load([
            'parent', 'children',
            'projects' => fn ($q) => $q->latest('start_date'),
            'maintenanceRequests' => fn ($q) => $q->latest()->limit(10),
            'maintenancePlans',
        ]);

        return view('admin.infrastructures.show', [
            'infrastructure' => $infrastructure,
            'schedule'       => $infrastructure->depreciationSchedule(),
        ]);
    }

    public function edit(Infrastructure $infrastructure)
    {
        abort_if(Gate::denies('infrastructure_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.infrastructures.edit', $this->formData($infrastructure) + compact('infrastructure'));
    }

    public function update(Request $request, Infrastructure $infrastructure)
    {
        abort_if(Gate::denies('infrastructure_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $infrastructure->update($this->validated($request, $infrastructure));

        return redirect()->route('admin.infrastructures.show', $infrastructure)->with('message', 'Infrastructure mise à jour.');
    }

    public function destroy(Infrastructure $infrastructure)
    {
        abort_if(Gate::denies('infrastructure_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $infrastructure->delete();

        return redirect()->route('admin.infrastructures.index')->with('message', 'Infrastructure supprimée.');
    }

    public function massDestroy(MassDestroyInfrastructureRequest $request)
    {
        Infrastructure::whereIn('id', request('ids', []))->get()->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function validated(Request $request, ?Infrastructure $infrastructure = null): array
    {
        $data = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'nature'             => ['required', Rule::in(array_keys(Infrastructure::NATURES))],
            'parent_id'          => ['nullable', 'integer', 'exists:infrastructures,id', Rule::notIn(array_filter([$infrastructure?->id]))],
            'type'               => ['nullable', 'string', 'max:255'],
            'description'        => ['nullable', 'string', 'max:5000'],
            'status'             => ['required', Rule::in(array_keys(Infrastructure::STATUSES))],
            'condition'          => ['nullable', Rule::in(array_keys(Infrastructure::CONDITIONS))],
            'last_inspection_at' => ['nullable', 'date', 'before_or_equal:today'],
            'location'           => ['nullable', 'string', 'max:255'],
            'surface'            => ['nullable', 'numeric', 'min:0'],
            'construction_date'  => ['nullable', 'date'],
            'acquisition_value'  => ['nullable', 'numeric', 'min:0'],
            'depreciation_years' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], [
            'parent_id.not_in' => 'Une infrastructure ne peut pas être rattachée à elle-même.',
        ]);

        $errors = [];

        $parentNature = ! empty($data['parent_id']) ? Infrastructure::whereKey($data['parent_id'])->value('nature') : null;

        if ($data['nature'] === 'structure' && $parentNature) {
            $errors['parent_id'] = 'Une structure est un site principal : elle ne se rattache à rien.';
        } elseif ($data['nature'] === 'batiment' && $parentNature && $parentNature !== 'structure') {
            $errors['parent_id'] = 'Un bâtiment se rattache à une structure.';
        } elseif ($data['nature'] === 'bloc' && $parentNature && ! in_array($parentNature, ['batiment', 'structure'], true)) {
            $errors['parent_id'] = 'Un bloc se rattache à un bâtiment (ou directement à une structure).';
        }

        // Amortissement linéaire : valeur, date de mise en service et durée vont ensemble.
        if (! empty($data['acquisition_value']) || ! empty($data['depreciation_years'])) {
            foreach (['acquisition_value' => 'la valeur d\'origine', 'construction_date' => 'la date de mise en service', 'depreciation_years' => 'la durée'] as $field => $label) {
                if (empty($data[$field])) {
                    $errors[$field] = 'Pour calculer l\'amortissement, indiquez aussi '.$label.'.';
                }
            }
        }

        if ($errors) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        $data['depreciation_plan'] = ! empty($data['depreciation_years']) ? $data['depreciation_years'].' ans' : null;

        return $data;
    }

    private function formData(?Infrastructure $current = null): array
    {
        return [
            'parents' => Infrastructure::whereIn('nature', ['structure', 'batiment'])
                ->when($current, fn ($q) => $q->whereKeyNot($current->id))
                ->orderBy('name')
                ->pluck('name', 'id'),
        ];
    }
}
