@extends('layouts.admin')

@section('content')
<div class="page-head">
    <div>
        <h1>Mes notifications</h1>
        <p class="sub">
            @if($unread)
                {{ $unread }} {{ $unread > 1 ? 'notifications non lues' : 'notification non lue' }} sur {{ $total }}.
            @else
                Vous êtes à jour : aucune notification non lue.
            @endif
        </p>
    </div>
    <div class="page-actions">
        @if($unread)
            <form method="POST" action="{{ route('admin.notifications.readAll') }}">
                @csrf
                <button type="submit" class="btn btn-default"><i class="bi bi-check2-all"></i> Tout marquer comme lu</button>
            </form>
        @endif
        @can('user_alert_create')
            <a href="{{ route('admin.user-alerts.create') }}" class="btn btn-primary"><i class="bi bi-send"></i> Envoyer une notification</a>
        @endcan
    </div>
</div>

<nav class="sy-tabs">
    <a href="{{ route('admin.notifications.index') }}" @class(['active' => $filter === 'toutes'])>Toutes<span class="count">{{ $total }}</span></a>
    <a href="{{ route('admin.notifications.index', ['filtre' => 'non-lues']) }}" @class(['active' => $filter === 'non-lues'])>Non lues<span class="count">{{ $unread }}</span></a>
</nav>

<section class="sy-card">
    @forelse($notifications as $n)
        @php($isRead = (bool) $n->pivot->read)
        <a href="{{ route('admin.notifications.open', $n) }}" class="notif-row {{ $isRead ? '' : 'is-unread' }}"
           @if($n->alert_link && ! $n->isInternal()) target="_blank" rel="noopener noreferrer" @endif>
            <span class="notif-icon notif-{{ $n->kind }}"><i class="bi {{ $n->icon }}" aria-hidden="true"></i></span>
            <span class="notif-body">
                <span class="notif-text">{{ $n->alert_text }}</span>
                <span class="notif-meta">
                    {{ $n->kind_label }} · {{ $n->created_at?->diffForHumans() }}
                    <span class="muted">({{ $n->created_at?->format('d/m/Y à H:i') }})</span>
                </span>
            </span>
            @unless($isRead)<span class="notif-dot" aria-label="Non lue"></span>@endunless
            @if($n->alert_link)<i class="bi bi-chevron-right notif-go" aria-hidden="true"></i>@endif
        </a>
    @empty
        <div class="empty-state">
            <i class="bi bi-bell-slash"></i>
            {{ $filter === 'non-lues' ? 'Aucune notification non lue.' : 'Vous n\'avez encore reçu aucune notification.' }}
        </div>
    @endforelse
</section>

@if($notifications->hasPages())
    <div class="d-flex justify-content-center">{{ $notifications->links('pagination::bootstrap-4') }}</div>
@endif
@endsection
