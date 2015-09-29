<?php
/*
Ukrainian Language Fileset
Produced by Vyacheslav Buchatskiy (kot2007)
E-mail: koot2007@gmail.com
*/

// Locale Settings
setlocale(LC_TIME, 'uk_UA.utf8'); // Linux Server (Windows may differ)
$locale['charset'] = "utf-8";
$locale['xml_lang'] = "ua";
$locale['tinymce'] = "uk";
$locale['phpmailer'] = "ua";
$locale['datepicker'] = "ua";

// Full & Short Months
$locale['months'] = "&nbsp;|Січень|Лютий|Березень|Квітень|Травень|Червень|Липень|Серпень|Вересень|Жовтень|Листопад|Грудень";
$locale['shortmonths'] = "&nbsp|Січ|Лют|Бер|Кві|Тра|Чер|Лип|Сер|Вер|Жов|Лис|Гру";
$locale['weekdays'] = "Неділя|Понеділок|Вівторок|Середа|Четвер|П&lsquo;ятниця|Субота";

// Timers
$locale['year'] = "рік";
$locale['year_a'] = "роки(ів)";
$locale['month'] = "місяць";
$locale['month_a'] = "місяці(в)";
$locale['day'] = "день";
$locale['day_a'] = "дні(в)";
$locale['hour'] = "година";
$locale['hour_a'] = "годин(и)";
$locale['minute'] = "хвилина";
$locale['minute_a'] = "хвилин(и)";
$locale['second'] = "секунда";
$locale['second_a'] = "секунд(и)";
$locale['just_now'] = "щойно";
$locale['ago'] = "тому";

// Geo
$locale['street1'] = "Адреса 1";
$locale['street2'] = "Адреса 2";
$locale['city'] = "Місто";
$locale['postcode'] = "Поштовий індекс";
$locale['sel_country'] = "Країна";
$locale['sel_state'] = "Регіон";
$locale['sel_user'] = "Будь ласка, введіть ім&lsquo;я";
$locale['add_language'] = "Додати мови";
$locale['add_lang'] = "Додати %s";

// Name
$locale['name'] = "Повне ім&lsquo;я";
$locale['username_pretext'] = "Ваше загальнодоступне ім&lsquo;я користувача співпадає з адресою облікового запису в:<div class='alert alert-info m-t-10 p-10'>%s<strong>%s</strong></div>";
$locale['first_name'] = "Ім&lsquo;я";
$locale['middle_name'] = "По батькові";
$locale['last_name'] = "Прізвище";

// Documents
$locale['doc_type'] = "Тип документу";
$locale['doc_series'] = "Серія";
$locale['doc_number'] = "Номер";
$locale['doc_authority'] = "Видано (відповідальний орган)";
$locale['doc_date_issue'] = "Дата видачі";
$locale['doc_date_expire'] = "Дійсний до";

// Standard User Levels
$locale['user0'] = "Загальний";
$locale['user1'] = "Користувач";
$locale['user2'] = "Адміністратор";
$locale['user3'] = "Головний адміністратор";
$locale['user_na'] = "не визначено";
$locale['user_guest'] = "Гість";
$locale['user_anonymous'] = "Анонім";
$locale['genitive'] = "%s %s";

// Standard User Status
$locale['status0'] = "Активний";
$locale['status1'] = "Заблокований";
$locale['status2'] = "Неактивований";
$locale['status3'] = "Призупинений";
$locale['status4'] = "Заблокований з міркувань безпеки";
$locale['status5'] = "Скасований";
$locale['status6'] = "Анонімний";
$locale['status7'] = "Деактивований";
$locale['status8'] = "Неактивний";

// Forum Moderator Level(s)
$locale['userf1'] = "Модератор";

// Navigation
$locale['global_001'] = "Навігація";
$locale['global_002'] = "Посилання відсутні\n";

// Users Online
$locale['global_010'] = "Зараз на сайті";
$locale['global_011'] = "Гостей";
$locale['global_012'] = "Користувачів";
$locale['global_013'] = "Користувачі відсутні";
$locale['global_014'] = "Всього користувачів";
$locale['global_015'] = "Неактивних користувачів";
$locale['global_016'] = "Новий користувач";

// Forum Side panel
$locale['global_020'] = "Теми форуму";
$locale['global_021'] = "Нові теми";
$locale['global_022'] = "Обговорювані";
$locale['global_023'] = "Теми відсутні";

