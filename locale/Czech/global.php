<?php

// Locale Settings
setlocale(LC_TIME, "cs_CS.utf8","CS"); // Linux Server (Windows may differ)
$locale['charset'] = "utf-8";
$locale['xml_lang'] = "cs";
$locale['tinymce'] = "cs";
$locale['phpmailer'] = "cs";
$locale['recaptcha'] = "cs";

/* This is for reCapcha translation. You don't have to add those lines for the following languages:
English (en), Netherland (nl), France (fr), German (de), Portuguese (pt), Russian (ru), Spanish (es), Turkey (tr)
Important do not translate the first word which has an underscore in it, this is needed for JavaScript to understand the translation */
$locale['recaptcha_l10n'] = "visual_challenge:'Získat psanou výzvu', ";
$locale['recaptcha_l10n'] .= "audio_challenge:'Získat audio výzvu', ";
$locale['recaptcha_l10n'] .= "refresh_btn:'Získat novou výzvu', ";
$locale['recaptcha_l10n'] .= "instructions_visual:'Napište dvě slova:', ";
$locale['recaptcha_l10n'] .= "instructions_context:'Napište dvě slova v rámečcích:', ";
$locale['recaptcha_l10n'] .= "instructions_audio:'Napište co uslyšíte:', ";
$locale['recaptcha_l10n'] .= "help_btn:'Pomoc', ";
$locale['recaptcha_l10n'] .= "play_again:'Znovu přehrát zvuk', ";
$locale['recaptcha_l10n'] .= "cant_hear_this:'Stáhnout zvuk jako MP3', ";
$locale['recaptcha_l10n'] .= "incorrect_try_again:'Nesprávně. Zkusit znovu.'";

// Full & Short Months
$locale['months'] = "&nbsp|Leden|Únor|Březen|Duben|Květen|Červen|Červenec|Srpen|Září|Říjen|Listopad|Prosinec";
$locale['shortmonths'] = "&nbsp|Leden|Únor|Březen|Duben|Květen|Červen|Červenec|Srpen|Září|Říjen|Listopad|Prosinec";

// Standard User Levels
$locale['user0'] = "Veřejnost";
$locale['user1'] = "Člen";
$locale['user2'] = "Administrátor";
$locale['user3'] = "Hlavní Administrátor";
$locale['user_na'] = "N/A";
$locale['user_anonymous'] = "Anonymní uživatel";
// Standard User Status
$locale['status0'] = "Aktivní";
$locale['status1'] = "Zabanovaný";
$locale['status2'] = "Neaktivovaný";
$locale['status3'] = "Suspendovaný";
$locale['status4'] = "Bezpečnostně zabanovaný";
$locale['status5'] = "Zrušený";
$locale['status6'] = "Anonymní";
$locale['status7'] = "Deaktivovaný";
$locale['status8'] = "Neaktivní";

// Forum Moderator Level(s)
$locale['userf1'] = "Moderátor";
// Navigation
$locale['global_001'] = "Navigace";
$locale['global_002'] = "Žádné odkazy nebyly definovány";
// Users Online
$locale['global_010'] = "Kdo je on-line";
$locale['global_011'] = "Hosté on-line";
$locale['global_012'] = "Členové on-line";
$locale['global_013'] = "Žádný člen není on-line";
$locale['global_014'] = "Registrovaní členové";
$locale['global_015'] = "Neaktivní členové";
$locale['global_016'] = "Nejnovější člen";
// Forum Side panel
$locale['global_020'] = "Diskuze fóra";
$locale['global_021'] = "Nejnovější diskuze";
$locale['global_022'] = "Nejdiskutovanější";
$locale['global_023'] = "Žádná diskuze";
// Comments Side panel
$locale['global_025'] = "Nejnovější komentáře";
$locale['global_026'] = "Žádné komentáře nejsou k dispozici";

