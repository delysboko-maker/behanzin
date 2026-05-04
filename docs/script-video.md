# Script vidéo — Béhanzin · Histoire(s) Interactive

Durée cible : **6 min 30 – 7 min**.
À enregistrer en partage d'écran (OBS, Loom, ScreenRec, ou l'enregistreur Windows `Win + G`).

## Préparation avant l'enregistrement

1. `php artisan migrate:fresh --seed` (BDD propre).
2. `php artisan serve` dans un terminal (laisser ouvert).
3. `npm run build` (si pas déjà fait).
4. Préparer **4 fenêtres de navigation privée** côte à côte :
   - **Fenêtre 1 (MJ)** : `http://127.0.0.1:8000`
   - **Fenêtre 2 (Joueur "ganix")** : page d'accueil aussi
   - **Fenêtre 3 (Joueur "delys")** : page d'accueil aussi
   - **Fenêtre 4 (Joueur "awa")** : page d'accueil aussi
5. Avoir un éditeur (VS Code) ouvert avec quelques fichiers clés à montrer rapidement.

---

## ⏱ 0:00 – 0:30 · Intro & présentation du concept

**À l'écran :** la page d'accueil (`/`) plein écran, joli hero avec le titre "Béhanzin — l'Histoire qui se réécrit".

**Voix off :**
> « Bonjour, je suis Delys Boko et avec ma binôme Ndéye Awa Mbodji nous présentons **Béhanzin — Histoire(s) Interactive**, un projet web pédagogique construit sur le thème "histoires" — au double sens du mot : c'est à la fois de l'**Histoire** avec un grand H, celle du roi Béhanzin et de la résistance du Danhomè face à la France entre 1875 et 1906, et des **histoires** au pluriel, parce que chaque session de jeu produit son propre récit. »
>
> « Le principe : un maître de jeu crée une session, plusieurs joueurs la rejoignent avec un pseudo, lisent ensemble une scène historique, et **votent** chacun un choix proposé au roi. La majorité l'emporte, l'histoire avance, jusqu'à l'une des trois fins possibles. »

---

## ⏱ 0:30 – 1:30 · Le maître de jeu crée une session

**À l'écran :** Fenêtre 1 (MJ). Cliquer sur "Maître de jeu" en bas, ou aller sur `/admin/creer`.

**Actions :**
1. Remplir le formulaire :
   - Nom du maître de jeu : `Prof. Adjovi`
   - Nombre max de joueurs : `8`
   - Code personnalisé : `REQUIN42` (optionnel)
2. Cliquer "Créer la session".

**Voix off :**
> « On commence par créer une session. Le formulaire valide tout côté serveur — je peux te le montrer dans le contrôleur — et génère deux choses importantes : un **code public** que je vais transmettre aux joueurs, ici REQUIN42, et un **lien MJ secret** avec un token aléatoire de 40 caractères qui me donnera accès au dashboard de supervision. »

**Action :** copier le code (bouton "Copier"), copier le lien MJ.

**Voix off :**
> « Je copie le lien MJ — sans ce token, impossible d'accéder au dashboard, même en connaissant le code de la session. »

---

## ⏱ 1:30 – 2:30 · Trois joueurs rejoignent la session

**À l'écran :** basculer sur la fenêtre 2 (privée). Aller sur `/auth/rejoindre`.

**Actions fenêtre 2 :**
- Pseudo : `ganix`
- Code : `REQUIN42`
- Submit → arrive directement sur la première scène ("La Conférence de Berlin").

**Voix off :**
> « ganix rejoint avec le code. La session HTTP de Laravel garde son identité dans un cookie. Notez que je suis en navigation **privée** : chaque fenêtre privée a son propre cookie, ce qui me permet d'avoir 4 identités distinctes sur la même machine. »

**Action fenêtre 3 :** même chose, pseudo `delys`.
**Action fenêtre 4 :** même chose, pseudo `awa`.

**Voix off :**
> « delys et awa rejoignent à leur tour. Sur chaque page de scène, je vois en bas la liste des conseillers avec un point gris pour ceux qui n'ont pas encore voté, et un point doré dès qu'ils auront voté. »

---

## ⏱ 2:30 – 3:30 · Un premier vote, et la magie de l'AJAX

**À l'écran :** mettre les 3 fenêtres joueurs côte à côte (split-screen).

**Action fenêtre 2 (ganix) :** cocher un choix (par exemple "Renforcer immédiatement l'armée"), cliquer "Soumettre".

