<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2010 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: locale/Swedish/global.php
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. 
+--------------------------------------------------------+
| Removal of this copyright header is strictly prohibited 
| without written permission from the original author(s).
+--------------------------------------------------------+
| This file is part of the PHP-Fusion localization 
| standard.
+--------------------------------------------------------+
| Locale: Swedish
| PHP-Fusion version: 7.02.04
+--------------------------------------------------------+
| Originally translated by KEFF in 2004.
| Regular updates by KEFF, Paulsson, Mojkan and others.
| There would hardly be any Swedish PHP-Fusion 
| without them!
+--------------------------------------------------------+
| Last changed 12 nov 2011, Homdax.
| Credits to:
| Danne for extensive help with this version.
| Lilleman72 & DrunkeN for help with recent versions. 
| homdax@gmail.com, www.php-fusion.se
+--------------------------------------------------------*/
// Locale Settings
setlocale(LC_TIME, "swedish"); // Linux Server (Windows may differ)
$locale['charset'] = "iso-8859-1";
$locale['xml_lang'] = "sv";
$locale['tinymce'] = "sv";
$locale['phpmailer'] = "en";
// Full & Short Months
$locale['months'] = "&nbsp|Januari|Februari|Mars|April|Maj|Juni|Juli|Augusti|September|Oktober|November|December";
$locale['shortmonths'] = "&nbsp|Jan|Feb|Mar|Apr|Maj|Jun|Jul|Aug|Sep|Okt|Nov|Dec";
// Standard User Levels
$locale['user0'] = "Besökare";
$locale['user1'] = "Användare";
$locale['user2'] = "Administratör";
$locale['user3'] = "Superadministratör";
$locale['user_na'] = "N/A";
$locale['user_anonymous'] = "Anonym användare";
// Standard User Status
$locale['status0'] = "Aktiva";
$locale['status1'] = "Avstängda";
$locale['status2'] = "Inaktiverad";
$locale['status3'] = "Uteslutna";
$locale['status4'] = "Säkerhetsavstängda";
$locale['status5'] = "Avbrutna";
$locale['status6'] = "Anonyma";
$locale['status7'] = "Avaktiverade";
$locale['status8'] = "Inaktiva";
// Forum Moderator Level(s)
$locale['userf1'] = "Moderator";
// Navigation
$locale['global_001'] = "Navigation";
$locale['global_002'] = "Det finns inga länkar definierade\n";
// Users Online
$locale['global_010'] = "Inloggade användare";
$locale['global_011'] = "Besökare";
$locale['global_012'] = "Inloggade användare";
$locale['global_013'] = "Inga användare inloggade";
$locale['global_014'] = "Registrerade användare";
$locale['global_015'] = "Ej aktiverade";
$locale['global_016'] = "Senast registrerade användare";
// Forum Side panel
$locale['global_020'] = "Forum ämnen";
$locale['global_021'] = "Senaste ämne";
$locale['global_022'] = "Mest aktiva ämne";
$locale['global_023'] = "Det finns inga ämnen";
// Comments Side panel
$locale['global_025'] = "Senaste kommentarer";
$locale['global_026'] = "Inga kommentarer tillgängliga";
// Articles Side panel
$locale['global_030'] = "Senaste artiklarna";
$locale['global_031'] = "Inga tillgängliga artiklar";
// Downloads Side panel
$locale['global_032'] = "Senaste nedladdningar";
$locale['global_033'] = "Inga tillgängliga nedladdningar";
// Welcome panel
$locale['global_035'] = "Välkommen";
// Latest Active Forum Threads panel
$locale['global_040'] = "Senaste aktiva ämne";
$locale['global_041'] = "Mina senaste ämnen";
$locale['global_042'] = "Mina senaste inlägg";
$locale['global_043'] = "Nya inlägg";
$locale['global_044'] = "Ämne";
$locale['global_045'] = "Antal visningar";
$locale['global_046'] = "Svar";
$locale['global_047'] = "Senaste inlägg";
$locale['global_048'] = "Forum";
$locale['global_049'] = "Publicerat";
$locale['global_050'] = "Författare";
$locale['global_051'] = "Omröstning";
$locale['global_052'] = "Flyttat";
$locale['global_053'] = "Du har inte skapat några ämnen än.";
$locale['global_054'] = "Du har inte skrivit några inlägg än.";
$locale['global_055'] = "Det finns %u nya inlägg sedan ditt senaste besök.";
$locale['global_056'] = "Prenumerationer";
$locale['global_057'] = "Alternativ";
$locale['global_058'] = "Radera";
$locale['global_059'] = "Du har inte prenumererat på något ämne.";
$locale['global_060'] = "Radera prenumeration?";
// News & Articles
$locale['global_070'] = "Inlägg skrivet av ";
$locale['global_071'] = "den ";
$locale['global_072'] = "Läs mer";
$locale['global_073'] = " Kommentarer";
$locale['global_073b'] = " Kommentera";
$locale['global_074'] = " Visningar";
$locale['global_075'] = "Skriv ut";
$locale['global_076'] = "Redigera";
$locale['global_077'] = "Nyheter";
$locale['global_078'] = "Det finns inga nyheter än";
$locale['global_079'] = "I ";
$locale['global_080'] = "Okategoriserat";
// Page Navigation
$locale['global_090'] = "Föregående";
$locale['global_091'] = "Nästa";
$locale['global_092'] = "Sida ";
$locale['global_093'] = " av ";
// Guest User Menu
$locale['global_100'] = "Logga in";
$locale['global_101'] = "Användarnamn";
$locale['global_102'] = "Lösenord";
$locale['global_103'] = "Kom ihåg mig";
$locale['global_104'] = "Logga in";
$locale['global_105'] = "<br />Om ni inte redan är en registrerad användare<br /><a class='button3' href='".BASEDIR."register.php'>registrerar ni er här.</a>";
$locale['global_106'] = "Har ni förlorat lösenordet?<br />Då kan ni begära ett nytt <a href='".BASEDIR."lostpassword.php'>här</a>";
$locale['global_107'] = "Registrera";
$locale['global_108'] = "Förlorat lösenord";
// Member User Menu
$locale['global_120'] = "Redigera din profil";
$locale['global_121'] = "Privata meddelanden";
$locale['global_122'] = "Användarlista";
$locale['global_123'] = "Administrationspanel";
$locale['global_124'] = "Logga ut";
$locale['global_125'] = "Du har %u nytt/nya ";
$locale['global_126'] = "meddelande";
$locale['global_127'] = "meddelanden";
$locale['global_128'] = "bidrag";
$locale['global_129'] = "bidragen";
// Poll
$locale['global_130'] = "Omröstning";
$locale['global_131'] = "Rösta";
$locale['global_132'] = "Du måste vara inloggad för att rösta.";
$locale['global_133'] = "Rösta";
$locale['global_134'] = "Röster";
$locale['global_135'] = "Röster: ";
$locale['global_136'] = "Påbörjad: ";
$locale['global_137'] = "Avslutad: ";
$locale['global_138'] = "Omröstningsarkiv";
$locale['global_139'] = "Välj en omröstning du vill visa från listan:";
$locale['global_140'] = "Visa";
$locale['global_141'] = "Visa omröstning";
$locale['global_142'] = "Det finns inga omröstningar.";
// Captcha
$locale['global_150'] = "Säkerhetskod";
$locale['global_151'] = "Skriv säkerhetskod:";

