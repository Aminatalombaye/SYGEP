@php
    $catalog = \App\Support\PermissionCatalog::class;
    $actions = $catalog::ACTIONS;
    $selected = array_map('intval', (array) $selected);
    $groups = $catalog::group($permissions);
@endphp

<div class="form-group perm-matrix-wrap">
    <div class="perm-matrix-head">
        <label class="required mb-0">{{ trans('cruds.role.fields.permissions') }}</label>
        <div class="perm-tools">
            <input type="search" class="form-control form-control-sm" placeholder="Filtrer les modules…" data-perm-filter>
            <button type="button" class="btn btn-default btn-sm" data-perm-all="1"><i class="bi bi-check2-all"></i> Tout cocher</button>
            <button type="button" class="btn btn-default btn-sm" data-perm-all="0"><i class="bi bi-x-lg"></i> Tout décocher</button>
        </div>
    </div>
    @if($errors->has('permissions'))
        <div class="alert alert-danger">{{ $errors->first('permissions') }}</div>
    @endif

    <div class="perm-matrix">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Module</th>
                    @foreach($actions as $label)<th class="text-center">{{ $label }}</th>@endforeach
                    <th>Autres droits</th>
                    <th class="text-center">Ligne</th>
                </tr>
            </thead>
            @foreach($groups as $sectionKey => $section)
                <tbody data-perm-section>
                    <tr class="perm-section">
                        <th colspan="{{ count($actions) + 2 }}">
                            <i class="bi {{ $section['icon'] }}"></i> {{ $section['label'] }}
                            <span class="perm-section-count"><span data-perm-section-count>0</span> / {{ collect($section['modules'])->sum(fn ($m) => collect($m['actions'])->flatten(1)->count()) }}</span>
                        </th>
                        <th class="text-center">
                            <button type="button" class="btn btn-xs btn-icon" data-perm-section-toggle title="Cocher / décocher toute la section"><i class="bi bi-check2-all"></i></button>
                        </th>
                    </tr>
                    @foreach($section['modules'] as $module => $info)
                        @php($byAction = $info['actions'])
                        <tr data-perm-row data-name="{{ \Illuminate\Support\Str::lower($section['label'].' '.$info['label'].' '.$module) }}">
                            <td class="strong">{{ $info['label'] }}</td>
                            @foreach(array_keys($actions) as $action)
                                <td class="text-center">
                                    @foreach($byAction[$action] ?? [] as $perm)
                                        <input type="checkbox" class="perm-check" name="permissions[]" value="{{ $perm['id'] }}" title="{{ $perm['title'] }}" @checked(in_array($perm['id'], $selected, true))>
                                    @endforeach
                                </td>
                            @endforeach
                            <td>
                                @foreach($byAction['other'] ?? [] as $perm)
                                    <label class="perm-other" title="{{ $perm['title'] }}">
                                        <input type="checkbox" class="perm-check" name="permissions[]" value="{{ $perm['id'] }}" @checked(in_array($perm['id'], $selected, true))>
                                        {{ $catalog::actionLabel($perm['title']) }}
                                    </label>
                                @endforeach
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-xs btn-icon" data-perm-row-toggle title="Cocher / décocher la ligne"><i class="bi bi-check2-square"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            @endforeach
        </table>
    </div>
    <span class="help-block"><span data-perm-count>0</span> permission(s) sélectionnée(s)</span>
</div>

@push('perm-js')
<script>
(function () {
    var wrap = document.querySelector('.perm-matrix-wrap');
    if (!wrap) return;
    var checks = function (scope) { return Array.prototype.slice.call((scope || wrap).querySelectorAll('.perm-check')); };
    var visible = function (c) { return !c.closest('tr').hidden; };
    var counter = wrap.querySelector('[data-perm-count]');

    function count() {
        counter.textContent = checks().filter(function (c) { return c.checked; }).length;
        wrap.querySelectorAll('[data-perm-section]').forEach(function (section) {
            section.querySelector('[data-perm-section-count]').textContent = checks(section).filter(function (c) { return c.checked; }).length;
        });
    }
    function toggle(list) {
        var allOn = list.every(function (c) { return c.checked; });
        list.forEach(function (c) { c.checked = !allOn; });
        count();
    }

    wrap.addEventListener('change', count);
    wrap.querySelectorAll('[data-perm-all]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var on = btn.getAttribute('data-perm-all') === '1';
            checks().filter(visible).forEach(function (c) { c.checked = on; });
            count();
        });
    });
    wrap.querySelectorAll('[data-perm-row-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () { toggle(checks(btn.closest('tr'))); });
    });
    wrap.querySelectorAll('[data-perm-section-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () { toggle(checks(btn.closest('[data-perm-section]')).filter(visible)); });
    });

    var filter = wrap.querySelector('[data-perm-filter]');
    filter.addEventListener('input', function () {
        var q = filter.value.trim().toLowerCase();
        wrap.querySelectorAll('[data-perm-section]').forEach(function (section) {
            var shown = 0;
            section.querySelectorAll('[data-perm-row]').forEach(function (tr) {
                tr.hidden = q && tr.getAttribute('data-name').indexOf(q) === -1;
                if (!tr.hidden) shown++;
            });
            section.hidden = shown === 0;
        });
    });
    count();
})();
</script>
@endpush
