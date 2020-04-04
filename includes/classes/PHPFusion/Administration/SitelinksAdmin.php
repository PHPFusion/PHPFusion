<?php
namespace PHPFusion\Administration;

use PHPFusion\SiteLinks;

class SitelinksAdmin {

    private static $instance = NULL;
    private static $default_display = 16;
    private $data = [
        'link_id'          => 0,
        'link_name'        => '',
        'link_url'         => '',
        'link_icon'        => '',
        'link_cat'         => 0,
        'link_language'    => LANGUAGE,
        'link_visibility'  => 0,
        'link_status'      => 1,
        'link_order'       => 0,
        'link_position'    => 1,
        'link_position_id' => 0,
        'link_window'      => 0,
    ];
    private $language_opts = [];
    private $link_index = [];
    private $form_action = '';

    private $aidlink = '';

    private $locale = [];
    private $cancel_action = '';
    private $settings = [];

    private function __construct() {
        $this->aidlink = fusion_get_aidlink();
        $this->locale = fusion_get_locale("", LOCALE.LOCALESET."admin/sitelinks.php");
        $this->language_opts = fusion_get_enabled_languages();
        $this->link_index = dbquery_tree(DB_SITE_LINKS, 'link_id', 'link_cat');

        $_GET['link_id'] = isset($_GET['link_id']) && isnum($_GET['link_id']) ? $_GET['link_id'] : 0;
        $_GET['link_cat'] = isset($_GET['link_cat']) && isnum($_GET['link_cat']) ? $_GET['link_cat'] : 0;

        $action = get('action');
        switch ($action) {
            case 'edit':
                $this->data = self::get_sitelinks($_GET['link_id']);
                $this->data['link_position_id'] = 0;
                if (empty($this->data['link_id'])) {
                    redirect(FUSION_SELF.$this->aidlink);
                }

                $this->form_action = FUSION_SELF.$this->aidlink."&amp;action=edit&amp;section=nform&amp;link_id=".$_GET['link_id']."&amp;link_cat=".$_GET['link_cat'];

                \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => $this->form_action, 'title' => $this->locale['SL_0011']]);

                break;
            case 'delete':

                $result = self::delete_sitelinks($_GET['link_id']);

                if ($result) {
                    addNotice("success", $this->locale['SL_0017']);
                    redirect(FUSION_SELF.$this->aidlink);
                }

