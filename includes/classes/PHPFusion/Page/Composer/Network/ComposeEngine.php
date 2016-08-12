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

    private static $composerData = array();

    public static function displayContent() {

        self::load_ComposerData();

        // This is the composer
        //echo form_button('add_row', 'Add Row', 'add row', array('class' => 'btn-primary m-r-10'));
        ?>
        <div class="composerAction m-b-20">
            <a class="btn btn-primary m-r-10"
               href="<?php echo clean_request('compose=add_row', array('compose'), FALSE) ?>" title="Add Row">
                Add New Row
            </a>
        </div>

        <?php
        if (isset($_POST['cancel_row'])) {
            redirect(clean_request('', array('compose'), FALSE));
        }
        if (isset($_GET['compose']) && $_GET['compose'] == 'add_row') {
            if (isset($_POST['save_row'])) {
                self::validate_RowData();
                self::execute_gridUpdate();
            }
            self::display_row_form();
        }
        ?>

        <section id='pageComposerLayout' class="m-t-20">

            <?php foreach (self::$composerData as $row_id => $columns) : ?>

                <div class="well">
                    <div class="pull-right sortable btn btn-xs m-r-10 m-b-10 display-inline-block">
                        <i class="fa fa-arrows-alt"></i>
                    </div>
                    <div class="btn-group btn-group-sm m-b-10">
                        <?php
                        echo form_button('add_compo', '', 'add_compo',
                                         array('icon' => 'fa fa-dashboard', 'alt' => 'Add Component')).
                            form_button('add_col', '', 'add_col',
                                        array('icon' => 'fa fa-plus-circle', 'alt' => 'Add Column')).
                            form_button('set_prop', '', 'set_prop',
                                        array('icon' => 'fa fa-cog', 'alt' => 'Configure Properties'));
                        ?>
                    </div>
                    <div class="btn-group btn-group-sm m-b-10">
                        <?php
                        echo form_button('copy_row', '', 'copy_row',
                                         array('icon' => 'fa fa-copy', 'alt' => 'Duplicate Row')).
                            form_button('del_col', '', 'del_col',
                                        array('icon' => 'fa fa-minus-circle', 'alt' => 'Remove Column')).
                            form_button('del_row', '', 'del_row',
                                        array('class' => 'btn-danger', 'icon' => 'fa fa-trash', 'alt' => 'Delete Row'));
                        ?>
                    </div>
                    <?php if (!empty($columns)) : ?>
                        <div class="row">
                            <?php foreach ($columns as $column_id => $columnData) : ?>
                                <div class="<?php echo self::calculateSpan($columnData['page_grid_column_count'],
                                                                           count($columns)) ?>">
                                    <div class="list-group-item m-t-10">
                                        <?php echo $columnData['page_content'] ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="list-group-item text-center">Add Content</div>
                    <?php endif; ?>
                </div>

            <?php endforeach; ?>

        </section>
        <?php
    }

    public static function load_ComposerData() {
        $query = "SELECT rows.*, col.*
        FROM ".DB_CUSTOM_PAGES_GRID." rows
        #LEFT JOIN ".DB_CUSTOM_PAGES_CONTENT." col USING(page_grid_id)
        LEFT JOIN ".DB_CUSTOM_PAGES_CONTENT." col ON col.page_grid_id = rows.page_grid_id
        WHERE rows.page_id=".self::$data['page_id']."
        ORDER BY rows.page_grid_order ASC, col.page_content_order ASC
        ";
        self::$composerData = dbquery_tree_full(DB_CUSTOM_PAGES_CONTENT, 'page_content_id', 'page_grid_id', FALSE,
                                                $query);
        print_p(self::$composerData);
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
            redirect(clean_request('', array('compose'), false));
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