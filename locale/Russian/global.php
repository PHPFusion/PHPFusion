<?php
/*
Russian Language Fileset
Produced by Chubatyj Vitalij (Rizado)
http://chubatyj.ru/
*/
// Locale Settings
setlocale(LC_ALL, "ru_RU.UTF-8"); // Linux Server (Windows may differ)
$locale['charset'] = "utf-8";
$locale['xml_lang']  = "ru";
$locale['tinymce']   = "ru";
$locale['phpmailer'] = "ru";
$locale['datepicker'] = "ru";
// Full & Short Months
$locale['months']  = "&nbsp;|Январь|Февраль|Март|Апрель|Май|Июнь|Июль|Август|Сентябрь|Октябрь|Ноябрь|Декабрь";
$locale['shortmonths'] = "&nbsp;|Янв|Фев|Мар|Апр|Май|Июн|Июл|Авг|Сен|Окт|Ноя|Дек";
$locale['weekdays'] = "Воскресенье|Понедельник|Вторник|Среда|Четверг|Пятница|Суббота";
	// Timers
$locale['year'] = "год";
$locale['year_a'] = "года(лет)";
$locale['month'] = "месяц";
$locale['month_a'] = "месяца(-ев)";
$locale['day'] = "день";
$locale['day_a'] = "дня(-ей)";
$locale['hour'] = "час";
$locale['hour_a'] = "часа(-ов)";
$locale['minute'] = "минута";
$locale['minute_a'] = "минут(-ы)";
$locale['second'] = "секунда";
$locale['second_a'] = "секунд(-ы)";
$locale['just_now'] = "только что";
$locale['ago'] = "назад";

// Geo
$locale['street1'] = "Улица, адрес 1";
$locale['street2'] = "Улица, адрес 2";
$locale['city'] = "Город";
$locale['postcode'] = "Почтовый индекс";
$locale['sel_country'] = "Страна";
$locale['sel_state'] = "Регион";
$locale['sel_user'] = "Пожалуйста, укажите имя пользователя";
$locale['add_language'] = "Добавить переводы";
$locale['add_lang'] = "Добавить язык: %s";

