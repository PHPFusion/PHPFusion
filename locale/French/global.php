<?php
/*
French Language Fileset
Produced by Gérard Vandenabeele admin@relaxmax.fr
*/
// Locale Settings
setlocale(LC_TIME, "fra_FR.utf8"); // Linux Server (Windows may differ)

$locale += [
'charset' => "utf-8",
'xml_lang' => "fr",
'tinymce' => "fr_FR",
'phpmailer' => "fr",
'datepicker' => "fr"];

// Full & Short Months

$locale['months'] = "&nbsp|Janvier|Février|Mars|Avril|Mai|Juin|Juillet|Août|Septembre|Octobre|Novembre|Décembre";
$locale['shortmonths'] = "&nbsp|Jan|Fév|Mar|Avr|Mai|Jun|Jul|Aoû|Sept|Oct|Nov|Déc";
$locale['weekdays'] = "Dimanche|Lundi|Mardi|Mercredi|Jeudi|Vendredi|Samedi";

// Timers
$locale['year'] = "an";
$locale['year_a'] = "ans";
$locale['month'] = "mois";
$locale['month_a'] = "mois";
$locale['day'] = "jour";
$locale['day_a'] = "jours";
$locale['hour'] = "heure";
$locale['hour_a'] = "heures";
$locale['minute'] = "minute";
$locale['minute_a'] = "minutes";
$locale['second'] = "seconde";
$locale['second_a'] = "secondes";
$locale['just_now'] = "tout juste maintenant";
$locale['ago'] = "il y a";

// Geo
$locale['street1'] = "Addresse 1";
$locale['street2'] = "Addresse 2";
$locale['city'] = "Ville";
$locale['postcode'] = "Code postal";
$locale['sel_country'] = "Sélection du pays";
$locale['sel_state'] = "Sélection de l'état";
$locale['sel_user'] = "Veuillez entrer un nom d'utilisateur";
$locale['add_language'] = "Ajouter une  traduction dans une langue";
$locale['add_lang'] = "Ajouter %s";

// Name
$locale['name'] = "Nom et prénom";
$locale['username_pretext'] = "Le nom d'utilisateur public est le même qu'à l'adresse du profil utilisateur située à <div class='alert alert-info m-t-10 p-10'>%s<strong>%s.</strong></div>";
$locale['first_name'] = "Prénom";
$locale['middle_name'] = "Deuxième prénom";
$locale['last_name'] = "Nom de famille";

// Documents
$locale['doc_type'] = "Type de document";
$locale['doc_series'] = "Séries";
$locale['doc_number'] = "Nombre";
$locale['doc_authority'] = "Autorité";
$locale['doc_date_issue'] = "Date d'émission";
$locale['doc_date_expire'] = "Date d'expiration";

// Standard User Levels
$locale['user0'] = "Public";
$locale['user1'] = "Membre";
$locale['user2'] = "Administrateur";
$locale['user3'] = "Super administrateur";
$locale['user_na'] = "N/A";
$locale['user_guest'] = "Invité";
$locale['user_anonymous'] = "Utilisateur anonyme";
$locale['genitive'] = "%s's %s";

// Standard User Status
$locale['status0'] = "Activé";
$locale['status1'] = "Banni";
$locale['status2'] = "Pas activé";
$locale['status3'] = "Suspendu";
$locale['status4'] = "Banni par sécurité";
$locale['status5'] = "Annulé";
$locale['status6'] = "Anonyme";
$locale['status7'] = "Désactivé";
$locale['status8'] = "Inactif";

// Forum Moderator Level(s)
$locale['userf1'] = "Modérateur";

// Navigation
$locale['global_001'] = "Navigation";
$locale['global_002'] = "Aucun lien n'a encore été créé";

// Users Online
$locale['global_010'] = "Utilisateur(s) en ligne";
$locale['global_011'] = "Invité(s) en ligne";
$locale['global_012'] = "Membre(s) en ligne";
$locale['global_013'] = "Aucun membre n'est en ligne!";
$locale['global_014'] = "Total des membres";
$locale['global_015'] = "Membres non activés";
$locale['global_016'] = "Nouveau membre";

// Forum Side panel
$locale['global_020'] = "Sujets du forum";
$locale['global_021'] = "Nouveaux sujets";
$locale['global_022'] = "Sujets sur le feu";
$locale['global_023'] = "Aucun sujet n'a été proposé!";

