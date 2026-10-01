<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Infrastructure;
use App\Models\MaintenancePlan;
use App\Models\User;
use App\Services\MaintenanceWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class MaintenancePlanController extends Controller
{
    public function __construct(private readonly MaintenanceWorkflow $workflow)
    {
    }

    public function index()
    {
        abort_if(Gate::denies('maintenance_plan_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $plans = MaintenancePlan::with(['infrastructure', 'asset', 'responsible'])
            ->withCount('requests')
            ->orderByDesc('active')
            ->orderBy('next_due_at')
            ->get();

        return view('admin.maintenancePlans.index', compact('plans'));
    }

    public function create(Request $request)
    {
        abort_if(Gate::denies('maintenance_plan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $plan = new MaintenancePlan([
            'target_type'       => $request->query('asset') ? 'asset' : 'infrastructure',
            'infrastructure_id' => $request->integer('infrastructure') ?: null,
            'asset_id'          => $request->integer('asset') ?: null,
            'frequency_months'  => 6,
            'lead_days'         => 7,
            'active'            => true,
            'next_due_at'       => today()->addMonth(),
        ]);

        return view('admin.maintenancePlans.create', $this->formData() + compact('plan'));
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('maintenance_plan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        MaintenancePlan::create($this->validated($request));

        return redirect()->route('admin.maintenance-plans.index')->with('message', 'Plan de maintenance préventive enregistré.');
    }

    public function show(MaintenancePlan $maintenancePlan)
    {
        return redirect()->route('admin.maintenance-plans.edit', $maintenancePlan);
    }

    public function edit(MaintenancePlan $maintenancePlan)
    {
        abort_if(Gate::denies('maintenance_plan_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $maintenancePlan->load(['requests' => fn ($q) => $q->latest()->limit(10)]);

        return view('admin.maintenancePlans.edit', $this->formData() + ['plan' => $maintenancePlan]);
    }

    public function update(Request $request, MaintenancePlan $maintenancePlan)
    {
        abort_if(Gate::denies('maintenance_plan_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $maintenancePlan->update($this->validated($request));

        return redirect()->route('admin.maintenance-plans.index')->with('message', 'Plan mis à jour.');
    }

    public function destroy(MaintenancePlan $maintenancePlan)
    {
        abort_if(Gate::denies('maintenance_plan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $maintenancePlan->delete();

        return redirect()->route('admin.maintenance-plans.index')->with('message', 'Plan supprimé.');
    }

    /** Crée tout de suite la prochaine demande préventive du plan. */
    public function generate(MaintenancePlan $maintenancePlan)
    {
        abort_if(Gate::denies('maintenance_plan_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $created = $this->workflow->generatePreventive($maintenancePlan);
        $request = $maintenancePlan->requests()->latest('id')->first();

        if (! $created) {
            return back()->with('message', 'Une demande est déjà en cours pour ce plan.');
        }

        return redirect()->route('admin.maintenance-requests.show', $request)
            ->with('message', 'Demande préventive '.$request->reference.' créée et transmise au Directeur pour approbation.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title'             => ['required', 'string', 'max:255'],
            'description'       => ['required', 'string', 'max:5000'],
            'target_type'       => ['required', Rule::in(['infrastructure', 'asset'])],
            'infrastructure_id' => ['nullable', 'required_if:target_type,infrastructure', 'integer', 'exists:infrastructures,id'],
            'asset_id'          => ['nullable', 'required_if:target_type,asset', 'integer', 'exists:assets,id'],
            'frequency_months'  => ['required', 'integer', 'min:1', 'max:120'],
            'next_due_at'       => ['required', 'date', $request->isMethod('post') ? 'after_or_equal:today' : 'date'],
            'lead_days'         => ['required', 'integer', 'min:0', 'max:90', fn ($attr, $value, $fail) => (int) $value >= (int) $request->input('frequency_months') * 30 ? $fail('Le délai de création doit rester inférieur à la périodicité du plan.') : null],
            'responsible_id'    => ['nullable', 'integer', 'exists:users,id'],
            'active'            => ['nullable', 'boolean'],
        ], [
            'infrastructure_id.required_if' => 'Choisissez l\'infrastructure à entretenir.',
            'asset_id.required_if'          => 'Choisissez la matière à entretenir.',
            'description.required'          => 'Listez les points de contrôle à vérifier à chaque opération.',
            'next_due_at.after_or_equal'    => 'La prochaine échéance ne peut pas être dans le passé.',
        ]);

        $data['active'] = $request->boolean('active');
        $data['infrastructure_id'] = $data['target_type'] === 'infrastructure' ? $data['infrastructure_id'] : null;
        $data['asset_id'] = $data['target_type'] === 'asset' ? $data['asset_id'] : null;

        return $data;
    }

    private function formData(): array
    {
        return [
            'infrastructures' => Infrastructure::orderBy('name')->pluck('name', 'id'),
            'assets'          => Asset::orderBy('name')->get(['id', 'name', 'qr_code'])->mapWithKeys(fn ($a) => [$a->id => ($a->name ?: 'Matière #'.$a->id).($a->qr_code ? ' · '.$a->qr_code : '')]),
            'users'           => User::orderBy('name')->pluck('name', 'id'),
        ];
    }
}