// Comments Side panel
$locale['global_025'] = "Останні коментарі";
$locale['global_026'] = "Коментарі відсутні";

// Articles Side panel
$locale['global_030'] = "Останні статті";
$locale['global_031'] = "Статті відсутні";

// Downloads Side panel
$locale['global_032'] = "Останні завантаження";
$locale['global_033'] = "Завантаження відсутні";

// Welcome panel
$locale['global_035'] = "Ласкаво просимо";

// Latest Active Forum Threads panel
$locale['global_040'] = "Останні активні теми на форумі";
$locale['global_041'] = "Мої теми";
$locale['global_042'] = "Мої повідомлення";
$locale['global_043'] = "Нові повідомлення";
$locale['global_044'] = "Теми";
$locale['global_045'] = "Переглядів";
$locale['global_046'] = "Відповідей";
$locale['global_047'] = "Останні повідомлення";
$locale['global_048'] = "Форум";
$locale['global_049'] = "Додано";
$locale['global_050'] = "Автор";
$locale['global_051'] = "Опитування";
$locale['global_052'] = "Переміщено";
$locale['global_053'] = "Ви не створювали тем на форумі";
$locale['global_054'] = "Ви не залишали повідомлень на форумі";
$locale['global_055'] = "З часу Вашого останнього візиту є %u нове(их) повідомлення(нь) в %u темі(ах).";
$locale['global_056'] = "Мої теми для стеження";
$locale['global_057'] = "Параметри";
$locale['global_058'] = "Скасувати";
$locale['global_059'] = "Ви не стежите за жодною з тем";
$locale['global_060'] = "Припинити стеження за цією темою?";

// Blog, News & Articles
$locale['global_070'] = "Опубліковано: ";
$locale['global_070b'] = "Переглянути всі записи від %s";
$locale['global_071'] = " ";
$locale['global_071b'] = "Автор";
$locale['global_072'] = "Читати далі...";
$locale['global_073'] = " коментарів";
$locale['global_073b'] = " коментар";
$locale['global_074'] = " переглядів";
$locale['global_074b'] = " перегляд";
$locale['global_075'] = "Друк";
$locale['print'] = "Друк";
$locale['global_076'] = "Редагувати";
$locale['global_077'] = "Новини";
$locale['global_078'] = "Новини відсутні";
$locale['global_079'] = "Розділ: ";
$locale['global_080'] = "Несортовані";
$locale['global_081'] = "На початок";
$locale['global_082'] = "Новини";
$locale['global_083'] = "Останнє оновлення";
$locale['global_084'] = "Розділ новин";
$locale['global_085'] = "Всі розділи";
$locale['global_086'] = "Останні новини";
$locale['global_087'] = "Найбільш коментовані новини";
$locale['global_088'] = "Новини з найвищою оцінкою";
$locale['global_089'] = "Прокоментуйте першим %s !";
$locale['global_089a'] = "Оцініть першим %s !";
$locale['global_089b'] = "Мініатюри";
$locale['global_089c'] = "Список";

// Page Navigation
$locale['global_090'] = "&laquo;Попередня";
$locale['global_091'] = "Наступна&raquo;";
$locale['global_092'] = "Сторінка ";
$locale['global_093'] = " з ";
$locale['global_094'] = " з ";

// Guest User Menu
$locale['global_100'] = "Авторизація";
$locale['global_101'] = "Ім&lsquo;я";
$locale['global_101a'] = "Ім&lsquo;я";
$locale['global_101b'] = "Електронна адреса";
$locale['global_101c'] = "Ім&lsquo;я або електронна адреса";
$locale['global_102'] = "Пароль";
$locale['global_103'] = "зберігати";
$locale['global_104'] = "Вхід";
$locale['global_105'] = "Ще не зареєстровані? <br /><a href='".BASEDIR."register.php' class='side'>Зареєструватися</a>";
$locale['global_106'] = "Не пам&lsquo;ятаєте пароль? <br /><a href='".BASEDIR."lostpassword.php' class='side'>Відновити</a>";
$locale['global_107'] = "Реєстрація на сайті";
$locale['global_108'] = "Відновлення паролю";

