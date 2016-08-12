<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: Network/ComposeEngine.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion\Page\Composer\Network;

use PHPFusion\Page\PageAdmin;

class ComposeEngine extends PageAdmin {

    public static function displayContent() {

        // This is the composer
        echo form_button('add_row', 'Add Row', 'add row', array(
            'class' => 'btn-primary m-r-10'
        ));
        ?>

        <section id='pageComposerLayout' class="m-t-20">

            <div class="well">

                <div class="pull-right sortable btn btn-xs m-r-10 m-b-10 display-inline-block">
                    <i class="fa fa-arrows-alt"></i>
                </div>


                <div class="btn-group btn-group-sm m-b-10">
                    <?php
                    echo form_button('add_compo', '', 'add_compo',
                                     array('icon' => 'fa fa-dashboard', 'alt' => 'Add Component')).
                        form_button('add_col', '', 'add_col',
                                    array('icon' => 'fa fa-plus-circle', 'alt' => 'Add Column')).
                        form_button('set_prop', '', 'set_prop',
                                    array('icon' => 'fa fa-cog', 'alt' => 'Configure Properties'));
                    ?>
                </div>

                <div class="btn-group btn-group-sm m-b-10">
                    <?php
                    echo form_button('copy_row', '', 'copy_row',
                                     array('icon' => 'fa fa-copy', 'alt' => 'Duplicate Row')).
                        form_button('del_col', '', 'del_col',
                                    array('icon' => 'fa fa-minus-circle', 'alt' => 'Remove Column')).
                        form_button('del_row', '', 'del_row',
                                    array('class' => 'btn-danger', 'icon' => 'fa fa-trash', 'alt' => 'Delete Row'));
                    ?>
                </div>


                <div class="list-group-item m-t-10">
                    <div class='text-center'>Add Content</div>
                </div>
            </div>


        </section>

        <?php

        add_to_jquery("
			$('#delete').bind('click', function() { confirm('".self::$locale['450']."'); });
			$('#save').bind('click', function() {
			var page_title = $('#page_title').val();
			if (page_title =='') { alert('".self::$locale['451']."'); return false; }
			});
		");
        if (fusion_get_settings('tinymce_enabled')) {
            add_to_jquery("
			function SetTinyMCE(val) {
			now=new Date();\n"."now.setTime(now.getTime()+1000*60*60*24*365);
			expire=(now.toGMTString());\n"."document.cookie=\"custom_pages_tinymce=\"+escape(val)+\";expires=\"+expire;
			location.href='".FUSION_SELF.fusion_get_aidlink()."&section=cp2';
			}
		    ");
        }
    }

}