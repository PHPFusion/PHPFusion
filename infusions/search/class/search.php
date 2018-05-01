<?php

class Search extends \PHPFusion\Search\Search_Engine {

    private static $debug_logger = FALSE;
    private static $debug_ui = FALSE;
    private static $inputData = [];
    private static $SEARCH_PATTERNS = [];


    protected static function define_search_patterns() {
        self::$SEARCH_PATTERNS = [
            'members' => [
                'select'        => 'user_id, user_name, user_email, user_status, user_avatar, user_level',
                'db'            => DB_USERS,
                'pri'           => 'user_id',
                'stext'         => 'user_name',
                'regex_search'  => '/lookup=(\d+)/',
                'regex_index'   => 1, // if pattern matches, the key to look for as value for 'key' (i.e. $matches[$regex_key] = $key, ie. 'user_id' = 2522)
                'callback_func' => 'view_member',
            ],
            'sitemap' => [
                'select'        => 'link_id, link_name, link_url',
                'db'            => DB_SITE_LINKS,
                'pri'           => 'link_url',
                'stext'         => 'link_name',
                'regex_search'  => '/([^0-9]+)/',
                'regex_index'   => 1, // if pattern matches, the key to look for as value for 'key' (i.e. $matches[$regex_key] = $key, ie. 'user_id' = 2522)
                'callback_func' => 'view_sitemap',
            ],
        ];
        if (infusion_exists('news')) {
            self::$SEARCH_PATTERNS += [
                'news' => [
                    'select'        => 'tn.*, tu.user_id, tu.user_name, tu.user_status, ni.news_image, ni.news_image_t1, ni.news_image_t2',
                    'db'            => DB_NEWS." tn LEFT JOIN ".DB_NEWS_IMAGES." ni ON ni.news_id=tn.news_id LEFT JOIN ".DB_USERS." tu ON tu.user_id=tn.news_name",
                    'pri'           => 'tn.news_id',
                    'stext'         => 'news_subject',
                    'regex_search'  => '/news.php\?readmore=(\d+)/',
                    'regex_index'   => 1, // if pattern matches, the key to look for as value for 'key' (i.e. $matches[$regex_key] = $key, ie. 'user_id' = 2522)
                    'callback_func' => 'view_news',
                ]
            ];
        }
        if (infusion_exists('forum')) {
            self::$SEARCH_PATTERNS += [
                'forum' => [
                    'select'        => 'tn.*, tu.user_id, tu.user_name, tu.user_status, ni.news_image, ni.news_image_t1, ni.news_image_t2',
                    'db'            => DB_NEWS." tn LEFT JOIN ".DB_NEWS_IMAGES." ni ON ni.news_id=tn.news_id LEFT JOIN ".DB_USERS." tu ON tu.user_id=tn.news_name",
                    'pri'           => 'tn.news_id',
                    'stext'         => 'news_subject',
                    'regex_search'  => '/news.php\?readmore=(\d+)/',
                    'regex_index'   => 1, // if pattern matches, the key to look for as value for 'key' (i.e. $matches[$regex_key] = $key, ie. 'user_id' = 2522)
                    'callback_func' => 'view_news',
                ]
            ];
        }
    }

    protected static function unlogged_link($value) {
        return str_replace(
            array(
                '?',
                '&amp;',
                '&',
                'sref=search',
                fusion_get_settings('site_path'),
            ), array(), $value);
    }

