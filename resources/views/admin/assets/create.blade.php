@extends('layouts.admin')
@section('content')

@include('partials.form-head', ['module' => 'asset', 'mode' => 'create', 'index' => 'admin.assets.index'])
<div class="card sy-form">

    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route("admin.assets.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="category_id">{{ trans('cruds.asset.fields.category') }}</label>
                <select class="form-control select2 {{ $errors->has('category') ? 'is-invalid' : '' }}" name="category_id" id="category_id" required>
                    @foreach($categories as $id => $entry)
                        <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('category'))
                    <div class="invalid-feedback">
                        {{ $errors->first('category') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.asset.fields.category_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="serial_number">{{ trans('cruds.asset.fields.serial_number') }}</label>
                <input class="form-control {{ $errors->has('serial_number') ? 'is-invalid' : '' }}" type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', '') }}">
                @if($errors->has('serial_number'))
                    <div class="invalid-feedback">
                        {{ $errors->first('serial_number') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.asset.fields.serial_number_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="name">{{ trans('cruds.asset.fields.name') }}</label>
                <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', '') }}" required>
                @if($errors->has('name'))
                    <div class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.asset.fields.name_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="photos">{{ trans('cruds.asset.fields.photos') }}</label>
                <div class="needsclick dropzone {{ $errors->has('photos') ? 'is-invalid' : '' }}" id="photos-dropzone">
                </div>
                @if($errors->has('photos'))
                    <div class="invalid-feedback">
                        {{ $errors->first('photos') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.asset.fields.photos_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="status_id">{{ trans('cruds.asset.fields.status') }}</label>
                <select class="form-control select2 {{ $errors->has('status') ? 'is-invalid' : '' }}" name="status_id" id="status_id" required>
                    @foreach($statuses as $id => $entry)
                        <option value="{{ $id }}" {{ old('status_id', $defaultStatusId ?? null) == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('status'))
                    <div class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.asset.fields.status_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="location_id">{{ trans('cruds.asset.fields.location') }}</label>
                <select class="form-control select2 {{ $errors->has('location') ? 'is-invalid' : '' }}" name="location_id" id="location_id" required>
                    @foreach($locations as $id => $entry)
                        <option value="{{ $id }}" {{ old('location_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('location'))
                    <div class="invalid-feedback">
                        {{ $errors->first('location') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.asset.fields.location_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="notes">{{ trans('cruds.asset.fields.notes') }}</label>
                <textarea class="form-control {{ $errors->has('notes') ? 'is-invalid' : '' }}" name="notes" id="notes">{{ old('notes') }}</textarea>
                @if($errors->has('notes'))
                    <div class="invalid-feedback">
                        {{ $errors->first('notes') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.asset.fields.notes_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="type">{{ trans('cruds.asset.fields.type') }}</label>
                <input class="form-control {{ $errors->has('type') ? 'is-invalid' : '' }}" type="text" name="type" id="type" value="{{ old('type', '') }}">
                @if($errors->has('type'))
                    <div class="invalid-feedback">
                        {{ $errors->first('type') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.asset.fields.type_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="date_achat">{{ trans('cruds.asset.fields.date_achat') }}</label>
                <input class="form-control date {{ $errors->has('date_achat') ? 'is-invalid' : '' }}" type="text" name="date_achat" id="date_achat" value="{{ old('date_achat') }}" required>
                @if($errors->has('date_achat'))
                    <div class="invalid-feedback">
                        {{ $errors->first('date_achat') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.asset.fields.date_achat_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="date_mise_en_service">{{ trans('cruds.asset.fields.date_mise_en_service') }}</label>
                <input class="form-control date {{ $errors->has('date_mise_en_service') ? 'is-invalid' : '' }}" type="text" name="date_mise_en_service" id="date_mise_en_service" value="{{ old('date_mise_en_service') }}">
                @if($errors->has('date_mise_en_service'))
                    <div class="invalid-feedback">
                        {{ $errors->first('date_mise_en_service') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.asset.fields.date_mise_en_service_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="modele">{{ trans('cruds.asset.fields.modele') }}</label>
                <input class="form-control {{ $errors->has('modele') ? 'is-invalid' : '' }}" type="text" name="modele" id="modele" value="{{ old('modele', '') }}">
                @if($errors->has('modele'))
                    <div class="invalid-feedback">
                        {{ $errors->first('modele') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.asset.fields.modele_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="fournisseurs">{{ trans('cruds.asset.fields.fournisseur') }}</label>
                <select class="form-control select2 {{ $errors->has('fournisseurs') ? 'is-invalid' : '' }}" name="fournisseurs[]" id="fournisseurs">
                    <option value="">{{ trans('global.pleaseSelect') }}</option>
                    @foreach($fournisseurs as $id => $fournisseur)
                        <option value="{{ $id }}" {{ in_array($id, old('fournisseurs', [])) ? 'selected' : '' }}>{{ $fournisseur }}</option>
                    @endforeach
                </select>
                @if($errors->has('fournisseurs'))
                    <div class="invalid-feedback">
                        {{ $errors->first('fournisseurs') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.asset.fields.fournisseur_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="bons">{{ trans('cruds.asset.fields.bon') }}</label>
                <select class="form-control select2 {{ $errors->has('bons') ? 'is-invalid' : '' }}" name="bons[]" id="bons">
                    <option value="">{{ trans('global.pleaseSelect') }}</option>
                    @foreach($bons as $id => $bon)
                        <option value="{{ $id }}" {{ in_array($id, old('bons', [])) ? 'selected' : '' }}>{{ $bon }}</option>
                    @endforeach
                </select>
                @if($errors->has('bons'))
                    <div class="invalid-feedback">
                        {{ $errors->first('bons') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.asset.fields.bon_helper') }}</span>
            </div>
            <div class="sy-form-actions">
                <a href="{{ route('admin.assets.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> {{ trans('global.save') }}</button>
            </div>
        </form>
    </div>
</div>



@endsection

@section('scripts')
<script>
    var uploadedPhotosMap = {}
Dropzone.options.photosDropzone = {
    url: '{{ route('admin.assets.storeMedia') }}',
    maxFilesize: 2, // MB
    addRemoveLinks: true,
    headers: {
      'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    params: {
      size: 2
    },
    success: function (file, response) {
      $('form').append('<input type="hidden" name="photos[]" value="' + response.name + '">')
      uploadedPhotosMap[file.name] = response.name
    },
    removedfile: function (file) {
      file.previewElement.remove()
      var name = ''
      if (typeof file.file_name !== 'undefined') {
        name = file.file_name
      } else {
        name = uploadedPhotosMap[file.name]
      }
      $('form').find('input[name="photos[]"][value="' + name + '"]').remove()
    },
    init: function () {
@if(isset($asset) && $asset->photos)
          var files =
            {!! json_encode($asset->photos) !!}
              for (var i in files) {
              var file = files[i]
              this.options.addedfile.call(this, file)
              file.previewElement.classList.add('dz-complete')
              $('form').append('<input type="hidden" name="photos[]" value="' + file.file_name + '">')
            }
@endif
    },
     error: function (file, response) {
         if ($.type(response) === 'string') {
             var message = response //dropzone sends it's own error messages in string
         } else {
             var message = response.errors.file
         }
         file.previewElement.classList.add('dz-error')
         _ref = file.previewElement.querySelectorAll('[data-dz-errormessage]')
         _results = []
         for (_i = 0, _len = _ref.length; _i < _len; _i++) {
             node = _ref[_i]
             _results.push(node.textContent = message)
         }

         return _results
     }
}
</script>
@endsection