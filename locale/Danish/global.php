<?php
/*
Danish Language Fileset
Produced by Jan Moelgaard (janmol)
Email: janmol@php-fusion.dk
Web: http://www.php-fusion.dk
*/

// Locale Settings
setlocale(LC_TIME, "da","DA"); // Linux Server (Windows may differ)
$locale['charset'] = "iso-8859-1";
$locale['xml_lang'] = "da";
$locale['tinymce'] = "da";
$locale['phpmailer'] = "da";
$locale['recaptcha'] = "da";

// Full & Short Months
$locale['months'] = "&nbsp|Januar|Februar|Marts|April|Maj|Juni|Juli|August|September|Oktober|November|December";
$locale['shortmonths'] = "&nbsp|Jan|Feb|Mar|Apr|Maj|Jun|Jul|Aug|Sep|Okt|Nov|Dec";

// Standard User Levels
$locale['user0'] = "Gæst";
$locale['user1'] = "Bruger";
$locale['user2'] = "Administrator";
$locale['user3'] = "Super Administrator";
$locale['user_na'] = "N/A";
$locale['user_anonymous'] = "Anonym Bruger";
// Standard User Status
$locale['status0'] = "Aktiv";
$locale['status1'] = "Udelukket";
$locale['status2'] = "Ikke aktiveret";
$locale['status3'] = "Suspenderet";
$locale['status4'] = "Udelukket af sikkerhedsårsager";
$locale['status5'] = "Annulleret";
$locale['status6'] = "Anonym";
$locale['status7'] = "Deaktiveret";
$locale['status8'] = "Inaktiv";
// Forum Moderator Level(s)
$locale['userf1'] = "Ordstyrer";
// Navigation
$locale['global_001'] = "Navigation";
$locale['global_002'] = "Der er ikke oprettet links\n";
// Users Online
$locale['global_010'] = "Besøgende";
$locale['global_011'] = "Gæster online";
$locale['global_012'] = "Brugere online";
$locale['global_013'] = "Ingen brugere online";
$locale['global_014'] = "Antal brugere";
$locale['global_015'] = "Ikke aktiverede";
$locale['global_016'] = "Nyeste bruger";
// Forum Side panel
$locale['global_020'] = "Debatemner";
$locale['global_021'] = "Nyeste emne";
$locale['global_022'] = "Mest aktive emner";
$locale['global_023'] = "Ingen emner oprettet";
// Comments Side panel
$locale['global_025'] = "Seneste kommentarer";
$locale['global_026'] = "Der er ikke skrevet kommentarer";
// Articles Side panel
$locale['global_030'] = "Nyeste artikler";
$locale['global_031'] = "Ingen artikler tilgængelige";
// Downloads Side panel
$locale['global_032'] = "Seneste downloads";
$locale['global_033'] = "Der er ikke oprettet downloads";
// Welcome panel
$locale['global_035'] = "Velkommen";
// Latest Active Forum Threads panel
$locale['global_040'] = "Senest aktive debatemner";
$locale['global_041'] = "Mine nyeste emner";
$locale['global_042'] = "Mine nyeste emner";
$locale['global_043'] = "Nye indlæg";
$locale['global_044'] = "Debat";
$locale['global_045'] = "Visninger";
$locale['global_046'] = "Svar";
$locale['global_047'] = "Nyeste indlæg";
$locale['global_048'] = "Forum";
$locale['global_049'] = "Skrevet";
$locale['global_050'] = "Forfatter";
$locale['global_051'] = "Afstemning";
$locale['global_052'] = "Flyttet";
$locale['global_053'] = "Du har ikke oprettet debatemner endnu.";
$locale['global_054'] = "Du har ikke oprettet debatindlæg endnu.";
$locale['global_055'] = "Der er kommet %u nye indlæg siden dit sidste besøg.";
$locale['global_056'] = "Mine udvalgte emner";
$locale['global_057'] = "Valgmuligheder";
$locale['global_058'] = "Stop";
$locale['global_059'] = "Du har ikke udvalgt emner.";
$locale['global_060'] = "Fravælg dette emne?";
// News & Articles
$locale['global_070'] = "Skrevet af ";
$locale['global_071'] = "d. ";
$locale['global_072'] = "Læs mere";
$locale['global_073'] = " Kommentarer";
$locale['global_073b'] = " Kommentar";
$locale['global_074'] = " Fremvisninger";
$locale['global_075'] = "Udskriv";
$locale['global_076'] = "Rediger";
$locale['global_077'] = "Nyheder";
$locale['global_078'] = "Der er ikke oprettet nyheder endnu";
$locale['global_079'] = "I ";
$locale['global_080'] = "Ukategoriseret";
// Page Navigation
$locale['global_090'] = "Foregående";
$locale['global_091'] = "Næste";
$locale['global_092'] = "Side ";
$locale['global_093'] = " af ";
// Guest User Menu
$locale['global_100'] = "Log ind";
$locale['global_101'] = "Brugernavn";
$locale['global_102'] = "Kodeord";
$locale['global_103'] = "Husk mig";
$locale['global_104'] = "Log ind";
$locale['global_105'] = "Er du endnu ikke registreret bruger?<br /><a href='".BASEDIR."register.php' class='side'>Klik her</a> for at oprette dig.";
$locale['global_106'] = "Har du glemt dit kodeord?<br />Bed om et nyt <a href='".BASEDIR."lostpassword.php' class='side'>ved at klikke her</a>.";
$locale['global_107'] = "Tilmelding";
$locale['global_108'] = "Glemt kodeord";
// Member User Menu
$locale['global_120'] = "Rediger profil";
$locale['global_121'] = "Beskeder";
$locale['global_122'] = "Brugerliste";
$locale['global_123'] = "Administration";
$locale['global_124'] = "Log ud";
$locale['global_125'] = "Der er %u <br />";
$locale['global_126'] = "ny besked til dig";
$locale['global_127'] = "nye beskeder til dig";
$locale['global_128'] = "forslag";
$locale['global_129'] = "forslag";
// Poll
$locale['global_130'] = "Brugerafstemning";
$locale['global_131'] = "Stem";
$locale['global_132'] = "Du er nødt til at logge på for at stemme.";
$locale['global_133'] = "Stemme";
$locale['global_134'] = "Stemmer";
$locale['global_135'] = "Stemmer: ";
$locale['global_136'] = "Påbegyndt: ";
$locale['global_137'] = "Afsluttet: ";
$locale['global_138'] = "Afstemningsarkiv";
$locale['global_139'] = "Vælg en afstemning fra listen:";
$locale['global_140'] = "Se";
$locale['global_141'] = "Se afstemning";
$locale['global_142'] = "Der er endnu ikke oprettet afstemninger.";
// Shoutbox
$locale['global_150'] = "Replikboks";
$locale['global_151'] = "Navn:";
// Footer Counter
$locale['global_170'] = "Unikt besøg";
$locale['global_171'] = "Unikke besøg";
$locale['global_172'] = "Siden dannet på: %s sekunder";
$locale['global_173'] = "Forespørgsler";
// Admin Navigation
$locale['global_180'] = "Administration";
$locale['global_181'] = "Forside";
$locale['global_182'] = "<strong>Bemærk</strong>: Administratorkodeordet er ikke blevet indtastet eller er ikke korrekt.";
// Miscellaneous
$locale['global_190'] = "Vedligeholdelsestilstand er aktiveret";
$locale['global_191'] = "Din IP adresse er aktuelt udelukket.";
$locale['global_192'] = "Logger ud som ";
$locale['global_193'] = "Logger ind som ";
$locale['global_194'] = "Denne konto er aktuelt lukket.";
$locale['global_195'] = "Denne konto er endnu ikke aktiveret.";
$locale['global_196'] = "Forkert brugernavn eller kodeord.";
$locale['global_197'] = "Vent et øjeblik, mens vi overfører dig ...<br /><br />
[ <a href='index.php'>Eller klik her, hvis du ikke ønsker at vente</a> ]";
$locale['global_198'] = "<strong>Advarsel:</strong> Du har glemt at slette setup.php. Slet den med det samme!";
$locale['global_199'] = "<strong>Advarsel:</strong> administratorkodeord er ikke oprettet. Klik på <a href='".BASEDIR."edit_profile.php'>Rediger profil</a> for at oprette det.";
//Titles
$locale['global_200'] = " - ";
$locale['global_201'] = ": ";
$locale['global_202'] = $locale['global_200']."Søg";
$locale['global_203'] = $locale['global_200']."FAQ";
$locale['global_204'] = $locale['global_200']."Debat";
//Themes
$locale['global_210'] = "Spring til indhold";
// No themes found
$locale['global_300'] = "intet tema fundet";
$locale['global_301'] = "Vi beklager meget, men siden kan ikke vises. Af ukendte årsager kan sidens tema ikke findes. 
Hvis du er administrator på siden, så brug din FTP-klient til at uploade et tema designet til brug i forbindelse med 
<strong>PHP-Fusion version 7</strong> til folderen <strong>themes/</strong>. Efter at du har gjort det, skal du se 
under <strong>Hovedopsætning</strong> for at sikre dig, at det uploadede tema er kommet korrekt op på siden. 
Bemærk at den uploadede temafolder skal have nøjagtigt samme navn inklusive store og små bogstaver som navnet 
på det tema, du vælger under <strong>Hovedopsætning</strong>.<br /><br /> Hvis du er medlem på siden, så skal 
du kontakte sidens administrator via ".hide_email($settings['siteemail'])." mail og rapportere om problemet.";
$locale['global_302'] = "Det tema, som du har valgt under hovedopsætning eksisterer ikke eller er inkompatibelt!";
// JavaScript Not Enabled
$locale['global_303'] = "Jamen dog! Hvor finder vi det stærke <strong>JavaScript</strong>?<br />Din browser understøtter ikke
JavaScript eller har ikke understøttelsen slået til. Slå <strong>JavaScript til</strong> i din browser for at se denne side
ordentligt,<br /> eller <strong>opgrader</strong> til en browser, der understøtter JavaScript; <a href='http://firefox.com' rel='nofollow' 
title='Mozilla Firefox'>Firefox</a>, <a href='http://apple.com/safari/' rel='nofollow' title='Safari'>Safari</a>, 
<a href='http://opera.com' rel='nofollow' title='Opera Web Browser'>Opera</a>, <a href='http://www.google.com/chrome' 
rel='nofollow' title='Google Chrome'>Chrome</a> eller en version af <a href='http://www.microsoft.com/windows/internet-explorer/' 
rel='nofollow' title='Internet Explorer'>Internet Explorer</a> nyere end version 6.";
// User Management
// Member status
$locale['global_400'] = "suspenderet";
$locale['global_401'] = "udelukket";
$locale['global_402'] = "deaktiveret";
$locale['global_403'] = "brugerkontoen lukket";
$locale['global_404'] = "brugerkontoen anonymiseret";
$locale['global_405'] = "anonym bruger";
$locale['global_406'] = "Denne brugerkonto er udelukket af følgende årsag:";
$locale['global_407'] = "Denne brugerkonto er suspenderet indtil ";
$locale['global_408'] = " af følgende årsag:";
$locale['global_409'] = "Denne konto er blevet udelukket af sikkerhedsårsager.";
$locale['global_410'] = "Begrundelsen er: ";
$locale['global_411'] = "Denne konto er blevet sat i passiv tilstand.";
$locale['global_412'] = "Denne konto er blevet anonymiseret sandsynligvis på grund af manglende aktivitet.";
// Banning due to flooding
$locale['global_440'] = "Automatisk udelukkelse via Flood Control";
$locale['global_441'] = "Din brugerkonto på ".$settings['sitename']."er blevet udelukket";
$locale['global_442'] = "Hej [USER_NAME]\n
Din konto på ".$settings['sitename']." blev registreret fordi den offentliggjorde for mange indlæg på for kort tid fra IP-adressen ".USER_IP.". Derfor er kontoen blevet udelukket. Dette sker for at forhindre, at scripts kan lægge spam ind meget hurtigt.\n
Kontakt administratoren på ".$settings['siteemail']." for at få genåbnet din konto eller dokumenter, at det ikke var dig der udløste denne udelukkelse.\n
".$settings['siteusername'];
// Lifting of suspension
$locale['global_450'] = "Udelukkelsen er automatisk ophævet af systemet";
$locale['global_451'] = "Udelukkelse er ophævet på ".$settings['sitename'];
$locale['global_452'] = "Hej USER_NAME\n
Udelukkelsen af din konto på ".$settings['siteurl']." er blevet ophævet. Her er dine pålogningsoplysninger:\n
Brugernavn: USER_NAME
Kodeord: Skjult af sikkerhedsårsager\n
Hvis du har glemt dit kodeord kan du oprette et nyt via følgende link: LOST_PASSWORD\n\n
Venlig hilsen\n
".$settings['siteusername'];
$locale['global_453'] = "Hej USER_NAME\n
Udelukkelsen af din konto på ".$settings['siteurl']." er blevet ophævet.\n\n
Venlig hilsen\n
".$settings['siteusername'];
$locale['global_454'] = "Kontoen er genaktiveret på ".$settings['sitename'];
$locale['global_455'] = "Hej USER_NAME\n
Sidste gang du loggede på, blev din konto reaktiveret på ".$settings['siteurl']." og du er ikke længere registreret som inaktiv.\n\n
Venlig hilsen\n
".$settings['siteusername'];
// Function parsebytesize()
$locale['global_460'] = "Tom";
$locale['global_461'] = "Bytes";
$locale['global_462'] = "kB";
$locale['global_463'] = "MB";
$locale['global_464'] = "GB";
$locale['global_465'] = "TB";
//Safe Redirect
$locale['global_500'] = "Vi sender dig videre til %s, vent venligst. Hvis du ikke bliver sendt videre, så klik her.";

// Captcha Locales
// Captcha Locales
$locale['global_600'] = "Sikkerhedskode";
$locale['recaptcha'] = "da";

//Miscellaneous
$locale['global_900'] = "Ude af stand til at konvertere HEX til DEC";
?>