                break;
            default:
                $this->form_action = FUSION_SELF.$this->aidlink."&amp;section=link_form";
                \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => $this->form_action, 'title' => (isset($_GET['ref']) && $_GET['ref'] == 'link_form' ? $this->locale['SL_0010'] : $this->locale['SL_0012'])]);
                break;
        }


    }

    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function admin() {
        pageAccess("SL");

        // move to construct
        $this->cancel_action = check_post('cancel');

        if ($this->cancel_action) {
            redirect(FUSION_SELF.$this->aidlink);
        }

        $master_title['title'][] = $this->getTitle();
        $master_title['id'][] = "links";
        $master_title['icon'][] = '';

        $master_title['title'][] = $this->locale['SL_0041'];
        $master_title['id'][] = "settings";
        $master_title['icon'][] = '';

        //$link_index = dbquery_tree(DB_SITE_LINKS, "link_id", "link_cat");
        //$link_data = dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat");
        //make_page_breadcrumbs($link_index, $link_data, "link_id", "link_name", "link_cat");

        opentable($this->locale['SL_0012']);
        ?>
        <h1 class='m-0'>Sitelinks</h1>
        <?php
        echo "<div class='display-flex-row' style='align-content:center; padding-bottom:25px;'></div>";
        echo opentab($master_title, (isset($_GET['section']) ? $_GET['section'] : "links"), 'link', TRUE);
        if (isset($_GET['section']) && $_GET['section'] == "settings") {
            $this->display_sitelinks_settings();
        } else {
            if (isset($_GET['ref'])) {
                switch ($_GET['ref']) {
                    case "link_form":
                        $this->display_sitelinks_form();
                        break;
                    default:
                        $this->display_sitelinks_list();
                }
            }
        }
        $this->display();
        echo closetab();
        closetable();
    }

    private function getTitle() {
        $title = $this->locale['SL_0001'];
        if (isset($_GET['ref']) && $_GET['ref'] == "link_form") {
            $title = isset($_GET['link_id']) && $this->verify_sitelinks($_GET['link_id']) ? $this->locale['SL_0011'] : $this->locale['SL_0010'];
        }
        return $title;

    }

    /**
     * Site Links Settings Administration Form
     */
    private function display_sitelinks_settings() {
        fusion_confirm_exit();
        add_to_title($this->locale['SL_0041']);

        $settings = fusion_get_settings();
        if (!isset($settings['link_bbcode'])) {
            dbquery("INSERT INTO ".DB_SETTINGS." (`settings_name`, `settings_value`) VALUES ('link_bbcode', '0')");
        }

        $settings = [
            "links_per_page" => fusion_get_settings("links_per_page"),
            "links_grouping" => fusion_get_settings("links_grouping"),
            'link_bbcode'    => fusion_get_settings('link_bbcode'),
        ];

        if (isset($_POST['save_settings'])) {

            $settings = [
                "links_per_page" => form_sanitizer($_POST['links_per_page'], 1, "links_per_page"),
                "links_grouping" => form_sanitizer($_POST['links_grouping'], 0, "links_grouping"),
                'link_bbcode'    => form_sanitizer($_POST['link_bbcode'], 0, 'link_bbcode')
            ];
            if (fusion_safe()) {
                foreach ($settings as $key => $value) {
                    dbquery("UPDATE ".DB_SETTINGS." SET settings_value = '$value' WHERE settings_name = '$key'");
                }
                addNotice("success", $this->locale['SL_0018']);
                redirect(FUSION_REQUEST);
            }
        }

        echo openform("sitelinks_settings", "post", FUSION_REQUEST, ["class" => "m-t-20 m-b-20"]);

        echo "<div class='well'>\n";
        echo $this->locale['SL_0042'];
        echo "</div>\n";

        echo form_checkbox('link_bbcode', $this->locale['SL_0063'], $settings['link_bbcode'], [
            'options' => [
                '0' => $this->locale['no'],
                1   => $this->locale['yes']
            ],
            'type'    => 'radio',
            'inline'  => TRUE,
        ]);

        echo "<div class='row'>\n<div class='col-xs-12 col-sm-3'><strong>".$this->locale['SL_0046']."</strong><br/>".$this->locale['SL_0047']."</div>";
        echo "<div class='col-xs-12 col-sm-9'>\n";
        echo form_checkbox("links_grouping", "", $settings['links_grouping'],
            [
                "options" => [
                    0 => $this->locale['SL_0048'],
                    1 => $this->locale['SL_0049']
                ],
                "type"    => "radio",
                "inline"  => TRUE,
                "width"   => "250px",
            ]
        );
        echo "</div>\n</div>\n";


        echo "<div id='lpp' class='row' ".($settings['links_grouping'] == FALSE ? "style='display:none'" : "").">\n<div class='col-xs-12 col-sm-3'><strong>".$this->locale['SL_0043']."</strong><br/>".$this->locale['SL_0044']."</div>";
        echo "<div class='col-xs-12 col-sm-9'>\n";
        echo form_text("links_per_page", $this->locale['SL_0045'], $settings['links_per_page'],
            [
                "type"     => "number",
                "inline"   => FALSE,
                "width"    => "250px",
                "required" => TRUE,
            ]
        );
        echo "</div>\n</div>\n";
        add_to_jquery("
        var lpp = $('#lpp');
        $('#links_grouping-0').bind('click', function(e){ lpp.slideUp(); });
        $('#links_grouping-1').bind('click', function(e){ lpp.slideDown(); });
        ");

        echo form_button('save_settings', $this->locale['save_changes'], $this->locale['save_changes'],
            ['class' => 'btn-primary']);
        echo closeform();
    }

    /**
     * Site Links Form
     */
    private function display_sitelinks_form() {
        fusion_confirm_exit();

        if (isset($_POST['savelink'])) {

            $this->data = [
                "link_id"         => form_sanitizer($_POST['link_id'], 0, 'link_id'),
                "link_cat"        => form_sanitizer($_POST['link_cat'], 0, 'link_cat'),
                "link_name"       => form_sanitizer($_POST['link_name'], '', 'link_name'),
                "link_url"        => form_sanitizer($_POST['link_url'], '', 'link_url'),
                "link_icon"       => form_sanitizer($_POST['link_icon'], '', 'link_icon'),
                "link_language"   => form_sanitizer($_POST['link_language'], '', 'link_language'),
                "link_visibility" => form_sanitizer($_POST['link_visibility'], '', 'link_visibility'),
                "link_position"   => form_sanitizer($_POST['link_position'], '', 'link_position'),
                'link_status'     => form_sanitizer($_POST['link_status'], 0, 'link_status'),
                "link_order"      => form_sanitizer($_POST['link_order'], '', 'link_order'),
                "link_window"     => form_sanitizer(isset($_POST['link_window']) && $_POST['link_window'] == 1 ? 1 : 0, 0, 'link_window')
            ];
            if ($this->data['link_position'] > 3) {
                $this->data['link_position'] = form_sanitizer($_POST['link_position_id'], 3, 'link_position_id');
            }

            if (empty($this->data['link_order'])) {

                $max_order_query = "SELECT MAX(link_order) 'link_order' FROM ".DB_SITE_LINKS."
                ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")."
                link_cat='".$this->data['link_cat']."'";

                $this->data['link_order'] = dbresult(dbquery($max_order_query), 0) + 1;
            }

            if (fusion_safe()) {

                if (!empty($this->data['link_id'])) {

                    dbquery_order(DB_SITE_LINKS, $this->data['link_order'], "link_order", $this->data['link_id'], "link_id", $this->data['link_cat'], "link_cat", multilang_table("SL"), "link_language", "update");

                    dbquery_insert(DB_SITE_LINKS, $this->data, 'update');

                    $child = get_child($this->link_index, $this->data['link_id']);
                    if (!empty($child)) {
                        foreach ($child as $child_id) {
                            // update new link position
                            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_position='".$this->data['link_position']."' WHERE link_id='$child_id'");
                            if ($result) {
                                continue;
                            }
                        }
                    }
                    addNotice("success", $this->locale['SL_0016']);
                } else {

                    dbquery_order(DB_SITE_LINKS, $this->data['link_order'], "link_order", $this->data['link_id'], "link_id", $this->data['link_cat'], "link_cat", multilang_table("SL"), "link_language", "save");
                    dbquery_insert(DB_SITE_LINKS, $this->data, 'save');
                    // New link will not have child
                    addNotice("success", $this->locale['SL_0015']);
                }
                redirect(clean_request("link_cat=".$this->data['link_cat'], ['ref'], FALSE));
            }
        }

        echo "<div class='m-t-20'>\n";
        echo openform('link_administration_frm', 'post', FUSION_REQUEST);
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-8 col-lg-8'>\n";
        echo form_hidden('link_id', '', $this->data['link_id']);
        echo form_text('link_name', $this->locale['SL_0020'], $this->data['link_name'], [
            'max_length' => 100,
            'required'   => TRUE,
            'error_text' => $this->locale['SL_0085'],
            'form_name'  => 'link_administration_frm',
            'type'       => 'bbcode',
            'inline'     => TRUE
        ]);
        echo form_text('link_icon', $this->locale['SL_0020a'], $this->data['link_icon'], [
            'max_length' => 100,
            'inline'     => TRUE
        ]);
        echo form_text('link_url', $this->locale['SL_0021'], $this->data['link_url'], [
            'error_text' => $this->locale['SL_0086'],
            'inline'     => TRUE
        ]);
        echo form_text('link_order', $this->locale['SL_0023'], $this->data['link_order'], [
            'inline' => TRUE,
            'width'  => '250px',
            'type'   => 'number'
        ]);

        // There will be a trick to manipulate the situation here
        if ($this->data['link_position'] > 3) {
            $this->data['link_position_id'] = $this->data['link_position'];
            $this->data['link_position'] = 4;
        }

        echo form_select('link_position', $this->locale['SL_0024'], $this->data['link_position'],
            [
                'options' => self::get_SiteLinksPosition(),
                'inline'  => TRUE,
                'stacked' => form_text('link_position_id', '', $this->data['link_position_id'],
                    [
                        'required'    => TRUE,
                        'placeholder' => 'ID',
                        'type'        => 'number',
                        'width'       => '150px'
                    ]
                )
            ]);


        add_to_jquery("
        checkLinkPosition( ".$this->data['link_position']." );
        $('#link_position').bind('change', function(e) {
            checkLinkPosition( $(this).val() );
        });
        ");


        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-4 col-lg-4'>\n";

        echo form_select('link_status', $this->locale['SL_0031'], $this->data['link_status'], [
            'options' => [0 => $this->locale['unpublish'], 1 => $this->locale['publish']],
            'width'   => '100%',
        ]);

        echo form_select('link_cat', $this->locale['SL_0029'], $this->data['link_cat'], [
            'input_id'      => 'link_categories',
            'parent_value'  => $this->locale['parent'],
            'disable_opts'  => $this->data['link_id'],
            'hide_disabled' => TRUE,
            'db'            => DB_SITE_LINKS,
            'id_col'        => 'link_id',
            'cat_col'       => 'link_cat',
            'title_col'     => 'link_name',
            'custom_query'  => "SELECT link_id, link_name, link_cat FROM ".DB_SITE_LINKS.(multilang_table("SL") ? " WHERE link_language='".LANGUAGE."'" : '')
        ]);

        echo form_select('link_language', $this->locale['global_ML100'], $this->data['link_language'], [
            'options'     => $this->language_opts,
            'placeholder' => $this->locale['choose'],
            'width'       => '100%'
        ]);

        echo form_select('link_visibility', $this->locale['SL_0022'], $this->data['link_visibility'], [
            'options'     => self::get_LinkVisibility(),
            'placeholder' => $this->locale['choose'],
            'width'       => '100%'
        ]);
        echo form_checkbox('link_window', $this->locale['SL_0028'], $this->data['link_window']);
        echo "</div>\n";
        echo "</div>\n";
        echo form_button('savelink', $this->locale['SL_0040'], $this->locale['SL_0040'],
            ['class' => 'btn-primary m-r-10', 'input_id' => 'savelink_2']);
        echo form_button("cancel", $this->locale['cancel'], "cancel", ['input_id' => 'cancel2']);
        echo closeform();
        echo "</div>\n";
    }

    private function display() {
        $this->settings = fusion_get_settings();
        // need to boil the grid
        add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery-ui/jquery-ui.min.js'></script>");
        add_to_jquery("
        $('#site-links').sortable({
            handle : '.handle',
            placeholder: 'state-highlight',
            connectWith: '.connected',
            scroll: true,
            axis: 'y',
            update: function () {
                var ul = $(this),
                order = ul.sortable('serialize'),
                i = 0;
                $.ajax({
                    url: '".ADMIN."includes/site_links_updater.php".$this->aidlink."',
                    type: 'GET',
                    dataType: 'json',
                    data : order,
                    success: function(e) {
                        console.log(e);
                        if (e.status == 200) {
                        alert('".fusion_get_locale('SL_0016', LOCALE.LOCALESET."admin/sitelinks.php")."');

                        ul.find('.num').each(function(i) {
                        $(this).text(i+1);
                        });
                        ul.find('li').removeClass('tbl2').removeClass('tbl1');
                        ul.find('li:odd').addClass('tbl2');
                        ul.find('li:even').addClass('tbl1');
                        window.setTimeout('closeDiv();',2500);
                        }
                    },
                    error: function(result) {
                        alert('".fusion_get_locale('error_preview', LOCALE.LOCALESET."admin/html_buttons.php")."'
                        + '\\n".fusion_get_locale('error_preview_text', LOCALE.LOCALESET."admin/html_buttons.php")."');
                    }
                });
            }
        });

        function checkLinkPosition(val) {
            if (val == 4) {
                $('#link_position_id').prop('disabled', false).show();
            } else {
                $('#link_position_id').prop('disabled', true).hide();
            }
        }
        ");

        function normal_links() {
            echo openform('customlinksFrm', 'post', FORM_REQUEST, ['class' => 'form-horizontal']);
            echo form_text('link_name', 'Link Name', '', ['required' => TRUE, 'inline' => TRUE]).
                form_text('link_url', 'Link URL', '', ['required' => FALSE, 'inline' => TRUE]);
            echo "<div class='text-right'>";
            echo form_button('link_add', 'Add to Navigation', '', ['class' => 'btn-primary btn-outline']);
            echo "</div>";
            echo closeform();
        }
        ?>
        <div class="list-group-item my-4">
            <?php echo openform('menufrm', 'post', FORM_REQUEST, ['inline' => TRUE]) ?>
            <?php echo form_select('menu', 'Select a menu to edit:', '', [
                    'options'     => SiteLinks::get_SiteLinksPosition(),
                    'select_alt'  => TRUE,
                    'inline'      => TRUE,
                    'class'       => 'mr-3',
                    'label_class' => 'col-lg-4'
                ])
                .form_button('select_menu', 'Select', 'select_menu', ['class' => 'btn-primary mr-3'])
                .'<div> or <a href="">Create a new menu.</a></div>'
            ?>
            <?php echo closeform() ?>
        </div>
        <div class="row">
            <div class="col-12 col-xl-2">
                <h2>Add menu items</h2>
                <?php
                echo opencollapse('menu-switch');
                echo opencollapsebody('News', 'menu-news', 'menu-switch', FALSE);
                echo closecollapsebody();
                echo opencollapsebody('Custom Links', 'menu-1', 'menu-switch', TRUE);
                echo normal_links();
                echo closecollapsebody().closecollapse();
                ?>
            </div>
            <div class="col-12 col-xl-10">
                <h2>Menu management</h2>
                <?php openside(form_text('', '', '', ['placeholder' => 'Menu name', 'required' => TRUE, 'inline' => TRUE, 'class' => 'm-t-10 align-self-center'])); ?>
                Drag each item into the order you prefer. Click the arrow on the right of the item to reveal additional configuration options.
                <div class="row">
                    <div class="sortable col-xs-12 col-sm-6 col-lg-2">
                        <div class="list">
                            <?php echo opencollapse('menu-item-1').opencollapsebody('Link text', 'menu-item-1-item', 'menu-item-1').
                                form_text('link_url', 'URL', '', ['input_id' => 'mi-1-link_url', 'inline' => TRUE]).
                                form_text('link_name', 'Link Name', '', ['input_id' => 'mi-1-link_name']).
                                form_text('link_title', 'Title Attribute', '', ['input_id' => 'mi-1-link_title']).
                                form_checkbox('link_window', 'Open link in a new tab', '', ['reverse_label' => TRUE, 'type' => 'checkbox']).
                                form_text('link_class', 'CSS Classes (optional)', '').
                                form_textarea('link_description', 'Description', '', ['ext_tip' => 'The description will be displayed in the menu if the current theme supports it.']);
                            ?>
                            <a href="" class="text-danger">Remove</a> | <a href="" class="text-primary">Cancel</a>
                            <?php
                            echo closecollapsebody().closecollapse(); ?>
                        </div>
                    </div>
                </div>
                <?php closeside();?>
            </div>
        </div>
        <?php
        echo "<script src='".INCLUDES."jscripts/admin-post.js'></script>";
        echo "<script>
         // bootstrap added list
         $(document).on('click', 'a[data-toggle=\"collapse\"]', function(e) {
            let target = $(this).attr('href');            
            $(target).collapse('toggle');
        });

        $(document).on('submit', 'form#customlinksFrm', function (e) {
            e.preventDefault();
            adminpost.request('customlinksFrm', '".cookie(COOKIE_PREFIX.'user')."', 'SL', 'add-links')
            .then(function(posts) {
               //console.log('Success!', posts);                                   
               //$('.sortable').append('<div class=\"list\">' + posts.responseText +'</div>');
               /*
               return {
                    title: post.title.toUpperCase(),
                    content: post.body,
                    date: post.date
                }
                */
            })
            .catch(function (error) {
		        console.log('Something went wrong', error);
	        });            
        });                
        </script>";
        return '';

        //$visibility = self::get_LinkVisibility();
        //$position_opts = self::get_SiteLinksPosition();
        //$allowed_actions = array_flip(["publish", "unpublish", "move", "move_confirm", "delete"]);

        if (isset($_POST['link_clear'])) {
            redirect(FUSION_SELF.$this->aidlink);
        }

        // custom table action
        if (isset($_POST['table_action']) && isset($allowed_actions[$_POST['table_action']])) {
            $input = (isset($_POST['link_id']) ? explode(",", form_sanitizer($_POST['link_id'], "", "link_id")) : 0);
            if (!empty($input)) {

                if ($_POST['table_action'] === 'move') {
                    // find all links except all the selected link_ids
                    $link_ids = implode(',', $input);
                    $available_query = "SELECT * FROM ".DB_SITE_LINKS."
                    ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id NOT IN ($link_ids) ORDER BY link_name";
                    $list_query = dbquery($available_query);
                    $res = '';
                    if (dbrows($list_query) > 0) {
                        $list[0] = $this->locale['SL_0032'];
                        while ($lData = dbarray($list_query)) {
                            if (!stristr($lData['link_name'], '-')) {
                                $list[$lData['link_id']] = $lData['link_name'];
                            }
                        }
                        $res .= openform('move_frm', 'post', FUSION_REQUEST);
                        $res .= form_select('move_to_id', $this->locale['SL_0037'], '', ['options' => $list, 'inline' => TRUE]);
                        foreach ($input as $link_id) {
                            $res .= form_hidden('link_id[]', '', $link_id);
                        }
                        $res .= form_button('table_action', $this->locale['SL_0039'], 'move_confirm', ['class' => 'btn-primary']);
                        $res .= form_button('link_clear', $this->locale['cancel'], 'cancel', ['class' => 'btn-default m-l-10']);
                        $res .= closeform();
                    } else {
                        addNotice("warning", $this->locale['SL_0038']);
                        redirect(FUSION_REQUEST);
                    }
                    $modal = openmodal('move_to_mdl', $this->locale['SL_0036'], ['static' => TRUE]);
                    $modal .= $res;
                    $modal .= closemodal();
                    add_to_footer($modal);

                } else {

                    foreach ($input as $link_id) {
                        // check input table
                        if (self::verify_sitelinks($link_id) && fusion_safe()) {
                            switch ($_POST['table_action']) {
                                case "publish":
                                    dbquery("UPDATE ".DB_SITE_LINKS." SET link_status='1' WHERE link_id='".intval($link_id)."'");
                                    break;
                                case "unpublish":
                                    dbquery("UPDATE ".DB_SITE_LINKS." SET link_status='0' WHERE link_id='".intval($link_id)."'");
                                    break;
                                case "move_confirm":
                                    // pop a model up
                                    $link_move_to = (isset($_POST['move_to_id']) ? form_sanitizer($_POST['move_to_id'], 0, 'move_to_id') : 0);
                                    dbquery("UPDATE ".DB_SITE_LINKS." SET link_cat='$link_move_to' WHERE link_id='".intval($link_id)."'");
                                    break;
                                case "delete":
                                    dbquery("DELETE FROM  ".DB_SITE_LINKS." WHERE link_id='".intval($link_id)."'");
                                    break;
                                default:
                                    redirect(FUSION_SELF.$this->aidlink);
                            }
                        }
                    }
                    addNotice("success", $this->locale['SL_0016']);
                    redirect(FUSION_SELF.$this->aidlink);
                }
            } else {
                addNotice("warning", $this->locale['SL_0087']);
                redirect(FUSION_SELF.$this->aidlink);
            }
        }

        $sql_condition = multilang_table('SL') ? 'link_language="'.LANGUAGE.'"' : '';
        $search_string = [];

        if (isset($_POST['p-submit-link_name'])) {
            $search_string['link_name'] = ["input" => form_sanitizer($_POST['link_name'], "", "link_name"), "operator" => "LIKE"];
        }
        if (!empty($_POST['link_status']) && isnum($_POST['link_status'])) {
            $search_string['link_status'] = ["input" => form_sanitizer($_POST['link_status'], 0, 'link_status'), "operator" => "="];
        }
        if (!empty($_POST['link_visibility'])) {
            $search_string['link_visibility'] = ["input" => form_sanitizer($_POST['link_visibility'], iGUEST, "link_visibility"), "operator" => "="];
        }
        if (!empty($_POST['link_position'])) {
            $search_string['link_position'] = ["input" => form_sanitizer($_POST['link_position'], 0, "link_position"), "operator" => "="];
        }
        if (isset($_POST['link_cat']) && isnum($_POST['link_cat'])) {
            $search_string['link_cat'] = ['input' => form_sanitizer($_POST['link_cat'], 0, 'link_cat'), 'operator' => '='];
        } else if (isset($_GET['link_cat']) && isnum($_GET['link_cat'])) {
            $search_string['link_cat'] = ['input' => $_GET['link_cat'], 'operator' => '='];
        }
        if (!empty($search_string)) {
            foreach ($search_string as $key => $values) {
                if ($sql_condition)
                    $sql_condition .= ' AND ';
                $sql_condition .= "`$key` ".$values['operator'].($values['operator'] == "LIKE" ? "'%" : "'").$values['input'].($values['operator'] == "LIKE" ? "%'" : "'");
            }
        }


        $limit = self::$default_display;
        if ((!empty($_POST['link_display']) && isnum($_POST['link_display'])) || (!empty($_GET['link_display']) && isnum($_GET['link_display']))) {
            $limit = (!empty($_POST['link_display']) ? $_POST['link_display'] : $_GET['link_display']);
        }

        $max_rows = dbcount("(link_id)", DB_SITE_LINKS, $sql_condition);

        $rowstart = 0;
        if (!isset($_POST['news_display'])) {
            $rowstart = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_rows ? $_GET['rowstart'] : 0);
        }

        $query = "SELECT * FROM ".DB_SITE_LINKS."
        WHERE $sql_condition
        ORDER BY link_order LIMIT $rowstart, $limit";

        $result = dbquery($query);

        $link_rows = dbrows($result);

        echo "<div class='m-t-15'>\n";
        echo openform("link_filter", "post", FUSION_REQUEST);
        echo "<div class='clearfix'>\n";
        echo "<div class='pull-right'>\n";
        echo "<a class='btn btn-success btn-sm m-r-10' href='".clean_request("ref=link_form", ["ref"], FALSE)."' >".$this->locale['SL_0010']."</a>\n";
        echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('publish', '#table_action', '#link_table');\"><i class='fa fa-check fa-fw'></i> ".$this->locale['publish']."</a>\n";
        echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('unpublish', '#table_action', '#link_table');\"><i class='fa fa-ban fa-fw'></i> ".$this->locale['unpublish']."</a>\n";
        echo "<a class='btn pointer btn-default btn-sm m-r-10' onclick=\"run_admin('move', '#table_action', '#link_table');\"><i class='fa fa-hand-grab-o fa-fw'></i> ".$this->locale['move']."</a>\n";
        echo "<a class='btn btn-danger btn-sm m-r-10' onclick=\"run_admin('delete', '#table_action', '#link_table');\"><i class='fa fa-trash-o fa-fw'></i> ".$this->locale['delete']."</a>\n";
        echo "</div>\n";

        $filter_values = [
            "link_name"       => !empty($_POST['news_text']) ? form_sanitizer($_POST['link_name'], "", "link_name") : "",
            "link_cat"        => !empty($_POST['link_cat']) ? form_sanitizer($_POST['link_cat'], 0, 'link_cat') : '',
            "link_status"     => !empty($_POST['link_status']) ? form_sanitizer($_POST['link_status'], "", "link_status") : "",
            "link_category"   => !empty($_POST['link_category']) ? form_sanitizer($_POST['link_category'], "", "link_category") : "",
            "link_visibility" => !empty($_POST['link_visibility']) ? form_sanitizer($_POST['link_visibility'], "", "link_visibility") : "",
            "link_position"   => !empty($_POST['link_position']) ? form_sanitizer($_POST['link_position'], 0, "link_position") : 0,
        ];
        $filter_empty = TRUE;
        foreach ($filter_values as $val) {
            if ($val) {
                $filter_empty = FALSE;
            }
        }
        echo "<div class='display-inline-block pull-left m-r-10'>\n";
        echo form_text("link_name", "", $filter_values['link_name'], [
            "placeholder"       => $this->locale['SL_0050'],
            "append_button"     => TRUE,
            "append_value"      => "<i class='fa fa-search'></i>",
            "append_form_value" => "search_link",
            "width"             => "200px",
            "group_size"        => "sm"
        ]);
        echo "</div>\n";

        echo "<div class='display-inline-block va-top'>\n";
        echo "<a class='btn btn-sm ".($filter_empty == FALSE ? "btn-info" : " btn-default'")."' id='toggle_options' href='#'>".$this->locale['SL_0075']."
                <span id='filter_caret' class='fa ".($filter_empty == FALSE ? "fa-caret-up" : "fa-caret-down")."'></span></a>\n";
        echo form_button("link_clear", $this->locale['clear'], "clear", ['class' => 'btn-default btn-sm']);
        echo "</div>\n";
        echo "</div>\n";

        add_to_jquery("
            $('#toggle_options').bind('click', function(e) {
                e.preventDefault();
                $('#news_filter_options').slideToggle();
                var caret_status = $('#filter_caret').hasClass('fa-caret-down');
                if (caret_status == 1) {
                    $('#filter_caret').removeClass('fa-caret-down').addClass('fa-caret-up');
                    $(this).removeClass('btn-default').addClass('btn-info');
                } else {
                    $('#filter_caret').removeClass('fa-caret-up').addClass('fa-caret-down');
                    $(this).removeClass('btn-info').addClass('btn-default');
                }
            });
            // Select change
            $('#link_status, #link_visibility, #link_cat, #link_position, #link_display').bind('change', function(e){
                $(this).closest('form').submit();
            });
            ");

        unset($filter_values['link_name']);

        echo "<div id='news_filter_options'".($filter_empty == FALSE ? "" : " style='display:none;'").">\n";
        echo "<div class='display-inline-block'>\n";
        echo form_select("link_status", "", $filter_values['link_status'],
            [
                "allowclear"  => TRUE,
                "placeholder" => "- ".$this->locale['SL_0031']." -",
                "options"     => [
                    0 => $this->locale['unpublish'],
                    1 => $this->locale['publish']
                ]
            ]);
        echo "</div>\n";

        echo "<div class='display-inline-block'>\n";
        echo form_select("link_visibility", "", $filter_values['link_visibility'], [
            "allowclear" => TRUE, "placeholder" => "- ".$this->locale['SL_0022']." -", "options" => fusion_get_groups()
        ]);
        echo "</div>\n";

        $pos_result = dbquery("SELECT link_position FROM ".DB_SITE_LINKS." GROUP BY link_position");
        $link_position = [];
        if (dbrows($pos_result) > 0) {
            while ($posData = dbarray($pos_result)) {
                $link_position[$posData['link_position']] = $this->locale['custom']." ID #".$posData['link_position'];
                if (isset($position_opts[$posData['link_position']]) && $posData['link_position'] < 4) {
                    $link_position[$posData['link_position']] = $position_opts[$posData['link_position']];
                }
            }
        }
        echo "<div class='display-inline-block'>\n";
        echo form_select("link_position", "", $filter_values['link_position'], [
            "allowclear" => TRUE, "placeholder" => "- ".$this->locale['SL_0024']." -", "options" => $link_position
        ]);
        echo "</div>\n";
        unset($link_position);

        echo "<div class='display-inline-block'>\n";
        $link_cat_opts = [0 => $this->locale['SL_0032']];
        $cat_result = dbquery("SELECT sl.link_cat, sl2.link_id, sl2.link_name
          FROM ".DB_SITE_LINKS." sl
          INNER JOIN ".DB_SITE_LINKS." sl2 ON sl.link_cat=sl2.link_id
          ".(multilang_table("SL") ? "WHERE sl.link_language='".LANGUAGE."' AND " : "WHERE ")."sl.link_cat > 0 GROUP BY sl.link_cat ORDER BY link_name ASC
        ");
        if (dbrows($cat_result) > 0) {
            while ($cdata = dbarray($cat_result)) {
                $link_cat_opts[$cdata['link_id']] = $cdata['link_name'];
            }
        }
        echo form_select("link_cat", "", $filter_values['link_cat'], [
            "allowclear" => TRUE, "placeholder" => "- ".$this->locale['SL_0029']." -", "options" => $link_cat_opts
        ]);
        echo "</div>\n";
        echo "</div>\n";
        echo closeform();
        echo "</div>\n";
        echo "<hr/>";

        /*
         * Table results
         */

        echo "<div class='m-t-20 m-b-20'>\n";
        echo openform("display_frm", "post", FUSION_REQUEST);
        echo "<div class='display-block'>\n<div class='display-inline-block m-l-10'>\n";
        echo form_select('link_display', $this->locale['show'], $limit,
            [
                'inner_width' => '100px',
                'inline'      => TRUE,
                'options'     => [
                    5   => 5,
                    10  => 10,
                    16  => 16,
                    25  => 25,
                    50  => 50,
                    100 => 100
                ],
            ]
        );
        echo "</div>\n";
        if ($max_rows > $link_rows) {
            echo "<div class='display-inline-block pull-right'>\n";
            echo makepagenav($rowstart, $limit, $max_rows, 3, FUSION_SELF.$this->aidlink.(isset($_GET['link_cat']) && isnum($_GET['link_cat']) ? "&amp;link_cat=".intval($_GET['link_cat'])."&amp;" : '')."news_display=$limit&amp;");
            echo "</div>\n";
        }
        echo "</div>\n";
        echo closeform();

        echo openform("link_table", "post", FUSION_REQUEST);
        echo form_hidden("table_action", "", "");
        echo "<div class='table-responsive'><table class='table table-striped'>\n";
        echo "<tr>\n";
        echo "<th>".form_checkbox('check_all', '', '')."</th>\n";
        add_to_jquery("
        $('#check_all').bind('change', function(e) {
            val = $(this).is(':checked') ? 1 : 0;
            console.log(val);
            setChecked('link_table', 'link_id[]', val);
        });
        ");

        echo "<th>".$this->locale['SL_0073']."</th>";
        echo "<th class='col-xs-12 col-sm-4 col-md-4 col-lg-4'>".$this->locale['SL_0050']."</th>\n";
        echo "<th>".$this->locale['SL_0035']."</th>";
        echo "<th>".$this->locale['SL_0031']."</th>";
        echo "<th>".$this->locale['SL_0070']."</th>";
        echo "<th>".$this->locale['SL_0071']."</th>";
        echo "<th>".$this->locale['SL_0072']."</th>";
        echo "<th>".$this->locale['SL_0051']."</th>";
        echo "<th>".$this->locale['SL_0052']."</th>";
        echo "</tr>\n";

        // Load form data. Then, if have data, show form.. when post, we use back this page's script.
        if (isset($_POST['link_quicksave'])) {

            $this->data = [
                "link_id"         => form_sanitizer($_POST['link_item_id'], 0, "link_item_id"),
                "link_name"       => form_sanitizer($_POST['link_name'], "", "link_name"),
                "link_icon"       => form_sanitizer($_POST['link_icon'], "", "link_icon"),
                "link_language"   => form_sanitizer($_POST['link_language'], "", "link_language"),
                "link_position"   => form_sanitizer($_POST['link_position'], "", "link_position"),
                'link_status'     => form_sanitizer($_POST['link_status'], "", "link_status"),
                "link_visibility" => form_sanitizer($_POST['link_visibility'], "", "link_visibility"),
                "link_window"     => isset($_POST['link_window']) ? TRUE : FALSE,
            ];
            if ($this->data['link_position'] > 3) {
                $this->data['link_position'] = form_sanitizer($_POST['link_position_id'], 3, 'link_position_id');
            }
            if (fusion_safe()) {
                dbquery_insert(DB_SITE_LINKS, $this->data, "update");
                $child = get_child($this->link_index, $this->data['link_id']);
                if (!empty($child)) {
                    foreach ($child as $child_id) {
                        // update new link position
                        $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_position='".$this->data['link_position']."' WHERE link_id='$child_id'");
                        if ($result) {
                            continue;
                        }
                    }
                }
                if ($result) {
                    addNotice("success", $this->locale['SL_0016']);
                    redirect(FUSION_SELF.$this->aidlink."&amp;section=links&amp;link_cat=".$_GET['link_cat']);
                }
            }
        }

        echo "<tr class='qform'>\n";
        echo "<td colspan='10'>\n";
        echo "<div class='panel panel-default panel-body m-t-20 m-b-20'>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-5 col-md-12 col-lg-6'>\n";
        echo form_hidden("link_item_id", "", '', ['input_id' => 'sl_id']);
        echo form_textarea('link_name', $this->locale['SL_0020'], '', [
            'placeholder' => $this->locale['SL_0020'], "input_id" => "sl_name", "type" => 'bbcode', 'form_name' => 'link_table'
        ]);
        echo form_text('link_icon', $this->locale['SL_0030'], $this->data['link_icon'], ['max_length' => 100, "input_id" => "sl_icon"]);
        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
        echo form_select('link_language', $this->locale['global_ML100'], $this->data['link_language'], [
            'options'  => $this->language_opts,
            'input_id' => 'sl_language',
            'width'    => '100%'
        ]);
        echo form_select('link_position', $this->locale['SL_0024'], $this->data['link_position'],
            [
                'allowclear' => TRUE,
                'options'    => $position_opts,
                'input_id'   => 'sl-link_position',
                'stacked'    => form_text('link_position_id', '', $this->data['link_position_id'],
                    [
                        'class'       => 'm-t-20',
                        'required'    => TRUE,
                        'placeholder' => 'ID',
                        'type'        => 'number',
                        'inner_width' => '150px'
                    ]
                )
            ]
        );
        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-3'>\n";
        echo form_select('link_status', $this->locale['SL_0031'], $this->data['link_status'], [
            'options'  => [0 => $this->locale['unpublish'], 1 => $this->locale['publish']],
            'input_id' => 'sl_status',
            'width'    => '100%'
        ]);
        echo form_select('link_visibility', $this->locale['SL_0022'], $this->data['link_visibility'], [
            'options'  => $visibility,
            'input_id' => 'sl_visibility',
            'width'    => '100%'
        ]);
        echo form_checkbox('link_window', $this->locale['SL_0028'], $this->data['link_window'],
            ['input_id' => 'sl_window', 'reverse_label' => TRUE]);
        echo "</div>\n";
        echo "</div>\n";
        echo "<div class='m-t-10 m-b-10'>\n";
        echo form_button('cancel', $this->locale['cancel'], 'cancel', [
            'class' => 'btn btn-default m-r-10',
            'type'  => 'button'
        ]);
        echo form_button('link_quicksave', $this->locale['save'], 'save', ['class' => 'btn btn-primary']);
        echo "</div>\n";
        echo "</div>\n";
        echo "</td>\n";
        echo "</tr>\n";

        echo "<tbody id='site-links' class='connected'>\n";

        if ($link_rows > 0) {
            $i = 0;
            while ($data = dbarray($result)) {

                $data['link_name'] = parsesmileys(parseubb($data['link_name']));
                $link_status = $data['link_status'] ? $this->locale['published'] : $this->locale['unpublished'];
                $link_position = $this->locale['custom']." ID #".$data['link_position'];
                if (isset($position_opts[$data['link_position']]) && $data['link_position'] < 4) {
                    $link_position = $position_opts[$data['link_position']];
                }

                echo "<tr id='listItem_".$data['link_id']."' data-id='".$data['link_id']."' class='list-result '>\n";
                echo "<td>".form_checkbox("link_id[]", "", '', ["value" => $data['link_id'], "class" => 'm-0'])."</td>\n";
                echo "<td><i class='pointer handle fa fa-arrows' title='".$this->locale['SL_0074']."'></i></td>\n";

                echo "<td>\n";

                echo "<a class='text-dark' href='".FUSION_SELF.$this->aidlink."&amp;section=links&amp;link_cat=".$data['link_id']."'>".$data['link_name']."</a>\n";

                echo "<div class='actionbar text-smaller' id='sl-".$data['link_id']."-actions'>
                //<a href='".FUSION_SELF.$this->aidlink."&amp;section=links&amp;ref=link_form&amp;action=edit&amp;link_id=".$data['link_id']."&amp;link_cat=".$data['link_cat']."'>".$this->locale['edit']."</a> |
                <a class='qedit pointer' data-id='".$data['link_id']."'>".$this->locale['qedit']."</a> |
                ";
                //echo (isset($this->link_index[$data['link_id']]) ? $this->locale['SL_0034'] : "<a class='delete' href='".FUSION_SELF.$this->aidlink."&amp;action=delete&amp;link_id=".$data['link_id']."' onclick=\"return confirm('".$this->locale['SL_0080']."');\">".$this->locale['delete']."</a>")." | ";

                if (strstr($data['link_url'], "http://") || strstr($data['link_url'], "https://")) {
                    echo "<a href='".$data['link_url']."'>".$this->locale['view']."</a>\n";
                } else {
                    echo "<a href='".BASEDIR.$data['link_url']."'>".$this->locale['view']."</a>\n";
                }
                echo "</div>";
                echo "</td>\n";

                echo "<td><span class='badge'>".(isset($this->link_index[$data['link_id']]) ? count($this->link_index[$data['link_id']]) : 0)."</span></td>\n";

                echo "<td>$link_status</td>\n";

                echo "<td><i class='".$data['link_icon']."'></i></td>\n";
                echo "<td>".($data['link_window'] ? $this->locale['yes'] : $this->locale['no'])."</td>\n";
                echo "<td>$link_position</td>\n";
                echo "<td>".$visibility[$data['link_visibility']]."</td>\n";
                echo "<td class='num'>".$data['link_order']."</td>\n";
                echo "</tr>\n";
                $i++;
            }
        } else {
            echo "<tr>\n";
            //echo "<td colspan='10' class='text-center'>".$this->locale['SL_0062']."</td>\n";
            echo "</tr>\n";

            //redirect(FUSION_SELF.$this->aidlink);
        }
        echo "</tbody>\n";
        echo "</table>\n</div>";
        echo closeform();

        echo "</div>\n";

        add_to_jquery("
            $('.actionbar').hide();
            $('tr').hover(
                function(e) { $('#sl-'+ $(this).data('id') +'-actions').show(); },
                function(e) { $('#sl-'+ $(this).data('id') +'-actions').hide(); }
            );
            $('.qform').hide();
            $('.qedit').bind('click', function(e) {
                $.ajax({
                    url: '".ADMIN."includes/sldata.php',
                    dataType: 'json',
                    type: 'get',
                    data: { q: $(this).data('id'), token: '".$this->aidlink."' },
                    success: function(e) {
                        checkLinkPosition(e.link_position);
                        $('#sl_id').val(e.link_id);
                        $('#sl_name').val(e.link_name);
                        $('#sl_icon').val(e.link_icon);
                        $('#sl_status').select2('val', e.link_status);
                        // switch to custom
                        $('#sl-link_position').select2('val', e.link_position);
                        if (e.link_position > 3) {
                            checkLinkPosition(e.link_position);
                            $('#link_position_id').val(e.link_position_id);
                        }
                        $('#sl-link_position').bind('change', function(e) {
                            checkLinkPosition( $(this).val() );
                        });
                        $('#sl_language').select2('val', e.link_language);
                        $('#sl_visibility').select2('val', e.link_visibility);
                        var length = e.link_window;
                        if (e.link_window > 0) { $('#sl_window').attr('checked', true);	} else { $('#sl_window').attr('checked', false); }
                    },
                    error : function(e) {
                        console.log(e);
                    }
                });
                $('.qform').show();
                $('.list-result').hide();
            });
            $('#cancel').bind('click', function(e) {
                $('.qform').hide();
                $('.list-result').show();
            });
        ");

    }

}
