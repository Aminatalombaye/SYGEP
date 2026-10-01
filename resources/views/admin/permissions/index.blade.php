@extends('layouts.admin')
@section('content')
@include('partials.module-overview', [
    'title' => trans('cruds.permission.title'),
    'create' => ['route' => 'admin.permissions.create', 'can' => 'permission_create', 'label' => trans('global.add').' '.trans('cruds.permission.title_singular')],
])

@php($groups = \App\Support\PermissionCatalog::group($permissions))
@php($actions = \App\Support\PermissionCatalog::ACTIONS)

<div class="perm-browser">
    <div class="perm-matrix-head">
        <p class="muted mb-0">Chaque pastille indique le nombre de rôles qui possèdent le droit. Cliquez dessus pour voir le détail.</p>
        <div class="perm-tools">
            <input type="search" class="form-control form-control-sm" placeholder="Filtrer les modules…" data-perm-browser-filter>
        </div>
    </div>

    @foreach($groups as $sectionKey => $section)
        <section class="sy-card" data-perm-browser-section>
            <div class="sy-card-head">
                <h2><i class="bi {{ $section['icon'] }}"></i> {{ $section['label'] }}</h2>
                <span class="muted">{{ collect($section['modules'])->sum(fn ($m) => collect($m['actions'])->flatten(1)->count()) }} permission(s)</span>
            </div>
            <div class="sy-card-body flush">
                <div class="table-responsive">
                    <table class="dash-table perm-browser-table">
                        <thead>
                            <tr>
                                <th>Module</th>
                                @foreach($actions as $label)<th class="text-center">{{ $label }}</th>@endforeach
                                <th>Autres droits</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($section['modules'] as $module => $info)
                                <tr data-perm-browser-row data-name="{{ \Illuminate\Support\Str::lower($section['label'].' '.$info['label'].' '.$module) }}">
                                    <td class="strong">{{ $info['label'] }}</td>
                                    @foreach(array_keys($actions) as $action)
                                        <td class="text-center">
                                            @foreach($info['actions'][$action] ?? [] as $perm)
                                                @include('admin.permissions.partials.chip', ['perm' => $perm, 'label' => null])
                                            @endforeach
                                        </td>
                                    @endforeach
                                    <td>
                                        @foreach($info['actions']['other'] ?? [] as $perm)
                                            @include('admin.permissions.partials.chip', ['perm' => $perm, 'label' => \App\Support\PermissionCatalog::actionLabel($perm->title)])
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endforeach
</div>
@endsection

@section('scripts')
@parent
<script>
(function () {
    var filter = document.querySelector('[data-perm-browser-filter]');
    if (!filter) return;
    filter.addEventListener('input', function () {
        var q = filter.value.trim().toLowerCase();
        document.querySelectorAll('[data-perm-browser-section]').forEach(function (section) {
            var shown = 0;
            section.querySelectorAll('[data-perm-browser-row]').forEach(function (tr) {
                tr.hidden = q && tr.getAttribute('data-name').indexOf(q) === -1;
                if (!tr.hidden) shown++;
            });
            section.hidden = shown === 0;
        });
    });
})();
</script>
@endsection
