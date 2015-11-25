<?php
// Member Management Options
$locale['400'] = "Členové";
$locale['401'] = "Jméno";
$locale['402'] = "Přidat nového člena";
$locale['403'] = "Typ účtu";
$locale['404'] = "Nastavení";
$locale['405'] = "Náhled";
$locale['406'] = "Upravit";
$locale['407'] = "Aktivovat";
$locale['408'] = "Zrušit BAN";
$locale['409'] = "Zabanovat";
$locale['410'] = "Vymazat";
$locale['411'] = "Neexistují žádní %s užívatelé";
$locale['412'] = " začínající na ";
$locale['413'] = " začínající na ";
$locale['414'] = "Zobrazit všechny";
$locale['415'] = "Hledat uživatele:";
$locale['416'] = "Hledat";
$locale['417'] = "Zvolit úlohu";
$locale['418'] = "Storno";
$locale['419'] = "Obnovit";
// Ban/Unban/Delete Member
$locale['420'] = "Zabanovaný";
$locale['421'] = "Odbanovaný";
$locale['422'] = "Člen byl vymazán";
$locale['423'] = "Jste si jisti, že chcete vymazat tohoto člena?";
$locale['424'] = "Člen aktivován";
$locale['425'] = "<h2>Varování!</h2><br />
Chystáte se smazat uživatele <strong>%s</strong> !<br />
Následující obsah <u>přidaný tímto uživatelem</u> bude smazán, pokud smažete uživatele:<br />
- Články<br />
- Novinky<br />
- Vlákna fóra. Příspěvky ostatních uživatelů, stejně tak hlasy v anketě, v tomto vláknu budou také odstraněny.<br />
- Příspěvky ve fóru<br />
- Přílohy ve fóru<br />
- Komentáře<br />
- Soukromé zprávy (přijaté i odeslané)<br />
- Hlasy v anketě<br />
- Hodnocení<br />
Pokud nejde o spammera, doporučujeme vám dát uživateli Ban, Suspendovat ho, Zrušit ho nebo ho Anonymizovat.<br />
<br />
Chcete pokračovat a smazat uživatele?<br />";
$locale['426'] = "Ano";
$locale['427'] = "Ne";
// Edit Member Details
$locale['430'] = "Upravit člena";
$locale['431'] = "Detaily člena byly aktualizovány";
$locale['432'] = "Návrat do administrace členů";
$locale['433'] = "Návrat do administrace";
$locale['434'] = "Detaily člena není možné upravit:";
// Extra Edit Member Details form options
$locale['440'] = "Uložit změny";
// Update Profile Errors
$locale['450'] = "Nelze upravit hlavního administrátora.";
$locale['451'] = "Musíte zadat jméno a email.";
$locale['452'] = "Jméno obsahuje nepovolené znaky.";
$locale['453'] = "Jméno ".(isset($_POST['user_name']) ? $_POST['user_name'] : "")." už někdo používá. Zvolte si prosím jiné.";
$locale['454'] = "Nesprávná e-mailová adresa.";
$locale['455'] = "Tento Email ".(isset($_POST['user_email']) ? $_POST['user_email'] : "")." už někdo používá. Zvolte si prosím jiné.";
$locale['456'] = "Nová hesla se neshodují.";
$locale['457'] = "Nesprávné heslo. Používejte jen alfa-numerické znaky.<br />
Heslo musí mít minimálně 6 znaků.";
$locale['458'] = "<strong>Upozornění:</strong> nepovolené spuštění skriptu.";
// View Member Profile
$locale['470'] = "Profil";
$locale['472'] = "Statistiky";
$locale['473'] = "Uživatelské skupiny";
// Add Member Errors
$locale['480'] = "Přidat člena";
$locale['481'] = "Účet byl úspěšně vytvořen.";
$locale['482'] = "Účet nebyl vytvořen.";
// Suspension Log 
$locale['510s'] = "Pozastaven kvůli ";
$locale['511s'] = "Tento člen nemá žádný záznam v seznamu pozastavení.";
$locale['512s'] = "Předchozí pozastavení ";
$locale['513'] = "Ne."; // as in number
$locale['514'] = "Datum";
$locale['515'] = "Důvod";
$locale['516'] = "Uděleno adminem";
$locale['517'] = "Možnosti";
$locale['518'] = "Zpět na uživatelský profil";
$locale['519'] = "Záznam pozastavení pro tohoto uživatele ";
$locale['520'] = "Zastaven: ";
$locale['521'] = "IP: ";
$locale['522'] = "Ještě neobnoveni";
$locale['540'] = "Chyba";
$locale['541'] = "Chyba: Musíte zadat důvod pro pozastavení!";
$locale['542'] = "Chyba: Musíte zadat důvod pro zablokování!";
// User Management Admin
$locale['550'] = "Pozastavený uživatel: ";
$locale['551'] = "Délka trvání ve dnech:";
$locale['552'] = "Důvod:";
$locale['553'] = "Pozastavit";
$locale['554'] = "Nejsou zde žádné žádné záznamy pro tohoto uživatele";
$locale['555'] = "Když se rozhodnete, že tento uživatel by měl být banován, klikněte na 'Zananovat'";
$locale['556'] = "Odebrat pozastavení uživatele: ";
$locale['557'] = "Odebrat pozastavení";
$locale['558'] = "Odebrat ban uživateli: ";
$locale['559'] = "Odebrat ban ";
$locale['560'] = "Odebrat bezpečnostní ban uživatele: ";
$locale['561'] = "Odebrat bezpečnostní ban";
$locale['562'] = "Zabanovat uživatele: ";
$locale['563'] = "Bezpečnostně zabanovat uživatele: ";
$locale['585a'] = "Prosím zadejte důvod banu nebo unbanu ";

