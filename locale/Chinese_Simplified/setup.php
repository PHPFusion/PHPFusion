<?php
// Index
$locale['setup_0000'] = "PHP-Fusion Core 9 Edition Setup";
$locale['setup_0001'] = "PHP-Fusion Core 9 Edition Setup";
$locale['setup_0002'] = 'Welcome to PHP-Fusion 9.00 Installation';
$locale['setup_0003'] = "The installer guide will guide you through the steps required to install PHP-Fusion CMS on your server. Should you need further assistance, please check our <a class='strong' href='https://php-fusion.co.uk/infusions/wiki/documentation.php?page=208'>Online Installation Documentation</a>.";
$locale['setup_0005'] = " I have read and agreed to the PHP-Fusion <a href='https://php-fusion.co.uk/license/'>terms and conditions use</a>.";
$locale['setup_5000'] = "In order to use PHP-Fusion, you need to check and agree to the terms of PHP-Fusion</a>.";
$locale['setup_0010'] = '9.0';
$locale['setup_0011'] = "en";
$locale['setup_0012'] = "utf-8";
$locale['setup_0101'] = "Introduction";
$locale['setup_0102'] = "File and Folder Diagnostics";
$locale['setup_0103'] = "Database Settings";
$locale['setup_0104'] = "Config / Database Setup";
$locale['setup_0105'] = "Configure Core System";
$locale['setup_0106'] = "Primary Admin Details";
$locale['setup_0107'] = "Final Settings";
$locale['setup_stepx'] = "Step %1\$d: %2\$s";

// Buttons
$locale['setup_0120'] = "Finish Configuration";
$locale['setup_0121'] = "Next";
$locale['setup_0122'] = "Try Again";
$locale['setup_0123'] = "Finish";

// Step 1
$locale['setup_1000'] = "Please select the required locale (language):";
$locale['setup_1001'] = "Download more locales from <a href='https://www.php-fusion.co.uk/downloads.php#langpacks'><strong>PHP-Fusion Official Support Site</strong></a>";
$locale['setup_1002'] = 'Welcome to PHP-Fusion 9.00 Recovery Mode.';
$locale['setup_1003'] = 'We have detected that there is an existing system installed.<br/><br/>Please choose any of the following to proceed.';
$locale['setup_1004'] = 'Clean Installation';
$locale['setup_1005'] = 'You can uninstall and clean your database and start a clean installation again.';
$locale['setup_1006'] = 'PLEASE BACKUP YOUR CONFIG.PHP. IT WILL BE REMOVED FROM THE SYSTEM DURING UNINSTALL.';
$locale['setup_1007'] = 'Uninstall and Start Again';
$locale['setup_1008'] = 'Core System Installer';
$locale['setup_1009'] = 'Change core system configurations.';
$locale['setup_1010'] = 'Go to System Installer';
$locale['setup_1011'] = 'Change Primary Account Details';
$locale['setup_1012'] = 'Change System Super Administrator details without need to recover password or transfer SA account ownership to another person.';
$locale['setup_1013'] = 'Change Super Admin Details';
$locale['setup_1014'] = 'Rebuild .htaccess';
$locale['setup_1015'] = 'Discard current file and replace with a standard version of the .htaccess file';
$locale['setup_1016'] = 'Build file';

$locale['setup_1017'] = 'Cancel and Exit this Installer';
$locale['setup_1018'] = 'You can exit this installer right now by clicking the button below. This will rename yoru config_temp.php file back to config.php.';
$locale['setup_1019'] = 'Exit Installer';


// Step 2
$locale['setup_1100'] = 'Passed';
$locale['setup_1101'] = 'Failed';
$locale['setup_1102'] = 'In order for setup to continue, the following files/folders must be marked as <span class="label label-success">writable</span> and should any tests fail, please chmod it to 755 or 777';
$locale['setup_1103'] = 'Write permissions check passed, click Next to continue.';
$locale['setup_1104'] = 'Write permissions check failed, please CHMOD files/folders marked Failed.';
$locale['setup_1105'] = 'Refresh';
$locale['setup_1106'] = 'File Structure Diagnostics';

