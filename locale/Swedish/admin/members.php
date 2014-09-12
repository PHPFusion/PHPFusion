<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2010 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: locale/Swedish/admin/members.php
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
// Member Management Options
$locale['400'] = "Användare";
$locale['401'] = "Användare";
$locale['402'] = "Lägg till ny användare";
$locale['403'] = "Användarstatus";
$locale['404'] = "Inställningar";
$locale['405'] = "Granska";
$locale['406'] = "Redigera";
$locale['407'] = "Aktivera";
$locale['408'] = "Radera spärr";
$locale['409'] = "Spärra";
$locale['410'] = "Radera";
$locale['411'] = "Det finns inga %s användare";
$locale['412'] = " som börjar på ";
$locale['413'] = " matchar ";
$locale['414'] = "Visa alla";
$locale['415'] = "Sök användare:";
$locale['416'] = "Sök";
$locale['417'] = "Välj åtgärd";
$locale['418'] = "Ångra";
$locale['419'] = "Reaktivera";
// Ban/Unban/Delete Member
$locale['420'] = "Uteslut användare";
$locale['421'] = "Upphäv uteslutning";
$locale['422'] = "Användare raderad";
$locale['423'] = "Är du säker på att du vill radera den här användaren?";
$locale['424'] = "Användare aktiverad";
// Edit Member Details
$locale['430'] = "Redigera användare";
$locale['431'] = "Användarinformation uppdaterad";
$locale['432'] = "Återgå till att administrera användare";
$locale['433'] = "Återgå till administrationspanel";
$locale['434'] = "Det gick inte att uppdatera användaren:";
// Extra Edit Member Details form options
$locale['440'] = "Spara ändringar";
// Update Profile Errors
$locale['450'] = "Du kan inte redigera superadministratörer.";
$locale['451'] = "Du måste ange användarnamn och e-post adress.";
$locale['452'] = "Användarnamnet innehåller otillåtna tecken.";
$locale['453'] = "Användarnamnet ".(isset($_POST['user_name']) ? $_POST['user_name'] : "")." används redan.";
$locale['454'] = "Felaktig e-post adress.";
$locale['455'] = "E-post adressen ".(isset($_POST['user_email']) ? $_POST['user_email'] : "")." används redan.";
$locale['456'] = "Lösenorden är inte identiska.";
$locale['457'] = "Ogiltigt lösenord, endast alfanumeriska tecken får användas.<br />
Lösenordet måste bestå av minst 6 tecken.";
$locale['458'] = "<strong>Varning:</strong> oväntad scripthändelse.";
// View Member Profile
$locale['470'] = "Användarprofil";
$locale['472'] = "Statistik";
$locale['473'] = "Användargrupper";
// Add Member Errors
$locale['480'] = "Lägg till användare";
$locale['481'] = "Användarkontot har skapats.";
$locale['482'] = "Användarkontot kan inte skapas.";
// Suspension Log
$locale['510s'] = "Indragningslogg för ";
$locale['511s'] = "Där finns inga registrerade indragningar för denna användare.";
$locale['512s'] = "Föregående indragningar ";
$locale['513'] = "Nej."; // as in number
$locale['514'] = "Datum";
$locale['515'] = "Orsak";
$locale['516'] = "Utfärdande Administratör";
$locale['517'] = "Systemåtgärd";
$locale['518'] = "Tillbaka till användarprofil";
$locale['519'] = "Indragningslogg för denna användare: ";
$locale['520'] = "Upphävd: ";
$locale['521'] = "IP: ";
$locale['522'] = "Ej ännu återupprättad";
$locale['540'] = "Fel";
$locale['541'] = "Fel: Du måste ange skäl för Indragning!";
$locale['542'] = "Fel: Du måste ange skäl för säkerhetsavstängning!";
// User Management Admin
$locale['550'] = "Dra in användarrättigheter: ";
$locale['551'] = "Antal dagar:";
$locale['552'] = "Orsak:";
$locale['553'] = "Dra in användarrättigheter.";
$locale['554'] = "Där finns inga registrerade indragningar för denna användare.";
$locale['555'] = "Skall denna användare stängas av?";
$locale['556'] = "Återge användarrättigheter till användare: ";
$locale['557'] = "Återge användarrättigheter";
$locale['558'] = "Reaktivera användare: ";
$locale['559'] = "Reaktivera ";
$locale['560'] = "Återta Säkerhetsavstängning av användare: ";
$locale['561'] = "Återta Säkerhetsavstängning";
$locale['562'] = "Avaktivera användare: ";
$locale['563'] = "Säkerhetsavstäng användare: ";
$locale['566'] = "Avstängning upphävd";
$locale['568'] = "Avstängd av säkerhetsskäl";
$locale['569'] = "Säkerhetsavstängning upphävd";
$locale['572'] = "Användares åtkomsträttigheter indragna.";
$locale['573'] = "Åtkomsträttigheter återupprättade";
$locale['574'] = "Användare avaktiverad";
$locale['575'] = "Användare återaktiverad";
$locale['576'] = "Konto annullerades";
$locale['577'] = "Återtagning av konto ej genomförd";
$locale['578'] = "Kontot annullerades och anonymiserades";
$locale['579'] = "Anonymisering av konto ej genomfört";
$locale['580'] = "Avaktivera inaktiva användare";
$locale['581'] = "Det finns fler än 50 inaktiva användare och måste därmed köra avaktiveringsprocessen<strong>%d gånger</strong>.";
$locale['582'] = "Återaktivera";
$locale['583'] = "Återupprätta";
$locale['584'] = "Välj ny status";
$locale['585'] = "Denna användare blev avaktiverad av säkerhetsskäl! Är du säker på att vilja återupprätta användaren?";
$locale['585a'] = "Specificera skäl till varför du stänger av eller häver avstängning ";
$locale['590'] = "Uteslut";
$locale['591'] = "Återta supendering";
$locale['592'] = "utesluter";
$locale['593'] = "återtar suspendering";
$locale['594'] = "Ange ett skäl till att du ";
$locale['595'] = " användaren ";
$locale['596'] = "Tid:";
$locale['600'] = "Säkerhetsavstängning";
$locale['601'] = "inaktiverar av säkerhetsskäl";
$locale['602'] = "Avstängning";
$locale['603'] = "återtar avstängning";
$locale['604'] = "Orsak:";
// Deactivation System
$locale['610'] = "<strong>%d användaren(-na)</strong> har inte loggat in på <strong>%d dag(-ar)</strong> och har inaktiverats. 
Genom avaktivering av användare har de<strong>%d dag(-ar)</strong> innan de är %s.";
$locale['611'] = "Observera att vissa användare kan ha medverkat till att ha skapat innehåll på din site, såsom foruminlägg, kommentarer, bilder, etc.
Dessa blir också raderade då icke aktiva användare raderas.";
$locale['612'] = "användare";
$locale['613'] = "användare";
$locale['614'] = "Deaktivera";
$locale['615'] = "permanent raderad";
$locale['616'] = "anonym";
$locale['617'] = "Varning:";
$locale['618'] = "det rekommenderas att ändra avaktiveringsmetod från radering till anonymisera för att förhindra dataförlust!";
$locale['619'] = "Det kan göras <a href='".ADMIN."settings_users.php".$aidlink."'>här</a>.";
$locale['620'] = "anonym";
$locale['621'] = "Automatisk deaktivering av inaktiva användare.";
?>