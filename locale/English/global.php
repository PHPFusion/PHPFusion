<?php
/*
English Language Fileset
Produced by Nick Jones (Digitanium)
Email: digitanium@php-fusion.co.uk
Web: https://www.php-fusion.co.uk
*/

// Locale Settings
setlocale(LC_TIME, "en","GB"); // Linux Server (Windows may differ)
$locale['charset'] = "iso-8859-1";
$locale['xml_lang'] = "en";
$locale['tinymce'] = "en";
$locale['phpmailer'] = "en";

// Full & Short Months
$locale['months'] = "&nbsp|January|February|March|April|May|June|July|August|September|October|November|December";
$locale['shortmonths'] = "&nbsp|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sept|Oct|Nov|Dec";

// Standard User Levels
$locale['user0'] = "Public";
$locale['user1'] = "Member";
$locale['user2'] = "Administrator";
$locale['user3'] = "Super Administrator";
$locale['user_na'] = "N/A";
$locale['user_anonymous'] = "Anonymous User";
// Standard User Status
$locale['status0'] = "Active";
$locale['status1'] = "Banned";
$locale['status2'] = "Unactivated";
$locale['status3'] = "Suspended";
$locale['status4'] = "Security Banned";
$locale['status5'] = "Canceled";
$locale['status6'] = "Anonymous";
$locale['status7'] = "Deactivated";
$locale['status8'] = "Inactive";
// Forum Moderator Level(s)
$locale['userf1'] = "Moderator";
// Navigation
$locale['global_001'] = "Navigation";
$locale['global_002'] = "No links defined\n";
// Users Online
$locale['global_010'] = "Users Online";
$locale['global_011'] = "Guests Online";
$locale['global_012'] = "Members Online";
$locale['global_013'] = "No Members Online";
$locale['global_014'] = "Total Members";
$locale['global_015'] = "Unactivated Members";
$locale['global_016'] = "Newest Member";
// Forum Side panel
$locale['global_020'] = "Forum Threads";
$locale['global_021'] = "Newest Threads";
$locale['global_022'] = "Hottest Threads";
$locale['global_023'] = "No Threads created";
// Comments Side panel
$locale['global_025'] = "Latest Comments";
$locale['global_026'] = "No comments available";
// Articles Side panel
$locale['global_030'] = "Latest Articles";
$locale['global_031'] = "No Articles available";
// Downloads Side panel
$locale['global_032'] = "Latest Downloads";
$locale['global_033'] = "No Downloads available";
// Welcome panel
$locale['global_035'] = "Welcome";
// Latest Active Forum Threads panel
$locale['global_040'] = "Latest Active Forum Threads";
$locale['global_041'] = "My Recent Threads";
$locale['global_042'] = "My Recent Posts";
$locale['global_043'] = "New Posts";
$locale['global_044'] = "Thread";
$locale['global_045'] = "Views";
$locale['global_046'] = "Replies";
$locale['global_047'] = "Last Post";
$locale['global_048'] = "Forum";
$locale['global_049'] = "Posted";
$locale['global_050'] = "Author";
$locale['global_051'] = "Poll";
$locale['global_052'] = "Moved";
$locale['global_053'] = "You have not started any forum threads yet.";
$locale['global_054'] = "You have not posted any forum messages yet.";
$locale['global_055'] = "There are %u new posts in %u different threads since your last visit.";
$locale['global_056'] = "My Tracked Threads";
$locale['global_057'] = "Options";
$locale['global_058'] = "Stop";
$locale['global_059'] = "You're not tracking any threads.";
$locale['global_060'] = "Stop tracking this thread?";
// News & Articles
$locale['global_070'] = "Posted by ";
$locale['global_071'] = "on ";
$locale['global_072'] = "Read More";
$locale['global_073'] = " Comments";
$locale['global_073b'] = " Comment";
$locale['global_074'] = " Reads";
$locale['global_075'] = "Print";
$locale['global_076'] = "Edit";
$locale['global_077'] = "News";
$locale['global_078'] = "No News has been posted yet";
$locale['global_079'] = "In ";
$locale['global_080'] = "Uncategorised";
// Page Navigation
$locale['global_090'] = "Prev";
$locale['global_091'] = "Next";
$locale['global_092'] = "Page ";
$locale['global_093'] = " of ";
// Guest User Menu
$locale['global_100'] = "Login";
$locale['global_101'] = "Username";
$locale['global_102'] = "Password";
$locale['global_103'] = "Remember Me";
$locale['global_104'] = "Login";
$locale['global_105'] = "Not a member yet?<br /><a href='".BASEDIR."register.php' class='side'>Click here</a> to register.";
$locale['global_106'] = "Forgotten your password?<br />Request a new one <a href='".BASEDIR."lostpassword.php' class='side'>here</a>.";
$locale['global_107'] = "Register";
$locale['global_108'] = "Lost password";
// Member User Menu
$locale['global_120'] = "Edit Profile";
$locale['global_121'] = "Private Messages";
$locale['global_122'] = "Members List";
$locale['global_123'] = "Admin Panel";
$locale['global_124'] = "Logout";
$locale['global_125'] = "You have %u new ";
$locale['global_126'] = "message";
$locale['global_127'] = "messages";
$locale['global_128'] = "submission";
$locale['global_129'] = "submissions";
// Poll
$locale['global_130'] = "Member Poll";
$locale['global_131'] = "Submit Vote";
$locale['global_132'] = "You must login to vote.";
$locale['global_133'] = "Vote";
$locale['global_134'] = "Votes";
$locale['global_135'] = "Votes: ";
$locale['global_136'] = "Started: ";
$locale['global_137'] = "Ended: ";
$locale['global_138'] = "Polls Archive";
$locale['global_139'] = "Select a Poll to view from the list:";
$locale['global_140'] = "View";
$locale['global_141'] = "View Poll";
$locale['global_142'] = "There are no polls defined.";