// Member User Menu
$locale['global_120'] = "Профіль";
$locale['global_121'] = "Приватні повідомлення";
$locale['global_122'] = "Користувачі";
$locale['global_123'] = "Центр керування";
$locale['global_124'] = "Вихід";
$locale['global_125'] = "У Вас %u ";
$locale['global_126'] = "повідомлення";
$locale['global_127'] = "повідомлень";
$locale['global_128'] = "ухвалення";
$locale['global_129'] = "ухвалень";

// User Menu
$locale['global_123'] = "Центр керування";
$locale['UM060'] = "Авторизація";
$locale['UM061'] = "Ім&lsquo;я";
$locale['UM061a'] = "Електронна адреса";
$locale['UM061b'] = "Ім&lsquo;я або електронна адреса";
$locale['UM062'] = "Пароль";
$locale['UM063'] = "Зберігати";
$locale['UM064'] = "Авторизація";
$locale['UM065'] = "Ще не зареєстровані?<br /><a href='".BASEDIR."register.php' class='side'>Зареєструватися</a>.";
$locale['UM066'] = "Не пам&lsquo;ятаєте пароль?<br /><a href='".BASEDIR."lostpassword.php' class='side'>Відновити</a>.";
$locale['UM080'] = "Профіль";
$locale['UM081'] = "Приватні повідомлення";
$locale['UM082'] = "Користувачі";
$locale['UM083'] = "Центр керування";
$locale['UM084'] = "Вихід";
$locale['UM085'] = "У Вас %u ";
$locale['UM086'] = "повідомлення";
$locale['UM087'] = "повідомлень";
$locale['UM088'] = "Відстежувані теми";

// Submit (news, link, article)
$locale['UM089'] = "Запропонувати...";
$locale['UM090'] = "Запропонувати новину";
$locale['UM091'] = "Запропонувати посилання";
$locale['UM092'] = "Запропонувати статтю";
$locale['UM093'] = "Запропонувати світлину";
$locale['UM094'] = "Запропонувати завантаження";
$locale['UM095'] = "Запропонувати допис в блог";

// User Panel
$locale['UM096'] = "Вітаємо: ";
$locale['UM097'] = "Особисте меню";
$locale['UM101'] = "Зміна мови";

// Gauges
$locale['UM098'] = "Вхідні:";
$locale['UM099'] = "Вихідні:";
$locale['UM100'] = "Архів:";

// Keywords and Meta
$locale['tags'] = "Теґи";

// Captcha
$locale['global_150'] = "Код підтвердження:";
$locale['global_151'] = "Введіть код підтвердження:";

// Footer Counter
$locale['global_170'] = "відвідувач";
$locale['global_171'] = "відвідувачів";
$locale['global_172'] = "Завантажено за %s сек.";
$locale['global_173'] = "Запити";
$locale['global_174'] = "Використано пам&lsquo;яті";
$locale['global_175'] = "Середнє: %s сек.";

// Admin Navigation
$locale['global_180'] = "Центр керування";
$locale['global_181'] = "Повернутися на сайт";
$locale['global_182'] = "<strong>Зауваження:</strong> Пароль адміністрування введено некоректно";

// Miscellaneous
$locale['global_190'] = "Ввімкнено режим обслуговування";
$locale['global_191'] = "Вашу IP адресу заблоковано";
$locale['global_192'] = "Термін Вашої сесії закінчився. Будь ласка, авторизуйтесь знову, щоб продовжити.";
$locale['global_193'] = "Не вдалося встановити коржик (cookie). Будь ласка, переконайтеся, що у переглядачі ввімкнено дозвіл на встановлення коржиків (cookie) , щоб мати можливість увійти належним чином.";
$locale['global_194'] = "Дію цього облікового запису на даний час призупинено";
$locale['global_195'] = "Цей обліковий запис ще не активовано";
$locale['global_196'] = "Вказані ім&lsquo;я та\або пароль некоректні";
$locale['global_197'] = "Будь ласка, зачекайте ...<br />Відбувається перевірка авторизації Вашого облікового запису.<br /><br />
[ <u><b><a href='index.php'>можете натиснути тут, якщо не бажаєте більше чекати</a></b></u> ]";
$locale['global_198'] = "<strong>УВАГА:</strong> ВИЯВЛЕНО МОДУЛЬ ВСТАНОВЛЕННЯ. БУДЬ ЛАСКА, НЕГАЙНО ВИДАЛІТЬ КАТАЛОГ /INSTALL/.";
$locale['global_199'] = "<strong>Увага:</strong> Не введено пароль адміністрування, натисніть <u><b><a href='".BASEDIR."edit_profile.php'>Редагувати обліковий запис</a></b></u> і введіть його";

