<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/templates/weblinks.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (!function_exists("display_main_weblink")) {
    /**
     * Weblink Page Template
     * @param $info
     */
    function display_main_weblink($info) {

        $weblink_settings = \PHPFusion\Weblinks\WeblinksServer::get_weblink_settings();
        $locale = fusion_get_locale("", WEBLINK_LOCALE);

        add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.cookie.js'></script>");

        $cookie_expiry = time() + 7 * 24 * 3600;
        if (empty($_COOKIE['fusion_weblink_view'])) {
            setcookie("fusion_weblink_view", 1, $cookie_expiry);
        } elseif (isset($_POST['switchview']) && isnum($_POST['switchview'])) {
            setcookie("fusion_weblink_view", intval($_POST['switchview'] == 2 ? 2 : 1), $cookie_expiry);
            redirect(FUSION_REQUEST);
        }

        opentable($locale['web_0000']);
        echo render_breadcrumbs();
		?>

		<div class="panel panel-default panel-weblink-header">

		  <!-- Display Informations -->
		  <div class="panel-body">
		    <div class="pull-right">
			  <a class="btn btn-sm btn-default" href="<?php echo INFUSIONS."weblinks/weblinks.php"; ?>" title="<?php echo $locale['web_0001']; ?>"><i class="fa fa-fw fa-desktop"></i> <?php echo $locale['web_0001']; ?></a>
			  <button type="button" class="btn btn-sm btn-primary" data-toggle="collapse" data-target="#weblinkscat" aria-expanded="true" aria-controls="weblinkscat" title="<?php echo $locale['web_0002']; ?>">
			    <i class="fa fa-fw fa-folder"></i>
			  </button>
			</div>
			<div class="overflow-hide">
			  <h3 class="display-inline text-dark"><?php echo $info['weblink_cat_name']; ?></h3><br />
			  <?php if ($info['weblink_cat_description']) { ?>
			  <div class="weblink-cat-description"><?php echo $info['weblink_cat_description']; ?></div>
			  <br />
			  <?php } ?>
			  <span class="strong text-smaller"><?php echo $locale['web_0004']; ?></span>
			  <span class="text-dark text-smaller"><?php echo ($info['weblink_last_updated'] > 0 ? showdate("newsdate", $info['weblink_last_updated']) : $locale['na']); ?></span>
			</div>
		  </div>

		  <!-- Diplay Categories -->
		  <div id="weblinkscat" class="panel-collapse collapse m-b-10">
		    <!--pre_weblinks_cat_idx-->
			<ul class="list-group">
			  <li class="list-group-item">
				<hr class="m-t-0 m-b-5">
				<span class="display-inline-block m-b-10 strong text-smaller text-uppercase"><?php echo $locale['web_0003']; ?></span><br />
				<?php
					if (is_array($info['weblink_categories']) && !empty($info['weblink_categories'])) {
						foreach ($info['weblink_categories'] as $cat_id => $cat_data) {
							if (!isset($_GET['cat_id']) || $_GET['cat_id'] != $cat_id) {
								echo "<a href='".$cat_data['link']."' title='".(!empty($cat_data['description']) ? $cat_data['description'] : "")."' class='btn btn-sm btn-default m-5'>".$cat_data['name']."<p>".format_word($cat_data['count'], $locale['fmt_weblink'])."</p></a>";
							}
						}
					} else {
						echo "<div class='well text-center'>".$locale['web_0060']."</div>";
					}
				?>
			  </li>
			</ul>
		    <!--sub_weblinks_cat_idx-->
		  </div>
		</div>

	    <!-- Display Sorting Options -->
	    <div class="row m-t-20 m-b-20">
	      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

		    <!-- Display View Options -->
		    <?php echo openform("viewform", "post", FUSION_REQUEST, array("max_tokens" => 1, "class" => "pull-right display-inline-block m-l-10")); ?>
			<div class="btn-group">
			  <?php $active = isset($_COOKIE['fusion_weblink_view']) && isnum($_COOKIE['fusion_weblink_view']) && $_COOKIE['fusion_weblink_view'] == 2 ? 2 : 1; ?>
			  <?php echo form_button("switchview", "", "1", array("class" => "btn-sm btn-default nsv".($active == "1" ? " active" : ""), "icon" => "fa fa-fw fa-th-large", "alt" => $locale['web_0040'])); ?>
			  <?php echo form_button("switchview", "", "2", array("class" => "btn-sm btn-default nsv".($active == "2" ? " active" : ""), "icon" => "fa fa-fw fa-bars", "alt" => $locale['web_0041'])); ?>
	        </div>
			<?php echo closeform(); ?>

			<!-- Display Filters -->
			<div class="display-inline-block">
			  <span class="text-dark strong m-r-10"><?php echo $locale['show']; ?></span>
			  <?php $i = 0;
				foreach ($info['weblink_filter'] as $link => $title) {
					$filter_active = (!isset($_GET['type']) && $i == '0') || isset($_GET['type']) && stristr($link, $_GET['type']) ? "text-dark strong" : "";
					echo "<a href='".$link."' class='display-inline $filter_active m-r-10'>".$title."</a>";
					$i++;
				}
			  ?>
			</div>
		  </div>
		</div>

		<!-- Display Weblink -->
	    <?php
        $weblinkColumn = $active == 2 ? 12 : 4;
        if (!empty($info['weblink_items'])) {
		?>
			<div class="row">
				<?php foreach ($info['weblink_items'] as $i => $weblink_info) {?>
					<div class="col-xs-12 col-sm-<?php echo $weblinkColumn; ?> col-md-<?php echo $weblinkColumn; ?> col-lg-<?php echo $weblinkColumn; ?>">
					<?php echo (isset($_GET['cat_id'])) ? "<!--pre_weblink_cat_idx-->\n" : "<!--weblink_prepost_".$i."-->\n"; ?>
					<?php render_weblink($weblink_info['weblink_name'], $weblink_info['weblink_description'], $weblink_info, ($active == 2 ? true : false)); ?>
					<?php echo (isset($_GET['cat_id'])) ? "<!--sub_weblink_cat_idx-->" : "<!--sub_weblink_idx-->\n"; ?>
					</div>
				<?php } ?>
			</div>
			<?php
            if ($info['weblink_total_rows'] > $weblink_settings['links_per_page']) {
                $type_start = isset($_GET['type']) ? "type=".$_GET['type']."&amp;" : "";
                $cat_start = isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : ""; ?>
				<div class="text-center m-t-10 m-b-10">
				<?php echo makepagenav($_GET['rowstart'], $weblink_settings['links_per_page'], $info['weblink_total_rows'], 3, INFUSIONS."weblinks/weblinks.php?".$cat_start.$type_start); ?>
				</div>
            <?php
			}
        } else { ?>
			<div class="well text-center"><?php echo (isset($_GET['cat_id']) ? $locale['web_0062'] : $locale['web_0061']); ?></div>
        <?php
		}
        closetable();
    }
}

