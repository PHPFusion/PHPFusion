<?php
$locale['email_create_subject'] = "Account aangemaakt op ";
$locale['email_create_message'] = "Beste [USER_NAME],\n
Uw account op ".$settings['sitename']." is succesvol aangemaakt.\n
U kunt nu inloggen met de volgende details:\n
gebruikersnaam: [USER_NAME]\n
wachtwoord: [PASSWORD]\n\n
Met vriendelijke groet,\n
".$settings['siteusername'];

$locale['email_activate_subject'] = "Account geactiveerd op ";
$locale['email_activate_message'] = "Beste [USER_NAME],\n
Uw account op ".$settings['sitename']." is geactiveerd.\n
U kunt nu inloggen met uw gebruikersnaam en wachtwoord.\n\n
Met vriendelijke groet,\n
".$settings['siteusername'];

$locale['email_deactivate_subject'] = "Account reactivicatie nodig op ".$settings['sitename'];
$locale['email_deactivate_message'] = "Beste [USER_NAME],\n
Het zijn ".$settings['deactivation_period']." dagen geleden dat je voor het laatst bent ingelogd op ".$settings['sitename'].". Uw account is als inactief gemarkeerd, maar alles van uw account is nog steeds intact gebleven.\n
Om uw account te reactiveren dient u op deze link te klikken:\n
".$settings['siteurl']."reactivate.php?user_id=[USER_ID]&code=[CODE]\n\n
Met vriendelijke groet,\n
".$settings['siteusername'];

$locale['email_ban_subject'] = "Uw account op ".$settings['sitename']." is verbannen";
$locale['email_ban_message'] = "Beste [USER_NAME],\n
Uw account op ".$settings['sitename']." is verbannen door ".$userdata['user_name']." om de volgende reden:\n
[REASON].\n
Als u meer informatie wilt hebben over de verbanning, neem contact op met de site beheerder op ".$settings['siteemail'].".\n
".$settings['siteusername'];

$locale['email_secban_subject'] = "Uw account op ".$settings['sitename']." is verbannen";
$locale['email_secban_message'] = "Beste [USER_NAME],\n
Uw account op ".$settings['sitename']." is verbannen door ".$userdata['user_name']." vanwege activiteiten door u of verband houdende met u en die een risico vormen voor de site.\n
Als u meer informatie wilt hebben over deze veiligheidsverbanning, neem contact op met de site beheerder op ".$settings['siteemail'].".\n
".$settings['siteusername'];

$locale['email_suspend_subject'] = "Uw account op ".$settings['sitename']." is geschorst";
$locale['email_suspend_message'] = "Beste [USER_NAME],\n
Uw account op ".$settings['sitename']." is geschorst door ".$userdata['user_name']." tot en met [DATE] (tijd van site) om de volgende reden:\n
[REASON].\n
Als u meer informatie wilt hebben over deze schorsing, neem contact op met de site beheerder op ".$settings['siteemail'].".\n
".$settings['siteusername'];
?>