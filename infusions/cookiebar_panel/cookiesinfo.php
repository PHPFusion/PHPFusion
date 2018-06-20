<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: cookiebar_panel/cookiesinfo.php
| Author: PHP-Fusion Development Team
| Co-Author: Joakim Falk (Domi)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../../maincore.php';

$locale = fusion_get_locale("", COOKIE_LOCALE);

echo "<h1>".$locale['CBP104']."</h1>
<div class='table-responsive'><table class='table table-hover table-striped'>
<thead>
<tr>
<th class='text-center'>".$locale['CBP105']."</th>
<th class='text-center'>".$locale['CBP106']."</th>
<th class='text-center'>".$locale['CBP107']."</th>
<th class='text-center'>".$locale['CBP108']."</th>
<th class='text-center'>".$locale['CBP109']."</th>
</tr>
</thead><tbody>
<tr>
<th colspan='6' class='text-center'>".$locale['CBP110']."</th>
</tr>
<tr>
<td>".$locale['CBP111']."</td>
<td>".$locale['CBP112']."</td>
<td>".$locale['CBP113']."</td>
<td>".$locale['CBP114']."</td>
<td>".$locale['CBP115']."</td>
</tr>
<tr>
<th colspan='6' class='text-center'>".$locale['CBP116']."</th>
</tr>
<tr>
<td class='text-center'>".$locale['CBP117']."</td>
<td>".$locale['CBP118']."</td>
<td>".$locale['CBP119']."</td>
<td>".$locale['CBP114']."</td>
<td>".$locale['CBP120']."</td>
</tr>
<th colspan='6' class='text-center'>".$locale['CBP121']."</th>
</tr>
<tr>
<td class='text-center'>".$locale['CBP122']."</td>
<td>".$locale['CBP123']."</td>
<td>".$locale['CBP124']."</td>
<td>".$locale['CBP125']."</td>
<td>".$locale['CBP126']."</td>
</tr>
<tr>
<th colspan='6' class='text-center'>".$locale['CBP127']."</th>
</tr>
<tr>
<td class='text-center'>".COOKIE_PREFIX."".$locale['CBP128']."</td>
<td>".$locale['CBP129']."</td>
<td>".$locale['CBP130']."</td>
<td>".$locale['CBP114']."</td>
<td>".$locale['CBP131']."</td>
</tr>
<tr>
<td class='text-center'>".$locale['CBP132']."</td>
<td>".$locale['CBP133']."</td>
<td>".$locale['CBP134']."</td>
<td>".$locale['CBP114']."</td>
<td>".$locale['CBP131']."</td>
</tr>
<tr>
<th colspan='6' class='text-center'>".$locale['CBP135']."</th>
</tr>
<tr>
<td class='text-center'>".COOKIE_PREFIX."".$locale['CBP136']."</td>
<td>".$locale['CBP137']."</td>
<td>".$locale['CBP138']."</td>
<td>".$locale['CBP139']."</td>
<td>".$locale['CBP140']."</td>
</tr>
<tr>
<td class='text-center'>".COOKIE_PREFIX."".$locale['CBP141']."</td>
<td>".$locale['CBP142']."</td>
<td>".$locale['CBP143']."</td>
<td>".$locale['CBP139']."</td>
<td>".$locale['CBP140']."</td>
</tr>
<tr>
<td class='text-center'>".COOKIE_PREFIX."".$locale['CBP144']."</td>
<td>".$locale['CBP145']."</td>
<td>".$locale['CBP146']."</td>
<td>".$locale['CBP147']."</td>
<td>".$locale['CBP140']."</td>
</tr></tbody></table></div>";
