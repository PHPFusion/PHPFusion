<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: groups.tpl.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if ( !function_exists( 'render_user_group' ) ) {
    /**
     * Display user groups
     * @param $info - fetch from UserGroups method setGroupInfo($group_id)
     */
    function render_user_group( $info ) {
        $locale = fusion_get_locale( '', LOCALE . LOCALESET . 'user_fields.php' );
        opentable( $locale['u057'] );
        echo "<div class='well text-center'>
            <h4>" . ( !empty( $info['group_icon'] ) ? get_icon( $info['group_icon'], 'm-r-10' ) : '' ) . ( !empty( $info['group_name'] ) ? $info['group_name'] : '' ) . " (" . format_word( $info['total_rows'], $locale['fmt_user'] ) . ")</h4>
            <p>" . $info['group_description'] . "</p>
        </div>";
        if ( !empty( $info['group_members'] ) ) {
            $sort_plugin = fusion_sort_table( 'groupTbl' );
            echo "<div class='table-responsive'><table id='groupTbl' class='table table-hover " . $sort_plugin . "'>
                <tr>
                    <th class='col-xs-1'>" . $locale['u062'] . "</th>
                    <th class='col-xs-1'>" . $locale['u113'] . "</th>
                    <th class='col-xs-1'>" . $locale['u114'] . "</th>";
                    if ( count( fusion_get_enabled_languages() ) > 1 ) {
                        echo "<th class='col-xs-1'>" . $locale['u115'] . "</th>";
                    }
                    echo "<th class='col-xs-1'>" . $locale['status'] . "</th>
                </tr>\n";
                foreach ( $info['group_members'] as $mData ) {
                    echo "<tr>
                        <td class='col-xs-1'>" . display_avatar( $mData, '50px', '', FALSE, 'img-rounded' ) . "</td>
                        <td>" . profile_link( $mData['user_id'], $mData['user_name'], $mData['user_status'] ) . "</td>
                        <td class='col-xs-1'>" . getuserlevel( $mData['user_level'] ) . "</td>";
                        if ( count( fusion_get_enabled_languages() ) > 1 ) {
                            echo "<td class='col-xs-1'>" . translate_lang_names( $mData['user_language'] ) . "</td>";
                        }
                        echo "<td class='col-xs-1'>" . getuserstatus( $mData['user_status'] ) . "</td>
                    </tr>";
                }
            echo "</table>\n</div>";
        } else {
            echo "<div class='well text-center'>" . $locale['u116'] . "</div>";
        }
        if ( !empty( $info['group_pagenav'] ) ) {
            echo "<div class='text-center m-t-10 m-b-10'>" . $info['group_pagenav'] . "</div>";
        }
        closetable();
    }
}