// Articles Side panel
$locale['global_030'] = "Poslední články";
$locale['global_031'] = "Zatím nejsou žádné články";
// Downloads Side panel
$locale['global_032'] = "Nejnovější Soubory";
$locale['global_033'] = "Žádný stažitelný obsah není k dispozici";

// Welcome panel
$locale['global_035'] = "Vítejte";
// Latest Active Forum Threads panel
$locale['global_040'] = "Poslední aktivní diskuze fóra";
$locale['global_041'] = "Moje diskuze";
$locale['global_042'] = "Moje příspěvky";
$locale['global_043'] = "Nové příspěvky";
$locale['global_044'] = "Diskuze";
$locale['global_045'] = "Shlédnuté";
$locale['global_046'] = "Odpovězeno";
$locale['global_047'] = "Poslední příspěvek";
$locale['global_048'] = "Fórum";
$locale['global_049'] = "Přidané";
$locale['global_050'] = "Autor";
$locale['global_051'] = "Anketa";
$locale['global_052'] = "Přesunul/a";
$locale['global_053'] = "Nezaložili jste zatím žádnou diskuzi.";
$locale['global_054'] = "Zatím jste do fóra nepřispěli žádným příspěvkem.";
$locale['global_055'] = "Od vaší poslední návštěvy bylo napsáno %u nových příspěvků.";
$locale['global_056'] = "Moje sledované diskuze";
$locale['global_057'] = "Nastavení";
$locale['global_058'] = "Zastavit";
$locale['global_059'] = "Nesledujete žádnou diskuzi.";
$locale['global_060'] = "Zastavit sledování diskuzí?";
// News & Articles
$locale['global_070'] = "Přidal/a ";
$locale['global_071'] = "dne ";
$locale['global_072'] = "Přečíst vše";
$locale['global_073'] = " Komentářů";
$locale['global_073b'] = " Komentářů";
$locale['global_074'] = "x Přečteno";
$locale['global_075'] = "Tisk";
$locale['global_076'] = "Upravit";
$locale['global_077'] = "Novinky";
$locale['global_078'] = "Nebyly napsané žádné novinky";
$locale['global_079'] = "V ";
$locale['global_080'] = "žádné kategorii";
// Page Navigation
$locale['global_090'] = "Zpět";
$locale['global_091'] = "Další";
$locale['global_092'] = "Strana ";
$locale['global_093'] = " z ";
// Guest User Menu
$locale['global_100'] = "Přihlášení";
$locale['global_101'] = "Jméno";
$locale['global_102'] = "Heslo";
$locale['global_103'] = "Zapamatovat";
$locale['global_104'] = "Přihlásit";
$locale['global_105'] = "Nejste členem?<br /><a href='".BASEDIR."register.php' class='side'><strong>Klikněte sem</strong></a><br /> a zaregistrujte se.";
$locale['global_106'] = "Zapomněli jste heslo?<br />Pro zaslání nového  <br /><a href='".BASEDIR."lostpassword.php' class='side'><strong>Klikněte sem</strong></a>.";
$locale['global_107'] = "Registrace";
$locale['global_108'] = "Zapomenuté heslo";
// Member User Menu
$locale['global_120'] = "Upravit profil";
$locale['global_121'] = "Soukromé zprávy";
$locale['global_122'] = "Seznam členů";
$locale['global_123'] = "Administrační sekce";
$locale['global_124'] = "Odhlásit";
$locale['global_125'] = "Máte %u novou/nové ";
$locale['global_126'] = "zprávu";
$locale['global_127'] = "zprávy";
$locale['global_128'] = "podání";
$locale['global_129'] = "podání";
// Poll
$locale['global_130'] = "Anketa";
$locale['global_131'] = "Hlasovat";
$locale['global_132'] = "Pro hlasování musíte být přihlášeni.";
$locale['global_133'] = "Hlas";
$locale['global_134'] = "Hlasy";
$locale['global_135'] = "Hlasy: ";
$locale['global_136'] = "Začátek hlasování: ";
$locale['global_137'] = "Konec hlasování: ";
$locale['global_138'] = "Archív anket";
$locale['global_139'] = "Vyber anketu ze seznamu:";
$locale['global_140'] = "Zobrazit";
$locale['global_141'] = "Zobrazit anketu";
$locale['global_142'] = "Zatím nejsou žádné ankety.";
// Captcha
$locale['global_150'] = "Validační kód";
$locale['global_151'] = "Vložte validační kód:";
// Footer Counter
$locale['global_170'] = "návštěv";
$locale['global_171'] = "návštěv";
$locale['global_172'] = "Vygenerované za: %s sekund";
$locale['global_173'] = "Dotazů(y)";
// Admin Navigation
$locale['global_180'] = "Admin Index";
$locale['global_181'] = "Zpět na stránku";
$locale['global_182'] = "<strong>Poznámka:</strong> Admin heslo nebylo zadané, nebo je chybné.";
// Miscellaneous
$locale['global_190'] = "Mód údržby byl aktivován";
$locale['global_191'] = "Z této IP adresy nemáte povolený přístup na tuto stránku.";
$locale['global_192'] = "Vaše cookie vypršela. Pro pokračování se musíte přihlásit.  ";
$locale['global_193'] = "Nelze nastavit uživatelovo cookie. Prosím ujistěte se, že máte zapnutou podporu cookie aby jste se mohl(a) přihlásit.";
$locale['global_194'] = "Váš účet je zablokován.";
$locale['global_195'] = "Tento účet nebyl aktivován.";
$locale['global_196'] = "Nesprávné jméno nebo heslo.";
$locale['global_197'] = "Počkejte prosím. Přihlašování může chvíli trvat...<br /><br />
[ <a href='index.php'>Pokud se stránka nezobrazí do 10 sekund klikněte sem.</a> ]";
$locale['global_198'] = "<strong>UPOZORNĚNÍ:</strong> setup.php nebyl smazán. Smažte jej co nejdříve.";
$locale['global_199'] = "<strong>UPOZORNĚNÍ:</strong> Není nastavené Admin heslo. Klikněte na <a href='edit_profile.php'>Editovat profil</a> a nastavte ho.";
//Titles
$locale['global_200'] = " - ";
$locale['global_201'] = ": ";
$locale['global_202'] = $locale['global_200']."Vyhledávání";
$locale['global_203'] = $locale['global_200']."FAQ";
$locale['global_204'] = $locale['global_200']."Fórum";
//Themes
$locale['global_210'] = "Vynechat obsah";
// No themes found
$locale['global_300'] = "vzhled nenalezen";
$locale['global_301'] = "Velmi se omlouváme, ale tuto stránku nelze zobrazit. Díky neznámým komplikacím nelze najít Theme (vzhled) stránky. Pokud jste adminem stránek, použijte FTP program a nahrajte do složky <em>themes/</em> vzhled, který je určen pro <em>PHP-Fusion verze 7.02.xx</em>. Poté přejděte do <em>Hlavního nastavení</em> a zkontrolujte, zda vzhled, který jste nahráli do složky  <em>themes/</em>, je zde v seznamu dostupných vzhledů. Pokud se zde vzhled nachází, zkontrolujte ještě, že adresář vzhledu má stejný název, jaký je obsažen v souborech (důležitá jsou malá a velká písmena, která se rozlišují), jestli je vše v pořádku, tak vyberte vzhled v <em>Hlavním nastavení</em> a uložte.<br /><br />Jestliže jste pouze návštěvník stránek, kontaktujte administrátora stránky na jeho emailu: ".hide_email($settings['siteemail'])." a oznamte chybu na stránce.";
$locale['global_302'] = "Vzhled, který jste nastavili v Hlavním nastavení neexistuje nebo není kompletní!";
// JavaScript Not Enabled
$locale['global_303'] = "Ale ne! Kde je <strong>JavaScript</strong>?<br />Váš prohlížeč nemá zaplý JavaScript nebo jej nepodporuje. Prosím zapněte <strong>JavaScript</strong> ve Vašem prohlížeči ke správnému zobrazení této stránky,<br /> nebo <strong>přejděte</strong> k prohlížeči, který podporuje JavaScript; <a href='http://firefox.com' rel='nofollow' title='Mozilla Firefox'>Firefox</a>, <a href='http://apple.com/safari/' rel='nofollow' title='Safari'>Safari</a>, <a href='http://opera.com' rel='nofollow' title='Opera Web Browser'>Opera</a>, <a href='http://www.google.com/chrome' rel='nofollow' title='Google Chrome'>Chrome</a> nebo na verzi <a href='http://www.microsoft.com/windows/internet-explorer/' rel='nofollow' title='Internet Explorer'>Internet Explorer</a> novější než verze 6.";
// User Management  
// Member status
$locale['global_400'] = "Pozastaven";
$locale['global_401'] = "Zablokován";
$locale['global_402'] = "Deaktivován";
$locale['global_403'] = "Účet smazán";
$locale['global_404'] = "Anonymní účet";
$locale['global_405'] = "Anonymní uživatel";
$locale['global_406'] = "Tento účet byl zabanován z následujících důvodů:";
$locale['global_407'] = "Tento účet byl pozastaven do ";
$locale['global_408'] = " pro následující důvody:";
$locale['global_409'] = "Tento účet byl zabanován z bezpečnostních důvodů.";
$locale['global_410'] = "Důvod: ";
$locale['global_411'] = "Tento účet byl zrušen.";
$locale['global_412'] = "Tento účet byl deaktivován z důvodu neaktivity.";
// Banning due to flooding
$locale['global_440'] = "Zablokován kvůli Spamování";
$locale['global_441'] = "Váš účet na ".$settings['sitename']."byl zablokován.";
$locale['global_442'] = "Vítejte [USER_NAME],\n
Váš účet na ".$settings['sitename']." byl přistižen při zadávaní příliš mnoho požadavků ve velice krátkém čase z IP adresy ".USER_IP." a proto byl zablokován. Toto se dělá z prevence před boty.\n
Prosím kontaktujte hlavního administrátora na emailu ".$settings['siteemail']." pro odblokování vašeho účtu nebo nahlášení, že je to nedorozumění.\n
".$settings['siteusername'];
// Lifting of suspension
$locale['global_450'] = "Deaktivován.";
$locale['global_451'] = "Váš účet byl deaktivován na ".$settings['sitename'];
$locale['global_452'] = "Vítejte USER_NAME,\n
Blokace vašeho účtu na ".$settings['siteurl']." byla zrušena. Zde jsou Vaše přihlašovací údaje:\n
Přihlašovací jméno: USER_NAME
Heslo: Skryto z bezpečnostních důvodů.\n
Pokud jste zapomněli Vaše heslo, můžete získat nové zde: LOST_PASSWORD\n\n
S pozdravem,\n
".$settings['siteusername'];
$locale['global_453'] = "Vítejte USER_NAME,\n
Blokace Vašeho účtu na ".$settings['siteurl']." byla zrušena.\n\n
S pozdravem,\n
".$settings['siteusername'];
$locale['global_454'] = "Účet na ".$settings['sitename']." už je aktivní.";
$locale['global_455'] = "Vítejte USER_NAME,\n
Váš učet na ".$settings['siteurl']." byl deaktivovaný, nyní už je aktivní.\n\n
S pozdravem,\n
".$settings['siteusername'];
// Function parsebytesize()
$locale['global_460'] = "Prázdné";
$locale['global_461'] = "Byty";
$locale['global_462'] = "kB";
$locale['global_463'] = "MB";
$locale['global_464'] = "GB";
$locale['global_465'] = "TB";
//Safe Redirect
$locale['global_500'] = "Budete přesměrováni na %s, čekejte prosím. Pokud ne, klikněte zde.";

// Captcha Locales
$locale['global_600'] = "Validační kód";

//Miscellaneous
$locale['global_900'] = "Nelze převést z HEX do DEC";
?>
