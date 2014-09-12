<?php
$locale['email_create_subject'] = "Konto opprettet på ";
$locale['email_create_message'] = "Hei [USER_NAME],\n
Din konto på ".$settings['sitename']." har blitt opprettet.\n
Du kan nå logge inn med følgende detaljer:\n
brukernavn: [USER_NAME]\n
passord: [PASSWORD]\n\n
Hilsen,\n
".$settings['siteusername'];

$locale['email_activate_subject'] = "Konto aktivert på ";
$locale['email_activate_message'] = "Hei [USER_NAME],\n
Din konto på ".$settings['sitename']." har blitt aktivert.\n
Du kan nå logge inn med ditt valgte brukernavn og passord.\n\n
Hilsen,\n
".$settings['siteusername'];

$locale['email_deactivate_subject'] = "Konto reaktivering kreves på ".$settings['sitename'];
$locale['email_deactivate_message'] = "Hei [USER_NAME],\n
Det har gått ".$settings['deactivation_period']." dag(er) siden du sist logget inn på ".$settings['sitename'].". Din bruker har blitt markert som inaktiv, men alle kontodetaljene og innholdet er intakt.\n
For å reaktivere kontoen din klikk denne lenken:\n
".$settings['siteurl']."reactivate.php?user_id=[USER_ID]&code=[CODE]\n\n
Hilsen,\n
".$settings['siteusername'];

$locale['email_ban_subject'] = "Din konto på ".$settings['sitename']." har blitt utestengt";
$locale['email_ban_message'] = "Hei [USER_NAME],\n
Kontoen din på ".$settings['sitename']." har blitt utestengt av ".$userdata['user_name']." av følgende grunn:\n
[REASON].\n
Hvis du vil ha mer informasjon rundt utestengelsen, vennligst kontakt sidens administrator på ".$settings['siteemail'].".\n
".$settings['siteusername'];

$locale['email_secban_subject'] = "Din konto på ".$settings['sitename']." har blitt utestengt";
$locale['email_secban_message'] = "Hei [USER_NAME],\n
Din konto på ".$settings['sitename']." har blitt utestengt av ".$userdata['user_name']." på grunn av at handlinger akkreditert deg eller koblet til din konto var å regne som en sikkerhetstrussel mot siden.\n
Hvis du vil ha mer informasjon rundt sikkerhetsutestengelsen, vennligst kontakt sidens administrator på ".$settings['siteemail'].".\n
".$settings['siteusername'];

$locale['email_suspend_subject'] = "Din konto på ".$settings['sitename']." har blitt suspendert";
$locale['email_suspend_message'] = "Hei [USER_NAME],\n
Din konto på ".$settings['sitename']." har blitt suspendert av ".$userdata['user_name']." inntil [DATE] (site time) av følgende gunn:\n
[REASON].\n
Hvis du ønsker mer informasjon omkring suspenderingen, vennligst kontakt sidens administrator på ".$settings['siteemail'].".\n
".$settings['siteusername'];
?>
