@php
    $selected = array_map('intval', (array) $selected);
    $categories = $assets->pluck('category.name')->filter()->unique()->sort()->values();
@endphp

<div class="asset-picker" data-picker>
    <div class="asset-picker-tools">
        <input type="search" class="form-control" placeholder="Rechercher par nom, n° de série, modèle…" data-picker-search>
        @if($categories->count() > 1)
            <select class="form-control" data-picker-category>
                <option value="">Toutes les catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
        @endif
    </div>

    <div class="asset-picker-list">
        @forelse($assets as $asset)
            @php($checked = in_array($asset->id, $selected, true))
            <label class="asset-row {{ $checked ? 'is-checked' : '' }}"
                   data-search="{{ \Illuminate\Support\Str::lower($asset->name.' '.$asset->serial_number.' '.$asset->modele.' '.$asset->type) }}"
                   data-category="{{ $asset->category->name ?? '' }}">
                <input type="checkbox" name="assets[]" value="{{ $asset->id }}" @checked($checked)>
                <span class="asset-main">
                    <span class="asset-name">{{ $asset->name ?: 'Sans nom' }}</span>
                    <span class="asset-meta">
                        {{ $asset->category->name ?? 'Sans catégorie' }}
                        @if($asset->serial_number) · N° {{ $asset->serial_number }}@endif
                        @if($asset->modele) · {{ $asset->modele }}@endif
                    </span>
                </span>
                @if($asset->status)
                    <span class="pill pill-{{ $asset->status->tone }}">{{ $asset->status->label }}</span>
                @endif
            </label>
        @empty
            <div class="asset-picker-empty">
                <i class="bi bi-inbox"></i> Aucune matière disponible pour le moment.
            </div>
        @endforelse
        <div class="asset-picker-empty" data-picker-noresult hidden>Aucune matière ne correspond à la recherche.</div>
    </div>

    <div class="asset-picker-foot"><span data-picker-count>0</span> matière(s) sélectionnée(s)</div>
</div>

@push('picker-js')
<script>
(function () {
    document.querySelectorAll('[data-picker]').forEach(function (picker) {
        var search = picker.querySelector('[data-picker-search]');
        var category = picker.querySelector('[data-picker-category]');
        var rows = Array.prototype.slice.call(picker.querySelectorAll('.asset-row'));
        var counter = picker.querySelector('[data-picker-count]');
        var noResult = picker.querySelector('[data-picker-noresult]');

        function count() {
            var n = 0;
            rows.forEach(function (row) {
                var cb = row.querySelector('input');
                row.classList.toggle('is-checked', cb.checked);
                if (cb.checked) n++;
            });
            counter.textContent = n;
        }

        function filter() {
            var q = (search.value || '').trim().toLowerCase();
            var c = category ? category.value : '';
            var visible = 0;
            rows.forEach(function (row) {
                var show = (!q || row.dataset.search.indexOf(q) !== -1) && (!c || row.dataset.category === c);
                row.hidden = !show;
                if (show) visible++;
            });
            if (noResult) noResult.hidden = visible > 0 || rows.length === 0;
        }

        picker.addEventListener('change', function (e) { if (e.target.matches('input[type=checkbox]')) count(); });
        search.addEventListener('input', filter);
        if (category) category.addEventListener('change', filter);
        count();
    });
})();
</script>
@endpush
