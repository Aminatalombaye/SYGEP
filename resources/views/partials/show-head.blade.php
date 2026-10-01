@php
    $moduleTitle = trans('cruds.'.$module.'.title');
    $singular = trans('cruds.'.$module.'.title_singular');
    $label = $record->name ?? $record->title ?? $record->reference ?? null;
    $label = $label ?: trim(($record->prenom ?? '').' '.($record->nom ?? ''));
    $label = $label ?: $singular.' #'.$record->id;
@endphp
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route($index) }}">{{ $moduleTitle }}</a> › {{ \Illuminate\Support\Str::limit($label, 40) }}</div>
        <h1>{{ $label }}</h1>
        <p class="sub">{{ $singular }} · créé le {{ $record->created_at?->format('d/m/Y') ?? '—' }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route($index) }}" class="btn btn-default"><i class="bi bi-arrow-left"></i> {{ trans('global.back_to_list') }}</a>
        @if(!empty($edit) && Route::has($edit['route']))
            @can($edit['can'])
                <a href="{{ route($edit['route'], $record) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> {{ trans('global.edit') }}</a>
            @endcan
        @endif
    </div>
</div>