// Step 3 - Access criteria
$locale['setup_1200'] = 'Database Settings and Server Paths';
$locale['setup_1201'] = 'Please enter your MySQL database access settings.';
$locale['setup_1202'] = "Database Hostname:";
$locale['setup_1203'] = "Database Username:";
$locale['setup_1204'] = "Database Password:";
$locale['setup_1205'] = "Database Name:";
$locale['setup_1206'] = "Table Prefix:";
$locale['setup_1207'] = "Cookie Prefix:";
$locale['setup_1208'] = "Enable PDO?";
$locale['setup_1209'] = "PDO seems to not be available";
$locale['setup_1210'] = "No";
$locale['setup_1211'] = "Yes";
$locale['setup_1212'] = "Select languages to use:";
$locale['setup_1213'] = "Site Owner Name";

// Step 4 - Database Setup
$locale['setup_1300'] = "Database connection established.";
$locale['setup_1301'] = "Config file successfully written.";
$locale['setup_1302'] = "Database tables created.";
$locale['setup_1303'] = "Error:";
$locale['setup_1304'] = "Unable to connect with MySQL.";
$locale['setup_1305'] = "Please ensure your MySQL username and password are correct.";
$locale['setup_1306'] = "Unable to write config file.";
$locale['setup_1307'] = "Please ensure config.php is writable.";
$locale['setup_1308'] = "Unable to create database tables.";
$locale['setup_1309'] = "Please specify your database name.";
$locale['setup_1310'] = "Unable to connect with MySQL database.";
$locale['setup_1311'] = "The specified MySQL database does not exist.";
$locale['setup_1312'] = "Table prefix is currently being used.";
$locale['setup_1313'] = "The specified table prefix is already in use and is running. No tables will be installed. Please start over or proceed to the next step.";
$locale['setup_1314'] = "Could not write or delete MySQL tables.";
$locale['setup_1315'] = "Please make sure your MySQL user has read, write and delete permission for the selected database.";
$locale['setup_1316'] = "Empty fields.";
$locale['setup_1317'] = "Please make sure you have filled out all the MySQL connection fields.";

// Step 5
$locale['setup_1400'] = "Please configure your core system.";
$locale['setup_1401'] = "IMPORTANT: Please back up your data if any before proceed. Removing a System will permanently erase all existing records.";
$locale['setup_1402'] = "Core System Ready.";
$locale['setup_1403'] = "Your website is now fully configured.<br/><br/>If you have not setup your Super Admin account yet, please proceed to the next step, otherwise, you can remove the installer.";
$locale['setup_1404'] = 'Install';
$locale['setup_1405'] = 'Uninstall';
$locale['setup_1406'] = '%s system have been successfully installed.';
$locale['setup_1407'] = '%s system system installation failed.';
$locale['setup_1408'] = '%s system have been successfully removed.';
$locale['setup_1409'] = '%s system cannot be removed or failed.';

// Step 6 - Super Admin login
$locale['setup_1500'] = "Primary Super Admin Account";
$locale['setup_1501'] = "Configure your Super Administrator account details.";
$locale['setup_1502'] = "Change Primary Super Admin Account";
$locale['setup_1503'] = "We have detected an existing Super Administrator Account. If you need to change details of this account, please type in new particulars to update the system with a new Super Administrator Account. ";
$locale['setup_1504'] = "Username:";
$locale['setup_1505'] = "Login Password:";
$locale['setup_1506'] = "Repeat Login password:";
$locale['setup_1507'] = "Admin Password:";
$locale['setup_1508'] = "Repeat Admin password:";
$locale['setup_1509'] = "Email address:";

