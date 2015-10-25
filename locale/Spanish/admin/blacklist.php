<?php
// Delete blacklisted user
$locale['400'] = "Borrar";
$locale['401'] = "Usuario eliminado de expulsiones";
$locale['402'] = "Volver a la AdministraciÃ³n de Expulsiones";
$locale['403'] = "Volver a AdministraciÃ³n";
$locale['404'] = "Por favor, introduzca Lista negra IP o Lista negra de correo electrÃ³nico";
$locale['405'] = "DirecciÃ³n de correo electrÃ³nico de la lista negra no es vÃ¡lida de correo electrÃ³nico.";
$locale['406'] = "Lista negra";
// Add/Edit Blacklist Titles
$locale['420'] = "Expulsiones";
$locale['421'] = "Editar Usuario Expulsado";
// Add/Edit blacklist form
$locale['440'] = "IntroducciÃ³n de una direcciÃ³n IP evitarÃ¡ que un usuario cuya direcciÃ³n IP coincide con la entrada de visitar este sitio.
Puede introducir una IP completa, por ejemplo, <em>123.45.67.89.</em>, o una IP parcial, por ejemplo, <em>123.45.67</em> o <em>123,45</em>.
Nota: Las direcciones IPv6 se convierten a su forma de cuerpo entero en este sitio,
por ejemplo <em>ABCD:1234:5:6:7:8:9:FF</em> se mostrarÃ¡ como <em>ABCD:1234:0005:0006:0007:0008:0009:00FF</em>.
Direcciones IP mixtos (aquellos que contienen ambos parte IPv6 y IPv4) no se comprobarÃ¡ la coincidencia parcial.
<br /> <br />
IntroducciÃ³n de una direcciÃ³n de correo electrÃ³nico va a impedir que los miembros se registren usando esa direcciÃ³n.
Puede introducir una direcciÃ³n de correo electrÃ³nico completa, por ejemplo, <em>foo@bar.com</em>, o un dominio de correo electrÃ³nico, por ejemplo, <em>bar.com</em>. <br /> <br />

% - Coincide con cualquier cadena <br /> <br />.

%.%.%.%@dominio.tld prohÃ­be cualquier direcciÃ³n que contiene al menos 3 puntos. <br />
%+%@dominio.tld prohÃ­be cualquier direcciÃ³n que contiene al menos un signo mÃ¡s. <br />
%@dominio.tld prohÃ­be cualquier direcciÃ³n de <br /> dominio.tld
%.dominio.tld prohÃ­be todos los subdominios de <br /> dominio.tld
%payday% prohÃ­be cualquier direcciÃ³n que contiene la palabra \"payday\" que era muy a menudo en los sitios. <br />
dominio.tld es un alias de %@dominio.tld para que sea compatible con las normas definidas en v7 <br />. ";

$locale['441'] = "Expulsar DirecciÃ³n IP:";
$locale['442'] = "<b>o</b> Expulsar DirecciÃ³n o Dominio de Email:";
$locale['443'] = "Motivo de la ExpulsiÃ³n";
$locale['444'] = "Expulsar";
// Current blacklisted users
$locale['460'] = "Expulsiones Existentes";
$locale['461'] = "InformaciÃ³n de la ExpulsiÃ³n";
$locale['462'] = "Opciones";
$locale['463'] = "Editar";
$locale['464'] = "Borrar";
$locale['465'] = "No hay expulsiones.";
$locale['466'] = "N/D";
$locale['467'] = "Administrador";
$locale['468'] = "Fecha";