// Comments Side panel
$locale['global_025'] = "Derniers commentaires";
$locale['global_026'] =	"Aucun commentaire n'a encore été proposé!";

// Articles Side panel
$locale['global_030'] =	"Derniers articles";
$locale['global_031'] =	"Aucun article n'a encore été rédigé!";

// Downloads Side panel
$locale['global_032'] =	"Derniers téléchargements";
$locale['global_033'] =	"Aucun téléchargement n'a encore été créé!";

// Welcome panel
$locale['global_035'] =	"Éditorial";

// Latest Active Forum Threads panel
$locale['global_040'] =	"Derniers sujets actifs du forum";
$locale['global_041'] = "Mes sujets récents";
$locale['global_042'] = "Mes messages récents";
$locale['global_043'] = "Nouveaux messages";
$locale['global_044'] = "Sujets";
$locale['global_045'] = "Vues";
$locale['global_046'] = "Réponses";
$locale['global_047'] = "Dernier message";
$locale['global_048'] = "Forum";
$locale['global_049'] = "Envoyé";
$locale['global_050'] = "Auteur";
$locale['global_051'] = "Sondage";
$locale['global_052'] = "Déplacé";
$locale['global_053'] = "Vous n'avez pas encore engagé de discussion!";
$locale['global_054'] = "Vous n'avez pas encore posté de message sur le forum!";
$locale['global_055'] = "Il y a %u nouveaux messages sur %u différents sujets depuis votre dernière visite!";
$locale['global_056'] = "Mes sujets suivis";
$locale['global_057'] = "Options";
$locale['global_058'] = "Stopper le suivi d'un sujet";
$locale['global_059'] = "Vous ne suivez plus aucun sujet!";
$locale['global_060'] = "Cesser de suivre ce sujet?";

// Blog, News & Articles
$locale['global_070'] = "Envoyé par ";
$locale['global_071b'] = "Afficher tous les messages provenant de %s.";
$locale['global_071'] = "le ";
$locale['global_071b'] = "Auteur";
$locale['global_072'] = "Continuer la lecture...";
$locale['global_073'] = " Commentaires";
$locale['global_073b'] = " Commentaire";
$locale['global_074'] = " Lectures";
$locale['global_074b'] = " Lecture";
$locale['global_075'] = "Imprimer";
$locale['print'] = "Imprimer";
$locale['global_076'] = "Éditer";
$locale['global_077'] = "Nouvelles";
$locale['global_078'] = "Aucune nouvelle n'a encore été postée!";
$locale['global_079'] = "En ";
$locale['global_080'] = "En hors catégorie";
$locale['global_081'] = "Accueil des nouvelles";
$locale['global_082'] = "Nouvelles";
$locale['global_083'] = "Dernière actualisation";
$locale['global_084'] = "Catégories de nouvelles";
$locale['global_085'] = "Toutes les autres catégories";
$locale['global_086'] = "Nouvelles les plus récentes";
$locale['global_087'] = "Nouvelles les plus commentées";
$locale['global_088'] = "Meilleures évaluations des nouvelles";
$locale['global_089'] = "Être le premier à proposer un commentaire sur %s!";
$locale['global_089a'] = "Être le premier à proposer une évaluation sur %s!";
$locale['global_089b'] = "Apperçu de la miniature";
$locale['global_089c'] = "Apperçu de la liste";

// Page Navigation
$locale['global_090'] = "Précédent";
$locale['global_091'] = "Suivant";
$locale['global_092'] = "Page ";
$locale['global_093'] = " de ";
$locale['global_094'] = " hors de ";

// Guest User Menu
$locale['global_100'] = "Sinscrire";
$locale['global_101'] = "Identifiant";
$locale['global_101a'] = "Veuillez entrer votre identifiant";
$locale['global_102'] = "Mot de passe";
$locale['global_103'] = "Mémoriser";
$locale['global_104'] = "Sinscrire";
$locale['global_105'] = "<i class='fa fa-question-circle m-r-10'></i>Envie de devenir membre.<br />C'est <a href='".BASEDIR."register.php' class='side'>ici</a> pour l'enregistrement!";
$locale['global_106'] = "<i class='fa fa-question-circle m-r-10'></i>Mot de passe oublié.<br /><a href='".BASEDIR."lostpassword.php' class='side'>En demander un autre!</a>";
$locale['global_107'] = "Devenir membre";
$locale['global_108'] = "Mot de passe oublié ou perdu";

