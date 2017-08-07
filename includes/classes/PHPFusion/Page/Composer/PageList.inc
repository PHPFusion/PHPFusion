<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: PageList.inc
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
namespace PHPFusion\Page\Composer;

use PHPFusion\Page\PageAdmin;

class PageList extends PageAdmin {

    /**
     * List custom page administration table
     */
    public static function displayContent() {

        $aidlink = fusion_get_aidlink();
        $locale = self::$locale;

        if (isset($_POST['page_clear'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        $search_string = array();
        if (isset($_POST['p-submit-page_title'])) {
            $search_string['cp.page_title'] = array(
                "input" => form_sanitizer($_POST['page_title'], "", "page_title"), "operator" => "LIKE"
            );
        }

        if (!empty($_POST['page_status']) && isnum($_POST['page_status'])) {
            switch ($_POST['cp.page_status']) {
                case 1: // is a draft
                    $search_string['page_status'] = array("input" => 1, "operator" => "=");
                    break;
                case 2: // is a sticky
                    $search_string['page_status'] = array("input" => 2, "operator" => "=");
                    break;
            }
        }

        if (!empty($_POST['page_access'])) {
            $search_string['cp.page_access'] = array(
                "input" => form_sanitizer($_POST['page_access'], "", "page_access"), "operator" => "="
            );
        }

        if (!empty($_POST['page_cat'])) {
            $search_string['cp.page_cat'] = array(
                "input" => form_sanitizer($_POST['page_cat'], "", "page_cat"), "operator" => "="
            );
        }
        // This one cannot - must be ".in_group()

        if (!empty($_POST['page_language'])) {
            $language = form_sanitizer($_POST['page_language'], '', 'page_language');
            $search_string['cp.page_language'] = array(
                "input" => in_group('page_language', $language), "operator" => ""
            );
        }

        if (!empty($_POST['page_user'])) {
            $search_string['cp.page_user'] = array(
                "input" => form_sanitizer($_POST['page_user'], "", "page_user"), "operator" => "="
            );
        }

        if (isset($_GET['pref']) && isnum($_GET['pref'])) {
            $search_string['cp.page_cat'] = array(
                'input' => intval($_GET['pref']),
                'operator' => '='
            );
        }

        $sql_condition = '';
        if (!empty($search_string)) {
            $i = 0;
            foreach ($search_string as $key => $values) {
                if ($i > 0) {
                    $sql_condition .= " AND ";
                }
                $sql_condition .= " $key ".$values['operator'].($values['operator'] == "LIKE" ? "'%" : "'").$values['input'].($values['operator'] == "LIKE" ? "%'" : "'");
                $i++;
            }
        }

        $rowstart = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;
        $page_per_query = 20;

        $page_query = "SELECT cp.*, cp2.page_title 'page_cat_title', count(cp2.page_id) 'page_sub_count', u.user_id, u.user_name, u.user_status, u.user_avatar
        FROM ".DB_CUSTOM_PAGES." cp
        LEFT JOIN ".DB_USERS." u ON u.user_id=cp.page_user
        LEFT JOIN ".DB_CUSTOM_PAGES." cp2 ON cp.page_cat=cp2.page_id
        ".($sql_condition ? "WHERE " : "")." $sql_condition
        GROUP BY cp.page_id
        ORDER BY cp.page_status DESC, cp.page_datestamp DESC LIMIT $rowstart, $page_per_query
        ";

        $page_result = dbquery($page_query);
        ?>

        <div class="m-t-15">
            <?php

            echo openform("cp_filter", "post", FUSION_REQUEST);
            echo "<div class='clearfix'>\n";

            echo "<div class='pull-right'>\n";
            echo "<a class='btn btn-success btn-sm m-r-10' href='".clean_request("section=compose_frm", array("section"), FALSE)."'><i class='fa fa-plus fa-fw'></i> ".self::$locale['page_0200']."</a>";
            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('publish');\"><i class='fa fa-check fa-fw'></i> ".self::$locale['publish']." </a>";
            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('unpublish');\"><i class='fa fa-ban fa-fw'></i> ".self::$locale['unpublish']."</a>";
            echo "<a class='btn btn-danger btn-sm m-r-10' onclick=\"run_admin('delete');\"><i class='fa fa-trash-o fa-fw'></i> ".self::$locale['delete']."</a>";
            echo "</div>\n";

            ?>
            <script>
                function run_admin(action) {
                    $('#table_action').val(action);
                    $('#cp_table').submit();
                }
            </script>

            <?php
            $filter_values = array(
                "page_title" => !empty($_POST['page_title']) ? form_sanitizer($_POST['page_title'], "", "page_title") : "",
                "page_status" => !empty($_POST['page_status']) ? form_sanitizer($_POST['page_status'], "", "page_status") : "",
                "page_cat" => !empty($_POST['page_cat']) ? form_sanitizer($_POST['page_cat'], "", "page_cat") : "",
                "page_access" => !empty($_POST['page_access']) ? form_sanitizer($_POST['page_access'], "", "page_access") : "",
                "page_language" => !empty($_POST['page_language']) ? form_sanitizer($_POST['page_language'], "", "page_language") : "",
                "page_user" => !empty($_POST['page_user']) ? form_sanitizer($_POST['page_user'], "", "page_user") : "",
            );

            $filter_empty = TRUE;
            foreach ($filter_values as $val) {
                if ($val) {
                    $filter_empty = FALSE;
                }
            }

            echo "<div class='display-inline-block pull-left m-r-10' style='width:300px;'>\n";
            echo form_text("page_title", "", $filter_values['page_title'], array(
                "placeholder" => self::$locale['page_0101'],
                "append_button" => TRUE,
                "append_value" => "<i class='fa fa-search'></i>",
                "append_form_value" => "search_page",
                "width" => "250px",
                "group_size" => "sm"
            ));
            echo "</div>\n";
            echo "<div class='display-inline-block'>";
            echo "<a class='btn btn-sm m-r-10 ".($filter_empty == FALSE ? "btn-info" : " btn-default'")."' id='toggle_options' href='#'>".self::$locale['page_0107']." <span id='filter_caret' class='fa ".($filter_empty == FALSE ? "fa-caret-up" : "fa-caret-down")."'></span></a>\n";
            echo form_button("page_clear", self::$locale['page_0108'], self::$locale['page_0108'], array('class' => 'btn-default btn-sm'));
            echo "</div>\n";
            echo "</div>\n";
            add_to_jquery("
            $('#toggle_options').bind('click', function(e) {
                e.preventDefault();
                $('#page_filter_options').slideToggle();
                var caret_status = $('#filter_caret').hasClass('fa-caret-down');
                if (caret_status == 1) {
                    $('#filter_caret').removeClass('fa-caret-down').addClass('fa-caret-up');
                    $(this).removeClass('btn-default').addClass('btn-info');
                } else {
                    $('#filter_caret').removeClass('fa-caret-up').addClass('fa-caret-down');
                    $(this).removeClass('btn-info').addClass('btn-default');
                }
            });

            // Select change
            $('#page_status, #page_access, #page_cat, #page_language, #page_user').bind('change', function(e){
                $(this).closest('form').submit();
            });
            ");
            unset($filter_values['page_title']);

            echo "<div id='page_filter_options'".($filter_empty == FALSE ? "" : " style='display:none;'").">\n";
            echo "<div class='display-inline-block'>\n";
            echo form_select("page_status", "", $filter_values['page_status'], array(
                "allowclear" => TRUE, "placeholder" => "- ".self::$locale['page_0109']." -", "options" => array(
                    0 => self::$locale['page_0110'],
                    1 => self::$locale['publish'],
                    2 => self::$locale['unpublish'],
                )
            ));
            echo "</div>\n";
            echo "<div class='display-inline-block'>\n";
            echo form_select("page_access", "", $filter_values['page_access'],
                             array(
                                 "allowclear" => TRUE,
                                 "placeholder" => "- ".self::$locale['page_0111']." -",
                                 "options" => fusion_get_groups()
                             )
            );
            echo "</div>\n";
            echo "<div class='display-inline-block'>\n";
            echo form_select_tree("page_cat", "", $filter_values['page_cat'],
                                  array(
                                      "allowclear" => TRUE,
                                      "placeholder" => "- ".self::$locale['page_0112']." -"
                                  ),
                                  DB_CUSTOM_PAGES, 'page_title', 'page_id', 'page_cat');
            echo "</div>\n";
            echo "<div class='display-inline-block'>\n";
            $language_opts = array(0 => self::$locale['page_0113']);
            $language_opts += fusion_get_enabled_languages();
            echo form_select("page_language", "", $filter_values['page_language'], array(
                "allowclear" => TRUE, "placeholder" => "- ".self::$locale['page_0114']." -", "options" => $language_opts
            ));
            echo "</div>\n";
            echo "<div class='display-inline-block'>\n";
            $author_opts = array(0 => self::$locale['page_0115']);

            $result = dbquery("SELECT u.user_id, u.user_name, u.user_status
          FROM ".DB_CUSTOM_PAGES." cp
          LEFT JOIN ".DB_USERS." u on cp.page_user = u.user_id
          GROUP BY u.user_id
          ORDER BY user_name ASC");
            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $author_opts[$data['user_id']] = $data['user_name'];
                }
            }
            echo form_select("page_user", "", $filter_values['page_user'],
                             array(
                                 "allowclear" => TRUE, "placeholder" => "- ".self::$locale['page_0116']." -", "options" => $author_opts
                             ));

            echo "</div>\n";
            echo "</div>\n";
            echo closeform();
            ?>
        </div>
        <?php

        add_to_jquery("
        $('.qform').hide();
        $('#delete').bind('click', function() { confirm('".self::$locale['page_0413']."'); });
        ");

        echo "<div class='m-t-20 table-responsive'><table id='cp_table_list' class='table".(!empty($data) ? " table-striped " : " ")."table-hover ".fusion_sort_table('cp_table_list')."'>\n";
        echo "<thead>\n";
        echo "<tr>\n";
        echo "<th></th>\n";
        echo "<th class='col-xs-4'>".self::$locale['page_0101']."</th>\n";
        echo "<th>".$locale['page_0102']."</th>\n";
        echo "<th>".$locale['page_0103']."</th>\n";
        //echo "<th>".$locale['page_0104']."</th>\n";
        //echo "<th>".$locale['page_0105']."</th>\n";
        echo "<th>".$locale['page_0305']."</th>\n";
        echo "<th>".$locale['page_0118']."</th>\n";
        echo "<th>".$locale['page_0106']."</th>\n";
        echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody id='custompage-links' class='connected'>\n";

        if (dbrows($page_result) > 0) {
            while ($pageData = dbarray($page_result)) {
                $pageLanguage = '';
                $pageLang = explode(".", $pageData['page_language']);
                foreach ($pageLang as $languages) {
                    $pageLanguage .= "<span class='badge'>".translate_lang_names($languages)."</span>\n";
                }
                $pageParent = $pageData['page_cat'] == 0 ? self::$locale['page_0106'] : "<a href='".clean_request('pref='.$pageData['page_cat'], array('pref'), FALSE)."'>".$pageData['page_cat_title']."</a>\n";
                $pageStatus = $pageData['page_status'] == 1 ? self::$locale['publish'] : self::$locale['unpublish'];
                $edit_link = FUSION_SELF.$aidlink."&amp;section=compose_frm&amp;action=edit&amp;cpid=".$pageData['page_id'];
                // Disable until later releases
                //$pageLink = clean_request('pref='.$pageData['page_id'], array('pref'), FALSE);
                echo "<tr id='listItem_".$pageData['page_id']."' data-id='".$pageData['page_id']."' class='list-result pointer'>\n";
                echo "<td>".form_checkbox('cp[]', '', '', array(
                        'value' => $pageData['page_id'], 'input_id' => 'cp-'.$pageData['page_id'], 'class' => 'm-b-0'
                    ))."</td>";
                echo "<td><a href='$edit_link'>".$pageData['page_title']."</a></td>\n";
                echo "<td>".getgroupname($pageData['page_access'])."</td>\n";
                echo "<td>".$pageLanguage."</td>\n";
                //echo "<td>$pageParent</td>\n";
                //echo "<td>".$pageData['page_sub_count']."</td>\n";
                echo "<td>$pageStatus</td>\n";
                echo "<td>";
                echo "<a target='_new' href='".BASEDIR."viewpage.php?page_id=".$pageData['page_id']."'>".$locale['preview']."</a> &middot;
                <a href='$edit_link'>".$locale['edit']."</a> &middot; 
                <a class='delete' href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;cpid=".$pageData['page_id']."' onclick=\"return confirm('".$locale['page_0413']."');\">".$locale['delete']."</a>";
                echo "</td>";
                echo "<td>".$pageData['page_id']."</td>\n";
                echo "</tr>\n";
            }
        } else {
            echo "<tr>\n";
            echo "<td colspan='9' class='text-center'>\n<div class='well'>\n".$locale['page_0440']."</div>\n</td>\n";
            echo "</tr>\n";
        }
        echo "</tbody>\n";
        echo "</table>\n</div>";
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
