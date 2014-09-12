<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2010 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: locale/Swedish/admin/members_email.php
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
$locale['email_create_subject'] = "Konto skapat ";
$locale['email_create_message'] = "Hej [USER_NAME],\n
ditt konto på ".$settings['sitename']." har skapats.\n
Du kan nu logga in med följande information:\n
användarnamn: [USER_NAME]\n
lösenord: [PASSWORD]\n\n
Observera att ditt lösenord lagras krypterat och kan inte återställas av oss.
Om det skulle behövas, använd funktionen för Återställning av lösenord.
Hälsningar,\n
".$settings['siteusername'];

$locale['email_activate_subject'] = "Konto aktiverat ";
$locale['email_activate_message'] = "Hej [USER_NAME],\n
Ditt konto på ".$settings['sitename']." har blivit aktiverat.\n
Du kan nu logga in med ditt valda användarnamn och lösenord.\n\n
Hälsningar,\n
".$settings['siteusername'];

$locale['email_deactivate_subject'] = "Återaktivering av konto erfordras på ".$settings['sitename'];
$locale['email_deactivate_message'] = "Hej [USER_NAME],\n
Det har gått ".$settings['deactivation_period']." dag(-ar) sedan du sist loggade in på ".$settings['sitename'].". Ditt konto har markerats som inaktivt, men all din information finns kvar.\n
För att återaktivera ditt konto behöver du bara klicka på följande länk:\n
".$settings['siteurl']."reactivate.php?user_id=[USER_ID]&code=[CODE]\n\n
Hälsningar,\n
".$settings['siteusername'];

$locale['email_ban_subject'] = "Ditt konto på ".$settings['sitename']." har blivit avstängt.";
$locale['email_ban_message'] = "Hej [USER_NAME],\n
Ditt konto på ".$settings['sitename']." har blivit avstängt av ".$userdata['user_name']." på grund av:\n
[REASON].\n
Om du önskar mer information, vänligen kontakta administrator på ".$settings['sitename']." genom ".$settings['siteemail'].".\n
".$settings['siteusername'];

$locale['email_secban_subject'] = "Ditt konto på ".$settings['sitename']." har blivit avstängt.";
$locale['email_secban_message'] = "Hej [USER_NAME],\n
Ditt konto på ".$settings['sitename']." har blivit avstängt av ".$userdata['user_name']." eftersom vissa händelser på siten relaterade till ditt användarnamn eller konto bedömdes vara en säkerhetsrisk.\n
Om du önskar mer information, vänligen kontakta en administratör på ".$settings['sitename']." genom ".$settings['siteemail'].".\n
".$settings['siteusername'];

$locale['email_suspend_subject'] = "Ditt konto på ".$settings['sitename']." har blivit avaktiverat";
$locale['email_suspend_message'] = "Hej [USER_NAME],\n
Ditt konto på ".$settings['sitename']." har blivit avstängt av ".$userdata['user_name']." tills [DATE] (sidans tid) på grund av:\n
[REASON].\n
Om du önskar mer information, vänligen kontakta en administratör på ".$settings['sitename']." genom ".$settings['siteemail'].".\n
".$settings['siteusername'];
?>