    private static function log_page() {
        /*
         * Content I need to log is 3 things only so I can callback fast without running scans again.
         * Storage of an actual Array Storage with mbencode will be fine.
         * 1 - Image
         * 2 - Title
         * 3 - Description
         * 4 - Actual URL
         */
        self::$inputData = [
            'search_user'          => fusion_get_userdata('user_id'),
            'search_keywords'      => (!empty(self::$search_text) ? urldecode(self::$search_text) : ''),
            'search_type'          => 'all',
            'search_method'        => 'OR',
            'search_forum_id'      => 0,
            'search_datelimit'     => 0,
            'search_fields'        => 2,
            'search_sort'          => 'datestamp',
            'search_order'         => 0,
            'search_chars'         => 50,
            'search_datestamp'     => TIME,
            'search_status'        => 0, // yield results or not?
            'search_language'      => LANGUAGE,
            'search_ip'            => USER_IP,
            'search_ip_type'       => USER_IP_TYPE,
            'search_callback_type' => '',
            'search_callback_data' => ''
        ];
        /*
         * Log Input Search Conversion
         */
        $debug_html = '';
        $match_found = FALSE;

        foreach (self::$SEARCH_PATTERNS as $stype => $regex_info) {

            if (self::$debug_logger) {
                $debug_html .= '========================'.PHP_EOL;
                $debug_html .= '****** Type: '.$stype.PHP_EOL;
                $debug_html .= print_p($regex_info, FALSE, FALSE).PHP_EOL;
            }

            if (preg_check($regex_info['regex_search'], FUSION_REQUEST)) {
                preg_match($regex_info['regex_search'], FUSION_REQUEST, $matches);
                $query = "SELECT ".$regex_info['select']." FROM ".$regex_info['db']." WHERE ".$regex_info['pri']."=:item_id";
                $bind = self::unlogged_link(array(
                    ':item_id' => $matches[$regex_info['regex_index']]
                ));
                if (self::$debug_logger) {
                    $debug_html .= '****** Type: '.$stype.' PASSED THE REGEX SEARCH '.PHP_EOL;
                    $debug_html .= "Matches as following ".PHP_EOL;
                    $debug_html .= print_p($matches, FALSE, FALSE).PHP_EOL;
                    $debug_html .= '========================'.PHP_EOL;
                    $debug_html .= print_p($query, FALSE, FALSE).PHP_EOL;
                    $debug_html .= print_p($bind, FALSE, FALSE).PHP_EOL;
                    $debug_html .= '========================'.PHP_EOL;
                }
                $result = dbquery($query, $bind);
                if (dbrows($result) > 0) {
                    $data = dbarray($result);
                    if (isset($data[$regex_info['stext']])) {
                        self::$inputData['search_keywords'] = $data[$regex_info['stext']];
                    }
                    self::$inputData['search_callback_type'] = $stype;
                    self::$inputData['search_callback_data'] = \defender::encode($data);
                    $match_found = TRUE;
                    break;
                } elseif (self::$debug_logger) {
                    $debug_html .= print_p('could not find things', FALSE, FALSE).PHP_EOL;
                    $debug_html .= '========================'.PHP_EOL;
                }
            }
        }

        /*
         * Save Unique Searches... no, i need to update when logged
         */
        if (
            (!empty($_POST['stext']) && !empty($_POST['search']) && FUSION_SELF !== 'search.php' // This will redirect to Search.php and therefore will not have any material
                || isset($_GET['sref']) // This will log the search
            ) && iMEMBER
        ) {
            $search_query = "SELECT search_id FROM ".DB_SEARCH." WHERE search_keywords=:keywords AND search_user=:user AND search_ip=:ip and search_ip_type=:ip_type";
            $search_bind = array(
                ':keywords' => self::$inputData['search_keywords'],
                ':user'     => self::$inputData['search_user'],
                ':ip'       => self::$inputData['search_ip'],
                ':ip_type'  => self::$inputData['search_ip_type']
            );

            if (dbcount('(search_id)', DB_SEARCH, 'search_keywords=:keywords AND search_user=:user AND search_ip=:ip AND search_ip_type=:ip_type', $search_bind)) {
                if (self::$debug_logger) {
                    $debug_html .= '========================';
                    $debug_html .= print_p(self::$inputData, FALSE, FALSE).PHP_EOL;
                    $debug_html .= '========================';
                } else {
                    // get query and update
                    self::$inputData['search_id'] = dbresult(dbquery($search_query, $search_bind),0);
                    dbquery_insert(DB_SEARCH, self::$inputData, 'update');
                    addNotice('success', 'Search Input Updated');
                }
            } else {
                dbquery_insert(DB_SEARCH, self::$inputData, 'save');
                addNotice('success', 'Search Input Created');
            }
        }

        if (self::$debug_logger && isset($debug_html)) {
            print_p($debug_html, TRUE);
        }
        // do not log otherwise because these pages are not loggable.
    }

    /**
     * Template Assigns top Override Search Engine
     */
    private static function assign_template() {
        $item_list = "
        <div class='list-group-item br-t-0 clearfix'>
        <a class='text-black text-normal' href='{%item_url%}'>
            <div class='clearfix'>
            <div class='search_list_image'>{%item_image%}</div>
            <div class='overflow-hide'><span class='va' style='height:40px;'></span>
            <span class='va'><strong class='text-bigger'>{%item_title%}</strong><br/><span class='text-lighter'>{%item_description%}</span></span></div>            
            </div>
        </a>
        </div>";
        parent::$search_item_wrapper = "<div class='list-group-hover'>{%search_content%}</div>";
        parent::$search_item_list = $item_list;
        parent::$search_item = $item_list;
    }

