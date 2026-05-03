<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

/* ══════════════════════════════════════════
   PAGE D'ACCUEIL
══════════════════════════════════════════ */

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

/* ══════════════════════════════════════════
   AUTHENTIFICATION (pseudo)
══════════════════════════════════════════ */

Route::prefix('auth')->name('auth.')->group(function () {

    Route::get('/rejoindre',  [AuthController::class, 'showJoin'])->name('join');
    Route::post('/rejoindre', [AuthController::class, 'join'])->name('join.post');

    Route::get('/connexion',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login'])->name('login.post');

    Route::post('/deconnexion', [AuthController::class, 'logout'])->name('logout');
});

/* ══════════════════════════════════════════
   JEU (protégé par middleware auth.pseudo)
══════════════════════════════════════════ */

Route::prefix('jeu/{code}')->name('game.')->middleware('auth.pseudo')->group(function () {
    Route::get('/',                  [GameController::class, 'scene'])->name('scene');
    Route::post('/vote/{sceneId}',   [GameController::class, 'vote'])->name('vote');
    Route::get('/check/{sceneId}',   [GameController::class, 'check'])->name('check');
    Route::get('/resultats',         [GameController::class, 'results'])->name('results');
});

/* ══════════════════════════════════════════
   ADMIN — MAÎTRE DE JEU
══════════════════════════════════════════ */

Route::prefix('admin')->name('admin.')->group(function () {

    // Création
    Route::get('/creer',  [GameController::class, 'createSession'])->name('create');
    Route::post('/creer', [GameController::class, 'storeSession'])->name('store');

    // Dashboard sécurisé par token
    Route::get('/session/{code}/{token}',           [GameController::class, 'dashboard'])->name('dashboard');
    Route::post('/session/{code}/{token}/advance',  [GameController::class, 'dashboardAdvance'])->name('dashboard.advance');
    Route::post('/session/{code}/{token}/end',      [GameController::class, 'dashboardEnd'])->name('dashboard.end');
    Route::post('/session/{code}/{token}/kick',     [GameController::class, 'dashboardKick'])->name('dashboard.kick');
});
