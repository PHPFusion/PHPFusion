<?php
// Created by Admin
$locale['email_create_name'] = "Registration Confirmation Email";
$locale['email_create_subject'] = "Account created at [SITENAME]";
$locale['email_create_message'] = "Hello [USER_NAME],<br/>
Your account at [SITENAME] has been created.<br/>You can now login using the following details:<br/>
Username: [USER_NAME]<br/>Password: [PASSWORD]<br/>Regards,<br/>[SITEUSERNAME]";

// Registered by User - activation link determines whether email is valid
$locale['email_verify_name'] = "Registration Activation Email";
$locale['email_verify_subject'] = "Welcome to [SITENAME]. Please activate your account";
$locale['email_verify_message'] = "Hello [USER_NAME],<br/>
Welcome to [SITENAME]. Here are your login details:<br/>
Username: [USER_NAME]<br/>
Password: [USER_PASSWORD]<br/>
Please activate your account via the following link: [LINK] Activate Account<br/>
Regards,<br/>[SITEUSERNAME]";

// Verify New Email in update profile
$locale['email_change_name'] = "New Email Address Verification Email";
$locale['email_change_subject'] = "E-mail address verify - [SITENAME]";
$locale['email_change_message'] = "Hello [USER_NAME],<br/>
Someone set this email address in his account on our site.<br/>
If you really want to change your email address to this one please click the following link:<br/>
[EMAIL_VERIFY_LINK]<br/>
Note: you have to be logged in to proceed.<br/>
Regards, [SITEUSERNAME]<br/>[SITENAME]";

// Password change notification
$locale['email_passchange_name'] = "Password Notification Email";
$locale['email_passchange_subject'] = "New password notification for [SITENAME]";
$locale['email_passchange_message'] = "Hi [USER_NAME],
<br/>A new password has been set for your account at [SITENAME]. Please find the enclosed new login details:<br/>
Username: [USER_NAME]<br/>Password: [PASSWORD]<br/>Regards,<br/>[SITEUSERNAME]";

$locale['email_activate_name'] = "Account Confirmation Email";
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

$locale['email_2fa_name'] = "2 Factor Authorization PIN";
$locale['email_2fa_subject'] = "Your [SITENAME] account One Time Passcode";
$locale['email_2fa_message'] = "It looks like you are trying to log in to [SITENAME]. Here is the 2FA Pin code you need to access your account: <strong>[OTP]</strong><br/>
This email was sent because someone attempted to log into your [SITENAME] account. The login attempt included your correct username and password.<br/>
If you are not trying to log in, we recommend that you reset your [SITENAME] password as the security of your [SITENAME] account may be compromised. The login pin contained in this email is required to access your account. Do not share the pin with anyone.<br/><br/>
Regards,<br/>[SITENAME]<br/><br/>This notification has been sent to the email address associated with your [SITENAME] account. This email message was auto-generated. Please do not respond. If you need any additional help, please visit the [SITENAME] Support.";


$locale['email_2fa_setup_message'] = "It looks like you are trying to setup your 2 step verification in to [SITENAME]. Here is the 2FA Pin code you need to activate 2FA in your account: [OTP]\n\n
This email was sent because someone attempted to log into your [SITENAME] account. The login attempt included your correct username and password.\n\n
If you are not trying to log in, we recommend that you reset your [SITENAME] password as the security of your [SITENAME] account may be compromised. The login pin contained in this email is required to access your account. Do not share the pin with anyone.\n\n
Regards,\n\n[SITENAME]\n\nThis notification has been sent to the email address associated with your [SITENAME] account. This email message was auto-generated. Please do not respond. If you need any additional help, please visit the [SITENAME] Support.";



$locale['email_pass_name'] = "Password Recovery Email";
$locale['email_pass_subject'] = "New password request for [USER_NAME]";
$locale['email_pass_message'] = "Hello [USER_NAME],<br/>You have or someone has requested a new password to access your [SITENAME] account.<br/>
To change your password please click the following link:<br/>[LINK]<br/>Regards,<br/>[SITEUSERNAME]<br/><br/><br/><br/>This notification has been sent to the email address associated with your [SITENAME] account. This email message was auto-generated. Please do not respond. If you need any additional help, please visit the [SITENAME] Support.";
$locale['email_pass_notify'] = "Hello [USER_NAME],<br/>Your new password to access your [SITENAME] account is:<br/>
[TEXT]<br/>Regards,<br/>[SITEUSERNAME]";

// Flooding
$locale['email_secban_name'] = "Account Security Ban Email";
$locale['email_secban_subject'] = "Your account on [SITENAME] has been banned";
$locale['email_secban_message'] = "Hello [USER_NAME],<br/>
Your account on [SITENAME] was caught posting too many items to the system in very short time from the IP [USER_IP], and have therefore been banned. This is done to prevent bots from submitting spam messages in rapid succession.<br/>
Please contact the site administrator at [SITE_EMAIL] to have your account restored or report if this was not you causing this security ban.<br/>
Regards,<br/>[SITEUSERNAME]";
/**
 * $locale['global_441'] = "Your account on [SITENAME] has been banned";
 * $locale['global_442'] = "Hello [USER_NAME],<br/>
 * Your account on [SITENAME] was caught posting too many items to the system in very short time from the IP [USER_IP], and have therefor been banned. This is done to prevent bots from submitting spam messages in rapid succession.<br/>
 * Please contact the site administrator at [SITE_EMAIL] to have your account restored or report if this was not you causing this security ban.<br/>
 * Regards,<br/>[SITEUSERNAME]";
 */

$locale['email_reactivated_name'] = "Account Reactivation Email";
$locale['email_reactivated_subject'] = "Account reactivated at [SITENAME]"; // 454
$locale['email_reactivated_message'] = "Hello USER_NAME,<br/>
The suspension of your account at [SITEURL] has been lifted. Here are your login details:<br/>
Username: USER_NAME<br/>Password: Hidden for security reasons<br/>
If you have forgot your password you can reset it via the following link: LOST_PASSWORD<br/>
Regards,<br/>[SITEUSERNAME]";

/**
 * * $locale['global_454'] = "Account reactivated at [SITENAME]";
 * $locale['global_452'] = "Hello USER_NAME,<br/>
 * The suspension of your account at [SITEURL] has been lifted. Here are your login details:<br/>
 * Username: USER_NAME<br/>Password: Hidden for security reasons<br/>
 * If you have forgot your password you can reset it via the following link: LOST_PASSWORD<br/>
 * Regards,<br/>[SITEUSERNAME]";
 *
 * $locale['global_453'] = "Hello USER_NAME,<br/>The suspension of your account at [SITEURL] has been lifted.<br/>
 * Regards,<br/>[SITEUSERNAME]";
 */

$locale['email_unsuspend_name'] = "Account Unsuspended Email";
$locale['email_unsuspend_subject'] = "Suspension lifted at [SITENAME]"; // 451
$locale['email_unsuspend_message'] = "Hello USER_NAME,<br/>
Last time you logged in your account was reactivated at [SITEURL] and your account is no longer marked as inactive.<br/>
Regards,<br/>[SITEUSERNAME]";
/*
$locale['global_451'] = "Suspension lifted at [SITENAME]";
$locale['global_455'] = "Hello USER_NAME,<br/>
Last time you logged in your account was reactivated at [SITEURL] and your account is no longer marked as inactive.<br/>
Regards,<br/>[SITEUSERNAME]";
 */
