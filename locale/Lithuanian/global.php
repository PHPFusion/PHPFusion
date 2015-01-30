<?php
/*
Lithuanian Language Fileset
Ready for: PHP-Fusion V9.00
Language version: v1.0
Vertė:
	Edmundas Jankauskas (Creatium)[V7.01, V7.02, V7.02.01, v7.02.02, v9]
	Gintautas Brainys (ozzWANTED) [V7.00, V7.01, V7.02]
	Algimantas Virbašius (MaFetas)[V7.02]
	Gediminas Bruzgis (chipass)	  [V7.00, V7.01]
	Gediminas Survila (Fanio)	  [V7.00]
	Džiugas Šidlauskas (Enzo)	  [V7.00]
	Simonas Švėgžda (Sineik)	  [V7.00]
	Edvinas Vyšniauskas (mXt)	  [V7.00]

	Adresas: http://www.phpfusion-lt.com
*/

// Locale Settings
setlocale(LC_TIME, "lt","LT"); // Linux Server (Windows may differ)
$locale['charset'] = "UTF-8";

$locale['xml_lang'] = "lt";
$locale['tinymce'] = "lt";
$locale['phpmailer'] = "lt";

// Full & Short Months
$locale['months'] = "&nbsp|Sausis|Vasaris|Kovas|Balandis|Gegužė|Birželis|Liepa|Rugpjūtis|Rugsėjis|Spalis|Lapkritis|Gruodis";
$locale['shortmonths'] = "&nbsp|Sau|Vas|Kov|Bal|Geg|Bir|Lie|Rug|Rgs|Spa|Lap|Gru";


