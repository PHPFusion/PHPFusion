<?php
$locale['email_create_subject'] = "Учётная запись создана на сайте ";
$locale['email_create_message'] = "Приветствую, [USER_NAME]!\n
Ваша учётная запись на сайте ".fusion_get_settings('sitename')." была создана.\n
Вы можете войти, используя эти данные:\n
Имя пользователя: [USER_NAME]\n
Пароль: [PASSWORD]\n\n
С наилучшими пожеланиями,\n
".fusion_get_settings('siteusername');
$locale['email_activate_subject'] = "Учётная запись активирована на сайте ";
$locale['email_activate_message'] = "Приветствую, [USER_NAME]!\n
Ваша учётная запись на сайте ".fusion_get_settings('sitename')." была активирована.\n
Вы можете войти на сайт, используя свои имя пользователя и пароль.\n\n
С наилучшими пожеланиями,\n
".fusion_get_settings('siteusername');
$locale['email_deactivate_subject'] = "Ваша учётная запись на сайте ".fusion_get_settings('sitename')." требует повторной активации";
$locale['email_deactivate_message'] = "Приветствую, [USER_NAME]!\
Прошло ".fusion_get_settings('deactivation_period')." дней с Вашего последнего посещения сайта ".fusion_get_settings('sitename').". Ваша учётная запись была помечена как неактивная, но Ваши пользовательские данные и содержимое на сайте остались в сохранности.\n
Для повторной активации Вашей учётной записи нажмите на ссылку:\n
".fusion_get_settings('siteurl')."reactivate.php?user_id=[USER_ID]&code=[CODE]\n\n
С наилучшими пожеланиями,\n
".fusion_get_settings('siteusername');
$locale['email_ban_subject'] = "Ваша учётная запись на сайте ".fusion_get_settings('sitename')." была заблокирована.";
$locale['email_ban_message'] = "Приветствую, [USER_NAME]!\n
Ваша учётная запись на сайте ".fusion_get_settings('sitename')." была заблокирована администратором ".$userdata['user_name']." по следующим причинам:\n
[REASON].\n
Если Вы хотите узнать больше информации, пожалуйста, свяжитесь с администратором по электропочте ".fusion_get_settings('siteemail').".\n
".fusion_get_settings('siteusername');
$locale['email_secban_subject'] = "Ваша учётная запись на сайте ".fusion_get_settings('sitename')." заблокирована";
$locale['email_secban_message'] = "Приветствую, [USER_NAME]!\n
Ваша учётная запись на сайте ".fusion_get_settings('sitename')." была заблокирована администратором ".$userdata['user_name']." из-за связанных с Вашей учётной записью действий, которые содержали угрозу безопасности сайта.\n
Если Вы хотите узнать больше информации, пожалуйста, свяжитесь с администратором по электропочте ".fusion_get_settings('siteemail').".\n
".fusion_get_settings('siteusername');
$locale['email_suspend_subject'] = "Ваша учётная запись на сайте ".fusion_get_settings('sitename')." приостановлена";
$locale['email_suspend_message'] = "Приветствую, [USER_NAME]!\n
Ваша учётная запись на сайте ".fusion_get_settings('sitename')." была приостановлена администратором ".$userdata['user_name']." до [DATE] (часовой пояс сайта) по следующим причинам:\n
[REASON].\n
Если Вы хотите узнать больше информации, пожалуйста, свяжитесь с администратором по электропочте ".fusion_get_settings('siteemail').".\n
".fusion_get_settings('siteusername');
