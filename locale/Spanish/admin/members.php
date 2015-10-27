<?php
// Member Management Options
$locale['400'] = "GestiÃ³n de Usuarios";
$locale['401'] = "Usuario";
$locale['402'] = "AÃ±adir Miembro";
$locale['403'] = "Tipo de Usuario";
$locale['404'] = "Opciones";
$locale['405'] = "Ver Estado";
$locale['406'] = "Editar";
$locale['407'] = "Activar";
$locale['408'] = "Readmitir";
$locale['409'] = "Expulsar";
$locale['410'] = "Borrar";
$locale['411'] = "No hay miembros en estado %s";
$locale['412'] = " que empiecen por ";
$locale['413'] = " que coincidan con ";
$locale['414'] = "Mostrar Todos";
$locale['415'] = "Buscar:";
$locale['416'] = "Buscar";
$locale['417'] = "Seleccionar AcciÃ³n";
$locale['418'] = "Cancelar";
$locale['419'] = "Rehabilitar";
// Ban/Unban/Delete Member
$locale['420'] = "ExpulsiÃ³n impuesta";
$locale['421'] = "ExpulsiÃ³n eliminada";
$locale['422'] = "Miembro borrado";
$locale['423'] = "¿Borrar este miembro?";
$locale['424'] = "Miembro activado";
$locale['425'] = "<h2>Warning!</h2><br />
You are about to delete user <strong>%s</strong> !<br />
The following content <u>posted by this user</u> on this site will be deleted if you proceed:<br />
- Articles<br />
- News<br />
- Forum threads. Note that posts made by other users in these threads will also be deleted, along with the poll votes and attachemets existent in this threads.<br />
- Forum posts<br />
- Forum attachements<br />
- Comments<br />
- Private messages sent or received by this user<br />
- Poll votes<br />
- Ratings given<br />
Unless this is a spammer for eg. we recommend you to Ban, Suspend, Cancel or Anomymize this user.<br />
<br />
Are you sure you want to delete this user?<br />";
$locale['426'] = "SÃ­";
$locale['427'] = "No";
// Edit Member Details
$locale['430'] = "Editar Miembro";
$locale['431'] = "Datos del miembro actualizados";
$locale['432'] = "Volver a la AdministraciÃ³n de Miembros";
$locale['433'] = "Volver a AdministraciÃ³n";
$locale['434'] = "No se han podido actualizar los datos del miembro:";
// Extra Edit Member Details form options
$locale['440'] = "Guardar";
// Update Profile Errors
$locale['450'] = "No se ha podido editar el administrador principal.";
$locale['451'] = "Debes indicar un nombre de usuario y una direcciÃ³n de email.";
$locale['452'] = "El nombre de usuario contiene caracteres no vÃ¡lidos.";
$locale['453'] = "El nombre de usuario ".(isset($_POST['user_name']) ? $_POST['user_name'] : "")." ya estÃ¡ en uso.";
$locale['454'] = "La direcciÃ³n de email no es vÃ¡lida.";
$locale['455'] = "La direcciÃ³n de email ".(isset($_POST['user_email']) ? $_POST['user_email'] : "")." ya estÃ¡ en uso.";
$locale['456'] = "Las nuevas contraseÃ±as no coinciden.";
$locale['457'] = "ContraseÃ±a no vÃ¡lida. Usa sÃ³lo caracteres alfanumÃ©ricos.<br />
La contraseÃ±a debe tener, como mÃ­nimo, 8 caracteres de longitud.";
$locale['458'] = "<b>Aviso:</b> EjecuciÃ³n inesperada del programa.";
// View Member Profile
$locale['470'] = "Perfil del Miembro";
$locale['472'] = "EstadÃ­sticas";
$locale['473'] = "Grupos de Usuarios";
// Add Member Errors
$locale['480'] = "AÃ±adir Miembro";
$locale['481'] = "La cuenta del miembro ha sido creada.";
$locale['482'] = "No se ha podido crear la cuenta del miembro.";
// Suspension Log 
$locale['510s'] = "Registro de SuspensiÃ³n para ";
$locale['511s'] = "En el registro de suspensiones no hay datos de este miembro.";
$locale['512s'] = "Suspensiones anteriores de ";
$locale['513'] = "Nº."; // as in number
$locale['514'] = "Fecha";
$locale['515'] = "Motivo";
$locale['516'] = "SuspensiÃ³n del Administrador";
$locale['517'] = "AcciÃ³n del Sistema";
$locale['518'] = "Volver al Perfil del Usuario";
$locale['519'] = "Registro de SuspensiÃ³n para este Usuario ";
$locale['520'] = "Cancelada: ";
$locale['521'] = "IP: ";
$locale['522'] = "TodavÃ­a no rehabilitado";
$locale['540'] = "Error";
$locale['541'] = "Error: Debes indicar un motivo para la SuspensiÃ³n.";
$locale['542'] = "Error: Debes indicar un motivo para la ExpulsiÃ³n de Seguridad.";
// User Management Admin
$locale['550'] = "Suspender Usuario: ";
$locale['551'] = "DuraciÃ³n en dÃ­as:";
$locale['552'] = "Motivo:";
$locale['553'] = "Suspender";
$locale['554'] = "En el registro de suspensiones no hay datos de este miembro.";
$locale['555'] = "Si decides que este usuario deberÃ­a ser expulsado, pulsa Expulsar";
$locale['556'] = "Cancelar SuspensiÃ³n del Usuario: ";
$locale['557'] = "Cancelar SuspensiÃ³n";
$locale['558'] = "Cancelar ExpulsiÃ³n del Usuario: ";
$locale['559'] = "Cancelar ExpulsiÃ³n";
$locale['560'] = "Cancelar ExpulsiÃ³n de Seguridad del Usuario: ";
$locale['561'] = "Cancelar ExpulsiÃ³n de Seguridad";
$locale['562'] = "Expulsar al Usuario: ";
$locale['563'] = "Expulsar por seguridad al Usuario: ";
$locale['585a'] = "Explica el motivo por el que estÃ¡s expulsando o readmitiendo ";
$locale['566'] = "ExpulsiÃ³n cancelada";
$locale['568'] = "ExpulsiÃ³n de seguridad impuesta";
$locale['569'] = "ExpulsiÃ³n de seguridad cancelada";
$locale['572'] = "Miembro suspendido";
$locale['573'] = "SuspensiÃ³n cancelada";
$locale['574'] = "Miembro desactivado";
$locale['575'] = "Miembro reactivado";
$locale['576'] = "Cuenta cancelada";
$locale['577'] = "CancelaciÃ³n de cuenta anulada";
$locale['578'] = "Cuenta cancelada y anonimizada";
$locale['579'] = "AnonimizaciÃ³n de cuenta anulada";
$locale['580'] = "Desactivar Miembros Inactivos";
$locale['581'] = "Tienes mÃ¡s de 50 usuarios inactivos y el proceso de desactivaciÃ³n tendrÃ¡ que ejecutarse <b>%d veces</b>.";
$locale['582'] = "Reactivar";
$locale['583'] = "Rehabilitar";
$locale['584'] = "Seleccionar nuevo estado";
$locale['585'] = "Este miembro fue inicialmente expulsado por razones de seguridad. ¿Seguro que deseas readmitir ahora a este miembro?";
$locale['590'] = "Suspender";
$locale['591'] = "Rehabilitar";
$locale['592'] = "suspendiendo";
$locale['593'] = "rehabilitando";
$locale['594'] = "Indica el motivo por el que estÃ¡s ";
$locale['595'] = " al usuario ";
$locale['596'] = "DuraciÃ³n:";
$locale['600'] = "Expulsar por seguridad";
$locale['601'] = "expulsando por seguridad";
$locale['602'] = "Readmitir";
$locale['603'] = "readmitiendo";
$locale['604'] = "Motivo:";
// Deactivation System
$locale['610'] = "Hay <b>%d usuarios</b> que no han iniciado sesiÃ³n durante <b>%d dÃ­as</b> y han sido marcados como inactivos.
Si se desactivan, dispondrÃ¡n de <b>%d dÃ­as</b> antes de que sean %s.";
$locale['611'] = "Ten en cuenta que algunos usuarios han podido enviar contenidos al sitio, tales como mensajes del foro, comentarios, fotos, etc.,
y Ã©stos serÃ¡n eliminados cuando los usuarios desactivados sean borrados.";
$locale['612'] = "usuario";
$locale['613'] = "usuarios";
$locale['614'] = "Desactivar";
$locale['615'] = "borrado permanentemente";
$locale['616'] = "anonimizar";
$locale['617'] = "<b>Aviso:</b>";
$locale['618'] = "Es muy recomendable cambiar la acciÃ³n de desactivaciÃ³n por la de anonimizaciÃ³n para evitar el borrado y la perdida de datos.";
$locale['619'] = "Puedes hacerlo aquÃ­.";
$locale['620'] = "anonimizar";
$locale['621'] = "DesactivaciÃ³n automÃ¡tica de los usuarios inactivos.";
