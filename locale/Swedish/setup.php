<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2010 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: locale/Swedish/setup.php
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
$locale['title'] = "PHP-Fusion Core 7 Edition Installation";
$locale['sub-title'] = "PHP-Fusion Core 7 Edition Installation";

$locale['charset'] = "iso-8859-1";
$locale['001'] = "Steg 1: Välj språkfil";
$locale['002'] = "Steg 2: Test av fil- och mapprättigheter";
$locale['003'] = "Steg 3: Databasinställningar";
$locale['004'] = "Steg 4: Konfiguration och databasinstallation";
$locale['005'] = "Steg 5: Administratörens inloggningsuppgifter";
$locale['006'] = "Steg 6: Slutgiltiga inställningar";
$locale['007'] = "Nästa";
$locale['008'] = "Tillbaka";
$locale['009'] = "Slutför";
// Step 1
$locale['010'] = "Vänligen välj språk:";
$locale['011'] = "Ladda ner fler språk från <a href='http://www.php-fusion.co.uk'>php-fusion.co.uk</a>";
// Step 2
$locale['020'] = "För att fortsätta installationen måste följande filer och mappar vara skrivbara:";
$locale['021'] = "Skrivrättighetskontroll godkänd, tryck Nästa för att fortsätta.";
$locale['022'] = "Skrivrättighetskontroll ej godkänd, vänligen ändra detta på filer och mappar som markerats som ej godkända.";
$locale['023'] = "Godkänd";
$locale['024'] = "Misslyckades";
$locale['025'] = "Uppdatera";
// Step 3 - Access criteria
$locale['030'] = "Vänligen ange dina inställningar för MySQL.";
$locale['031'] = "Värdnamn databas:";
$locale['032'] = "Användarnamn databas:";
$locale['033'] = "Lösenord databas:";
$locale['034'] = "Databasnamn:";
$locale['035'] = "Tabellprefix:";
$locale['036'] = "Cookie Prefix:";
// Step 4 - Database Setup
$locale['040'] = "Databaskontakt etablerad.";
$locale['041'] = "Konfigurationsfil skapad.";
$locale['042'] = "Databastabeller skapade.";
$locale['043'] = "Fel:";
$locale['044'] = "Kontakt med databasen kunde ej upprättas.";
$locale['045'] = "Vänligen kontrollera dina inställningar för MySQL.";
$locale['046'] = "Det gick ej att skriva till config.php.";
$locale['047'] = "Vänligen kontrollera att config.php ej är skrivskyddad.";
$locale['048'] = "Kunde ej skapa databastabeller.";
$locale['049'] = "Vänligen ange rätt databasnamn.";
$locale['050'] = "Kan ej ansluta till MySQL databas.";
$locale['051'] = "Angiven MySQL databas existerar inte.";
$locale['052'] = "Fel i angivet tabellprefix.";
$locale['053'] = "Angivet tabellprefix används redan.";
$locale['054'] = "Kan inte skriva till eller radera MySQL tabeller.";
$locale['055'] = "Kontrollera att du har tillräckliga behörigheter för att ändra, radera och skriva till angiven MySQL databas.";
$locale['056'] = "Töm alla fält.";
$locale['057'] = "Kontrollera att du fyllt i alla fält för att kunna ansluta till angiven MySQL databas.";
// Step 5 - Super Admin login
$locale['060'] = "Superadministratörens inloggningsuppgifter";
$locale['061'] = "Användarnamn:";
$locale['062'] = "Lösenord:";
$locale['063'] = "Repetera lösenord:";
$locale['064'] = "Administratörslösenord:";
$locale['065'] = "Repetera administratörslösenord:";
$locale['066'] = "E-post adress:";
// Step 6 - User details validation
$locale['070'] = "Användarnamnet innehåller otillåtna tecken.";
$locale['070b'] = "Användarnamn <strong>måste</strong> anges.";
$locale['071'] = "Lösenorden är ej identiska.";
$locale['072'] = "Ogiltigt lösenord, använd endast alfanumeriska tecken.<br />Lösenordet måste vara minst 6 tecken långt.";
$locale['072b'] = "Lösenord <strong>måste</strong> fyllas i.";
$locale['073'] = "Administratörslösenorden är inte identiska.";
$locale['074'] = "Ditt lösenord och administratörslösenordet måste vara olika.";
$locale['075'] = "Ogiltigt administratörslösenord, använd enbart alfanumeriska tecken.<br />Lösenordet måste vara minst 6 tecken långt.";
$locale['075b'] = "Administratörslösenord <strong>måste</strong> fyllas i.";
$locale['076'] = "Den angivna e-post adressen förefaller felaktig.";
$locale['076b'] = "E-post adress<strong>måste</strong> fyllas i.";
$locale['077'] = "Det är fel i dina användarinställningar:";
// Step 6 - Admin Panels
$locale['080'] = "Administratörer";
$locale['081'] = "Artikelkategorier";
$locale['082'] = "Artiklar";
$locale['083'] = "Logotyper";
$locale['084'] = "BB kod";
$locale['085'] = "Spärrlista";
$locale['086'] = "Kommentarer";
$locale['087'] = "Användarsidor";
$locale['088'] = "Säkerhetskopiering databas";
$locale['089'] = "Kategorier Filarkiv";
$locale['090'] = "Filarkiv";
$locale['091'] = "FAQ";
$locale['092'] = "Forum";
$locale['093'] = "Bilder";
$locale['094'] = "Infusioner";
$locale['095'] = "Paneler för Infusions";
$locale['096'] = "Användare";
$locale['097'] = "Nyhetskategorier";
$locale['098'] = "Nyheter";
$locale['099'] = "Paneladministration";
$locale['100'] = "Fotoalbum";
$locale['101'] = "PHP Info";
$locale['102'] = "Omröstningar";
$locale['103'] = "Klotterplank";
$locale['104'] = "Interna länkar";
$locale['105'] = "Smileys";
$locale['106'] = "Inlämnade bidrag";
$locale['107'] = "Uppgradera";
$locale['108'] = "Användargrupper";
$locale['109'] = "Länkkategorier";
$locale['110'] = "Länkar";
$locale['111'] = "Huvudinställningar";
$locale['112'] = "Inställningar för tid och datum";
$locale['113'] = "Inställningar forum";
$locale['114'] = "Registreringsinställningar";
$locale['115'] = "Inställningar fotogalleri";
$locale['116'] = "Övriga inställningar";
$locale['117'] = "Inställningar privata meddelande";
$locale['118'] = "Användarinformation";
$locale['119'] = "Ranking för forum";
$locale['120'] = "Kategorier för Användarfält";
$locale['121'] = "Nyheter";
$locale['122'] = "Användarhantering";
$locale['123'] = "Nerladdningar";
$locale['124'] = "Antal nyheter per siduppslag";
$locale['125'] = "Säkerhet";
$locale['126'] = "Inställningar för Nyheter";
$locale['127'] = "Inställningar för Nerladdningar";
$locale['128'] = "Återställning av adminlösenord";
$locale['129'] = "Fellogg";
$locale['129a'] = "Användar logg";
// Step 6 - Navigation Links
$locale['130'] = "Startsida";
$locale['131'] = "Artiklar";
$locale['132'] = "Filarkiv";
$locale['133'] = "FAQ";
$locale['134'] = "Forum";
$locale['135'] = "Kontakt";
$locale['136'] = "Nyhetskategorier";
$locale['137'] = "Länkar";
$locale['138'] = "Fotogalleri";
$locale['139'] = "Sök";
$locale['140'] = "Föreslå länk";
$locale['141'] = "Föreslå nyhet";
$locale['142'] = "Föreslå artikel";
$locale['143'] = "Föreslå bild";
$locale['144'] = "Föreslå nedladdning";
// Stage 6 - Panels
$locale['160'] = "Navigation";
$locale['161'] = "Inloggade användare";
$locale['162'] = "Forums ämnen";
$locale['163'] = "Senaste artiklar";
$locale['164'] = "Välkomstmeddelande";
$locale['165'] = "Lista över forums ämnen";
$locale['166'] = "Användarinformation";
$locale['167'] = "Omröstning";
$locale['168'] = "Klotterplank";
// Stage 6 - News Categories
$locale['180'] = "Buggar";
$locale['181'] = "Filarkiv";
$locale['182'] = "Spel";
$locale['183'] = "Grafik";
$locale['184'] = "Hårdvara";
$locale['185'] = "Hemsida";
$locale['186'] = "Medlemmar";
$locale['187'] = "Mods";
$locale['188'] = "Filmer";
$locale['189'] = "Nätverk";
$locale['190'] = "Nyheter";
$locale['191'] = "PHP-Fusion";
$locale['192'] = "Säkerhet";
$locale['193'] = "Mjukvara";
$locale['194'] = "Tema";
$locale['195'] = "Windows";
// Stage 6 - Sample Forum Ranks
$locale['200'] = "Superadministratör";
$locale['201'] = "Administratör";
$locale['202'] = "Moderator";
$locale['203'] = "Nybörjare";
$locale['204'] = "Junior";
$locale['205'] = "Användare";
$locale['206'] = "Frekvent användare";
$locale['207'] = "Erfaren användare";
$locale['208'] = "Fusioneer";
// Stage 6 - Sample Smileys
$locale['210'] = "Smile";
$locale['211'] = "Wink";
$locale['212'] = "Sad";
$locale['213'] = "Frown";
$locale['214'] = "Shock";
$locale['215'] = "Pfft";
$locale['216'] = "Cool";
$locale['217'] = "Grin";
$locale['218'] = "Angry";
// Stage 6 - User Field Categories
$locale['220'] = "Kontakt information";
$locale['221'] = "Övrig information";
$locale['222'] = "Alternativ";
$locale['223'] = "Statistik";
// Welcome message
$locale['230'] = "Välkommen";
// Final message
$locale['240'] = "Installationen är slutförd, PHP-Fusion 7 kan nu användas.<br />
Tryck Slutför för att komma till din PHP-Fusion hemsida.<br />
<strong>Notera:</strong> Efter att du har kommit in på din sida, radera setup.php från din server och CHMOD filen config.php tillbaka till 644 av säkerhetskäl.<br /><br />
Tack för att du provar PHP-fusion 7.";
// Default time settings
// http://php.net/manual/en/function.strftime.php
$locale['shortdate'] = "%d.%m.%y";
$locale['longdate'] = "%B %d %Y %H:%M:%S";
$locale['forumdate'] = "%d-%m-%Y %H:%M";
$locale['newsdate'] = "%B %d %Y";
$locale['subheaderdate'] = "%B %d %Y %H:%M:%S";
?>