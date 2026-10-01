@extends('layouts.auth')

@section('title', 'Mot de passe oublié')
@section('icon', 'bi-key')
@section('heading', 'Mot de passe oublié')
@section('intro', 'Indiquez votre adresse e-mail : vous recevrez un lien pour choisir un nouveau mot de passe.')

@section('form')
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        @include('partials.auth.field', ['name' => 'email', 'type' => 'email', 'label' => 'Adresse e-mail', 'icon' => 'bi-envelope', 'autocomplete' => 'email', 'autofocus' => true])
        <button type="submit" class="btn-primary btn-lg btn-block">Envoyer le lien <i class="bi bi-send" aria-hidden="true"></i></button>
    </form>
    <p class="auth-links"><a href="{{ route('login') }}"><i class="bi bi-arrow-left"></i> Retour à la connexion</a></p>
@endsection
