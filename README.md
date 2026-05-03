# Béhanzin — Histoire(s) Interactive

> Une fresque historique interactive multijoueur autour de la résistance du roi Béhanzin et du royaume du Danhomè face à la colonisation française (1875 – 1906).

Projet pédagogique (programmation web) — thème imposé : **histoire(s)**.

---

## Membres du binôme

- **Delys Boko** 
- **Ndéye Awa Mbodji**

---

## Concept

Le maître de jeu (un·e enseignant·e, un·e animateur·rice) crée une session et transmet un **code** à ses joueurs.
Plusieurs joueurs rejoignent la même session avec leur pseudo. Tous lisent ensemble la même scène historique, puis **votent** chacun pour un choix proposé au roi Béhanzin. La majorité l'emporte et l'histoire avance pour tout le monde, scène après scène, jusqu'à l'une des **trois fins possibles** :

- **La Résistance Éternelle** — Béhanzin se rend en roi, son nom devient mémoire vivante.
- **L'Exil du Roi** — reddition négociée, exil en Martinique puis en Algérie.
- **Le Sacrifice d'Abomey** — disparition mythique dans la forêt.

Une vraie bifurcation narrative existe en Acte III : selon le choix tactique des joueurs (embuscades, élimination du commandement, sabotage du ravitaillement), la scène suivante change réellement.

---

## Fonctionnalités

- Création de session par le maître de jeu (avec code personnalisé optionnel).
- Authentification légère par pseudo (pas de mot de passe), une session = un cookie.
- Vote multijoueur, indicateur en temps réel du nombre de votes reçus.
- Avancement automatique de la scène quand tous les joueurs actifs ont voté.
- **Polling AJAX** (toutes les 5 s) pour mettre à jour la page sans rechargement et rediriger automatiquement à l'avancement.
- **Dashboard maître de jeu** sécurisé par token : voir l'état de la partie, forcer la scène suivante, désactiver un joueur, terminer la session.
- Page de récapitulatif final avec le détail des décisions du Conseil et la fin obtenue.
- Anti-double-vote (index unique en BDD), transaction + `lockForUpdate()` contre les races multi-joueurs.

---

## Stack technique

| Couche | Technologie |
|---|---|
| Backend | **PHP 8.2** + **Laravel 12** |
| Base de données | MySQL ou SQLite (Eloquent ORM) |
| Frontend | **HTML5**, **CSS3** (Flexbox / Grid, BEM, variables CSS, fonts Playfair / Cinzel / Lato) |
| JavaScript | Vanilla JS (Fetch API, manipulation DOM, JSON) |
| Build | Vite + Tailwind 4 |
| Sessions | Cookies HTTP (`Illuminate\Session`) |

### Notions du cours mises en œuvre

- **HTML** : structure sémantique, formulaires multiples, blade.
- **CSS** : layouts complexes (flex / grid), variables, design responsive.
- **JavaScript** : événements, manipulation du DOM, polling AJAX, anti-double-submit.
- **PHP** : génération HTML (Blade), formulaires (avec validation), sessions HTTP, contrôleurs RESTful.
- **AJAX** : route `/jeu/{code}/check/{sceneId}` retournant du JSON, mise à jour du compteur de votes en place.
- **Multi-utilisateurs temps réel** : on voit l'avancement des autres joueurs (compteur, liste de pseudos avec indicateur "a voté"), et la scène avance pour tout le monde dès le quorum.

---

## Architecture du code

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php       Rejoindre / Reconnexion / Déconnexion
│   │   └── GameController.php       Scène / Vote / Polling / Résultats / Dashboard MJ
│   └── Middleware/
│       └── AuthPseudo.php           Protège les routes du jeu
├── Models/
│   ├── GameSession.php              Métier : avancement, lock, ending
│   ├── Player.php
│   ├── Scene.php                    Avec ending_type (resistance|exile|sacrifice)
│   ├── Choice.php                   next_scene_id pointe vers une scène (bifurcation)
│   └── Vote.php
└── Providers/AppServiceProvider.php Directive Blade @auth_pseudo

database/
├── migrations/                      6 migrations (FKs ajoutées séparément à la fin)
└── seeders/StorySeeder.php          11 scènes + bifurcation Acte III + 3 fins

resources/views/
├── layouts/app.blade.php            Layout commun (navbar, footer, flash)
├── welcome.blade.php                Hero d'accueil
├── auth/{join,login}.blade.php
├── game/{scene,results}.blade.php
└── admin/{create,dashboard}.blade.php

routes/web.php                       Toutes les routes
```

---

## Installation

```bash
# 1. Cloner
git clone https://github.com/delysboko-maker/behanzin.git
cd behanzin

# 2. Dépendances
composer install
npm install

# 3. Configuration
cp .env.example .env
php artisan key:generate

# Editer .env si tu veux MySQL.
# Par défaut, SQLite suffit : crée le fichier vide
mkdir -p database && touch database/database.sqlite

# 4. Migrations + seed
php artisan migrate:fresh --seed

# 5. Build frontend
npm run build

# 6. Lancer le serveur
php artisan serve
# → http://127.0.0.1:8000
```

---

## Comment jouer

1. **Créer une session** : aller sur `/admin/creer`, choisir un nom de maître de jeu et un nombre max de joueurs. Noter le **code** + le **lien MJ** (avec token) qui s'affichent.
2. **Rejoindre** : ouvrir `/auth/rejoindre` dans plusieurs fenêtres (idéalement en navigation privée pour avoir des sessions HTTP distinctes), entrer un pseudo + le code de session.
3. **Jouer** : lire la scène, voter, attendre les autres, voir l'histoire avancer.
4. **Superviser (MJ)** : ouvrir le lien MJ dans un navigateur. Suivre l'état en temps réel, forcer la scène suivante si besoin.

---

## Sécurité / robustesse

- Index unique `(player_id, game_session_id, scene_id)` → impossible de voter deux fois.
- `Vote::create()` sous transaction, exception unique attrapée proprement.
- `GameSession::advanceToNextScene()` sous `DB::transaction()` + `lockForUpdate()` → pas de double-avancement même en cas de votes simultanés.
- Token MJ aléatoire (40 caractères) requis pour accéder au dashboard.
- CSRF Laravel sur tous les formulaires.
- Validation côté serveur sur tous les inputs.

---

## Choix de design

- Pseudo seul (sans mot de passe) car le contexte est pédagogique (classe / groupe d'amis), pas un service public.
- Polling toutes les 5 s plutôt que WebSockets : suffisant pour 6-10 joueurs, et beaucoup plus simple à déployer.
- Scénario en BDD (et non en fichiers JSON statiques) pour permettre une future interface d'édition par le MJ.

---

## Licence

Projet pédagogique. Le contenu narratif s'appuie sur des sources historiques publiques.