// Titles
$locale['global_200'] = " - ";
$locale['global_201'] = ": ";
$locale['global_202'] = $locale['global_200']."Пошук";
$locale['global_203'] = $locale['global_200']."ЧАП";
$locale['global_204'] = $locale['global_200']."Форум";

// Themes
$locale['global_210'] = "Перейти до змісту";
$locale['global_300'] = "Тему сайту не визначено";
$locale['global_301'] = "Вибачте, неможливо відобразити сторінку. Через певні обставини, неможливо знайти жодну тему оформлення сайту. Якщо Ви адміністратор сайту, використайте менеджер FTP для завантаження теми оформлення, яка сумісна з <em>PHP-Fusion v7</em> в каталог <em>themes/</em>. Після завантаження теми, перевірте у розділі <em>Налаштування - Головне</em> Центру керування, що завантажена тема в каталог <em>themes/</em> активована. Майте на увазі, що завантажена тема повинна мати ту ж назву (враховуючи регістр символів; важливо для Unix-серверів), що й вибрана тема в розділі <em>Налаштування - Головне</em> Центру керування.<br /><br />Якщо Ви користувач, будь ласка, зв&lsquo;яжіться з адміністратором сайту за електронною адресою: ".hide_email($settings['siteemail'])." та повідомте про цю проблему.";
$locale['global_302'] = "Вибрану тему оформлення в розділі <em>Налаштування - Головне</em>, не вдається знайти або її пошкоджено!";

// JavaScript Not Enabled
$locale['global_303'] = "Ой, сталась помилка! А де ж <strong>JavaScript</strong>?<br />Схоже, що Ваш переглядач не підтримує технологію JavaScript або її вимкнено. Будь ласка, <strong>увімкніть JavaScript</strong> для коректного відображення цього сайту,<br /> або <strong>використайте іншого переглядача інтернет сторінок</strong>, який має підтримку JavaScript; <a href='http://firefox.com' rel='nofollow' title='Mozilla Firefox'>Firefox</a>, <a href='http://apple.com/safari/' rel='nofollow' title='Safari'>Safari</a>, <a href='http://opera.com' rel='nofollow' title='Opera Web Browser'>Opera</a>, <a href='http://www.google.com/chrome' rel='nofollow' title='Google Chrome'>Chrome</a> або версію <a href='http://www.microsoft.com/windows/internet-explorer/' rel='nofollow' title='Internet Explorer'>Internet Explorer</a> не нижче 7.";

// User Management
$locale['global_400'] = "призупинено";
$locale['global_401'] = "заблоковано (бан)";
$locale['global_402'] = "деактивовано";
$locale['global_403'] = "обліковий запис знищено";
$locale['global_404'] = "обліковий запис анонімний";
$locale['global_405'] = "анонімний користувач";
$locale['global_406'] = "Цей обліковий запис заблоковано (бан) за такою підставою:";
$locale['global_407'] = "Цей обліковий запис призупинено до ";
$locale['global_408'] = " за такими підставами:";
$locale['global_409'] = "Цей обліковий запис заблоковано (бан) з міркувань безпеки.";
$locale['global_410'] = "Причина: ";
$locale['global_411'] = "Цей обліковий запис скасовано.";
$locale['global_412'] = "Цей обліковий запис зроблено анонімним, можливо, через бездіяльність.";
$locale['global_440'] = "Автоматичний бан через порушення контролю за флудом";
$locale['global_441'] = "Ваш обліковий запис заблоковано";
$locale['global_442'] = "Вітаємо, [USER_NAME],\n
З вашого облікового запису на сайті <<".fusion_get_settings('sitename').">> зафіксовано надсилання надто великої кількості повідомлень за короткий проміжок часу через адресу IP ".USER_IP.", що призвело до його блокування. Це було зроблено з метою попередження поширення спаму через бот-системи.\n
Будь ласка, зв&lsquo;яжіться із адміністрацією сайту за електронною адресою ".fusion_get_settings('siteemail')." , щоб розблокувати свій обліковий запис або повідомити про свою непричетність до зафіксованих потенційно шкідливих дій.\n
".fusion_get_settings('siteusername');
$locale['global_450'] = "Призупинення автоматично знято системою";
$locale['global_451'] = "Знято призупинення облікового запису";
$locale['global_452'] = "Вітаємо, USER_NAME,\n
Призупинення дії Вашого облікового запису ".fusion_get_settings('siteurl')." знято. Нагадуємо Ваші параметри авторизації на сайті:\n
Ім&lsquo;я: USER_NAME
Пароль: не показано з міркувань безпеки\n
Якщо Ви забули свій пароль, можете надіслати запит через це посилання: LOST_PASSWORD\n\n
З повагою,\n
".fusion_get_settings('siteusername');
$locale['global_453'] = "Вітаємо, USER_NAME,\n
Призупинення дії Вашого облікового запису на ".fusion_get_settings('siteurl')." знято.\n\n
З повагою,\n
".fusion_get_settings('siteusername');
$locale['global_454'] = "Відновлено обліковий запис";
$locale['global_455'] = "Вітаємо, USER_NAME,\n
Під час Вашого останнього візиту на ".fusion_get_settings('siteurl')." Ваш обліковий запис було відновлено і з нього знято статус неактивного.\n\n
З повагою,\n
".fusion_get_settings('siteusername');

