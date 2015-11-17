<?php
// Error messages
$locale['500'] = "Se ha producido un error";
$locale['501'] = "El enlace de reactivación que has usado ya no es válido.<br /><br />
Contacta con el administrador del sitio en <a href='mailto:".fusion_get_settings('siteemail')."'>".fusion_get_settings('siteemail')."</a> si deseas solicitar la reactivación manual.";
$locale['502'] = "El enlace de reactivación que has usado no es válido.<br /><br />
Contacta con el administrador del sitio en <a href='mailto:".fusion_get_settings('siteemail')."'>".fusion_get_settings('siteemail')."</a> si deseas solicitar la reactivación manual.";
$locale['503'] = "El enlace de reactivación que has usado no ha podido reactivar tu cuenta.<br />
Quizá tu cuenta ya haya sido reactivada y, en ese caso, deberías poder <a href='".fusion_get_settings('siteurl')."login.php'>iniciar sesión aquí</a>.<br /><br />
Si ahora no puedes iniciar sesión, contacta con el administrador del sitio en <a href='mailto:".fusion_get_settings('siteemail')."'>".fusion_get_settings('siteemail')."</a> si deseas solicitar la reactivación manual.";
// Send confirmation mail
$locale['504'] = "Cuenta Reactivada en ".fusion_get_settings('sitename');
$locale['505'] = "Hola [USER_NAME],\n
Tu cuenta en ".fusion_get_settings('sitename')." ha sido reactivada. Esperamos poder verte más a menudo en el sitio.\n\n
Saludos,\n\n
".fusion_get_settings('siteusername');
$locale['506'] = "Reactivada por el usuario.";
