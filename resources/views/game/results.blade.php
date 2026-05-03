@extends('layouts.app')
@section('title', 'Fin — ' . $session->ending_title . ' — Béhanzin')
@section('body_class', 'results--' . $session->ending_type)

@section('content')
<div class="results">

    @php
        $endingMeta = [
            'resistance' => ['emblem' => '♔', 'label' => '— Résistance Éternelle —'],
            'exile'      => ['emblem' => '✦', 'label' => "— L'Exil du Roi —"],
            'sacrifice'  => ['emblem' => '⚔', 'label' => "— Le Sacrifice d'Abomey —"],
        ];
        $meta = $endingMeta[$session->ending_type] ?? ['emblem' => '✦', 'label' => '— Fin —'];
    @endphp

    <div class="results__emblem">{{ $meta['emblem'] }}</div>

    <p class="results__eyebrow">{{ $meta['label'] }}</p>

    <h1 class="results__title results__title--{{ $session->ending_type }}">
        {{ $session->ending_title }}
    </h1>

    <p class="results__subtitle">{{ $session->ending_subtitle }}</p>

    <hr class="divider--gold divider">

    <div class="results__text">
        {!! nl2br(e($session->ending_text)) !!}
    </div>

    {{-- Statistiques --}}
    <div class="results__stats">
        <div class="results__stat">
            <span class="results__stat-number">{{ $totalScenes }}</span>
            <span class="results__stat-label">Scènes traversées</span>
        </div>
        <div class="results__stat">
            <span class="results__stat-number">{{ $totalPlayers }}</span>
            <span class="results__stat-label">Conseillers royaux</span>
        </div>
        <div class="results__stat">
            <span class="results__stat-number">{{ $totalVotes }}</span>
            <span class="results__stat-label">Votes exprimés</span>
        </div>
    </div>

    {{-- Récapitulatif des choix --}}
    <div class="admin__card" style="text-align:left; margin-top:2rem;">
        <div class="admin__card-title">Récapitulatif des décisions du Conseil</div>

        @foreach($decisions as $decision)
        <div style="padding:1rem 0; border-bottom:1px solid var(--border);">
            <p style="font-size:0.72rem; letter-spacing:0.2em; text-transform:uppercase;
                      color:var(--gold); margin-bottom:0.4rem;">
                Scène {{ $loop->iteration }} &middot; {{ $decision['scene_title'] }}
            </p>
            <p style="color:var(--text-muted); font-size:0.9rem; margin:0;">
                <strong style="color:var(--text);">Choix majoritaire :</strong>
                {{ $decision['winning_choice'] }}
                <span style="color:var(--text-dim); font-size:0.8rem;">
                    ({{ $decision['vote_count'] }} vote(s))
                </span>
            </p>
        </div>
        @endforeach
    </div>

    <div class="results__actions">
        <a href="{{ route('welcome') }}" class="btn btn--primary btn--lg">
            Retour à l'accueil
        </a>
        <a href="{{ route('admin.create') }}" class="btn btn--outline">
            Nouvelle session
        </a>
    </div>

    <div class="ornament mt-4">
        <span class="ornament__icon">◆ ◆ ◆</span>
    </div>

    <p style="font-size:0.78rem; color:var(--text-dim); font-style:italic;
              font-family:'Playfair Display',serif; margin-top:1rem;">
        Béhanzin fut exilé à la Martinique en 1894, puis en Algérie en 1906.
        Il mourut à Blida le 10 décembre 1906. Son corps fut rapatrié au Bénin en 1928.
        Sa résistance demeure l'un des symboles les plus forts de la lutte contre la colonisation.
    </p>

</div>
@endsection