<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Search_Engine.php
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

class Search_Engine extends SearchModel {

    public static $locale = [];

    /*
     * Template
     * Adds a third option to replace output template.
     * Just extend and declare your own by mutating the following
     * strings.
     *
     * In order to access the variables, extend your class to Search_Engine!
     */
    protected static $search_instance = NULL;

    protected function __construct() {
        parent::__construct();
        self::$locale = fusion_get_locale('', LOCALE.LOCALESET.'search.php');
    }

    /**
     * Returns the search engine instance
     *
     * @return null|static
     */
    public static function getInstance() {
        if (self::$search_instance === NULL) {
            self::$search_instance = new static();
            self::$search_instance->init();
        }

        return self::$search_instance;
    }

    /**
     * Controller for search form
     */
    protected static function displaySearchForm() {
        $locale = self::$locale;
        add_to_title($locale['global_202']);
        $form_elements = self::$form_config['form_elements'];

        /*
         * Search Areas
         */
        $options_table = [];
        $options_table['radio_buttons'] = [];
        if (!empty(self::$form_config['radio_button'])) {
            foreach (self::$form_config['radio_button'] as $value) {
                $options_table['radio_buttons'][] = $value;
            }
        }
        $options_table['radio_buttons'][] = form_checkbox('stype', $locale['407'], self::get_param('stype'), [
            'type'          => 'radio',
            'value'         => 'all',
            'onclick'       => 'display(this.value)',
            'reverse_label' => TRUE
        ]);

        /*
         * Date limit
         */
        $disabled_status = FALSE;
        if (isset($form_elements[self::get_param('stype')]['disabled'])) {
            $disabled_status = !empty($form_elements[self::get_param('stype')]['disabled']);
            if (self::get_param('stype') != 'all') {
                $disabled_status = in_array("datelimit", $form_elements[self::get_param('stype')]['disabled']);
            }
        }

        if (self::get_param('stype') == "all") {
            $disabled_status = TRUE;
        }

        $search_areas = [];
        $search_areas['datelimit'] = form_select('datelimit', '', self::get_param('datelimit'), [
            'inner_width' => '150px',
            'options'     => [
                '0'        => $locale['421'],
                '86400'    => $locale['422'],
                '604800'   => $locale['423'],
                '1209600'  => $locale['424'],
                '2419200'  => $locale['425'],
                '7257600'  => $locale['426'],
                '14515200' => $locale['427']
            ],
            'deactivate'  => $disabled_status
        ]);
        $search_areas['title_message'] = form_checkbox('fields', $locale['430'], self::get_param('fields'), [
            'type'          => 'radio',
            'value'         => '2',
            'reverse_label' => TRUE,
            'input_id'      => 'fields1',
            'class'         => 'm-b-0',
            'deactivate'    => (self::get_param('stype') != "all" && isset($form_elements[self::get_param('stype')]) && in_array("fields1", $form_elements[self::get_param('stype')]['disabled']))
        ]);
        $search_areas['message'] = form_checkbox('fields', $locale['431'], self::get_param('fields'), [
            'type'          => 'radio',
            'value'         => '1',
            'reverse_label' => TRUE,
            'input_id'      => 'fields2',
            'class'         => 'm-b-0',
            'deactivate'    => (self::get_param('stype') != "all" && isset($form_elements[self::get_param('stype')]) && in_array("fields2", $form_elements[self::get_param('stype')]['disabled']))
        ]);
        $search_areas['title'] = form_checkbox('fields', $locale['432'], self::get_param('fields'), [
            'type'          => 'radio',
            'value'         => '0',
            'reverse_label' => TRUE,
            'input_id'      => 'fields3',
            'class'         => 'm-b-0',
            'deactivate'    => (self::get_param('stype') != "all" && isset($form_elements[self::get_param('stype')]) && in_array("fields3", $form_elements[self::get_param('stype')]['disabled']))
        ]);

        /*
         * Sort
         */
        $sort = [];
        $sort['sort'] = form_select('sort', '', self::get_param('sort'), [
            'inner_width' => '150px',
            'options'     => [
                'datestamp' => $locale['441'],
                'subject'   => $locale['442'],
                'author'    => $locale['443']
            ],
            'deactivate'  => (self::get_param('stype') != "all" && isset($form_elements[self::get_param('stype')]) && in_array("sort", $form_elements[self::get_param('stype')]['disabled']))
        ]);
        $sort['desc'] = form_checkbox('order', $locale['450'], self::get_param('order'), [
            'type'          => 'radio',
            'value'         => '0',
            'reverse_label' => TRUE,
            'input_id'      => 'order1',
            'class'         => 'm-b-0',
            'deactivate'    => (self::get_param('stype') != "all" && isset($form_elements[self::get_param('stype')]) && in_array("order1", $form_elements[self::get_param('stype')]['disabled']))
        ]);
        $sort['asc'] = form_checkbox('order', $locale['451'], self::get_param('order'), [
            'type'          => 'radio',
            'value'         => '1',
            'reverse_label' => TRUE,
            'input_id'      => 'order2',
            'class'         => 'm-b-0',
            'deactivate'    => (self::get_param('stype') != "all" && isset($form_elements[self::get_param('stype')]) && in_array("order2", $form_elements[self::get_param('stype')]['disabled']))
        ]);

        /*
         * Char list
         */
        $char_areas = form_select('chars', '', self::get_param('chars'), [
            'inner_width' => '150px',
            'options'     => [
                '50'  => '50',
                '100' => '100',
                '150' => '150',
                '200' => '200'
            ],
            'deactivate'  => (self::get_param('stype') != "all" && isset($form_elements[self::get_param('stype')]) && in_array("chars", $form_elements[self::get_param('stype')]['disabled']))
        ]);

        /*
         * Bind
         */
        $info = [
            'openform'       => openform('advanced_search_form', 'post', BASEDIR.'search.php'),
            'closeform'      => closeform(),
            'search_text'    => form_text('stext', str_replace('[SITENAME]', fusion_get_settings('sitename'), self::$locale['400']), urldecode(self::get_param('stext')), ['inline' => FALSE, 'placeholder' => $locale['401']]),
            'search_button'  => form_button('search', $locale['402'], $locale['402'], ['class' => 'btn-primary']),
            'search_method'  => form_checkbox('method', '', self::get_param('method'),
                [
                    "options"       => [
                        'OR'  => $locale['403'],
                        'AND' => $locale['404']
                    ],
                    'type'          => 'radio',
                    'reverse_label' => TRUE,
                ]),
            'search_sources' => $options_table,
            'search_areas'   => $search_areas,
            'sort_areas'     => $sort,
            'char_areas'     => $char_areas,
            'title'          => str_replace('[SITENAME]', fusion_get_settings('sitename'), self::$locale['400'])
        ];

        echo $info['openform'];
        echo render_search($info);
        echo $info['closeform'];

        /*
         * Javascript
         */
        $search_js = "function display(val) {switch (val) {";
        foreach ($form_elements as $type => $array1) {
            $search_js .= "case '".$type."':";
            foreach ($array1 as $what => $array2) {
                foreach ($array2 as $value) {
                    if ($what == "enabled") {
                        $search_js .= "document.getElementById('".$value."').disabled = false;";
                    } else {
                        if ($what == "disabled") {
                            $search_js .= "document.getElementById('".$value."').disabled = true;";
                        } else {
                            if ($what == "display") {
                                $search_js .= "document.getElementById('".$value."').style.display = 'block';";
                            } else {
                                if ($what == "nodisplay") {
                                    $search_js .= "document.getElementById('".$value."').style.display = 'none';";
                                }
                            }
                        }
                    }
                }
            }
            $search_js .= "break;\n";
        }
        $search_js .= "case 'all':\n";
        $search_js .= "document.getElementById('datelimit').disabled = false;";
        $search_js .= "document.getElementById('fields1').disabled = false;";
        $search_js .= "document.getElementById('fields2').disabled = false;";
        $search_js .= "document.getElementById('fields3').disabled = false;";
        $search_js .= "document.getElementById('sort').disabled = false;";
        $search_js .= "document.getElementById('order1').disabled = false;";
        $search_js .= "document.getElementById('order2').disabled = false;";
        $search_js .= "document.getElementById('chars').disabled = false;";
        $search_js .= "break;}}";
        add_to_footer('<script>'.jsminify($search_js).'</script>');
    }