// Member User Menu
$locale['global_120'] = "Personnalisation du profil de votre page";
$locale['global_121'] = "Messages privés";
$locale['global_122'] = "Annuaire des membres ";
$locale['global_123'] = "Panneau d'administration";
$locale['global_124'] = "Déconnexion";
$locale['global_125'] = "Vous avez %u nouvelles.";
$locale['global_126'] = "message";
$locale['global_127'] = "messages";
$locale['global_128'] = "proposition";
$locale['global_129'] = "propositions";

// User Menu
$locale['global_123'] = "Panneau d'administration";
$locale['UM060'] = "Se connecter";
$locale['UM061'] = "Nom d'utilisateur";
$locale['UM061a'] = "e-mail";
$locale['UM061b'] = "Nom d'utilisateur ou e-mail";
$locale['UM062'] = "Mot de passe";
$locale['UM063'] = "Mémoriser";
$locale['UM064'] = "Identification";
$locale['UM065'] = "Pas encore membre?<br />Cest <a href='".BASEDIR."register.php' class='side'>ici</a> pour l'enregistrement.";
$locale['UM066'] = "Oubli du mot de passe?<br />Demande <a href='".BASEDIR."lostpassword.php' class='side'>ici</a> pour en obtenir un nouveau.";
$locale['UM080'] = "Édition du profil";
$locale['UM081'] = "Messages privés";
$locale['UM082'] = "Annuaire des membres";
$locale['UM083'] = "Panneau d'administration";
$locale['UM084'] = "Déconnexion";
$locale['UM085'] = "Vous avez %u nouvelles ";
$locale['UM086'] = "message";
$locale['UM087'] = "messages";
$locale['UM088'] = "Sujets suivis";

// Submit (news, link, article)
$locale['UM089'] = "Je propose...";
$locale['UM090'] = "une nouvelle";
$locale['UM091'] = "un lien";
$locale['UM092'] = "un article";
$locale['UM093'] = "une photo";
$locale['UM094'] = "un téléchargement";
$locale['UM095'] = "un blog";

// User Panel
$locale['UM096'] = "Bienvenue à ";
$locale['UM097'] = "Menu personnel";
$locale['UM101'] = "Changer de langue";

// Gauges
$locale['UM098'] = "Messages entrants";
$locale['UM099'] = "Messages sortants";
$locale['UM100'] = "Messages archivés";

// Poll
$locale['global_130'] = "Sondage des membres";
$locale['global_131'] = "Proposition d'un vote";
$locale['global_132'] = "Vous devez être identifié pour voter.";
$locale['global_133'] = "Vote";
$locale['global_134'] = "Votes";
$locale['global_135'] = "Votes ";
$locale['global_136'] = "Débuté ";
$locale['global_137'] = "Terminé ";
$locale['global_138'] = "Archives des sondages";

$locale['global_139'] = "Sélection d'un sondage pour voir la liste";
$locale['global_140'] = "Vues";
$locale['global_141'] = "Voir un sondage";
$locale['global_142'] = "Aucun sondage n'a été défini.";
$locale['global_143'] = "Évaluations";

// Keywords and Meta
$locale['tags'] = "Tags";

// Captcha
$locale['global_150'] = "Code de validation:";
$locale['global_151'] = "Entrer le code de validation:";

// Footer Counter
$locale['global_170'] = "visite unique";
$locale['global_171'] = "visites uniques";
$locale['global_172'] = "Durée du rendu de la page: %s secondes";
$locale['global_173'] = "requêtes à la base de données";
$locale['global_174'] = "Mémoire utilisée";

// Admin Navigation
$locale['global_180'] = "Accueil à l'administration";
$locale['global_181'] = "Retour au site";
$locale['global_182'] = "<strong>Notice:</strong> Mot de passe administrateur non entré ou incorrect.";

// Miscellaneous
$locale['global_190'] = "Mode maintenance activé";
$locale['global_191'] = "Votre adresse IP est actuellement dans ​​la liste noire.";
$locale['global_192'] = "Votre session de connexion a expiré. Veuillez vous connecter à nouveau pour continuer.";