// Name
$locale['name'] = "Полное имя";
$locale['username_pretext'] = "Ваше публичное имя пользователя, то же, что указанное в профиле адрес:<div class='alert alert-info m-t-10 p-10'>%s<strong>%s</strong></div>";
$locale['first_name'] = "Имя";
$locale['middle_name'] = "Отчество";
$locale['last_name'] = "Фамилия";
// Documents
$locale['doc_type'] = "Тип документа";
$locale['doc_series'] = "Серия";
$locale['doc_number'] = "Номер";
$locale['doc_authority'] = "Кем выдан";
$locale['doc_date_issue'] = "Дата выдачи";
$locale['doc_date_expire'] = "Срок действия";
// Standard User Levels
$locale['user0']  = "Гость";
$locale['user1']  = "Участник";
$locale['user2']  = "Администратор";
$locale['user3']  = "Суперадмин";
$locale['user_na']= "N/A";
$locale['user_guest'] = "Гость";
$locale['user_anonymous'] = "Анонимный";
$locale['genitive'] = "%s %s";
// Standard User Status
$locale['status0'] = "Активный";
$locale['status1'] = "Заблокированный";
$locale['status2'] = "Неактивированный";
$locale['status3'] = "Приостановленный";
$locale['status4'] = "Заблокированный по безопасности";
$locale['status5'] = "Отменённый";
$locale['status6'] = "Анонимный";
$locale['status7'] = "Деактивированный";
$locale['status8'] = "Неактивный";
// Forum Moderator Level(s)
$locale['userf1'] = "Модератор";
// Navigation
$locale['global_001'] = "Навигация";
$locale['global_002'] = "Ссылки не заданы\n";
// Users Online
$locale['global_010'] = "Пользователей на сайте";
$locale['global_011'] = "Гостей на сайте";
$locale['global_012'] = "Участников на сайте";
$locale['global_013'] = "Участников на сайте нет";
$locale['global_014'] = "Всего зарегистрировано";
$locale['global_015'] = "Неактивированные участники";
$locale['global_016'] = "Последний участник";
// Forum Side panel
$locale['global_020'] = "Темы форума";
$locale['global_021'] = "Новые темы";
$locale['global_022'] = "Обсуждаемые темы";
$locale['global_023'] = "Темы не созданы";
// Comments Side panel
$locale['global_025'] = "Последние комментарии";
$locale['global_026'] = "Нет комментариев";
// Articles Side panel
$locale['global_030'] = "Последние статьи";
$locale['global_031'] = "Нет статей";
// Downloads Side panel
$locale['global_032'] = "Последние загрузки";
$locale['global_033'] = "Нет загрузок";
// Welcome panel
$locale['global_035'] = "Добро пожаловать";
// Latest Active Forum Threads panel
$locale['global_040'] = "Последние активные темы форума";
$locale['global_041'] = "Мои последние темы";
$locale['global_042'] = "Мои последние сообщения";
$locale['global_043'] = "Новые сообщения";
$locale['global_044'] = "Тема";
$locale['global_045'] = "Просмотров";
$locale['global_046'] = "Ответов";
$locale['global_047'] = "Последнее сообщение";
$locale['global_048'] = "Форум";
$locale['global_049'] = "Опубликовано";
$locale['global_050'] = "Автор";
$locale['global_051'] = "Опрос";
$locale['global_052'] = "Перенесено";
$locale['global_053'] = "Вы не имеете начатых тем.";
$locale['global_054'] = "Вы не имеете сообщений на форуме.";
$locale['global_055'] = "Со времени последнего посещения вы имеете %u новых сообщений в %u темах.";
$locale['global_056'] = "Отслеживаемые темы";
$locale['global_057'] = "Параметры";
$locale['global_058'] = "Прекратить";
$locale['global_059'] = "Вы не отслеживаете тем.";
$locale['global_060'] = "Прекратить отслеживать тему?";
// Blog, News & Articles
$locale['global_070']  = "Опубликовано ";
$locale['global_070b'] = "Смотреть всё сообщение от %s";
$locale['global_071']  = "в ";
$locale['global_071b'] = "Автор";
$locale['global_072']  = "Продолжить чтение";
$locale['global_073']  = " комментариев";
$locale['global_073b'] = " комментарий";
$locale['global_074']  = " прочтений";
$locale['global_074b']  = " прочтение";
$locale['global_075']  = "Печать";
$locale['print'] = 'Печать';
$locale['global_076']  = "Правка";
$locale['global_077']  = "Новости";
$locale['global_078']  = "Новости не опубликованы";
$locale['global_079']  = "В ";
$locale['global_080']  = "Без категории";
$locale['global_081'] = "В начало новостей";
$locale['global_082'] = "Новости";
$locale['global_083'] = "Последнее обновлённое";
$locale['global_084'] = "Категория новостей";
$locale['global_085'] = "Все другие категории";
$locale['global_086'] = "Последние новости";
$locale['global_087'] = "Наиболее комментируемые новости";
$locale['global_088'] = "Новости с наивысшими оценками";
$locale['global_089'] = "Будьте первым, кто прокомментирует %s";
$locale['global_089a'] = "Будьте первым, кто оценит %s";
$locale['global_089b'] = "В виде предпросмотра";
$locale['global_089c'] = "В виде списка";
// Page Navigation
$locale['global_090'] = "Пред.";
$locale['global_091'] = "След.";
$locale['global_092'] = "Страница ";
$locale['global_093'] = " из ";
$locale['global_094'] = " выйти из ";
// Guest User Menu
$locale['global_100'] = "Вход на сайт";
$locale['global_101'] = "ID входа";
$locale['global_101a'] = "Пожалуйста, введите ваш ID входа";
$locale['global_102'] = "Пароль";
$locale['global_103'] = "Не выходить с сайта";
$locale['global_104'] = "Войти";
$locale['global_105'] = "Не зарегистрированы? <a href='".BASEDIR."register.php' class='side'>Нажмите</a> для регистрации.";
$locale['global_106'] = "<i class='fa fa-question-circle m-r-10'></i><a href='".BASEDIR."lostpassword.php' class='side'>Забыли пароль?</a>";
$locale['global_107'] = "Регистрация";
$locale['global_108'] = "Забыли пароль";
// Member User Menu
$locale['global_120'] = "Настроить  профиль";
$locale['global_121'] = "Личные сообщения";
$locale['global_122'] = "Список участников";
$locale['global_123'] = "Панель администратора";
$locale['global_124'] = "Выход";
$locale['global_125'] = "Вы имеете %u новых ";
$locale['global_126'] = "сообщение";
$locale['global_127'] = "сообщений";
$locale['global_128'] = "присланный материал";
$locale['global_129'] = "присланных материалов";
// Member User Menu
$locale['UM060'] = "Вход";
$locale['UM061'] = "Пользователь";
$locale['UM061a'] = "Электропочта";
$locale['UM061b'] = "Пользователь или электропочта";
$locale['UM062'] = "Пароль";
$locale['UM063'] = "Запомнить";
$locale['UM064'] = "Войти";
$locale['UM065'] = "Не зарегистрированы?<br /><a href='".BASEDIR."register.php' class='side'>Нажмите</a> для регистрации.";
$locale['UM066'] = "Забыли пароль?<br />Запросите новый <a href='".BASEDIR."lostpassword.php' class='side'>здесь</a>.";
$locale['UM080'] = "Изменить профиль";
$locale['UM081'] = "Личные сообщения";
$locale['UM082'] = "Участники";
$locale['UM083'] = "Админпанель";
$locale['UM084'] = "Выход";
$locale['UM085'] = "У вас %u новых ";
$locale['UM086'] = "сообщение";
$locale['UM087'] = "сообщений";
$locale['UM088'] = "Отслеживаемые темы";
// Submit (news, link, article)
$locale['UM089'] = "Добавить...";
$locale['UM090'] = "Добавить новость";
$locale['UM091'] = "Добавить ссылку";
$locale['UM092'] = "Добавить статью";
$locale['UM093'] = "Добавить фото";
$locale['UM094'] = "Добавить загрузку";
$locale['UM095'] = "Добавить блог";
// User Panel
$locale['UM096'] = "Добро пожаловать: ";
$locale['UM097'] = "Личное меню";
$locale['UM101'] = "Изменить язык";
// Gauges
$locale['UM098'] = "Входящие сообщения:";
$locale['UM099'] = "Отправленные сообщения:";
$locale['UM100'] = "Архив сообщений:";