    protected static $stext = '';

    public static function display_Input() {
        self::define_search_patterns();
        self::assign_template();
        self::log_page();
        if (!empty(self::$inputData['search_keywords'])) {
            self::$stext = stripinput(urldecode(self::$inputData['search_keywords']));
        } else {
            self::$stext = urldecode(self::$search_text);
        }

        $search_form = "
        <div id='typeahead' class='dropdown pull-left m-l-15'>
        ".openform('search_frm', 'post', BASEDIR.'search.php', ['class' => 'form-inline navbar-form navbar-left m-0', 'remote_url' => fusion_get_settings('site_path').'search.php']).
            form_text('stext', '', self::$stext,
                [
                    'input_id'           => 'typeahead_search',
                    'placeholder'        => 'Search PHP-Fusion',
                    'class'              => 'm-b-0 p-l-0 input-sm typeahead',
                    'append'             => true,
                    'autocomplete_off'   => TRUE,
                    'append_button'      => TRUE,
                    'append_form_value'  => 'search',
                    'append_class'       => 'btn btn-default',
                    'append_button_name' => 'search',
                    'append_value'       => "<i class='fa fa-search fa-lg m-r-5 m-l-5'></i>\n",
                    //'remote_url'         => FUSION_ROOT.BASEDIR.'search.php?stype=all'
                ]
            ).closeform()."
            <div id='typeahead_result' class='dropdown-menu".(self::$debug_ui ? " display-block" : '')."'>".self::get_default_view()."</div>                             
        </div>
        ";

        $javaScript = "
        <script>        
        var delay = (function(){
        var timer = 0;
        return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
        };
        })();        
        $('#typeahead_search').focusin(function(e) {
            $('#typeahead').addClass('open');            
            var SearchInput = $('#typeahead_search');
            
            $(this).keyup(function(e) {                
                if (SearchInput.val().length > 0) {
                    $('#typeahead_result').html('<div class=\'text-center\'><img src=\'".IMAGES."loader.gif\'/></div>');
                    delay(function(e) {
                        var send = $(this).closest('form').serialize();
                        var data = { 'q' : SearchInput.val() } 
                        var xhr = send +'&'+$.param(data);                        
                        $.ajax({
                            url: '".FUSION_ROOT.SEARCH."search.php',
                            type: 'post',
                            dataType: 'html',
                            data : xhr,                            
                            complete: function(){                                
                                $('#typeahead').addClass('open');
                            },
                            success: function(result) {                                
                                $('#typeahead_result').html(result);
                            },                            
                            error: function() {
                                console.log('Typeahead Error');
                            }
                        })                        
                    }, 500);                                                                     
                }
            })                       
        }).focusout(function(e) {
            setTimeout(function() { $('#typeahead').removeClass('open'); }, 400);            
        });        
        </script>
        ";
        add_to_jquery(strtr($javaScript, ['<script>' => '', '</script>' => '']));

