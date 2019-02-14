<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Page/PageModel.inc
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

class PageModel {
    protected static $admin_composer_opts = [];
    protected static $composerData = [];
    protected static $widgets = [];
    protected static $widget_exclude_list = ".|..|.htaccess|.DS_Store|config.php|config.temp.php|.gitignore|LICENSE|README.md|robots.txt";
    /**
     * @var array - default custom page data
     */
    protected static $data = [
        'page_id'           => 0,
        'page_cat'          => 0,
        'page_link_cat'     => 0,
        'page_title'        => '',
        'page_access'       => iGUEST,
        'page_content'      => '',
        'page_keywords'     => '',
        'page_status'       => 0,
        'page_breaks'       => '',
        'page_user'         => 0,
        'page_datestamp'    => 0,
        'page_language'     => LANGUAGE,
        'page_grid_id'      => 0,
        'page_content_id'   => 0,
        'page_left_panel'   => 1,
        'page_right_panel'  => 1,
        'page_header_panel' => 1,
        'page_footer_panel' => 1,
        'page_top_panel'    => 1,
        'page_bottom_panel' => 1
    ];
    /**
     * @var array - default grid row data
     */
    protected static $rowData = [
        'page_grid_id'           => 0,
        'page_id'                => 0,
        'page_grid_container'    => 0,
        'page_grid_column_count' => 1,
        'page_grid_html_id'      => '',
        'page_grid_class'        => '',
        'page_grid_order'        => 1,
    ];
    /**
     * @var array - default grid column data
     */
    protected static $colData = [
        'page_id'            => 0,
        'page_grid_id'       => 0,
        'page_content_id'    => 0,
        'page_content_type'  => 'content',
        'page_content'       => '',
        'page_content_order' => 1,
        'page_options'       => ''
    ];
    private static $page_instance = NULL;

    /**
     * @var array       the row information
     */
    protected static $gridData = [];

    /**
     * Return page composer object
     *
     * @return static
     */
    public static function getInstance() {
        if (empty(self::$page_instance)) {
            self::$page_instance = new static;
        }

        return self::$page_instance;
    }

    // need an administration interface - to couple with theme engine

    /**
     * Displays a single custom page data
     *
     * @param $id - page_id
     *
     * @return array;
     */
    public static function load_customPage($id) {
        $array = [];
        $page_id = filter_var($id, FILTER_VALIDATE_INT);
        if ($page_id) {
            $array = dbarray(
                dbquery("
                    SELECT cp.* FROM ".DB_CUSTOM_PAGES." cp WHERE page_id=:pageid", [':pageid' => $page_id]
                )
            );
        }

        return (array)$array;
    }

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
     * Displays Custom Page Selector
     */
    public static function display_customPage_selector() {

        $aidlink = fusion_get_aidlink();

        $locale = fusion_get_locale("", LOCALE.LOCALESET."custom_pages.php");

        $result = dbquery("SELECT page_id, page_title, page_language FROM ".DB_CUSTOM_PAGES." ".(multilang_table("CP") ? "WHERE page_language='".LANGUAGE."'" : "")." ORDER BY page_title");

        echo "<div class='pull-right'>\n";
        echo openform('selectform', 'get', ADMIN.'custom_pages.php'.$aidlink);
        echo "<div class='pull-left m-t-0'>\n";

        $edit_opts = [];
        if (dbrows($result) != 0) {
            while ($data = dbarray($result)) {
                $edit_opts[$data['page_id']] = $data['page_title'];
            }
        }
        echo form_select('cpid', '', isset($_POST['page_id']) && isnum($_POST['page_id']) ? $_POST['page_id'] : '',
            [
                "options"  => $edit_opts,
                "class"    => 'm-b-0',
                "required" => TRUE,
            ]);
        echo form_hidden('section', '', 'cp2');
        echo form_hidden('aid', '', iAUTH);
        echo "</div>\n";
        echo form_button('action', $locale['edit'], 'edit', ['class' => 'btn-default pull-left m-l-10 m-r-10']);
        echo form_button('action', $locale['delete'], 'delete', [
            'class' => 'btn-danger pull-left',
            'icon'  => 'fa fa-trash'
        ]);
        echo closeform();
        echo "</div>\n";
    }

