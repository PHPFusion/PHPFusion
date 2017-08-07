<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: CustomPage.inc
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
namespace PHPFusion;

class CustomPage {
    /**
     * @var array
     */
    private $data = array(
        'page_id' => '',
        'page_title' => '',
        'link_id' => 0,
        'link_order' => 0,
        'page_link_cat' => 0,
        'page_access' => 0,
        'page_content' => '',
        'page_keywords' => '',
        'page_language' => LANGUAGE,
        'page_allow_comments' => 0,
        'page_allow_ratings' => 0,
    );

    public static function query_customPage($id = NULL) {

        $result = dbquery("
                    SELECT cp.*, link.link_id, link.link_order
                    FROM ".DB_CUSTOM_PAGES." cp
                    LEFT JOIN ".DB_SITE_LINKS." link on (cp.page_link_cat = link.link_cat AND ".in_group("link.link_url", "viewpage.php?page_id=")."
                     AND ".in_group("link.link_url", "cp.page_id").")
                    ".($id !== NULL && isnum($id) ? " WHERE page_id= '".intval($id)."' " : "")."
                    ");

        return $result;
    }

    /**
     * List custom page administration table
     */
    public static function listPage() {
        global $aidlink;

        $locale = fusion_get_locale("", LOCALE.LOCALESET."admin/custom_pages.php");

        $data = array();
        // now load new page
        $result = dbquery("SELECT page_id, page_link_cat, page_title, page_access, page_allow_comments, page_allow_ratings, page_language FROM ".DB_CUSTOM_PAGES." ORDER BY page_id ASC");
        if (dbrows($result) > 0) {
            while ($cdata = dbarray($result)) {
                $data[$cdata['page_id']] = $cdata;
            }
        }
        $choice = array('0' => $locale['no'], '1' => $locale['yes']);
        add_to_jquery("
        $('.actionbar').hide();
        $('tr').hover(
            function(e) { $('#coupon-'+ $(this).data('id') +'-actions').show(); },
            function(e) { $('#coupon-'+ $(this).data('id') +'-actions').hide(); }
        );
        $('.qform').hide();
        ");
        echo "<div class='m-t-20 table-responsive'>\n";
        echo "<table class='table ".(!empty($data) ? " table-striped " : "")."table-hover'>\n";
        echo "<tr>\n";
        echo "<th>".$locale['cp_100']."</th>\n";
        echo "<th>".$locale['cp_101']."</th>\n";
        echo "<th>".$locale['cp_102']."</th>\n";
        echo "<th>".$locale['cp_103']."</th>\n";
        echo "<th>".$locale['cp_104']."</th>\n";
        echo "<th>".$locale['cp_105']."</th>\n";
        echo "<th>".$locale['cp_106']."</th>\n";
        echo "</tr>\n";

        if (!empty($data)) {
            echo "<tbody id='custompage-links' class='connected'>\n";
            foreach ($data as $id => $pageData) {
                $displayLanguage = "";
                $pageLang = explode(".", $pageData['page_language']);
                foreach ($pageLang as $languages) {
                    $displayLanguage .= "<span class='badge'>".translate_lang_names($languages)."</span>\n";
                }

                echo "<tr id='listItem_".$pageData['page_id']."' data-id='".$pageData['page_id']."' class='list-result pointer'>\n";
                echo "<td>".$pageData['page_id']."</td>\n";
                echo "<td class='col-sm-4'>".$pageData['page_title']."\n";
                echo "<div class='actionbar text-smaller' id='coupon-".$pageData['page_id']."-actions'>
                <a target='_blank' href='".BASEDIR."viewpage.php?page_id=".$pageData['page_id']."'>".$locale['view']."</a> |
                <a href='".FUSION_SELF.$aidlink."&amp;section=cp2&amp;action=edit&amp;cpid=".$pageData['page_id']."'>".$locale['edit']."</a> |
                <a class='delete' href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;cpid=".$pageData['page_id']."' onclick=\"return confirm('".$locale['450']."');\">".$locale['delete']."</a>
                </div>\n";
                echo "</td>\n";
                echo "<td>".getgroupname($pageData['page_access'])."</td>\n";
                echo "<td>".$displayLanguage."</td>\n";
                echo "<td>".$choice[$pageData['page_allow_comments']]."</td>\n";
                echo "<td>".$choice[$pageData['page_allow_ratings']]."</td>\n";
                echo "<td>".($pageData['page_link_cat'] ? $choice[1] : $choice[0])."</td>\n";
                echo "</tr>\n";
            }
            echo "</tbody>\n";
        } else {
            echo "<tr>\n";
            echo "<td colspan='7' class='text-center'>\n<div class='well'>\n".$locale['458']."</div>\n</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
        echo "</div>\n";
    }

    /**
     * The HTML form
     * @param $data
     */
    public static function customPage_form($data) {
        global $aidlink;

        $locale = fusion_get_locale("", LOCALE.LOCALESET."admin/custom_pages.php");

        if (isset($_POST['preview'])) {
            if (\defender::safe()) {
                $previewHtml = openmodal("cp_preview", $locale['429']);
                $previewHtml .= "<h3>".$data['page_title']."</h3>\n";
                if (fusion_get_settings("allow_php_exe")) {
                    ob_start();
                    eval("?>".stripslashes($_POST['page_content'])."<?php ");
                    $eval = ob_get_contents();
                    ob_end_clean();
                    $previewHtml .= $eval;
                } else {
                    $previewHtml .= "<p>".nl2br(parse_textarea($_POST['page_content']))."</p>\n";
                }
                $previewHtml .= closemodal();
                add_to_footer($previewHtml);
            }
            $data = array(
                'page_id' => form_sanitizer($_POST['page_id'], 0, 'page_id'),
                'link_id' => form_sanitizer($_POST['link_id'], 0, 'link_id'),
                'link_order' => form_sanitizer($_POST['link_order'], 0, 'link_order'),
                'page_link_cat' => form_sanitizer((isset($_POST['page_link_cat'])) ? $_POST['page_link_cat'] : 0, 0, 'page_link_cat'),
                'page_title' => form_sanitizer($_POST['page_title'], '', 'page_title'),
                'page_access' => form_sanitizer($_POST['page_access'], 0, 'page_access'),
                'page_content' => form_sanitizer($_POST['page_content'], "", "page_content"),
                'page_keywords' => form_sanitizer($_POST['page_keywords'], '', 'page_keywords'),
                //'page_language' => implode('.', isset($_POST['page_language']) ? \defender::sanitize_array($_POST['page_language']) : array()),
                'page_language' => isset($_POST['page_language']) ? form_sanitizer($_POST['page_language'], '', 'page_language') : LANGUAGE,
                'page_allow_comments' => isset($_POST['page_allow_comments']) ? 1 : 0,
                'page_allow_ratings' => isset($_POST['page_allow_ratings']) ? 1 : 0
            );
        }

        echo openform('inputform', 'post', FUSION_REQUEST, array("class" => "m-t-20"));

        if (isset($_POST['edit']) && isset($_POST['page_id'])) {
            echo form_hidden('edit', '', 'edit');
        }

        echo "<div class='row m-t-20' >\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-8'>\n";
        echo form_text('page_title', $locale['422'], $data['page_title'], array('required' => 1));
        echo form_select('page_keywords', $locale['432'], $data['page_keywords'], array(
            'max_length' => 320,
            'width' => '100%',
            'tags' => 1,
            'multiple' => 1,
        ));

        $textArea_config = array(
            'width' => '100%',
            'height' => '260px',
            'form_name' => 'inputform',
            'type' => "html",
            'class' => 'm-t-20',
        );

        if (isset($_COOKIE['custom_pages_tinymce']) && $_COOKIE['custom_pages_tinymce'] == 1 && fusion_get_settings('tinymce_enabled')) {
            $textArea_config = array(
                "type" => "tinymce",
                "tinymce" => "advanced",
                "class" => "m-t-20",
                "height" => "400px",
            );
        }

        echo form_textarea('page_content', '', $data['page_content'], $textArea_config);

        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-4'>\n";

        openside("");
        echo form_button('save', $locale['430'], $locale['430'], array('class' => 'btn-primary m-r-10'));
        echo form_button('preview', $locale['429'], $locale['429'], array('class' => 'btn-default m-r-10'));
        closeside();

        if (fusion_get_settings('tinymce_enabled')) {
            openside('');
            $val = !isset($_COOKIE['custom_pages_tinymce']) || $_COOKIE['custom_pages_tinymce'] == 0 ? $locale['461']." TINYMCE" : $locale['462']." TINYMCE";
            echo form_button('tinymce_switch', $val, $val, array('class' => 'btn-default btn-block', 'type' => 'button'));
            add_to_jquery("
            $('#tinymce_switch').bind('click', function() {
                SetTinyMCE(".(!isset($_COOKIE['custom_pages_tinymce']) || $_COOKIE['custom_pages_tinymce'] == 0 ? 1 : 0).");
            });
            ");
            closeside();
        }

        if (fusion_get_settings('comments_enabled') == "0" || fusion_get_settings('ratings_enabled') == "0") {
            echo "<div class='tbl2 well'>\n";
            if (fusion_get_settings('comments_enabled') == "0" && fusion_get_settings('ratings_enabled') == "0") {
                $sys = $locale['457'];
            } elseif (fusion_get_settings('comments_enabled') == "0") {
                $sys = $locale['455'];
            } else {
                $sys = $locale['456'];
            }
            echo sprintf($locale['454'], $sys);
            echo "</div>\n";
        }

        if (!$data['page_id']) {
            openside("");

            echo form_checkbox('add_link', $locale['426'], 1);

            echo "<div id='link_add_sel' style='display:none;'>\n";

            echo form_select_tree("page_link_cat", $locale['SL_0029'], $data['page_link_cat'], array(
                "parent_value" => $locale['parent'],
                'width' => '100%',
                'query' => (multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : '')." link_position >= 2",
                'disable_opts' => $data['link_id'],
                'hide_disabled' => 1
            ), DB_SITE_LINKS, "link_name", "link_id", "link_cat");

            echo "</div>\n";


            add_to_jquery("
            var checked = $('#add_link').is(':checked');
            if (checked) {
                $('#link_add_sel').show();
            } else {
                $('#link_add_sel').hide();
            }
            $('#add_link').bind('click', function(e) {
                var checked = $(this).is(':checked');
                if (checked) {
                    $('#link_add_sel').show();
                } else {
                    $('#link_add_sel').hide();
                }
            });
            ");

            closeside();
        }

        openside("");

        echo form_checkbox('page_allow_comments', $locale['427'], $data['page_allow_comments'], array('class' => 'm-b-0', 'reverse_label' => TRUE));
        echo form_checkbox('page_allow_ratings', $locale['428'], $data['page_allow_ratings'], array('class' => 'm-b-0', 'reverse_label' => TRUE));

        echo form_hidden('link_id', '', $data['link_id']);
        echo form_hidden('link_order', '', $data['link_order']);

        closeside();

        openside();
        if (multilang_table("CP")) {

            $page_lang = !empty($data['page_language']) ? explode('.', $data['page_language']) : array();

            foreach (fusion_get_enabled_languages() as $language => $language_name) {

                echo form_checkbox('page_language[]', $language_name, in_array($language, $page_lang) ? TRUE : FALSE, array(
                    'class' => 'm-b-0',
                    'value' => $language,
                    'input_id' => 'page_lang-'.$language,
                    "delimiter" => ".",
                    'reverse_label' => TRUE,
                    'required' => TRUE
                ));

            }
        } else {
            echo form_hidden('page_language', '', $data['page_language']);
        }
        closeside();
        openside();
        echo form_select('page_access', $locale['423'], $data['page_access'], array(
            "options" => fusion_get_groups(),
            "width" => '100%',
            "inline" => TRUE,
        ));
        closeside();
        echo "</div></div>\n";
        echo form_hidden('page_id', '', $data['page_id']);
        echo form_button('save', $locale['430'], $locale['430'], array('class' => 'btn-primary m-r-10'));
        if (isset($_POST['edit'])) {
            echo form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-default m-r-10'));
        }
        echo closeform();
        add_to_jquery("
            $('#delete').bind('click', function() { confirm('".$locale['450']."'); });
            $('#save').bind('click', function() {
            var page_title = $('#page_title').val();
            if (page_title =='') { alert('".$locale['451']."'); return false; }
            });
        ");
        if (fusion_get_settings('tinymce_enabled')) {
            add_to_jquery("
            function SetTinyMCE(val) {
            now=new Date();\n"."now.setTime(now.getTime()+1000*60*60*24*365);
            expire=(now.toGMTString());\n"."document.cookie=\"custom_pages_tinymce=\"+escape(val)+\";expires=\"+expire;
            location.href='".FUSION_SELF.$aidlink."&section=cp2';
            }
            ");
        }
    }

    public function display_custom_page_admin() {
        global $aidlink;

        $locale = fusion_get_locale("", LOCALE.LOCALESET."admin/custom_pages.php");

        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.$aidlink);
        }

        $locale = fusion_get_locale("", LOCALE.LOCALESET."admin/custom_pages.php");

        $_POST['page_id'] = isset($_POST['page_id']) && isnum($_POST['page_id']) ? $_POST['page_id'] : 0;
        $_GET['status'] = isset($_GET['status']) ? $_GET['status'] : '';
        $_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';

        switch ($_GET['action']) {
            case 'edit':
                fusion_confirm_exit();
                if (!isset($_GET['cpid'])) {
                    redirect(FUSION_SELF.$aidlink);
                }
                $this->data = self::load_customPage($_GET['cpid']);
                if (empty($this->data)) {
                    redirect(FUSION_SELF.$aidlink);
                }
                opentable($locale['401']);
                break;
            case 'delete':
                if (!isset($_GET['cpid'])) {
                    redirect(FUSION_SELF.$aidlink);
                }
                self::delete_customPage($_GET['cpid']);
                break;
            default:
                opentable($locale['403']);
        }

        $this->display_customPage_selector();

        $this->data = self::set_customPage($this->data);

    }

    /**
     * Displays a single custom page data
     * @param $id - page_id
     * @return array;
     */
    public static function load_customPage($id) {
        $array = array();
        if (isnum($id)) {
            $array = dbarray(
                dbquery("
                    SELECT cp.*, link.link_id, link.link_order
                    FROM ".DB_CUSTOM_PAGES." cp
                    LEFT JOIN ".DB_SITE_LINKS." link on (cp.page_link_cat = link.link_cat AND link.link_url='viewpage.php?page_id=".intval($id)."' )
                    WHERE page_id= '".intval($id)."'
                    ")
            );
        }

        return (array)$array;
    }

    /**
     * SQL delete page
     * @param $page_id
     */
    protected function delete_customPage($page_id) {
        global $aidlink, $locale;
        if (isnum($page_id) && self::verify_customPage($page_id)) {
            $result = dbquery("DELETE FROM ".DB_CUSTOM_PAGES." WHERE page_id='".intval($page_id)."'");
            if ($result) {
                $result = dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_url='viewpage.php?page_id=".intval($page_id)."'");
            }
            if ($result) {
                addNotice("success", $locale['413']);
                redirect(FUSION_SELF.$aidlink);
            }
        }
    }

    /**
     * Authenticate the page ID is valid
     * @param $id
     * @return bool|string
     */
    protected function verify_customPage($id) {
        if (isnum($id)) {
            return dbcount("(page_id)", DB_CUSTOM_PAGES, "page_id='".intval($id)."'");
        }

        return FALSE;
    }

    /**
     * Displays Custom Page Selector
     */
    public static function display_customPage_selector() {
        global $aidlink;

        $locale = fusion_get_locale("", LOCALE.LOCALESET."custom_pages.php");

        $result = dbquery("SELECT page_id, page_title, page_language FROM ".DB_CUSTOM_PAGES." ".(multilang_table("CP") ? "WHERE page_language='".LANGUAGE."'" : "")." ORDER BY page_title");

        echo "<div class='pull-right'>\n";
        echo openform('selectform', 'get', ADMIN.'custom_pages.php'.$aidlink);
        echo "<div class='pull-left m-t-0'>\n";

        $edit_opts = array();
        if (dbrows($result) != 0) {
            while ($data = dbarray($result)) {
                $edit_opts[$data['page_id']] = $data['page_title'];
            }
        }
        echo form_select('cpid', '', isset($_POST['page_id']) && isnum($_POST['page_id']) ? $_POST['page_id'] : '',
                         array(
                             "options" => $edit_opts,
                             "class" => 'm-b-0',
                             "required" => TRUE,
                         ));
        echo form_hidden('section', '', 'cp2');
        echo form_hidden('aid', '', iAUTH);
        echo "</div>\n";
        echo form_button('action', $locale['edit'], 'edit', array('class' => 'btn-default pull-left m-l-10 m-r-10'));
        echo form_button('action', $locale['delete'], 'delete', array(
            'class' => 'btn-danger pull-left',
            'icon' => 'fa fa-trash'
        ));
        echo closeform();
        echo "</div>\n";
    }

    /**
     * SQL update or save data
     * @param $data
     * @return array
     */
    protected function set_customPage($data) {
        global $aidlink;

        $locale = fusion_get_locale("", LOCALE.LOCALESET."admin/custom_pages.php");

        if (isset($_POST['save'])) {

            $data = array(
                'page_id' => form_sanitizer($_POST['page_id'], 0, 'page_id'),
                'page_link_cat' => isset($_POST['page_link_cat']) ? form_sanitizer($_POST['page_link_cat'], 0, 'page_link_cat') : "",
                'page_title' => form_sanitizer($_POST['page_title'], '', 'page_title'),
                'page_access' => form_sanitizer($_POST['page_access'], 0, 'page_access'),
                'page_content' => addslash($_POST['page_content']),
                'page_keywords' => form_sanitizer($_POST['page_keywords'], '', 'page_keywords'),
                'page_language' => isset($_POST['page_language']) ? form_sanitizer($_POST['page_language'], "", "page_language") : LANGUAGE,
                'page_allow_comments' => isset($_POST['page_allow_comments']) ? 1 : 0,
                'page_allow_ratings' => isset($_POST['page_allow_ratings']) ? 1 : 0,
            );

            if ($data['page_id'] == 0) {
                $data += array(
                    "add_link" => isset($_POST['add_link']) ? 1 : 0,
                    'link_id' => form_sanitizer($_POST['link_id'], 0, 'link_id'),
                );
            }

            if (self::verify_customPage($data['page_id'])) {

                dbquery_insert(DB_CUSTOM_PAGES, $data, 'update');

                if (\defender::safe()) {
                    addNotice('success', $locale['411']);
                    redirect(FUSION_SELF.$aidlink."&amp;pid=".$data['page_id']);
                }

            } else {

                dbquery_insert(DB_CUSTOM_PAGES, $data, 'save');

                $data['page_id'] = dblastid();

                if (!empty($data['add_link'])) {
                    self::set_customPageLinks($data);
                }

                if (\defender::safe()) {
                    addNotice('success', $locale['410']);
                    redirect(FUSION_SELF.$aidlink."&amp;pid=".$data['page_id']);
                }
            }
        }

        return $data;
    }

    /**
     * Set CustomPage Links into Navigation Bar
     * @param $data
     */
    protected function set_customPageLinks($data) {

        $page_language = explode(".", $data['page_language']);

        foreach ($page_language as $language) {

            $link_order = dbresult(dbquery("SELECT MAX(link_order) FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_cat='".$data['page_link_cat']."'"),
                                   0) + 1;

            $link_data = array(
                'link_id' => !empty($data['link_id']) ? $data['link_id'] : 0,
                'link_cat' => $data['page_link_cat'],
                'link_name' => $data['page_title'],
                'link_url' => 'viewpage.php?page_id='.$data['page_id'],
                'link_icon' => '',
                'link_language' => $language,
                'link_visibility' => 0,
                'link_position' => 2,
                'link_window' => 0,
                'link_order' => $link_order
            );

            if (SiteLinks::verify_sitelinks($link_data['link_id'])) {

                dbquery_insert(DB_SITE_LINKS, $link_data, 'update');

            } else {

                dbquery_insert(DB_SITE_LINKS, $link_data, 'save');
            }
        }
    }

    /**
     * @return data array from object initial or constructor when overriden.
     */
    public function getData() {
        return $this->data;
    }
}