// Footer Counter
$locale['global_170'] = "unikt besök";
$locale['global_171'] = "unika besök";
$locale['global_172'] = "Sidan uppdaterad på %s sekunder";
$locale['global_173'] = "Förfrågningar";
// Admin Navigation
$locale['global_180'] = "Administation";
$locale['global_181'] = "Tillbaka";
$locale['global_182'] = "<strong>Varning</strong>: administratörlösenordet ej angivet eller ej giltigt.";
// Miscellaneous
$locale['global_190'] = "Underhållsläge aktiverat";
$locale['global_191'] = "Ditt IP - nummer är för närvarande blockerat.";
$locale['global_192'] = "Loggar ut som ";
$locale['global_193'] = "Loggar in som ";
$locale['global_194'] = "Det här kontot är spärrat.";
$locale['global_195'] = "Det här kontot har ännu inte aktiverats.";
$locale['global_196'] = "Ogiltigt användarnamn eller lösenord.";
$locale['global_197'] = "Vänta medan du förflyttas...<br /><br />[ <a href='index.php'>Eller klicka här om du inte vill vänta</a> ]";
$locale['global_198'] = "<strong>Varning:</strong> filen setup.php har upptäckts, vänligen radera den omedelbart.";
$locale['global_199'] = "<strong>Varning:</strong> administratörslösenord är inte angivet, klicka <a href='".BASEDIR."edit_profile.php'>Redigera profil</a> för att ange det.";
//Titles
$locale['global_200'] = " - ";
$locale['global_201'] = ": ";
$locale['global_202'] = $locale['global_200']."Sök";
$locale['global_203'] = $locale['global_200']."FAQ";
$locale['global_204'] = $locale['global_200']."Forum";
//Themes
$locale['global_210'] = "Gå vidare till innehåll";
// No themes found
$locale['global_300'] = "Inget tema angivet i inställningarna";
$locale['global_301'] = "Den här sidan kan inte visas korrekt. På grund av okänd omständighet kan inget tema hittas. Om du är superadministratör, vänligen använd din FTP - klient för att ladda upp ett tema gjort för <em>PHP-Fusion v7</em> till <em>tema/</em> mappen. Sök därefter i <em>Huvudinställningar</em> för att se om valt tema återfinns i <em>tema/</em> mappen. Notera att valt tema måste ha exakt samma namn som är valt i <em>Huvudinställningar</em> sidan.<br /><br />Om du är normalanvändare på sidan, vänligen kontakta sidans superadministratör via ".hide_email($settings['siteemail'])." epost och rapportera felet.";
$locale['global_302'] = "Valt tema i huvudinställningar finns inte eller är inte färdigt!";
// JavaScript Not Enabled
$locale['global_303'] = "Åh nej! Var är <strong>JavaScript</strong>?<br />Din webbläsare har inte JavaScript aktiverat eller så stöder den inte JavaScript. Vänligen <strong>aktivera JavaScript</strong> i din webbläsare för att kunna visa denna webbplats,<br /> eller <strong>uppdatera</strong> till en webbläsare som stödjer JavaScript; <a href='http://firefox.com' rel='nofollow' title='Mozilla Firefox'>Firefox</a>, <a href='http://apple.com/safari/' rel='nofollow' title='Safari'>Safari</a>, <a href='http://opera.com' rel='nofollow' title='Opera Web Browser'>Opera</a>, <a href='http://www.google.com/chrome' rel='nofollow' title='Google Chrome'>Chrome</a> eller en version av <a href='http://www.microsoft.com/windows/internet-explorer/' rel='nofollow' title='Internet Explorer'>Internet Explorer</a> som är nyare än version 6.";
// Member status
$locale['global_400'] = "utesluten";
$locale['global_401'] = "avstängd";
$locale['global_402'] = "deaktiverad";
$locale['global_403'] = "konto avstängt";
$locale['global_404'] = "konto anonymiserat";
$locale['global_405'] = "anonym användare";
$locale['global_406'] = "Detta konto har blivit avstängt på grund av följande skäl:";
$locale['global_407'] = "Detta konto har blivit upphävts till ";
$locale['global_408'] = " på grund av följande skäl:";
$locale['global_409'] = "Detta konto är avstängt av säkerhetsskäl.";
$locale['global_410'] = "Anledning till det är: ";
$locale['global_411'] = "Detta konto är upphävt.";
$locale['global_412'] = "Detta konto har blivit anonymiserat, vanligen på grund av inaktivitet.";
// Banning due to flooding
$locale['global_440'] = "Automatic Ban by Flood Control";
$locale['global_441'] = "Ditt konto hos ".$settings['sitename']."har blivit avstängt.";
$locale['global_442'] = "Hej [USER_NAME],\n
Ditt konto hos ".$settings['sitename']." har ertappats med att skriva allt för många inlägg/klotter på kort tid från IP ".USER_IP.", och har därför blivit avstängt. Detta göres per automatik för att skydda siten från så kallade SpamBots.\n
Vänligen kontakta Administratören hos ".$settings['siteemail']." i syfte att häva denna spärr eller förklara varför det skett.\n
".$settings['siteusername'];
// Lifting of suspension
$locale['global_450'] = "Avstängning upphävs automatiskt av systemet.";
$locale['global_451'] = "Avstängning har upphört hos ".$settings['sitename'];
$locale['global_452'] = "Hej USER_NAME,\n
Avstängning av ditt konto hos ".$settings['siteurl']." har deaktiverats. Här är dina inloggningsuppgifter:\n
Username: USER_NAME
Password: Skyddat av säkerhetsskäl\n
Om du har glömt ditt lösenord och få ett nytt utskickat till din e-post adress: LOST_PASSWORD\n\n
Hälsningar,\n
".$settings['siteusername'];
$locale['global_453'] = "Hej USER_NAME,\n
Avstängning av ditt konto hos ".$settings['siteurl']." har deaktiverats.\n\n
Hälsningar,\n
".$settings['siteusername'];
$locale['global_454'] = "Konto återaktiverat hos ".$settings['sitename'];
$locale['global_455'] = "Hej USER_NAME,\n
Senast du loggade in blev ditt konto återaktiverat ".$settings['siteurl']."och ditt konto är inte längre markerat som inaktivt.\n\n
Hälsningar,\n
".$settings['siteusername'];
// Function parsebytesize()
$locale['global_460'] = "Tom";
$locale['global_461'] = "Bytes";
$locale['global_462'] = "kB";
$locale['global_463'] = "MB";
$locale['global_464'] = "GB";
$locale['global_465'] = "TB";
//Safe Redirect
$locale['global_500'] = "Du omdirigeras nu till %s, ett ögonblick. Om du inte förs vidare, klicka här.";

// Captcha Locales
$locale['global_600'] = "Säkerhetskod";
$locale['recaptcha'] = "se";

//Miscellaneous
$locale['global_900'] = "Det går inte att konvertera HEX till DEC";
?>