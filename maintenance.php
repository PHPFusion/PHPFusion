<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: maintenance.php
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "maincore.php";
include THEME."theme.php";

if (!$settings['maintenance']) {
	redirect("index.php");
}
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='<?php echo $locale['xml_lang'] ?>' lang='<?php echo $locale['xml_lang'] ?>'>
<head>
	<title><?php echo $settings['sitename'] ?></title>
	<meta http-equiv='Content-Type' content='text/html; charset=<?php echo $locale['charset'] ?>' />
	<meta name='description' content='<?php echo $settings['description'] ?>' />
	<meta name='keywords' content='<?php echo $settings['keywords'] ?>' />
	<style type='text/css'>html, body { height:100%; }</style>
	<link rel='stylesheet' href='<?php echo THEME ?>styles.css' type='text/css' media='screen'/>
	<link rel='shortcut icon' href='<?php echo IMAGES ?>favicon.ico' type='image/x-icon' />
</head>
<body class='maintenance'>
<table style='width:100%;height:100%'>
	<tr>
		<td>
			<table cellpadding='0' cellspacing='1' width='80%' class='tbl-border center'>
				<tr>
					<td class='tbl1'>
						<div style='text-align:center'>
							<br />
							<img src='<?php echo BASEDIR.$settings['sitebanner'] ?>' alt='<?php echo $settings['sitename'] ?>' /><br /><br />
							<?php echo stripslashes(nl2br($settings['maintenance_message'])) ?><br /><br />
							Powered by <a href='http://www.php-fusion.co.uk'>PHP-Fusion</a> &copy; 2003 - <?php echo date("Y") ?><br /><br />
						</div>
					</td>
				</tr>
			</table>
			<?php if (!iMEMBER) : ?>
				<div align='center'>
					<br />
					<form name='loginform' method='post' action='<?php echo $settings['opening_page'] ?>'>
						<?php echo $locale['global_101'] ?>: <input type='text' name='user_name' class='textbox' style='width:100px' />
						<?php echo $locale['global_102'] ?>: <input type='password' name='user_pass' class='textbox' style='width:100px' />
						<input type='checkbox' name='remember_me' value='y' title='<?php echo $locale['global_103'] ?>' />
						<input type='submit' name='login' value='<?php echo $locale['global_104'] ?>' class='button' />
					</form>
				</div>
			<?php endif ?>
		</td>
	</tr>
</table>
</body>
</html>