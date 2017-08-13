<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: messages.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (!function_exists('display_inbox')) {

    function display_inbox($info) {
        $locale = fusion_get_locale();
        /**
         * Message Reader Functions for Inbox, Outbox, Archive
         * @param $info
         */
        function _inbox($info) {
            $locale = fusion_get_locale();
            if (isset($_GET['msg_read']) && isset($info['items'][$_GET['msg_read']])) : // read view
                $data = $info['items'][$_GET['msg_read']];
                echo '
                <h4>'.$data['message']['message_header'].'</h4>
                <div class="clearfix m-t-20 m-b-20">
                    <div class="pull-left m-r-15">'.display_avatar($data, "40px", '', FALSE, 'img-circle').'</div>
                    <div class="overflow-hide">
                        '.profile_link($data['user_id'], $data['user_name'], $data['user_status']).'<br/>
                        '.showdate($locale['date_day'], $data['message_datestamp']).', '.timer($data['message_datestamp']).'
                    </div>
                </div>
                '.nl2br(parse_textarea($data['message']['message_text'], TRUE, TRUE, FALSE)).'
                <hr/>
                '.$info['reply_form'];

            elseif (isset($_GET['msg_send'])) : // send new message form
                echo $info['reply_form'];
            else : // display view

                $is_inbox = isset($_GET['folder']) && $_GET['folder'] == 'inbox' ? TRUE : FALSE;
                // Outbox has no unread and read.
                // It's a straight

                if (!empty($info['items'])) {
                    $unread = array();
                    $read = array();
                    if ($is_inbox) {
                        foreach ($info['items'] as $message_id => $messageData) {
                            if ($messageData['message_read']) {
                                $read[$message_id] = $messageData;
                            } else {
                                $unread[$message_id] = $messageData;
                            }
                        }
                    } else {
                        foreach ($info['items'] as $message_id => $messageData) {
                            $read[$message_id] = $messageData;
                        }
                    }

                    if ($is_inbox) {
                        echo '<h4><a data-target="#unread_inbox" class="pointer text-dark" data-toggle="collapse">'.$locale['446'].'</a></h4>
                    <div id="unread_inbox" class="collapse in">';
                        if (!empty($unread)) {
                            echo '<div class="table-responsive"><table id="unread_tbl" class="table table-hover">';
                            foreach ($unread as $id => $messageData) {
                                echo "<tr>\n";
                                echo "<td>".form_checkbox("pmID", "", "", array(
                                        "input_id" => "pmID-".$id,
                                        "value"    => $id,
                                        "class"    => "m-b-0"
                                    ))."</td>\n";
                                echo "<td class='col-xs-2'><strong>".$messageData['contact_user']['user_name']."</strong></td>\n";
                                echo "<td class='col-xs-7'><strong><a href='".$messageData['message']['link']."'>".$messageData['message']['name']."</a></strong></td>\n";
                                echo "<td>".showdate($locale['date_day'], $messageData['message_datestamp'])."</td>\n";
                                echo "</tr>\n";
                            }
                            echo '</table></div>';
                        } else {
                            echo '<div class="text-center well">'.$locale['471'].'</div>';
                        }
                        echo '</div>';

                        echo '<h4><a data-target="#read_inbox" class="pointer text-dark" data-toggle="collapse">'.$locale['447'].'</a></h4>
                        <div id="read_inbox" class="collapse in">';
                        if (!empty($read)) {
                            echo '<div class="table-responsive"><table id="read_tbl"  class="table table-hover">';
                            foreach ($read as $id => $messageData) {
                                echo "<tr>\n";
                                echo "<td>".form_checkbox("pmID", "", "", array(
                                        "input_id" => "pmID-".$id,
                                        "value"    => $id,
                                        "class"    => "m-b-0"
                                    ))."</td>\n";
                                echo "<td class='col-xs-2'>".$messageData['contact_user']['user_name']."</td>\n";
                                echo "<td class='col-xs-7'><a href='".$messageData['message']['link']."'>".$messageData['message']['name']."</a></td>\n";
                                echo "<td>".showdate($locale['date_day'], $messageData['message_datestamp'])."</td>\n";
                                echo "</tr>\n";
                            }
                            echo '</table></div>';
                        }
                        echo '</div>';

                    } else {
                        if (!empty($read)) {
                            echo '<div class="table-responsive"><table id="read_tbl"  class="table table-hover">';
                            foreach ($read as $id => $messageData) {
                                echo "<tr>\n";
                                echo "<td>".form_checkbox("pmID", "", "", array(
                                        "input_id" => "pmID-".$id,
                                        "value"    => $id,
                                        "class"    => "m-b-0"
                                    ))."</td>\n";
                                echo "<td class='col-xs-2'>".$messageData['contact_user']['user_name']."</td>\n";
                                echo "<td class='col-xs-7'><a href='".$messageData['message']['link']."'>".$messageData['message']['name']."</a></td>\n";
                                echo "<td>".showdate($locale['date_day'], $messageData['message_datestamp'])."</td>\n";
                                echo "</tr>\n";
                            }
                            echo '</table></div>';
                        }
                    }

                } else {
                    echo '<div class="text-center list-group-item">'.$info['no_item'].'</div>';
                }
            endif;
        }

        opentable($locale['400']);
        ?>
        <!---start_inbox_idx-->
        <div class="row spacer-sm">
            <?php if (!isset($_GET['msg_send'])) : ?>
                <div class="col-xs-12 col-sm-3">
                    <a class='btn btn-primary btn-block text-white' href='<?php echo $info['button']['new']['link'] ?>'>
                    <?php echo $info['button']['new']['name'] ?>
                </a>
                <?php
                $i = 0;
                echo "<ul class='m-t-20 nav'>\n";
                foreach ($info['folders'] as $key => $folderData) {
                    echo "<li><a href='".$folderData['link']."' class='text-dark ".($_GET['folder'] == $key ? "strong" : '')."'>".$folderData['title'];
                    if ($i < count($info['folders']) - 1) {
                        $total_key = $key."_total";
                        echo "<div class='pull-right'>(".$info[$total_key].")</div>";
                    }
                    echo "</a></li>\n";
                    $i++;
                }
                echo "</ul>\n";
                ?>
            </div>
            <?php endif; ?>
            <div class="col-xs-12 col-sm-<?php echo(!isset($_GET['msg_send']) ? 9 : 12) ?>">
                <!-- start inbox actions -->
                <?php if (!isset($_GET['msg_send'])) : ?>
                    <div class="inbox_header m-b-20">
                        <?php if (isset($_GET['msg_read'])) : ?>
                            <a href="<?php echo $info['button']['back']['link'] ?>" class="btn btn-default">
                                <i title="<?php echo $info['button']['back']['title'] ?>" class="fa fa-long-arrow-left"></i>
                            </a>
                        <?php endif; ?>
                        <?php echo $info['actions_form']; ?>
                    </div>
                <?php endif; ?>
                <!-- end inbox actions -->
                <!-- start inbox body -->
                <?php
                switch ($_GET['folder']) {
                    case "options": // display options form
                        echo '<div class="list-group-item">'.$info['options_form'].'</div>';
                        break;
                    case "inbox":
                        _inbox($info);
                        break;
                    default :
                        _inbox($info);
                }
                ?>
                <!-- end inbox body -->
            </div>
        </div>
        <!--end_inbox_idx--->
        <?php
        closetable();
    }
}
