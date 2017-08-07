<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Search_Engine.inc
| Author: Frederick MC Chan
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Search;

use PHPFusion\Search;

class Search_Engine extends Search_Model {

    protected static $search_instance = NULL;

    /*
     * Template
     * Adds a third option to replace output template.
     * Just extend and declare your own by mutating the following
     * strings.
     *
     * In order to access the variables, extend your class to Search_Engine!
     */
    protected static $render_search = '';
    protected static $search_no_result = '';
    protected static $search_count = '';
    protected static $search_item_wrapper = '';

    protected static $search_item = '';
    protected static $search_item_list = '';
    protected static $search_item_image = '';
    public static $locale = array();

    /**
     * Returns params
     * @param null $key
     * @return array|null
     */
    public static function get_param($key = NULL) {
        $info = [
            'stype' => self::$search_type,
            'stext' => self::$search_text,
            'method' => self::$search_method,
            'datelimit' => self::$search_date_limit,
            'fields' => self::$search_fields,
            'sort' => self::$search_sort,
            'chars' => self::$search_chars,
            'order' => self::$search_order,
            'forum_id' => self::$forum_id,
            'memory_limit' => self::$memory_limit,
            'composevars' => self::$composevars,
            'rowstart' => self::$rowstart,
            'search_param' => self::$search_param,
        ];

        return $key === NULL ? $info : (isset($info[$key]) ? $info[$key] : NULL);
    }

    /**
     * Returns the search engine instance
     * @return null|static
     */
    public static function getInstance() {
        if (self::$search_instance === NULL) {
            self::$search_instance = new Static();
            self::$search_instance->init();
        }
        return self::$search_instance;
    }

    private function __sleep() {
    }

    protected function __construct() {
        self::$locale = fusion_get_locale('', LOCALE.LOCALESET.'search.php');
    }

