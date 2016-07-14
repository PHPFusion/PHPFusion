<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_tags.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

if (!function_exists("display_forum_tags")) {

    function display_forum_tags($info) {

        echo render_breadcrumbs();

        if (isset($_GET['tag_id'])) {
            // thread design




        } else {

            ?>
            <div class="row m-0">
                <?php if (!empty($info)) : ?>
                    <?php unset($info[0]) ?>
                    <?php foreach($info as $tag_id => $tag_data): ?>
                        <div class="col-xs-12 col-sm-4" style="height: 200px; max-height:200px; background-color: <?php echo $tag_data['tag_color'] ?>">
                            <a href="<?php echo $tag_data['tag_link'] ?>">
                                <div class="panel-body">
                                    <h4 class="text-white"><?php echo $tag_data['tag_title'] ?></h4>
                                    <p class="text-white"><?php echo $tag_data['tag_description'] ?></p>
                                </div>
                                <hr/>
                                <span class="tag_result text-white">
                                    <?php echo trim_text($tag_data['threads']['thread_subject'], 10)." - ".timer($tag_data['threads']['thread_lastpost']) ?>
                                </span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php
        }

    }
}