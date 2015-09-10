<?php
// Error messages
$locale['500'] = "Une erreur est survenue";
$locale['501'] = "Le lien de ré-activation sur lequel vous avez cliqué n'est plus valide!<br /><br />
Contactez un administrateur à <a href='mailto:".fusion_get_settings('siteemail')."'>".fusion_get_settings('siteemail')."</a> si vous voulez que cette opération soit effectuée manuellement.";
$locale['502'] = "Le lien de ré-activation sur lequel vous avez cliqué n'est pas valide!<br /><br />
Contactez un administrateur à <a href='mailto:".fusion_get_settings('siteemail')."'>".fusion_get_settings('siteemail')."</a> si vous voulez que cette opération soit effectuée manuellement.";
$locale['503'] = "Le lien de ré-activation que vous avez suivi n'a pas pu réactiver votre compte.<br />
Peut-être que votre compte a déjà été réactivé et dans ce cas, vous devriez être en mesure de vous <a href='".fusion_get_settings('siteurl')."login.php'>connecter</a>.<br /><br />
Si vous ne pouvez pas vous connecter maintenant, veuillez contacter l'administrateur du site à <a href='mailto:".fusion_get_settings('siteemail')."'>".fusion_get_settings('siteemail')."</a> si vous voulez que cette opération soit effectuée manuellement.";
// Send confirmation mail
$locale['504'] = "Compte ré-activé sur ".fusion_get_settings('sitename');
$locale['505'] = "Bonjour [USER_NAME],\n
Votre compte sur ".fusion_get_settings('sitename')." a été ré-activé. Nous espérons vous voir plus souvent sur ​​le site.\n\n
Cordialement,\n\n
".fusion_get_settings('siteusername');
$locale['506'] = "Ré-activé par l'utilisateur.";
?>
