@extends('layouts.app')

@section('title', 'Rejoindre une session — Béhanzin')
@section('meta_desc', 'Rejoignez une session de jeu avec votre pseudo et le code transmis par le maître de jeu.')

@section('content')
<div class="auth">
    <div class="auth__box">

        {{-- Emblème --}}
        <div class="auth__emblem">
            <div class="auth__emblem-icon">B</div>
            <h1 class="auth__title">Rejoindre la session</h1>
            <p class="auth__subtitle">Entrez votre pseudo de conseiller royal</p>
        </div>

        {{-- Erreurs globales --}}
        @if ($errors->any())
            <div class="alert alert--error">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Message flash info --}}
        @if (session('info'))
            <div class="alert alert--info">
                {{ session('info') }}
            </div>
        @endif

        {{-- Formulaire --}}
        <form action="{{ route('auth.join.post') }}" method="POST" novalidate>
            @csrf

            <div class="form-group">
                <label for="pseudo" class="form-label">Pseudo</label>
                <input
                    type="text"
                    id="pseudo"
                    name="pseudo"
                    class="form-input @error('pseudo') form-input--error @enderror"
                    placeholder="Ex : Aropanou"
                    value="{{ old('pseudo') }}"
                    maxlength="32"
                    autocomplete="nickname"
                    autofocus
                    required
                >
                @error('pseudo')
                    <span class="form-error">{{ $message }}</span>
                @enderror
                <span class="form-hint">Entre 2 et 32 caractères. Lettres, chiffres, tirets acceptés.</span>
            </div>

            <div class="form-group">
                <label for="code" class="form-label">Code de session</label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    class="form-input @error('code') form-input--error @enderror"
                    placeholder="Ex : DANHOME"
                    value="{{ old('code') }}"
                    maxlength="8"
                    autocomplete="off"
                    style="letter-spacing: 0.25em; text-transform: uppercase; font-family: 'Cinzel Decorative', serif;"
                    required
                >
                @error('code')
                    <span class="form-error">{{ $message }}</span>
                @enderror
                <span class="form-hint">Le code vous est transmis par le maître de jeu.</span>
            </div>

            <button type="submit" class="btn btn--primary btn--full mt-3">
                Entrer dans le royaume <span class="btn__arrow">›</span>
            </button>
        </form>

        {{-- Séparateur --}}
        <div class="form-divider mt-3">ou</div>

        {{-- Liens alternatifs --}}
        <div class="auth__footer">