@php
    $actions = ['access' => 'Accès', 'show' => 'Voir', 'create' => 'Créer', 'edit' => 'Modifier', 'delete' => 'Supprimer'];
    $selected = array_map('intval', (array) $selected);
    $groups = [];
    foreach ($permissions as $id => $title) {
        if (preg_match('/^(.+)_(access|show|create|edit|delete)$/', $title, $m)) {
            [$module, $action] = [$m[1], $m[2]];
        } else {
            [$module, $action] = [$title, 'other'];
        }
        $groups[$module][$action][] = ['id' => $id, 'title' => $title];
    }
    ksort($groups);
    $moduleLabel = function ($module) {
        $key = 'cruds.'.\Illuminate\Support\Str::camel(preg_replace('/_management$/', 'Management', $module)).'.title';
        return \Illuminate\Support\Facades\Lang::has($key) ? trans($key) : ucfirst(str_replace('_', ' ', $module));
    };
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
                    <th>Autres</th>
                    <th class="text-center">Ligne</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groups as $module => $byAction)
                    <tr data-perm-row data-name="{{ \Illuminate\Support\Str::lower($moduleLabel($module).' '.$module) }}">
                        <td class="strong">{{ $moduleLabel($module) }}</td>
                        @foreach(array_keys($actions) as $action)
                            <td class="text-center">
                                @foreach($byAction[$action] ?? [] as $perm)
                                    <input type="checkbox" class="perm-check" name="permissions[]" value="{{ $perm['id'] }}" title="{{ $perm['title'] }}" @checked(in_array($perm['id'], $selected, true))>
                                @endforeach
                            </td>
                        @endforeach
                        <td>
                            @foreach($byAction['other'] ?? [] as $perm)
                                <label class="perm-other">
                                    <input type="checkbox" class="perm-check" name="permissions[]" value="{{ $perm['id'] }}" @checked(in_array($perm['id'], $selected, true))>
                                    {{ str_replace('_', ' ', $perm['title']) }}
                                </label>
                            @endforeach
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-xs btn-icon" data-perm-row-toggle title="Cocher / décocher la ligne"><i class="bi bi-check2-square"></i></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
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
    var counter = wrap.querySelector('[data-perm-count]');
    function count() { counter.textContent = checks().filter(function (c) { return c.checked; }).length; }
    wrap.addEventListener('change', count);
    wrap.querySelectorAll('[data-perm-all]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var on = btn.getAttribute('data-perm-all') === '1';
            checks().forEach(function (c) { if (!c.closest('tr').hidden) c.checked = on; });
            count();
        });
    });
    wrap.querySelectorAll('[data-perm-row-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row = checks(btn.closest('tr'));
            var allOn = row.every(function (c) { return c.checked; });
            row.forEach(function (c) { c.checked = !allOn; });
            count();
        });
    });
    var filter = wrap.querySelector('[data-perm-filter]');
    filter.addEventListener('input', function () {
        var q = filter.value.trim().toLowerCase();
        wrap.querySelectorAll('[data-perm-row]').forEach(function (tr) {
            tr.hidden = q && tr.getAttribute('data-name').indexOf(q) === -1;
        });
    });
    count();
})();
</script>
@endpush
