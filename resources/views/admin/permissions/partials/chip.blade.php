@php($roleNames = $perm->roles->pluck('title'))
<a href="{{ route('admin.permissions.show', $perm->id) }}"
   class="perm-chip {{ $roleNames->isEmpty() ? 'is-empty' : '' }}"
   title="{{ $perm->title }} — {{ $roleNames->isEmpty() ? 'aucun rôle' : $roleNames->implode(', ') }}">
    @if($label)<span>{{ $label }}</span>@endif
    <strong>{{ $roleNames->count() }}</strong>
</a>
