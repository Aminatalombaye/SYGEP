<div @class(['field', 'has-error' => $errors->has($name)])>
    <label for="{{ $id ?? $name }}">{{ $label }}</label>
    <div class="input-icon">
        <i class="bi {{ $icon }}" aria-hidden="true"></i>
        <input id="{{ $id ?? $name }}" type="{{ $type ?? 'text' }}" name="{{ $name }}"
               value="{{ ($type ?? 'text') === 'password' ? '' : old($name, $value ?? '') }}"
               @if($required ?? true) required @endif
               @if(!empty($autocomplete)) autocomplete="{{ $autocomplete }}" @endif
               @if(!empty($autofocus)) autofocus @endif
               @if(!empty($placeholder)) placeholder="{{ $placeholder }}" @endif
               @error($name) aria-invalid="true" @enderror>
    </div>
    @error($name)
        <div class="error"><i class="bi bi-exclamation-circle" aria-hidden="true"></i>{{ $message }}</div>
    @enderror
</div>
