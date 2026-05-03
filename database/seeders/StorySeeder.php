<?php

namespace Database\Seeders;

use App\Models\Choice;
use App\Models\Scene;
use App\Models\GameSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StorySeeder extends Seeder
{
    public function run(): void
    {
        // Truncate sûr en présence de FKs.
        // On désactive les contraintes le temps de tout vider, puis on les réactive.
        Schema::disableForeignKeyConstraints();
        try {
            DB::table('votes')->truncate();          // dépend de tout
            DB::table('choices')->truncate();        // dépend de scenes
            DB::table('scenes')->truncate();
            // game_sessions et players ne sont pas vidés : ce seeder ne les touche pas.
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        /*
         * ARCHITECTURE DE L'HISTOIRE (avec bifurcation réelle en Acte III)
         * ───────────────────────────────────────────────────────────
         *  ACTE I   — Scènes 1, 2, 3       (L'Éveil du Requin)        (linéaire)
         *  ACTE II  — Scènes 4, 5, 6       (Les Tensions Montent)     (linéaire)
         *  ACTE III — Scène 7
         *               ↓ choix 1  → 8a (Embuscades nocturnes)
         *               ↓ choix 2  → 8b (Élimination du commandement) ★ nouveau
         *               ↓ choix 3  → 8c (Couper le ravitaillement)    ★ nouveau
         *             toutes → Scène 9 (Les Flammes d'Abomey)
         *  ACTE IV  — Scène 10
         *               ↓ choix A  → 11a (FIN — Résistance Éternelle)
         *               ↓ choix B  → 11b (FIN — L'Exil du Roi)
         *               ↓ choix C  → 11c (FIN — Le Sacrifice d'Abomey)
         * ───────────────────────────────────────────────────────────
         */

        // ════════════════════════════════════════════
        // ACTE I — L'ÉVEIL DU REQUIN (1875–1889)
        // ════════════════════════════════════════════

        $s1 = Scene::create([
            'act'        => 1,
            'act_title'  => 'L\'Éveil du Requin',
            'year'       => '1875',
            'sort_order' => 10,
            'title'      => 'La Conférence de Berlin',
            'content'    => "En novembre 1884, les grandes puissances européennes se réunissent à Berlin. Quatorze nations tracent des frontières sur des cartes de l'Afrique sans consulter un seul Africain. Le continent est découpé comme un gâteau de cérémonie.\n\nLe Danhomè — royaume fondé au XVIIe siècle au cœur de l'actuel Bénin — n'est pas représenté. Pourtant, depuis des décennies, ce royaume résiste à toute domination étrangère. Ses guerrières — les Agojié, que les Européens appellent les Amazones — sont redoutées de toute la région.\n\nLe roi Glèlè règne encore. Mais il vieillit. Son fils Aropanou observe, apprend, et attend. Il a compris que la France a des vues sur le port de Cotonou et les revenus des palmeraies. Le temps des négociations touche à sa fin.\n\nEn 1889, Glèlè meurt. Aropanou monte sur le trône et prend le nom de Béhanzin — « l'œuf du monde que la force garde ». Son symbole : le requin. Il ne plie pas.",
            'quote'      => '« Le requin trouble les eaux de la mer. Que la France se souvienne de ses propres tempêtes. »',
            'quote_author' => 'Béhanzin, lettre au gouverneur, 1890',
            'question'   => 'Béhanzin monte sur le trône du Danhomè. Quelle est votre première priorité en tant que conseiller du roi ?',
            'is_ending'  => false,
        ]);

        $s2 = Scene::create([
            'act'        => 1,
            'act_title'  => 'L\'Éveil du Requin',
            'year'       => '1890',
            'sort_order' => 20,
            'title'      => 'Les Guerrières du Roi',
            'content'    => "Le palais d'Abomey bourdonne d'activité. Des milliers de soldats s'entraînent dans la chaleur de la saison sèche. Parmi eux, les Agojié — les épouses guerrières du roi. Elles sont quatre mille, armées de fusils et de machettes, entraînées depuis l'enfance à une discipline de fer.\n\nLeur commandante, Seh-Dong-Hong-Beh, passe les troupes en revue. Elle a participé à vingt batailles. Son regard ne trahit aucune peur.\n\nBéhanzin a choisi de renforcer son armée avant toute chose. Il a acheté des fusils modernes à des marchands allemands et brésiliens, en échange de palmistes et de captifs de guerre. Il sait que la France finira par attaquer. L'heure de préparer la défense est venue.\n\nDes espions reviennent de Cotonou : les Français construisent un fort. Ils prétendent que le traité de 1878 leur donne droit à ce port. Béhanzin conteste : ce traité a été signé par son prédécesseur sous la contrainte. Il est nul.",
            'quote'      => '« Nous sommes les épouses du roi, et nous mourons pour lui. Mais nous mourrons après l\'ennemi. »',
            'quote_author' => 'Seh-Dong-Hong-Beh, commandante des Agojié',
            'question'   => 'Les Français fortifient Cotonou. Quelle réponse conseillez-vous au roi ?',
            'is_ending'  => false,
        ]);

        $s3 = Scene::create([
            'act'        => 1,
            'act_title'  => 'L\'Éveil du Requin',
            'year'       => '1890',
            'sort_order' => 30,
            'title'      => 'La Lettre au Président',
            'content'    => "Béhanzin prend une décision surprenante : il écrit au président de la République française, Sadi Carnot. La lettre est rédigée en français, dictée avec soin. Elle est directe, diplomatique, et ferme.\n\nIl y rappelle les droits souverains du Danhomè, la nullité des traités signés sous contrainte, et la volonté du royaume de vivre en paix — à condition que la France reconnaisse ses frontières. Il propose un accord commercial équitable.\n\nLa lettre ne recevra jamais de réponse officielle. À Paris, on la lit, on s'en amuse en haut lieu, et on la classe. Le gouverneur Bayol, au contraire, accélère les préparatifs militaires.\n\nDans le palais, les conseillers sont divisés. Certains pensent que la diplomatie gagnera du temps. D'autres estiment que chaque jour perdu renforce l'ennemi. Le roi écoute, son regard posé sur la carte du royaume.",
            'quote'      => '« Je ne fais la guerre à personne. Mais si l\'on vient me chercher, on me trouvera. »',
            'quote_author' => 'Béhanzin, 1890',
            'question'   => 'La diplomatie n\'a pas fonctionné. Le roi doit choisir sa stratégie avant l\'inévitable conflit.',
            'is_ending'  => false,
        ]);

        // ════════════════════════════════════════════
        // ACTE II — LES TENSIONS MONTENT (1890–1892)
        // ════════════════════════════════════════════

        $s4 = Scene::create([
            'act'        => 2,
            'act_title'  => 'Les Tensions Montent',
            'year'       => '1890',
            'sort_order' => 40,
            'title'      => 'L\'Incident de Cotonou',
            'content'    => "Avril 1890. Des soldats dahoméens pénètrent dans le périmètre du fort français de Cotonou, revendiquant le sol au nom du roi. Les Français ouvrent le feu. Plusieurs guerriers tombent, dont deux Agojié.\n\nL'incident enflamme le palais d'Abomey. Les jeunes commandants réclament une attaque immédiate. Les anciens conseillers prônent la prudence. Béhanzin, lui, serre les mâchoires et réfléchit.\n\nIl sait que ses troupes — même excellentes — affrontent des fusils Chassepot et des canons. Il sait aussi que reculer serait interprété comme une faiblesse, aussi bien par la France que par ses propres vassaux.\n\nUn émissaire arrive de Porto-Novo : les Français y ont installé un protectorat avec l'accord du roi Tofa. Celui-ci, ennemi de longue date du Danhomè, guide désormais les soldats français dans la région. La trahison vient de toutes parts.",
            'quote'      => '« Tofa a vendu sa dignité pour une poignée de mains de Blancs. Le Danhomè n\'oubliera pas. »',
            'quote_author' => 'Conseiller royal, rapporté par un missionnaire, 1890',
            'question'   => 'Face à l\'incident de Cotonou et à la trahison de Tofa, que doit faire Béhanzin ?',
            'is_ending'  => false,
        ]);

        $s5 = Scene::create([
            'act'        => 2,
            'act_title'  => 'Les Tensions Montent',
            'year'       => '1891',
            'sort_order' => 50,
            'title'      => 'Les Fortifications d\'Abomey',
            'content'    => "L'année 1891 est celle des tranchées et des palissades. Sur ordre du roi, des milliers de paysans creusent des fossés défensifs autour de la capitale. Des murs de terre séchée, renforcés de rondins, transforment Abomey en forteresse.\n\nBéhanzin a également modifié sa stratégie militaire. Plutôt qu'une armée massive en champ ouvert — vulnérable à l'artillerie française — il privilégie les embuscades, les attaques nocturnes, la guérilla dans la forêt dense.\n\nDes instructeurs brésiliens, descendants d'anciens esclaves dahoméens revenus en Afrique, forment des unités à de nouvelles tactiques. Des prisonniers de guerre européens racontent comment fonctionnent les canons. On apprend. On s'adapte.\n\nMais la France, elle aussi, prépare. Le général Alfred-Amédée Dodds — un métis sénégalais devenu officier supérieur — est désigné pour mener la prochaine expédition. Il connaît la brousse. Il connaît les hommes qu'il va affronter.",
            'quote'      => '« Nous nous battrons dans les forêts, dans les marécages, dans la nuit. Jamais en rase campagne. »',
            'quote_author' => 'Stratégie attribuée aux conseillers militaires de Béhanzin',
            'question'   => 'Les préparatifs avancent. Béhanzin doit allouer ses ressources limitées. Quelle priorité choisissez-vous ?',
            'is_ending'  => false,
        ]);

        $s6 = Scene::create([
            'act'        => 2,
            'act_title'  => 'Les Tensions Montent',
            'year'       => '1892',
            'sort_order' => 60,
            'title'      => 'Le Dernier Ultimatum',
            'content'    => "Printemps 1892. Le gouverneur Victor Ballot remet un ultimatum à Béhanzin : reconnaître le protectorat français sur les territoires contestés, cesser tout mouvement de troupes, accepter un résident français à Abomey.\n\nC'est, en réalité, un acte de capitulation déguisé.\n\nBéhanzin réunit son conseil. Les mots sont pesés. Certains conseillers, épuisés de vivre sous la menace constante, suggèrent d'accepter partiellement pour gagner encore du temps. D'autres — les guerriers — veulent répondre par les armes immédiatement.\n\nLe roi regarde ses hommes, puis regarde la fenêtre du palais. Dehors, des femmes pilent le mil comme si de rien n'était. Des enfants jouent dans la poussière rouge. Ce royaume existe depuis deux siècles. Il ne peut pas disparaître par un simple morceau de papier.\n\nIl déchire l'ultimatum. La guerre est désormais inévitable.",
            'quote'      => '« Ce papier ne vaut pas le sang de mes ancêtres. Renvoyez-le à son auteur. »',
            'quote_author' => 'Béhanzin, selon les chroniques orales dahoméennes',
            'question'   => 'Béhanzin a rejeté l\'ultimatum. La guerre commence dans quelques semaines. Quelle est la première action militaire ?',
            'is_ending'  => false,
        ]);

        // ════════════════════════════════════════════
        // ACTE III — LA GUERRE ÉCLATE (1892)
        // ════════════════════════════════════════════

        $s7 = Scene::create([
            'act'        => 3,
            'act_title'  => 'La Guerre Éclate',
            'year'       => '1892',
            'sort_order' => 70,
            'title'      => 'L\'Arrivée du Colonel Dodds',
            'content'    => "Août 1892. Le colonel Dodds débarque à Cotonou avec quatre mille hommes — tirailleurs sénégalais, légionnaires français, auxiliaires de Porto-Novo — et une artillerie de campagne moderne.\n\nIl remonte lentement le fleuve Ouémé sur des canonnières, brûlant les villages qui résistent. La progression est méthodique, brutale.\n\nLes Agojié tendent une première embuscade à Dogba. Les guerrières sortent de la forêt en silence, attaquent à l'aube. Pendant deux heures, les Français reculent. Puis les canons parlent. Des dizaines d'Agojié tombent.\n\nLa bravoure ne suffit pas contre les obus. Béhanzin le sait maintenant de façon certaine. Mais il continue. Chaque embuscade coûte des soldats français, retarde la marche, épuise les stocks de munitions ennemis. C'est une guerre d'usure.\n\nLe temps est peut-être le seul allié qu'il lui reste.",
            'quote'      => '« Elles se battaient comme des diables. Je n\'ai jamais vu une telle furie. »',
            'quote_author' => 'Capitaine Borghese, armée française, rapport de Dogba, 1892',
            'question'   => 'Dodds progresse vers Abomey. Quelle tactique adopter face à sa supériorité en artillerie ?',
            'is_ending'  => false,
        ]);

        // ─── BIFURCATION : 3 chemins distincts vers Scène 9 ───

        // 8a — chemin "embuscades nocturnes" (le récit historique original)
        $s8a = Scene::create([
            'act'        => 3,
            'act_title'  => 'La Guerre Éclate',
            'year'       => '1892',
            'sort_order' => 80,
            'title'      => 'La Nuit de Cana',
            'content'    => "Octobre 1892. La bataille de Cana — à trente kilomètres d'Abomey — est la plus féroce du conflit. Pendant trois nuits consécutives, les soldats dahoméens attaquent le camp français dans l'obscurité totale.\n\nLes Agojié avancent en silence dans la forêt dense, guidées par des sentiers qu'elles connaissent depuis l'enfance. Les Français, épuisés, tirent dans tous les sens. Les pertes sont lourdes des deux côtés.\n\nAu matin du troisième jour, Dodds ordonne une contre-attaque à la baïonnette. Les lignes dahoméennes se replient — non pas en déroute, mais méthodiquement, préservant leurs forces pour d'autres batailles.\n\nBéhanzin reçoit le rapport. Il ferme les yeux un long moment. Parmi les morts : trois commandantes des Agojié qu'il connaissait depuis son enfance. La guerre a un visage, maintenant. Ce visage, c'est le deuil.\n\nMais Abomey tient encore. Le roi décide du sort du royaume.",
            'quote'      => '« Ils sont revenus trois nuits de suite. Chaque nuit, nous pensions que ce serait la dernière. »',
            'quote_author' => 'Sous-lieutenant Decorse, journal de campagne, Cana, 1892',
            'question'   => 'Cana résiste mais les pertes sont sévères. Béhanzin doit prendre une décision cruciale sur la suite de la guerre.',
            'is_ending'  => false,
        ]);

        // 8b — chemin "élimination du commandement" (NOUVEAU)
        $s8b = Scene::create([
            'act'        => 3,
            'act_title'  => 'La Guerre Éclate',
            'year'       => '1892',
            'sort_order' => 81,
            'title'      => 'Les Tireurs de l\'Aube',
            'content'    => "Le roi a choisi la voie du fil tranchant : décapiter le commandement français. Une compagnie de tireurs d'élite est constituée — d'anciens chasseurs de panthères, des Agojié au regard d'aigle, des transfuges instruits aux fusils à longue portée par les marchands brésiliens.\n\nIls suivent la colonne de Dodds depuis les frondaisons, parfois à moins de cent mètres, des jours durant. À l'aube, alors que les officiers sortent de leurs tentes pour le rapport, les coups partent — un, deux, trois — puis la forêt se referme.\n\nEn quinze jours, deux capitaines, un médecin-major et le chef du génie tombent. Le moral français vacille. Les colonnes se déplacent désormais avec un cordon de tirailleurs en éclaireurs permanents. La progression ralentit.\n\nMais les représailles sont terribles. Dodds, fou de rage, fait raser quatre villages soupçonnés d'avoir abrité les tireurs. Des civils meurent. Béhanzin reçoit les rapports en silence — chaque vie française gagnée se paie en sang dahoméen.\n\nLa guerre prend un visage qu'il n'avait pas voulu, mais qu'il ne peut plus retirer.",
            'quote'      => '« Tuez les chefs, et l\'armée se cherche. Tuez les villages, et c\'est le peuple qui tombe. »',
            'quote_author' => 'Conseiller militaire de Béhanzin, hiver 1892',
            'question'   => 'Les tireurs ont ralenti Dodds, mais à quel prix ? Quelle est la suite de la stratégie royale ?',
            'is_ending'  => false,
        ]);

        // 8c — chemin "couper le ravitaillement" (NOUVEAU)
        $s8c = Scene::create([
            'act'        => 3,
            'act_title'  => 'La Guerre Éclate',
            'year'       => '1892',
            'sort_order' => 82,
            'title'      => 'La Famine du Fleuve',
            'content'    => "Pas de grande bataille. Pas de coup d'éclat. Béhanzin a choisi l'arme la plus lente et la plus sûre : couper le ravitaillement.\n\nDes équipes de plongeurs nocturnes sabotent les canonnières françaises sur l'Ouémé. Des chefs de villages refusent de vendre du mil aux quartiers-maîtres. Des sentiers sont effacés, des puits comblés, des marigots empoisonnés au manioc fermenté.\n\nEn six semaines, les rations françaises chutent de moitié. La dysenterie se répand. Les chevaux meurent les uns après les autres — la cavalerie auxiliaire devient inutilisable. Dodds doit détacher un quart de ses hommes pour escorter chaque convoi.\n\nL'avancée vers Abomey, qui devait prendre trois semaines, en prend dix. Les pluies arrivent. Les colonnes s'enfoncent dans la boue. Dans les hôpitaux de campagne, plus de soldats meurent de fièvres que de balles.\n\nMais le Danhomè paie aussi son prix : les paysans qui ont participé au sabotage sont traqués. Des hameaux entiers sont brûlés en représailles. La guerre s'étend en taches sourdes sur tout le royaume.",
            'quote'      => '« Le requin n\'attaque pas le navire. Il attend que la mer le fatigue. »',
            'quote_author' => 'Proverbe attribué à un capitaine d\'Abomey, 1892',
            'question'   => 'Dodds est ralenti, affaibli, mais il avance encore. Quelle est la prochaine étape pour Béhanzin ?',
            'is_ending'  => false,
        ]);

        $s9 = Scene::create([
            'act'        => 3,
            'act_title'  => 'La Guerre Éclate',
            'year'       => '1892',
            'sort_order' => 90,
            'title'      => 'Les Flammes d\'Abomey',
            'content'    => "Novembre 1892. Dodds est aux portes d'Abomey. Béhanzin convoque son conseil de guerre pour la dernière fois dans le grand palais.\n\nLes murs de terre ocre qui ont vu naître des rois depuis deux siècles. Les bas-reliefs sculptés racontant les victoires du Danhomè. Les autels des ancêtres.\n\nLe roi ordonne d'évacuer les civils et les trésors royaux vers le nord, dans les forêts de Gbècon. Puis il donne l'ordre que personne ne voulait entendre : incendier le palais.\n\n« Que les Français trouvent des cendres. Pas notre dignité. »\n\nLes flammes s'élèvent dans la nuit. Du camp français, Dodds voit la lueur orange à l'horizon et comprend : Béhanzin ne se rend pas. Il continue.\n\nLa guerre entre maintenant dans sa phase ultime. Le roi est en fuite, mais il est libre. Et tant qu'il est libre, la résistance continue.",
            'quote'      => '« Qu\'ils trouvent des cendres. Pas notre dignité. »',
            'quote_author' => 'Béhanzin, avant l\'incendie d\'Abomey, novembre 1892',
            'question'   => 'Abomey est en flammes. Béhanzin est en fuite dans la forêt. Quelle est la prochaine décision du roi ?',
            'is_ending'  => false,
        ]);

        // ════════════════════════════════════════════
        // ACTE IV — LE DESTIN DU ROI (1892–1894)
        // ════════════════════════════════════════════

        $s10 = Scene::create([
            'act'        => 4,
            'act_title'  => 'Le Destin du Roi',
            'year'       => '1893',
            'sort_order' => 100,
            'title'      => 'Un Roi dans la Forêt',
            'content'    => "Un an s'est écoulé. Béhanzin tient la forêt de Gbècon. Il dispose encore de plusieurs milliers de fidèles — guerriers, Agojié survivantes, chefs de villages qui lui restent loyaux. Les Français contrôlent les villes et les routes. La forêt appartient encore au roi.\n\nMais les ressources s'épuisent. La saison des pluies a été longue. Les maladies progressent. Des messages arrivent de partout : certains chefs dahoméens commencent à négocier séparément avec Dodds. La résistance s'émiette.\n\nDodds fait circuler des appels à la reddition : Béhanzin sera traité « avec honneur » s'il se rend. Il sait que c'est un mensonge orné de mots doux.\n\nDans la forêt, sous la pluie, le roi tient conseil une dernière fois. Autour du feu, les visages sont las mais les yeux restent fiers. Le moment de la décision ultime est venu.\n\nTrois chemins s'ouvrent devant lui. Trois destins pour un roi, un peuple, une Histoire.",
            'quote'      => '« Trois chemins, et derrière chacun, un Danhomè différent qui s\'écrira dans la mémoire des hommes. »',
            'quote_author' => 'Chronique orale de Gbècon, transcrite en 1928',
            'question'   => 'Le roi doit choisir entre trois destins. Lequel offrira le récit le plus juste pour son peuple ?',
            'is_ending'  => false,
        ]);

        // ════════════════════════════════════════════
        // FINS — TROIS DESTINS POSSIBLES
        // ════════════════════════════════════════════

        $s11a = Scene::create([
            'act'         => 4,
            'act_title'   => 'Le Destin du Roi',
            'year'        => '1894–1906',
            'sort_order'  => 110,
            'title'       => 'La Résistance Éternelle',
            'content'     => "Béhanzin choisit de continuer. Pas de reddition. Pas de fuite définitive. La forêt sera son royaume, et ce royaume n'aura pas de capitales visibles — seulement des âmes debout.\n\nPendant encore une année, les harceleurs dahoméens frappent les convois français, libèrent des prisonniers, maintiennent vivante la flamme de la résistance. Des villages entiers refusent de collaborer. Des femmes cachent des guerriers blessés dans leurs cases. Des enfants servent d'éclaireurs.\n\nEn janvier 1894, après des mois de tractations et de pressions sur sa famille, Béhanzin se rend — mais à ses propres conditions. Il se présente en roi, en tenue royale, entouré de ses derniers fidèles. Il ne rampe pas. Il marche.\n\nDevant Dodds, il déclare : « Je me rends à la France, non à la défaite. Le Danhomè vivra dans le cœur de son peuple quand vos fortifications seront poussière. »\n\nIl est exilé à la Martinique, puis en Algérie. Il meurt à Blida en 1906. Mais sa résistance a duré deux ans contre l'une des armées les mieux équipées du monde. Son nom devient immortel.\n\nAujourd'hui, Béhanzin est une figure nationale du Bénin. Des rues, des stades, des écoles portent son nom. Son visage orne des billets de banque et des fresques murales. La résistance qu'il incarne ne s'est jamais vraiment terminée — elle s'est transformée en mémoire, et la mémoire est éternelle.",
            'quote'       => '« Je me rends à la France, non à la défaite. »',
            'quote_author'=> 'Béhanzin, janvier 1894',
            'is_ending'   => true,
            'ending_type' => 'resistance',
        ]);

        $s11b = Scene::create([
            'act'         => 4,
            'act_title'   => 'Le Destin du Roi',
            'year'        => '1894',
            'sort_order'  => 120,
            'title'       => 'L\'Exil du Roi',
            'content'     => "Béhanzin prend la décision la plus douloureuse de sa vie : négocier sa reddition pour épargner ce qu'il reste de son peuple. Trop de sang a coulé. Trop de mères ont pleuré.\n\nIl envoie un émissaire à Dodds avec ses conditions : aucune represaille contre les combattants dahoméens, préservation des rites et coutumes royales, reconnaissance formelle qu'il se rend en souverain — pas en criminel.\n\nDodds accepte, partiellement. Les pourparlers durent trois semaines. Finalement, Béhanzin descend de la forêt.\n\nLe 25 janvier 1894, le roi du Danhomè sort de la forêt de Gbècon. Il est vêtu de ses habits royaux, sa tête est haute. Derrière lui, quelques centaines de fidèles — les derniers. Devant lui, les rangs des tirailleurs sénégalais au garde-à-vous.\n\nDodds le reçoit avec une froideur militaire. La poignée de main est brève. Le mot « capitulation » n'est jamais prononcé — mais tout le monde sait ce que ce moment signifie.\n\nBéhanzin est embarqué sur un navire à destination de la Martinique. Sur le quai, des femmes pleurent en silence. Des hommes détournent les yeux. Un enfant crie « Vive le roi ! » avant d'être réduit au silence par un soldat.\n\nLe roi ne reverra jamais son pays. Il meurt en exil à Blida, Algérie, le 10 décembre 1906. Ses restes sont rapatriés à Abomey en 1928, trente-quatre ans trop tard.",
            'quote'       => '« Il vaut mieux se rendre en roi que de disparaître en fantôme. »',
            'quote_author'=> 'Attribué à Béhanzin, chroniques orales',
            'is_ending'   => true,
            'ending_type' => 'exile',
        ]);

        $s11c = Scene::create([
            'act'         => 4,
            'act_title'   => 'Le Destin du Roi',
            'year'        => '1893',
            'sort_order'  => 130,
            'title'       => 'Le Sacrifice d\'Abomey',
            'content'     => "Béhanzin refuse toute reddition. Toute fuite. Il y a une troisième voie — la plus difficile, la plus pure : le sacrifice.\n\nIl renvoie ses derniers fidèles au nord, vers la liberté. Aux Agojié, il dit : « Vous avez accompli plus que ce que les dieux pouvaient demander. Vivez. Portez notre histoire. » Beaucoup refusent de partir. Il le leur ordonne.\n\nSeul avec une poignée de gardes choisis, Béhanzin organise une dernière action d'éclat. Dans la nuit du 12 janvier 1893, ses hommes attaquent simultanément trois postes français en périphérie d'Abomey. L'attaque est spectaculaire, inutile militairement, mais son symbole est immense.\n\nLes Français mettent trois jours à retrouver ses traces. Quand ils pénètrent dans le dernier campement, ils ne trouvent que des feux encore chauds et, plantée dans un arbre, une lance royale avec un message en langue fon :\n\n« Le Danhomè n'a pas été vaincu. Il s'est retiré dans l'invisible. »\n\nBéhanzin disparaît dans la forêt dense du nord. Nul ne sait avec certitude ce qu'il advient de lui. Des rumeurs parlent d'un vieux roi qui vivrait dans un village reculé, reconnu de tous mais protégé par le silence.\n\nL'histoire officielle dit qu'il se rendit plus tard. La mémoire populaire, elle, préfère une autre vérité : un roi qui choisit le mystère plutôt que la cage. Un requin qui s'enfonce dans les profondeurs — et qu'on ne revoit jamais.",
            'quote'       => '« Le Danhomè n\'a pas été vaincu. Il s\'est retiré dans l\'invisible. »',
            'quote_author'=> 'Message laissé par Béhanzin, selon les chroniques orales',
            'is_ending'   => true,
            'ending_type' => 'sacrifice',
        ]);

        // ════════════════════════════════════════════
        // CHOIX — ACTE I  (linéaire : 3 choix → même scène)
        // ════════════════════════════════════════════

        // s1 → s2
        Choice::create(['scene_id' => $s1->id, 'next_scene_id' => $s2->id, 'sort_order' => 1,
            'text' => 'Renforcer immédiatement l\'armée : acheter des armes modernes, entraîner les Agojié, mobiliser les alliés régionaux.']);
        Choice::create(['scene_id' => $s1->id, 'next_scene_id' => $s2->id, 'sort_order' => 2,
            'text' => 'Entamer des négociations diplomatiques avec Paris : envoyer une délégation et démontrer la souveraineté du Danhomè.']);
        Choice::create(['scene_id' => $s1->id, 'next_scene_id' => $s2->id, 'sort_order' => 3,
            'text' => 'Construire des alliances régionales : rallier les royaumes voisins contre l\'expansionnisme français.']);

        // s2 → s3
        Choice::create(['scene_id' => $s2->id, 'next_scene_id' => $s3->id, 'sort_order' => 1,
            'text' => 'Attaquer le fort de Cotonou par surprise et l\'incendier avant que les renforts français n\'arrivent.']);
        Choice::create(['scene_id' => $s2->id, 'next_scene_id' => $s3->id, 'sort_order' => 2,
            'text' => 'Établir un blocus commercial autour de Cotonou pour asphyxier économiquement la garnison française.']);
        Choice::create(['scene_id' => $s2->id, 'next_scene_id' => $s3->id, 'sort_order' => 3,
            'text' => 'Envoyer une délégation de haut rang à Paris pour contester le traité de 1878 devant l\'opinion internationale.']);

        // s3 → s4
        Choice::create(['scene_id' => $s3->id, 'next_scene_id' => $s4->id, 'sort_order' => 1,
            'text' => 'Adopter une stratégie de guérilla : frapper et disparaître, épuiser les Français plutôt que les affronter de front.']);
        Choice::create(['scene_id' => $s3->id, 'next_scene_id' => $s4->id, 'sort_order' => 2,
            'text' => 'Chercher un soutien international : approcher l\'Allemagne ou la Grande-Bretagne pour contrebalancer l\'influence française.']);
        Choice::create(['scene_id' => $s3->id, 'next_scene_id' => $s4->id, 'sort_order' => 3,
            'text' => 'Fortifier le royaume et attendre : que la France s\'enlise dans d\'autres conflits coloniaux ailleurs.']);

        // ════════════════════════════════════════════
        // CHOIX — ACTE II  (linéaire)
        // ════════════════════════════════════════════

        // s4 → s5
        Choice::create(['scene_id' => $s4->id, 'next_scene_id' => $s5->id, 'sort_order' => 1,
            'text' => 'Riposter militairement contre Porto-Novo et punir Tofa pour sa trahison.']);
        Choice::create(['scene_id' => $s4->id, 'next_scene_id' => $s5->id, 'sort_order' => 2,
            'text' => 'Lancer une campagne diplomatique pour isoler Tofa et montrer aux autres rois la valeur de la résistance.']);
        Choice::create(['scene_id' => $s4->id, 'next_scene_id' => $s5->id, 'sort_order' => 3,
            'text' => 'Concentrer toutes les forces sur la défense d\'Abomey et ignorer Cotonou et Porto-Novo.']);

        // s5 → s6
        Choice::create(['scene_id' => $s5->id, 'next_scene_id' => $s6->id, 'sort_order' => 1,
            'text' => 'Priorité aux armes : investir toutes les ressources dans l\'achat de fusils modernes et de munitions.']);
        Choice::create(['scene_id' => $s5->id, 'next_scene_id' => $s6->id, 'sort_order' => 2,
            'text' => 'Priorité à la formation : entraîner intensivement les troupes aux techniques de guérilla et d\'embuscade.']);
        Choice::create(['scene_id' => $s5->id, 'next_scene_id' => $s6->id, 'sort_order' => 3,
            'text' => 'Priorité aux fortifications : creuser des tranchées, ériger des palissades, préparer Abomey pour un siège long.']);

        // s6 → s7
        Choice::create(['scene_id' => $s6->id, 'next_scene_id' => $s7->id, 'sort_order' => 1,
            'text' => 'Attaque préventive : frapper les colonnes françaises avant qu\'elles ne soient complètement formées.']);
        Choice::create(['scene_id' => $s6->id, 'next_scene_id' => $s7->id, 'sort_order' => 2,
            'text' => 'Retraite stratégique dans la forêt : attirer les Français loin de leurs lignes de ravitaillement.']);
        Choice::create(['scene_id' => $s6->id, 'next_scene_id' => $s7->id, 'sort_order' => 3,
            'text' => 'Défense en profondeur : céder du terrain lentement pour épuiser l\'ennemi avant la bataille décisive.']);

        // ════════════════════════════════════════════
        // CHOIX — ACTE III  ★ VRAIE BIFURCATION ★
        // s7 → 3 chemins distincts (s8a / s8b / s8c)
        // ════════════════════════════════════════════

        Choice::create(['scene_id' => $s7->id, 'next_scene_id' => $s8a->id, 'sort_order' => 1,
            'text' => 'Multiplier les embuscades nocturnes : frapper les campements français à la tombée de la nuit pour briser leur moral.']);
        Choice::create(['scene_id' => $s7->id, 'next_scene_id' => $s8b->id, 'sort_order' => 2,
            'text' => 'Cibler les officiers : des tireurs d\'élite pour éliminer le commandement ennemi et semer la désorganisation.']);
        Choice::create(['scene_id' => $s7->id, 'next_scene_id' => $s8c->id, 'sort_order' => 3,
            'text' => 'Couper les lignes de ravitaillement : harceler les convois fluviaux et les dépôts logistiques français.']);

        // Convergence : 8a/8b/8c → 9 (chacune avec ses propres choix narratifs)

        // s8a → s9
        Choice::create(['scene_id' => $s8a->id, 'next_scene_id' => $s9->id, 'sort_order' => 1,
            'text' => 'Continuer la résistance : lancer une grande contre-offensive pour reprendre Cana et stopper Dodds.']);
        Choice::create(['scene_id' => $s8a->id, 'next_scene_id' => $s9->id, 'sort_order' => 2,
            'text' => 'Replier vers Abomey et préparer le siège final : transformer la capitale en forteresse imprenable.']);
        Choice::create(['scene_id' => $s8a->id, 'next_scene_id' => $s9->id, 'sort_order' => 3,
            'text' => 'Évacuer les non-combattants et les trésors royaux vers le nord, puis mener une guerre de forêt indéfinie.']);

        // s8b → s9
        Choice::create(['scene_id' => $s8b->id, 'next_scene_id' => $s9->id, 'sort_order' => 1,
            'text' => 'Relâcher les tireurs : trop de villages brûlent. Reprendre une stratégie de défense classique.']);
        Choice::create(['scene_id' => $s8b->id, 'next_scene_id' => $s9->id, 'sort_order' => 2,
            'text' => 'Pousser l\'avantage : envoyer une seconde vague viser le quartier-général de Dodds lui-même.']);
        Choice::create(['scene_id' => $s8b->id, 'next_scene_id' => $s9->id, 'sort_order' => 3,
            'text' => 'Faire diversion vers Cana, où la moitié des forces françaises est immobilisée.']);

        // s8c → s9
        Choice::create(['scene_id' => $s8c->id, 'next_scene_id' => $s9->id, 'sort_order' => 1,
            'text' => 'Étendre le sabotage : empoisonner les puits, brûler les récoltes sur le passage de Dodds.']);
        Choice::create(['scene_id' => $s8c->id, 'next_scene_id' => $s9->id, 'sort_order' => 2,
            'text' => 'Profiter de l\'épuisement français pour offrir une dernière fois la paix avant la chute d\'Abomey.']);
        Choice::create(['scene_id' => $s8c->id, 'next_scene_id' => $s9->id, 'sort_order' => 3,
            'text' => 'Lancer un assaut frontal sur la colonne affaiblie pendant qu\'elle traverse la forêt boueuse.']);

        // s9 → s10
        Choice::create(['scene_id' => $s9->id, 'next_scene_id' => $s10->id, 'sort_order' => 1,
            'text' => 'Rester à Abomey jusqu\'au bout : mourir en roi plutôt que fuir en réfugié.']);
        Choice::create(['scene_id' => $s9->id, 'next_scene_id' => $s10->id, 'sort_order' => 2,
            'text' => 'Fuir vers le nord avec les derniers fidèles : continuer la résistance depuis les forêts inaccessibles.']);
        Choice::create(['scene_id' => $s9->id, 'next_scene_id' => $s10->id, 'sort_order' => 3,
            'text' => 'Envoyer son frère Agoli-Agbo négocier avec Dodds pour sauver les vies civiles.']);

        // ════════════════════════════════════════════
        // CHOIX — ACTE IV (bifurcation finale vers les 3 fins)
        // ════════════════════════════════════════════

        Choice::create(['scene_id' => $s10->id, 'next_scene_id' => $s11a->id, 'sort_order' => 1,
            'text' => 'Continuer la résistance dans la forêt : harceler les Français indéfiniment, ne jamais capituler, rester un symbole vivant de la liberté.']);
        Choice::create(['scene_id' => $s10->id, 'next_scene_id' => $s11b->id, 'sort_order' => 2,
            'text' => 'Négocier une reddition honorable : se rendre en roi, la tête haute, pour épargner les derniers survivants et préserver la dignité du Danhomè.']);
        Choice::create(['scene_id' => $s10->id, 'next_scene_id' => $s11c->id, 'sort_order' => 3,
            'text' => 'Disparaître dans la forêt sans reddition ni combat final : devenir un mythe, laisser la France face à un vide qu\'elle ne peut ni emprisonner ni tuer.']);
    }
}