// Timers
$locale['year'] = "metai";
$locale['year_a'] = "metai";
$locale['month'] = "mėnesis";
$locale['month_a'] = "mėnesiai";
$locale['day'] = "diena";
$locale['day_a'] = "dienos";
$locale['hour'] = "valanda";
$locale['hour_a'] = "valandos";
$locale['minute'] = "minutė";
$locale['minute_a'] = "minutės";
$locale['second'] = "sekundė";
$locale['second_a'] = "sekundės";
$locale['just_now'] = "tik ką";
$locale['ago'] = "prieš";
// Geo
$locale['street1'] = "Gatvės adresas 1";
$locale['street2'] = "Gatvės adresas 2";
$locale['city'] = "Miestas";
$locale['postcode'] = "Pašto kodas";
$locale['sel_country'] = "Pasirinkti šalį";
$locale['sel_state'] = "Pasirinkti valstiją";
// Standard User Levels
$locale['user0'] = "Svečias";
$locale['user1'] = "Narys";
$locale['user2'] = "Administratorius";
$locale['user3'] = "Vyr. administratorius";
$locale['user_na'] = "Nėra";
$locale['user_anonymous'] = "Anonimas";
// Standard User Status
$locale['status0'] = "Aktyvūs";
$locale['status1'] = "Uždrausti";
$locale['status2'] = "Neaktyvuoti";
$locale['status3'] = "Suspenduoti";
$locale['status4'] = "Uždrausti apsaugos";
$locale['status5'] = "Atšaukti";
$locale['status6'] = "Anonimai";
$locale['status7'] = "DE-Aktyvuoti";
$locale['status8'] = "Neaktyvūs";
// Forum Moderator Level(s)
$locale['userf1'] = "Moderatorius";
// Navigation
$locale['global_001'] = "Navigacija";
$locale['global_002'] = "Nuorodų nėra";
// Users Online
$locale['global_010'] = "Prisijungę vartotojai";
$locale['global_011'] = "Prisijungę svečiai";
$locale['global_012'] = "Prisijungę nariai";
$locale['global_013'] = "Prisijungusių narių nėra";
$locale['global_014'] = "Iš viso narių";
$locale['global_015'] = "Nektyvuotų narių";
$locale['global_016'] = "Naujausias narys";
// Forum Side panel
$locale['global_020'] = "Forumo temos";
$locale['global_021'] = "Naujausios temos";
$locale['global_022'] = "Populiariausios temos";
$locale['global_023'] = "Temų nėra";
// Comments Side panel
$locale['global_025'] = "Naujausi komentarai";
$locale['global_026'] = "Komentarų nėra";
// Articles Side panel
$locale['global_030'] = "Naujausi straipsniai";
$locale['global_031'] = "Straipsnių nėra";
// Downloads Side panel
$locale['global_032'] = "Naujausi siuntiniai";
$locale['global_033'] = "Siuntinių nėra";
// Welcome panel
$locale['global_035'] = "Sveiki";
// Latest Active Forum Threads panel
$locale['global_040'] = "Paskutinės aktyvios forumo temos";
$locale['global_041'] = "Mano naujausios temos";
$locale['global_042'] = "Mano naujausi pranešimai";
$locale['global_043'] = "Nauji pranešimai";
$locale['global_044'] = "Tema";
$locale['global_045'] = "Žiūrėjo";
$locale['global_046'] = "Ats.";
$locale['global_047'] = "Naujausia žinutė";
$locale['global_048'] = "Forumas";
$locale['global_049'] = "Parašyta";
$locale['global_050'] = "Autorius";
$locale['global_051'] = "Apklausa";
$locale['global_052'] = "Perkeltas";
$locale['global_053'] = "Jūs nesukūrėte nei vienos forumo temos.";
$locale['global_054'] = "Jūs neparašėte nei vienos žinutės.";
$locale['global_055'] = "%u naujos žinutės nuo jūsų paskutinio apsilankymo.";
$locale['global_056'] = "Mano sekamos temos";
$locale['global_057'] = "Pasirinkimai";
$locale['global_058'] = "Sustoti";
$locale['global_059'] = "Jūs nesekate jokių temų.";
$locale['global_060'] = "Nustoti sekti šią temą?";
// News & Articles
$locale['global_070'] = "Parašė ";
$locale['global_071'] = "&middot; ";
$locale['global_072'] = "Toliau...";
$locale['global_073'] = " komentarai";
$locale['global_073b'] = " komentaras";
$locale['global_074'] = " perskaitymai";
$locale['global_074b'] = " perskaitymas";
$locale['global_075'] = "Spausdinti";
$locale['global_076'] = "Redaguoti";
$locale['global_077'] = "Naujienos";
$locale['global_078'] = "Naujienų kol kas nėra";
$locale['global_077b'] = "Tinklaraštis";
$locale['global_078b'] = "Tinklaraščių kol kas nėra";
$locale['global_079'] = " ";
$locale['global_080'] = "Nekategorizuota";
$locale['global_081'] = "Naujienų pradinis";
$locale['global_082'] = "Naujienų centras";
$locale['global_081b'] = "Tinklaraščių pradinis";
$locale['global_082b'] = "Tinklaraščių centras";
$locale['global_082c'] = "Tinklaraščių archyvo panelė";
$locale['global_083'] = "Paskutinis atnujinimas";
$locale['global_084'] = "Naujienų kategorija";
$locale['global_084b'] = "Tinklaraščių kategorija";
$locale['global_085'] = "Visos kitos kategorijos";
$locale['global_086'] = "Paskutinės naujienos";
$locale['global_087'] = "Daugiausiai komentuojamos naujienos";
$locale['global_088'] = "Didžiausių reitingų naujienos";
$locale['global_086b'] = "Paskutiniai tinklaraščiai";
$locale['global_087b'] = "Daugiausiai komentuojami tinklaraščiai";
$locale['global_088b'] = "Didžiausių reitingų tinklaraščiai";
$locale['global_089'] = "Būkite pirmi, pakomentavę %s";
$locale['global_089a'] = "Būkite pirmi, įvertinę %s";
// Page Navigation
$locale['global_090'] = "Buvęs";
$locale['global_091'] = "Kitas";
$locale['global_092'] = "Puslapis ";
$locale['global_093'] = " iš ";
$locale['global_094'] = " iš ";
// Guest User Menu
$locale['global_100'] = "Prisijungti";
$locale['global_101'] = "Prisijungimo ID";
$locale['global_101a'] = "Prašom įvesti savo prisijungimo ID";
$locale['global_102'] = "Slaptažodis";
$locale['global_103'] = "Atsiminti";
$locale['global_104'] = "Jungtis!";
$locale['global_105'] = "Dar ne narys?<br /><a href='".BASEDIR."register.php' class='side'>Registruotis</a>.";
$locale['global_106'] = "Pamiršai slaptažodį?<br /><a href='".BASEDIR."lostpassword.php' class='side'>Prašyk naujo!</a>";
$locale['global_107'] = "Registruotis";
$locale['global_108'] = "Pamiršau slaptažodį";
// Member User Menu
$locale['global_120'] = "Redaguoti profilį";
$locale['global_121'] = "Asmeninės žinutės";
$locale['global_122'] = "Narių sąrašas";
$locale['global_123'] = "Administracijos panelė";
$locale['global_124'] = "Atsijungti";
$locale['global_125'] = "Tu turi %u ";
$locale['global_126'] = "naują žinutę";
$locale['global_127'] = "naujas žinutes";
$locale['global_128'] = "pateikimas";
$locale['global_129'] = "pateikimai";
// User Menu
$locale['global_123'] = "Admin panelė";
$locale['UM060'] = "Prisijungimas";
$locale['UM061'] = "Vartotojo vardas";
$locale['UM061a'] = "El. paštas";
$locale['UM061b'] = "Vartotojo vardas arba el. paštas";
$locale['UM062'] = "Slaptažodis";
$locale['UM063'] = "Įsiminti mane";
$locale['UM064'] = "Prisijungti";
$locale['UM065'] = "Dar ne narys?<br /><a href='".BASEDIR."register.php' class='side'>Prisiregistruok</a>.";
$locale['UM066'] = "Pamiršai slaptažodį?<br /><a href='".BASEDIR."lostpassword.php' class='side'>Prašyk naujo!</a>";
$locale['UM080'] = "Keisti profilį";
$locale['UM081'] = "Asmeninės žinutės";
$locale['UM082'] = "Narių sąrašas";
$locale['UM083'] = "Administracijos panelė";
$locale['UM084'] = "Atsijungti";
$locale['UM085'] = "Jūs turite %u naują(-ų) ";
$locale['UM086'] = "žinutę";
$locale['UM087'] = "žinučių";
$locale['UM088'] = "Sekamos temos";
// Submit (news, link, article)
$locale['UM089'] = "Pateikti...";
$locale['UM090'] = "Pateikti naujieną";
$locale['UM091'] = "Pateikti nuorodą";
$locale['UM092'] = "Pateikti straipsnį";
$locale['UM093'] = "Pateikti nuotrauką";
$locale['UM094'] = "Pateikti siuntinį";
$locale['UM095'] = "Pateikti tinklaraštį";
// User Panel
$locale['UM096'] = "Sveiki: ";
$locale['UM097'] = "Asmeninis meniu";
$locale['UM101'] = "Keisti kalbą";
// Gauges
$locale['UM098'] = "Gautos žinutės:";
$locale['UM099'] = "Išsiųstos žinutės:";
$locale['UM100'] = "Žinučių archyvas:";
// Poll
$locale['global_130'] = "Narių apklausa";
$locale['global_131'] = "Balsuoti";
$locale['global_132'] = "Kad galėtum balsuoti, turi prisijungti.";
$locale['global_133'] = "Balsas";
$locale['global_134'] = "Balsai";
$locale['global_135'] = "Balsai: ";
$locale['global_136'] = "Pradėta: ";
$locale['global_137'] = "Baigta: ";
$locale['global_138'] = "Apklausų archyvas";
$locale['global_139'] = "Pasirinkite apklausą peržiūrai iš sąrašo:";
$locale['global_140'] = "Žiūrėti";
$locale['global_141'] = "Žiūrėti apklausą";
$locale['global_142'] = "Nėra apklausų.";

