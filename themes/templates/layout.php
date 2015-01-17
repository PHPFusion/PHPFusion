<?php

/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: footer.php
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
?>
<!DOCTYPE html>
<head>
	<title><?php echo $settings['sitename'] ?></title>
	<meta charset='<?php echo $locale['charset'] ?>' />
	<meta name='description' content='<?php echo $settings['description'] ?>' />
	<meta name='keywords' content='<?php echo $settings['keywords'] ?>' />
	<?php if ($bootstrap_theme_css_src) : ?>
	<meta http-equiv='X-UA-Compatible' content='IE=edge' />
	<meta name='viewport' content='width=device-width, initial-scale=1.0' />
	<link href='<?php echo $bootstrap_theme_css_src ?>' rel='stylesheet' media='screen' />
	<?php endif; ?>
	<!-- Entypo icons -->
	<link href='<?php echo INCLUDES.'font/entypo/entypo.css' ?>' rel='stylesheet' media='screen' />
	<!-- Default CSS styling which applies to all themes but can be overriden -->
	<link href='<?php echo THEMES.'templates/default.css' ?>' rel='stylesheet' type='text/css' media='screen' />
	<!-- Theme CSS -->
	<link href='<?php echo THEME.'styles.css' ?>' rel='stylesheet' type='text/css' media='screen' />
	<?php echo render_favicons(IMAGES);
	if (function_exists("get_head_tags")) {
		echo get_head_tags();
	} ?>
	<script type='text/javascript' src='<?php echo INCLUDES.'jquery/jquery.js' ?>'></script>
	<script type='text/javascript' src='<?php echo INCLUDES.'jscript.js' ?>'></script>
</head>
<body>
	<?php
	defined('ADMIN_PANEL') ? render_adminpanel() : render_page();
	
	// Output lines added with add_to_footer()
	echo $fusion_page_footer_tags;
	
	?>
	<div class='admin-message'><?php echo $footerError ?></div>
	<?php if (!empty($fusion_jquery_tags)) : ?>
	<script type="text/javascript">
		$(function() {
			<?php echo $fusion_jquery_tags; // Output lines added with add_to_jquery() ?>
		});
	</script>
	<?php endif;
	// End HTML document
	?>
</body>
</html>