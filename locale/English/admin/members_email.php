<?php
$locale['email_create_subject'] = "Account created at [SITENAME]";
$locale['email_create_message'] = "Hello [USER_NAME],<br/>
Your account at [SITENAME] has been created.<br/>You can now login using the following details:<br/>
Username: [USER_NAME]<br/>Password: [PASSWORD]<br/>Regards,<br/>[SITEUSERNAME]";

$locale['email_activate_subject'] = "Account activated at [SITENAME]";
$locale['email_activate_message'] = "Hello [USER_NAME],<br/>Your account at [SITENAME] has been activated.<br/>
You can now login using your chosen username and password.<br/>Regards,<br/>[SITEUSERNAME]";

$locale['email_deactivate_subject'] = "Account reactivation required at [SITENAME]";
$locale['email_deactivate_message'] = "Hello [USER_NAME],<br/>It has been [DEACTIVATION_PERIOD] day(s) since you last logged in at [SITENAME]. Your user has been marked as inactive but all your account details and content remains intact.<br/>
To reactivate your account simply click the following link: [REACTIVATION_LINK]<br/>Regards,<br/>[SITEUSERNAME]";

$locale['email_ban_subject'] = "Your account on [SITENAME] has been banned";
$locale['email_ban_message'] = "Hello [USER_NAME],<br/>Your account on [SITENAME] has been banned by [ADMIN_USERNAME] because of the following reason:<br/>
[REASON]<br/>If you want more information about this ban, please, contact the site administrator at [SITENAME].<br/>Regards,<br/>[SITEUSERNAME]";

$locale['email_secban_subject'] = "Your account on [SITENAME] has been banned";
$locale['email_secban_message'] = "Hello [USER_NAME],<br/>Your account on [SITENAME] has been banned by [ADMIN_USERNAME] because of some actions accredited to you or linked to your account were considered a security threat to the site.<br/>
If you want more information about this security ban, please, contact the site administrator at [SITENAME].<br/>Regards,<br/>[SITEUSERNAME]";

$locale['email_suspend_subject'] = "Your account on [SITENAME] has been suspended";
$locale['email_suspend_message'] = "Hello [USER_NAME],<br/>
Your account on [SITENAME] has been suspended by [ADMIN_USERNAME] until [DATE] (site time) because of the following reason:<br/>
[REASON]<br/>If you want more information about this suspension, please, contact the site administrator at [SITENAME].<br/>Regards,<br/>[SITEUSERNAME]";

$locale['email_resend_subject'] = "Re-sent activation link - [SITENAME]";
$locale['email_resend_message'] = "Hello [USER_NAME],<br/>
You received this email because you did not activate the email on our site - [SITENAME].<br/>If you do not activate an email within one day, your registration request will be canceled.<br/>
You have registered with the following information:<br/>Username: [USER_NAME]<br/>
You can activate account with the following link:<br/>[ACTIVATION_LINK]<br/>Regards,<br/>[SITENAME]";
