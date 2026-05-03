@extends('layouts.app')

@section('title', 'Béhanzin — Histoire Interactive · Accueil')
@section('meta_desc', 'Plongez dans le Danhomè de 1890. Fresque historique multijoueur autour du roi Béhanzin.')

@section('content')

{{-- =================== HERO =================== --}}
<section class="hero">
    <div class="hero__bg--fallback"></div>

    <p class="hero__eyebrow">Royaume du Danhomè &middot; 1875 — 1906</p>

    <h1 class="hero__title">
        Béhanzin —<br>
        <em>l'Histoire qui se réécrit</em>
    </h1>

    <p class="hero__tagline">
        « Le requin trouble les eaux de la mer. »
    </p>

    <p class="hero__desc">
        Une fresque historique interactive. Plusieurs joueurs, une même histoire :
        rejoignez une session, lisez les scènes, votez pour les choix du roi.
        Le destin du royaume dépend de la majorité.
    </p>

    <div class="hero__actions">
        <a href="{{ route('auth.join') }}" class="btn btn--primary btn--lg">
            Commencer l'aventure <span class="btn__arrow">›</span>
        </a>
        <a href="{{ route('auth.login') }}" class="btn btn--outline btn--lg">