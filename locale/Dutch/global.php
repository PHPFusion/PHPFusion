<?php
/*
Nederlandse Locale Bestanden
Engels door Nick Jones (Digitanium)
E-mail: digitanium@php-fusion.co.uk
Web: http://www.php-fusion.co.uk
Vertaald door Paul Beuk (muscapaul) en Wim de Lange (Wanabo)
Nederlandstalige support site: http://www.phpfusion-nederlands.info
*/
// Locale Settings
// setlocale(LC_TIME, "nl","NL"); // Voor Windows Server
setlocale(LC_TIME, "nl_NL"); // Voor Linux Server
ini_set('default_charset', 'ISO-8859-1');
$locale['charset'] = "ISO-8859-1";
$locale['xml_lang'] = "nl";
$locale['tinymce'] = "nl";
$locale['phpmailer'] = "nl";

// Full & Short Months
$locale['months'] = "&nbsp|Januari|Februari|Maart|April|Mei|Juni|Juli|Augustus|September|Oktober|November|December";
$locale['shortmonths'] = "&nbsp|Jan|Feb|Maa|Apr|Mei|Jun|Jul|Aug|Sept|Okt|Nov|Dec";

// Standard User Levels
$locale['user0'] = "Publiek";
$locale['user1'] = "Lid";
$locale['user2'] = "Beheerder";
$locale['user3'] = "Superbeheerder";
$locale['user_na'] = "N/A";
$locale['user_anonymous'] = "Anonieme Gebruiker";
// Standard User Status
$locale['status0'] = "Actief";
$locale['status1'] = "Geband";
$locale['status2'] = "Nog te activeren";
$locale['status3'] = "Geschorst";
$locale['status4'] = "Veiligheids Verbanning";
$locale['status5'] = "Geannuleerd";
$locale['status6'] = "Anoniem";
$locale['status7'] = "Gedeactiveerd";
$locale['status8'] = "Inactief";
// Forum Moderator Level(s)
$locale['userf1'] = "Moderator";
// Navigation
$locale['global_001'] = "Navigatie";
$locale['global_002'] = "Geen links aangemaakt\n";
// Users Online
$locale['global_010'] = "Gebruikers Online";
$locale['global_011'] = "Gasten online";
$locale['global_012'] = "Leden online";
$locale['global_013'] = "Geen leden online";
$locale['global_014'] = "Totaal aantal leden";
$locale['global_015'] = "Niet-geactiveerde leden";
$locale['global_016'] = "Nieuwste lid";
// Forum Side panel
$locale['global_020'] = "Forum Onderwerpen";
$locale['global_021'] = "Nieuwste onderwerpen";
$locale['global_022'] = "Actiefste onderwerpen";
$locale['global_023'] = "Geen onderwerpen aanwezig";
// Comments Side panel
$locale['global_025'] = "Laatste Commentaar";
$locale['global_026'] = "Geen commentaar beschikbaar";
// Articles Side panel
$locale['global_030'] = "Laatste Artikelen";
$locale['global_031'] = "Geen artikelen beschikbaar";
// Downloads Side panel
$locale['global_032'] = "Laatste Downloads";
$locale['global_033'] = "Geen Downloads beschikbaar";
// Welcome panel
$locale['global_035'] = "Welkom";
// Latest Active Forum Threads panel
$locale['global_040'] = "Hieronder de recente Forum onderwerpen. Het <u><i>complete</i></u> Forum overzicht staat &raquo;<a href='".BASEDIR."forum/index.php' class='capmain' title='".$settings['siteusername']." forum overzicht' style='text-decoration: blink'>hier</a>&laquo;.</td><td class='capmain' style='text-align:right;'><a class='capmain' href='http://telfort.gebruikers.eu' target='_blank' title=''>&copy;</a>";
$locale['global_041'] = "Mijn recente onderwerpen";
$locale['global_042'] = "Mijn recente berichten";
$locale['global_043'] = "Nieuwe berichten";
$locale['global_044'] = "Onderwerpen";
$locale['global_045'] = "X bekeken";
$locale['global_046'] = "Antw.";
$locale['global_047'] = "Laatste bericht";
$locale['global_048'] = "Forum";
$locale['global_049'] = "Gepost";
$locale['global_050'] = "Auteur";
$locale['global_051'] = "Enquête";
$locale['global_052'] = "Verplaatst";
$locale['global_053'] = "U heeft nog geen onderwerpen in het forum gestart.";
$locale['global_054'] = "U heeft nog geen berichten in het forum geplaatst.";
$locale['global_055'] = "Er zijn %u nieuwe berichten sinds uw laatste bezoek.";
$locale['global_056'] = "Onderwerpen die ik volg";
$locale['global_057'] = "Opties";
$locale['global_058'] = "Stop";
$locale['global_059'] = "U volgt geen enkel onderwerp.";
$locale['global_060'] = "Stop met volgen van dit onderwerp?";
// News & Articles
$locale['global_070'] = "Gepost door ";
$locale['global_071'] = "op ";
$locale['global_072'] = "Lees meer";
$locale['global_073'] = " reacties";
$locale['global_073b'] = " reactie";
$locale['global_074'] = " keer gelezen";
$locale['global_075'] = "Afdrukken";
$locale['global_076'] = "Wijzigen";
$locale['global_077'] = "Nieuws";
$locale['global_078'] = "Er is nog geen nieuws geplaatst";
$locale['global_079'] = "In ";
$locale['global_080'] = "Nog niet gecategoriseerd";
// Page Navigation
$locale['global_090'] = "Vorige";
$locale['global_091'] = "Volgende";
$locale['global_092'] = "Pagina ";
$locale['global_093'] = " van ";
// Guest User Menu
$locale['global_100'] = "Inloggen";
$locale['global_101'] = "Gebruikersnaam";
$locale['global_102'] = "Wachtwoord";
$locale['global_103'] = "Onthouden";
$locale['global_104'] = "Inloggen";
$locale['global_105'] = "<font style='font-size:16px; font-weight:bold;'><b>Nog geen lid?</b></font><br /><br /><a href='".BASEDIR."register.php' class='side' title='Registreren'><font style='font-size:14px; font-weight:bold;'><b>&raquo; Registreer &laquo;</b></font></a><br />Als geregistreerd lid kunt u reageren en alle extra functies gebruiken.";
$locale['global_106'] = "Wachtwoord vergeten?<br />Verzoek <a href='".BASEDIR."lostpassword.php' class='side' title='Verzoek nieuw wachtoord'>nieuw</a> wachtwoord.";
$locale['global_107'] = "Aanmelden";
$locale['global_108'] = "Wachtwoord kwijt";


