<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyUserAlertRequest;
use App\Http\Requests\StoreUserAlertRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class UserAlertsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('user_alert_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $userAlerts = UserAlert::withCount([
            'users',
            'users as read_count' => fn ($q) => $q->where('user_user_alert.read', true),
        ])->latest()->get();

        return view('admin.userAlerts.index', compact('userAlerts'));
    }

    public function create()
    {
        abort_if(Gate::denies('user_alert_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.userAlerts.create', [
            'users' => User::orderBy('name')->pluck('name', 'id'),
            'roles' => Role::withCount('users')->orderBy('title')->get(),
            'destinations' => self::destinations(),
        ]);
    }

    public function store(StoreUserAlertRequest $request)
    {
        $recipients = match ($request->input('audience')) {
            'tous'  => User::pluck('id'),
            'roles' => User::whereHas('roles', fn ($q) => $q->whereIn('roles.id', $request->input('roles', [])))->pluck('id'),
            default => collect($request->input('users', [])),
        };

        $destination = $request->input('destination');
        $link = match (true) {
            $destination === 'custom' => $request->input('alert_link'),
            ! empty($destination) => $this->urlFor($destination),
            default => null,
        };

        $userAlert = UserAlert::create([
            'alert_text' => $request->input('alert_text'),
            'alert_link' => $link,
        ]);
        $userAlert->users()->sync($recipients->unique()->values()->all());

        return redirect()
            ->route('admin.user-alerts.show', $userAlert)
            ->with('message', 'Notification envoyée à '.$recipients->unique()->count().' utilisateur(s).');
    }

    /**
     * Pages de SYGEP proposées comme destination d'une notification.
     */
    public static function destinations(): array
    {
        $groups = [
            'Général' => [
                'admin.home' => 'Tableau de bord',
                'admin.notifications.index' => 'Mes notifications',
            ],
            'Matières & inventaire' => [
                'admin.assets.index' => 'Matières',
                'admin.assets.create' => 'Matières — ajouter',
                'admin.assets-histories.index' => 'Historique des mouvements',
                'admin.inventaires.index' => 'Inventaires',
                'admin.suppliers.index' => 'Fournisseurs',
                'admin.bons.index' => 'Bons',
            ],
            'Affectations & personnel' => [
                'admin.assignments.index' => 'Affectations en cours',
                'admin.assignments.index|statut=en_retard' => 'Affectations — retours en retard',
                'admin.assignments.create' => 'Nouvelle affectation',
                'admin.agents.index' => 'Agents',
                'admin.services.index' => 'Services',
            ],
            'Infrastructures & projets' => [
                'admin.infrastructures.index' => 'Infrastructures',
                'admin.projects.index' => 'Projets',
                'admin.reports.index' => 'Rapports',
            ],
            'Maintenance & tâches' => [
                'admin.maintenance-requests.index' => 'Demandes de maintenance',
                'admin.maintenance-requests.create' => 'Nouvelle demande de maintenance',
                'admin.tasks.index' => 'Tâches',
                'admin.tasks-calendars.index' => 'Calendrier des tâches',
            ],
            'Mon compte' => [
                'profile.password.edit' => 'Mon profil / mot de passe',
            ],
        ];

        foreach ($groups as $group => $pages) {
            $groups[$group] = array_filter(
                $pages,
                fn ($key) => Route::has(explode('|', $key)[0]),
                ARRAY_FILTER_USE_KEY
            );
        }

        return array_filter($groups);
    }

    private function urlFor(string $key): ?string
    {
        [$name, $query] = array_pad(explode('|', $key, 2), 2, null);

        if (! Route::has($name)) {
            return null;
        }

        parse_str((string) $query, $params);

        return route($name, $params);
    }

    public function show(UserAlert $userAlert)
    {
        abort_if(Gate::denies('user_alert_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $userAlert->load(['users' => fn ($q) => $q->orderBy('name')]);

        return view('admin.userAlerts.show', compact('userAlert'));
    }

    public function destroy(UserAlert $userAlert)
    {
        abort_if(Gate::denies('user_alert_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $userAlert->delete();

        return redirect()->route('admin.user-alerts.index')->with('message', 'Notification supprimée.');
    }

    public function massDestroy(MassDestroyUserAlertRequest $request)
    {
        UserAlert::whereIn('id', $request->input('ids', []))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function read(Request $request)
    {
        $request->user()->userUserAlerts()->newPivotQuery()->update(['read' => true]);

        return response()->noContent();
    }
}
