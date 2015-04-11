<?php

/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin_layout.php
| Author: Takács Ákos (Rimelek)
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
	<meta charset='<?php echo $locale['charset'] ?>'/>
	<?php if ($bootstrap_theme_css_src) : ?>
		<meta http-equiv='X-UA-Compatible' content='IE=edge'/>
		<meta name='viewport' content='width=device-width, initial-scale=1.0'/>
		<link href='<?php echo $bootstrap_theme_css_src ?>' rel='stylesheet' media='screen'/>
	<?php endif ?>
	<!-- Entypo icons -->
	<link href='<?php echo INCLUDES."font/entypo/entypo.css" ?>' rel='stylesheet' media='screen'/>
	<!-- Default CSS styling which applies to all themes but can be overriden -->
	<link href='<?php echo THEMES."templates/default.css" ?>' rel='stylesheet' type='text/css' media='screen'/>
	<!-- Admin Panel Theme CSS -->
	<link href='<?php echo THEMES."admin_templates/".$settings['admin_theme']."/acp_styles.css" ?>' rel='stylesheet'
		  type='text/css' media='screen'/>
	<?php
	echo render_favicons(IMAGES);
	if (function_exists("get_head_tags")) {
		echo get_head_tags();
	}
	?>
	<script type='text/javascript' src='<?php echo INCLUDES."jquery/jquery.js" ?>'></script>
	<script type='text/javascript' src='<?php echo INCLUDES."jscript.js" ?>'></script>
	<script type='text/javascript' src='<?php echo INCLUDES."jquery/admin-msg.js" ?>'></script>
	<?php if (fusion_get_settings('tinymce_enabled')) : ?>
		<style type='text/css'>
			.mceIframeContainer iframe {
				width: 100% !important;
			}
		</style>
		<script type='text/javascript' src='<?php echo INCLUDES."jscripts/tinymce/tinymce.min.js" ?>'></script>
		<script type='text/javascript'>
			function advanced() {
				tinymce.init({
					selector: 'textarea',
					theme: 'modern',
					entity_encoding: 'raw',
					relative_urls: false,
					language: '<?php echo $locale['tinymce'] ?>',
					width: '100%',
					height: 300,
					plugins: [
						'advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker',
						'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
						'save table contextmenu directionality emoticons template paste textcolor'
					],
					image_list: <?php echo $tinymce_list ?>,
					document_base_url: '<?php echo fusion_get_settings('site_path') ?>',
					content_css: '<?php echo THEME.'styles.css' ?>',
					toolbar1: 'insertfile undo redo | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | newdocument fullscreen preview cut copy paste pastetext spellchecker searchreplace code',
					toolbar2: 'styleselect formatselect removeformat | fontselect fontsizeselect bold italic underline strikethrough subscript superscript blockquote | forecolor backcolor',
					toolbar3: 'hr pagebreak insertdatetime | link unlink anchor | image media | table charmap visualchars visualblocks emoticons',
					image_advtab: true,
					style_formats: [
						{title: 'Bold text', inline: 'b'},
						{title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
						{title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
						{title: 'Example 1', inline: 'span', classes: 'example1'},
						{title: 'Example 2', inline: 'span', classes: 'example2'},
						{title: 'Table styles'},
						{title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
					]
				});
			}

			function simple() {
				tinymce.init({
					selector: 'textarea',
					theme: 'modern',
					entity_encoding: 'raw',
					language: '<?php echo $locale['tinymce'] ?>'
				});
			}

			function toggleEditor(id) {
				if (!tinyMCE.get(id)) {
					tinyMCE.execCommand('mceAddControl', false, id);
				} else {
					tinyMCE.execCommand('mceRemoveControl', false, id);
				}
			}
		</script>
	<?php endif ?>
</head>
<body>
<?php
open_ap();
// Output lines added with add_to_footer()
echo $fusion_page_footer_tags;
if ($footerError) : ?>
	<div class='alert alert-warning m-t-10 error-message'><?php echo $footerError ?></div>
<?php
endif;
close_ap();
if (!empty($fusion_jquery_tags)) : ?>
	<script type="text/javascript">
		$(function () {
			<?php echo $fusion_jquery_tags; // Output lines added with add_to_jquery() ?>
		});
	</script>
<?php endif; ?>
</body>
</html>