$locale['global_193'] = "Impossible de définir le cookie. Assurez-vous d'avoir activé les cookies dans votre navigateur pour pouvoir vous connecter correctement.";
$locale['global_194'] = "Ce compte est actuellement suspendu.";
$locale['global_195'] = "Ce compte n'a pas encore été activé par un administrateur.";
$locale['global_196'] = "Nom d'utilisateur ou mot de passe invalide.";
$locale['global_197'] = "Veuillez patienter pendant que nous vous transférons...<br /><br /> [Ou cliquez <a href='index.php'>ici</a> si vous souhaitez ne pas attendre.]";

$locale['global_198'] = "<strong>ATTENTION:</strong> PROGRAMME D'INSTALLATION DÉTECTÉ, SUPPRIMEZ IMMÉDIATEMENT LE DOSSIER /INSTALL/.";
$locale['global_199'] = "<strong>ATTENTION:</strong> le mot de passe administrateur n'est pas défini, cliquez immédiatement sur <a href='".BASEDIR."edit_profile.php'>Éditer le profil</a> pour le configurer.";

//Titles
$locale['global_200'] = " - ";
$locale['global_201'] = ": ";
$locale['global_202'] = $locale['global_200']."Recherche";
$locale['global_203'] = $locale['global_200']."FAQ";
$locale['global_204'] = $locale['global_200']."Forum";

//Themes
$locale['global_210'] = "Aller au contenu";

// No themes found
$locale['global_300'] = "Aucun thème complet n'a été trouvé";


$locale['global_301'] = "Nous sommes vraiment désolés, mais cette page ne ​​peut être affichée. En raison de certaines circonstances, aucun thème du site ne peut être trouvé. Si vous êtes un administrateur du site, utilisez votre client FTP pour télécharger un thème conçu pour <em>PHP-Fusion v9.00</em> dans le dossier <em>themes/</em>. Après l'enregistrement du téléchargement, allez dans <em>Paramètres principaux</em> pour voir si le thème choisi a été téléchargé correctement dans votre répertoire <em>themes/</em>. Notez que le dossier du thème téléchargé doit avoir exactement le même nom (y compris les casses de caractères , ce qui est important sur ​​les serveurs Unix) que choisi <em>Main Settings</em> page.<br /><br />Si vous êtes un membre régulier de ce site, contactez l'administrator via ".hide_email(fusion_get_settings('siteemail'))." e -mail et signalez ce problème.";
$locale['global_302'] = "Le thème sélectionné dans les paramètres généraux n'existe pas ou bien est incomplet!";

// JavaScript Not Enabled
$locale['global_303'] = "Oh non ! Où est le <strong>JavaScript</strong>?<br />Votre navigateur n'a pas le JavaScript activé ou ne supporte pas JavaScript. Veuillez <strong >l'activer</strong> sur votre navigateur Web correctement view this Web site,<br /> or <strong>upgrade</strong> to a Web browser that does support JavaScript; <a href='http://firefox.com' rel='nofollow' title='Mozilla Firefox'>Firefox</a>, <a href='http://apple.com/safari/' rel='nofollow' title='Safari'>Safari</a>, <a href='http://opera.com' rel='nofollow' title='Opera Web Browser'>Opera</a>, <a href='http://www.google.com/chrome' rel='nofollow' title='Google Chrome'>Chrome</a> or a version of <a href='http://www.microsoft.com/windows/internet-explorer/' rel='nofollow' title='Internet Explorer'>Internet Explorer</a> newer then version 6.";

// User Management

// Member status
$locale['global_400'] = "suspendu";
$locale['global_401'] = "banni";
$locale['global_402'] = "désactivé";
$locale['global_403'] = "compte terminé";
$locale['global_404'] = "compte anonymisé";
$locale['global_405'] = "utilisateur anonyme";

$locale['global_406'] = "Ce compte a été banni pour la raison suivante:";
$locale['global_407'] = "Ce compte a été suspendu jusqu'à ";
$locale['global_408'] = " pour la raison suivante:";

$locale['global_409'] = "Ce compte a été banni pour des raisons de sécurité.";
$locale['global_410'] = "La raison pour celà est: ";
$locale['global_411'] = "Ce compte a été annulé.";

$locale['global_412'] = "Ce compte a été rendu anonyme, probablement à cause de son inactivité.";

// Banning due to flooding
$locale['global_440'] = "Bannissement automatique par contrôle des débordements";
$locale['global_441'] = "Votre compte chez ".fusion_get_settings('sitename')." a été banni";
$locale['global_442'] = "Bonjour [USER_NAME],\n

