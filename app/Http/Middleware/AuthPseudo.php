<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthPseudo
{
    /**
     * Protège les routes du jeu : redirige vers /rejoindre si pas de session active.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('player_id')) {
            return redirect()
                ->route('auth.join')
                ->with('info', 'Vous devez rejoindre une session pour accéder au jeu.');
        }

        return $next($request);
    }
}