    /**
     * Returns the search locale
     * @return array|null|string
     */
    protected function get_search_locale() {
        return self::$locale;
    }
    /**
     * Controller for search form
     */
    protected static function display_search_form() {
        $locale = self::$locale;
        add_to_title($locale['global_202']);
        $form_elements = self::$form_config['form_elements'];
        /*
         * Search Areas
         */
        $options_table = "<p><strong>".$locale['405']."</strong></p><table style='width:100%'>\n";
        if (!empty(self::$form_config['radio_button'])) {
            foreach (self::$form_config['radio_button'] as $key => $value) {
                $options_table .= "<tr>\n<td>".$value."</td>\n</tr>\n";
            }
        }
        $options_table .= "<tr>\n<td>\n
        ".form_checkbox('stype', $locale['407'], self::get_param('stype'), array(
                               'type' => 'radio',
                               'value' => 'all',
                               'onclick' => 'display(this.value)',
                               'reverse_label' => TRUE
                           )
        )."</td>\n</tr>\n</table>\n";

        /*
         * Date limit
         */
        $date_opts = array(
            '0'        => $locale['421'],
            '86400'    => $locale['422'],
            '604800'   => $locale['423'],
            '1209600'  => $locale['424'],
            '2419200'  => $locale['425'],
            '7257600'  => $locale['426'],
            '14515200' => $locale['427'],
        );
        $disabled_status = FALSE;
        if (isset($form_elements[self::get_param('stype')]['disabled'])) {
            $disabled_status = !empty($form_elements[self::get_param('stype')]['disabled']) ? TRUE : FALSE;
            if (self::get_param('stype') != 'all') {
                $disabled_status = in_array("datelimit", $form_elements[self::get_param('stype')]['disabled']) ? TRUE : FALSE;
            }
        }
        if (self::get_param('stype') == "all") {
            $disabled_status = TRUE;
        }
        //$search_areas = "<p><strong>".$locale['406']."</strong></p>\n";
        $search_areas = "<table style='width:100%'>\n<tr>\n";
        $search_areas .= "<td>".$locale['420']."</td>\n";
        $search_areas .= "<td>\n";
        $search_areas .= form_select('datelimit', '', self::get_param('datelimit'),
                         array(
                             'inner_width' => '150px',
                             'options' => $date_opts,
                             'deactivate' => $disabled_status
                         ));
        $search_areas .= "</td>\n";
        $search_areas .= "</tr>\n<tr>\n";
        $search_areas .= "<td>&nbsp;</td>\n";
        $search_areas .= "<td>";
        $search_areas .= form_checkbox('fields', $locale['430'], self::get_param('fields'),
                           array(
                               'type'          => 'radio',
                               'value'         => '2',
                               'reverse_label' => TRUE,
                               'input_id'      => 'fields1',
                               'deactivate'    => (self::get_param('stype') != "all" ? (isset($form_elements[self::get_param('stype')]) && in_array("fields1", $form_elements[self::get_param('stype')]['disabled']) ? TRUE : FALSE) : FALSE)
                           )
        );
        $search_areas .= form_checkbox('fields', $locale['431'], self::get_param('fields'),
                           array(
                               'type'          => 'radio',
                               'value'         => '1',
                               'reverse_label' => TRUE,
                               'input_id'      => 'fields2',
                               'deactivate'    => (self::get_param('stype') != "all" ? (isset($form_elements[self::get_param('stype')]) && in_array("fields2", $form_elements[self::get_param('stype')]['disabled']) ? TRUE : FALSE) : FALSE)
                           )
        );
        $search_areas .= form_checkbox('fields', $locale['432'], self::get_param('fields'),
                           array(
                               'type' => 'radio',
                               'value' => '0',
                               'reverse_label' => TRUE,
                               'input_id' => 'fields3',
                               'deactivate' => (self::get_param('stype') != "all" ? (isset($form_elements[self::get_param('stype')]) && in_array("fields3",
                                                                                                                                                 $form_elements[self::get_param('stype')]['disabled']) ? TRUE : FALSE) : FALSE)
                           )
        );
        $search_areas .= "</td>\n</tr>\n</table>\n";
        /*
         * Sort
         */
        $sort_opts = array(
            'datestamp' => $locale['441'],
            'subject' => $locale['442'],
            'author' => $locale['443']
        );
        $sort = "<table class='table'><td>".$locale['440']."&nbsp;</td>\n";
        $sort .= "<td>\n";
        $sort .= form_select('sort', '', self::get_param('sort'), array(
            'inner_width' => '150px',
            'options' => $sort_opts,
            'deactivate' => (self::get_param('stype') != "all" ? (isset($form_elements[self::get_param('stype')]) && in_array("sort",
                                                                                                                              $form_elements[self::get_param('stype')]['disabled']) ? TRUE : FALSE) : FALSE)
        ));
        $sort .= form_checkbox('order', $locale['450'], self::get_param('order'),
                                array(
                                    'type' => 'radio',
                                    'value' => '0',
                                    'reverse_label' => TRUE,
                                    'input_id' => 'order1',
                                    'deactivate' => (self::get_param('stype') != "all" ? (isset($form_elements[self::get_param('stype')]) && in_array("order1",
                                                                                                                                                      $form_elements[self::get_param('stype')]['disabled']) ? TRUE : FALSE) : FALSE)
                                )
        );
        $sort .= form_checkbox('order', $locale['451'], self::get_param('order'),
                                array(
                                    'type' => 'radio',
                                    'value' => '1',
                                    'reverse_label' => TRUE,
                                    'input_id' => 'order2',
                                    'deactivate' => (self::get_param('stype') != "all" ? (isset($form_elements[self::get_param('stype')]) && in_array("order2", $form_elements[self::get_param('stype')]['disabled']) ? TRUE : FALSE) : FALSE)
                                )
        );
        $sort .= "</td>\n</tr>\n</table>\n";
        /*
         * Char list
         */
        $char_opts = array(
            '50' => '50',
            '100' => '100',
            '150' => '150',
            '200' => '200'
        );
        $char_areas = "<table class='table'><tr><td>".$locale['460']."</td>\n";
        $char_areas .= "<td>\n";
        $char_areas .= form_select('chars', '', self::get_param('chars'), array(
                                    'inner_width' => '150px',
                                    'options' => $char_opts,
                                    'deactivate' => (self::get_param('stype') != "all" ? (isset($form_elements[self::get_param('stype')]) && in_array("chars",
                                                                                                                                                      $form_elements[self::get_param('stype')]['disabled']) ? TRUE : FALSE) : FALSE)
                                )
        );
        $char_areas .= "</td>\n</tr>\n</table>\n";

        /*
         * Bind
         */
        $info = [
            'openform' => openform('advanced_search_form', 'post', BASEDIR.'search.php'),
            'closeform' => closeform(),
            'search_form_stext' => form_text('stext', str_replace('[SITENAME]', fusion_get_settings('sitename'), self::$locale['400']), urldecode(self::get_param('stext')), array('inline'=> FALSE, 'placeholder'=>$locale['401'])),
            'search_form_button' => form_button('search', $locale['402'], $locale['402'], array('class' => 'btn-primary')),
            'search_form_method' => form_checkbox('method', '', self::get_param('method'),
                                                       array(
                                                           "options" => array(
                                                               'OR' => $locale['403'],
                                                               'AND' => $locale['404']
                                                           ),
                                                           'type' => 'radio',
                                                           'reverse_label' => TRUE,
                                                       )),
            'search_form_sources' => $options_table,
            'search_areas' => $search_areas,
            'sort_areas' => $sort,
            'char_areas' => $char_areas
        ];
        /*
         * Replace
         */
        echo $info['openform'];
        echo strtr(Search::render_search(), array(
            '{%title%}' => str_replace('[SITENAME]', fusion_get_settings('sitename'), self::$locale['400']),
            '{%search_text%}' => $info['search_form_stext'],
            '{%search_button%}' => $info['search_form_button'],
            '{%search_method%}' => $info['search_form_method'],
            '{%search_sources%}' => $info['search_form_sources'],
            '{%search_areas%}' => $info['search_areas'],
            '{%sort_areas%}' => $info['sort_areas'],
            '{%char_areas%}' => $info['char_areas'],
        ));
        echo $info['closeform'];
        /*
         * Javascript
         */
        $search_js = "function display(val) {\nswitch (val) {\n";
        foreach ($form_elements as $type => $array1) {
            $search_js .= "case '".$type."':\n";
            foreach ($array1 as $what => $array2) {
                foreach ($array2 as $elements => $value) {
                    if ($what == "enabled") {
                        $search_js .= "document.getElementById('".$value."').disabled = false;\n";
                    } else {
                        if ($what == "disabled") {
                            $search_js .= "document.getElementById('".$value."').disabled = true;\n";
                        } else {
                            if ($what == "display") {
                                $search_js .= "document.getElementById('".$value."').style.display = 'block';\n";
                            } else {
                                if ($what == "nodisplay") {
                                    $search_js .= "document.getElementById('".$value."').style.display = 'none';\n";
                                }
                            }
                        }
                    }
                }
            }
            $search_js .= "break;\n";
        }
        $search_js .= "case 'all':\n";
        $search_js .= "document.getElementById('datelimit').disabled = false;\n";
        $search_js .= "document.getElementById('fields1').disabled = false;\n";
        $search_js .= "document.getElementById('fields2').disabled = false;\n";
        $search_js .= "document.getElementById('fields3').disabled = false;\n";
        $search_js .= "document.getElementById('sort').disabled = false;\n";
        $search_js .= "document.getElementById('order1').disabled = false;\n";
        $search_js .= "document.getElementById('order2').disabled = false;\n";
        $search_js .= "document.getElementById('chars').disabled = false;\n";
        $search_js .= "break;}}";
        add_to_footer("<script type='text/javascript'>".jsminify($search_js)."</script>");
    }
    /**
     * Controller for display the search results
     */
    protected static function display_results() {
        $locale = self::$locale;
        self::$composevars = "method=".self::get_param('method')."&amp;datelimit=".self::get_param('datelimit')."&amp;fields=".self::get_param('fields')."&amp;sort=".self::get_param('sort')."&amp;order=".self::get_param('order')."&amp;chars=".self::get_param('chars')."&amp;forum_id=".self::get_param('forum_id')."&amp;";
        add_to_title($locale['global_201'].$locale['408']);

        $search_text = explode(' ', urldecode(self::$search_text));
        $qualified_search_text = array();
        $disqualified_search_text = array();

        /*
         * @todo: roadmap on author
         */
        self::$fields_count = self::get_param('fields') + 1;
        for ($i = 0, $k = 0; $i < count($search_text); $i++) {
            if (strlen($search_text[$i]) >= 3) {
                $qualified_search_text[] = $search_text[$i];
                for ($j = 0; $j < self::$fields_count; $j++) {
                    // It is splitting to 2 parts.
                    self::$search_param[':sword'.$k.$j] = '%'.$search_text[$i].'%';
                }
                $k++;
            } else {
                $disqualified_search_text[] = $search_text[$i];
            }
        }
        unset($search_text);
        self::$swords = $qualified_search_text;

        self::$c_swords = count($qualified_search_text) ?: redirect(FUSION_SELF);
        self::$i_swords = count($disqualified_search_text);

        self::$swords_keys_for_query = array_keys(self::$search_param);
        self::$swords_values_for_query = array_values(self::$search_param);

        // Highlight using Jquery the words. This, can actually parse as settings.
        $highlighted_text = "";
        $i = 1;
        foreach ($qualified_search_text as $value) {
            $highlighted_text .= "'".$value."'";
            $highlighted_text .= ($i < self::$c_swords ? "," : "");
            $i++;
        }
        add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.highlight.js'></script>");
        add_to_jquery("$('.search_result').highlight([".$highlighted_text."],{wordsOnly:true}); $('.highlight').css({backgroundColor:'#FFFF88'});");

        /*
         * Run the drivers via include.. but this method need to change to simplify the kiss concept.
         */
        if (self::get_param('stype') == "all") {
            $dh = opendir(INCLUDES."search");
            while (FALSE !== ($entry = readdir($dh))) {
                if ($entry != "." && $entry != ".." && preg_match("/include.php/i", $entry)) {
                    self::__Load($entry);
                }
            }
            closedir($dh);
        } else {
            self::__Load("search_".self::get_param('stype')."_include.php");
        }

        // Show how many disqualified search texts
        $c_iwords = count($disqualified_search_text);
        if ($c_iwords) {
            $txt = "";
            for ($i = 0; $i < $c_iwords; $i++) {
                $txt .= $disqualified_search_text[$i].($i < $c_iwords - 1 ? ", " : "");
            }
            echo "<div class='well m-t-10 text-center strong'>".sprintf($locale['502'], $txt)."</div><br />";
        }

        $c_search_result_array = count(self::$search_result_array);

        if (self::get_param('stype') == "all") {
            $from = self::get_param('rowstart');
            $to = ($c_search_result_array - (self::get_param('rowstart') + 10)) <= 0 ? $c_search_result_array : self::get_param('rowstart') + 10;
        } else {
            $from = 0;
            $to = $c_search_result_array < 10 ? $c_search_result_array : 10;
        }

        /*
         * HTML output
         */
        if (self::get_param('stype') == "all") {
            parent::search_navigation(0);
            echo strtr(Search::render_search_count(), [
                '{%search_count%}' => self::$items_count,
                '{%result_text%}' => ((self::$site_search_count > 100 || parent::search_globalarray("")) ? "<br/>".sprintf($locale['530'], self::$site_search_count) : "<br/>".self::$site_search_count." ".$locale['510'])
            ]);
        }
        else {
            echo strtr(Search::render_search_count(), [
                '{%search_count%}' => self::$items_count,
                '{%result_text%}' => ((self::$site_search_count > 100 || parent::search_globalarray("")) ? "<br/><strong>".sprintf($locale['530'], self::$site_search_count)."</strong>" : $locale['500'])
            ]);
        }

        echo "<div class='search_result'>\n";
        echo "<div class='block'>\n";
        foreach(self::$search_result_array as $results) {
            echo $results;
        }

        // Now it is by per module. Therefore rowstart does not apply
        //for ($i = $from; $i < $to; $i++) {
          //  echo self::$search_result_array[$i];
        //}
        echo "</div>\n";
        echo "</div>\n";

        //echo self::$navigation_result;
    }
    /**
     * Controller for omitting search
     */
    protected static function display_noResults() {
        $locale = self::$locale;
        add_to_title($locale['global_201'].$locale['408']);
        echo strtr(Search::render_search_no_result(), [
            '{%title%}' => $locale['408'],
            '{%content%}' => $locale['501'],
        ]);
    }
    /**
     * Prevents class cloning
     */
    private function __clone() {}
    /**
     * Load the search driver file
     * - Prevents string mutation
     * @param $path
     */
    protected static function __Load($path) {
        $file = INCLUDES.'search/'.$path;
        if (file_exists($file)) {
            include($file);
        }
    }
}