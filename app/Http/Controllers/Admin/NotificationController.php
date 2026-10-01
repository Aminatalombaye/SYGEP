<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserAlert;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $filter = $request->query('filtre') === 'non-lues' ? 'non-lues' : 'toutes';

        $notifications = $user->userUserAlerts()
            ->withPivot('read')
            ->when($filter === 'non-lues', fn ($q) => $q->wherePivot('read', false))
            ->orderByDesc('user_alerts.created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'filter'        => $filter,
            'unread'        => $user->userUserAlerts()->wherePivot('read', false)->count(),
            'total'         => $user->userUserAlerts()->count(),
        ]);
    }

    public function open(Request $request, UserAlert $userAlert)
    {
        $user = $request->user();

        abort_unless($user->userUserAlerts()->whereKey($userAlert->id)->exists(), 404);

        $user->userUserAlerts()->updateExistingPivot($userAlert->id, ['read' => true]);

        return $userAlert->alert_link
            ? redirect()->away($userAlert->alert_link)
            : redirect()->route('admin.notifications.index');
    }

    public function markAllRead(Request $request)
    {
        $request->user()->userUserAlerts()->newPivotQuery()->update(['read' => true]);

        return back()->with('message', 'Toutes vos notifications sont marquées comme lues.');
    }
}
