<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/templates/forum_tags.php
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

if (!function_exists("display_forum_tags")) {

    function display_forum_tags($info) {
        add_to_head("<link rel='stylesheet' type='text/css' href='".INFUSIONS."forum/templates/css/forum.css'>");
        $locale = fusion_get_locale();

        echo render_breadcrumbs();

        if (isset($_GET['tag_id'])) {

            // thread design
            echo "<!--pre_forum-->\n";
            echo "<div class='forum-title m-t-20'>".$locale['forum_0341']."</div>\n";

            forum_filter($info);

            if (!empty($info['threads']['pagenav'])) {
                echo "<div class='text-right'>\n";
                echo $info['threads']['pagenav'];
                echo "</div>\n";
            }

            if (!empty($info['threads'])) {
                echo "<div class='forum-container list-group-item'>\n";
                if (!empty($info['threads']['sticky'])) {
                    foreach ($info['threads']['sticky'] as $cdata) {
                        render_thread_item($cdata);
                    }
                }
                if (!empty($info['threads']['item'])) {
                    foreach ($info['threads']['item'] as $cdata) {
                        render_thread_item($cdata);
                    }
                }
                echo "</div>\n";
            } else {
                echo "<div class='text-center'>".$locale['forum_0269']."</div>\n";
            }

            if (!empty($info['threads']['pagenav'])) {
                echo "<div class='text-right hidden-xs m-t-15'>\n";
                echo $info['threads']['pagenav'];
                echo "</div>\n";
            }

            if (!empty($info['threads']['pagenav2'])) {
                echo "<div class='hidden-sm hidden-md hidden-lg m-t-15'>\n";
                echo $info['threads']['pagenav2'];
                echo "</div>\n";
            }


        } else {

            ?>
            <div class="row m-0">
                <?php if (!empty($info['tags'])) : ?>
                    <?php unset($info['tags'][0]) ?>
                    <?php foreach ($info['tags'] as $tag_id => $tag_data): ?>
                        <div class="col-xs-12 col-sm-4"
                             style="height: 200px; max-height:200px; background-color: <?php echo $tag_data['tag_color'] ?>">
                            <a href="<?php echo $tag_data['tag_link'] ?>">
                                <div class="panel-body">
                                    <h4 class="text-white"><?php echo $tag_data['tag_title'] ?></h4>

                                    <p class="text-white"><?php echo $tag_data['tag_description'] ?></p>
                                </div>
                                <hr/>
                                <?php if (!empty($tag_data['threads'])) : ?>
                                    <span class="tag_result text-white">
                                    <?php echo trim_text($tag_data['threads']['thread_subject'],
                                                         10)." - ".timer($tag_data['threads']['thread_lastpost']) ?>
                                </span>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php
        }

    }
}