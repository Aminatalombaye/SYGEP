@php
    $moduleTitle = trans('cruds.'.$module.'.title');
    $singular = trans('cruds.'.$module.'.title_singular');
    $isEdit = ($mode ?? 'create') === 'edit';
@endphp
<div class="page-head form-page-head">
    <div>
        <div class="crumb">
            <a href="{{ route($index) }}">{{ $moduleTitle }}</a> › {{ $isEdit ? 'Modifier' : 'Nouveau' }}
        </div>
        <h1>{{ $isEdit ? 'Modifier' : 'Ajouter' }} · {{ $singular }}</h1>
        <p class="sub">
            @if($isEdit && isset($record))
                {{ $record->name ?? $record->title ?? $record->reference ?? trim(($record->prenom ?? '').' '.($record->nom ?? '')) ?: '#'.$record->id }}
            @else
                Les champs marqués <span class="req-star">*</span> sont obligatoires.
            @endif
        </p>
    </div>
    <div class="page-actions">
        <a href="{{ route($index) }}" class="btn btn-default"><i class="bi bi-x-lg"></i> Annuler</a>
    </div>
</div>