// Step 6 - User details validation
$locale['setup_5010'] = "User name contains invalid characters.";
$locale['setup_5011'] = "User name field can not be left empty.";
$locale['setup_5012'] = "Your two login passwords do not match.";
$locale['setup_5013'] = "Invalid login password, please use alpha numeric characters only.<br />Password must be a minimum of 8 characters long.";
$locale['setup_5014'] = "Login password fields can not be left empty";
$locale['setup_5015'] = "Your two admin passwords do not match.";
$locale['setup_5016'] = "Your user password and admin password must be different.";
$locale['setup_5017'] = "Invalid admin password, please use alpha numeric characters only.<br />Password must be a minimum of 8 characters long.";
$locale['setup_5018'] = "Admin password fields can not be left empty.";
$locale['setup_5019'] = "Your email address does not appear to be valid.";
$locale['setup_5020'] = "Email field can not be left empty.";
$locale['setup_5021'] = "Your user settings are not correct:";

// Step 6 - Admin Panels
$locale['setup_3000'] = "Administrators";
$locale['setup_3001'] = "Article Categories";
$locale['setup_3002'] = "Articles";
$locale['setup_3003'] = "Banners";
$locale['setup_3004'] = "BB Codes";
$locale['setup_3005'] = "Blacklist";
$locale['setup_3006'] = "Comments";
$locale['setup_3007'] = "Custom Pages";
$locale['setup_3008'] = "Database Backup";
$locale['setup_3009'] = "Download Categories";
$locale['setup_3010'] = "Downloads";
$locale['setup_3011'] = "FAQs";
$locale['setup_3012'] = "Forums";
$locale['setup_3013'] = "Images";
$locale['setup_3014'] = "Infusions";
$locale['setup_3015'] = "Infusion Panels";
$locale['setup_3016'] = "Members";
$locale['setup_3017'] = "News Categories";
$locale['setup_3018'] = "News";
$locale['setup_3019'] = "Panels";
$locale['setup_3020'] = "Gallery Albums";
$locale['setup_3021'] = "PHP Info";
$locale['setup_3022'] = "Polls";
$locale['setup_3023'] = "Site Links";
$locale['setup_3024'] = "Smileys";
$locale['setup_3025'] = "Submissions";
$locale['setup_3026'] = "Upgrade";
$locale['setup_3027'] = "User Groups";
$locale['setup_3028'] = "Web Link Categories";
$locale['setup_3029'] = "Web Links";
$locale['setup_3030'] = "Main";
$locale['setup_3031'] = "Time and Date";
$locale['setup_3032'] = "Forum Settings";
$locale['setup_3033'] = "Registration";
$locale['setup_3034'] = "Gallery Settings";
$locale['setup_3035'] = "Miscellaneous";
$locale['setup_3036'] = "Private Message";
$locale['setup_3037'] = "User Fields";
$locale['setup_3038'] = "Forum Ranks";
$locale['setup_3039'] = "User Field Categories";
$locale['setup_3040'] = "News";
$locale['setup_3041'] = "User Management";
$locale['setup_3042'] = "Downloads";
$locale['setup_3043'] = "Items per Page";
$locale['setup_3044'] = "Security";
$locale['setup_3045'] = "News Settings";
$locale['setup_3046'] = "Downloads Settings";
$locale['setup_3047'] = "Admin Password Reset";
$locale['setup_3048'] = "Error Log";
$locale['setup_3049'] = "User Log";
$locale['setup_3050'] = "robots.txt";
$locale['setup_3051'] = "Language Settings";
$locale['setup_3052'] = "Permalinks";
$locale['setup_3053'] = "eShop";
$locale['setup_3054'] = "Blog Categories";
$locale['setup_3055'] = "Blog";
$locale['setup_3056'] = "Theme Customizations";
$locale['setup_3057'] = "Migration Tool";
$locale['setup_3058'] = "Theme Settings";

//Multilanguage table rights
$locale['setup_3200'] = "Articles";
$locale['setup_3201'] = "Custom Pages";
$locale['setup_3202'] = "Downloads";
$locale['setup_3203'] = "FAQs";
$locale['setup_3204'] = "Forums";
$locale['setup_3205'] = "News";
$locale['setup_3206'] = "Gallery";
$locale['setup_3207'] = "Polls";
$locale['setup_3208'] = "Email Templates";
$locale['setup_3209'] = "Weblinks";
$locale['setup_3210'] = "Sitelinks";
$locale['setup_3211'] = "Panels";
$locale['setup_3212'] = "Forum Ranks";
$locale['setup_3213'] = "Blog";
$locale['setup_3214'] = "eShop";