    /**
     * Returns params
     *
     * @param string $key
     *
     * @return array|string
     */
    public static function get_param($key = NULL) {
        $info = [];
        try {
            $info = [
                'stype'        => stripinput(self::$search_type),
                'stext'        => stripinput(self::$search_text),
                'method'       => stripinput(self::$search_method),
                'datelimit'    => self::$search_date_limit,
                'fields'       => self::$search_fields,
                'sort'         => self::$search_sort,
                'chars'        => stripinput(self::$search_chars),
                'order'        => self::$search_order,
                'forum_id'     => self::$forum_id,
                'memory_limit' => self::$memory_limit,
                'composevars'  => self::$composevars,
                'rowstart'     => self::$rowstart,
                'search_param' => self::$search_param,
            ];
        } catch (\Exception $e) {
            redirect(BASEDIR.fusion_get_settings('opening_page'));
        }


        return $key === NULL ? $info : (isset($info[$key]) ? $info[$key] : NULL);
    }

    /**
     * Controller for display the search results
     */
    protected static function displayResults() {
        $locale = self::$locale;
        self::$composevars = "method=".self::get_param('method')."&datelimit=".self::get_param('datelimit')."&fields=".self::get_param('fields')."&sort=".self::get_param('sort')."&order=".self::get_param('order')."&chars=".self::get_param('chars')."&forum_id=".self::get_param('forum_id')."&";
        add_to_title($locale['global_201'].$locale['408']);

        $search_text = explode(' ', urldecode(self::$search_text));
        $qualified_search_text = [];
        $disqualified_search_text = [];

        self::$fields_count = self::get_param('fields') + 1;
        for ($i = 0, $k = 0; $i < count($search_text); $i++) {
            if (strlen($search_text[$i]) >= 3) {
                $qualified_search_text[] = htmlentities($search_text[$i]);
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

        self::$c_swords = count($qualified_search_text);
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

        add_to_footer("<script type='text/javascript' src='".INCLUDES."jquery/jquery.highlight.js'></script>");
        add_to_jquery("$('.search_result .results').highlight([".$highlighted_text."],{wordsOnly:true}); $('.highlight').css({backgroundColor:'#FFFF88'});");

        /*
         * Run the drivers via include. but this method need to change to simplify the kiss concept.
         */
        if (self::get_param('stype') == "all") {
            $search_deffiles = [];
            $search_includefiles = makefilelist(INCLUDES.'search/', '.|..|index.php|location.json.php|users.json.php|.DS_Store', TRUE);
            $search_infusionfiles = makefilelist(INFUSIONS, '.|..|index.php', TRUE, 'folders');
            if (!empty($search_infusionfiles)) {
                foreach ($search_infusionfiles as $files_to_check) {
                    if (is_dir(INFUSIONS.$files_to_check.'/search/')) {
                        $search_checkfiles = makefilelist(INFUSIONS.$files_to_check.'/search/', ".|..|index.php", TRUE);
                        $search_deffiles = array_merge($search_deffiles, $search_checkfiles);
                    }
                }
            }
            $search_files = array_merge($search_includefiles, $search_deffiles);

            foreach ($search_files as $file_to_check) {
                if (preg_match("/include.php/i", $file_to_check)) {
                    if (file_exists(INCLUDES."search/".$file_to_check)) {
                        self::loadDriver(INCLUDES."search/".$file_to_check);
                    }

                    foreach ($search_infusionfiles as $inf_files_to_check) {
                        if (file_exists(INFUSIONS.$inf_files_to_check.'/search/'.$file_to_check)) {
                            self::loadDriver(INFUSIONS.$inf_files_to_check.'/search/'.$file_to_check);
                        }
                    }
                }
            }
        } else {
            if (file_exists(INCLUDES."search/search_".self::get_param('stype')."_include.php")) {
                self::loadDriver(INCLUDES."search/search_".self::get_param('stype')."_include.php");
            }

            $search_infusionfiles = makefilelist(INFUSIONS, '.|..|index.php', TRUE, 'folders');
            foreach ($search_infusionfiles as $inf_files_to_check) {
                if (file_exists(INFUSIONS.$inf_files_to_check.'/search/search_'.self::get_param('stype').'_include.php')) {
                    self::loadDriver(INFUSIONS.$inf_files_to_check.'/search/search_'.self::get_param('stype').'_include.php');
                }
            }
        }

        $info = [];

        // Show how many disqualified search texts
        $c_iwords = count($disqualified_search_text);
        if ($c_iwords) {
            $txt = "";
            for ($i = 0; $i < $c_iwords; $i++) {
                $txt .= $disqualified_search_text[$i].($i < $c_iwords - 1 ? ", " : "");
            }

            $info['disqualified_stexts'] = sprintf($locale['502'], $txt);
        }

        /*
         * HTML output
         */
        $info['search_count'] = self::$items_count;
        if (self::get_param('stype') == "all") {
            ob_start();
            parent::search_navigation(0);
            $info['navigation'] = ob_get_contents();
            ob_end_clean();
            $info['result_text'] = ((self::$site_search_count > 100 || parent::search_globalarray("")) ? "<br/>".sprintf($locale['530'], self::$site_search_count) : "<br/>".self::$site_search_count." ".$locale['510']);
        } else {
            $info['result_text'] = ((self::$site_search_count > 100 || parent::search_globalarray("")) ? "<br/><strong>".sprintf($locale['530'], self::$site_search_count)."</strong>" : (empty(self::$site_search_count) ? $locale['500'] : ''));
        }

        $info['results'] = implode('', self::$search_result_array);

        $info['navigation_result'] = '';
        if (self::get_param('stype') != "all") {
            $info['navigation_result'] = self::$navigation_result;
        }

        echo render_search_count($info);
    }

    /**
     * Load the search driver file
     * - Prevents string mutation
     *
     * @param string $path
     */
    protected static function loadDriver($path) {
        include_once($path);
    }

    /**
     * Controller for omitting search
     */
    protected static function displayNoResults() {
        $locale = self::$locale;
        add_to_title($locale['global_201'].$locale['408']);
        echo render_search_no_result([
            'title'   => $locale['408'],
            'content' => $locale['501'],
        ]);
    }

    /**
     * Prevents class cloning
     */
    private function __clone() {
    }

    public static function displaySearch() {
        echo '<div class="search-page">';
        self::displaySearchForm();
        if (strlen(self::get_param('stext')) >= 3) {
            self::displayResults();
        } else if (check_post('stext')) {
            self::displayNoResults();
        }
        echo '</div>';
    }
}
