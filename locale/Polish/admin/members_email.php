<?php
$locale['email_create_subject'] = "Account created at ";
$locale['email_create_message'] = "Hello [USER_NAME],\n
Your account at ".fusion_get_settings('sitename')." has been created.\n
You can now login using the following details:\n
username: [USER_NAME]\n
password: [PASSWORD]\n\n
Regards,\n
".fusion_get_settings('siteusername');
$locale['email_activate_subject'] = "Account activated at ";
$locale['email_activate_message'] = "Hello [USER_NAME],\n
Your account at ".fusion_get_settings('sitename')." has been activated.\n
You can now login using your chosen username and password.\n\n
Regards,\n
".fusion_get_settings('siteusername');
$locale['email_deactivate_subject'] = "Account reactivation required at ".fusion_get_settings('sitename');
$locale['email_deactivate_message'] = "Hello [USER_NAME],\n
It has been ".fusion_get_settings('deactivation_period')." day(s) since you last logged in at ".fusion_get_settings('sitename').". Your user has been marked as inactive but all your account details and content remains intact.\n
To reactivate your account simply click the following link:\n
".fusion_get_settings('siteurl')."reactivate.php?user_id=[USER_ID]&code=[CODE]\n\n
Regards,\n
".fusion_get_settings('siteusername');
$locale['email_ban_subject'] = "Your account on ".fusion_get_settings('sitename')." has been banned";
$locale['email_ban_message'] = "Hello [USER_NAME],\n
Your account on ".fusion_get_settings('sitename')." has been banned by ".$userdata['user_name']." because of the following reason:\n
[REASON].\n
If you want more information about this ban, please, contact the site administrator at ".fusion_get_settings('siteemail').".\n
".fusion_get_settings('siteusername');
$locale['email_secban_subject'] = "Your account on ".fusion_get_settings('sitename')." has been banned";
$locale['email_secban_message'] = "Hello [USER_NAME],\n
Your account on ".fusion_get_settings('sitename')." has been banned by ".$userdata['user_name']." because of some actions accredited to you or linked to your account were considered a security threat to the site.\n
If you want more information about this security ban, please, contact the site administrator at ".fusion_get_settings('siteemail').".\n
".fusion_get_settings('siteusername');
$locale['email_suspend_subject'] = "Your account on ".fusion_get_settings('sitename')." has been suspended";
$locale['email_suspend_message'] = "Hello [USER_NAME],\n
Your account on ".fusion_get_settings('sitename')." has been suspended by ".$userdata['user_name']." until [DATE] (site time) because of the following reason:\n
[REASON].\n
If you want more information about this suspension, please, contact the site administrator at ".fusion_get_settings('siteemail').".\n
".fusion_get_settings('siteusername');