// Captcha
$locale['global_150'] = "Validation Code:";
$locale['global_151'] = "Enter Validation Code:";

// Footer Counter
$locale['global_170'] = "unique visit";
$locale['global_171'] = "unique visits";
$locale['global_172'] = "Render time: %s seconds";
$locale['global_173'] = "Queries";
// Admin Navigation
$locale['global_180'] = "Admin Home";
$locale['global_181'] = "Return to Site";
$locale['global_182'] = "<strong>Notice:</strong> Admin Password not entered or incorrect.";
// Miscellaneous
$locale['global_190'] = "Maintenance Mode Activated";
$locale['global_191'] = "Your IP address is currently blacklisted.";
$locale['global_192'] = "Your user cookie has expired. Please log in again to proceed.";
$locale['global_193'] = "Could not set user cookie. Please make sure you have cookies enabled to be able to log in properly.";
$locale['global_194'] = "This account is currently suspended.";
$locale['global_195'] = "This account has not been activated.";
$locale['global_196'] = "Invalid username or password.";
$locale['global_197'] = "Please wait while we transfer you...<br /><br />
[ <a href='index.php'>Or click here if you do not wish to wait</a> ]";
$locale['global_198'] = "<strong>Warning:</strong> setup.php detected, please delete it immediately.";
$locale['global_199'] = "<strong>Warning:</strong> admin password not set, click <a href='".BASEDIR."edit_profile.php'>Edit Profile</a> to set it.";
//Titles
$locale['global_200'] = " - ";
$locale['global_201'] = ": ";
$locale['global_202'] = $locale['global_200']."Search";
$locale['global_203'] = $locale['global_200']."FAQ";
$locale['global_204'] = $locale['global_200']."Forum";
//Themes
$locale['global_210'] = "Skip to content";
// No themes found
$locale['global_300'] = "no theme found";
$locale['global_301'] = "We are really sorry but this page cannot be displayed. Due to some circumstances no site theme can be found. If you are a Site Administrator, please use your FTP client to upload any theme designed for <em>PHP-Fusion v7</em> to the <em>themes/</em> folder. After upload check in <em>Main Settings</em> to see if the selected theme was correctly uploaded to your <em>themes/</em> directory. Please note that the uploaded theme folder has to have the exact same name (including character case, which is important on Unix based servers) as chosen in <em>Main Settings</em> page.<br /><br />If you are regular member of this site, please contact the site\'s administrator via ".hide_email($settings['siteemail'])." e-mail and report this issue.";
$locale['global_302'] = "The Theme chosen in Main Settings does not exist or is incomplete!";
// JavaScript Not Enabled
$locale['global_303'] = "Oh no! Where's the <strong>JavaScript</strong>?<br />Your Web browser does not have JavaScript enabled or does not support JavaScript. Please <strong>enable JavaScript</strong> on your Web browser to properly view this Web site,<br /> or <strong>upgrade</strong> to a Web browser that does support JavaScript; <a href='http://firefox.com' rel='nofollow' title='Mozilla Firefox'>Firefox</a>, <a href='http://apple.com/safari/' rel='nofollow' title='Safari'>Safari</a>, <a href='http://opera.com' rel='nofollow' title='Opera Web Browser'>Opera</a>, <a href='http://www.google.com/chrome' rel='nofollow' title='Google Chrome'>Chrome</a> or a version of <a href='http://www.microsoft.com/windows/internet-explorer/' rel='nofollow' title='Internet Explorer'>Internet Explorer</a> newer then version 6.";
// User Management
// Member status
$locale['global_400'] = "suspended";
$locale['global_401'] = "banned";
$locale['global_402'] = "deactivated";
$locale['global_403'] = "account terminated";
$locale['global_404'] = "account anonymised";
$locale['global_405'] = "anonymous user";
$locale['global_406'] = "This account has been banned for the following reason:";
$locale['global_407'] = "This account has been suspended until ";
$locale['global_408'] = " for the following reason:";
$locale['global_409'] = "This account has been banned for security reasons.";
$locale['global_410'] = "The reason for this is: ";
$locale['global_411'] = "This account has been cancelled.";
$locale['global_412'] = "This account has been anonymized, probably becuase of inactivity.";
// Banning due to flooding
$locale['global_440'] = "Automatic Ban by Flood Control";
$locale['global_441'] = "Your account on ".$settings['sitename']."has been banned";
$locale['global_442'] = "Hello [USER_NAME],\n
Your account on ".$settings['sitename']." was caught posting too many items to the system in very short time from the IP ".USER_IP.", and have therefor been banned. This is done to prevent bots from submitting spam messages in rapid succession.\n
Please contact the site administrator at ".$settings['siteemail']." to have your account restored or report if this was not you causing this security ban.\n
".$settings['siteusername'];
// Lifting of suspension
$locale['global_450'] = "Suspension automatically lifted by system";
$locale['global_451'] = "Suspension lifted at ".$settings['sitename'];
$locale['global_452'] = "Hello USER_NAME,\n
The suspension of your account at ".$settings['siteurl']." has been lifted. Here are your login details:\n
Username: USER_NAME
Password: Hidden for security reasons\n
If you have forgot your password you can reset it via the following link: LOST_PASSWORD\n\n
Regards,\n
".$settings['siteusername'];
$locale['global_453'] = "Hello USER_NAME,\n
The suspension of your account at ".$settings['siteurl']." has been lifted.\n\n
Regards,\n
".$settings['siteusername'];
$locale['global_454'] = "Account reactivated at ".$settings['sitename'];
$locale['global_455'] = "Hello USER_NAME,\n
Last time you logged in your account was reactivated at ".$settings['siteurl']." and your account is no longer marked as inactive.\n\n
Regards,\n
".$settings['siteusername'];
// Function parsebytesize()
$locale['global_460'] = "Empty";
$locale['global_461'] = "Bytes";
$locale['global_462'] = "kB";
$locale['global_463'] = "MB";
$locale['global_464'] = "GB";
$locale['global_465'] = "TB";
//Safe Redirect
$locale['global_500'] = "You are being redirected to %s, please wait. If you're not redirected, click here.";

// Captcha Locales
$locale['global_600'] = "Validation Code";
$locale['recaptcha'] = "en";

//Miscellaneous
$locale['global_900'] = "Unable to convert HEX to DEC";
?>