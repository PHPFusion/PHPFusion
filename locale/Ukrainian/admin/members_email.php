<?php
$locale['email_create_subject'] = "Обліковий запис створено ";
$locale['email_create_message'] = "Вітаємо, [USER_NAME]!\n
Ваш обліковий запис на сайті <<".fusion_get_settings('sitename').">> успішно створено.\n
Ви можете тепер увійти на сайт, використовуючи наступні параметри авторизації:\n
ім&lsquo;я: [USER_NAME]\n
пароль: [PASSWORD]\n\n
З повагою,\n
".fusion_get_settings('siteusername');
$locale['email_activate_subject'] = "Обліковий запис активовано ";
$locale['email_activate_message'] = "Вітаємо, [USER_NAME]!\n
Ваш обліковий запис на сайті <<".fusion_get_settings('sitename').">> успішно активовано.\n
Ви можете тепер входити на сайт, використовуючи свої ім&lsquo;я та пароль.\n\n
З повагою,\n
".fusion_get_settings('siteusername');
$locale['email_deactivate_subject'] = "Запит на повторну активацію облікового запису";
$locale['email_deactivate_message'] = "Вітаємо, [USER_NAME].\n
Минуло ".fusion_get_settings('deactivation_period')." день(днів) з часу Вашого останнього візиту на сайт <<".fusion_get_settings('sitename').">>. Ваш обліковий запис позначено як бездіяльний, але уся інформація, пов&lsquo;язана із ним, збережена і не зазнала змін.\n
Щоб повторно активувати Ваш обліковий запис, просто перейдіть за наступним посиланням:\n
".fusion_get_settings('siteurl')."reactivate.php?user_id=[USER_ID]&code=[CODE]\n\n
З повагою,\n
".fusion_get_settings('siteusername');
$locale['email_ban_subject'] = "Ваш обліковий запис заблоковано";
$locale['email_ban_message'] = "Вітаємо, [USER_NAME].\n
Ваш обліковий запис на сайті <<".fusion_get_settings('sitename').">> заблокував (застосував бан) адміністратор ".$userdata['user_name']." за такою підставою:\n
[REASON].\n
Якщо бажаєте отримати детальну інформацію про це блокування, будь ласка, зв&lsquo;яжіться з адміністрацією сайту з допомогою електронної пошти ".fusion_get_settings('siteemail').".\n
".fusion_get_settings('siteusername');
$locale['email_secban_subject'] = "Ваш обліковий запис заблоковано з міркувань безпеки";
$locale['email_secban_message'] = "Вітаємо, [USER_NAME].\n
Ваш обліковий запис на сайті <<".fusion_get_settings('sitename').">> заблокував адміністратор ".$userdata['user_name']." через деякі Ваші особисті дії або дії пов&lsquo;язані з Вашим обліковим записом, що потенційно становили загрозу безпеці сайту.\n
Якщо бажаєте отримати детальну інформацію про це блокування з міркувань безпеки, будь ласка, зв&lsquo;яжіться з адміністрацією сайту з допомогою електронної пошти ".fusion_get_settings('siteemail').".\n
".fusion_get_settings('siteusername');
$locale['email_suspend_subject'] = "Ваш обліковий запис призупинено";
$locale['email_suspend_message'] = "Вітаємо, [USER_NAME].\n
Дію Вашого облікового запису на сайті <<".fusion_get_settings('sitename').">> призупинив адміністратор ".$userdata['user_name']." до [DATE] (час на сайті) за наступною підставою:\n
[REASON].\n
Якщо бажаєте отримати детальну інформацію про це призупинення, будь ласка, зв&lsquo;яжіться з адміністрацією сайту з допомогою електронної пошти ".fusion_get_settings('siteemail').".\n
".fusion_get_settings('siteusername');
