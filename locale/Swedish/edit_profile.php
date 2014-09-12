<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2010 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: locale/Swedish/edit_profile.php
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
$locale['400'] = "Redigera profil";
// Edit Profile Messages
$locale['410'] = "För att ändra lösenord eller e-postadress<br />måste du ange ditt nuvarande lösenord.";
$locale['411'] = "Profil uppdaterad";
$locale['412'] = "Går inte att uppdatera profil:";
// Edit Profile Form
$locale['420'] = "Nuvarande lösenord";
$locale['421'] = "Nuvarande adminlösenord";
$locale['422'] = "Nytt adminlösenord";
$locale['423'] = "Bekräfta nytt adminlösenord";
$locale['424'] = "Uppdatera profil";
// Update Profile Errors
$locale['430'] = "Du måste ange ett användarnamn och e-postadress.";
$locale['431'] = "Användarnamn innehåller ogiltiga tecken.";
$locale['432'] = "Användarnamnet ".(isset($_POST['user_name']) ? $_POST['user_name'] : "")." finns redan.";
$locale['433'] = "Felaktig e-postadress.";
$locale['434'] = "E-postadressen ".(isset($_POST['user_email']) ? $_POST['user_email'] : "")." finns redan.";
$locale['435'] = "Nytt lösenord stämmer inte.";
$locale['436'] = "Felaktigt lösenord, använd endast alfa numeriska tecken.<br />Lösenord måste vara minst 6 tecken långt.";
$locale['437'] = "Du måste ange ditt nuvarande lösenord för att ändra lösenord eller e-postadress.";
$locale['438'] = "Nytt adminlösenord stämmer inte.";
$locale['439'] = "Ditt lösenord och adminlösenordet måste vara olika.";
$locale['440'] = "Felaktigt adminlösenord använd endast alfa numeriska tecken.<br />Adminlösenord måste vara minst 6 tecken långt.";
$locale['441'] = "Du måste ange ditt nuvarande adminlösenord för att ändra ditt adminlösenord.";
$locale['442'] = "Avatar filnamnet är felaktig.";
$locale['443'] = "Avatar filens storlek är för stor.";
$locale['444'] = "Avatar filtypen är felaktig.";
?>