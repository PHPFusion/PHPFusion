<?php
/**
 * English Locale
 *
 * @locale charset           ISO 639-1 - http://www.loc.gov/standards/iso639-2/php/code_list.php
 * @locale region            ISO-3166 (2 Alpha numeric) - https://www.iso.org/obp/ui/#search
 * @locale tinymce           refer to path includes/jscripts/tinymce/langs/{value}.js
 * @locale select2           refer to path includes/dynamics/select2/select2_locale_{value}.js
 * @locale phpmailer         refer to path includes/classes/PHPMailer/language/phpmailer.lang-{value}.php
 * @locale password_strength refer to path includes/dynamics/password/lang/{value}.js
 * @package locale/English
 */
setlocale(LC_TIME, "en_GB.utf8"); // Linux Server (Windows may differ)
$locale['charset'] = "utf-8";
$locale['region'] = "GB";
$locale['xml_lang'] = "en";
$locale['short_lang_name'] = "en";
$locale['tinymce'] = "en";
$locale['select2'] = "en";
$locale['phpmailer'] = "en";
$locale['datepicker'] = "en-gb";
$locale['datepicker_js'] = "M-DD-YYYY H:mm:ss";
$locale['datepicker_php'] = "m-d-Y H:i:s";
$locale['password_strength'] = "en";
$locale['date_day'] = "%d %b";
// Full & Short Months
$locale['months'] = "&nbsp|January|February|March|April|May|June|July|August|September|October|November|December";
$locale['shortmonths'] = "&nbsp|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sept|Oct|Nov|Dec";
$locale['weekdays'] = "Sunday|Monday|Tuesday|Wednesday|Thursday|Friday|Saturday";
// Timers
$locale['year'] = "year";
$locale['year_a'] = "years";
$locale['month'] = "month";
$locale['month_a'] = "months";
$locale['day'] = "day";
$locale['day_a'] = "days";
$locale['hour'] = "hour";
$locale['hour_a'] = "hours";
$locale['minute'] = "minute";
$locale['minute_a'] = "minutes";
$locale['second'] = "second";
$locale['second_a'] = "seconds";
$locale['just_now'] = "just now";
$locale['now'] = 'now';
$locale['ago'] = "ago";
// Geo
$locale['street1'] = "Street Address 1";
$locale['street2'] = "Street Address 2";
$locale['city'] = "City";
$locale['postcode'] = "Postcode";
$locale['sel_country'] = "Select Country";
$locale['sel_state'] = "Select Region";
$locale['sel_user'] = "Please type a user name";
$locale['add_language'] = "Add Language Translations";
$locale['add_lang'] = "Add %s";
$locale['other_states'] = "Other States";
// Name
$locale['name'] = "Full Name";
$locale['username_pretext'] = "Your public username is also the same as your user profile address located at: %s %s";
$locale['first_name'] = "First Name";
$locale['middle_name'] = "Middle Name";
$locale['last_name'] = "Last Name";
// Documents
$locale['doc_type'] = "Document type";
$locale['doc_series'] = "Series";
$locale['doc_number'] = "Number";
$locale['doc_authority'] = "Authority";
$locale['doc_date_issue'] = "Date of issue";
$locale['doc_date_expire'] = "Date of expiry";
// Standard User Levels
$locale['user0'] = "Public";
$locale['user1'] = "Member";
$locale['user2'] = "Administrator";
$locale['user3'] = "Super Administrator";
$locale['user_na'] = "N/A";
$locale['user_guest'] = "Guest";
$locale['user_anonymous'] = "Anonymous User";
$locale['genitive'] = "%s's %s";
// Standard User Status
$locale['status0'] = "Active";
$locale['status1'] = "Banned";
$locale['status2'] = "Unactivated";
$locale['status3'] = "Suspended";
$locale['status4'] = "Security Banned";
$locale['status5'] = "Cancelled";
$locale['status6'] = "Anonymous";
$locale['status7'] = "Deactivated";
$locale['status8'] = "Inactive";
// Forum Moderator Level(s)
$locale['userf1'] = "Moderator";
// Navigation
$locale['global_001'] = "Navigation";
$locale['global_002'] = "No links defined\n";
$locale['global_003'] = "Nothing to preview";
// Users Online
$locale['global_010'] = "Users Online Now";
$locale['global_011'] = "Guests Online";
$locale['global_012'] = "Members Online";
$locale['global_013'] = "No Members Online";
$locale['global_014'] = "Total Members";
$locale['global_015'] = "Unactivated Members";
$locale['global_016'] = "Newest Member";
$locale['global_017'] = "Toggle Navigation";
// Forum Side panel
$locale['global_020'] = "Forum Threads";
$locale['global_021'] = "Latest Discussions";
$locale['global_022'] = "Hottest Threads";
$locale['global_023'] = "No Threads created";
$locale['global_024'] = "Participated Discussions";
$locale['global_027'] = "Unanswered Threads";
$locale['global_028'] = "Unsolved Questions";
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
$locale['global_056'] = "Tracked Threads";
$locale['global_057'] = "Options";
$locale['global_058'] = "Stop Tracking";
$locale['global_059'] = "You're not tracking any threads.";
$locale['global_060'] = "Stop tracking this thread?";
// Blog, News & Articles
$locale['global_070'] = "Posted by ";
$locale['global_070b'] = "View all Post by %s";
$locale['global_071'] = "on ";
$locale['global_071b'] = "Author";
$locale['global_072'] = "Continue Reading";
$locale['global_073'] = " Comments";
$locale['global_073b'] = " Comment";
$locale['global_074'] = " Reads";
$locale['global_074b'] = " Read";
$locale['global_075'] = "Print";
$locale['print'] = "Print";
$locale['global_076'] = "Edit";
$locale['global_077'] = "News";
$locale['global_078'] = "No News has been posted yet";
$locale['global_079'] = "In ";
$locale['global_080'] = "Uncategorised";
$locale['global_081'] = "News Home";
$locale['global_082'] = "News";
$locale['global_083'] = "Last Updated";
$locale['global_084'] = "News Category";
$locale['global_085'] = "All Other Categories";
$locale['global_086'] = "Most Recent News";
$locale['global_087'] = "Most Commented News";
$locale['global_088'] = "Highest Rating News";
$locale['global_089'] = "Be the first to comment on %s";
$locale['global_089a'] = "Be the first to rate on this %s";
$locale['global_089b'] = "Thumb view";
$locale['global_089c'] = "List view";
// Page Navigation
$locale['global_090'] = "Prev";
$locale['global_091'] = "Next";
$locale['global_092'] = "Page ";
$locale['global_093'] = " of ";
$locale['global_094'] = " out of ";
// Guest User Menu
$locale['global_100'] = "Sign In";
$locale['global_101'] = "Login ID";
$locale['global_101a'] = "Enter Username";
$locale['global_101b'] = "Enter Email";
$locale['global_101c'] = "Enter Email or Username";
$locale['global_102'] = "Password";
$locale['global_103'] = "Stay signed in";
$locale['global_104'] = "Sign In";
$locale['global_105'] = "Not a member yet? [LINK]Click here[/LINK] to register.";
$locale['global_106'] = "[LINK]Forgot Password?[/LINK]";
$locale['global_107'] = "Register";
$locale['global_108'] = "Lost password";
$locale['global_109'] = "Sign Up";
// Member User Menu
$locale['global_120'] = "Customize your Profile Page";
$locale['global_121'] = "Private Messages";
$locale['global_122'] = "Members List";
$locale['global_123'] = "Admin Panel";
$locale['global_124'] = "Logout";
$locale['global_125'] = "You have %u new ";
$locale['global_126'] = "message";
$locale['global_127'] = "messages";
$locale['global_128'] = "submission";
$locale['global_129'] = "submissions";
// User Menu
$locale['UM060'] = "Login";
$locale['UM061'] = "Username";
$locale['UM061a'] = "Email";
$locale['UM061b'] = "Username or Email";
$locale['UM062'] = "Password";
$locale['UM063'] = "Remember me on this computer";
$locale['UM064'] = "Login";
$locale['UM065'] = "Not a member yet? [LINK]Click here[/LINK] to register.";
$locale['UM066'] = "Forgotten your password?\n[LINK]Request a new one[/LINK].";
$locale['UM067'] = "(not recommended on public or shared computer)";
$locale['UM080'] = "Edit Profile";
$locale['UM081'] = "Private Messages";
$locale['UM082'] = "Members List";
$locale['UM083'] = "Admin Panel";
$locale['UM084'] = "Logout";
$locale['UM085'] = "You have %u new ";
$locale['UM086'] = "message";
$locale['UM087'] = "messages";
$locale['UM088'] = "Followed threads";
// Submit (news, link, article)
$locale['UM089'] = "Submit...";
$locale['UM090'] = "Submit News";
$locale['UM091'] = "Submit Link";
$locale['UM092'] = "Submit Article";
$locale['UM093'] = "Submit Photo";
$locale['UM094'] = "Submit Download";
$locale['UM095'] = "Submit Blog";
$locale['UM102'] = "Submit FAQ";
// User Panel
$locale['UM096'] = "Welcome: ";
$locale['UM097'] = "Personal menu";
$locale['UM101'] = "Switch Language";
// Gauges
$locale['UM098'] = "PM Inbox :";
$locale['UM099'] = "PM Outbox :";
$locale['UM100'] = "PM Archive :";
// Keywords and Meta
$locale['tags'] = "Tags";
// Captcha
$locale['global_150'] = "Validation Code:";
$locale['global_151'] = "Enter Validation Code:";
// Footer Counter
$locale['global_170'] = "unique visit";
$locale['global_171'] = "unique visits";
$locale['global_172'] = "Render time: %s seconds";
$locale['global_173'] = "Queries";
$locale['global_174'] = "Memory used";
$locale['global_175'] = "Average: %s seconds";
$locale['global_176'] = "Privacy Policy";
// Admin Navigation
$locale['global_180'] = "Admin Home";
$locale['global_181'] = "Return to Site";
$locale['global_182'] = "Admin Password not entered or incorrect.";
// Miscellaneous
$locale['global_190'] = "Maintenance Mode Activated";
$locale['global_191'] = "Your IP address is currently blacklisted.";
$locale['global_192'] = "Your login session has expired. Please log in again to proceed.";
$locale['global_193'] = "Could not set document cookie. Please make sure you have cookies enabled to be able to log in properly.";
$locale['global_194'] = "This account is currently suspended.";
$locale['global_195'] = "This account has not been activated.";
$locale['global_196'] = "Invalid username or password.";
$locale['global_197'] = "Please wait while we transfer you...\n\n[ [LINK]Or click here if you do not wish to wait[/LINK] ]";
$locale['global_198'] = "WARNING: INSTALLER DETECTED, PLEASE DELETE THE INSTALL-PHP FILE IMMEDIATELY.";
$locale['global_199'] = "WARNING: admin password not set, click [LINK]Edit Profile[/LINK] to set it.";
//Titles
$locale['global_200'] = " - ";
$locale['global_201'] = ": ";
$locale['global_202'] = " - Search";
$locale['global_203'] = " - FAQ";
$locale['global_204'] = " - Forum";
//Themes
$locale['global_210'] = "Skip to content";
$locale['global_300'] = "no theme found";
$locale['global_301'] = "We are really sorry but this page cannot be displayed. Due to some circumstances no site theme can be found.
 If you are a Site Administrator, please use your FTP client to upload any theme designed for PHP-Fusion 9 to the themes folder.
 After upload check in Theme Settings to see if the selected theme was correctly uploaded to your themes directory.
 Please note that the uploaded theme folder has to have the exact same name (including character case, which is important on Unix based servers)
 as chosen in Theme Settings page.\n\nIf you are regular member of this site, please contact the site\'s administrator via [SITE_EMAIL] e-mail and report this issue.";
