@extends('layouts.auth')

@section('title', 'Confirmer le mot de passe')
@section('icon', 'bi-shield-check')
@section('heading', 'Confirmation requise')
@section('intro', 'Pour continuer, confirmez votre mot de passe.')

@section('form')
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        @include('partials.auth.field', ['name' => 'password', 'type' => 'password', 'label' => 'Mot de passe', 'icon' => 'bi-lock', 'autocomplete' => 'current-password', 'autofocus' => true])
        <button type="submit" class="btn-primary btn-lg btn-block">Confirmer <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
    </form>
    @if(Route::has('password.request'))
        <p class="auth-links"><a href="{{ route('password.request') }}">Mot de passe oublié ?</a></p>
    @endif
@endsection
