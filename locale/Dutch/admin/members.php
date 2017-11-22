<?php
// Member Management Options
$locale['400'] = "Leden";
$locale['401'] = "Gebruiker";
$locale['402'] = "Nieuw lid toevoegen";
$locale['403'] = "Gebruikertype";
$locale['404'] = "Opties";
$locale['405'] = "Bekijken";
$locale['406'] = "Wijzigen";
$locale['407'] = "Activeren";
$locale['408'] = "Verbanning opheffen";
$locale['409'] = "Verbannen";
$locale['410'] = "Verwijderen";
$locale['411'] = "Er zijn geen %s leden";
$locale['412'] = " beginnend met ";
$locale['413'] = " overeenkomend met ";
$locale['414'] = "Toon alle";
$locale['415'] = "Lid zoeken:";
$locale['416'] = "Zoeken";
$locale['417'] = "Selecteer actie";
$locale['418'] = "Annuleren";
$locale['419'] = "Terugzetten";
// Ban/Unban/Delete Member
$locale['420'] = "Opgelegde verbanning";
$locale['421'] = "Verbanning verwijderd";
$locale['422'] = "Lid verwijderd";
$locale['423'] = "Weet u zeker dat u dit lid wilt verwijderen?";
$locale['424'] = "Lid geactiveerd";
$locale['425'] = "<h2>Let op!</h2><br />
U staat op het punt om <strong>%s</strong> te verwijderen!<br />
De volgende bijdrage(n) <u>gepost door dit lid</u> zal worden verwijderd als u doorgaat:<br />
- Artikelen<br />
- Nieuws<br />
- Forum onderwerpen. Ook de bijdragen van andere leden in de te verwijderen onderwerpen worden verwijderd tesamen met enguetes en bijlagen.<br />
- Forum bijdragen<br />
- Forum bijlagen<br />
- Kommentaren<br />
- Prive berichten verzonden of ontvangen door dit lid<br />
- Enquete stemmen<br />
- Gegeven waarderingen<br />
Tenzij dit een spammer is, is het aan te raden om dit lid te Verbannen, Schorsen, Annuleren of te Anonimiseren. Dan blijven de bijdragen bewaard<br />
<br />
Weet u zeker dat u dit lid wilt verwijderen??<br />";
$locale['426'] = "Ja";
$locale['427'] = "Nee";
// Edit Member Details
$locale['430'] = "Wijzig lid";
$locale['431'] = "Lidgegevens gewijzigd";
$locale['432'] = "Terug naar Leden beheer";
$locale['433'] = "Terug naar Beheerder Index";
$locale['434'] = "Niet in staat lidgegevens te wijzigen:";
// Extra Edit Member Details form options
$locale['440'] = "Wijzigingen opslaan";
// Update Profile Errors
$locale['450'] = "Een primaire beheerder kan niet worden gewijzigd.";
$locale['451'] = "U dient een gebruikersnaam en een e-mailadres op te geven.";
$locale['452'] = "Gebruikersnaam bevat ongeldige tekens.";
$locale['453'] = "De gebruikersnaam ".(isset($_POST['user_name']) ? $_POST['user_name'] : "")." is reeds in gebruik.";
$locale['454'] = "Ongeldig e-mailadres.";
$locale['455'] = "Het e-mailadres ".(isset($_POST['user_email']) ? $_POST['user_email'] : "")." is reeds in gebruik.";
$locale['456'] = "De nieuwe wachtwoorden komen niet overeen.";
$locale['457'] = "Ongeldig wachtwoord, gebruik alleen alfanumerieke tekens.<br />
Een wachtwoord dient tenminste zes tekens lang te zijn.";
$locale['458'] = "<strong>Waarschuwing:</strong> onverwachte uitvoering van een script.";
// View Member Profile
$locale['470'] = "Lidprofiel";
$locale['472'] = "Statistieken";
$locale['473'] = "Gebruikersgroepen";
// Add Member Errors
$locale['480'] = "Lid toevoegen";
$locale['481'] = "Het lidaccount is aangemaakt.";
$locale['482'] = "Het lidaccount kon niet worden aangemaakt.";
// Suspension Log
$locale['510s'] = "Schorsing logboek voor ";
$locale['511s'] = "Er zijn geen opgeslagen schorsingen voor dit lid, in dit logboek.";
$locale['512s'] = "Vorige schorsingen van ";
$locale['513'] = "Nr."; // as in number
$locale['514'] = "Datum";
$locale['515'] = "Reden";
$locale['516'] = "Schorsing door Beheerder";
$locale['517'] = "Systeem actie";
$locale['518'] = "Terug naar Gebruikersprofiel";
$locale['519'] = "Schorsingen logboek voor deze gebruiker ";
$locale['520'] = "Opgeheven: ";
$locale['521'] = "IP: ";
$locale['522'] = "Nog niet teruggezet.";
$locale['540'] = "Fout";
$locale['541'] = "Waarschuwing: Er moet een reden opgegeven worden waarvoor er geschorst werd!";
$locale['542'] = "Waarschuwing: Er moet een reden opgegeven worden voor de veiligheidsverbanning!";
// User Management Admin
$locale['550'] = "Schors gebruiker: ";
$locale['551'] = "Lengte in dagen:";
$locale['552'] = "Reden:";
$locale['553'] = "Schors";
$locale['554'] = "Er zijn geen opgeslagen schorsingen in dit logboek.";
$locale['555'] = "Als je zeker weet dat deze gebruiker verbannen moet worden, klik op 'Verban'";
$locale['556'] = "Hef schorsing op voor gebruiker: ";
$locale['557'] = "Hef schorsing op";
$locale['558'] = "Hef verbanning op van gebruiker: ";
$locale['559'] = "Hef verbanning op ";
$locale['560'] = "Hef veiligheidsverbanning op van gebruiker: ";
$locale['561'] = "Hef veiligheidsverbanning op";
$locale['562'] = "Verban gebruiker: ";
$locale['563'] = "Veiligheidsverban gebruiker: ";
$locale['585a'] = "Er moet een reden worden opgegeven waarom u iemand verbant of unbant ";
$locale['566'] = "Verbanning opgeheven";
$locale['568'] = "Veiligheidsverbanning opgelegd";
$locale['569'] = "Veiligheidsverbanning opgeheven";
$locale['572'] = "Lid geschorst";
$locale['573'] = "Schorsing opgeheven";
$locale['574'] = "Lid gedeactiveerd";
$locale['575'] = "Lid gereactiveerd";
$locale['576'] = "Account opgezegd";
$locale['577'] = "Accountopzegging ongedaan gemaakt";
$locale['578'] = "Account opgezegd en geanonimiseerd";
$locale['579'] = "Account anonimisatie ongedaan gemaakt";
$locale['580'] = "Deactivicatie inactieve leden";
$locale['581'] = "U heeft meer dan 50 inactieve gebruikers en u zult daarom het deactivicatie proces <strong>%d keer</strong> moeten doorlopen.";
$locale['582'] = "Reactivicatie";
$locale['583'] = "Re-installeren";
$locale['584'] = "Selecteer nieuwe status";
$locale['585'] = "Dit lid was oorspronkelijk verbannen wegens veiligheidsredenen! Weet je zeker dat je dit lid nu wilt unbannen?";
$locale['590'] = "Schors";
$locale['591'] = "Haal schorsing weg";
$locale['592'] = "<b>schorsen</b>";
$locale['593'] = "haal schorsingen weg";
$locale['594'] = "Geef reden voor ";
$locale['595'] = " gebruiker ";
$locale['596'] = "Tijdsduur:";
$locale['600'] = "Veiligheidsverbanning";
$locale['601'] = "veiligheidsverbanning";
$locale['602'] = "Hef verbanning op";
$locale['603'] = "hef verbanning op";
$locale['604'] = "Reden:";
// Deactivation System
$locale['610'] = "<strong>%d gebruiker(s)</strong> zijn niet ingelogd voor <strong>%d day(s)</strong> en zijn gemarkeerd als inactief.
Als je deze gebruikers deactiveert hebben ze <strong>%d dag(en)</strong> voordat ze worden %s.";
$locale['611'] = "Onthoudt dat sommige gebruikers inhoud op uw site hebben geplaats, zoals forumberichten, reacties, foto&rsquo;s etc.. Dit wordt verwijderd als gedeactiveerde gebruikers worden verwijderd.";
$locale['612'] = "gebruiker";
$locale['613'] = "gebruikers";
$locale['614'] = "Deactiveer";
$locale['615'] = "permanent verwijderd";
$locale['616'] = "anonimiseren";
$locale['617'] = "Waarschuwing:";
$locale['618'] = "Het is sterk aangeraden om in plaats van deactiveren, anonimiseren te gebruiken om dataverlies te voorkomen!";
$locale['619'] = "Je kan dit hier doen.";
$locale['620'] = "anonimiseren";
$locale['621'] = "Automatische deactivicatie van inactieve gebruikers.";
?>