// Function parsebytesize()
$locale['global_460'] = " - ";
$locale['global_461'] = " Байт";
$locale['global_462'] = " Кб";
$locale['global_463'] = " Мб";
$locale['global_464'] = " Гб";
$locale['global_465'] = " Тб";

// Safe Redirect
$locale['global_500'] = "Ваш запит перенаправлено на %s, будь ласка, зачекайте. Якщо перенаправлення не відбулось, натисніть тут";

// Captcha Locales
$locale['global_600'] = "Код підтвердження";
$locale['recaptcha'] = "en";

// Miscellaneous
$locale['global_900'] = "Неможливо конвертувати HEX в DEC";

// Language Selection
$locale['global_ML100'] = "Мова:";
$locale['global_ML101'] = "- виберіть мову -";
$locale['global_ML102'] = "Мова сайту";

// Flood Control
$locale['flood'] = "Нові публікації заблоковані до завершення встановленого часу контролю за флудом. Будь ласка, зачекайте %s";
$locale['no_image'] = "Зображення відсутнє";
$locale['send_message'] = "Надіслати повідомлення";
$locale['go_profile'] = "Перейти у профіль %s";

// Global one word locales
$locale['hello'] = "Вітаємо!";
$locale['goodbye'] = "На все добре!";
$locale['welcome'] = "З поверненням";
$locale['home'] = "Головна";

// Status
$locale['error'] = "Помилка!";
$locale['success'] = "Успіх!";
$locale['enable'] = "Дозволити";
$locale['disable'] = "Заборонити";
$locale['no'] = "Ні";
$locale['yes'] = "Так";
$locale['off'] = "Вимкнути";
$locale['on'] = "Увімкнути";
$locale['can'] = 'can';
$locale['cannot'] = 'cannot';
$locale['or'] = "або";
$locale['by'] = "за";
$locale['in'] = "в";
$locale['of'] = "з";
$locale['and'] = "та";
$locale['na'] = "не доступно";
$locale['joined'] = "Приєднання: ";

// Navigation
$locale['next'] = "Наступний";
$locale['previous'] = "Попередній";
$locale['back'] = "Назад";
$locale['forward'] = "Далі";
$locale['go'] = "Перейти";
$locale['cancel'] = "Скасувати";
$locale['move_up'] = "Вище";
$locale['move_down'] = "Нижче";
$locale['load_more'] = "Показати більше";
$locale['load_end'] = "Показати все";

// Actions
$locale['add'] = "Додати";
$locale['save'] = "Зберегти";
$locale['save_changes'] = "Зберегти зміни";
$locale['confirm'] = "Ухвалити";
$locale['update'] = "Оновити";
$locale['updated'] = "Оновлено";
$locale['remove'] = "Видалити";
$locale['delete'] = "Видалити";
$locale['search'] = "Знайти";
$locale['help'] = "Допомога";
$locale['register'] = "Реєстрація";
$locale['ban'] = "Бан";
$locale['reactivate'] = "Відновлення";
$locale['user'] = "Користувач";
$locale['promote'] = "Поширення";
$locale['show'] = "Показати";

