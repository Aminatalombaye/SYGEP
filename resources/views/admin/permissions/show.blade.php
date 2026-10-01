@extends('layouts.admin')
@section('content')
@php($catalog = \App\Support\PermissionCatalog::class)
@php([$module] = $catalog::parse($permission->title))

<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('admin.permissions.index') }}">{{ trans('cruds.permission.title') }}</a> › {{ $catalog::moduleLabel($module) }}</div>
        <h1>{{ $catalog::label($permission->title) }}</h1>
        <p class="sub">{{ $catalog::sectionLabel($permission->title) }} · {{ $permission->roles->count() }} rôle(s)</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-default"><i class="bi bi-arrow-left"></i> {{ trans('global.back_to_list') }}</a>
        @can('permission_edit')
            <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> {{ trans('global.edit') }}</a>
        @endcan
    </div>
</div>

<div class="sy-grid-2">
    <section class="sy-card">
        <div class="sy-card-head"><h2>Détails</h2></div>
        <div class="sy-card-body">
            <dl class="dl">
                <dt>Section</dt><dd>{{ $catalog::sectionLabel($permission->title) }}</dd>
                <dt>Module</dt><dd>{{ $catalog::moduleLabel($module) }}</dd>
                <dt>Droit</dt><dd>{{ $catalog::actionLabel($permission->title) }}</dd>
                <dt>Code technique</dt><dd><code>{{ $permission->title }}</code></dd>
            </dl>
        </div>
    </section>

    <section class="sy-card">
        <div class="sy-card-head"><h2>Rôles qui possèdent ce droit</h2></div>
        <div class="sy-card-body flush">
            @if($permission->roles->isEmpty())
                <div class="empty-state"><i class="bi bi-slash-circle"></i> Aucun rôle.</div>
            @else
                <table class="dash-table">
                    <tbody>
                        @foreach($permission->roles as $role)
                            <tr>
                                <td class="strong">
                                    @can('role_show')<a href="{{ route('admin.roles.show', $role) }}">{{ $role->title }}</a>@else{{ $role->title }}@endcan
                                    @if($role->description)<div class="muted">{{ $role->description }}</div>@endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>
</div>

@can('permission_delete')
    <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-trash3"></i> Supprimer ce droit</button>
    </form>
@endcan
@endsection