**Voix off :**
> « ganix vote. Tu vois sa page change : elle affiche "Votre vote a été enregistré, patientez". Et regarde maintenant les pages des deux autres joueurs… »

**À l'écran :** pointer la liste des joueurs en bas des fenêtres 3 et 4. Le pseudo "ganix" passe en doré, le compteur passe à `1 / 3`.

**Voix off :**
> « Sans recharger les pages des autres joueurs, leur compteur passe à 1 sur 3, et le pseudo ganix s'illumine. C'est de l'**AJAX en polling** : toutes les 5 secondes, leur navigateur envoie une requête à `/jeu/REQUIN42/check/1` qui renvoie du JSON. Je peux te le montrer dans la console : F12, onglet Réseau… »

**À l'écran :** ouvrir DevTools (F12), onglet Network, filtrer "check", attendre quelques secondes, montrer la requête JSON et son contenu.

**Voix off :**
> « Voilà les requêtes qui partent toutes les 5 secondes, et la réponse JSON qui contient `voted_count`, `advanced`, et `ended`. C'est avec ça que la page se met à jour en place. »

---

## ⏱ 3:30 – 4:30 · La scène avance pour tout le monde

**Actions fenêtres 3 et 4 :** delys et awa votent (n'importe quel choix).

**Voix off :**
> « delys vote… puis awa vote, et là, dès que le quorum est atteint — tous les joueurs actifs ont voté — la scène avance **automatiquement** pour les trois joueurs en même temps, sans que personne n'ait à recharger. »

**À l'écran :** les trois fenêtres redirigent vers la scène 2 ("Les Guerrières du Roi"). Pointer la barre de progression qui passe de "Scène 1/9" à "Scène 2/9".

**Voix off :**
> « Côté serveur, c'est protégé contre les races multi-joueurs : la méthode `advanceToNextScene` est dans une transaction avec un `lockForUpdate` sur la session. Même si trois votes arrivent dans la même milliseconde, l'avancement n'est exécuté qu'une seule fois. »

**À l'écran rapide :** ouvrir VS Code, montrer 5 secondes la méthode `advanceToNextScene` dans `app/Models/GameSession.php` avec le `lockForUpdate()`.

---

## ⏱ 4:30 – 5:30 · La vraie bifurcation Acte III + tentatives bloquées

**Actions :** continuer à voter sur les scènes 2-7 rapidement (je peux accélérer la vidéo en post-production sur cette partie).

**Voix off (pendant l'accélération) :**
> « On enchaîne quelques scènes — la lettre au président, l'incident de Cotonou, l'arrivée du colonel Dodds. Les joueurs continuent de voter. »

**Action critique scène 7 ("L'Arrivée du Colonel Dodds") :** faire voter majoritairement le **choix 2** ("Cibler les officiers : tireurs d'élite") — c'est-à-dire 2 joueurs sur 3 votent ce choix.

**Voix off :**
> « Maintenant attention : la scène suivante n'est **pas la même** selon le choix de la majorité. C'est ma vraie bifurcation. Si on avait choisi les embuscades nocturnes, on irait sur "La Nuit de Cana" — la scène historique classique. Mais comme on a voté pour cibler les officiers… »

**À l'écran :** la scène suivante apparaît : **"Les Tireurs de l'Aube"**, avec un récit différent.

**Voix off :**
> « …on tombe sur "Les Tireurs de l'Aube", une scène que j'ai écrite spécialement, qui raconte une stratégie alternative et qui mène à des conséquences différentes. »

**Tentatives bloquées (rapide, ~30 s) :**

**Action 1 :** dans la fenêtre 2, cliquer plusieurs fois rapidement le bouton "Soumettre mon vote" (sur une scène où on n'a pas encore voté).

**Voix off :**
> « Anti double-submit : le bouton se désactive dès le premier clic, et même si une seconde requête arrivait quand même, l'**index unique** en BDD sur `(player_id, session_id, scene_id)` la rejetterait. »

**Action 2 :** ouvrir un nouvel onglet incognito, aller sur `/auth/rejoindre`, essayer le pseudo `ganix` avec le même code → message d'erreur.

**Voix off :**
> « Pseudo déjà pris dans la session : refusé. »

**Action 3 :** essayer de saisir un code bidon `XXXX` → message d'erreur.

**Voix off :**
> « Code de session inexistant : refusé aussi. »

**Action 4 :** dans la barre d'URL, taper `http://127.0.0.1:8000/admin/session/REQUIN42/AAAAAAAAA` (faux token).

**Voix off :**
> « Et si quelqu'un connaît le code mais pas le token MJ, le dashboard reste verrouillé et je suis renvoyé vers l'écran de création. »

---

## ⏱ 5:30 – 6:15 · Le dashboard maître de jeu

**À l'écran :** fenêtre 1 (MJ). Aller sur le vrai lien MJ qu'on a copié au début.

**Voix off :**
> « Le maître de jeu, lui, voit son dashboard. Il a une vue temps réel : code, statut, joueurs actifs, scène courante, votes reçus pour la scène en cours. La page se rafraîchit toute seule toutes les 8 secondes. »

**Actions :**
- Pointer chaque section.
- Montrer les boutons "Forcer la scène suivante", "Terminer la session", "Désactiver" en face de chaque joueur.
- Cliquer "Désactiver" sur ganix → confirmation → ganix devient inactif.

**Voix off :**
> « Je peux désactiver un joueur — par exemple s'il a quitté sans se déconnecter et bloque le quorum. Je peux aussi forcer la scène suivante manuellement, ou terminer la session prématurément. »

**Action :** dans la fenêtre 2 (ganix), naviguer ou recharger → il est redirigé vers la page de reconnexion avec un message "Vous avez été désactivé par le maître de jeu".

**Voix off :**
> « Et côté joueur, ganix est instantanément redirigé avec un message clair. »

---

## ⏱ 6:15 – 6:45 · La fin de partie et le récap

**Actions :** revenir à un état de jeu où on enchaîne jusqu'à la scène 10, puis voter le choix C ("Disparaître dans la forêt") pour atteindre la fin **"Le Sacrifice d'Abomey"**.

(Si la vidéo est trop longue, on peut sauter directement à la fin via le bouton "Forcer la scène suivante" du dashboard MJ jusqu'à l'ending.)

**À l'écran :** la page de résultats avec son emblème ⚔, son titre "Le Sacrifice d'Abomey", la citation, le texte narratif final, les 3 statistiques (scènes traversées, conseillers, votes), et le **récapitulatif des décisions** — chaque scène avec le choix majoritaire et le nombre de votes.

**Voix off :**
> « Et voici la fin — Le Sacrifice d'Abomey. Le récap montre exactement quels choix ont été faits par la majorité à chaque scène. Aucune partie ne ressemble à une autre : il y a 3 fins, et parce qu'on a une vraie bifurcation en Acte III, certaines scènes intermédiaires ne sont visitées que par les groupes qui ont fait certains choix. »

---

## ⏱ 6:45 – 7:00 · Conclusion technique

**À l'écran :** retour rapide sur le diagramme d'architecture (`docs/architecture.svg`).

**Voix off :**
> « Côté technique, le projet utilise du **HTML sémantique**, du **CSS** avec Flexbox et Grid, du **JavaScript** vanilla pour le polling et la manipulation du DOM, du **PHP** avec Laravel pour la génération HTML, les formulaires avec validation, les sessions HTTP, et de l'**AJAX** pour la synchronisation temps réel sans WebSockets. La sécurité est assurée par le CSRF de Laravel, des index uniques en base, et des transactions avec verrous. Merci pour votre attention. »

---

## 📋 Checklist post-tournage

- [ ] Vidéo entre 5 et 7 minutes (idéalement 6:30)
- [ ] Format MP4 (H.264 + AAC, 1080p suffit)
- [ ] Voix audible, pas de bruit de fond
- [ ] Tous les bullet points du sujet sont couverts :
  - [ ] HTML
  - [ ] CSS
  - [ ] JavaScript (events, DOM, JSON)
  - [ ] PHP (génération HTML, formulaires, sessions)
  - [ ] AJAX
  - [ ] Multi-utilisateurs temps réel
  - [ ] Tentatives d'actions bloquées (double-vote, pseudo dupliqué, mauvais code, mauvais token)
- [ ] Upload sur YouTube (en non-listé) ou export MP4 dans le dépôt

## 🎬 Astuces pratiques

- **Enregistreur Windows** : `Win + G` ouvre Game Bar, qui peut enregistrer le bureau.
- **OBS Studio** : meilleur contrôle, gratuit, bon pour split-screen.
- **Couper / accélérer** : DaVinci Resolve (gratuit), CapCut, ou Clipchamp (pré-installé sur Windows 11).
- **Taille des fenêtres** : pour 4 fenêtres lisibles, viser `Win+gauche/droite` puis ajuster, ou écrans virtuels Windows pour transitions propres.
- **Si tu fais une erreur en parlant** : continue, on coupera au montage. Ne pas tout recommencer.