// User Menu
$locale['global_123'] = "Beheerder Paneel";
$locale['UM060'] = "Inloggen";
$locale['UM061'] = "Gebruikersnaam";
$locale['UM061a'] = "Email";
$locale['UM061b'] = "Gebruikersnaam of Email";
$locale['UM062'] = "Wachtwoord";
$locale['UM063'] = "Onthouden";
$locale['UM064'] = "Inloggen";
$locale['UM065'] = "<font style='font-size:16px; font-weight:bold;'><b>Nog geen lid?</b></font><br /><br /><a href='".BASEDIR."register.php' class='side' title='Registreren'><font style='font-size:14px; font-weight:bold;'><b>&raquo; Registreer &laquo;</b></font></a><br />Als geregistreerd lid kunt u reageren en alle extra functies gebruiken.";
$locale['UM066'] = "Wachtwoord vergeten?<br />Verzoek <a href='".BASEDIR."lostpassword.php' class='side' title='Verzoek nieuw wachtoord'>nieuw</a> wachtwoord.";
$locale['UM080'] = "Profiel aanpassen";
$locale['UM081'] = "Priv&eacute; Berichten";
$locale['UM082'] = "Ledenlijst";
$locale['UM083'] = "Beheerder Paneel";
$locale['UM084'] = "Uitloggen";
$locale['UM085'] = "U hebt %u nieuwe ";
$locale['UM086'] = "bericht";
$locale['UM087'] = "berichten";
$locale['UM088'] = "Gevolgde onderwerpen";
// Submit (news, link, article)
$locale['UM089'] = "Inzenden...";
$locale['UM090'] = "Nieuws inzenden";
$locale['UM091'] = "Link inzenden";
$locale['UM092'] = "Artikel inzenden";
$locale['UM093'] = "Fhoto inzenden";
$locale['UM094'] = "Download inzenden";
// User Panel
$locale['UM095'] = "Welkom: ";
$locale['UM096'] = "Persoonlijk menu";
$locale['UM097'] = "Kies taal";
// Gauges
$locale['UM098'] = "Berichten inbox:";
$locale['UM099'] = "Berichten outbox:";
$locale['UM100'] = "Berichten archief:";
// Poll
$locale['global_130'] = "Ledenenquête";
$locale['global_131'] = "Stemmen";
$locale['global_132'] = "U dient in te loggen om te stemmen.";
$locale['global_133'] = "stem";
$locale['global_134'] = "stemmen";
$locale['global_135'] = "Stemmen: ";
$locale['global_136'] = "Gestart: ";
$locale['global_137'] = "Geëindigd: ";
$locale['global_138'] = "Enquête-archief";
$locale['global_139'] = "Kies een enquête uit de lijst om die te bekijken:";
$locale['global_140'] = "Bekijken";
$locale['global_141'] = "Enquête bekijken";
$locale['global_142'] = "Er zijn nog geen enquêtes aangemaakt.";
// Captcha
$locale['global_150'] = "Validatie Code:";
$locale['global_151'] = "Voer Validatie Code in:";
// Footer Counter
$locale['global_170'] = "uniek bezoek";
$locale['global_171'] = "unieke bezoeken";
$locale['global_172'] = "Verwerkingstijd: %s seconden";
$locale['global_173'] = "Zoekopdrachten";
// Admin Navigation
$locale['global_180'] = "Beheerder Index";
$locale['global_181'] = "Terug naar site";
$locale['global_182'] = "<strong>Waarschuwing:</strong> Beheerderwachtwoord niet opgegeven of incorrect";
// Miscellaneous
$locale['global_190'] = "Onderhoudsmodus geactiveerd";
$locale['global_191'] = "Uw IP-adres staat op dit moment op de zwarte lijst.";
$locale['global_192'] = "Uw cookie is verlopen! Log opnieuw in om verder te gaan.";
$locale['global_193'] = "Kan geen cookie instellen! Sta a.u.b. cookies toe, controleer de instellingen van uw firewall of uw browser, om correct in te kunnen loggen.";
$locale['global_194'] = "Dit account is momenteel geschorst.";
$locale['global_195'] = "Dit account is nog niet geactiveerd.";
$locale['global_196'] = "Ongeldige gebruikersnaam of wachtwoord.";
$locale['global_197'] = "Wacht u a.u.b. terwijl we u doorsturen...<br /><br />
[ <a href='".BASEDIR."index.php'>Of klik hier indien u niet wenst te wachten.</a> ]";
$locale['global_198'] = "<strong>Waarschuwing:</strong> setup.php aangetroffen, a.u.b. onmiddellijk verwijderen.";
$locale['global_199'] = "<strong>Waarschuwing:</strong> beheerderwachtwoord niet ingesteld, klik <a href='".BASEDIR."edit_profile.php' title='Profiel wijzigen'>Profiel wijzigen</a> om dit in te stellen.";
//Titles
$locale['global_200'] = " - ";
$locale['global_201'] = ": ";
$locale['global_202'] = $locale['global_200']."Zoeken";
$locale['global_203'] = $locale['global_200']."FAQ";
$locale['global_204'] = $locale['global_200']."Forum";
//Themes
$locale['global_210'] = "Ga naar inhoud";
// No themes found
$locale['global_300'] = "geen thema gevonden";
$locale['global_301'] = "Het spijt ons zeer, maar deze pagina kan niet worden getoond. Door bepaalde omstandigheden kan geen site-thema worden gevonden. Indien u een beheerder van de site bent, gebruik dan a.u.b. uw FTP-programma om een voor <em>PHP-Fusion v7</em> ontworpen thema naar de <em>themes</em> map te uploaden. Controleer na de upload in <em>Algemene Instellingen</em> of het geselecteerde thema correct is ge-upload. Houdt er rekening mee dat de ge-uploade thema map exact dezelfde naam moet hebben (inclusief eventuele hoofdletters, van belang bij Unix-servers) als gekozen in <em>Algemene Instellingen</em>.<br /><br />Indien u een gewoon lid van de site bent, neemt u dan a.u.b. contact op met de beheerder van de site via ".hide_email($settings['siteemail'])." e-mail en meldt dit probleem.";
$locale['global_302'] = "Het thema gekozen in Algemene Instellingen bestaat niet of is incompleet.!";
// JavaScript Not Enabled
$locale['global_303'] = "<center>Oeps! <strong>JavaScript</strong> ontbreekt!<br />Uw Web browser heeft JavaScript uitstaan of ondersteund geen JavaScript.<br />Om deze website optimaal te kunnen zien dient u <strong>JavaScript aan te zetten</strong> in uw Web browser en/of Firewall.<br />Of <strong>verander</strong> van Web browser die wel JavaScript ondersteund; <a href='http://firefox.com' rel='nofollow' title='Mozilla Firefox'>Firefox</a>, <a href='http://apple.com/safari/' rel='nofollow' title='Safari'>Safari</a>, <a href='http://opera.com' rel='nofollow' title='Opera Web Browser'>Opera</a>, <a href='http://www.google.com/chrome' rel='nofollow' title='Google Chrome'>Chrome</a> of <a href='http://www.microsoft.com/windows/internet-explorer/' rel='nofollow' title='Internet Explorer'>Internet Explorer</a> nieuwer dan versie 6.</center>";
// User Management
// Member status
$locale['global_400'] = "geschorst";
$locale['global_401'] = "verbannen";
$locale['global_402'] = "gedeactiveerd";
$locale['global_403'] = "account beeindigd";
$locale['global_404'] = "account geanonimiseerd";
$locale['global_405'] = "annonieme gebruiker";
$locale['global_406'] = "Dit account is verbannen om de volgende reden:";
$locale['global_407'] = "Dit account is geschorst tot ";
$locale['global_408'] = " om de volgende reden:";
$locale['global_409'] = "Dit account is verbannen om veiligheidsredenen.";
$locale['global_410'] = "De reden hiervoor is: ";
$locale['global_411'] = "Dit account is geannuleerd.";
$locale['global_412'] = "Dit account is geanonimiseerd, waarschijnlijk door inactiviteit.";
// Banning due to flooding
$locale['global_440'] = "Automatische verbanning door Flood Control";
$locale['global_441'] = "Uw account op ".$settings['sitename']." is verbannen";
$locale['global_442'] = "Beste [USER_NAME],\n
Uw account op ".$settings['sitename']." heeft in korte tijd te veel berichten in het systeem geplaatst met IP ".USER_IP.", en is daarom verbannen. Dit is gedaan om snelle verspreiding van spam door robots te voorkomen.\n
Neem contact op met de site beheerder via ".$settings['siteemail']." om uw account vrij te geven of om te melden dat u het niet was die dit veroorzaakt heeft.\n
".$settings['siteusername'];
// Lifting of suspension
$locale['global_450'] = "Uw schorsing is automatisch opgeheven door het systeem";
$locale['global_451'] = "Schorsing opgeheven op ".$settings['sitename'];
$locale['global_452'] = "Beste USER_NAME,\n
De schorsing van uw account op ".$settings['siteurl']." is opgeheven. Hier zijn uw login gegevens:\n
Gebruikersnaam: USER_NAME\n
Wachtwoord: verborgen wegens veiligheid redenen\n\n
Als u uw wachtwoord bent vergeten, kunt u hier het wachtwoord opvragen:LOST_PASSWORD\n\n
Met vriendelijke groet,\n
".$settings['siteusername'];
$locale['global_453'] = "Beste USER_NAME,\n
De schorsing van uw account op ".$settings['siteurl']." is opgeheven.\n\n
Met vriendelijke groet,\n
".$settings['siteusername'];
$locale['global_454'] = "Account gereactiveerd op ".$settings['sitename'];
$locale['global_455'] = "Beste USER_NAME,\n
De laatste keer dat u bent ingelogd is uw account gereactiveerd op ".$settings['siteurl']." en is uw account niet langer meer als inactief gemarkeerd.\n\n
Met vriendelijke groet,\n
".$settings['siteusername'];
// Function parsebytesize()
$locale['global_460'] = "Leeg";
$locale['global_461'] = "Bytes";
$locale['global_462'] = "KB";
$locale['global_463'] = "MB";
$locale['global_464'] = "GB";
$locale['global_465'] = "TB";
//Safe Redirect
$locale['global_500'] = "U wordt doorgestuurd naar %s, wacht een moment. Als u niet wordt doorgestuurd, klikt u hier.";
// Captcha Locales
$locale['global_600'] = "Validatie Code";
$locale['recaptcha'] = "en";
//Miscellaneous
$locale['global_900'] = "Niet in staat om HEX naar DEC te converteren";
//Language Selection
$locale['global_ML100'] = "Taal:";
$locale['global_ML101'] = "- Selecteer Taal -";
$locale['global_ML102'] = "Site taal";

$locale['flood'] = "U mag niet meer posten totdat de Flood periode voorbij is. Wacht a.u.b. %t";

?>
