<?php
// Delete blacklisted user
$locale['400'] = "Borrar";
$locale['401'] = "Usuario eliminado de expulsiones";
$locale['402'] = "Volver a la Administración de Expulsiones";
$locale['403'] = "Volver a Administración";
$locale['404'] = "Por favor, introduzca Lista negra IP o Lista negra de correo electrónico";
$locale['405'] = "Dirección de correo electrónico de la lista negra no es válida de correo electrónico.";
$locale['406'] = "Lista negra";
// Add/Edit Blacklist Titles
$locale['420'] = "Expulsiones";
$locale['421'] = "Editar Usuario Expulsado";
// Add/Edit blacklist form
$locale['440'] = "Introducción de una dirección IP evitará que un usuario cuya dirección IP coincide con la entrada de visitar este sitio.
Puede introducir una IP completa, por ejemplo, <em>123.45.67.89.</em>, o una IP parcial, por ejemplo, <em>123.45.67</em> o <em>123,45</em>.
Nota: Las direcciones IPv6 se convierten a su forma de cuerpo entero en este sitio,
por ejemplo <em>ABCD:1234:5:6:7:8:9:FF</em> se mostrará como <em>ABCD:1234:0005:0006:0007:0008:0009:00FF</em>.
Direcciones IP mixtos (aquellos que contienen ambos parte IPv6 y IPv4) no se comprobará la coincidencia parcial.
<br /> <br />
Introducción de una dirección de correo electrónico va a impedir que los miembros se registren usando esa dirección.
Puede introducir una dirección de correo electrónico completa, por ejemplo, <em>foo@bar.com</em>, o un dominio de correo electrónico, por ejemplo, <em>bar.com</em>. <br /> <br />

% - Coincide con cualquier cadena <br /> <br />.

%.%.%.%@dominio.tld prohíbe cualquier dirección que contiene al menos 3 puntos. <br />
%+%@dominio.tld prohíbe cualquier dirección que contiene al menos un signo más. <br />
%@dominio.tld prohíbe cualquier dirección de <br /> dominio.tld
%.dominio.tld prohíbe todos los subdominios de <br /> dominio.tld
%payday% prohíbe cualquier dirección que contiene la palabra \"payday\" que era muy a menudo en los sitios. <br />
dominio.tld es un alias de %@dominio.tld para que sea compatible con las normas definidas en v7 <br />. ";

$locale['441'] = "Expulsar Dirección IP:";
$locale['442'] = "<b>o</b> Expulsar Dirección o Dominio de Email:";
$locale['443'] = "Motivo de la Expulsión";
$locale['444'] = "Expulsar";
// Current blacklisted users
$locale['460'] = "Expulsiones Existentes";
$locale['461'] = "Información de la Expulsión";
$locale['462'] = "Opciones";
$locale['463'] = "Editar";
$locale['464'] = "Borrar";
$locale['465'] = "No hay expulsiones.";
$locale['466'] = "N/D";
$locale['467'] = "Administrador";
$locale['468'] = "Fecha";
