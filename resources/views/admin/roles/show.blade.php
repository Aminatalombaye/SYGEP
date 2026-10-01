@extends('layouts.admin')
@section('content')
@php
    $catalog = \App\Support\PermissionCatalog::class;
    $granted = $role->permissions->pluck('id')->flip();
    $groups = $catalog::group($permissions);
    $countIn = fn ($section, $onlyGranted) => collect($section['modules'])
        ->flatMap(fn ($m) => collect($m['actions'])->flatten(1))
        ->filter(fn ($p) => ! $onlyGranted || isset($granted[$p->id]))
        ->count();
    $local = $role->permissions->contains('title', 'perimetre_service');
    $isAll = $role->permissions->count() >= $permissions->count();
@endphp

<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('admin.roles.index') }}">{{ trans('cruds.role.title') }}</a> › {{ $role->title }}</div>
        <h1>{{ $role->title }}</h1>
        <p class="sub">{{ $role->description ?: 'Aucune description.' }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-default"><i class="bi bi-arrow-left"></i> {{ trans('global.back_to_list') }}</a>
        @can('role_edit')
            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> {{ trans('global.edit') }}</a>
        @endcan
    </div>
</div>

<div class="kpi-grid kpi-auto" style="--kpi-cols: 3">
    <div class="kpi kpi-static {{ $isAll ? 'kpi-good' : '' }}">
        <span class="kpi-icon"><i class="bi bi-shield-check"></i></span>
        <span class="kpi-body">
            <span class="kpi-value">{{ $role->permissions->count() }}<small style="font-size: 15px; color: var(--sy-muted)"> / {{ $permissions->count() }}</small></span>
            <span class="kpi-label">Droits accordés</span>
            @if($isAll)<span class="kpi-hint">Accès complet</span>@endif
        </span>
    </div>
    <div class="kpi kpi-static">
        <span class="kpi-icon"><i class="bi bi-people"></i></span>
        <span class="kpi-body">
            <span class="kpi-value">{{ $role->users->count() }}</span>
            <span class="kpi-label">Utilisateur(s)</span>
        </span>
    </div>
    <div class="kpi kpi-static {{ $local ? 'kpi-warning' : '' }}">
        <span class="kpi-icon"><i class="bi {{ $local ? 'bi-geo-alt' : 'bi-globe2' }}"></i></span>
        <span class="kpi-body">
            <span class="kpi-value" style="font-size: 19px">{{ $local ? 'Son service' : 'Tout le ministère' }}</span>
            <span class="kpi-label">Périmètre</span>
        </span>
    </div>
</div>

<div class="sy-grid-2 role-show">
    <div>
        <div class="perm-matrix-head">
            <h2 class="role-show-title">Droits par domaine</h2>
            <label class="perm-other" style="margin: 0"><input type="checkbox" data-only-granted checked> Afficher seulement les droits accordés</label>
        </div>

        @foreach($groups as $key => $section)
            @php($have = $countIn($section, true))
            @php($all = $countIn($section, false))
            <section class="sy-card role-section {{ $have ? '' : 'is-empty' }}" data-role-section>
                <div class="sy-card-head">
                    <h2><i class="bi {{ $section['icon'] }}"></i> {{ $section['label'] }}</h2>
                    <span class="role-perm {{ $have && $have >= $all ? 'is-full' : '' }} {{ $have ? '' : 'is-none' }}">{{ $have }}<small>/{{ $all }}</small></span>
                </div>
                <div class="sy-card-body flush">
                    <table class="dash-table role-rights">
                        <tbody>
                            @foreach($section['modules'] as $module => $info)
                                @php($moduleHas = collect($info['actions'])->flatten(1)->contains(fn ($p) => isset($granted[$p->id])))
                                <tr data-role-row data-has="{{ $moduleHas ? 1 : 0 }}">
                                    <td class="strong" style="width: 38%">{{ $info['label'] }}</td>
                                    <td>
                                        @foreach(array_merge(array_keys($catalog::ACTIONS), ['other']) as $action)
                                            @foreach($info['actions'][$action] ?? [] as $perm)
                                                @php($on = isset($granted[$perm->id]))
                                                <span class="right-chip {{ $on ? 'is-on' : 'is-off' }}" data-on="{{ $on ? 1 : 0 }}" title="{{ $perm->title }}">
                                                    <i class="bi {{ $on ? 'bi-check-lg' : 'bi-dash' }}"></i> {{ $catalog::actionLabel($perm->title) }}
                                                </span>
                                            @endforeach
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="empty-state role-section-empty"><i class="bi bi-slash-circle"></i> Aucun droit dans ce domaine.</div>
                </div>
            </section>
        @endforeach
    </div>

    <div>
        <section class="sy-card">
            <div class="sy-card-head"><h2>Utilisateurs ayant ce rôle</h2></div>
            <div class="sy-card-body flush">
                @if($role->users->isEmpty())
                    <div class="empty-state"><i class="bi bi-person-dash"></i> Aucun utilisateur.</div>
                @else
                    <ul class="role-users">
                        @foreach($role->users as $user)
                            <li>
                                <span class="sy-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</span>
                                <span>
                                    @can('user_show')
                                        <a class="strong" href="{{ route('admin.users.show', $user->id) }}">{{ $user->name }}</a>
                                    @else
                                        <span class="strong">{{ $user->name }}</span>
                                    @endcan
                                    <span class="muted">{{ $user->email }}{{ $user->service ? ' · '.$user->service->name : '' }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        @can('role_delete')
            <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST"
                  onsubmit="return confirm('Supprimer le rôle « {{ $role->title }} » ? {{ $role->users->count() }} utilisateur(s) le perdront.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-trash3"></i> Supprimer ce rôle</button>
            </form>
        @endcan
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
(function () {
    var box = document.querySelector('[data-only-granted]');
    if (!box) return;
    function apply() {
        var only = box.checked;
        document.querySelectorAll('[data-role-section]').forEach(function (section) {
            section.classList.toggle('only-granted', only);
        });
    }
    box.addEventListener('change', apply);
    apply();
})();
</script>
@endsection