$locale['global_302'] = "The Theme chosen in Main Settings does not exist or is incomplete!";
// JavaScript Not Enabled
$locale['global_303'] = "Oh no! Where's the JavaScript?\nYour Web browser does not have JavaScript enabled or does not support JavaScript.
Please enable JavaScript on your Web browser to properly view this Web site, or upgrade to a Web browser that does support JavaScript.";
// User Management
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
$locale['global_412'] = "This account has been anonymized, probably because of inactivity.";
// Flood control
$locale['global_440'] = "Automatic Ban by Flood Control";
$locale['global_441'] = "Your account on [SITENAME] has been banned";
$locale['global_442'] = "Hello [USER_NAME],\n
Your account on [SITENAME] was caught posting too many items to the system in very short time from the IP [USER_IP], and have therefor been banned. This is done to prevent bots from submitting spam messages in rapid succession.\n
Please contact the site administrator at [SITE_EMAIL] to have your account restored or report if this was not you causing this security ban.\n\n
Regards,\n[SITEUSERNAME]";
// Authenticate Class
$locale['global_450'] = "Suspension automatically lifted by system";
$locale['global_451'] = "Suspension lifted at [SITENAME]";
$locale['global_452'] = "Hello USER_NAME,\n
The suspension of your account at [SITEURL] has been lifted. Here are your login details:\n
Username: USER_NAME\nPassword: Hidden for security reasons\n
If you have forgot your password you can reset it via the following link: LOST_PASSWORD\n\n
Regards,\n[SITEUSERNAME]";
$locale['global_453'] = "Hello USER_NAME,\nThe suspension of your account at [SITEURL] has been lifted.\n\n
Regards,\n[SITEUSERNAME]";
$locale['global_454'] = "Account reactivated at [SITENAME]";
$locale['global_455'] = "Hello USER_NAME,\n
Last time you logged in your account was reactivated at [SITEURL] and your account is no longer marked as inactive.\n\n
Regards,\n[SITEUSERNAME]";
$locale['global_456'] = "New password notification for [SITENAME]";
$locale['global_457'] = "Hi USER_NAME,
\n\nA new password has been set for your account at [SITENAME]. Please find the enclosed new login details:\n\n
Username: USER_NAME\nPassword: [PASSWORD]\n\nRegards,\n[SITEUSERNAME]";
$locale['global_458'] = "New password has been set for USER_NAME";
$locale['global_459'] = "New password has been set for USER_NAME, and email was not sent. Please ensure to tell the user of the new details.";
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
$locale['global_601'] = "You must enter a correct validation code";
$locale['recaptcha'] = "en";
// Site links
$locale['global_700'] = "Show More";
//Miscellaneous
$locale['global_900'] = "Unable to convert HEX to DEC";
$locale['global_901'] = "Insert Media";
//Language Selection
$locale['global_ML100'] = "Language:";
$locale['global_ML101'] = "- Select Language -";
$locale['global_ML102'] = "Site language";
$locale['global_ML103'] = "Switch Language";
// Flood Control
$locale['flood'] = "You are barred to post until the flood period cooldown is over. Please wait for %s.";
$locale['no_image'] = "No Image";
$locale['send_message'] = "Send Message";
$locale['go_profile'] = "Go to %s Profile Page";
// Global one word locales
$locale['hello'] = "Hello!";
$locale['goodbye'] = "Goodbye!";
$locale['welcome'] = "Welcome back";
$locale['home'] = "Home";
// Status
$locale['error'] = "Error!";
$locale['success'] = "Success!";
$locale['enable'] = "Enable";
$locale['disable'] = "Disable";
$locale['can'] = "can";
$locale['cannot'] = "cannot";
$locale['no'] = "No";
$locale['yes'] = "Yes";
$locale['off'] = "Off";
$locale['on'] = "On";
$locale['or'] = "or";
$locale['by'] = "by";
$locale['in'] = "in";
$locale['of'] = "of";
$locale['and'] = "and";
$locale['na'] = "No information available";
$locale['joined'] = "Joined since: ";
// Navigation
$locale['next'] = "Next";
$locale['previous'] = "Previous";
$locale['back'] = "Back";
$locale['forward'] = "Forward";
$locale['go'] = "Go";
$locale['cancel'] = "Cancel";
$locale['clear'] = "Clear";
$locale['move'] = "Move";
$locale['move_up'] = "Move up";
$locale['move_down'] = "Move down";
$locale['load_more'] = "Load more Items";
$locale['load_end'] = "Load from Beginning";
// Actions
$locale['add'] = "Add";
$locale['save'] = "Save";
$locale['save_changes'] = "Save Changes";
$locale['save_and_close'] = "Save and Close";
$locale['confirm'] = "Confirm";
$locale['update'] = "Update";
$locale['updated'] = "Updated";
$locale['remove'] = "Remove";
$locale['delete'] = "Delete";
$locale['search'] = "Search";
$locale['help'] = "Help";
$locale['register'] = "Register";
$locale['ban'] = "Ban";
$locale['reactivate'] = "Reactivate";
$locale['user'] = "User";
$locale['promote'] = "Promote";
$locale['show'] = "Show";
$locale['actions'] = "Actions";
$locale['language'] = "Language";
// Persons & Identifiers
$locale['you'] = "You";
$locale['me'] = "Me";
$locale['they'] = "They";
$locale['we'] = "We";
$locale['us'] = "Us";
$locale['he'] = "He";
$locale['she'] = "She";
$locale['it'] = "It";
//Tables
$locale['order'] = "Order";
$locale['sort'] = "Sort";
$locale['id'] = "ID";
$locale['title'] = "Title";
$locale['rights'] = "Rights";
$locale['info'] = "Info";
$locale['image'] = "Image";
// Forms
$locale['choose'] = "Please Choose One...";
$locale['no_opts'] = "No selection";
$locale['root'] = "As Parent";
$locale['choose-user'] = "Please Choose a User...";
$locale['choose-location'] = "Please Choose a Location";
$locale['parent'] = "Create as New Parent..";
$locale['item_order'] = "Item Ordering";
$locale['status'] = "Status";
$locale['note'] = "Make a note of this item";
$locale['publish'] = "Publish";
$locale['unpublish'] = "Unpublish";
$locale['sticky'] = "Sticky";
$locale['unsticky'] = "Unsticky";
$locale['draft'] = "Draft";
$locale['settings'] = "Settings";
$locale['posted'] = "posted";
$locale['profile'] = "Profile";
$locale['edit'] = "Edit";
$locale['qedit'] = "Quick Edit";
$locale['view'] = "View";
$locale['login'] = "Login";
$locale['logout'] = "Logout";
$locale['admin-logout'] = "Admin Logout";
$locale['message'] = "Private Messages";
$locale['logged'] = "Logged in as ";
$locale['version'] = "Version ";
$locale['browse'] = "Browse ...";
$locale['close'] = "Close";
$locale['nopreview'] = "There is nothing to Preview";
$locale['mark_as'] = "Mark As";
$locale['preview'] = "Preview";
$locale['custom'] = "Custom";
$locale['submit'] = "Submit";
// Alignment
$locale['left'] = "Left";
$locale['center'] = "Center";
$locale['right'] = "Right";
// Comments and ratings
$locale['comments'] = "Comments";
$locale['ratings'] = "Ratings";
$locale['comments_ratings'] = "Comments and Ratings";
$locale['user_account'] = "User Account";
$locale['about'] = "About";
// User status
$locale['online'] = "Online";
$locale['offline'] = "Offline";
// Words for formatting to single and plural forms. Count of forms is language-dependent
$locale['fmt_submission'] = "submission|submissions";
$locale['fmt_article'] = "article|articles";
$locale['fmt_blog'] = "blog|blogs";
$locale['fmt_comment'] = "comment|comments";
$locale['fmt_vote'] = "vote|votes";
$locale['fmt_rating'] = "rating|ratings";
$locale['fmt_day'] = "day|days";
$locale['fmt_download'] = "download|downloads";
$locale['fmt_follower'] = "follower|followers";
$locale['fmt_forum'] = "forum|forums";
$locale['fmt_guest'] = "guest|guests";
$locale['fmt_hour'] = "hour|hours";
$locale['fmt_item'] = "item|items";
$locale['fmt_member'] = "member|members";
$locale['fmt_message'] = "message|messages";
$locale['fmt_minute'] = "minute|minutes";
$locale['fmt_month'] = "month|months";
$locale['fmt_news'] = "news|news";
$locale['fmt_photo'] = "photo|photos";
$locale['fmt_post'] = "post|posts";
$locale['fmt_question'] = "question|questions";
$locale['fmt_read'] = "read|reads";
$locale['fmt_second'] = "second|seconds";
$locale['fmt_shouts'] = "shout|shouts";
$locale['fmt_thread'] = "thread|threads";
$locale['fmt_user'] = "user|users";
$locale['fmt_views'] = "view|views";
$locale['fmt_weblink'] = "weblink|weblinks";
$locale['fmt_week'] = "week|weeks";
$locale['fmt_year'] = "year|years";
$locale['fmt_points'] = "point|points";
$locale['fmt_admin'] = "admin|admins";
$locale['fmt_group'] = "group|groups";
$locale['fmt_category'] = "category|categories";
// include Defender locales
include __DIR__."/defender.php";
