@extends('layouts.app')
@section('title', 'Créer une session — Maître de jeu — Béhanzin')

@section('content')
<div class="admin">

    <div class="admin__header">
        <p class="admin__eyebrow">Maître de jeu</p>
        <h1 class="admin__title">Créer une session</h1>
        <p class="admin__subtitle">
            Configurez la partie et transmettez le code à vos joueurs.
        </p>
    </div>

    @if($errors->any())
        <div class="alert alert--error">{{ $errors->first() }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif

    {{-- ===== Formulaire de création ===== --}}
    <div class="admin__card">
        <div class="admin__card-title">Paramètres de la session</div>

        <form action="{{ route('admin.store') }}" method="POST" novalidate>
            @csrf

            <div class="form-group">
                <label for="master_name" class="form-label">Votre nom (maître de jeu)</label>
                <input
                    type="text"
                    id="master_name"
                    name="master_name"
                    class="form-input @error('master_name') form-input--error @enderror"
                    placeholder="Ex : Professeur Adjovi"
                    value="{{ old('master_name') }}"
                    maxlength="60"
                    required
                >
                @error('master_name')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="max_players" class="form-label">Nombre maximum de joueurs</label>
                <select name="max_players" id="max_players" class="form-input">
                    @foreach([2,3,4,5,6,8,10,12,15,20,30] as $n)
                        <option value="{{ $n }}" {{ old('max_players', 6) == $n ? 'selected' : '' }}>
                            {{ $n }} joueurs
                        </option>
                    @endforeach
                </select>
                <span class="form-hint">Recommandé : 4–10 joueurs pour une bonne dynamique.</span>
            </div>

            <div class="form-group">
                <label for="custom_code" class="form-label">
                    Code personnalisé
                    <span style="color:var(--text-dim); font-weight:300;">(optionnel)</span>
                </label>
                <input
                    type="text"
                    id="custom_code"
                    name="custom_code"
                    class="form-input @error('custom_code') form-input--error @enderror"
                    placeholder="Laissez vide pour un code automatique"
                    value="{{ old('custom_code') }}"
                    maxlength="8"
                    autocomplete="off"
                    style="letter-spacing:0.2em; text-transform:uppercase; font-family:'Cinzel Decorative',serif;"
                >
                @error('custom_code')<span class="form-error">{{ $message }}</span>@enderror
                <span class="form-hint">3 à 8 lettres/chiffres. Ex : REQUIN, ABOMEY…</span>
            </div>

            <button type="submit" class="btn btn--gold btn--full mt-3">
                Créer la session <span class="btn__arrow">›</span>
            </button>
        </form>
    </div>

    {{-- ===== Code généré après création ===== --}}
    @if(session('generated_code'))
    <div class="admin__card" style="border-color:var(--gold-dark);">
        <div class="admin__card-title" style="color:var(--gold);">✓ Session créée avec succès !</div>

        <p style="font-size:0.9rem; color:var(--text-muted); margin-bottom:1rem;">
            Transmettez ce code à vos joueurs. Ils pourront rejoindre la session via
            <a href="{{ route('auth.join') }}">la page Rejoindre</a>.
        </p>

        <div class="admin__generated-code">
            <span class="admin__code-value" id="sessionCode">
                {{ session('generated_code') }}
            </span>
            <button class="admin__copy-btn" onclick="copyCode()">
                Copier
            </button>
        </div>

        <div class="admin__info-row mt-3">
            <div class="admin__info-item">
                <div class="admin__info-label">Maître de jeu</div>
                <div class="admin__info-value">{{ session('master_name') }}</div>
            </div>
            <div class="admin__info-item">
                <div class="admin__info-label">Max joueurs</div>
                <div class="admin__info-value">{{ session('max_players') }}</div>
            </div>
            <div class="admin__info-item">
                <div class="admin__info-label">Statut</div>
                <div class="admin__info-value">
                    <span class="badge badge--active">En attente</span>
                </div>
            </div>
        </div>

        @if(session('master_url'))
        <div style="margin-top:2rem; padding:1.2rem; border:1px solid var(--gold-dark);
                    border-radius:6px; background:var(--bg-elevated);">
            <div style="font-size:0.72rem; letter-spacing:0.2em; text-transform:uppercase;
                        color:var(--gold); margin-bottom:0.6rem;">
                Lien MJ — à conserver
            </div>
            <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:0.8rem;">
                Ce lien (avec son token secret) vous donne accès au dashboard de supervision.
                Notez-le, il ne sera plus jamais réaffiché.
            </p>
            <div style="display:flex; gap:0.6rem; align-items:stretch;">
                <input type="text"
                       id="masterUrl"
                       value="{{ session('master_url') }}"
                       readonly
                       class="form-input"
                       style="font-family:monospace; font-size:0.78rem;">
                <button type="button" class="btn btn--outline" onclick="copyMasterUrl()">
                    Copier
                </button>
            </div>
            <a href="{{ session('master_url') }}" class="btn btn--gold mt-2">
                Ouvrir le dashboard MJ <span class="btn__arrow">›</span>
            </a>
        </div>
        @endif

        <div style="margin-top:2rem; display:flex; gap:1rem; flex-wrap:wrap;">
            <a href="{{ route('auth.join') }}" class="btn btn--primary">
                Rejoindre comme joueur
            </a>
            <a href="{{ route('admin.create') }}" class="btn btn--outline">
                Créer une autre session
            </a>
        </div>
    </div>
    @endif

    {{-- ===== Aide ===== --}}
    <div class="admin__card">
        <div class="admin__card-title">Comment ça marche ?</div>
        <ol style="padding-left:1.4rem; color:var(--text-muted); font-size:0.9rem; line-height:2;">
            <li>Créez la session et notez le code.</li>
            <li>Partagez le code avec vos joueurs (sur écran, tableau, chat…).</li>
            <li>Chaque joueur rejoint avec un pseudo + le code.</li>
            <li>L'histoire commence à la première scène. Tous lisent ensemble.</li>
            <li>Chacun vote pour un choix. Quand tout le monde a voté, la majorité l'emporte.</li>
            <li>L'histoire avance jusqu'à l'une des deux fins : <em>Exil</em> ou <em>Résistance Éternelle</em>.</li>
        </ol>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.getElementById('custom_code') &&
    document.getElementById('custom_code').addEventListener('input', function(){
        this.value = this.value.toUpperCase();
    });

    function copyCode(){
        var code = document.getElementById('sessionCode').textContent.trim();
        copyText(code, 'Code copié : ' + code);
    }

    function copyMasterUrl(){
        var input = document.getElementById('masterUrl');
        if(!input) return;
        copyText(input.value, 'Lien MJ copié dans le presse-papier.');
    }

    function copyText(text, message){
        if(navigator.clipboard){
            navigator.clipboard.writeText(text).then(function(){ alert(message); });
        } else {
            var ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            alert(message);
        }
    }
</script>
@endpush