$locale['566'] = "Ban odstraněn";
$locale['568'] = "Bezpečnostní ban uložen";
$locale['569'] = "Bezpečnostní ban odstraněn";
$locale['572'] = "Uživatel pozastaven";
$locale['573'] = "Pozastavení odstraněno";
$locale['574'] = "Člen deaktivován";
$locale['575'] = "Člen reaktivován";
$locale['576'] = "Přístum zamezen";
$locale['577'] = "Stornování přístupu se nezdařilo";
$locale['578'] = "Přístup zamezen a anonymizován";
$locale['579'] = "Anonymizace přístupu se nezdařila";
$locale['580'] = "Deaktivovat neaktivní členy";
$locale['581'] = "Máte více jak 50 neaktivních členů, budete muset proces opakovat <strong>%d krát</strong>.";
$locale['582'] = "Reaktivovat";
$locale['583'] = "Obnovit";
$locale['584'] = "Zvolit nový status";
$locale['585'] = "Tento uživatel byl původně zabanovaný. Opravdu jej chcete odbanovat ?";

$locale['590'] = "Pozastavit";
$locale['591'] = "Obnovit";
$locale['592'] = "pozastavujete";
$locale['593'] = "obnovujete";
$locale['594'] = "Prosím zadejte důvod proč ";
$locale['595'] = " uživatel ";
$locale['596'] = "Délka:";

$locale['600'] = "Bezpečnostní ban";
$locale['601'] = "bezpečnostní banování";
$locale['602'] = "Odbanování";
$locale['603'] = "odbanovávám";
$locale['604'] = "Důvod:";
// Deactivation System
$locale['610'] = "<strong>%d uživatel(s)</strong> se nepřihlásil <strong>%d den(dní)</strong> a byl označen jako neaktivní. 
Do deaktivace těchto uživatelů zbývá <strong>%d den(dní)</strong> předtím než %s.";
$locale['611'] = "Prosím uvažte, že uživatel mohl již zaslat nějaký obsah, který by mohl bude po jeho vymazání vymazán jako např. komentáře, příspěvky do fóra, atp...";
$locale['612'] = "uživatel";
$locale['613'] = "uživatelé";
$locale['614'] = "Deaktivovat";
$locale['615'] = "trvale vymazán";
$locale['616'] = "anonymizovat";
$locale['617'] = "Varování:";
$locale['618'] = "Je striktně doporučeno změnit deaktivační úlohu na anonymizaci kvůli zachování dat!";
$locale['619'] = "Také můžete <a href='".ADMIN."settings_users.php".$aidlink."'>zde</a>.";
$locale['620'] = "anonymizovat";
$locale['621'] = "Automatická deaktivace neaktivních členů.";
?>