// Tables
$locale['status'] = "Стан";
$locale['order'] = "Порядок";
$locale['sort'] = "Сортування";
$locale['id'] = "ID";
$locale['title'] = "Назва";
$locale['rights'] = "Права";
$locale['image'] = "Зображення";
$locale['info'] = "Додатково";

// Forms
$locale['choose'] = "Будь ласка, виберіть...";
$locale['no_opts'] = "не вибрано";
$locale['root'] = "як кореневий";
$locale['choose-user'] = "Будь ласка, виберіть користувача...";
$locale['choose-location'] = "Будь ласка, вкажіть розташування";
$locale['parent'] = "Створити як кореневий..";
$locale['order'] = "Порядок розташування";
$locale['status'] = "Стан";
$locale['note'] = "Позначити";
$locale['publish'] = "Опубліковано";
$locale['unpublish'] = "Не опубліковано";
$locale['draft'] = "Чернетка";
$locale['settings'] = "Налаштування";
$locale['posted'] = "опубліковано";
$locale['in'] = "в";
$locale['profile'] = "Профіль";
$locale['edit'] = "Редагування";
$locale['qedit'] = "Швидке редагування";
$locale['view'] = "Перегляд";
$locale['login'] = "Вхід";
$locale['logout'] = "Вихід";
$locale['admin-logout'] = "Вихід адміністратора";
$locale['message'] = "Приватні повідомлення";
$locale['logged'] = "Авторизовано як ";
$locale['version'] = "Версія ";
$locale['browse'] = "Перегляд ...";
$locale['close'] = "Закрити";
$locale['nopreview'] = "Дані для перегляду відсутні";
$locale['mark_as'] = "Позначити як";

// Alignment
$locale['left'] = "Зліва";
$locale['center'] = "Центр";
$locale['right'] = "Справа";

// User status
$locale['online'] = "В мережі";
$locale['offline'] = "Не в мережі";

// Comments and ratings
$locale['comments'] = "Коментарі";
$locale['ratings'] = "Оцінки";
$locale['comments_ratings'] = "Коментарі та оцінки";
$locale['user_account'] = "Обліковий запис";
$locale['about'] = "Опис";

// Words for formatting to single and plural forms. Count of forms is language-dependent
$locale['fmt_submission'] = "ухвалення|ухвалення|ухвалень";
$locale['fmt_article'] = "стаття|статті|статей";
$locale['fmt_blog'] = "блог|блоги|блогу";
$locale['fmt_comment'] = "коментар|коментаря|коментарів";
$locale['fmt_vote'] = "голос|голоса|голосів";
$locale['fmt_rating'] = "оцінка|оцінки|оцінок";
$locale['fmt_day'] = "день|дня|днів";
$locale['fmt_download'] = "завантаження|завантаження|завантажень";
$locale['fmt_follower'] = "послідовник|послідовника|послідовників";
$locale['fmt_forum'] = "форум|форуму|форумів";
$locale['fmt_guest'] = "гість|гостя|гостей";
$locale['fmt_hour'] = "година|години|годин";
$locale['fmt_item'] = "елемент|елементи|елементів";
$locale['fmt_member'] = "користувач|користувача|користувачів";
$locale['fmt_message'] = "повідомлення|повідомлення|повідомлень";
$locale['fmt_minute'] = "хвилина|хвилини|хвилин";
$locale['fmt_month'] = "місяць|місяця|місяців";
$locale['fmt_news'] = "новина|новини|новин";
$locale['fmt_photo'] = "світлина|світлини|світлин";
$locale['fmt_post'] = "повідомлення|повідомлення|повідомлень";
$locale['fmt_question'] = "запитання|запитання|запитань";
$locale['fmt_read'] = "прочитання|прочитання|прочитань";
$locale['fmt_second'] = "секунда|секунди|секунд";
$locale['fmt_shouts'] = "повідомлення|повідомлення|повідомлень";
$locale['fmt_thread'] = "тема|теми|тем";
$locale['fmt_user'] = "користувач|користувача|користувачів";
$locale['fmt_views'] = "перегляд|перегляди|переглядів";
$locale['fmt_weblink'] = "посилання|посилання|посилань";
$locale['fmt_week'] = "тиждень|тижня|тижнів";
$locale['fmt_year'] = "рік|роки|років";

// include Defender locales
include __DIR__."/defender.php";