$locale['global_143'] = "Reitingai";
// Captcha
$locale['global_150'] = "Patvirtinimo kodas:";
$locale['global_151'] = "Įrašykite patvirtinimo kodą:";

// Footer Counter
$locale['global_170'] = "unikalus lankytojas";
$locale['global_171'] = "unikalūs lankytojai";
$locale['global_172'] = "Užkrauta per %s sekundes";
$locale['global_173'] = "Užklausos";
// Admin Navigation
$locale['global_180'] = "Administracija";
$locale['global_181'] = "Grįžti į tinklapį";
$locale['global_182'] = "<strong>Pastaba:</strong> Neįvestas arba neteisingas administratoriaus slaptažodis!";
// Miscellaneous
$locale['global_190'] = "Tinklapis išjungtas";
$locale['global_191'] = "Tavo IP adresas (".USER_IP.") yra užblokuotas.";
$locale['global_192'] = "Iki, ";
$locale['global_193'] = "Sveikas(-a), ";
$locale['global_194'] = "Šis vartotojas užblokuotas.";
$locale['global_195'] = "Šis vartotojas neaktyvuotas.";
$locale['global_196'] = "Neteisingas vartotojo vardas arba slaptažodis.";
$locale['global_197'] = "Prašome palaukti...<br /><br />
[ <a href='index.php'>Arba spauskite čia, jei nenorite laukti</a> ]";
$locale['global_198'] = "<strong>Dėmesio:</strong> rastas setup.php. Nedelsiant jį ištrinkite!";
$locale['global_199'] = "<strong>Dėmesio:</strong> administratoriaus slaptažodis nenustatytas, eikite į <a href='".BASEDIR."edit_profile.php'>Profilio Redagavimą</a>, kad jį nustatyti.";
//Titles
$locale['global_200'] = " - ";
$locale['global_201'] = ": ";
$locale['global_202'] = $locale['global_200']."Paieška";
$locale['global_203'] = $locale['global_200']."D.U.K";
$locale['global_204'] = $locale['global_200']."Forumas";
//Themes
$locale['global_210'] = "Peršokti prie turinio";
// No themes found
$locale['global_300'] = "dizainų nerasta";
$locale['global_301'] = "Atsiprašome, bet negalime atvaizduoti šio puslapio. Dėl kažkokių aplinkybių nebuvo rastas joks portalo dizainas. Jeigu Jūs esate puslapio administratorius, naudodamiesi savo FTP klientu įkelkite kokį nors dizainą skirtą <em>PHP-Fusion v7</em> sistemai į <em>themes/</em> FTP katalogą. Po įkėlimo <em>Pagrindiniuose nustatymuose</em> patikrinkite ar pasirinktas dizainas buvo teisingai įkeltas į Jūsų <em>themes/</em> FTP katalogą. Nepamirškite, kad įkelto dizaino katalogas turi turėti tokį patį pavadinimą(įskaitant ir didžiąsias/mažąsias raides, kas yra svarbu Unix pagrindu paremtiems serveriams) kaip ir esate pasirinke <em>Pagrindinių nustatymų</em> puslapyje.<br /><br />Jeigu esate paprastas šio portalo lankytojas, prašome susisiekti su portalo administratoriumi el. paštu: ".hide_email(fusion_get_settings('siteemail'))." ir pranešti apie šią problemą.";
$locale['global_302'] = "Dizainas, pasirinktas &#39;pagrindiniuose nustatymuose&#39; neegzistuoja arba yra nebaigtas!";
// JavaScript Not Enabled
$locale['global_303'] = "Oj! Kur dingo <strong>JavaScript</strong>?<br />Jūsų naršyklėje yra išjungtas JavaScript palaikymas, arba jo išvis nėra. Prašome <strong>įjungti JavaScript</strong> savo naršyklėje, jeigu norite matyti šį puslapį tvarkingai,<br /> arba <strong>atnaujinkite</strong> savo naršyklę į tokią, kuri palaiko JavaScript; < href='http://firefox.com' rel='nofollow' title='Mozilla Firefox'>Firefox</a>, <a href='http://apple.com/safari/' rel='nofollow' title='Safari'>Safari</a>, <a href='http://opera.com' rel='nofollow' title='Opera Web Browser'>Opera</a>, <a href='http://www.google.com/chrome' rel='nofollow' title='Google Chrome'>Chrome</a> arba į naujesnę <a href='http://www.microsoft.com/windows/internet-explorer/' rel='nofollow' title='Internet Explorer'>Internet Explorer</a> naršyklę, negu versija 6.";
// User Management
// Member status
$locale['global_400'] = "suspenduotas";
$locale['global_401'] = "blokuotas";
$locale['global_402'] = "deaktyvuotas";
$locale['global_403'] = "anketa sustabdyta";
$locale['global_404'] = "anketa padaryta anonimine";
$locale['global_405'] = "anonimas";
$locale['global_406'] = "Šis vartotojas buvo blokuotas dėl šių priežasčių:";
$locale['global_407'] = "Šis vartotojas suspenduotas iki ";
$locale['global_408'] = " dėl šių priežasčių:";
$locale['global_409'] = "Šis vartotojas blokuotas dėl saugumo priežasčių.";
$locale['global_410'] = "Priežastis yra: ";
$locale['global_411'] = "Anketa buvo atšaukta.";
$locale['global_412'] = "Anketa buvo padaryta anonimine, greičiausiai dėl neaktyvumo.";
// Banning due to flooding
$locale['global_440'] = "Automatinis flodo kontrolės sistemos blokavimas";
$locale['global_441'] = "Jūsų vartotojas ".fusion_get_settings('sitename')." buvo blokuotas";
$locale['global_442'] = "Sveiki [USER_NAME],\n
Jūsų vartotojas tinklapyje ".fusion_get_settings('sitename')." buvo pagautas pateikiantis per daug įrašų trumpame periode iš IP ".USER_IP.", ir todėl buvo blokuotas. Tai buvo padaryta siekiant apsisaugoti nuo botų, siunčiančių šlamšto pobūdžio informaciją.\n
Prašome susisiekti su administratoriumi paštu ".fusion_get_settings('siteemail')." norėdami, kad Jūsų vartotojas būtų atkurtas arba pranešti kad tai ne Jūsų kaltė dėl šio saugumo blokavimo.\n
".fusion_get_settings('siteusername');
// Lifting of suspension
$locale['global_450'] = "Suspendavimą automatiškai pašalino sistema";
$locale['global_451'] = "Suspendavimas pašalintas ".fusion_get_settings('sitename');
$locale['global_452'] = "Sveiki USER_NAME,\n
Jūsų anketos blokavimas tinklapyje ".fusion_get_settings('siteurl')." buvo pašalintas. Jūsų prisijungimo detalės:\n
Slapyvardis: USER_NAME
Slaptažodis: Nerodomas dėl saugumo motyvų\n
Jeigu pamiršote savo slaptažodį, jį galite nustatyti nauju čia: LOST_PASSWORD\n\n
Pagarbiai,\n
".fusion_get_settings('siteusername');
$locale['global_453'] = "Sveiki USER_NAME,\n
Jūsų anketos blokavimas tinklapyje ".fusion_get_settings('siteurl')." buvo pašalintas.\n\n
Pagarbiai,\n
".fusion_get_settings('siteusername');
$locale['global_454'] = "Vartotojas reaktyvuotas ".fusion_get_settings('sitename');
$locale['global_455'] = "Sveiki USER_NAME,\n
Paskutinį kartą kai prisijungė, Jūsų anketa buvo reaktyvuota tinklapyje ".fusion_get_settings('siteurl')." ir ji nėra daugiau pažymėta kaip neaktyvi.\n\n
Pagarbiai,\n
".fusion_get_settings('siteusername');
// Function parsebytesize()
$locale['global_460'] = "Tuščias";
$locale['global_461'] = "Baitai";
$locale['global_462'] = "kB";
$locale['global_463'] = "MB";
$locale['global_464'] = "GB";
$locale['global_465'] = "TB";
//Safe Redirect
$locale['global_500'] = "Jūs buvote nukreipas į %s, prašome palaukti. Jeigu nebuvote perkeltas, spauskite čia.";


