@extends('layouts.app')
@section('title', 'Dashboard MJ — ' . $session->code . ' — Béhanzin')
@section('content')

<div class="admin">

    <div class="admin__header">
        <p class="admin__eyebrow">Maître de jeu &middot; {{ $session->master_name }}</p>
        <h1 class="admin__title">Session {{ $session->code }}</h1>
        <p class="admin__subtitle">
            Tableau de bord — supervision en temps réel.
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="alert">{{ session('info') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert--error">{{ $errors->first() }}</div>
    @endif

    {{-- État global --}}
    <div class="admin__card">
        <div class="admin__card-title">État de la session</div>

        <div class="admin__info-row">
            <div class="admin__info-item">
                <div class="admin__info-label">Code</div>
                <div class="admin__info-value">{{ $session->code }}</div>
            </div>
            <div class="admin__info-item">
                <div class="admin__info-label">Statut</div>
                <div class="admin__info-value">
                    @php
                        $statusLabels = [
                            'waiting'  => ['En attente',  'badge--active'],
                            'active'   => ['En cours',    'badge--active'],
                            'finished' => ['Terminée',    'badge'],
                        ];
                        [$lbl, $cls] = $statusLabels[$session->status] ?? [$session->status, 'badge'];
                    @endphp
                    <span class="badge {{ $cls }}">{{ $lbl }}</span>
                </div>
            </div>
            <div class="admin__info-item">
                <div class="admin__info-label">Joueurs</div>
                <div class="admin__info-value">
                    {{ $players->where('is_active', true)->count() }} / {{ $session->max_players }}
                </div>
            </div>
            <div class="admin__info-item">
                <div class="admin__info-label">Votes (scène en cours)</div>
                <div class="admin__info-value">
                    {{ $votedCount }} / {{ $players->where('is_active', true)->count() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Scène en cours --}}
    <div class="admin__card">
        <div class="admin__card-title">Scène courante</div>
        @if($scene)
            <p style="font-size:0.72rem; letter-spacing:0.2em; text-transform:uppercase;
                      color:var(--gold); margin-bottom:0.4rem;">
                Acte {{ $scene->act }} &middot; {{ $scene->year }}
                @if($scene->is_ending)
                    &middot; <span style="color:var(--text-dim);">Scène finale ({{ $scene->ending_type }})</span>
                @endif
            </p>
            <h2 style="font-family:'Cinzel Decorative',serif; margin:0 0 0.8rem;">
                {{ $scene->title }}
            </h2>
            @if($scene->question)
                <p style="color:var(--text-muted); font-style:italic;">
                    « {{ $scene->question }} »
                </p>
            @endif
        @else
            <p style="color:var(--text-dim);">Aucune scène en cours (la session n'a pas encore commencé).</p>
        @endif

        <div style="display:flex; gap:0.8rem; flex-wrap:wrap; margin-top:1.5rem;">
            @if($session->status !== 'finished')
                <form action="{{ route('admin.dashboard.advance', [$session->code, $token]) }}"
                      method="POST" style="display:inline;"
                      onsubmit="return confirm('Forcer le passage à la scène suivante ?\nLe choix gagnant sera celui qui a le plus de votes (ou le premier choix si personne n\'a voté).');">
                    @csrf
                    <button type="submit" class="btn btn--gold">
                        Forcer la scène suivante <span class="btn__arrow">›</span>
                    </button>
                </form>

                <form action="{{ route('admin.dashboard.end', [$session->code, $token]) }}"
                      method="POST" style="display:inline;"
                      onsubmit="return confirm('Terminer la session maintenant ?\nLes joueurs seront redirigés vers les résultats.');">
                    @csrf
                    <button type="submit" class="btn btn--outline">
                        Terminer la session
                    </button>
                </form>
            @else
                <a href="{{ route('game.results', $session->code) }}" class="btn btn--primary">
                    Voir les résultats
                </a>
            @endif

            <button type="button" class="btn btn--outline" onclick="location.reload()">
                Rafraîchir
            </button>
        </div>
    </div>

    {{-- Joueurs --}}
    <div class="admin__card">
        <div class="admin__card-title">Conseillers ({{ $players->count() }})</div>

        @if($players->isEmpty())
            <p style="color:var(--text-dim);">Aucun joueur n'a encore rejoint.</p>
        @else
            <div style="display:grid; gap:0.6rem;">
                @foreach($players as $p)
                    @php
                        $hasVoted = $scene
                            ? $p->hasVotedOnScene($scene->id, $session->id)
                            : false;
                    @endphp
                    <div style="display:flex; justify-content:space-between; align-items:center;
                                padding:0.7rem 1rem; background:var(--bg-elevated);
                                border:1px solid var(--border); border-radius:6px;">
                        <div>
                            <strong>{{ $p->pseudo }}</strong>
                            <span style="color:var(--text-dim); font-size:0.78rem; margin-left:0.6rem;">
                                @if(!$p->is_active) inactif
                                @elseif($hasVoted) ✓ a voté
                                @else en attente
                                @endif
                            </span>
                        </div>

                        @if($p->is_active && $session->status !== 'finished')
                        <form action="{{ route('admin.dashboard.kick', [$session->code, $token]) }}"
                              method="POST"
                              onsubmit="return confirm('Désactiver ce joueur ?\nIl ne sera plus compté dans le quorum des votes.');">
                            @csrf
                            <input type="hidden" name="player_id" value="{{ $p->id }}">
                            <button type="submit" class="btn btn--outline" style="font-size:0.75rem; padding:0.4rem 0.9rem;">
                                Désactiver
                            </button>
                        </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- URL MJ rappelée --}}
    <div class="admin__card">
        <div class="admin__card-title">Lien d'accès au dashboard</div>
        <p style="font-size:0.85rem; color:var(--text-muted);">
            Conservez ce lien — c'est votre clé d'accès au dashboard MJ.
        </p>
        <div style="word-break:break-all; font-family:monospace; font-size:0.8rem;
                    padding:0.8rem 1rem; background:var(--bg-elevated);
                    border:1px solid var(--border); border-radius:4px;">
            {{ url()->current() }}
        </div>
    </div>

    <div style="text-align:center; margin-top:2rem;">
        <a href="{{ route('admin.create') }}" class="btn btn--outline">
            Créer une nouvelle session
        </a>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Auto-refresh toutes les 8s tant que la session n'est pas finie
    @if($session->status !== 'finished')
    setTimeout(function(){ location.reload(); }, 8000);
    @endif
</script>
@endpush
