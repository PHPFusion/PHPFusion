<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2010 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: locale/Swedish/contact.php
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
// Contact Form
$locale['400'] = "Kontakt";
$locale['401'] = "Ni kan skicka e-post direkt på ".hide_email($settings['siteemail']).". Om du är registrerad användare med åtkomst till PM tjänsten så kan ni skicka ett 
<a href='messages.php?msg_send=1'>PM</a>. Alternativt kan ni fylla i formuläret på den här sidan så skickas ditt meddelande till oss via e-post.";
$locale['402'] = "Namn:";
$locale['403'] = "E-post adress:";
$locale['404'] = "Ämne:";
$locale['405'] = "Meddelande:";
$locale['406'] = "Skicka meddelande";
$locale['407'] = "Valideringskod:";
$locale['408'] = "Fyll i valideringskod:";
// Contact Errors
$locale['420'] = "Du måste fylla i ett namn";
$locale['421'] = "Du måste fylla i en e-post adress";
$locale['422'] = "Du måste fylla i ett ämne";
$locale['423'] = "Du måste fylla i ett meddelande";
$locale['424'] = "Du måste fylla i rätt valideringskod";
$locale['425'] = "Internt fel: Ditt meddelande kunde inte skickas.";
// Message Sent
$locale['440'] = "Ditt meddelande är skickat";
$locale['441'] = "Tack";
$locale['442'] = "Ditt meddelande kunde inte skickas på grund av följande orsak(-er):";
$locale['443'] = "Vänligen försök igen.";
?>