votre compte chez ".fusion_get_settings('sitename')." a été capturé affichant trop d'éléments du système en très peu de temps depuis l'IP ".USER_IP.", et a été interdit à cet effet. Ceci est fait pour empêcher les robots de soumettre les messages de spam en succession rapide.\n


Veuillez contacter un administrateur du site à l'adresse ".fusion_get_settings('siteemail')." pour restaurer votre compte ou signaler que ce n'est pas vous la cause de cette interdiction de sécurité.\n
".fusion_get_settings('siteusername');

// Lifting of suspension
$locale['global_450'] = "Suspension levée automatiquement par le système";
$locale['global_451'] = "Suspension levée à ".fusion_get_settings('sitename');
$locale['global_452'] = "Bonjour USER_NAME,\n
La suspension de votre compte chez ".fusion_get_settings('siteurl')." a été levée. Voici vos informations de connexion:\n
Username: USER_NAME\n
Password: Caché pour des raisons de sécurité\n
Si vous avez oublié votre mot de passe vous pouvez le réinitialiser via le lien suivant: LOST_PASSWORD\n\n
Cordialement,\n
".fusion_get_settings('siteusername');
$locale['global_453'] = "Bonjour USER_NAME,\n
La suspension de votre compte chez ".fusion_get_settings('siteurl')." a été levée.\n\n
Cordialement,\n
".fusion_get_settings('siteusername');
$locale['global_454'] = "Compte réactivé chez ".fusion_get_settings('sitename');
$locale['global_455'] = "Bonjour USER_NAME,\n
La dernière fois que vous vous êtes connecté à votre compte chez ".fusion_get_settings('siteurl')." celui-ci a été réactivé et n'est plus marqué comme inactif.\n\n
Cordialement,\n
".fusion_get_settings('siteusername');

// Function parsebytesize()
$locale['global_460'] = "Vide";
$locale['global_461'] = "octets";
$locale['global_462'] = "ko";
$locale['global_463'] = "Mo";
$locale['global_464'] = "Go";
$locale['global_465'] = "To";

//Safe Redirect

$locale['global_500'] = "Vous allez être redirigé vers %s, veuillez patienter. Si vous n&#39;êtes pas redirigé, cliquez ici.";

// Captcha Locales
$locale['global_600'] = "Code de validation";
$locale['recaptcha'] = "fr";

//Miscellaneous
$locale['global_900'] = "Impossible de convertir de l'HEX en DEC";

//Language Selection
$locale['global_ML100'] = "Langue:";
$locale['global_ML101'] = "- Sélectionner la langue -";
$locale['global_ML102'] = "Langue du site";


$locale['flood'] = "On vous empêche d'afficher jusqu'à ce que la période de crue temps de recharge est terminée. Veuillez attendre pendant %s.";

$locale['no_image'] = "Pas d'image";
$locale['send_message'] = 'Envoyer un message';
$locale['go_profile'] = 'Aller à %s page de profil';

// ex. oneword.locale.php
// Greetings
$locale['hello'] = 'Bonjour!';
$locale['goodbye'] = 'Au revoir!';
$locale['welcome'] = 'À nouveau bienvenue';
$locale['home'] = 'Accueil';

// Status
$locale['error'] = 'Erreur!';
$locale['success'] = 'Succès!';
$locale['enable'] = 'Actif';
$locale['disable'] = 'Inactif';
$locale['no'] = 'Non';
$locale['yes'] = 'Oui';
$locale['off'] = 'Off';
$locale['on'] = 'Le';
$locale['or'] = 'ou';
$locale['by'] = 'par';
$locale['in'] = ' - Classé dans ';
$locale['of'] = 'de';
$locale['and'] = " - Commenté ";
$locale['na'] = 'Non disponible';
$locale['joined'] = "Inscrit depuis le : ";
$locale['nb-coms'] = "fois.";

// Navigation
$locale['next'] = 'Suivant';
$locale['pevious'] = 'Précédent';
$locale['back'] = 'Retour';
$locale['forward'] = 'Avant';
$locale['go'] = 'Aller';
$locale['cancel'] = 'Annuler';
$locale['move_up'] = "Monter";
$locale['move_down'] = "Descendre";