// Keywords and Meta
$locale['tags'] = "Тэги";
// Captcha
$locale['global_150'] = "Код проверки:";
$locale['global_151'] = "Введите код проверки:";
// Footer Counter
$locale['global_170'] = "уникальный посетитель";
$locale['global_171'] = "уникальных посетителей";
$locale['global_172'] = "Время загрузки: %s сек.";
$locale['global_173'] = "Запросов";
$locale['global_174'] = "Использовано памяти";
$locale['global_175'] = "В среднем: %s сек.";
// Admin Navigation
$locale['global_180'] = "Панель администратора";
$locale['global_181'] = "Вернуться на сайт";
$locale['global_182'] = "<strong>Внимание:</strong> Админпароль не введён или введён неправильно.";
// Miscellaneous
$locale['global_190'] = "Включен режим обслуживания";
$locale['global_191'] = "Ваш IP-адрес находится в чёрном списке.";
$locale['global_192'] = "Ваша сессия завершена. Пожалуйста, войдите для продолжения.";
$locale['global_193'] = "Невозможно установить cookie. Убедитесь, что cookies разрешены, это нужно для корректного входа.";
$locale['global_194'] = "Действие этой учётной записи приостановлено.";
$locale['global_195'] = "Эта учётная запись не активирована.";
$locale['global_196'] = "Неверное имя пользователя или пароль.";
$locale['global_197'] = "Подождите, пока мы перенаправим Вас...<br /><br />
[ <a href='index.php'>Или нажмите здесь, если не хотите ждать</a> ]";
$locale['global_198'] = "<strong>Внимание:</strong> обнаружен скрипт установки, пожалуйста, удалите его немедленно.";
$locale['global_199'] = "<strong>Внимание:</strong> не установлен административный пароль, нажмите &laquo;<a href='".BASEDIR."edit_profile.php'>Изменить профиль</a>&raquo; для установки.";
//Titles
$locale['global_200'] = " - ";
$locale['global_201'] = ": ";
$locale['global_202'] = $locale['global_200']."Поиск";
$locale['global_203'] = $locale['global_200']."ЧаВо";
$locale['global_204'] = $locale['global_200']."Форум";
//Themes
$locale['global_210'] = "Пропустить";
$locale['global_300'] = "Тема не найдена";
$locale['global_301'] = "К сожалению, эта страница не может быть отображена. По каким-тоо причинам файлы темы оформления не были найдены. Если Вы администратор сайта, при помощи FTP-клиента загрузите тему оформелния, созданнуя для <em>PHP-Fusion v7</em>, в папку <em>themes/</em> на сайте. После загрузки проверьте в <em>Основных параметрах</em>, что тема оформления корректно загружена на сайт. Пожалуйста, убедитесь, что папка с темой имеет такое же название (включая регистр символов, что важно на серверах под управлением Unix-систем), как и выбранная в <em>Основных параметрах</em>.<br /><br />Если Вы обычный пользователь сайте, пожалуйста, свяжитесь с администратором через электропочту ".hide_email(fusion_get_settings('siteemail'))." и сообщите о случившемся.";
$locale['global_302'] = "Тема, выбранная в настройках, не существует или повреждена!";
// JavaScript Not Enabled
$locale['global_303'] = "О, нет! Где <strong>JavaScript</strong>?<br />Ваш бразуер не поддерживает JavaScript или же JavaScript отключен в настройках. Пожалуйста, <strong>включите JavaScript</strong> в браузере для корректного отображения сайта<br />или <strong>обновите</strong> свой браузер на поддерживающий JavaScript: <a href='http://firefox.com' rel='nofollow' title='Mozilla Firefox'>Firefox</a>, <a href='http://apple.com/safari/' rel='nofollow' title='Safari'>Safari</a>, <a href='http://opera.com' rel='nofollow' title='Opera Web Browser'>Opera</a>, <a href='http://www.google.com/chrome' rel='nofollow' title='Google Chrome'>Chrome</a> или же на <a href='http://www.microsoft.com/windows/internet-explorer/' rel='nofollow' title='Internet Explorer'>Internet Explorer</a> версии новее, чем 6.";
// User Management
$locale['global_400'] = "приостановлен";
$locale['global_401'] = "заблокирован";
$locale['global_402'] = "деактивирован";
$locale['global_403'] = "действие учётной записи прекращено";
$locale['global_404'] = "учётная запись анонимизирована";
$locale['global_405'] = "анонимный пользователь";
$locale['global_406'] = "Учётная запись была заблокирована по причинам безопасности:";
$locale['global_407'] = "действие учётной записи приостановлено до ";
$locale['global_408'] = " по следующей причине:";
$locale['global_409'] = "Эта учётная запись была заблокирована по причинам безопасности.";
$locale['global_410'] = "Причина для этого: ";
$locale['global_411'] = "Действие учётной записи было отменено.";
$locale['global_412'] = "Эта учетная запись была анонимизирована, вероятно, из-за бездействия.";
$locale['global_440'] = "Автоматическая блокировка антифлудом";
$locale['global_441'] = "Ваша учётная запись на сайте ".fusion_get_settings('sitename')." была заблокирована";
$locale['global_442'] = "Приветствую, [USER_NAME],\n
Ваша учётная запись на сайте ".fusion_get_settings('sitename')." была замечена в слишком частой публикации записей за короткий отрезок времени с IP-адреса ".USER_IP." и по этой причине была заблокирована. Это было сделано для защиты от массового размещения ботами спам-сообщений.\n
Свяжитесь с администратором по электропочте ".fusion_get_settings('siteemail')." для восстановления Вашей учётной записи или для информирования о том, что Вы не размещали сообщений.\n
".fusion_get_settings('siteusername');
// Lifting of suspension
$locale['global_450'] = "Приостановка автоматичсеки снята системой";
$locale['global_451'] = "Приостановка снята системой на сайте ".fusion_get_settings('sitename');
$locale['global_452'] = "Приветствую, USER_NAME,\n
Приостановка действия Вашей учётной записи на сайте ".fusion_get_settings('siteurl')." была снята. Информация для входа:\n
Имя пользователя: USER_NAME
Пароль: Скрыт по соображениям безопасности\n
Если Вы забыли свой пароль, его можно восстановить по этой ссылке: LOST_PASSWORD\n\n
С наилучшими пожеланиями,\n
".fusion_get_settings('siteusername');
$locale['global_453'] = "Приветствую, USER_NAME,\n
Приостановка действия вашей учётной записи на сайте ".fusion_get_settings('siteurl')." была снята.\n\n
С наилучшими пожеланиями,\n
".fusion_get_settings('siteusername');
$locale['global_454'] = "Учётная запись повторно активирована на сайте ".fusion_get_settings('sitename');
$locale['global_455'] = "Приветствую, USER_NAME,\n
При последнем Вашем входе на сайт ".fusion_get_settings('siteurl')." ваша учётная запись была повторно активирована и больше не помечена как неактивная.\n\n
С наилучшими пожеланиями,\n
".fusion_get_settings('siteusername');
// Function parsebytesize()
$locale['global_460'] = "Пусто";
$locale['global_461'] = "Байт";
$locale['global_462'] = "кБ";
$locale['global_463'] = "МБ";
$locale['global_464'] = "ГБ";
$locale['global_465'] = "ТБ";
//Safe Redirect
$locale['global_500'] = "Вы перенаправляетесь на %s, пожалуйста, обождите. Если Вас не перенаправило, нажмите здесь.";
// Captcha Locales
$locale['global_600'] = "Код проверки:";
$locale['recaptcha']  = "ru";
//Miscellaneous
$locale['global_900'] = "Невозможно преобразовать HEX в DEC";
//Language Selection
$locale['global_ML100'] = "Язык:";
$locale['global_ML101'] = "- Выберите язык -";
$locale['global_ML102'] = "Язык сайта";

