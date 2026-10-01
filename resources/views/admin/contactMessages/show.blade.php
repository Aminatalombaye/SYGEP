@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} message de contact
    </div>

    <div class="card-body">
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.contact-messages.index') }}"><i class="bi bi-arrow-left"></i> {{ trans('global.back_to_list') }}
            </a>
            <a class="btn btn-primary" href="mailto:{{ $contactMessage->email }}?subject={{ rawurlencode('Re : ' . $contactMessage->subject_label) }}">
                <i class="bi bi-reply"></i> Répondre par e-mail
            </a>
        </div>
        <table class="table table-bordered table-striped">
            <tbody>
                <tr><th style="width:200px">Reçu le</th><td>{{ $contactMessage->created_at?->format('Y-m-d H:i') }}</td></tr>
                <tr><th>Nom</th><td>{{ $contactMessage->name }}</td></tr>
                <tr><th>E-mail</th><td><a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></td></tr>
                <tr><th>Téléphone</th><td>{{ $contactMessage->phone ?: '—' }}</td></tr>
                <tr><th>Structure / service</th><td>{{ $contactMessage->structure ?: '—' }}</td></tr>
                <tr><th>Objet</th><td>{{ $contactMessage->subject_label }}</td></tr>
                <tr><th>Message</th><td style="white-space: pre-line">{{ $contactMessage->message }}</td></tr>
                <tr><th>Adresse IP</th><td>{{ $contactMessage->ip_address ?: '—' }}</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
