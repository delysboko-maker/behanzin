<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Béhanzin — Histoire Interactive')</title>
    <meta name="description" content="@yield('meta_desc', 'Fresque historique interactive multijoueur. Incarnez les conseillers du roi Béhanzin et votez pour le destin du Danhomè.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Cinzel+Decorative:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="@yield('body_class')">

    {{-- ===================== NAVBAR ===================== --}}
    <nav class="navbar">
        <div class="navbar__inner">
            <a href="{{ route('welcome') }}" class="navbar__brand">
                <div class="navbar__logo">B</div>
                <div class="navbar__title">
                    <span class="navbar__name">Béhanzin</span>
                    <span class="navbar__subtitle">Histoire Interactive</span>
                </div>
            </a>

            <div class="navbar__nav">
                @auth_pseudo
                    <span class="navbar__pseudo">{{ session('player_pseudo') }}</span>
                    <form action="{{ route('auth.logout') }}" method="POST" style="display:inline">
                        @csrf
                        <button type="submit" class="navbar__logout">Quitter</button>
                    </form>
                @else
                    <a href="{{ route('auth.login') }}" class="navbar__link">Connexion</a>
                    <a href="{{ route('auth.join') }}"  class="navbar__link navbar__link--cta">Rejoindre</a>
                @endauth_pseudo
            </div>
        </div>
    </nav>

    {{-- ===================== FLASH MESSAGES ===================== --}}
    @if(session('success') || session('error') || session('info'))
    <div class="flash" id="flashContainer">
        @if(session('success'))
            <div class="flash__item flash__item--success">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash__item flash__item--error">✕ {{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="flash__item">◆ {{ session('info') }}</div>
        @endif
    </div>
    <script>
        setTimeout(function(){
            var f = document.getElementById('flashContainer');
            if(f) f.style.display = 'none';
        }, 4000);
    </script>
    @endif

    {{-- ===================== MAIN ===================== --}}
    <main class="main">
        @yield('content')
    </main>

    {{-- ===================== FOOTER ===================== --}}
    <footer class="footer">
        <div class="footer__inner">
            <span class="footer__copy">
                Projet pédagogique &middot; Béhanzin &mdash; Histoire(s) &middot; 2026
            </span>
            <div class="footer__links">
                <a href="{{ route('welcome') }}"  class="footer__link">Accueil</a>
                <a href="{{ route('auth.join') }}" class="footer__link">Rejoindre</a>
                <a href="{{ route('admin.create') }}" class="footer__link">Maître de jeu</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>