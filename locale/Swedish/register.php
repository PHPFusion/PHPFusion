<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2010 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: locale/Swedish/register.php
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
$locale['400'] =  "Registrera";
$locale['401'] = "Aktivera användarkonto";
// Registration Errors
$locale['402'] = "Du måste välja ett användarnamn, ett lösenord, samt ange en e-post adress";
$locale['403'] = "Ditt användarnamn innehåller otillåtna tecken";
$locale['404'] = "Lösenorden är inte identiska.";
$locale['405'] = "Ogiltigt lösenord, endast alfanumeriska tecken får användas.br />
Lösenordet måste bestå av minst 6 tecken.";
$locale['406'] = "Din epostadress förefaller ej giltlig.";
$locale['407'] = "Tyvärr, användarnamnet ".(isset($_POST['username']) ? $_POST['username'] : "")." är upptaget.";
$locale['408'] = "Tyvärr, e-post adressen ".(isset($_POST['email']) ? $_POST['email'] : "")." används redan.";
$locale['409'] = "En användare med ett inaktivt konto är redan registrerad med denna e-post adress.";
$locale['410'] = "Fel säkerhetskod.";
$locale['411'] = "Din e-post adress eller e-post domän är spärrat.";
// Email Message
$locale['449'] = "Välkommen till ".$settings['sitename'];
$locale['450'] = "Hej ".(isset($_POST['username']) ? $_POST['username'] : "").",\n
Välkommen till ".$settings['sitename'].". Här är dina inloggningsuppgifter:\n
Användarnamn: ".(isset($_POST['username']) ? $_POST['username'] : "")."
Lösenord: ".(isset($_POST['password1']) ? $_POST['password1'] : "")."\n
Var god aktivera ditt konto genom att klicka på följande länk:\n";
// Registration Success/Fail
$locale['451'] =  "Registreringen fullständig";
$locale['452'] = "Du kan logga in nu.";
$locale['453'] = "En administratör kommer att aktivera ditt konto snarast.";
$locale['454'] = "Registreringen är nästan klar, du kommer att få e-post innehållande dina inloggningsdetaljer tillsammans med en verifieringslänk.";
$locale['455'] = "Ditt konto är verifierat.";
$locale['456'] = "Registreringen misslyckades";
$locale['457'] =  "Det gick inte att skicka e-post. Kontakta <a href='mailto:".$settings['siteemail']."'>sidans administratör</a>.";
$locale['458'] = "Registreringen kunde ej genomföras på grund av följande anledning(ar):";
$locale['459'] = "Vänligen försök igen";
// Register Form
$locale['500'] = "Vänligen skriv in din information nedan. ";
$locale['501'] = "En bekräftelse kommer att skickas till din angivna e-post adress. ";
$locale['502'] = "Fält som markerats med <span style='color:#ff0000;'>*</span> måste fyllas i.
Ditt användarnamn och lösenord är känsligt för stora och små bokstäver.";
$locale['503'] = " Du kan lägga till mer information genom att gå till Redigera profil när du är inloggad.";
$locale['504'] = "Säkerhetskod:";
$locale['505'] = "Skriv in säkerhetskod:";
$locale['506'] = "Registrera";
$locale['507'] = "Registreringssystemet är tillfälligt avaktiverat.";
$locale['508'] = "Avtal";
$locale['509'] = "Jag har läst <a href='".BASEDIR."print.php?type=T' target='_blank'>Användarvillkor</a> och jag godkänner dem.";
// Validation Errors
$locale['550'] = "Vänligen skriv in ett användarnamn.";
$locale['551'] = "Vänligen skriv in ett lösenord.";
$locale['552'] = "Vänligen skriv in en giltig e-post adress.";
?>