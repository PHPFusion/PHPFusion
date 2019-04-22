<?php

namespace PHPFusion\Form;
/**
 * Class TagsMeta
 * Meta Tags UI
 *
 * @package PHPFusion\Form
 */
class TagsMeta {

    private static $tags = [];

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

    public function process_tags() {

        $_tags = self::getTagsConfig();

        $tags_input = stripinput($_POST['tags']);

        if (!empty($tags_input)) {

            $tags_input = array_map( function($val) {
                return "'$val'";
            }, explode(',', $tags_input));

            if (!empty($_tags['db']) && !empty($_tags['title_col']) && !empty($_tags['id_col'])) {

                // eee,ddd,asd.
                $sql = /** @lang text */
                    "SELECT {ID}, {TITLE} FROM {DB} WHERE {TITLE} IN ({TAGS_INPUT})";
                if (!empty($_tags['language_col'])) {
                    $sql = /** @lang text */
                        "SELECT {ID}, {TITLE} FROM {DB} WHERE {TITLE} IN ({TAGS_INPUT}) AND {LANG}='".LANGUAGE."'";
                }

                if (\Defender::safe()) {

                    $sql = strtr($sql, [
                        '{ID}' => $_tags['id_col'],
                        '{TITLE}' => $_tags['title_col'],
                        '{DB}' => $_tags['db'],
                        '{LANG}' => $_tags['language_col'],
                        '{TAGS_INPUT}' => implode(',', $tags_input),
                    ]);

                    $check = dbquery($sql);
                    if (!dbrows($check)) {
                        // split and insert
                        foreach($tags_input as $tag_value) {
                            $tag_value = trim($tag_value, "'");
                            $data[$_tags['title_col']] = trim($tag_value);
                            if (!empty($_tags['language_col'])) {
                                $data[$_tags['language_col']] = LANGUAGE;
                            }
                            dbquery_insert($_tags['db'], $data, 'save', ['keep_session'=>TRUE]);
                        }
                    }

                }
            }
        }
    }

    private function getTagsConfig() {

        if (empty(self::$tags)) {

            $tags_default = [
                'db'               => DB_TAGS, // database
                'id_col'           => 'tag_id',
                'title_col'        => 'tag_title', // title column
                'language_col'     => 'tag_language',
                'custom_query'     => '',
                'parent_db'        => '', // current item db
                'parent_id_col'    => '', // current item db id column
                'parent_cat_col'   => '', // current item db category column
                'parent_title_col' => '',
            ];

            $_tags = $this->factory->tags;

            if (!empty($_tags)) {
                self::$tags = $_tags;
            }

            self::$tags += $tags_default;
        }

        return (array)self::$tags;
    }

    public function displayMeta() {

        $_tags = self::getTagsConfig();

        if (!empty($_tags)) {

            $html = form_text('admin_ui_tags', '', '', [
                'placeholder'        => 'Enter tag name',
                'ext_tip' => '<small>Seperate tags with commas</small>',
                'append'             => TRUE,
                'append_button'      => TRUE,
                'append_type'        => 'button',
                'append_form_value'  => 'add_tag',
                'append_class'       => 'btn-default',
                'append_value'       => 'Add',
                'append_button_name' => 'add_tag',
                'append_button_id'   => 'admin_ui_tags_button',
                'class' => 'm-0',
            ]);

            /*
             * $options['append_button'] = true
             * $options['append_type'] = button or submit
             * $options['append_form_value'] = the value of the button
             * $options['append_class'] = your pick of .btn classes (bootstrap .btn-success, .btn-info, etc)
             * $options['append_value'] = the label
             * $options['append_button_name'] = your button name , default: p-submit-".$options['input_id']."
             * $options['append_button_id'] = your button name , default: ".$input_name."-append-btn
             */

            $html .= "<ul id='admin_tags_list'>\n";

            $tags = array_filter(explode(',', $this->factory->field_value('tags')));
            if (!empty($tags)) {
                foreach($tags as $index => $value) {
                    if ($value) {
                        $html .= "<li><button type='button' id='tag-$index' class='admin_ui_tag_del'><i class='far fa-times-circle' aria-hidden='true'></i></button>$value</li>";
                    }
                }
            }
            $html .= "</ul>\n";
            $html .= form_hidden('tags', '', $this->factory->field_value('tags'));
            $html .= "<a id='admin_ui_tags_list' class='pointer'><small>Choose from the most used tags</small></a>";
            $html .= "<div id='admin_ui_common_tags' style='display: none;'>\n";

            $id_col = [
                $_tags['id_col'],
                $_tags['title_col'],
            ];
            $sql = "SELECT `{ID}` FROM `{DB}` ORDER BY `{ORDER}`"; // we need a hierarchy ui in the UL checkboxes
            $sql = strtr($sql, [
                '`{ID}`'    => implode(',', array_filter($id_col)),
                '`{DB}`'    => $_tags['db'],
                '`{ORDER}`' => $_tags['title_col'].' ASC'
            ]);

            $sql = !empty($_tags['custom_query']) ? $_tags['custom_query'] : $sql;

            $result = dbquery($sql);

            if (dbrows($result)) {
                while ($data = dbarray($result)) {
                    $html .= form_button('tags_'.$data[ $_tags['title_col']], $data[ $_tags['title_col']], $data[ $_tags['title_col']], [
                        'class'=>'btn-link p-0 btn-tag', 'type'=>'button']
                    );
                }
            } else {
                $html .= "<small>There are no common tags defined.</small>";
            }
            $html .= "</div>\n";

            return (string) $html;
        }
    }

}