    /**
     * Load page composer data
     */
    protected static function load_ComposerData() {
        $query = "SELECT crows.*, col.page_id, col.page_content_id, col.page_content_type, col.page_content, col.page_content_order, col.page_widget, col.page_options
        FROM ".DB_CUSTOM_PAGES_GRID." crows
        LEFT JOIN ".DB_CUSTOM_PAGES_CONTENT." col USING(page_grid_id)
        WHERE crows.page_id=:pid
        ORDER BY crows.page_grid_order ASC, col.page_content_order ASC
        ";
        $result = dbquery($query, [':pid' => intval(self::$data['page_id'])]);
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                self::$gridData[$data['page_grid_id']] = [
                    'page_id'                => $data['page_id'],
                    'page_grid_id'           => $data['page_grid_id'],
                    'page_grid_container'    => $data['page_grid_container'],
                    'page_grid_column_count' => $data['page_grid_column_count'],
                    'page_grid_html_id'      => $data['page_grid_html_id'],
                    'page_grid_class'        => $data['page_grid_class'],
                    'page_grid_order'        => $data['page_grid_order']
                ];
                if (!empty($data['page_content_id'])) {
                    self::$composerData[$data['page_grid_id']][$data['page_content_id']] = $data;
                } else {
                    self::$composerData[$data['page_grid_id']][] = $data;
                }
                // Load rowData
                if (isset($_GET['row_id']) && $_GET['row_id'] == $data['page_grid_id']) {
                    self::$rowData = $data;
                }
            }
        }
    }

    /**
     * Cache widgets info and object
     *
     * @return mixed
     */
    protected static function cache_widget() {
        if (empty(self::$widgets)) {
            $list = [];
            $file_list = makefilelist(WIDGETS, self::$widget_exclude_list, TRUE, "folders");
            foreach ($file_list as $folder) {
                $widget_title = '';
                $widget_icon = '';
                $widget_description = '';
                $widget_admin_file = '';
                $widget_display_file = '';
                $widget_admin_callback = '';
                $widget_display_callback = '';
                $adminObj = '';
                $displayObj = '';

                if (file_exists(WIDGETS.$folder."/".$folder."_widget.php") && file_exists(WIDGETS.$folder."/".$folder.".php")) {
                    include WIDGETS.$folder."/".$folder."_widget.php";
                    // Creates object for Administration
                    if (iADMIN && !empty($widget_admin_callback) && file_exists(WIDGETS.$folder."/".$widget_admin_file)) {
                        require_once WIDGETS.$folder."/".$widget_admin_file;
                        if (class_exists($widget_admin_callback)) {
                            $class = new \ReflectionClass($widget_admin_callback);
                            $adminObj = $class->newInstance();
                        }
                    }

                    if (!empty($widget_display_callback) && !empty($widget_display_callback) && file_exists(WIDGETS.$folder."/".$widget_display_file)) {
                        require_once WIDGETS.$folder."/".$widget_display_file;
                        if (class_exists($widget_display_callback)) {
                            $class = new \ReflectionClass($widget_display_callback);
                            $displayObj = $class->newInstance();
                        }
                    }

                    $list[$folder] = [
                        'widget_name'        => $folder,
                        'widget_title'       => ucfirst($widget_title),
                        'widget_folder'      => $folder,
                        'widget_icon'        => $widget_icon,
                        'widget_description' => $widget_description,
                        'admin_instance'     => $adminObj,
                        'display_instance'   => $displayObj,
                    ];
                }
            }
            self::$widgets = $list;
        }

        return self::$widgets;
    }

    /**
     * @param $max_column_limit - max grid count per row
     * @param $current_count - current actual count if is a fluid design
     *
     * @return string
     */
    protected static function calculateSpan($max_column_limit, $current_count) {
        $default_xs_size = 12;
        $fluid_default_sm_size = $current_count >= $max_column_limit ? floor(12 / $max_column_limit) : floor(12 / $current_count);
        $fluid_default_md_size = $current_count >= $max_column_limit ? 12 / $max_column_limit : floor(12 / $current_count);
        $fluid_default_lg_size = $current_count >= $max_column_limit ? 12 / $max_column_limit : floor(12 / $current_count);
        $default_sm_size = floor(12 / $max_column_limit);
        $default_md_size = floor(12 / $max_column_limit);
        $default_lg_size = floor(12 / $max_column_limit);

        return "col-xs-$default_xs_size col-sm-$default_sm_size col-md-$default_md_size col-lg-$default_lg_size";
    }

    /**
     * SQL delete page
     *
     * @param $pageid
     */
    protected function delete_customPage($pageid) {
        $page_id = filter_var($pageid, FILTER_VALIDATE_INT);
        if ($page_id && self::verify_customPage($page_id)) {
            $result = dbquery("DELETE FROM ".DB_CUSTOM_PAGES." WHERE page_id=:pageid", [':pageid' => $page_id]);
            if ($result) {
                dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_url='viewpage.php?page_id=:pageid'", [':pageid' => $page_id]);
            }
        }
    }

    /**
     * Authenticate the page ID is valid
     *
     * @param $id
     *
     * @return bool|string
     */
    protected static function verify_customPage($id) {
        $page_id = filter_var($id, FILTER_VALIDATE_INT);
        if ($page_id) {
            return dbcount("(page_id)", DB_CUSTOM_PAGES, "page_id=:pageid", [':pageid' => $page_id]);
        }

        return FALSE;
    }

}
