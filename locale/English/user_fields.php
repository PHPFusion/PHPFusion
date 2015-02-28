<?php
// Items shown in profile
$locale['u040'] = "Date Joined";
$locale['u041'] = "Last Visit";
$locale['u042'] = "Not visited";
$locale['u043'] = "Send Private Message";
$locale['u044'] = "Contact Information";
$locale['u045'] = "Miscellaneous Information";
$locale['u046'] = "Options";
$locale['u047'] = "Statistics";
$locale['u048'] = "Admin Information";
$locale['u049'] = "IP Address";
$locale['u050'] = "Undefined";
$locale['u051'] = "Hide Email?";
$locale['u052'] = " Yes ";
$locale['u053'] = " No";
$locale['u054'] = "View Suspension Log";
$locale['u055'] = "User Status:";
$locale['u056'] = "Reason";
$locale['u057'] = "User Groups";
$locale['u058'] = "Admin Options";
$locale['u059'] = "Add";
$locale['u060'] = "Add this user to selected group?";
$locale['u061'] = "Add to group";
$locale['u062'] = "User Avatar";
$locale['u063'] = "User Level";
$locale['u064'] = "Email";

$locale['u066'] = "Date Joined";
$locale['u067'] = "Last Visit";
$locale['u068'] = "User Name";
$locale['u069'] = "Edit";
$locale['u070'] = "Ban";
$locale['u071'] = "Suspend";
$locale['u072'] = "Delete";
$locale['u073'] = "Delete this user?";

// Profile and register
$locale['u100'] = "In order to change your password or email address<br />you must enter your current password.";
$locale['u101'] = "Register";
$locale['u102'] = "Edit Profile";
$locale['u103'] = "Profile";
$locale['u104'] = "Member Profile for";
$locale['u105'] = "Update Profile";

// View User Groups
$locale['u110'] = "View User Group";
$locale['u111'] = "%u user";
$locale['u112'] = "%u users";
$locale['u113'] = "User Name";
$locale['u114'] = "User Type";

// User name and email
$locale['u120'] = "User name contains invalid characters.";
$locale['u121'] = "The chosen user name is already taken by another user.";
$locale['u122'] = "User Name can not be left empty.";
$locale['u123'] = "Your email address does not appear to be valid.";
$locale['u124'] = "Your email address or email domain appears to be blacklisted.";
$locale['u125'] = "The email address is already registered by another user.";
$locale['u126'] = "Email address can not be left empty.";
$locale['u127'] = "User Name";
$locale['u128'] = "Email Address";
$locale['u129'] = "Account Info";

// Passwords
$locale['u130'] = "Admin passwords";
$locale['u131'] = "Admin password";
$locale['u132'] = "Login passwords";
$locale['u133'] = "Login password";
$locale['u134'] = "New login password";
$locale['u135'] = "Confirm password";
$locale['u136'] = "Password can not be left empty.";
$locale['u137'] = "Your current admin password can not be left empty.";
$locale['u138'] = "Your current login password can not be left empty.";
$locale['u139'] = "Login password did not match your current login password.";
$locale['u140'] = "Admin password did not match your current admin password.";
$locale['u141'] = " can not be the same as ";
$locale['u142'] = " is too short or contains invalid characters!";
$locale['u143'] = " does not match!";
$locale['u143a'] = " can not be left empty.";
$locale['u144'] = "New admin password";
$locale['u145'] = "Confirm admin password";
$locale['u146'] = " can not be the same as your current ";
$locale['u147'] = "Password must be between 8 and 64 chars long.<br />Allowed symbols are a-z, 0-9 and @!#$%&amp;\/()=-_?+*.,:;";
$locale['u148'] = "New Login Passwords are not identical.";
$locale['u148a'] = "New Admin Passwords are not identical.";
$locale['u149'] = "Your Current Login Password was not specified or is invalid.";
$locale['u149a'] = "Your Current Admin Password was not specified or is invalid.";
$locale['u149b'] = "Your Current Login Password was not specified or is invalid.<br />You can't set your admin password without your correct login password.";

// Email actiation
$locale['u150'] = "Your registration is almost complete, you will receive an email containing your login details along with a link to verify your account.";
$locale['u151'] = "Welcome to ".$settings['sitename'];
$locale['u152'] = "Hello USER_NAME,\n
Welcome to ".$settings['sitename'].". Here are your login details:\n
Username: USER_NAME
Password: USER_PASSWORD\n
Please activate your account via the following link: ACTIVATION_LINK\n\n
Regards,
".$settings['sitename'];
$locale['u153'] = "Activation email could not be sent.";
$locale['u154'] = "Please <a href='".BASEDIR."contact.php'>contact</a> the Site Administrator.";
$locale['u155'] = "Activate Account";
$locale['u156'] = "Please type in your current password to change your email.";

// Success / Fail
$locale['u160'] = "Registration complete";
$locale['u161'] = "You can now log in.";
$locale['u162'] = "An administrator will activate your account shortly.";
$locale['u163'] = "Profile was sucessfully updated.";
$locale['u164'] = "Update failed";
$locale['u165'] = "Registration failed";
$locale['u167'] = "for the following reason(s):";
$locale['u168'] = "Please Try Again.";
$locale['u169'] = "Profile updated";
$locale['u170'] = "Registration successful";
$locale['u171'] = "Your account has been verified.";
$locale['u172'] = "Member successfully added.";
$locale['u173'] = "Back to User Management.";
$locale['u174'] = "Add another member.";

// Avatar upload
$locale['u180'] = "Your avatar exceeded file size allowed, the limit is ".parsebytesize($settings['avatar_filesize']).".";
$locale['u181'] = "Your avatar appears to be an unsupported image type, supported image types are jpg, png and gif.";
$locale['u182'] = "Your avatar exceeded ".$settings['avatar_width']."x".$settings['avatar_height']." pixels.";
$locale['u183'] = "Your avatar was not uploaded correctly.";
$locale['u184'] = "Max. file size: %s / Max. size: %ux%u pixels";
$locale['u185'] = "Avatar";
$locale['u186'] = "Click Browse to upload an image";
$locale['u187'] = "Delete";

// Captcha and terms
$locale['u190'] = "Validation Code";
$locale['u191'] = "Enter Validation Code";
$locale['u192'] = "Terms of Agreement";
$locale['u193'] = "I have read the <a href='".BASEDIR."print.php?type=T' target='_blank'>Terms of Agreement</a> and I agree with them.";
$locale['u194'] = "Incorrect validation code.";
$locale['u195'] = "Captcha code can not be left empty.";

// E-mail Change Confirmation
$locale['u200'] = "A verify email has been sent to your new email address (%s).";
$locale['u201'] = "Your email address will be changed when you click the link in the mail.";
$locale['u202'] = "E-mail address verify - ".$settings['sitename'];
$locale['u203'] = "Hello [USER_NAME],\n
Someone set this email address in his account on our site.\n
If you realy want to change your email address to this one please click the following link:\n
[EMAIL_VERIFY_LINK]\n
Note: you have to be logged in to proceed.\n
Regards,
".$settings['siteusername']."
".$settings['sitename'];

?>