// Step 6 - Navigation Links
$locale['setup_3300'] = "Home";
$locale['setup_3301'] = "Articles";
$locale['setup_3302'] = "Downloads";
$locale['setup_3303'] = "FAQ";
$locale['setup_3304'] = "Discussion Forum";
$locale['setup_3305'] = "Contact Me";
$locale['setup_3306'] = "News Categories";
$locale['setup_3307'] = "Web Links";
$locale['setup_3308'] = "Gallery";
$locale['setup_3309'] = "Search";
$locale['setup_3310'] = "Submit Link";
$locale['setup_3311'] = "Submit News";
$locale['setup_3312'] = "Submit Article";
$locale['setup_3313'] = "Submit Photo";
$locale['setup_3314'] = "Submit Download";
$locale['setup_3315'] = "Submissions";
$locale['setup_3316'] = "Shoutbox";
$locale['setup_3317'] = "Submit Blog";
$locale['setup_3318'] = "Blog Archive Panel";
$locale['setup_3319'] = "Latest Discussions";
$locale['setup_3320'] = "Participated Discussions";
$locale['setup_3321'] = "Tracked Threads";
$locale['setup_3322'] = "Unanswered Threads";
$locale['setup_3323'] = "Unsolved Questions";
$locale['setup_3324'] = "Start a New Thread";

// Stage 6 - Panels
$locale['setup_3400'] = "Navigation";
$locale['setup_3401'] = "Online Users";
$locale['setup_3402'] = "Forum Threads";
$locale['setup_3403'] = "Latest Articles";
$locale['setup_3404'] = "Welcome Message";
$locale['setup_3405'] = "Forum Threads List";
$locale['setup_3406'] = "User Info";
$locale['setup_3407'] = "Members Poll";

// Stage 6 - News Categories
$locale['setup_3500'] = "Bugs";
$locale['setup_3501'] = "Downloads";
$locale['setup_3502'] = "Games";
$locale['setup_3503'] = "Graphics";
$locale['setup_3504'] = "Hardware";
$locale['setup_3505'] = "Journal";
$locale['setup_3506'] = "Members";
$locale['setup_3507'] = "Mods";
$locale['setup_3508'] = "Movies";
$locale['setup_3509'] = "Network";
$locale['setup_3510'] = "News";
$locale['setup_3511'] = "PHP-Fusion";
$locale['setup_3512'] = "Security";
$locale['setup_3513'] = "Software";
$locale['setup_3514'] = "Themes";
$locale['setup_3515'] = "Windows";

// Stage 6 - Sample Forum Ranks
$locale['setup_3600'] = "Super Admin";
$locale['setup_3601'] = "Admin";
$locale['setup_3602'] = "Moderator";
$locale['setup_3603'] = "Newbie";
$locale['setup_3604'] = "Junior Member";
$locale['setup_3605'] = "Member";
$locale['setup_3606'] = "Senior Member";
$locale['setup_3607'] = "Veteran Member";
$locale['setup_3608'] = "Fusioneer";

// Stage 6 - Sample Smileys
$locale['setup_3620'] = "Smile";
$locale['setup_3621'] = "Wink";
$locale['setup_3622'] = "Sad";
$locale['setup_3623'] = "Frown";
$locale['setup_3624'] = "Shock";
$locale['setup_3625'] = "Pfft";
$locale['setup_3626'] = "Cool";
$locale['setup_3627'] = "Grin";
$locale['setup_3628'] = "Angry";

// Stage 6 - User Field Categories
$locale['setup_3640'] = "Profile";
$locale['setup_3641'] = "Contact Information";
$locale['setup_3642'] = "Miscellaneous Information";
$locale['setup_3643'] = "Options";
$locale['setup_3644'] = "Statistics";
$locale['setup_3645'] = "Privacy";