        return $search_form;
    }

    /*
     * Ajax search results loading
     */
    public static function get_search_results($q = '') {
        self::assign_template();
        if ($q) {
            self::$search_text = urlencode(stripinput($q));
            self::$composevars = "method=".parent::get_param('method')."&amp;datelimit=".parent::get_param('datelimit')."&amp;fields=".parent::get_param('fields')."&amp;sort=".self::get_param('sort')."&amp;order=".parent::get_param('order')."&amp;chars=".parent::get_param('chars')."&amp;forum_id=".parent::get_param('forum_id')."&amp;";
            $search_text = explode(' ', urldecode(parent::$search_text));
            $qualified_search_text = array();
            $disqualified_search_text = array();
            self::$fields_count = parent::get_param('fields') + 1;
            for ($i = 0, $k = 0; $i < count($search_text); $i++) {
                if (strlen($search_text[$i]) >= 3) {
                    $qualified_search_text[] = $search_text[$i];
                    for ($j = 0; $j < parent::$fields_count; $j++) {
                        // It is splitting to 2 parts.
                        parent::$search_param[':sword'.$k.$j] = '%'.$search_text[$i].'%';
                    }
                    $k++;
                } else {
                    $disqualified_search_text[] = $search_text[$i];
                }
            }
            unset($search_text);
            parent::$swords = $qualified_search_text;
            parent::$c_swords = count($qualified_search_text) ?: redirect(FUSION_SELF);
            parent::$i_swords = count($disqualified_search_text);
            parent::$swords_keys_for_query = array_keys(parent::$search_param);
            parent::$swords_values_for_query = array_values(parent::$search_param);
            /*
            * Run the drivers via include.. but this method need to change to simplify the kiss concept.
            */
            $dh = opendir(INCLUDES."search");
            while (FALSE !== ($entry = readdir($dh))) {
                if ($entry != "." && $entry != ".." && preg_match("/include.php/i", $entry)) {
                    parent::__Load($entry);
                }
            }
            closedir($dh);
            $c_search_result_array = count(self::$search_result_array);
            if (self::get_param('stype') == "all") {
                $from = parent::get_param('rowstart');
                $to = ($c_search_result_array - (parent::get_param('rowstart') + 10)) <= 0 ? $c_search_result_array : parent::get_param('rowstart') + 10;
            } else {
                $from = 0;
                $to = $c_search_result_array < 10 ? $c_search_result_array : 10;
            }
            for ($i = $from; $i < $to; $i++) {
                echo parent::$search_result_array[$i];
            }
            echo strtr(self::results_view(), array(
                    '{%search_url%}'      => BASEDIR.'search.php?stype=all&amp;stext='.$q,
                    '{%search_icon%}'     => "<img src='".INFUSIONS."search/history.svg' style='width:25px; margin-right: 5px;'/>",
                    '{%search_keywords%}' => "See all results for \"".$q."\"",
                )
            );
        }
    }

    /**
     * Default View return
     *
     * @return string
     */
    public static function get_default_view() {

        ob_start();
        $bind_array = array(
            ':user_id' => fusion_get_userdata('user_id'),
            ':ip'      => USER_IP,
            ':ip_type' => USER_IP_TYPE
        );
        if (dbcount('(search_id)', DB_SEARCH, 'search_user=:user_id AND search_ip=:ip AND search_ip_type=:ip_type', $bind_array)) {
            // show the basic search results
            $result = dbquery("SELECT * FROM ".DB_SEARCH." WHERE search_user=:user_id AND search_ip=:ip AND 
            search_ip_type=:ip_type ORDER BY search_datestamp DESC LIMIT 0, 5", $bind_array);

            if (dbrows($result) > 0) {

                $list_results = [];

                echo "<div class='search-title'>Your recent searches</div>";

                while ($data = dbarray($result)) {

                    $get_input = array(
                        'stext'     => $data['search_keywords'],
                        'method'    => $data['search_method'],
                        'forum_id'  => $data['search_forum_id'],
                        'datelimit' => $data['search_datelimit'],
                        'fields'    => $data['search_fields'],
                        'sort'      => $data['search_sort'],
                        'order'     => $data['search_order'],
                        'chars'     => $data['search_chars'],
                    );
                    $query = http_build_query($get_input, 'flags_', '&amp;');

                    if ($data['search_callback_type'] && $data['search_callback_data']) {

                        $method = self::$SEARCH_PATTERNS[$data['search_callback_type']]['callback_func'];
                        $value = \defender::decode($data['search_callback_data']);
                        // Group together
                        $list_results[$data['search_callback_type']][] = Search_View_Control::$method($value);

                    } else {

                        // The results
                        $list_results[0][] = strtr(self::results_view(), array(
                                '{%search_url%}'      => BASEDIR."search.php?".$query,
                                '{%search_icon%}'     => "<img src='".INFUSIONS."search/history.svg' style='width:25px; margin-right: 5px;'/>",
                                '{%search_keywords%}' => $data['search_keywords'],
                            )
                        );
                    }
                }
                echo strtr(parent::$search_item_wrapper,
                    array(
                        '{%search_content%}' => implode('', array_map(function ($e) {
                            return implode('', $e);
                        }, $list_results))
                    )
                );
            }
        } else {
            echo "<div class='search-title'><strong>Your recent searches</strong></div>";
            echo "<div class='list-group-item br-b-0'>You do not have any recent searches</div>\n";
        }

        return ob_get_clean();
    }

    /*
     * The results listing viewing
     */
    public static function results_view() {
        return "<a  class='hover-class' href='{%search_url%}'>\n
        <div class='list-group-item br-r-0' style='padding: 10px 5px;'>
        <div class='pull-left m-r-10 p-l-10'>{%search_icon%}</div>
        <div class='overflow-hide'><span class='va'>{%search_keywords%}</span></div>
        </div></a>
        ";
    }
}

require_once dirname(__FILE__).'/search_view_control.php';