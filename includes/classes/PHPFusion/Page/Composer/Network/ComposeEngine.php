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

    // Base request section, action, cpid, composer_tab,

    private static $composerData = array();
    private static $widgets = array();
    private static $widget_exclude_list = ".|..|.htaccess|.DS_Store|config.php|config.temp.php|.gitignore|LICENSE|README.md|robots.txt";
    private static $composer_exclude = array('compose', 'row_id', 'col_id', 'widget_type');

    public static function displayContent() {

        self::load_ComposerData();

        // This is the composer
        //echo form_button('add_row', 'Add Row', 'add row', array('class' => 'btn-primary m-r-10'));
        ?>
        <div class="composerAction m-b-20">
            <a class="btn btn-primary m-r-10"
               href="<?php echo clean_request('compose=add_row', self::$composer_exclude, FALSE) ?>" title="Add Row">
                Add New Row
            </a>
        </div>

        <?php
        if (isset($_POST['cancel_row'])) {
            redirect(clean_request('', self::$composer_exclude, FALSE));
        }

        if (isset($_GET['compose'])) {
            switch ($_GET['compose']) {
                case "add_row":
                    if (isset($_POST['save_row'])) {
                        self::validate_RowData();
                        self::execute_gridUpdate();
                    }
                    self::display_row_form();
                    break;
                case "add_col":
                    self::cache_widget();
                    self::display_col_form();
                    break;
                case "configure_col":
                    // Do php execution for page content on Widgets
                    if (isset($_GET['row_id']) && isnum($_GET['row_id'])) {
                        self::cache_widget();
                        self::get_colData();
                        self::display_widget_form();
                    }
                    break;
                case "del_col":
                    if (isset($_GET['row_id']) && isnum($_GET['row_id']) && isset($_GET['col_id']) && isnum($_GET['col_id'])) {
                        self::get_colData();
                        $delCondition = "page_content_id=".intval($_GET['col_id'])." AND page_grid_id=".intval($_GET['row_id']);
                        if (dbcount("('page_content_id')", DB_CUSTOM_PAGES_CONTENT, $delCondition)) {
                            dbquery_order(DB_CUSTOM_PAGES_CONTENT, self::$colData['page_content_order'],
                                          'page_content_order',
                                          self::$data['page_content_id'], 'page_content_id',
                                          self::$colData['page_grid_id'], 'page_grid_id',
                                          FALSE, '', 'delete');
                            dbquery("DELETE FROM ".DB_CUSTOM_PAGES_CONTENT." WHERE $delCondition");
                            addNotice("success", "Column Deleted");
                        }
                    }
                    redirect(clean_request('', self::$composer_exclude, FALSE));
                    break;
                case 'copy_col':
                    break;
            }
        }
        ?>
        <section id='pageComposerLayout' class="m-t-20">

            <?php foreach (self::$composerData as $row_id => $columns) : ?>
                <?php
                $add_col_url = clean_request("compose=add_col&row_id=".$row_id, self::$composer_exclude, FALSE);
                ?>
                <div class="well">
                    <div class="pull-right sortable btn btn-xs m-r-10 m-b-10 display-inline-block">
                        <i class="fa fa-arrows-alt"></i>
                    </div>
                    <div class="btn-group btn-group-sm m-b-10">
                        <a class='btn btn-default' href="<?php echo $add_col_url ?>" title="Add Column"><i
                                class="fa fa-plus-circle"></i></a>
                        <?php
                        form_button('set_prop', '', 'set_prop',
                                    array('icon' => 'fa fa-cog', 'alt' => 'Configure Properties'));
                        ?>
                    </div>
                    <div class="btn-group btn-group-sm m-b-10">
                        <?php
                        echo form_button('copy_row', '', 'copy_row',
                                         array('icon' => 'fa fa-copy', 'alt' => 'Duplicate Row')).
                            form_button('del_row', '', 'del_row',
                                        array('class' => 'btn-danger', 'icon' => 'fa fa-trash', 'alt' => 'Delete Row'));
                        ?>
                    </div>
                    <?php if (!empty($columns)) : ?>
                        <div class="row">
                            <?php
                            foreach ($columns as $column_id => $columnData) :
                                self::draw_cols($columnData, $columns);
                            endforeach;
                            ?>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endforeach; ?>

        </section>
        <?php
    }

    public static function load_ComposerData() {
        $query = "SELECT rows.*, col.page_id, col.page_content_id, col.page_content_type, col.page_content, col.page_content_order, col.page_widget
        FROM ".DB_CUSTOM_PAGES_GRID." rows
        LEFT JOIN ".DB_CUSTOM_PAGES_CONTENT." col USING(page_grid_id)
        WHERE rows.page_id=".self::$data['page_id']."
        ORDER BY rows.page_grid_order ASC, col.page_content_order ASC
        ";
        $result = dbquery($query);
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                if (!empty($data['page_content_id'])) {
                    // is a column
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
        //print_p(self::$composerData);
    }

    private static function validate_RowData() {

        self::$rowData = array(
            'page_grid_id' => form_sanitizer($_POST['page_grid_id'], '0', 'page_grid_id'),
            'page_id' => self::$data['page_id'],
            'page_grid_column_count' => form_sanitizer($_POST['page_grid_column_count'], 1, 'page_grid_column_count'),
            'page_grid_html_id' => form_sanitizer($_POST['page_grid_html_id'], '', 'page_grid_html_id'),
            'page_grid_class' => form_sanitizer($_POST['page_grid_class'], '', 'page_grid_class'),
            'page_grid_order' => form_sanitizer($_POST['page_grid_order'], 0, 'page_grid_order')
        );

        if (empty(self::$rowData['page_grid_order'])) {
            self::$rowData['page_grid_order'] = dbresult(
                    dbquery("SELECT COUNT(page_grid_id) FROM ".DB_CUSTOM_PAGES_GRID." WHERE page_id=".self::$data['page_id']),
                    0
                ) + 1;
        }
    }

    public static function execute_gridUpdate() {
        if (\defender::safe()) {
            if (!empty(self::$rowData['page_grid_id'])) {
                dbquery_order(DB_CUSTOM_PAGES_GRID, self::$rowData['page_grid_order'], 'page_grid_order',
                              self::$rowData['page_grid_id'], 'page_grid_id', 0, FALSE, FALSE, '', 'update');
                dbquery_insert(DB_CUSTOM_PAGES_GRID, self::$rowData, 'update');
            } else {
                dbquery_order(DB_CUSTOM_PAGES_GRID, self::$rowData['page_grid_order'], 'page_grid_order',
                              self::$rowData['page_grid_id'], 'page_grid_id', 0, FALSE, FALSE, '', 'save');
                dbquery_insert(DB_CUSTOM_PAGES_GRID, self::$rowData, 'save');
            }
            redirect(clean_request('', array('compose'), FALSE));
        }
    }

    private static function display_row_form() {
        ob_start();
        echo openmodal('addRowfrm', 'Add New Row', array('static' => TRUE)).
            openform('rowform', 'post', FUSION_REQUEST).
            form_hidden('page_grid_id', '', self::$rowData['page_grid_id']).
            form_btngroup('page_grid_column_count', 'Number of Columns', self::$rowData['page_grid_column_count'],
                          array(
                              'options' => array(
                                  1 => '1 Column',
                                  2 => '2 Columns',
                                  3 => '3 Columns',
                                  4 => '4 Columns',
                                  6 => '6 Columns',
                                  12 => '12 Columns'
                              ),
                              'inline' => TRUE,
                          )
            ).
            form_text('page_grid_html_id', 'Row ID', self::$rowData['page_grid_html_id'],
                      array('placeholder' => 'HTML Id', 'inline' => TRUE,)).
            form_text('page_grid_class', 'Row Class', self::$rowData['page_grid_class'],
                      array('placeholder' => 'HTML Class', 'inline' => TRUE,)).
            form_text('page_grid_order', 'Row Order', self::$rowData['page_grid_order'],
                      array('type' => 'number', 'inline' => TRUE, 'width' => '150px')).
            form_button('save_row', 'Save Row', 'save_row', array('class' => 'btn-primary m-r-10')).
            form_button('cancel_row', 'Cancel', 'cancel_row').
            closeform();
        echo closemodal();
        add_to_footer(ob_get_contents());
        ob_end_clean();
    }

    private static function cache_widget() {
        if (empty(self::$widgets)) {
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
                if (
                    file_exists(WIDGETS.$folder."/locale/".LANGUAGE.".php") &&
                    file_exists(WIDGETS.$folder."/".$folder."_widget.php") &&
                    file_exists(WIDGETS.$folder."/".$folder.".php")
                ) {
                    include WIDGETS.$folder."/".$folder."_widget.php";
                    // Creates object for Administration
                    if (iADMIN && !empty($widget_admin_callback) && file_exists(WIDGETS.$folder."/".$widget_admin_file)) {
                        require_once WIDGETS.$folder."/".$widget_admin_file;
                        if (class_exists($widget_admin_callback)) {
                            $class = new \ReflectionClass($widget_admin_callback);
                            $adminObj = $class->newInstance();
                        }
                    }

                    if (!empty($widget_display_callback) && !empty($widget_display_callback) && file_exists(WIDGETS.$folder."/".$widget_display_callback)) {
                        require_once WIDGETS.$folder."/".$widget_display_file;
                        if (class_exists($widget_display_callback)) {
                            $class = new \ReflectionClass($widget_admin_callback);
                            $displayObj = $class->newInstance();
                        }
                    }

                    $list[$folder] = array(
                        'widget_name' => $folder,
                        'widget_title' => ucfirst($widget_title),
                        'widget_icon' => $widget_icon,
                        'widget_description' => $widget_description,
                        'admin_instance' => $adminObj,
                        'display_instance' => $displayObj,
                    );
                }
            }
            self::$widgets = $list;
        }

        return self::$widgets;
    }

    private static function display_col_form() {
        ob_start();
        if (isset($_GET['row_id']) && isnum($_GET['row_id']) && isset($_GET['compose']) && $_GET['compose'] == 'add_col') :
            echo openmodal('addColfrm', 'Widget List', array('static' => TRUE)); ?>
            <div class="p-b-20 m-0 clearfix">
                <?php if (!empty(self::cache_widget())) : ?>
                    <div class="row">
                        <?php foreach (self::cache_widget() as $widget_file => $widget) : ?>
                            <div class="col-xs-4 col-sm-3 text-center">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <?php echo $widget['widget_icon'] ?>
                                        <h5 class="m-t-0 m-b-0"><?php echo $widget['widget_title'] ?></h5>
                                        <?php echo $widget['widget_description'] ?>
                                    </div>
                                    <div class="panel-footer">
                                        <a class="btn btn-xs btn-primary" href="<?php echo clean_request(
                                            'compose=configure_col&row_id='.$_GET['row_id'].'&widget_type='.$widget['widget_name'],
                                            self::$composer_exclude, FALSE
                                        ) ?>">Select Widget</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php
            echo modalfooter("<a class='btn btn-sm btn-default' href='".clean_request('', self::$composer_exclude,
                                                                                      FALSE)."'>Cancel</a>");
            echo closemodal();
            add_to_footer(ob_get_contents()).ob_end_clean();
        else:
            redirect(clean_request('', self::$composer_exclude, FALSE));
        endif;
    }

    public static function get_colData() {
        if (!empty(self::$composerData) && isset($_GET['row_id']) && isset($_GET['col_id']) &&
            !empty(self::$composerData[$_GET['row_id']][$_GET['col_id']])
        ) {
            self::$colData = self::$composerData[$_GET['row_id']][$_GET['col_id']];
        }

        return self::$colData;
    }

    /**
     * In development
     */
    private static function display_widget_form() {

        if (!empty(self::$widgets[$_GET['widget_type']]) && isset($_GET['row_id']) && isnum($_GET['row_id'])) {

            $currentWidget = self::$widgets[$_GET['widget_type']];

            self::$colData['page_id'] = self::$data['page_id'];
            self::$colData['page_grid_id'] = self::$rowData['page_grid_id'];
            self::$colData['page_content_type'] = $currentWidget['widget_title'];
            self::$colData['page_widget'] = $currentWidget['widget_name'];

            $object = $currentWidget['admin_instance'];

            /**
             * Validation
             */
            if (method_exists($object,
                              'validate_input') && isset($_POST['save_widget']) || isset($_POST['save_and_close_widget'])
            ) {

                self::$colData = array(
                    'page_id' => self::$data['page_id'],
                    'page_grid_id' => self::$rowData['page_grid_id'],
                    'page_content_id' => self::$colData['page_content_id'],
                    'page_content_type' => $currentWidget['widget_title'],
                    'page_content' => $object->validate_input(),
                    'page_widget' => $currentWidget['widget_name'],
                    'page_content_order' => dbcount("(page_content_id)", DB_CUSTOM_PAGES_CONTENT,
                                                    "page_grid_id=".self::$rowData['page_grid_id']) + 1
                );

                if (\defender::safe()) {
                    if (self::$colData['page_content_id'] > 0) {
                        dbquery_order(DB_CUSTOM_PAGES_CONTENT, self::$colData['page_content_order'],
                                      'page_content_order',
                                      self::$data['page_content_id'], 'page_content_id', self::$colData['page_grid_id'],
                                      'page_grid_id',
                                      FALSE, '', 'update');
                        dbquery_insert(DB_CUSTOM_PAGES_CONTENT, self::$colData, 'update');
                        addNotice('success', 'Column Updated');
                    } else {
                        dbquery_order(DB_CUSTOM_PAGES_CONTENT, self::$colData['page_content_order'],
                                      'page_content_order',
                                      self::$data['page_content_id'], 'page_content_id', self::$colData['page_grid_id'],
                                      'page_grid_id',
                                      FALSE, '', 'save');
                        dbquery_insert(DB_CUSTOM_PAGES_CONTENT, self::$colData, 'save');
                        self::$colData['page_content_id'] = dblastid();
                        addNotice('success', 'Column Created');
                    }
                    if (isset($_POST['save_and_close_widget'])) {
                        redirect(clean_request('col_id='.self::$colData['page_content_id'], self::$composer_exclude,
                                               FALSE));
                    } else {
                        redirect(clean_request('col_id='.self::$colData['page_content_id'], array('col_id'), FALSE));
                    }

                }

            }

            $object_button = form_button('save_widget', 'Save Widget', 'save_widget',
                                         array('class' => 'btn btn-primary'));
            if (method_exists($object, 'display_button')) {
                ob_start();
                $object->display_Button();
                $object_button = ob_get_contents();
                ob_end_clean();
            }

            ob_start();
            echo openmodal('addWidgetfrm', $currentWidget['widget_title'], array('static' => FALSE)); ?>
            <?php echo openform('widgetFrm', 'POST', FUSION_REQUEST, array("enctype" => TRUE)); ?>
            <div class="p-b-20 m-0 clearfix">
                <?php
                if (method_exists($object, 'display_input')) {
                    $object->display_input();
                }
                ?>
            </div>
            <?php
            echo modalfooter($object_button."<a class='btn btn-sm btn-default' href='".clean_request('',
                                                                                                     self::$composer_exclude,
                                                                                                     FALSE)."'>Cancel</a>
            ");
            echo closeform();
            echo closemodal();
            add_to_footer(ob_get_contents()).ob_end_clean();
        } else {
            redirect(clean_request('', self::$composer_exclude, FALSE));
        }
    }

    public static function draw_cols($columnData, $columns) {

        if ($columnData['page_content_id']) :

            $edit_link = clean_request(
                'compose=configure_col&col_id='.$columnData['page_content_id'].'&row_id='.$columnData['page_grid_id'].'&widget_type='.$columnData['page_widget'],
                self::$composer_exclude,
                FALSE
            );
            $copy_link = clean_request(
                'compose=copy_col&col_id='.$columnData['page_content_id'].'&row_id='.$columnData['page_grid_id'],
                self::$composer_exclude,
                FALSE
            );
            $delete_link = clean_request(
                'compose=del_col&col_id='.$columnData['page_content_id'].'&row_id='.$columnData['page_grid_id'],
                self::$composer_exclude,
                FALSE
            );
            ?>

            <div class="<?php echo self::calculateSpan($columnData['page_grid_column_count'], count($columns)) ?>">
                <div class="list-group-item m-t-10 text-center">
                    <h5>
                        <?php echo ucfirst($columnData['page_content_type']) ?>
                    </h5>

                    <div class="btn-group btn-group-sm">
                        <a class="btn btn-default" href="<?php echo $edit_link ?>" title="Edit Column"><i
                                class="fa fa-cog"></i></a>
                        <a class="btn btn-default" href="<?php echo $copy_link ?>" title="Copy Column"><i
                                class="fa fa-copy"></i></a>
                        <a class="btn btn-default" href="<?php echo $delete_link ?>" title="Remove Column"><i
                                class="fa fa-minus-circle"></i></a>
                    </div>
                </div>
            </div>
        <?php endif;
    }

    /**
     * @param $max_column_limit - max grid count per row
     * @param $current_count - current actual count if is a fluid design
     * @return string
     */
    private static function calculateSpan($max_column_limit, $current_count) {
        $default_xs_size = 12;
        $fluid_default_sm_size = $current_count >= $max_column_limit ? floor(12 / $max_column_limit) : floor(12 / $current_count);
        $fluid_default_md_size = $current_count >= $max_column_limit ? 12 / $max_column_limit : floor(12 / $current_count);
        $fluid_default_lg_size = $current_count >= $max_column_limit ? 12 / $max_column_limit : floor(12 / $current_count);
        $default_sm_size = floor(12 / $max_column_limit);
        $default_md_size = floor(12 / $max_column_limit);
        $default_lg_size = floor(12 / $max_column_limit);

        return "col-xs-$default_xs_size col-sm-$default_sm_size col-md-$default_md_size col-lg-$default_lg_size";
    }

}