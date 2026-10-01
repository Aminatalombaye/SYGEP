<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetStatus;
use App\Models\AssetsHistory;
use App\Models\Infrastructure;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceRequest;
use App\Models\Task;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Circuit d'une demande de maintenance :
 * Soumise → avis technique du responsable maintenance → approbation du Directeur
 * (ou rejet à l'une de ces étapes) → Planifiée → En cours → Terminée.
 */
class MaintenanceWorkflow
{
    public function submit(array $data): MaintenanceRequest
    {
        $request = DB::transaction(function () use ($data) {
            return MaintenanceRequest::create($this->targetFields($data) + [
                'reference'       => MaintenanceRequest::nextReference(),
                'title'           => $data['title'],
                'kind'            => $data['kind'] ?? 'corrective',
                'priority'        => $data['priority'] ?? 'normale',
                'establishment'   => $data['establishment'] ?? null,
                'description'     => $data['description'] ?? null,
                'status'          => 'soumise',
                'requested_by_id' => auth()->id(),
                'created_by'      => auth()->user()?->name,
            ]);
        });

        Notifier::permission(
            'maintenance_request_validate',
            "Nouvelle demande de maintenance {$request->reference} à valider : {$request->title}",
            route('admin.maintenance-requests.show', $request),
            auth()->id()
        );

        return $request;
    }

    public function update(MaintenanceRequest $request, array $data): void
    {
        $request->update($this->targetFields($data) + [
            'title'         => $data['title'],
            'kind'          => $data['kind'] ?? $request->kind,
            'priority'      => $data['priority'] ?? $request->priority,
            'establishment' => $data['establishment'] ?? null,
            'description'   => $data['description'] ?? null,
        ]);
    }

    /** Avis technique favorable : la demande part chez le Directeur. */
    public function validate(MaintenanceRequest $request, ?string $notes = null): void
    {
        $this->guard($request->canBeValidated(), 'Cette demande a déjà été traitée.');

        $request->update([
            'status'          => 'en_attente_direction',
            'validated_by_id' => auth()->id(),
            'validated_at'    => now(),
            'decision_notes'  => $notes,
        ]);

        Notifier::permission(
            'maintenance_request_approve',
            "Demande de maintenance {$request->reference} à approuver : {$request->title}",
            route('admin.maintenance-requests.show', $request),
            auth()->id()
        );
    }

    /** Approbation du Directeur : l'intervention peut être planifiée. */
    public function approve(MaintenanceRequest $request, ?string $notes = null): void
    {
        $this->guard($request->canBeApproved(), 'Cette demande n\'attend pas l\'approbation du Directeur.');

        $request->update([
            'status'          => 'validee',
            'approved_by_id'  => auth()->id(),
            'approved_at'     => now(),
            'approval_notes'  => $notes,
        ]);

        $this->notifyRequester($request, "Votre demande {$request->reference} a été approuvée.");
        Notifier::permission(
            'maintenance_request_validate',
            "Demande {$request->reference} approuvée par le Directeur : intervention à planifier.",
            route('admin.maintenance-requests.show', $request),
            auth()->id()
        );
    }

    public function reject(MaintenanceRequest $request, string $notes): void
    {
        $this->guard($request->isPending(), 'Cette demande a déjà été traitée.');

        if ($request->status === 'en_attente_direction') {
            $request->update([
                'status'         => 'rejetee',
                'approved_by_id' => auth()->id(),
                'approved_at'    => now(),
                'approval_notes' => $notes,
            ]);

            $this->notifyRequester($request, "Votre demande {$request->reference} a été rejetée par le Directeur : {$notes}");

            return;
        }

        $request->update([
            'status'          => 'rejetee',
            'validated_by_id' => auth()->id(),
            'validated_at'    => now(),
            'decision_notes'  => $notes,
        ]);

        $this->notifyRequester($request, "Votre demande {$request->reference} a été rejetée : {$notes}");
    }

    /** Programme l'intervention : crée la tâche correspondante dans le planning. */
    public function plan(MaintenanceRequest $request, array $data): Task
    {
        $this->guard($request->canBePlanned(), 'La demande doit être validée avant d\'être planifiée.');

        $task = DB::transaction(function () use ($request, $data) {
            $request->update([
                'status'         => 'planifiee',
                'planned_for'    => $data['planned_for'],
                'assigned_to_id' => $data['assigned_to_id'] ?? null,
            ]);

            $task = $request->tasks()->first() ?? new Task();
            $task->fill([
                'name'           => '['.$request->reference.'] '.$request->title,
                'description'    => trim(($request->description ?? '')."\n\n".($data['instructions'] ?? '')) ?: null,
                'scheduled_date' => $data['planned_for'],
                'due_date'       => $data['due_date'] ?? $data['planned_for'],
                'assigned_to_id' => $data['assigned_to_id'] ?? null,
                'status_id'      => $task->status_id ?? $this->taskStatus('open'),
            ])->save();

            $request->tasks()->syncWithoutDetaching([$task->id]);

            if ($request->asset_id) {
                $task->equipements()->syncWithoutDetaching([$request->asset_id]);
            }

            return $task;
        });

        if ($request->assigned_to_id) {
            Notifier::users(
                [$request->assigned_to_id],
                "Intervention {$request->reference} planifiée le ".$request->planned_for->format('d/m/Y')." : {$request->title}",
                route('admin.maintenance-requests.show', $request)
            );
        }

        return $task;
    }

    public function start(MaintenanceRequest $request): void
    {
        $this->guard($request->canBeStarted(), 'L\'intervention ne peut pas démarrer dans l\'état actuel.');

        DB::transaction(function () use ($request) {
            $request->update(['status' => 'en_cours', 'started_at' => now()]);
            $this->setTasksStatus($request, 'progress');

            if ($request->asset) {
                $this->setAssetStatus($request->asset, AssetStatus::REPAIR, 'Intervention '.$request->reference.' démarrée.');
            }

            if ($request->infrastructure && $request->infrastructure->status === 'en_service' && $request->priority === 'urgente') {
                $request->infrastructure->update(['status' => 'en_maintenance']);
            }
        });
    }

    public function complete(MaintenanceRequest $request, array $data): void
    {
        $this->guard($request->canBeCompleted(), 'Cette demande est déjà clôturée.');

        DB::transaction(function () use ($request, $data) {
            $request->update([
                'status'       => 'terminee',
                'started_at'   => $request->started_at ?? now(),
                'completed_at' => now(),
                'resolution'   => $data['resolution'],
                'cost'         => $data['cost'] ?? null,
            ]);

            $this->setTasksStatus($request, 'closed');

            if ($request->asset) {
                $working = ! empty($data['back_in_service']);
                $this->setAssetStatus(
                    $request->asset,
                    $working ? ($request->asset->isAssigned() ? AssetStatus::ASSIGNED : AssetStatus::AVAILABLE) : AssetStatus::BROKEN,
                    'Intervention '.$request->reference.' terminée'.($working ? ' : remise en service.' : ' : matière hors service.')
                );
            }

            if ($request->infrastructure) {
                $attributes = ['last_inspection_at' => today()];
                if ($request->infrastructure->status === 'en_maintenance') {
                    $attributes['status'] = 'en_service';
                }
                if (! empty($data['condition'])) {
                    $attributes['condition'] = $data['condition'];
                }
                $request->infrastructure->update($attributes);
            }

            if ($request->plan) {
                $request->plan->update(['last_done_at' => today()]);
            }
        });

        $this->notifyRequester($request, "L'intervention {$request->reference} est terminée.");
    }

    /**
     * Génère les demandes préventives arrivées à échéance.
     * Appelé chaque jour par le planificateur et à l'ouverture du module.
     */
    public function generatePreventive(?MaintenancePlan $only = null): int
    {
        $plans = $only ? collect([$only]) : MaintenancePlan::due()->get();
        $created = 0;

        foreach ($plans as $plan) {
            if (! $plan->active || $plan->requests()->withoutGlobalScope('perimetre')->open()->exists()) {
                continue;
            }

            $request = DB::transaction(function () use ($plan) {
                $request = MaintenanceRequest::create([
                    'reference'           => MaintenanceRequest::nextReference(),
                    'title'               => $plan->title,
                    'kind'                => 'preventive',
                    'target_type'         => $plan->target_type,
                    'infrastructure_id'   => $plan->target_type === 'infrastructure' ? $plan->infrastructure_id : null,
                    'asset_id'            => $plan->target_type === 'asset' ? $plan->asset_id : null,
                    'priority'            => 'normale',
                    'description'         => $plan->description,
                    'status'              => 'en_attente_direction',
                    'validated_at'        => now(),
                    'decision_notes'      => 'Avis technique : entretien prévu au plan de maintenance préventive.',
                    'planned_for'         => $plan->next_due_at,
                    'assigned_to_id'      => $plan->responsible_id,
                    'created_by'          => 'Plan préventif',
                    'maintenance_plan_id' => $plan->id,
                ]);

                $plan->update(['next_due_at' => $plan->next_due_at->copy()->addMonthsNoOverflow($plan->frequency_months)]);

                return $request;
            });

            $created++;

            $link = route('admin.maintenance-requests.show', $request);
            $due = $request->planned_for->format('d/m/Y');
            Notifier::permission('maintenance_request_approve', "Maintenance préventive à approuver : {$plan->title} (échéance {$due})", $link);
            if ($plan->responsible_id) {
                Notifier::users([$plan->responsible_id], "Maintenance préventive générée : {$plan->title} (échéance {$due}), en attente du Directeur.", $link);
            }
        }

        return $created;
    }

    /* Outils */

    private function targetFields(array $data): array
    {
        $type = $data['target_type'] ?? 'autre';

        return [
            'target_type'       => $type,
            'infrastructure_id' => $type === 'infrastructure' ? ($data['infrastructure_id'] ?? null) : null,
            'asset_id'          => $type === 'asset' ? ($data['asset_id'] ?? null) : null,
        ];
    }

    private function guard(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['status' => $message]);
        }
    }

    private function notifyRequester(MaintenanceRequest $request, string $text): void
    {
        if ($request->requested_by_id && $request->requested_by_id !== auth()->id()) {
            Notifier::users([$request->requested_by_id], $text, route('admin.maintenance-requests.show', $request));
        }
    }

    private function setTasksStatus(MaintenanceRequest $request, string $state): void
    {
        if ($statusId = $this->taskStatus($state)) {
            $request->tasks()->update(['status_id' => $statusId]);
        }
    }

    /** Retrouve l'identifiant d'un statut de tâche à partir de son sens. */
    private function taskStatus(string $state): ?int
    {
        $keywords = [
            'open'     => ['ouvr', 'open', 'à faire', 'a faire', 'nouveau'],
            'progress' => ['cours', 'progress'],
            'closed'   => ['ferm', 'clôtur', 'clotur', 'closed', 'termin'],
        ][$state];

        foreach (TaskStatus::orderBy('id')->get(['id', 'name']) as $status) {
            $name = mb_strtolower((string) $status->name);
            foreach ($keywords as $keyword) {
                if (str_contains($name, $keyword)) {
                    return $status->id;
                }
            }
        }

        return null;
    }

    private function setAssetStatus(Asset $asset, string $statusName, string $note): void
    {
        $statusId = AssetStatus::idFor($statusName);

        if (! $statusId || (int) $asset->status_id === $statusId) {
            return;
        }

        $asset->status_id = $statusId;
        $asset->saveQuietly();

        AssetsHistory::create([
            'asset_id'    => $asset->id,
            'status_id'   => $statusId,
            'location_id' => $asset->location_id,
            'agent_id'    => $asset->agent_id,
            'service_id'  => $asset->service_id,
            'action'      => 'maintenance',
            'user_id'     => auth()->id(),
            'notes'       => $note,
        ]);
    }
}