// Action
$locale['add'] = 'Ajouter';
$locale['save'] = 'Sauver';
$locale['save_changes'] = 'Enregistrer';
$locale['confirm'] = 'Confirmer';
$locale['update'] = 'Mise à jour';
$locale['updated'] = 'Mis à jour';
$locale['remove'] = 'Retirer';
$locale['delete'] = 'Suppression';
$locale['search'] = 'Rechercher';
$locale['help'] = 'Aide';
$locale['register'] = 'Enregistrer';
$locale['ban'] = 'Interdire';
$locale['reactivate'] = 'Réactiver';
$locale['user'] = 'Utilisateur';
$locale['promote'] = 'Promouvoir';
$locale['show'] = 'Montrer';

//Tables
$locale['status'] = 'Statut';
$locale['order'] = 'Ordre';
$locale['sort'] = 'Sortie';
$locale['id'] = 'ID';
$locale['title'] = 'Titre';
$locale['rights'] = 'Droits';
$locale['info'] = 'Info';
$locale['image'] = 'Image';

// Forms
$locale['choose'] = 'Veuillez entrer les renseignements...';
$locale['no_opts'] = 'Aucun choix';
$locale['root'] = 'Comme parent';
$locale['choose-user'] = 'Veuillez choisir un utilisateur...';
$locale['choose-location'] = 'Veuillez choisir un lieu';
$locale['parent'] = 'Créér comme nouveaux parents..';
$locale['order'] = 'Point de commande';
$locale['status'] = 'Statut';
$locale['note'] = 'Prenez note de cet objet';
$locale['publish'] = 'Publié';
$locale['unpublish'] = 'Non publié';
$locale['draft'] = 'Ébauche';
$locale['settings'] = 'Configuration';

$locale['posted'] = 'Rédigé le';
$locale['profile'] = 'Profil';
$locale['edit'] = 'Édition';
$locale['qedit'] = 'Édition rapide';
$locale['view'] = 'Voir';
$locale['login'] = "Entrer";
$locale['logout'] = 'Se Déconnecter';
$locale['admin-logout'] = 'Administrateur déconnecté';
$locale['message'] = 'Messages privés';
$locale['logged'] = 'Connecté en tant que ';
$locale['version'] = 'Version ';
$locale['browse'] = 'Parcourir...';
$locale['close'] = 'Fermer';
$locale['nopreview'] = "Il n'y a aucun aperçu";

//Alignment
$locale['left'] = "Gauche";
$locale['center'] = "Centre";
$locale['right'] = "Droit";

// Comments and ratings
$locale['comments'] = "Commentaires";
$locale['ratings'] = "Évaluations";
$locale['comments_ratings'] = "Commentaires et évaluations";
$locale['user_account'] = "Compte utilisateur";
$locale['about'] = "À propos de";

// User status
$locale['online'] = "En ligne";
$locale['offline'] = "Hors ligne";

// Words for formatting to single and plural forms. Count of forms is language-dependent
$locale['fmt_article'] = "article|articles";
$locale['fmt_blog'] = "blog|blogs";
$locale['fmt_comment'] = "commentaire|commentaires";
$locale['fmt_vote'] = "vote|votes";
$locale['fmt_rating'] = "évaluation|évaluations";

$locale['fmt_day'] = "jour|jours";
$locale['fmt_download'] = "téléchargement|téléchargements";
$locale['fmt_follower'] = "disciple|disciples";
$locale['fmt_forum'] = "forum|forums";
$locale['fmt_guest'] = "invité|invités";
$locale['fmt_hour'] = "heure|heures";
$locale['fmt_item'] = "élément|éléments";
$locale['fmt_member'] = "membre|membres";
$locale['fmt_message'] = "message|messages";
$locale['fmt_minute'] = "minute|minutes";
$locale['fmt_month'] = "mois|mois";
$locale['fmt_news'] = "nouvelle|nouvelles";
$locale['fmt_photo'] = "photo|photos";
$locale['fmt_post'] = "message|messages";
$locale['fmt_question'] = "question|questions";
$locale['fmt_read'] = "lecture|lectures";
$locale['fmt_second'] = "seconde|secondes";
$locale['fmt_shouts'] = "cri|cris";
$locale['fmt_thread'] = "fil|fils";
$locale['fmt_user'] = "utilisateur|utilisateurs";
$locale['fmt_views'] = "vue|vues";
$locale['fmt_weblink'] = "lien web|liens webb";
$locale['fmt_week'] = "semaine|semaines";
$locale['fmt_year'] = "an|ans";

// Load defender locale from here, is more reliable
// and now if part of the core, we could merge it in
include __DIR__."/defender.php";
?>