// Captcha Locales
$locale['global_600'] = "Patvirtinimo kodas";
$locale['recaptcha'] = "lt";

//Miscellaneous
$locale['global_900'] = "Neįmanoma paversti HEX į DEC";
//Language Selection
$locale['global_ML100'] = "Kalba:";
$locale['global_ML101'] = "- Pasirinkti kalbą -";
$locale['global_ML102'] = "Tinklapio kalba";

$locale['flood'] = "Jums yra draudžiama rašyti. Prašome palaukti kol nuslūgs flood'o banga. Preliminarus laikas %s.";
$locale['no_image'] = "Nėra paveiksliuko";
$locale['send_message'] = 'Siųsti žinutę';
$locale['go_profile'] = 'Eiti į %s profilį';
// ex. oneword.locale.php file
// translate next lines by yourself
// Greetings
$locale['hello'] = 'Sveiki!';
$locale['goodbye'] = 'Iki susitikimo!';
$locale['welcome'] = 'Sveiki sugrįžę';
$locale['home'] = 'Namai';
// Status
$locale['error'] = 'Klaida!';
$locale['success'] = 'Sėkmė!';
$locale['enable'] = 'Įjungti';
$locale['disable'] = 'Išjungti';
$locale['no'] = 'Ne';
$locale['yes'] = 'Taip';
$locale['off'] = 'Išjungta';
$locale['on'] = 'Įjungta';
$locale['or'] = 'arba';
$locale['by'] = '';
$locale['in'] = '';
$locale['of'] = 'iš';
// Navigation
$locale['next'] = 'Kitas';
$locale['pevious'] = 'Ankstesnis';
$locale['back'] = 'Atgal';
$locale['forward'] = 'Pirmyn';
$locale['go'] = 'Eiti';
$locale['cancel'] = 'Atšaukti';
$locale['move_up'] = "Perkelti aukštyn";
$locale['move_down'] = "Perkelti žemyn";
// Action
$locale['add'] = 'Pridėti';
$locale['save'] = 'Išsaugoti';
$locale['update'] = 'Atnaujinti';
$locale['updated'] = 'Atnaujinta';
$locale['remove'] = 'Pašalinti';
$locale['delete'] = 'Ištrinti';
$locale['search'] = 'Ieškoti';
$locale['help'] = 'Pagalba';
$locale['register'] = 'Registruotis';
$locale['ban'] = 'Blokuoti';
$locale['reactivate'] = 'Aktyvuoti iš naujo';
$locale['user'] = 'Narys';
$locale['promote'] = 'Reklamuoti';
$locale['show'] = 'Rodyti';
//Tables
$locale['status'] = 'Statusas';
$locale['order'] = 'Tvarka';
$locale['sort'] = 'Rikiavimas';
$locale['id'] = 'ID';
$locale['title'] = 'Pavadinimas';
$locale['rights'] = 'Teisės';
$locale['info'] = 'Informacija';
$locale['image'] = 'Paveiksliukas';
// Forms
$locale['choose'] = 'Pasirinkti...';
$locale['root'] = 'Kaip pagrindinį';
$locale['choose-user'] = 'Pasirinkti narį...';
$locale['parent'] = 'Sukurti kaip pagrindinį..';
$locale['order'] = 'Rodymo tvarka';
$locale['status'] = 'Statusas';
$locale['note'] = 'Pasižymėti šį daiktą';
$locale['publish'] = 'Publikuota';
$locale['unpublish'] = 'Nepublikuota';
$locale['draft'] = 'Juodraštis';
$locale['settings'] = 'Nustatymai';
$locale['posted'] = 'parašyta';
$locale['profile'] = 'Profilis';
$locale['edit'] = 'Redaguoti';
$locale['qedit'] = 'Greitasis redagavimas';
$locale['view'] = 'Rodyti';
$locale['login'] = 'Prisijungti';
$locale['logout'] = 'Atsijungti';
$locale['admin-logout'] = 'Admin atsijungimas';
$locale['message'] = 'Asmeninės žinutės';
$locale['logged'] = 'Prisijungęs kaip ';
$locale['version'] = 'Versija ';
$locale['browse'] = 'Ieškoti ...';
$locale['close'] = 'Uždaryti';
$locale['nopreview'] = 'Nėra ką rodyti';
//Alignment
$locale['left'] = "Kairė";
$locale['center'] = "Centras";
$locale['right'] = "Dešinė";
// Comments and ratings
$locale['comments'] = "Komentarai";
$locale['ratings'] = "Reitingai";
$locale['comments_ratings'] = "Komentarai ir reitingai";

// Testimonials
$locale['testimonial_rank'] = "Aš esu šio tinklapio %s";
$locale['testimonial_location'] = "ir šiuo metu gyvenu %s";
$locale['testimonial_join'] = ". Prie šio tinklapio prisiregistravau %s.";
$locale['testimonial_join'] = "Taip pat turiu savo tinklapį %s.";
$locale['testimonial_contact'] = "Jeigu norite su manimi susisiekti, tą padaryti galite per %s.";
$locale['testimonial_email'] = "Taip pat galite man rašyti el. paštu %s.";
?>