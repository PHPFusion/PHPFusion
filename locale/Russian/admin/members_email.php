<?php
$locale['email_create_subject'] = "Учётная запись создана на сайте ";
$locale['email_create_message'] = "Приветствую, [USER_NAME]!\n
Ваша учётная запись на сайте ".$settings['sitename']." была создана.\n
Вы можете войти, используя эти данные:\n
Имя пользователя: [USER_NAME]\n
Пароль: [PASSWORD]\n\n
С наилучшими пожеланиями,\n
".$settings['siteusername'];
$locale['email_activate_subject'] = "Учётная запись активирована на сайте ";
$locale['email_activate_message'] = "Приветствую, [USER_NAME]!\n
Ваша учётная запись на сайте ".$settings['sitename']." была активирована.\n
Вы можете войти на сайт, используя свои имя пользователя и пароль.\n\n
С наилучшими пожеланиями,\n
".$settings['siteusername'];
$locale['email_deactivate_subject'] = "Ваша учётная запись на сайте ".$settings['sitename']." требует повторной активации";
$locale['email_deactivate_message'] = "Приветствую, [USER_NAME]!\
Прошло ".$settings['deactivation_period']." дней с Вашего последнего посещения сайта ".$settings['sitename'].". Ваша учётная запись была помечена как неактивная, но Ваши пользовательские данные и содержимое на сайте остались в сохранности.\n
Для повторной активации Вашей учётной записи нажмите на ссылку:\n
".$settings['siteurl']."reactivate.php?user_id=[USER_ID]&code=[CODE]\n\n
С наилучшими пожеланиями,\n
".$settings['siteusername'];
$locale['email_ban_subject'] = "Ваша учётная запись на сайте ".$settings['sitename']." has been banned";
$locale['email_ban_message'] = "Приветствую, [USER_NAME]!\n
Ваша учётная запись на сайте ".$settings['sitename']." была заблокирована администратором ".$userdata['user_name']." по следующим причинам:\n
[REASON].\n
Если Вы хотите узнать больше информации, пожалуйста, свяжитесь с администратором по электропочте ".$settings['siteemail'].".\n
".$settings['siteusername'];
$locale['email_secban_subject'] = "Ваша учётная запись на сайте ".$settings['sitename']." заблокирована";
$locale['email_secban_message'] = "Приветствую, [USER_NAME]!\n
Ваша учётная запись на сайте ".$settings['sitename']." была заблокирована администратором ".$userdata['user_name']." из-за связанных с Вашей учётной записью действий, которые содержали угрозу безопасности сайта.\n
Если Вы хотите узнать больше информации, пожалуйста, свяжитесь с администратором по электропочте ".$settings['siteemail'].".\n
".$settings['siteusername'];
$locale['email_suspend_subject'] = "Ваша учётная запись на сайте ".$settings['sitename']." приостановлена";
$locale['email_suspend_message'] = "Приветствую, [USER_NAME]!\n
Ваша учётная запись на сайте ".$settings['sitename']." была приостановлена администратором ".$userdata['user_name']." до [DATE] (часовой пояс сайта) по следующим причинам:\n
[REASON].\n
Если Вы хотите узнать больше информации, пожалуйста, свяжитесь с администратором по электропочте ".$settings['siteemail'].".\n
".$settings['siteusername'];
?>