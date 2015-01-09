<?php
$locale['email_create_subject'] = "Vartotojas sukurtas puslapyje ";
$locale['email_create_message'] = "Sveiki [USER_NAME],\n
Jūsų vartotojas puslapyje ".fusion_get_settings('sitename')." buvo sukurtas.\n
Norėdami prisijungti naudokite šiuo duomenis:\n
prisijungimo vardas: [USER_NAME]\n
slaptažodis: [PASSWORD]\n\n
Pagarbiai,\n
".fusion_get_settings('siteusername');
$locale['email_activate_subject'] = "Vartotojas aktyvuoas ";
$locale['email_activate_message'] = "Sveiki [USER_NAME],\n
Jūsų vartotojas puslapyje ".fusion_get_settings('sitename')." buvo aktyvuotas.\n
Dabar jūs galite prisijungti naudodami pasirinktą vartotojo vardą ir slaptažodį.\n\n
Pagarbiai,\n
".fusion_get_settings('siteusername');

$locale['email_deactivate_subject'] = "Jūsų vartotojui puslapyje ".fusion_get_settings('sitename')." reikalingas naujas aktyvavimas.";
$locale['email_deactivate_message'] = "Sveiki, [USER_NAME],\n
Jau praėjo ".fusion_get_settings('deactivation_period')." diena(-ų, -os) nuo paskutinio jūsų prisijungimo puslapyje ".fusion_get_settings('sitename').". Jūsų vartotojas buvo pažymėtas kaip neaktyvus, tačiau visa vartotojo informacija išlieka nepaliesta.\n
Norėdami iš naujo aktyvuoti vartotoją paspauskite ant žemiau esančios nuorodos:\n
".fusion_get_settings('siteurl')."reactivate.php?user_id=[USER_ID]&code=[CODE]\n\n
Pagarbiai,\n
".fusion_get_settings('siteusername');

$locale['email_ban_subject'] = "Jūsų vartotojas puslapyje ".fusion_get_settings('sitename')." buvo užblokuotas";
$locale['email_ban_message'] = "Sveiki [USER_NAME],\n
Jūsų vartotojas puslapyje ".fusion_get_settings('sitename')." dėl žemiau nurodytų priežasčių buvo užblokuotas administratoriaus ".$userdata['user_name'].":\n
[REASON].\n
Jeigu norite gauti daugiau informacijos susisiekite su puslapio administratorium adresu ".fusion_get_settings('siteemail').".\n
".fusion_get_settings('siteusername');

$locale['email_secban_subject'] = "Jūsų vartotojas puslapyje ".fusion_get_settings('sitename')." buvo užblokuotas.";
$locale['email_secban_message'] = "Sveiki [USER_NAME],\n
Jūsų vartotojas puslapyje ".fusion_get_settings('sitename')." buvo užblokuotas administratoriaus ".$userdata['user_name'].", nes jūsų vartotojo veiksmai, ar jo buvimas puslapyje traktuojami kaip tiesioginė grėsmė puslapio saugumui.\n
Jeigu norite gauti daugiau informacijos susisiekite su puslapio administratorium adresu ".fusion_get_settings('siteemail').".\n
".fusion_get_settings('siteusername');

$locale['email_suspend_subject'] = "Jūsų vartotojo galiojimas puslapyje ".fusion_get_settings('sitename')." buvo laikinai sustabdytas";
$locale['email_suspend_message'] = "Sveiki [USER_NAME],\n
Jūsų vartotojo galiojimas puslapyje ".fusion_get_settings('sitename')." dėl žemiau nurodytų priežasčių buvo laikinai sustabdytas administratoriaus ".$userdata['user_name']." iki [DATE] (puslapio laiku):\n
[REASON].\n
Jeigu norite gauti daugiau informacijos susisiekite su puslapio administratorium adresu ".fusion_get_settings('siteemail').".\n
".fusion_get_settings('siteusername');
?>