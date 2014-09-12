<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2010 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: locale/Swedish/forum/post.php
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
// Post Titles
$locale['400'] = "Förhandsgranska ämne";
$locale['401'] = "Starta nytt ämne";
$locale['402'] = "Förhandsgranska svar";
$locale['403'] = "Svara på inlägg";
$locale['404'] = "Svara";
$locale['405'] = "Förhandsgranska ändringar";
$locale['407'] = "Radera inlägg";
$locale['408'] = "Redigera inlägg";
$locale['409'] = "Spara ändringar";
// Post Preview
$locale['420'] = "Inget ämne";
$locale['421'] = "Inlägget innehåller ingen text, inlägget kommer att avvisas om du inte skriver en text";
$locale['422'] = "Författare:";
$locale['423'] = "Inlägg:";
$locale['424'] = "Hemort:";
$locale['425'] = "Registrerad:";
$locale['426'] = "Publicerat: ";
$locale['427'] = "Redigerat av ";
$locale['428'] = " datum ";
$locale['429'] = " skrev:";
$locale['430'] = "Användar avatar";
$locale['431'] = "Senaste inlägg";
$locale['432'] = "Senaste %s inlägg";
// Post Error/Success
$locale['440a'] = "Otillåtet filformat på bilaga.";
$locale['440b'] = "Otillåtet filnamn eller filstorlek på bilagan.";
$locale['441'] = "Fel: Du har inte angivit en ämnesrubrik eller skrivit en text";
$locale['442'] = "Ditt inlägg är publicerat";
$locale['443'] = "Ditt svar är publicerat";
$locale['444'] = "tråd är raderad";
$locale['445'] = "Inlägget är raderat";
$locale['446'] = "Ditt inlägg är uppdaterat";
$locale['447'] = "Återgå till ämne";
$locale['448'] = "Återgå till ämne";
$locale['449'] = "Återgå till forumindex";
$locale['450'] = "Fel: Du har varit inaktiv för länge, var vänlig logga in igen";
$locale['451'] = "Underrätta mig vid svar i det här ämnet.";
$locale['452'] = "Du kommer att bli underrättad vid svar i det här ämnet.";
$locale['453'] = "Du blir inte längre underrättad vid svar i det här ämnet.";
$locale['454'] = "Detta inlägg är låst. Kontakta moderator för mer information.";
$locale['455'] = "Du kan bara redigera ett meddelande för %d minut(er) efter initial inlämning.";
// Post Form
$locale['460'] = "Ämne";
$locale['461'] = "Meddelande";
$locale['462'] = "Typsnittsfärg: ";
$locale['463'] = "Inställningar";
$locale['464'] = "Bifoga";
$locale['465'] = " (Ej obligatorisk)";
$locale['466'] = "Max. filstorlek: %s / Tillåtna filtyper: %s";
$locale['467'] = "Lägg till omröstning (Valfri)";
$locale['468'] = "Redigera omröstning";
$locale['469'] = "Titel på omröstning";
$locale['470'] = "Inställningar för omröstning";
$locale['471'] = "Lägg till valmöjlighet";
$locale['472'] = "Uppdatera";
$locale['473'] = "Radera";
$locale['474'] = "Redigera anledning";
// Post Form Options
$locale['480'] = "Prioritera den här ämnet?";
$locale['481'] = "Lås det här ämnet";
$locale['482'] = "Inaktivera smileys i det här inlägget";
$locale['483'] = "Visa min signatur i det här inlägget";
$locale['484'] = "Radera det här inlägget";
$locale['485'] = "Radera bilaga -";
$locale['486'] = "Underrätta mig när svar skrivs";
$locale['487'] = "Dölj redigering";
$locale['488'] = "Lås inlägg";
// Post Access Violation
$locale['500'] = "Du kan inte redigera det här inlägget.";
// Search Forum Form
$locale['530'] = "Sök i forum";
$locale['531'] = "Sök nyckelord";
$locale['532'] = "Sök";
// Forum Notification Email
$locale['550'] = "Underrättelse om svar i tråden - {THREAD_SUBJECT}";
$locale['551'] = "Hej {USERNAME},

Ett svar har skrivits i följande tråd: '{THREAD_SUBJECT}' vilken du följer på ".$settings['sitename'].". Du kan använda följande länk för att se svaret:

{THREAD_URL}

Om du inte längre önskar följa den här tråden kan du klicka 'Radera underrättelse vid svar i det här ämnet' som finns högst upp i ämnet.

Med vänlig hälsning,
".$settings['siteusername'].".";
?>