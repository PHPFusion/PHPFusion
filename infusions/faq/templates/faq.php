<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq.php
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

if (!function_exists("display_main_faq")) {
    /**
     * Page Template
     * @param $info
     */
    function display_main_faq($info) {
        $locale = fusion_get_locale();


        echo "<span id='content'></span>\n";
        opentable($locale['faqs_0000']);
        echo render_breadcrumbs();
		  //<!-- Diplay Categories -->
		echo "<div class='panel panel-default panel-faq-header' style='min-height: 50px;'>\n";
			echo "<div class='panel-body'>\n";
				echo "<div class='overflow-hide'><h3 class='display-inline text-dark'>".$locale['faqs_0001']."</h3></div>\n";
            	echo "<div id='content'>\n";
        		echo "<ul class='list-group'>\n";
        		echo "<li class='list-group-item'>\n";
			if (!empty($info['faq_categories'])) {
				foreach ($info['faq_categories'] as $cat_id => $cat_data) {
					if (!isset($_GET['cat_id']) || $_GET['cat_id'] != $cat_id) {
					echo "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-4'>\n";
					echo "<a href='".INFUSIONS."faq/faq.php?cat_id=".$cat_id."' title=".$cat_data['faq_question'].">".$cat_data['faq_question']."</a>";
	    			echo "</div>\n";
					}
				}
			}
					echo "</li>\n";
        			echo "</ul>\n";
        		echo "</div>\n";
        	echo "</div>\n";
		echo "</div>\n";

		echo "<div class='row'>\n";
		display_faq_category($info['faq_items'],$info['faq_get']);
		echo "</div>\n";
        closetable();
    }
}

	function display_faq_category($data, $id = 0, $level = 0) {
		$locale = fusion_get_locale();
		if (!empty($data[$id])) {
			echo "<div class='panel-group' id='accordion'>";
			foreach ($data[$id] as $i => $faq_info) {
				echo "<div id ='faqs".$faq_info['faq_id']."' class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
					echo "<faq class='panel panel-default clearfix'>\n";
					echo "<div class='panel-body'>\n";
        				echo "<div class='pull-right'>
        				<a class='pull-right' href='".FUSION_REQUEST."#content' title=".$locale['faqs_0002']."><i class='fa fa-arrow-up'></i></a>\n</div>\n";
						echo "<h4 class='panel-title'>";
						echo "<a data-toggle='collapse' data-parent='#accordion' href='#collapse".$faq_info['faq_id']."'>".str_repeat("-", $level)." ".$faq_info['faq_question']."</a>";
						echo "</h4>";
    					echo "<div id='collapse".$faq_info['faq_id']."' class='panel-collapse collapse'>";
    					echo "<div class='panel-body'>".$faq_info['faq_answer']."</div>";
                			echo "<div class='panel-footer'>\n";

							echo "<a href='".BASEDIR."print.php?type=FQ&amp;item_id=".$faq_info['faq_id']."' title=".$locale['print']."><i class='fa fa-fw fa-print m-l-10'></i></a>\n";
						if (iADMIN && checkrights("FQ")) {
							echo "<a href='".INFUSIONS."faq/faq_admin.php".fusion_get_aidlink()."&amp;section=faq&amp;ref=faq_form&amp;action=edit&amp;cat_id=".$faq_info['faq_cat_id']."&amp;faq_id=".$faq_info['faq_id']."' title='".$locale['edit']."'><i class='fa fa-fw fa-pencil m-l-10'></i></a>";
						}

							echo "</div>\n";  //footer
    					echo "</div>\n"; //collapse
    				echo "</div>\n";  //panel-budy
 					echo "</faq>\n";
	    		echo "</div>\n";
                if (isset($data[$faq_info['faq_id']])) {
                    display_faq_category($data, $faq_info['faq_id'], $level +1);
                }
             }
			echo "</div>\n";
		} else {
			echo "<div class='well text-center'>".$locale['faqs_0010']."</div>";

    	}
    }