$locale['flood'] = "Вам запрещено размещать сообщения до окончания времени действия антифлуда. Пожалуйста, подожите до %s";
$locale['no_image'] = "Нет изображения";
$locale['send_message'] = 'Отправить сообщение';
$locale['go_profile'] = 'Перейти к профилю %s';
// ex. oneword.locale.php
// Greetings
$locale['hello'] = "Приветствую!";
$locale['goodbye'] = "До свидания!";
$locale['welcome'] = "Добро пожаловать обратно";
$locale['home'] = 'Главная';
// Status
$locale['error'] = "Ошибка!";
$locale['success'] = "Успешно!";
$locale['enable'] = "Разрешить";
$locale['disable'] = "Запретить";
$locale['no'] = "Нет";
$locale['yes'] = "Да";
$locale['off'] = "Откл.";
$locale['on'] = "Вкл.";
$locale['or'] = "или";
$locale['by'] = "на";
$locale['in'] = "в";
$locale['of'] = "из";
$locale['and'] = "и";
$locale['na'] = "Информация недоступна";
$locale['joined'] = "Зарегистрирован с: ";

// Navigation
$locale['next'] = "След.";
$locale['pevious'] = "Пред.";
$locale['back'] = "Назад";
$locale['forward'] = "Вперёд";
$locale['go'] = "Перейти";
$locale['cancel'] = 'Отмена';
$locale['move_up'] = "Переместить вверх";
$locale['move_down'] = "Переместить вниз";
// Action
$locale['add'] = "Добавить";
$locale['save'] = "Сохранить";
$locale['save_changes'] = "Сохранить изменения";
$locale['confirm'] = "Подтвердить";
$locale['update'] = "Обновить";
$locale['updated'] = "Обновлено";
$locale['remove'] = "Убрать";
$locale['delete'] = "Удалить";
$locale['search'] = "Поиск";
$locale['help'] = "Справка";
$locale['register'] = "Регистрация";
$locale['ban'] = "Блокировка";
$locale['reactivate'] = "Реактивация";
$locale['user'] = "Пользователь";
$locale['promote'] = "Повысить";
$locale['show'] = 'Показать';
//Tables
$locale['status'] = "Статус";
$locale['order'] = "Порядок";
$locale['sort'] = "Сортировка";
$locale['id'] = "ID";
$locale['title'] = "Заголовок";
$locale['rights'] = "Права";
$locale['info'] = "Данные";
$locale['image'] = 'Изображение';
// Forms
$locale['choose'] = "Пожалуйста, выберите вариант...";
$locale['no_opts'] = 'Не выбрано';
$locale['root'] = 'Главный уровень';
$locale['choose-user'] = 'Пожалуйста, выберите пользователя...';
$locale['choose-location'] = 'Пожалуйста, выберите расположение';
$locale['parent'] = "Создать как нового предка...";
$locale['order'] = "Порядок элементов";
$locale['status'] = "Статус";
$locale['note'] = "Отметить этот элемент";
$locale['publish'] = "Опубликовано";
$locale['unpublish'] = "Не опубликовано";
$locale['draft'] = "Черновик";
$locale['settings'] = "Параметры";
$locale['posted'] = "размещено";
$locale['profile'] = "Профиль";
$locale['edit'] = "Изменить";
$locale['qedit'] = "Быстрая правка";
$locale['view'] = "Смотреть";
$locale['login'] = "Вход";
$locale['logout'] = "Выход";
$locale['admin-logout'] = "Выход из админпанели";
$locale['message'] = "Личные сообщения";
$locale['logged'] = "Вход как ";
$locale['version'] = "Версия ";
$locale['browse'] = "Обзор ...";
$locale['close'] = 'Закрыть';
$locale['nopreview'] = 'Нет данных для предпросмотра';
$locale['mark_as'] = "Отметить как";
//Alignment
$locale['left'] = "Влево";
$locale['center'] = "По центру";
$locale['right'] = "Вправо";
// Comments and ratings
$locale['comments'] = "Комментарии";
$locale['ratings'] = "Оценки";
$locale['comments_ratings'] = "Комментарии и оценки";
$locale['user_account'] = "Учётная запись пользователя";
$locale['about'] = "О ";
// User status
$locale['online'] = "В сети";
$locale['offline'] = "Не в сети";
// Words for formatting to single and plural forms. Count of forms is language-dependent
// Here must be three forms of word with | as separator:
// First form for 1, 21, 31, etc.
// Second form for 2-4, 22-24, 32-34, etc.
// third form for 0, 5-20, 25-30, 35-40, etc.
$locale['fmt_submission'] = "присланный материал|присланных материала|присланных материалов";
$locale['fmt_article'] = "статья|статьи|статей";
$locale['fmt_blog'] = "блог|блога|блогов";
$locale['fmt_comment'] = "комментарий|комментария|комментариев";
$locale['fmt_vote'] = "голос|голоса|голосов";
$locale['fmt_rating'] = "оценка|оценки|оценок";
$locale['fmt_day'] = "день|дня|дней";
$locale['fmt_download'] = "загрузка|загрузки|загрузок";
$locale['fmt_follower'] = "читатель|читателя|читателей";
$locale['fmt_forum'] = "форум|форума|форумов";
$locale['fmt_guest'] = "гость|гостя|гостей";
$locale['fmt_hour'] = "час|часа|часов";
$locale['fmt_item'] = "элемент|элемента|элементов";
$locale['fmt_member'] = "участник|участника|участников";
$locale['fmt_message'] = "сообщение|сообщения|сообщений";
$locale['fmt_minute'] = "минута|минуты|минут";
$locale['fmt_month'] = "месяц|месяца|месяцев";
$locale['fmt_news'] = "новость|новости|новостей";
$locale['fmt_photo'] = "фотография|фотографии|фотографий";
$locale['fmt_post'] = "сообщение|сообщения|сообщений";
$locale['fmt_question'] = "вопрос|вопроса|вопросов";
$locale['fmt_read'] = "прочтение|прочтения|прочтений";
$locale['fmt_second'] = "секунда|секунды|секунд";
$locale['fmt_shouts'] = "сообщение чата|сообщения чата|сообщений чата";
$locale['fmt_thread'] = "тема|темы|тем";
$locale['fmt_user'] = "пользователь|пользователя|пользователей";
$locale['fmt_views'] = "просмотр|просмотра|просмотров";
$locale['fmt_weblink'] = "ссылка|ссылки|ссылок";
$locale['fmt_week'] = "неделя|недели|недель";
$locale['fmt_year'] = "год|года|лет";

include __DIR__."/defender.php";
