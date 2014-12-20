<?php
$locale['email_create_subject'] = "Vartotojas sukurtas puslapyje ";
$locale['email_create_message'] = "Sveiki [USER_NAME],\n
Jūsų vartotojas puslapyje ".$settings['sitename']." buvo sukurtas.\n
Norėdami prisijungti naudokite šiuo duomenis:\n
prisijungimo vardas: [USER_NAME]\n
slaptažodis: [PASSWORD]\n\n
Pagarbiai,\n
".$settings['siteusername'];
$locale['email_activate_subject'] = "Vartotojas aktyvuoas ";
$locale['email_activate_message'] = "Sveiki [USER_NAME],\n
Jūsų vartotojas puslapyje ".$settings['sitename']." buvo aktyvuotas.\n
Dabar jūs galite prisijungti naudodami pasirinktą vartotojo vardą ir slaptažodį.\n\n
Pagarbiai,\n
".$settings['siteusername'];

$locale['email_deactivate_subject'] = "Jūsų vartotojui puslapyje ".$settings['sitename']." reikalingas naujas aktyvavimas.";
$locale['email_deactivate_message'] = "Sveiki, [USER_NAME],\n
Jau praėjo ".$settings['deactivation_period']." diena(-ų, -os) nuo paskutinio jūsų prisijungimo puslapyje ".$settings['sitename'].". Jūsų vartotojas buvo pažymėtas kaip neaktyvus, tačiau visa vartotojo informacija išlieka nepaliesta.\n
Norėdami iš naujo aktyvuoti vartotoją paspauskite ant žemiau esančios nuorodos:\n
".$settings['siteurl']."reactivate.php?user_id=[USER_ID]&code=[CODE]\n\n
Pagarbiai,\n
".$settings['siteusername'];

$locale['email_ban_subject'] = "Jūsų vartotojas puslapyje ".$settings['sitename']." buvo užblokuotas";
$locale['email_ban_message'] = "Sveiki [USER_NAME],\n
Jūsų vartotojas puslapyje ".$settings['sitename']." dėl žemiau nurodytų priežasčių buvo užblokuotas administratoriaus ".$userdata['user_name'].":\n
[REASON].\n
Jeigu norite gauti daugiau informacijos susisiekite su puslapio administratorium adresu ".$settings['siteemail'].".\n
".$settings['siteusername'];

$locale['email_secban_subject'] = "Jūsų vartotojas puslapyje ".$settings['sitename']." buvo užblokuotas.";
$locale['email_secban_message'] = "Sveiki [USER_NAME],\n
Jūsų vartotojas puslapyje ".$settings['sitename']." buvo užblokuotas administratoriaus ".$userdata['user_name'].", nes jūsų vartotojo veiksmai, ar jo buvimas puslapyje traktuojami kaip tiesioginė grėsmė puslapio saugumui.\n
Jeigu norite gauti daugiau informacijos susisiekite su puslapio administratorium adresu ".$settings['siteemail'].".\n
".$settings['siteusername'];

$locale['email_suspend_subject'] = "Jūsų vartotojo galiojimas puslapyje ".$settings['sitename']." buvo laikinai sustabdytas";
$locale['email_suspend_message'] = "Sveiki [USER_NAME],\n
Jūsų vartotojo galiojimas puslapyje ".$settings['sitename']." dėl žemiau nurodytų priežasčių buvo laikinai sustabdytas administratoriaus ".$userdata['user_name']." iki [DATE] (puslapio laiku):\n
[REASON].\n
Jeigu norite gauti daugiau informacijos susisiekite su puslapio administratorium adresu ".$settings['siteemail'].".\n
".$settings['siteusername'];
?>