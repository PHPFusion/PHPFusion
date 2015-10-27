<?php
$locale['email_create_subject'] = "Cuenta Creada en ";
$locale['email_create_message'] = "Hola [USER_NAME],\n
Tu cuenta en ".fusion_get_settings('sitename')." ha sido creada.\n
Ahora ya puedes iniciar sesiÃ³n con los siguientes datos:\n
Nombre de Usuario: [USER_NAME]\n
ContraseÃ±a: [PASSWORD]\n\n
Saludos,\n
".fusion_get_settings('siteusername');
$locale['email_activate_subject'] = "Cuenta Activada en ";
$locale['email_activate_message'] = "Hola [USER_NAME],\n
Tu cuenta en ".fusion_get_settings('sitename')." ha sido activada.\n
Ahora ya puedes iniciar sesiÃ³n usando el nombre de usuario y la contraseÃ±a que elegiste.\n\n
Saludos,\n
".fusion_get_settings('siteusername');
$locale['email_deactivate_subject'] = "ReactivaciÃ³n de Cuenta en ".fusion_get_settings('sitename');
$locale['email_deactivate_message'] = "Hola [USER_NAME],\n
Han pasado ".fusion_get_settings('deactivation_period')." dÃ­as desde la Ãºltima vez que accediste a ".fusion_get_settings('sitename').". Tu usuario ha sido marcado como inactivo, pero todos los datos de tu cuenta y tus contenidos permanecen intactos.\n
Si deseas reactivar tu cuenta, pulsa el siguiente enlace:\n
".fusion_get_settings('siteurl')."reactivate.php?user_id=[USER_ID]&code=[CODE]\n\n
Saludos,\n
".fusion_get_settings('siteusername');
$locale['email_ban_subject'] = "ExpulsiÃ³n de Cuenta en ".fusion_get_settings('sitename');
$locale['email_ban_message'] = "Hola [USER_NAME],\n
Tu cuenta en ".fusion_get_settings('sitename')." ha sido expulsada por ".$userdata['user_name'].". El motivo es el siguiente:\n
[REASON].\n
Si deseas mÃ¡s informaciÃ³n sobre esta expulsiÃ³n, contacta con el administrador del sitio en ".fusion_get_settings('siteemail').".\n Saludos,\n
".fusion_get_settings('siteusername');
$locale['email_secban_subject'] = "ExpulsiÃ³n de Cuenta en ".fusion_get_settings('sitename');
$locale['email_secban_message'] = "Hola [USER_NAME],\n
Tu cuenta en ".fusion_get_settings('sitename')." ha sido expulsada por ".$userdata['user_name']." debido a que algunas acciones atribuidas a tÃ­ o relacionadas con tu cuenta han sido consideradas como una amenaza de seguridad para el sitio.\n
Si deseas mÃ¡s informaciÃ³n sobre esta expulsiÃ³n de seguridad, contacta con el administrador del sitio en ".fusion_get_settings('siteemail').".\n Saludos,\n
".fusion_get_settings('siteusername');
$locale['email_suspend_subject'] = "SuspensiÃ³n de Cuenta en ".fusion_get_settings('sitename');
$locale['email_suspend_message'] = "Hola [USER_NAME],\n
Tu cuenta en ".fusion_get_settings('sitename')." ha sido suspendida por ".$userdata['user_name']." hasta [DATE] (fecha/hora del sitio) por lo siguiente:\n
[REASON].\n
Si deseas mÃ¡s informaciÃ³n sobre esta suspensiÃ³n, contacta con el administrador del sitio en ".fusion_get_settings('siteemail').".\n Saludos,\n
".fusion_get_settings('siteusername');
