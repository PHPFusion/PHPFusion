<?php
$locale['email_create_subject'] = "Brugerkonto oprettet ";
$locale['email_create_message'] = "Hej [USER_NAME],\n
Din brugerkonto på ".$settings['sitename']." er blevet oprettet.\n
Du kan nu logge på med følgende oplysninger:\n
Brugernavn: [USER_NAME]\n
Kodeord: [PASSWORD]\n\n
Venlig hilsen\n
".$settings['siteusername'];

$locale['email_activate_subject'] = "Brugerkonto aktiveret ";
$locale['email_activate_message'] = "Hej [USER_NAME],\n
Din konto på ".$settings['sitename']." er blevet aktiveret.\n
Du kan nu logge på med det brugernavn og det kodeord, du har valgt.\n\n
Venlig hilsen\n
".$settings['siteusername'];

$locale['email_deactivate_subject'] = "Behov for genaktivering på ".$settings['sitename'];
$locale['email_deactivate_message'] = "Hej [USER_NAME],\n
Der er nu mere end ".$settings['deactivation_period']." dag(e) siden du sidst var logget ind på ".$settings['sitename'].". Din brugerkonto er noteret som inaktiv, men alle dine kontooplysninger og alt indhold er bevaret.\n
For at genaktivere din brugerkonto skal du ganske enkelt klikke på følgende link:\n
".$settings['siteurl']."reactivate.php?user_id=[USER_ID]&code=[CODE]\n\n
Venlig hilsen\n
".$settings['siteusername'];

$locale['email_ban_subject'] = "Din brugerkonto på ".$settings['sitename']." er blevet udelukket";
$locale['email_ban_message'] = "Hej [USER_NAME],\n
Din brugerkonto på ".$settings['sitename']." er blevet udelukket af ".$userdata['user_name']." af følgende årsag:\n
[REASON].\n
Hvis du ønsker at få mere at vide om udelukkelsen, skal du kontakte sideadministratoren via ".$settings['siteemail'].".\n
".$settings['siteusername'];

$locale['email_secban_subject'] = "Din konto på ".$settings['sitename']." er blevet udelukket";
$locale['email_secban_message'] = "Hej [USER_NAME],\n
Din konto på ".$settings['sitename']." er blevet udelukket af ".$userdata['user_name']." på grund af bestemte aktiviteter der tilskrives dig eller har forbindelse til din konto, og som udgør en sikkerhedsrisiko.\n
Hvis du vil vide mere om denne udelukkelse, så vær så venlig at kontakte sidens administrator på ".$settings['siteemail'].".\n
".$settings['siteusername'];

$locale['email_suspend_subject'] = "Din konto på ".$settings['sitename']." er blevet suspenderet";
$locale['email_suspend_message'] = "Hej [USER_NAME]\n
Din brugerkonto på ".$settings['sitename']." er blevet suspenderet af ".$userdata['user_name']." frem til [DATE] (site time) af følgende årsag:\n
[REASON].\n
Hvis du vil vide mere om denne suspension, så vær så venlig at kontakte os via denne email: ".$settings['siteemail'].".\n
".$settings['siteusername'];
?>