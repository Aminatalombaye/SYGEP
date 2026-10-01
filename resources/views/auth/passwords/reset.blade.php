@extends('layouts.auth')

@section('title', 'Nouveau mot de passe')
@section('icon', 'bi-shield-lock')
@section('heading', 'Nouveau mot de passe')
@section('intro', 'Choisissez un mot de passe d\'au moins 8 caractères.')

@section('form')
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        @include('partials.auth.field', ['name' => 'email', 'type' => 'email', 'label' => 'Adresse e-mail', 'icon' => 'bi-envelope', 'value' => $email ?? request('email'), 'autocomplete' => 'email'])
        @include('partials.auth.field', ['name' => 'password', 'type' => 'password', 'label' => 'Nouveau mot de passe', 'icon' => 'bi-lock', 'autocomplete' => 'new-password', 'autofocus' => true])
        @include('partials.auth.field', ['name' => 'password_confirmation', 'id' => 'password-confirm', 'type' => 'password', 'label' => 'Confirmer le mot de passe', 'icon' => 'bi-lock-fill', 'autocomplete' => 'new-password'])
        <button type="submit" class="btn-primary btn-lg btn-block">Enregistrer le mot de passe <i class="bi bi-check2" aria-hidden="true"></i></button>
    </form>
@endsection
