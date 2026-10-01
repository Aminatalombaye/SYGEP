@extends('layouts.auth')

@section('title', 'Créer un compte')
@section('icon', 'bi-person-plus')
@section('heading', 'Créer un compte')
@section('intro', 'Votre compte devra être validé par un administrateur avant la première connexion.')

@section('form')
    <form method="POST" action="{{ route('register') }}">
        @csrf
        @include('partials.auth.field', ['name' => 'name', 'label' => 'Nom complet', 'icon' => 'bi-person', 'autocomplete' => 'name', 'autofocus' => true])
        @include('partials.auth.field', ['name' => 'email', 'type' => 'email', 'label' => 'Adresse e-mail', 'icon' => 'bi-envelope', 'autocomplete' => 'email'])
        @include('partials.auth.field', ['name' => 'password', 'type' => 'password', 'label' => 'Mot de passe', 'icon' => 'bi-lock', 'autocomplete' => 'new-password'])
        @include('partials.auth.field', ['name' => 'password_confirmation', 'type' => 'password', 'label' => 'Confirmer le mot de passe', 'icon' => 'bi-lock-fill', 'autocomplete' => 'new-password'])
        <button type="submit" class="btn-primary btn-lg btn-block">Créer mon compte <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
    </form>
    <p class="auth-links">Déjà inscrit ? <a href="{{ route('login') }}">Se connecter</a></p>
@endsection
