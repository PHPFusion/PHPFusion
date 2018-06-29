<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Network/ComposeEngine.inc
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
namespace PHPFusion\Page\Composer\Node;

use PHPFusion\Page\PageAdmin;

class ComposeEngine extends PageAdmin {

    // Base request section, action, cpid, composer_tab,
    private static $composer_exclude = ['compose', 'row_id', 'col_id', 'widget_type', 'widgetKey', 'widgetAction'];

    /**
     * Get the page composer exclude string
     *
     * @return array
     */
    public static function getComposerExclude() {
        return self::$composer_exclude;
    }

    /**
     * access       $gridData, $rowData, $composerData, $locale, $composer_exclude
     */
    public static function displayContent() {
        self::load_ComposerData();
        self::cache_widget();
        if (isset($_POST['cancel_row'])) {
            redirect(clean_request('', self::$composer_exclude, FALSE));
        }
        if (isset($_GET['compose'])) {
            switch ($_GET['compose']) {
                case "del_row":
                    self::execute_RowDelete();
                    break;
                case "copy_row":
                    // duplicate row
                    self::execute_RowDuplicate();
                    break;
                case "edit_row": // do not break
                case "add_row":
                    if (isset($_POST['save_row'])) {
                        self::validate_RowData();
                        self::execute_RowUpdate();
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
                        self::cache_widget();
                        self::get_colData();
                        $delCondition = "page_content_id=".intval($_GET['col_id'])." AND page_grid_id=".intval($_GET['row_id']);
                        if (dbcount("('page_content_id')", DB_CUSTOM_PAGES_CONTENT, $delCondition)) {
                            dbquery_order(DB_CUSTOM_PAGES_CONTENT, self::$colData['page_content_order'],
                                'page_content_order',
                                self::$data['page_content_id'], 'page_content_id',
                                self::$colData['page_grid_id'], 'page_grid_id',
                                FALSE, '', 'delete');

                            // execute the widget delete
                            $currentWidget = self::$widgets[self::$colData['page_widget']];
                            $object = $currentWidget['admin_instance'];
                            if (method_exists($object, 'validate_delete')) {
                                $object->validate_delete();
                            }

                            dbquery("DELETE FROM ".DB_CUSTOM_PAGES_CONTENT." WHERE $delCondition");

                            addNotice("success", self::$locale['page_0409a']);
                        }
                    }
                    redirect(clean_request('', self::$composer_exclude, FALSE));
                    break;
                case 'copy_col':
                    self::execute_ColDuplicate();
                    break;
            }
        }
        ?>
        <div class='composerAction m-b-20'>
            <a class='btn btn-primary m-r-10'
               href='<?php echo clean_request('compose=add_row', self::$composer_exclude, FALSE) ?>'>
                <?php echo self::$locale['page_0350'] ?>
            </a>
        </div>
        <section id='pageComposerLayout' class='spacer-sm'>
            <?php foreach (self::$composerData as $row_id => $columns) : ?>
                <?php
                $gridData = self::$gridData[$row_id];
                $add_col_url = clean_request("compose=add_col&row_id=".$row_id, self::$composer_exclude, FALSE);
                $edit_row_url = clean_request("compose=edit_row&row_id=".$row_id, self::$composer_exclude, FALSE);
                $copy_row_url = clean_request("compose=copy_row&row_id=".$row_id, self::$composer_exclude, FALSE);
                $del_row_url = clean_request("compose=del_row&row_id=".$row_id, self::$composer_exclude, FALSE);
                /*
                 * <div class="pull-right sortable btn btn-xs m-r-10 m-b-10 display-inline-block">
                        <i class="fa fa-arrows-alt"></i>
                    </div>
                 */
                // check if row has page_content_type == 'content'
                $_hasContent = FALSE;
                if (!empty($columns)) {
                    foreach ($columns as $column_data) {
                        if (!empty($column_data['page_content_type'])) {
                            $_hasContent = TRUE;
                        }
                    }
                }
                ?>
                <div class='well'>
                    <div class='btn-group btn-group-sm m-b-10'>
                        <a class='btn btn-default' href='<?php echo $add_col_url ?>'
                           title='<?php echo self::$locale['page_0351'] ?>'>
                            <i class='fa fa-plus-circle'></i>
                        </a>
                    </div>
                    <div class='pull-right'>
                        <?php if ($gridData['page_grid_html_id']) : ?>
                            <span class='label label-success'>HTML: #<?php echo $gridData['page_grid_html_id'] ?></span>
                        <?php endif; ?>
                        <?php if ($gridData['page_grid_class']) : ?>
                            <span class='label label-warning'>CSS: .<?php echo $gridData['page_grid_class'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class='btn-group btn-group-sm m-b-10'>
                        <a class='btn btn-default' href='<?php echo $edit_row_url ?>'
                           title='<?php echo self::$locale['page_0352'] ?>'>
                            <i class='fa fa-cog'></i>
                        </a>
                        <a class='btn btn-default' href='<?php echo $copy_row_url ?>'
                           title='<?php echo self::$locale['page_0353'] ?>'>
                            <i class='fa fa-copy'></i>
                        </a>
                        <a class='btn btn-danger' href='<?php echo $del_row_url ?>'
                           title='<?php echo self::$locale['page_0354'] ?>'>
                            <i class='fa fa-trash'></i>
                        </a>
                    </div>
                    <div class='row spacer-xs'>
                        <?php if (!empty($columns)) : ?>
                            <?php
                            foreach ($columns as $column_id => $columnData) :
                                self::draw_cols($columnData, $columns);
                            endforeach;
                            ?>
                        <?php endif; ?>
                        <?php
                        // grid is 3
                        // current column count is 1
                        $column_count = $_hasContent ? count($columns) : 0;
                        for ($i = $gridData['page_grid_column_count']; $i > $column_count; $i--) {
                            $style = "border:1px dashed #ccc; min-height:60px; content:''";
                            ?>
                            <div class='<?php echo self::calculateSpan($gridData['page_grid_column_count'], count($columns)) ?>'>
                                <div style='<?php echo $style ?>'></div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
        <?php
    }

    /**
     * Deletes Row and associated Columns
     */
    protected static function execute_RowDelete() {
        if (!empty(self::$rowData['page_grid_id'])) {
            $result = dbquery("SELECT * FROM ".DB_CUSTOM_PAGES_CONTENT." WHERE page_grid_id=:pagegrid", [':pagegrid' => self::$rowData['page_grid_id']]);
            if (dbrows($result) > 0) {
                while ($colData = dbarray($result)) {
                    dbquery_insert(DB_CUSTOM_PAGES_CONTENT, $colData, 'delete');
                }
            }
            dbquery_insert(DB_CUSTOM_PAGES_GRID, self::$rowData, 'delete');
            if (\defender::safe()) {
                addNotice('success', self::$locale['page_0403']);
            }
        } else {
            addNotice('danger', self::$locale['page_0404']);
        }
        redirect(clean_request('', self::$composer_exclude, FALSE));
    }

    /**
     * Duplicate Row and associated Columns
     */
    protected static function execute_RowDuplicate() {
        if (!empty(self::$rowData['page_grid_id'])) {
            // save new grid id.
            $rowData = self::$rowData;
            $rowData['page_grid_id'] = 0;
            $rowId = dbquery_insert(DB_CUSTOM_PAGES_GRID, $rowData, 'save');
            if (!$rowId) {
                \defender::stop();
                addNotice("danger", self::$locale['page_0405']);
            }
            // now check for all content and also duplicate it.
            $result = dbquery("SELECT * FROM ".DB_CUSTOM_PAGES_CONTENT." WHERE page_grid_id=:pagegrid", [':pagegrid' => self::$rowData['page_grid_id']]);
            if (dbrows($result) > 0) {
                while ($colData = dbarray($result)) {
                    $colData['page_content_id'] = 0; // resets the primary key
                    $colData['page_grid_id'] = $rowId;
                    $colId = dbquery_insert(DB_CUSTOM_PAGES_CONTENT, $colData, 'save');
                    if (!$colId) {
                        \defender::stop();
                        addNotice('danger', self::$locale['page_0406']);
                    }
                }
            }
            if (\defender::safe()) {
                addNotice('success', self::$locale['page_0407']);
            }
        } else {
            addNotice('danger', self::$locale['page_0404']);
        }
        redirect(clean_request('', self::$composer_exclude, FALSE));
    }

    protected static function validate_RowData() {

        self::$rowData = [
            'page_grid_id'           => form_sanitizer($_POST['page_grid_id'], '0', 'page_grid_id'),
            'page_id'                => self::$data['page_id'],
            'page_grid_column_count' => form_sanitizer($_POST['page_grid_column_count'], 1, 'page_grid_column_count'),
            'page_grid_html_id'      => form_sanitizer($_POST['page_grid_html_id'], '', 'page_grid_html_id'),
            'page_grid_container'    => form_sanitizer($_POST['page_grid_container'], '', 'page_grid_container'),
            'page_grid_class'        => form_sanitizer($_POST['page_grid_class'], '', 'page_grid_class'),
            'page_grid_order'        => form_sanitizer($_POST['page_grid_order'], 0, 'page_grid_order')
        ];

        if (empty(self::$rowData['page_grid_order'])) {
            self::$rowData['page_grid_order'] = dbresult(dbquery("SELECT COUNT(page_grid_id) FROM ".DB_CUSTOM_PAGES_GRID." WHERE page_id=:pageid", [':pageid' => self::$data['page_id']]),
                    0) + 1;
        }
    }

    protected static function execute_RowUpdate() {
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
            redirect(clean_request('', ['compose'], FALSE));
        }
    }

    private static function display_row_form() {
        ob_start();
        echo openmodal('addRowfrm',
                (isset($_GET['compose']) && $_GET['compose'] == 'edit_row' ? self::$locale['page_0352'] : self::$locale['page_0350']),
                ['static' => TRUE]).
            openform('rowform', 'post', FUSION_REQUEST).
            form_hidden('page_grid_id', '', self::$rowData['page_grid_id']).
            form_btngroup('page_grid_column_count', self::$locale['page_0380'], self::$rowData['page_grid_column_count'],
                [
                    'options' => [
                        1  => format_word(1, self::$locale['page_0381']),
                        2  => format_word(2, self::$locale['page_0381']),
                        3  => format_word(3, self::$locale['page_0381']),
                        4  => format_word(4, self::$locale['page_0381']),
                        6  => format_word(6, self::$locale['page_0381']),
                        12 => format_word(12, self::$locale['page_0381']),
                    ],
                    'inline'  => TRUE,
                ]
            ).
            form_btngroup('page_grid_container', self::$locale['page_0359'], self::$rowData['page_grid_container'],
                [
                    'options' => [0 => self::$locale['disable'], 1 => self::$locale['enable']],
                    'inline'  => TRUE,
                ]
            ).
            form_text('page_grid_html_id', self::$locale['page_0382'], self::$rowData['page_grid_html_id'],
                ['placeholder' => 'HTML Id', 'inline' => TRUE,]).
            form_text('page_grid_class', self::$locale['page_0383'], self::$rowData['page_grid_class'],
                ['placeholder' => 'HTML Class', 'inline' => TRUE,]).
            form_text('page_grid_order', self::$locale['page_0384'], self::$rowData['page_grid_order'],
                ['type' => 'number', 'inline' => TRUE, 'width' => '150px']).
            form_button('save_row', self::$locale['save'], 'save_row', ['class' => 'btn-primary m-r-10']).
            form_button('cancel_row', self::$locale['cancel'], 'cancel_row').
            closeform();
        echo closemodal();
        add_to_footer(ob_get_clean());
    }

    // Widget selection menu
    private static function display_col_form() {
        $widget_cache = self::cache_widget();
        ob_start();
        if (isset($_GET['row_id']) && isnum($_GET['row_id']) && isset($_GET['compose']) && $_GET['compose'] == 'add_col') :
            echo openmodal('addColfrm', self::$locale['page_0390'], ['static' => TRUE]); ?>
            <div class="p-b-20 m-0 clearfix">
                <?php
                if (!empty($widget_cache)) : ?>
                    <div class='row'>
                        <?php $i = 0; foreach (self::cache_widget() as $widget_file => $widget) :
                        if ($i > 3) {
                        	?>  </div><div class='row'> <?php $i=0;
                        }
                            ?>
                            <div class='col-xs-4 col-sm-3 text-center'>
                                <div class='panel panel-default'>
                                    <div class='panel-body'>
                                        <img style='width:40px; margin:15px;'
                                             src='<?php echo self::get_widget_icon(WIDGETS.$widget['widget_folder'].'/'.$widget['widget_icon']) ?>'/>
                                        <h5 class='m-t-0 m-b-0'><?php echo $widget['widget_title'] ?></h5>
                                        <?php echo $widget['widget_description'] ?>
                                    </div>
                                    <div class='panel-footer'>
                                        <a class='btn btn-primary'
                                           href='<?php echo clean_request('compose=configure_col&row_id='.$_GET['row_id'].'&widget_type='.$widget['widget_name'], self::$composer_exclude, FALSE) ?>'>
                                            <?php echo self::$locale['page_0391'] ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php $i++; endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php
            echo modalfooter("<a class='btn btn-sm btn-default' href='".clean_request('', self::$composer_exclude, FALSE)."'>".self::$locale['cancel']."</a>");
            echo closemodal();
            add_to_footer(ob_get_clean());
        else:
            redirect(clean_request('', self::$composer_exclude, FALSE));
        endif;
    }

    protected static function get_colData() {
        if (!empty(self::$composerData) && isset($_GET['row_id']) && isset($_GET['col_id']) &&
            !empty(self::$composerData[$_GET['row_id']][$_GET['col_id']])
        ) {
            self::$colData = self::$composerData[$_GET['row_id']][$_GET['col_id']];
        }

        return self::$colData;
    }

    private static function display_widget_form() {

        if (!empty(self::$widgets[$_GET['widget_type']]) && isset($_GET['row_id']) && isnum($_GET['row_id'])) {

            $currentWidget = self::$widgets[$_GET['widget_type']];

            self::$colData['page_id'] = self::$data['page_id'];
            self::$colData['page_grid_id'] = self::$rowData['page_grid_id'];
            self::$colData['page_content_type'] = $currentWidget['widget_title'];
            self::$colData['page_widget'] = $currentWidget['widget_name'];

            $object = $currentWidget['admin_instance'];
            if (method_exists($object, 'widgetInstance')) {
                $object = $object::widgetInstance();
            }

            /**
             * Validation
             */
            if (isset($_POST['save_widget']) || isset($_POST['save_and_close_widget'])) {
                $button_val = '';

                if (isset($_POST['save_widget'])) {
                    $button_val = stripinput($_POST['save_widget']);
                } else if (isset($_POST['save_and_close_widget'])) {
                    $button_val = stripinput($_POST['save_and_close_widget']);
                }

                self::$colData = [
                    'page_id'            => self::$data['page_id'],
                    'page_grid_id'       => self::$rowData['page_grid_id'],
                    'page_content_id'    => self::$colData['page_content_id'],
                    'page_content_type'  => $currentWidget['widget_title'],
                    'page_widget'        => $currentWidget['widget_name'],
                    'page_content_order' => (isset($_POST['page_content_order'])) ? form_sanitizer($_POST['page_content_order'], 0,
                        'page_content_order') :
                        self::$colData['page_content_order'],
                    'page_content'       => self::$colData['page_content'],
                    'page_options'       => self::$colData['page_options']
                ];

                if (self::$colData['page_content_order'] < 1) {
                    self::$colData['page_content_order'] = dbresult(dbquery("SELECT COUNT(page_content_id) 'content_count' FROM ".DB_CUSTOM_PAGES_CONTENT." WHERE page_grid_id=".self::$rowData['page_grid_id']),
                            0) + 1;
                }

                // Override the content or the options - depending on the button pushed. Default is previous data.
                if ($button_val == 'widget') {
                    if (method_exists($object, 'validate_input')) {
                        $input = $object->validate_input(); // will yield error
                        if ($input && \defender::unserialize($input)) {
                            self::$colData['page_content'] = $input;
                        }
                    }
                } else if ($button_val == 'settings') {
                    if (method_exists($object, 'validate_settings')) {
                        $input = $object->validate_settings();
                        if ($input && \defender::unserialize($input)) {
                            self::$colData['page_options'] = $input;
                        }
                    }
                }

                if (\defender::safe()) {
                    if (self::$colData['page_content_id'] > 0) {
                        dbquery_order(DB_CUSTOM_PAGES_CONTENT, self::$colData['page_content_order'],
                            'page_content_order',
                            self::$data['page_content_id'], 'page_content_id', self::$colData['page_grid_id'],
                            'page_grid_id',
                            FALSE, '', 'update');

                        dbquery_insert(DB_CUSTOM_PAGES_CONTENT, self::$colData, 'update');
                        addNotice('success', self::$locale['page_0408']);
                    } else {
                        dbquery_order(DB_CUSTOM_PAGES_CONTENT, self::$colData['page_content_order'],
                            'page_content_order',
                            self::$data['page_content_id'], 'page_content_id', self::$colData['page_grid_id'],
                            'page_grid_id',
                            FALSE, '', 'save');

                        dbquery_insert(DB_CUSTOM_PAGES_CONTENT, self::$colData, 'save');
                        self::$colData['page_content_id'] = dblastid();
                        addNotice('success', self::$locale['page_0409']);
                    }

                    if (method_exists($object, 'exclude_return')) {
                        if ($object->exclude_return()) {
                            self::$composer_exclude = array_merge(self::$composer_exclude, $object->exclude_return());
                        }
                    }

                    if (isset($_POST['save_and_close_widget'])) {
                        redirect(clean_request('col_id='.self::$colData['page_content_id'], self::$composer_exclude,
                            FALSE));
                    } else {

                        redirect(clean_request('col_id='.self::$colData['page_content_id'], ['col_id'], FALSE));

                    }

                }
            }

            $object_button = form_button('save_widget', self::$locale['page_0355'], 'save_widget', ['class' => 'btn btn-primary']);
            if (method_exists($object, 'display_form_button')) {
                ob_start();
                $object->display_form_button();
                $object_button = ob_get_contents();
                ob_end_clean();
            }

            ob_start();
            echo openmodal('addWidgetfrm', $currentWidget['widget_title'], ['static' => TRUE]); ?>

            <?php echo openform('widgetFrm', 'POST', FUSION_REQUEST, ["enctype" => TRUE]); ?>
            <div class="p-b-20 m-0 clearfix">
                <?php
                if (method_exists($object, 'display_form_input')) {
                    $object->display_form_input();
                }
                ?>
            </div>
            <?php echo form_text('page_content_order', self::$locale['page_0355a'], self::$colData['page_content_order'], [
                'type'        => 'number',
                'required'    => FALSE,
                'inline'      => TRUE,
                'inner_width' => '150px'
            ]); ?>

            <?php
            echo modalfooter($object_button."<a class='btn btn-default' href='".clean_request('',
                    self::$composer_exclude,
                    FALSE)."'>".self::$locale['cancel']."</a>
            ");
            echo closeform();
            echo closemodal();
            add_to_footer(ob_get_contents());
            ob_end_clean();
        } else {
            redirect(clean_request('', self::$composer_exclude, FALSE));
        }
    }

    /**
     * Duplicate a Column
     */
    protected static function execute_ColDuplicate() {
        if (isset($_GET['col_id']) && isnum($_GET['col_id'])) {
            $result = dbquery("SELECT * FROM ".DB_CUSTOM_PAGES_CONTENT." WHERE page_content_id=:pagecontent", [':pagecontent' => intval($_GET['col_id'])]);
            if (dbrows($result) > 0) {
                $data = dbarray($result);
                $data['page_content_id'] = 0;
                $data['page_content_order'] = dbcount("(page_content_id)", DB_CUSTOM_PAGES_CONTENT, "page_grid_id=".self::$rowData['page_grid_id']) + 1;

                $colId = dbquery_insert(DB_CUSTOM_PAGES_CONTENT, $data, 'save');
                if (!$colId) {
                    \defender::stop();
                    addNotice("danger", self::$locale['page_0406']);
                }
                addNotice("success", self::$locale['page_0411']);
            } else {
                addNotice("danger", self::$locale['page_0412']);
            }
        }
        redirect(clean_request('', self::$composer_exclude, FALSE));
    }

    protected static function get_widget_icon($widget_icon_path) {
        $default_widget = ADMIN.'images/widget.svg';

        return file_exists($widget_icon_path) && !is_dir($widget_icon_path) ? $widget_icon_path : $default_widget;
    }

    // Internal Administration Column Renderer
    protected static function draw_cols($colData, $columns) {
        if ($colData['page_content_id']) :
            $widget_path = '';
            if (isset(self::$widgets[$colData['page_widget']]['widget_icon'])) {
                $widget_path = WIDGETS.$colData['page_widget'].'/'.self::$widgets[$colData['page_widget']]['widget_icon'];
            }
            $widget_img_path = self::get_widget_icon($widget_path);
            $edit_link = clean_request('compose=configure_col&col_id='.$colData['page_content_id'].'&row_id='.$colData['page_grid_id'].'&widget_type='.$colData['page_widget'], self::$composer_exclude, FALSE);
            $copy_link = clean_request('compose=copy_col&col_id='.$colData['page_content_id'].'&row_id='.$colData['page_grid_id'], self::$composer_exclude, FALSE);
            $delete_link = clean_request('compose=del_col&col_id='.$colData['page_content_id'].'&row_id='.$colData['page_grid_id'], self::$composer_exclude, FALSE);
            ?>
            <div class='<?php echo self::calculateSpan($colData['page_grid_column_count'], count($columns)) ?>'>
                <div class='list-group-item m-t-5 m-b-5' style='border:1px solid #ddd; background: #fff;'>
                    <?php if (!empty($colData['page_widget'])) : ?>
                        <div class='pull-left m-r-10' style='width:40px;'>
                            <img class='img-responsive' alt='widget' src='<?php echo $widget_img_path ?>'/>
                        </div>
                        <div class='pull-right m-l-10'>
                            <div class='btn-group btn-group-sm'>
                                <a class='btn btn-default' href='<?php echo $edit_link ?>' title='<?php echo self::$locale['page_0356'] ?>'><i class='fa fa-cog'></i></a>
                                <a class='btn btn-default' href='<?php echo $copy_link ?>' title='<?php echo self::$locale['page_0357'] ?>'><i class='fa fa-copy'></i></a>
                                <a class='btn btn-default' href='<?php echo $delete_link ?>' title='<?php echo self::$locale['page_0358'] ?>'><i class='fa fa-minus-circle'></i></a>
                            </div>
                        </div>
                        <div class='overflow-hide'>
                            <h5 class='m-0' style='font-size:110%;'><?php
                                if ($colData['page_content_type'] == 'content') {
                                    echo self::$locale['page_0441'];
                                    echo "</h5>\n";
                                } else {
                                    echo ucfirst($colData['page_content_type'])."</h5>\n<i class='text-lighter'>";
                                    $current_widget = self::$widgets[$colData['page_widget']]['display_instance'];
                                    if (method_exists($current_widget, 'display_info')) {
                                        echo $current_widget->display_info($colData);
                                    } else {
                                        echo fusion_get_locale('na');
                                    }
                                    echo "</i>\n";
                                }
                                ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif;
    }
}
