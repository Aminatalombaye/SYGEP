@extends('layouts.admin')
@section('content')

@include('partials.form-head', ['module' => 'assetsHistory', 'mode' => 'create', 'index' => 'admin.assets-histories.index'])
<div class="card sy-form">

    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route("admin.assets-histories.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="sy-form-actions">
                <a href="{{ route('admin.assets-histories.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> {{ trans('global.save') }}</button>
            </div>
        </form>
    </div>
</div>



@endsection