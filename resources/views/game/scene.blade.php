@extends('layouts.app')

@section('title', $scene->title . ' — Béhanzin')
@section('meta_desc', 'Acte ' . $scene->act . ' · ' . $scene->year . ' · ' . $scene->title)
@section('body_class', 'game-body')

@section('content')
<div class="game">

    {{-- ===================== EN-TÊTE DE SCÈNE ===================== --}}
    <header class="scene__header">
        <p class="scene__eyebrow">
            Acte {{ $scene->act }} &middot; {{ $scene->act_title }} &middot; {{ $scene->year }}
        </p>
        <h1 class="scene__title">{{ $scene->title }}</h1>

        {{-- Barre de progression --}}
        <div class="progress" aria-label="Progression de l'histoire">
            <div class="progress__bar" style="width: {{ $progressPercent }}%"></div>
            <span class="progress__label">
                Scène {{ $sceneIndex }} / {{ $totalScenes }}
                &middot; {{ $progressPercent }}%
            </span>
        </div>
    </header>

    {{-- ===================== CITATION ===================== --}}
    @if($scene->quote)
    <blockquote class="scene__quote">
        <p class="scene__quote-text">{{ $scene->quote }}</p>
        @if($scene->quote_author)
            <cite class="scene__quote-author">— {{ $scene->quote_author }}</cite>
        @endif
    </blockquote>
    @endif

    {{-- ===================== CORPS NARRATIF ===================== --}}
    <article class="scene">
        <div class="scene__content">
            {!! nl2br(e($scene->content)) !!}
        </div>

        {{-- Ornement de séparation --}}
        <div class="ornament my-3"><span class="ornament__icon">◆ ◆ ◆</span></div>

        {{-- ===================== ZONE DE VOTE / ATTENTE ===================== --}}
        @if($scene->is_ending)
            {{-- Scène finale : pas de vote, redirection --}}
            <div class="vote">
                <div class="vote__title">L'Histoire s'achève…</div>
                <p class="vote__question">
                    Vous voici parvenus au terme du récit. Direction la salle des résultats.
                </p>
                <a href="{{ route('game.results', $session->code) }}" class="btn btn--primary btn--full">
                    Voir le destin du Danhomè <span class="btn__arrow">›</span>
                </a>
            </div>

        @elseif($hasVoted)
            {{-- A déjà voté : message d'attente + liste des joueurs --}}
            <div class="vote">
                <div class="vote__title">Votre vote a été enregistré</div>
                <p class="vote__question">
                    Patientez pendant que les autres conseillers délibèrent.
                    La scène avancera dès que tous auront voté.
                </p>

                <div class="vote__status">
                    <span class="vote__status-text">Votes enregistrés</span>
                    <span class="vote__status-count">
                        {{ $votedCount }} / {{ $session->players->count() }}
                    </span>
                </div>

                <div class="mt-3">
                    <p style="font-size:0.72rem; letter-spacing:0.2em; text-transform:uppercase;
                              color:var(--text-dim); margin-bottom:0.8rem;">
                        Conseillers
                    </p>
                    <div class="players-list">
                        @foreach($session->players as $p)
                            @php $pv = $p->hasVotedOnScene($scene->id, $session->id); @endphp
                            <span class="player-chip {{ $pv ? 'player-chip--voted' : '' }}">
                                <span class="player-chip__dot"></span>
                                {{ $p->pseudo }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

        @else
            {{-- FORMULAIRE DE VOTE --}}
            <div class="vote">
                <div class="vote__title">Le Conseil du Roi</div>
                <p class="vote__question">{{ $scene->question }}</p>

                <form action="{{ route('game.vote', [$session->code, $scene->id]) }}"
                      method="POST"
                      id="voteForm">
                    @csrf

                    <div class="vote__choices">
                        @foreach($scene->choices as $choice)
                            <div class="vote__choice">
                                <input type="radio"
                                       name="choice_id"
                                       id="choice_{{ $choice->id }}"
                                       value="{{ $choice->id }}"
                                       required>
                                <label for="choice_{{ $choice->id }}" class="vote__choice-label">
                                    <span class="vote__choice-letter">
                                        {{ chr(64 + $loop->iteration) }}
                                    </span>
                                    <span class="vote__choice-text">{{ $choice->text }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    @if($errors->has('choice_id'))
                        <div class="alert alert--error mb-2">
                            {{ $errors->first('choice_id') }}
                        </div>
                    @endif

                    <button type="submit" class="btn btn--primary btn--full" id="voteSubmit">
                        Soumettre mon vote au Conseil <span class="btn__arrow">›</span>
                    </button>
                </form>

                <div class="vote__status mt-2">
                    <span class="vote__status-text">
                        Votes enregistrés pour cette scène
                    </span>
                    <span class="vote__status-count">
                        {{ $votedCount }} / {{ $session->players->count() }}
                    </span>
                </div>

                <div class="players-list mt-2">
                    @foreach($session->players as $p)
                        @php $pv = $p->hasVotedOnScene($scene->id, $session->id); @endphp
                        <span class="player-chip {{ $pv ? 'player-chip--voted' : '' }}">
                            <span class="player-chip__dot"></span>
                            {{ $p->pseudo }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Ornement de fin --}}
        <div class="ornament mt-4"><span class="ornament__icon">◆</span></div>

    </article>{{-- /.scene --}}
</div>{{-- /.game --}}
@endsection

@push('scripts')
<script>
    /**
     * Anti-double-submit : empêche un clic multiple qui spam la route /vote.
     */
    (function(){
        var form = document.getElementById('voteForm');
        var btn  = document.getElementById('voteSubmit');
        if(form && btn){
            form.addEventListener('submit', function(){
                btn.disabled = true;
                btn.innerHTML = 'Envoi en cours…';
            });
        }
    })();

    /**
     * Auto-refresh : si le joueur a voté (ou si la scène est terminale),
     * on poll toutes les 5s pour détecter quand tous ont voté et rediriger.
     */
    @if($hasVoted && !$scene->is_ending)
    (function(){
        var checkUrl = "{{ route('game.check', [$session->code, $scene->id]) }}";
        var interval = setInterval(function(){
            fetch(checkUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(function(r){ return r.ok ? r.json() : null; })
            .then(function(data){
                if(!data) return;
                if(data.advanced){
                    clearInterval(interval);
                    window.location.href = data.next_url;
                    return;
                }
                if(data.ended){
                    clearInterval(interval);
                    window.location.href = data.results_url;
                    return;
                }
                if(typeof data.voted_count !== 'undefined'){
                    document.querySelectorAll('.vote__status-count').forEach(function(el){
                        el.textContent = data.voted_count + ' / {{ $session->players->count() }}';
                    });
                }
            })
            .catch(function(){ /* on continue en silence */ });
        }, 5000);
    })();
    @endif
</script>
@endpush