if (!function_exists("render_weblink")) {
    /**
     * Weblink Item Container
     * @param      $info
     * @param bool $list_view
     */
    function render_weblink($weblink, $desc, $info, $list_view = FALSE) {

        $locale = fusion_get_locale("", WEBLINK_LOCALE);

		// List
		if ($list_view) {
		?>

			<article class="panel panel-default clearfix" style="min-height: 150px;">
			  <div class="panel-body">

				<h4 class="weblink-title panel-title">
				  <a href="<?php echo INFUSIONS."weblinks/weblinks.php?weblink_id=".$info['weblink_id']; ?>" class="text-dark strong"><?php echo $weblink; ?></a>
				</h4>

				<div class="weblink-text m-t-10">
				  <?php echo $desc; ?>
				</div>
				<hr />

				<div class="weblink-footer m-t-5">

				  <i class="fa fa-fw fa-folder"></i>
				  <a href="<?php echo INFUSIONS."weblinks/weblinks.php?cat_id=".$info['weblink_cat']; ?>" title="<?php echo $info['weblink_cat_name']; ?>"><?php echo $info['weblink_cat_name']; ?></a>

				  <i class="fa fa-fw fa-eye m-l-10"></i> <?php echo format_word($info['weblink_count'], $locale['fmt_open']); ?>
				  <i class="fa fa-fw fa-upload m-l-10"></i> <?php echo showdate("shortdate",$info['weblink_datestamp']); ?>


				  <?php if (!empty($info['admin_actions'])) { ?>
					<a href="<?php echo $info['admin_actions']['edit']['link']; ?>" title="<?php echo $info['admin_actions']['edit']['title']; ?>"><i class="fa fa-fw fa-pencil m-l-10"></i> <?php echo $locale['edit']; ?></a>
					<a href="<?php echo $info['admin_actions']['delete']['link']; ?>" title="<?php echo $info['admin_actions']['delete']['title']; ?>"><i class="fa fa-fw fa-trash m-l-10"></i> <?php echo $locale['delete']; ?></a>
				  <?php } ?>

				</div>
			  </div>
			</article>

		<?php
		// Gallery
        } else {
		?>

			<!--articles_prepost_<?php echo $info['weblink_id']; ?>-->
			<article class="panel panel-default" style="min-height: 50px;">
			  <div class="panel-body">

				<h4 class="weblink_title panel-title">
				  <a href="<?php echo INFUSIONS."weblinks/weblinks.php?weblink_id=".$info['weblink_id']; ?>" class="text-dark strong"><?php echo $weblink; ?></a>
				</h4>

				<div class="weblink_text m-t-5" style="height: 50px;">
				  <?php echo trim_text(strip_tags($desc), 250); ?>
				</div>

				<div class="weblink-category m-t-5">
				  <i class="fa fa-fw fa-folder"></i>
				  <a href="<?php echo INFUSIONS."weblinks/weblinks.php?cat_id=".$info['weblink_cat']; ?>" title="<?php echo $info['weblink_cat_name']; ?>"><?php echo $info['weblink_cat_name']; ?></a>
				</div>
			  </div>

			  <div class="weblink-footer panel-footer">
				<i class="fa fa-fw fa-eye m-l-10"></i> <?php echo $info['weblink_count']; ?>
				  <i class="fa fa-fw fa-upload m-l-10"></i> <?php echo showdate("shortdate",$info['weblink_datestamp']); ?>


				<?php if (!empty($info['admin_actions'])) { ?>
					<a href="<?php echo $info['admin_actions']['edit']['link']; ?>" title="<?php echo $info['admin_actions']['edit']['title']; ?>"><i class="fa fa-fw fa-pencil m-l-10"></i></a>
					<a href="<?php echo $info['admin_actions']['delete']['link']; ?>" title="<?php echo $info['admin_actions']['delete']['title']; ?>"><i class="fa fa-fw fa-trash m-l-10"></i></a>
				<?php } ?>
			  </div>
			</article>
		<?php
        }
    }
}
