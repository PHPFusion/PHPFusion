<?php
$locale['email_create_subject'] = "Account created at ";
$locale['email_create_message'] = "Hello [USER_NAME],\n
Your account at [SITENAME] has been created.\nYou can now login using the following details:\n
username: [USER_NAME]\n
password: [PASSWORD]\n\nRegards,\n[SITENAME]";
$locale['email_activate_subject'] = "Account activated at ";
$locale['email_activate_message'] = "Hello [USER_NAME],\nYour account at [SITENAME] has been activated.\n
You can now login using your chosen username and password.\n\nRegards,\n[SITENAME]";
$locale['email_deactivate_subject'] = "Account reactivation required at [SITENAME]";
$locale['email_deactivate_message'] = "Hello [USER_NAME],\nIt has been [DEACTIVATION_PERIOD] day(s) since you last logged in at [SITENAME] Your user has been marked as inactive but all your account details and content remains intact.\n
To reactivate your account simply click the following link:\n[REACTIVATION_LINK]\n\nRegards,\n[SITENAME]";
$locale['email_ban_subject'] = "Your account on [SITENAME] has been banned";
$locale['email_ban_message'] = "Hello [USER_NAME],\nYour account on [SITENAME] has been banned by [USERNAME] because of the following reason:\n
[REASON]\nIf you want more information about this ban, please, contact the site administrator at [SITENAME].\n[SITENAME]";
$locale['email_secban_subject'] = "Your account on [SITENAME] has been banned";
$locale['email_secban_message'] = "Hello [USER_NAME],\nYour account on [SITENAME] has been banned by [USERNAME] because of some actions accredited to you or linked to your account were considered a security threat to the site.\n
If you want more information about this security ban, please, contact the site administrator at [SITENAME].\n
[SITENAME]";
$locale['email_suspend_subject'] = "Your account on [SITENAME] has been suspended";
$locale['email_suspend_message'] = "Hello [USER_NAME],\n
Your account on [SITENAME] has been suspended by [USERNAME] until [DATE] (site time) because of the following reason:\n
[REASON]\nIf you want more information about this suspension, please, contact the site administrator at [SITENAME].\n
[SITENAME]";