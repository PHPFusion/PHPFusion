<?php
// Error messages
$locale['500'] = "An error occurred";
$locale['501'] = "The re-activation link you clicked is no longer valid.<br /><br />
Contact the site's administrator at <a href='mailto:".fusion_get_settings('siteemail')."'>".fusion_get_settings('siteemail')."</a> if you want to request a manual re-activation.";
$locale['502'] = "The re-activation link you clicked is invalid!<br /><br />
Contact the site's administrator at <a href='mailto:".fusion_get_settings('siteemail')."'>".fusion_get_settings('siteemail')."</a> if you want to request a manual re-activation.";
$locale['503'] = "The re-activation link you followed could not re-activate your account.<br />
Perhaps your account has already been re-activated and in that case you should be able to <a href='".fusion_get_settings('siteurl')."login.php'>log in here</a>.<br /><br />
If you cannot log in now, please contact the site's administrator at <a href='mailto:".fusion_get_settings('siteemail')."'>".fusion_get_settings('siteemail')."</a> if you want to request a manual re-activation.";
// Send confirmation mail
$locale['504'] = "Account re-activated at ".fusion_get_settings('sitename');
$locale['505'] = "Hello [USER_NAME],\n
Your account at ".fusion_get_settings('sitename')." has been re-activated. We hope to see you more often at the site.\n\n
Regards,\n\n
".fusion_get_settings('siteusername');
$locale['506'] = "Reactivated by user.";