// Stage 6 - User Fields
require_once("user_fields/user_aim.php");
require_once("user_fields/user_birthdate.php");
require_once("user_fields/user_blacklist.php");
require_once("user_fields/user_icq.php");
require_once("user_fields/user_location.php");
require_once("user_fields/user_sig.php");
require_once("user_fields/user_skype.php");
require_once("user_fields/user_theme.php");
require_once("user_fields/user_timezone.php");
require_once("user_fields/user_web.php");
require_once("user_fields/user_yahoo.php");

// Welcome message
$locale['setup_3650'] = "Welcome to your site";

// Final message
$locale['setup_1600'] = "Setup is Complete";
$locale['setup_1601'] = "PHP-Fusion 9.00 is now ready for use. Click Finish to rewrite your config_temp.php file to config.php<br/>";
$locale['setup_1602'] = "<strong>Note: After you enter your site you should delete the entire /install folder and chmod your config.php back to 0644 for security reasons.</strong>";
$locale['setup_1603'] = "Thank you for choosing PHP-Fusion.";

// Default time settings
// http://php.net/manual/en/function.strftime.php
$locale['setup_3700'] = "%d.%m.%y";
$locale['setup_3701'] = "%B %d %Y %H:%M:%S";
$locale['setup_3702'] = "%d-%m-%Y %H:%M";
$locale['setup_3703'] = "%B %d %Y";
$locale['setup_3704'] = "%B %d %Y %H:%M:%S";

// Email Template Setup
// Please do NOT translate the words between brackets [] !
$locale['setup_3800'] = "Email Templates";
$locale['setup_3801'] = "Notification on new PM";
$locale['setup_3802'] = "You have a new private message from [USER] waiting at [SITENAME]";
$locale['setup_3803'] = "Hello [RECEIVER],\r\nYou have received a new Private Message titled [SUBJECT] from [USER] at [SITENAME]. You can read your private message at [SITEURL]messages.php\r\n\r\nMessage: [MESSAGE]\r\n\r\nYou can disable email notification through the options panel of the Private Message page if you no longer wish to be notified of new messages.\r\n\r\nRegards,\r\n[SENDER].";
$locale['setup_3804'] = "Notification on new forum posts";
$locale['setup_3805'] = "Thread Reply Notification - [SUBJECT]";
$locale['setup_3806'] = "Hello [RECEIVER],\r\n\r\nA reply has been posted in the forum thread \'[SUBJECT]\' which you are tracking at [SITENAME]. You can use the following link to view the reply:\r\n\r\n[THREAD_URL]\r\n\r\nIf you no longer wish to watch this thread you can click the \'Stop tracking this thread\' link located at the top of the thread.\r\n\r\nRegards,\r\n[SENDER].";
$locale['setup_3807'] = "Contact form";
$locale['setup_3808'] = "[SUBJECT]";
$locale['setup_3809'] = "[MESSAGE]";

// Language Admin
$locale['setup_3900'] = "Multi Language";

// Official Supported System List
$locale['articles']['title']       = "Articles";
$locale['articles']['description'] = "A Standard Documentation System.";
$locale['blog']['title']           = "Blog";
$locale['blog']['description']     = "A Standard Blogging System.";
$locale['downloads']['title']      = "Downloads";
$locale['downloads']['description'] = "A Standard Downloads System.";
$locale['eshop']['title']          = "eShop";
$locale['eshop']['description']    = "An Electronic Commerce System.";
$locale['faqs']['title']           = "FAQs";
$locale['faqs']['description']     = "A Knowledgebase FAQ System.";
$locale['forums']['title']         = "Forum";
$locale['forums']['description']   = "A Bulletin Board Forum System.";
$locale['news']['title']           = "News";
$locale['news']['description']     = "A News Publishing System.";
$locale['photos']['title']         = "Gallery";
$locale['photos']['description']   = "A Photo Gallery Publishing System.";
$locale['polls']['title']          = "Polls";
$locale['polls']['description']    = "A Poll and User Voting System.";
$locale['weblinks']['title']       = "Weblinks";
$locale['weblinks']['description'] = "A Web Directory System.";
$locale['install']                 = "Install Core";
