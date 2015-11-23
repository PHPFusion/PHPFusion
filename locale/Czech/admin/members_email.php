<?php
$locale['email_create_subject'] = "Účet byl vytvořen!";
$locale['email_create_message'] = "Zdravím [USER_NAME],\n
Tvůj účet na ".$settings['sitename']." byl vytvořen.\n
Nyní se již můžete přihlásit pomocí následující údajů:\n
Jméno: [USER_NAME]\n
Heslo: [PASSWORD]\n\n
S pozdravem ".$settings['siteusername'];

$locale['email_activate_subject'] = "Účet byl aktivovnán!";
$locale['email_activate_message'] = "Zdravím [USER_NAME],\n
Tvůj účet na ".$settings['sitename']." byl aktivován.\n
Nyní se můžete přihlásit pomocí Vámi zvoleného uživatelského jména a hesla.\n
S pozdravem, ".$settings['siteusername'];

$locale['email_deactivate_subject'] = "Účet byl obnoven na ".$settings['sitename'];
$locale['email_deactivate_message'] = "Zdravím [USER_NAME],\n
To bylo ".$settings['deactivation_period']." den(dní) od vaší poslední návštěvy ".$settings['sitename'].". Vaše účet byl označen jako neaktivní, ale všechny vaše přihlašovací údaje a obsah zůstanou nezměněny.\n
To reactivate your account simply click the following link:\n
".$settings['siteurl']."reactivate.php?user_id=[USER_ID]&code=[CODE]\n\n
S pozdravem, ".$settings['siteusername'];

$locale['email_ban_subject'] = "Tvůj účet na ".$settings['sitename']." byl zabanován!";
$locale['email_ban_message'] = "Zdravím [USER_NAME],\n
Váš účet ".$settings['sitename']." byl zablokován ".$userdata['user_name']." z těchto důvodů\n
[REASON].\n
Pokud chete vědět další informace o vašem účtu kontaktujte mně zde ".$settings['siteemail'].".\n
".$settings['siteusername'];

$locale['email_secban_subject'] = "Tvůj účet na ".$settings['sitename']." byl zabanován!";
$locale['email_secban_message'] = "Zdravím [USER_NAME],\n
Váš účet ".$settings['sitename']." byl zablokován ".$userdata['user_name']." protože některé akce akreditované vám nebo k vašemu účtu byly považovány za bezpečnostní hrozbu pro web.\n
Pokud chtete vědět více informací kontaktujte mně prosím tady: ".$settings['siteemail'].".\n
".$settings['siteusername'];

$locale['email_suspend_subject'] = "Tvů účet na ".$settings['sitename']." byl pozastaven!";
$locale['email_suspend_message'] = "Zdravím [USER_NAME],\n
Tvůj učet na ".$settings['sitename']." byl pozastaven adminem ".$userdata['user_name']." dne [DATE] (čas webu) z následujícího důvodu:\n
[REASON].\n
Více informací o pozastavení vašeho účtu zjistíte na emailu ".$settings['siteemail'].".\n
S pozdravem ".$settings['siteusername'];
?>