<?php
namespace PHPFusion\Form;

use Defender\Token;

class CategoryMeta {

    private static $instance = NULL;

    private $factory = NULL;

    public static function getInstance(FormFactory $factory) {
        if (empty(self::$instance)) {
            self::$instance = new self($factory);
        }

        return self::$instance;
    }

    public function __construct(FormFactory $factory) {
        if ($factory instanceof FormFactory) {
            $this->factory = $factory;
        }
    }

    public function displayMeta() {
        $_category = $this->factory->categories;

        if (!empty($_category)) {

            $category_default_data = [
                'db'               => '', // database
                'id_col'           => '', // id column
                'cat_col'          => '', // category column
                'title_col'        => '', // title column
                'custom_query'     => '',
                'unroot'           => TRUE, // true to allow 'uncategorized' options.
                'multiple'         => FALSE, // true for checkboxes instead of radios
                'parent_db'        => '', // current item db
                'parent_id_col'    => '', // current item db id column
                'parent_cat_col'   => '', // current item db category column
                'parent_title_col' => '',
                'select2_disabled' => TRUE,
            ];

            $_category += $category_default_data;

            $tab['title'][] = 'All Categories';
            $tab['id'][] = 'adminc_1';
            $tab['title'][] = 'Most Used';
            $tab['id'][] = 'adminc_2';
            $tab_active = tab_active($tab, 0);
            $html = opentab($tab, $tab_active, 'admin_ctab', FALSE, 'tab-sm m-t-10');
            $html .= opentabbody($tab['title'][0], $tab['id'][0], $tab_active);
            $html .= "<div id='admin_category_list' style='max-height:200px; overflow-y:scroll;'>\n";

            $cat_options = [];
            if ($_category['unroot'] === TRUE) {
                $cat_options[0] = "Uncategorized";
            }

            $id_col = [
                $_category['id_col'],
                $_category['cat_col'],
                $_category['title_col'],
            ];

            $sql = "SELECT `{ID}` FROM `{DB}` ORDER BY `{ORDER}`"; // we need a hierarchy ui in the UL checkboxes

            $sql = strtr($sql, [
                '`{ID}`'    => implode(',', array_filter($id_col)),
                '`{DB}`'    => $_category['db'],
                '`{ORDER}`' => $_category['title_col'].' ASC'
            ]);

            $sql = !empty($_category['custom_query']) ? $_category['custom_query'] : $sql;

            $result = dbquery($sql);
            if (dbrows($result)) {
                while ($data = dbarray($result)) {
                    $cat_options[$data[$_category['id_col']]] = $data [$_category['title_col']];
                }
            }
            $category_value = $this->factory->field_value('category');
            if (!empty($cat_options)) {
                if ($_category['multiple'] === TRUE) {
                    $html .= form_checkbox('category[]', '', $category_value, [
                        'type'    => 'checkbox',
                        'options' => $cat_options,
                        'class'   => 'm-0'
                    ]);
                } else {
                    $html .= form_checkbox('category', '', $category_value, [
                        'type'    => 'radio',
                        'options' => $cat_options,
                        'class'   => 'm-0'
                    ]);
                }
            }
            $html .= "</div>\n";
            $html .= closetabbody();
            $html .= opentabbody($tab['title'][1], $tab['id'][1], $tab_active);
            $html .= closetabbody();
            $html .= closetab();
            $html .= '<div class="m-t-5"><a class="admin-new-ui-cat-btn" href="#"><small>+ Add new category</small></a></div>
            <div class="admin-new-ui-cat m-t-10" style="display: none;">
            '.form_text('ui_cat_title', '', '').'
            <div id="ui_cat_select">'.form_select('ui_cat_parent', '', '', $_category).'</div>
            '.form_button('save_new_ui_cat', 'Add New Category <span class="fa fa-clock-o fa-spin" style="display: none;"></span>', 'save_category', ['type' => 'button']).'
            </div>';

            $encoded = json_encode($_category);

            add_to_jquery("admin_cat_meta_ui.ajaxCall('".Token::generate_token('ui-category')."', $encoded)");

            return (string)$html;
        }
    }
}