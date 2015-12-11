<?php
/**
 * Chinese_Simplified Locale
 * charset as ISO 639-1 - http://www.loc.gov/standards/iso639-2/php/code_list.php
 * region as ISO-3166 (2 Alpha numeric) - https://www.iso.org/obp/ui/#search
 */
setlocale(LC_TIME, "zh_CN.utf8"); // Linux Server (Windows may differ)
$locale['charset']    = "utf-8";
$locale['region'] = "CM";
$locale['xml_lang'] = "zh";
$locale['tinymce'] = "zh_CN";
$locale['phpmailer'] = "ch";
$locale['datepicker'] = "zh-cn";
//月份
$locale['months']      = "&nbsp|一月|二月|三月|四月|五月|六月|七月|八月|九月|十月|十一月|十二月";
$locale['shortmonths'] = "&nbsp|一月|二月|三月|四月|五月|六月|七月|八月|九月|十月|十一月|十二月";
$locale['weekdays']    = "星期日|星期一|星期二|星期三|星期四|星期五|星期六";
//时间
$locale['year']     = "年";
$locale['year_a']   = "年";
$locale['month']    = "月";
$locale['month_a']  = "个月";
$locale['day']      = "天";
$locale['day_a']    = "天";
$locale['hour']     = "小时";
$locale['hour_a']   = "个小时";
$locale['minute']   = "分钟";
$locale['minute_a'] = "分钟";
$locale['second']   = "秒";
$locale['second_a'] = "秒";
$locale['just_now'] = "刚刚";
$locale['ago']      = "前";
//地址
$locale['street1']      = "街道地址1";
$locale['street2']      = "街道地址2";
$locale['city']         = "市";
$locale['postcode']     = "邮政编码";
$locale['sel_country']  = "选择国家";
$locale['sel_state']    = "选择州";
$locale['sel_user']     = "请输入用户名";
$locale['add_language'] = "添加语言翻译";
$locale['add_lang']     = "添加 %s";
//名字
$locale['name']             = "全名";
$locale['username_pretext'] = "您的公共用户名和用户空间网页地址在以： %s %s";
$locale['first_name']       = "名字";
$locale['middle_name']      = "中名";
$locale['last_name']        = "姓名";
//文件
$locale['doc_type']        = "文件类型";
$locale['doc_series']      = "系列";
$locale['doc_number']      = "号码";
$locale['doc_authority']   = "权威";
$locale['doc_date_issue']  = "发行日期";
$locale['doc_date_expire'] = "到期日期";
//用户级别
$locale['user0']          = "公众";
$locale['user1']          = "会员";
$locale['user2']          = "管理员";
$locale['user3']          = "业主";
$locale['user_na']        = "其他";
$locale['user_guest']     = "客人";
$locale['user_anonymous'] = "匿名用户";
$locale['genitive']       = "%s 的 %s";
//用户状态
$locale['status0'] = "活性";
$locale['status1'] = "禁止";
$locale['status2'] = "未激活";
$locale['status3'] = "悬挂";
$locale['status4'] = "保安禁止";
$locale['status5'] = "取消";
$locale['status6'] = "匿名";
$locale['status7'] = "停用";
$locale['status8'] = "非活动";
//论坛版主级别
$locale['userf1'] = "版主";
//网站链接
$locale['global_001'] = "网站链接";
$locale['global_002'] = "没有链接\n";
//用户在线
$locale['global_010'] = "用户在线";
$locale['global_011'] = "客户在线";
$locale['global_012'] = "会员在线";
$locale['global_013'] = "没有会员在线";
$locale['global_014'] = "所有会员";
$locale['global_015'] = "未激活成员";
$locale['global_016'] = "最新成员";
//侧面板－论坛
$locale['global_020'] = "论坛主题";
$locale['global_021'] = "最新讨论";
$locale['global_022'] = "热门主题";
$locale['global_023'] = "未找到主题";
$locale['global_024'] = "参与讨论";
$locale['global_027'] = "未答复的主题";
$locale['global_028'] = "未解决的问题";
//侧面板－评论
$locale['global_025'] = "最新评论";
$locale['global_026'] = "未找到评论";
//侧面版－文章
$locale['global_030'] = "最新文章";
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
$locale['global_070']  = "Posted by ";
$locale['global_070b'] = "View all Post by %s";
$locale['global_071']  = "on ";
$locale['global_071b'] = "Author";
$locale['global_072']  = "Continue Reading";
$locale['global_073']  = " Comments";
$locale['global_073b'] = " Comment";
$locale['global_074']  = " Reads";
$locale['global_074b'] = " Read";
$locale['global_075']  = "Print";
$locale['print']       = 'Print';
$locale['global_076']  = "Edit";
$locale['global_077']  = "News";
$locale['global_078']  = "No News has been posted yet";
$locale['global_079']  = "In ";
$locale['global_080']  = "Uncategorised";
$locale['global_081']  = "News Home";
$locale['global_082']  = "News";
$locale['global_083']  = "Last Updated";
$locale['global_084']  = "News Category";
$locale['global_085']  = "All Other Categories";
$locale['global_086']  = "Most Recent News";
$locale['global_087']  = "Most Commented News";
$locale['global_088']  = "Highest Rating News";
$locale['global_089']  = "Be the first to comment on %s";
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
$locale['global_100']  = "Sign In";
$locale['global_101']  = "Login ID";
$locale['global_101a'] = "Enter Username";
$locale['global_101b'] = "Enter Email";
$locale['global_101c'] = "Enter Email or Username";
$locale['global_102']  = "Password";
$locale['global_103']  = "Stay signed in";
$locale['global_104']  = "Sign In";
$locale['global_105']  = "Not a member yet? [LINK]Click here[/LINK] to register.";
$locale['global_106']  = "[LINK]Forgot Password?[/LINK]";
$locale['global_107']  = "Register";
$locale['global_108']  = "Lost password";
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
$locale['UM060']  = "Login";
$locale['UM061']  = "Username";
$locale['UM061a'] = "Email";
$locale['UM061b'] = "Username or Email";
$locale['UM062']  = "Password";
$locale['UM063']  = "Remember Me";
$locale['UM064']  = "Login";
$locale['UM065']  = "Not a member yet? [LINK]Click here[/LINK] to register.";
$locale['UM066']  = "Forgotten your password?\n[LINK]Request a new one[/LINK].";
$locale['UM080']  = "Edit Profile";
$locale['UM081']  = "Private Messages";
$locale['UM082']  = "Members List";
$locale['UM083']  = "Admin Panel";
$locale['UM084']  = "Logout";
$locale['UM085']  = "You have %u new ";
$locale['UM086']  = "message";
$locale['UM087']  = "messages";
$locale['UM088']  = "Followed threads";
// Submit (news, link, article)
$locale['UM089'] = "Submit...";
$locale['UM090'] = "Submit News";
$locale['UM091'] = "Submit Link";
$locale['UM092'] = "Submit Article";
$locale['UM093'] = "Submit Photo";
$locale['UM094'] = "Submit Download";
$locale['UM095'] = "Submit Blog";
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

