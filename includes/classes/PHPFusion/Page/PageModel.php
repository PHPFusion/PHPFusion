<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: PageModel.php
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

namespace PHPFusion\Page;

if (!defined("IN_FUSION")) { die("Access Denied"); }

class PageModel {

    protected static $admin_composer_opts = array();
    protected static $info = array(
        'title' => '',
        'error' => '',
        'body' => '',
        'count' => 0,
        'pagenav' => '',
        'show_comments' => '',
        'show_ratings' => '',
    );
    private static $page_instance = null;
    private static $data = array(
        'page_id' => '',
        'page_title' => '',
        'page_link_cat' => 0,
        'page_access' => 0,
        'page_content' => '',
        'page_keywords' => '',
        'page_language' => LANGUAGE,
        // to join instead and add publish or unpublished only
        'link_id' => 0,
        'link_order' => 0,
    );

    /**
     * Return page composer object
     * @param bool|FALSE $set_info
     * @return null|static
     */
    public static function getInstance($set_info = false) {
        if (empty(self::$page_instance)) {
            self::$page_instance = new Static;
        }

        return self::$page_instance;
    }

    public static function render_composer() {
        echo "This is layout administration";
        echo "Give this a couple of grid options";
    }

    // need an administration interface - to couple with theme engine

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
                    SELECT cp.* FROM ".DB_CUSTOM_PAGES." cp WHERE page_id= '".intval($id)."'
                    ")
            );
        }
        return (array) $array;
    }

    public static function query_customPage($id = null) {

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
     * Composer display here
     */
    protected static function set_PageInfo() {

        $locale = fusion_get_locale("", LOCALE.LOCALESET."custom_pages.php");

        if (!isset($_GET['page_id']) || !isnum($_GET['page_id'])) {
            redirect("index.php");
        }

        self::$info['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;

        $page_query = "SELECT * FROM ".DB_CUSTOM_PAGES."
            WHERE page_id='".intval($_GET['page_id'])."' AND ".groupaccess('page_access')."
            ".(multilang_table("CP") ? "AND ".in_group("page_language", LANGUAGE) : "");

        $cp_result = dbquery($page_query);

        if (dbrows($cp_result) > 0) {

            $cp_data = dbarray($cp_result);

            add_to_title($locale['global_200'].$cp_data['page_title']);

            add_breadcrumb(array(
                               'link' => BASEDIR."viewpage.php?page_id=".$_GET['page_id'],
                               'title' => $cp_data['page_title']
                           ));

            if (!empty($cp_data['page_keywords'])) {
                set_meta("keywords", $cp_data['page_keywords']);
            }

            // build administration composer status
            if (checkrights('CP')) {

                self::$admin_composer_opts = array(
                    '0' => array(
                        '2' => array(
                            'link_id' => 'layout', 'link_cat' => 0, 'link_name' => 'Layout',
                            'link_url' => clean_request('compose=layout', array('compose'), FALSE),
                            'link_visibility' => USER_LEVEL_ADMIN
                        ),
                        '3' => array(
                            'link_id' => 'panel', 'link_cat' => 0, 'link_name' => 'Panels',
                            'link_url' => clean_request('compose=panel', array('compose'), FALSE),
                            'link_visibility' => USER_LEVEL_ADMIN
                        ),
                        '4' => array(
                            'link_id' => 'widget', 'link_cat' => 0, 'link_name' => 'Widgets',
                            'link_url' => clean_request('compose=widget', array('compose'), FALSE),
                            'link_visibility' => USER_LEVEL_ADMIN
                        ),
                    )
                );

            }

            self::$info['title'] = $cp_data['page_title'];

            ob_start();
            if (fusion_get_settings("allow_php_exe")) {
                eval("?>".stripslashes($cp_data['page_content'])."<?php ");
            } else {
                echo "<p>".parse_textarea($cp_data['page_content'])."</p>\n";
            }
            $eval = ob_get_contents();
            ob_end_clean();
            self::$info['body'] = preg_split("/<!?--\s*pagebreak\s*-->/i",
                (fusion_get_settings("tinymce_enabled") ? $eval : nl2br($eval)));
            self::$info['count'] = count(self::$info['body']);

            if (self::$info['count'] > 0) {
                if (self::$info['rowstart'] > self::$info['count']) {
                    redirect(BASEDIR."viewpage.php?page_id=".$_GET['page_id']);
                }
                self::$info['pagenav'] = makepagenav(self::$info['rowstart'], 1, self::$info['count'], 1,
                                                     BASEDIR."viewpage.php?page_id=".$_GET['page_id']."&amp;")."\n";
            }

            /*
            if ($cp_data['page_allow_ratings']) {
                ob_start();
                require_once INCLUDES."ratings_include.php";
                showratings("C", $_GET['page_id'], BASEDIR."viewpage.php?page_id=".$_GET['page_id']);
                self::$info['show_ratings'] = ob_get_contents();
                ob_end_clean();
            }
            */
            unset($cp_data);

        } else {

            add_to_title($locale['global_200'].$locale['401']);

            self::$info['title'] = $locale['401'];
            self::$info['error'] = $locale['402'];
        }

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
    protected static function verify_customPage($id) {
        if (isnum($id)) {
            return dbcount("(page_id)", DB_CUSTOM_PAGES, "page_id='".intval($id)."'");
        }

        return FALSE;
    }

}
