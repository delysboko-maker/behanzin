@extends('layouts.app')

@section('title', 'Reconnexion — Béhanzin')

@section('content')
<div class="auth">
    <div class="auth__box">

        <div class="auth__emblem">
            <div class="auth__emblem-icon">B</div>
            <h1 class="auth__title">Reconnexion</h1>
            <p class="auth__subtitle">Retrouvez votre place parmi les conseillers</p>
        </div>

        @if ($errors->any())
            <div class="alert alert--error">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert--info">
                {{ session('info') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert--success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('auth.login.post') }}" method="POST" novalidate>
            @csrf

            <div class="form-group">
                <label for="pseudo" class="form-label">Pseudo</label>
                <input
                    type="text"
                    id="pseudo"
                    name="pseudo"
                    class="form-input @error('pseudo') form-input--error @enderror"
                    placeholder="Votre pseudo"
                    value="{{ old('pseudo') }}"
                    maxlength="32"
                    autocomplete="nickname"
                    autofocus
                    required
                >
                @error('pseudo')
                    <span class="form-error">{{ $message }}</span>
                @enderror
                <span class="form-hint">Le pseudo utilisé lors de votre inscription à la session.</span>
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
                    style="letter-spacing:0.25em; text-transform:uppercase; font-family:'Cinzel Decorative',serif;"
                    required
                >
                @error('code')
                    <span class="form-error">{{ $message }}</span>
                @enderror
                <span class="form-hint">Le code de la session en cours.</span>
            </div>

            <button type="submit" class="btn btn--primary btn--full mt-3">
                Me reconnecter <span class="btn__arrow">›</span>
            </button>

        </form>

        <div class="form-divider mt-3">ou</div>

        <div class="auth__footer">
            Nouveau joueur ?
            <a href="{{ route('auth.join') }}">Rejoindre une session</a>
        </div>

        <div class="auth__footer" style="margin-top:0.6rem; border-top:none; padding-top:0;">
            Vous êtes maître de jeu ?
            <a href="{{ route('admin.create') }}">Créer une session</a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    var codeField = document.getElementById('code');
    if (codeField) {
        codeField.addEventListener('input', function () {
            var pos = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(pos, pos);
        });
    }
</script>
@endpush