<?php
// Member Management Options
$locale['400'] = "Brugere";
$locale['401'] = "Brugernavn";
$locale['402'] = "Tilføj ny bruger";
$locale['403'] = "Brugerstatus";
$locale['404'] = "Valgmuligheder";
$locale['405'] = "Se";
$locale['406'] = "Rediger";
$locale['407'] = "Aktiver";
$locale['408'] = "Ophæv udelukkelse";
$locale['409'] = "Udeluk";
$locale['410'] = "Slet";
$locale['411'] = "Der er ikke fundet: <i>%s brugere</i>";
$locale['412'] = ", hvis brugernavn begynder med ";
$locale['413'] = ", der passer på søgekriteriet ";
$locale['414'] = "Vis alle";
$locale['415'] = "Søg efter bruger:";
$locale['416'] = "Søg";
$locale['417'] = "Vælg handling";
$locale['418'] = "Fortryd";
$locale['419'] = "Genopret";
// Ban/Unban/Delete Member
$locale['420'] = "Udelukkelsen er gennemført";
$locale['421'] = "Udelukkelsen er ophævet";
$locale['422'] = "Brugeren er slettet";
$locale['423'] = "Er du sikker på, at denne bruger skal slettes?";
$locale['424'] = "Brugerkonto aktiveret";
// Edit Member Details
$locale['430'] = "Rediger bruger";
$locale['431'] = "Brugeroplysninger er opdateret";
$locale['432'] = "Tilbage til brugerstyring";
$locale['433'] = "Tilbage til administration";
$locale['434'] = "Var ikke i stand til at opdatere brugeroplysninger:";
// Extra Edit Member Details form options
$locale['440'] = "Gem ændringer";
// Update Profile Errors
$locale['450'] = "Den primære administrator kan ikke redigeres.";
$locale['451'] = "Du skal angive et brugernavn og en mail adresse.";
$locale['452'] = "Brugernavnet indeholder forbudte tegn.";
$locale['453'] = "Brugernavnet ".(isset($_POST['user_name']) ? $_POST['user_name'] : "")." anvendes allerede.";
$locale['454'] = "Fejl i mail adresse.";
$locale['455'] = "Mail adressen ".(isset($_POST['user_email']) ? $_POST['user_email'] : "")." anvendes allerede.";
$locale['456'] = "De to kodeord er ikke identiske.";
$locale['457'] = "Der er fejl i kodeordet, brug kun alfanumeriske karakterer.<br />
Kodeordet skal være på mindst 8 tegn.";
$locale['458'] = "<strong>Advarsel:</strong> fejl i udførelsen af scriptet.";
// View Member Profile
$locale['470'] = "Brugerprofil";
$locale['472'] = "Statistik";
$locale['473'] = "Brugergrupper";
// Add Member Errors
$locale['480'] = "Tilføj bruger";
$locale['481'] = "Brugerkontoen er oprettet.";
$locale['482'] = "Brugerkontoen kunne ikke oprettes.";
// Suspension Log 
$locale['510s'] = "Udelukkelseslog for ";
$locale['511s'] = "Der er ikke registreret udelukkelse for denne bruger.";
$locale['512s'] = "Tidligere udelukkelse for ";
$locale['513'] = "Nr."; // as in number
$locale['514'] = "Dato";
$locale['515'] = "Årsag";
$locale['516'] = "Udelukket af";
$locale['517'] = "Systemhandling";
$locale['518'] = "Tilbage til brugerprofil";
$locale['519'] = "Udelukkelseshistorik for denne bruger ";
$locale['520'] = "Ophævet: ";
$locale['521'] = "IP: ";
$locale['522'] = "Endnu ikke genaktiveret";
$locale['540'] = "Fejl";
$locale['541'] = "Fejl: Du skal angive en årsag til udelukkelsen!";
$locale['542'] = "Fejl: Du skal angive en årsag til sikkerhedsudelukkelsen!";
// User Management Admin
$locale['550'] = "Suspendér bruger: ";
$locale['551'] = "Varighed i dage:";
$locale['552'] = "Årsag:";
$locale['553'] = "Udelukket af";
$locale['554'] = "Der er ikke registreret udelukkelse for denne bruger.";
$locale['555'] = "Hvis du beslutter, at denne bruger skal udelukkes, så klik UDELUK";
$locale['556'] = "Ophæv udelukkelse af brugeren: ";
$locale['557'] = "Ophæv suspension";
$locale['558'] = "Fjern udelukkelse af brugeren: ";
$locale['559'] = "Ophæv udelukkelse for: ";
$locale['560'] = "Ophæv sikkerhedsudelukkelse for brugeren: ";
$locale['561'] = "Ophæv sikkerhedsudelukkelse";
$locale['562'] = "Udeluk brugeren: ";
$locale['563'] = "Sikkerhedsudeluk brugeren: ";
$locale['585a'] = "Angiv en årsag til udelukkelse eller ophævelse af udelukkelse for brugeren: ";