$locale['global_198'] = "WARNING: INSTALLER DETECTED, PLEASE DELETE THE /INSTALL/ FOLDER IMMEDIATELY.";
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
$locale['recaptcha']  = "en";
//Miscellaneous
$locale['global_900'] = "Unable to convert HEX to DEC";
//Language Selection
$locale['global_ML100'] = "Language:";
$locale['global_ML101'] = "- Select Language -";
$locale['global_ML102'] = "Site language";
// Flood Control
$locale['flood']        = "You are barred to post until the flood period cooldown is over. Please wait for %s.";
$locale['no_image']     = "No Image";
$locale['send_message'] = 'Send Message';
$locale['go_profile']   = 'Go to %s Profile Page';
// Global one word locales
$locale['hello']   = 'Hello!';
$locale['goodbye'] = 'Goodbye!';
$locale['welcome'] = 'Welcome back';
$locale['home']    = 'Home';
// Status
$locale['error']   = 'Error!';
$locale['success'] = 'Success!';
$locale['enable']  = 'Enable';
$locale['disable'] = 'Disable';
$locale['can']     = 'can';
$locale['cannot']  = 'cannot';
$locale['no']      = 'No';
$locale['yes']     = 'Yes';
$locale['off']     = 'Off';
$locale['on']      = 'On';
$locale['or']      = 'or';
$locale['by']      = 'by';
$locale['in']      = 'in';
$locale['of']      = 'of';
$locale['and']     = "and";
$locale['na']      = 'Not available';
$locale['joined']  = "Joined since: ";
// Navigation
$locale['next']      = 'Next';
$locale['previous']  = 'Previous';
$locale['back']      = 'Back';
$locale['forward']   = 'Forward';
$locale['go']        = 'Go';
$locale['cancel']    = 'Cancel';
$locale['move_up']   = "Move up";
$locale['move_down'] = "Move down";
$locale['load_more'] = "Load more Items";
$locale['load_end']  = "Load from Beginning";
// Actions
$locale['add']          = 'Add';
$locale['save']         = 'Save';
$locale['save_changes'] = 'Save Changes';
$locale['confirm']      = 'Confirm';
$locale['update']       = 'Update';
$locale['updated']      = 'Updated';
$locale['remove']       = 'Remove';
$locale['delete']       = 'Delete';
$locale['search']       = 'Search';
$locale['help']         = 'Help';
$locale['register']     = 'Register';
$locale['ban']          = 'Ban';
$locale['reactivate']   = 'Reactivate';
$locale['user']         = 'User';
$locale['promote']      = 'Promote';
$locale['show']         = 'Show';
//Tables
$locale['status'] = 'Status';
$locale['order']  = 'Order';
$locale['sort']   = 'Sort';
$locale['id']     = 'ID';
$locale['title']  = 'Title';
$locale['rights'] = 'Rights';
$locale['info']   = 'Info';
$locale['image']  = 'Image';
// Forms
$locale['choose']          = 'Please Choose One...';
$locale['no_opts']         = 'No selection';
$locale['root']            = 'As Parent';
$locale['choose-user']     = 'Please Choose a User...';
$locale['choose-location'] = 'Please Choose a Location';
$locale['parent']          = 'Create as New Parent..';
$locale['order']           = 'Item Ordering';
$locale['status']          = 'Status';
$locale['note']            = 'Make a note of this item';
$locale['publish']         = 'Published';
$locale['unpublish']       = 'Unpublished';
$locale['draft']           = 'Draft';
$locale['settings']        = 'Settings';
$locale['posted']          = 'posted';
$locale['profile']         = 'Profile';
$locale['edit']            = 'Edit';
$locale['qedit']           = 'Quick Edit';
$locale['view']            = 'View';
$locale['login']           = 'Login';
$locale['logout']          = 'Logout';
$locale['admin-logout']    = 'Admin Logout';
$locale['message']         = 'Private Messages';
$locale['logged']          = 'Logged in as ';
$locale['version']         = 'Version ';
$locale['browse']          = 'Browse ...';
$locale['close']           = 'Close';
$locale['nopreview']       = 'There is nothing to Preview';
$locale['mark_as']         = "Mark As";
// Alignment
$locale['left']   = "Left";
$locale['center'] = "Center";
$locale['right']  = "Right";
// Comments and ratings
$locale['comments']         = "Comments";
$locale['ratings']          = "Ratings";
$locale['comments_ratings'] = "Comments and Ratings";
$locale['user_account']     = "User Account";
$locale['about']            = "About";
// User status
$locale['online']  = "Online";
$locale['offline'] = "Offline";
// Words for formatting to single and plural forms. Count of forms is language-dependent
$locale['fmt_submission'] = "submission|submissions";
$locale['fmt_article']    = "article|articles";
$locale['fmt_blog']       = "blog|blogs";
$locale['fmt_comment']    = "comment|comments";
$locale['fmt_vote']       = "vote|votes";
$locale['fmt_rating']     = "rating|ratings";
$locale['fmt_day']        = "day|days";
$locale['fmt_download']   = "download|downloads";
$locale['fmt_follower']   = "follower|followers";
$locale['fmt_forum']      = "forum|forums";
$locale['fmt_guest']      = "guest|guests";
$locale['fmt_hour']       = "hour|hours";
$locale['fmt_item']       = "item|items";
$locale['fmt_member']     = "member|members";
$locale['fmt_message']    = "message|messages";
$locale['fmt_minute']     = "minute|minutes";
$locale['fmt_month']      = "month|months";
$locale['fmt_news']       = "news|news";
$locale['fmt_photo']      = "photo|photos";
$locale['fmt_post']       = "post|posts";
$locale['fmt_question']   = "question|questions";
$locale['fmt_read']       = "read|reads";
$locale['fmt_second']     = "second|seconds";
$locale['fmt_shouts']     = "shout|shouts";
$locale['fmt_thread']     = "thread|threads";
$locale['fmt_user']       = "user|users";
$locale['fmt_views']      = "view|views";
$locale['fmt_weblink']    = "weblink|weblinks";
$locale['fmt_week']       = "week|weeks";
$locale['fmt_year']       = "year|years";
// include Defender locales
include __DIR__."/defender.php";