$locale['566'] = "Udelukkelsen er ophævet";
$locale['568'] = "Sikkerhedsudelukkelse gennemført";
$locale['569'] = "Sikkerhedsudelukkelse ophævet";
$locale['572'] = "Brugeren er suspenderet";
$locale['573'] = "Suspensionen er ophævet";
$locale['574'] = "Brugeren er deaktiveret";
$locale['575'] = "Brugeren er genaktiveret";
$locale['576'] = "Kontoen er slettet";
$locale['577'] = "Sletning af brugerkonto ophævet";
$locale['578'] = "Brugerkontoen er slettet og brugeren anonymiseret";
$locale['579'] = "Anonymiseringen er ophævet";
$locale['580'] = "Fjern inaktive brugere";
$locale['581'] = "Du har flere end 50 inaktive brugere og er nødt til at gennemføre processen <strong>%d gange</strong>.";
$locale['582'] = "Genaktiver";
$locale['583'] = "Genindsæt";
$locale['584'] = "Vælg ny status";
$locale['585'] = "Denne bruger blev oprindelig udelukket af sikkerhedsårsager! Er du sikker på, at du vil genaktivere brugeren nu?";

$locale['590'] = "Suspendér";
$locale['591'] = "Ophæv suspension";
$locale['592'] = "udelukker";
$locale['593'] = "ophæver udelukkelsen af";
$locale['594'] = "Angiv en årsag til at du ";
$locale['595'] = " brugeren ";
$locale['596'] = "Varighed:";

$locale['600'] = "Sikkerhedsudelukkelse";
$locale['601'] = "sikkerhedsudelukkelse";
$locale['602'] = "Ophæv";
$locale['603'] = "ophæver";
$locale['604'] = "Årsag:";
// Deactivation System
$locale['610'] = "<strong>%d bruger(e)</strong> har ikke været logget på i <strong>%d dag(e)</strong> og er markeret som inaktive. 
Ved at deaktivere disse brugere vil de få <strong>%d dag(e)</strong> før de bliver %s.";
$locale['611'] = "Bemærk at visse brugere kan have bidraget med indhold til siden så som debatindlæg, kommentarer, billeder og så videre.
Dette indhold vil blive slettet, hvis deaktiverede brugere slettes.";
$locale['612'] = "bruger";
$locale['613'] = "brugere";
$locale['614'] = "Deaktiver";
$locale['615'] = "slettet helt";
$locale['616'] = "anonymiser";
$locale['617'] = "Advarsel:";
$locale['618'] = "Det anbefales stærkt at ændre den handling, som udløses af en deaktivering til anonymisering i stedet for sletning for ikke at miste data!";
$locale['619'] = "Det kan du gøre det samme <a href='".ADMIN."settings_users.php".$aidlink."'>HER</a>.";
$locale['620'] = "anonymiser";
$locale['621'] = "Automatisk deaktivering af inaktive brugere.";
?>