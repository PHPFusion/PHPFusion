<?php
namespace PHPFusion\Administration;

use PHPFusion\SiteLinks;

class SitelinksAdmin {

    private static $instance = NULL;
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


        //$link_index = dbquery_tree(DB_SITE_LINKS, "link_id", "link_cat");
        //$link_data = dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat");
        //make_page_breadcrumbs($link_index, $link_data, "link_id", "link_name", "link_cat");

        opentable($this->locale['SL_0012']);
        ?>
        <h1 class='m-0'>Sitelinks</h1>
        <?php
        echo "<div class='display-flex-row' style='align-content:center; padding-bottom:25px;'></div>";
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
    }

    private function display() {

        /** Custom links menu */

        /**
         * @param $link_position
         *
         * @return string
         */
        function custom_links($link_position) {
            $html = openform('customlinksFrm', 'post', FORM_REQUEST, ['class' => 'form-horizontal']);
            $html .= form_text('link_name', 'Link Name', '', ['required' => TRUE, 'inline' => FALSE]).
                form_text('link_url', 'Link URL', '', ['required' => FALSE, 'inline' => FALSE]).
                form_hidden('link_position', '', $link_position, ['required' => FALSE, 'inline' => FALSE]);
            $html .= "<div class='text-right'>";
            $html .= form_button('link_add', 'Add to Navigation', "", ['class' => 'btn-primary']);
            $html .= "</div>";
            $html .= closeform();
            return $html;
        }

        // custom links form
        function link_form($data) {
            $link_id = $data["link_id"];
            echo opencollapse($link_id);
            echo opencollapsebody($data["link_name"], $link_id."m", $link_id);
            echo openform("_form", "post", FORM_REQUEST, array("form_id" => "_form_".$link_id));
            switch ($data["link_type"]) {
                case "link":
                default:
                    echo form_hidden("_type", "", "links", array("input_id" => "_type_".$link_id));
                    echo form_text("_url", "URL", $data["link_url"], array("input_id" => "_url_".$link_id, "required" => TRUE));
                    echo form_text("_name", "Name", $data["link_name"], array("input_id" => "_name_".$link_id, "required" => TRUE));
                    echo form_text("_title", "Title Attribute", $data["link_title"], array("input_id" => "_title_".$link_id, "required" => TRUE));
                    echo form_textarea("_description", "Description", $data["link_description"], array("input_id" => "_description_".$link_id, "ext_tip" => "The description will be displayed in the menu if the current theme supports it."));
            }
            echo form_hidden("_position", "", $data["link_position"], array("input_id" => "_position_".$link_id));
            echo form_hidden("_id", "", $link_id, array("input_id" => "_lid_".$link_id));
            echo form_text("_icon", "Icon Class", $data["link_icon"], array("input_id" => "_icon_".$link_id));
            echo form_checkbox("_window", "Open link in a new tab", $data["link_window"], array("input_id" => "_window_".$link_id, "type" => "checkbox", "reverse_label" => TRUE));
            echo form_select("_visibility", "Visibility", $data["link_visibility"], array("input_id" => "_visibility_".$link_id, "options" => fusion_get_groups(), "select_alt" => TRUE));
            echo form_checkbox("_status", "Status", $data["link_status"], array("input_id" => "_status_".$link_id, "type" => "radio", "options" => get_status_opts()));
            echo '<a href="#" class="remove_link text-danger" data-id="'.$link_id.'_menu">Remove</a>';
            echo form_button("save_link", "Save Link", "save_link", array("input_id" => "_save_".$link_id, "class" => "btn-primary"));
            echo closeform();
            echo closecollapsebody();
            echo closecollapse();
        }

        function menu_forms($menu_id) {
            $html = opencollapse('menu-switch');
            $html .= opencollapsebody('News', 'menu-news', 'menu-switch', FALSE);
            $html .= closecollapsebody();
            $html .= opencollapsebody('Custom Links', 'menu-1', 'menu-switch', TRUE);
            $html .= custom_links($menu_id);
            $html .= closecollapsebody().closecollapse();
            return $html;
        }

        function menu_heading() {
            return form_text('menu_name', 'Menu Name', '', ['placeholder' => 'Menu name', 'required' => TRUE, 'inline' => TRUE, 'class' => 'm-t-10 align-self-center col-12 col-xl-6 pl-0']);
        }

        /**
         * @param $link_tree
         *
         * @return false|string
         */
        function menu_list($link_tree) {
            add_to_footer("<script src='".INCLUDES."jquery/jquery-ui/jquery-ui.min.js'></script>");
            add_to_footer("<script src='".INCLUDES."jquery/jquery-ui/jquery.mjs.nestedSortable.js'></script>");
            add_to_footer("<script src='".INCLUDES."jquery/sitelinks-sortable.js'></script>");
            function recurse_list($result, $index = 0) {
                /** check if have results */
                if (isset($result[$index])) {
                    if (!$index) {
                        /** only run once in the root level */
                        echo '<ol class="sortable ui-sortable" style="list-style:none;margin:0;padding:0;">';
                    }
                    /** Loop through current level
                     *
                     * @var  $link_id -  link_id
                     * @var  $link    -  link data
                     */
                    foreach ($result[$index] as $link_id => $link) {
                        echo '<li id="menuItem_'.$link['link_id'].'" style="cursor:pointer;">';
                        echo '<div style="max-width:400px;">';
                        link_form($link);
                        echo '</div>';
                        if (isset($result[$link_id])) {
                            echo '<ol style="list-style:none;">';
                            recurse_list($result, $link_id);
                            echo '</ol>';
                        }
                        echo '</li>';
                    }
                    if (!$index) {
                        echo '</ol>';
                        // Update the link
                        $update_success = json_encode(array(
                            "toast"       => TRUE,
                            "title"       => "Site Links",
                            "description" => "The links are updated successfully.",
                            "icon"        => "fas fa-link",
                        ));

                        $cookie = cookie(COOKIE_PREFIX.'user');
                        add_to_jquery(/** @lang JavaScript */ "
                        /** Update site links */    
                        $(document).on('click', 'button[name=\"save_link\"]', function(e){
                            e.preventDefault();
                            let form = $(this).closest('form');
                            let form_id = form.prop('id');                            
                            // admin post...
                            let links = new FusionPost(form_id, '$cookie', 'SL', 'update-links');
                            links.submit()
                                .then(function(response){
                                    return links.return();
                                })
                                .then(function(xhr){
                                    let response = JSON.parse(xhr['responseText']);
                                    if (response['status'] === true && response['target']) {
                                        $('#'+response['target']).removeClass('show');
                                        // trigger toast
                                        links.showNotice('success', $update_success);
                                        // now trigger the toast
                                        links.resetButton();
                                        
                                    }                                     
                                })
                                .catch(function(error){
                                    console.log('Something went wrong', error);
                                });
                        });
                        /** Title changes on keyup */
                        $(document).on('keyup input paste', 'input[name=\"_name\"]', function(e) {
                            let panelTitle = $(this).closest('.panel-default').find('.collapse-title');
                            panelTitle.text($(this).val());
                        });
                        ");
                    }
                }
            }

            ob_start();
            recurse_list($link_tree);
            return ob_get_clean();
        }

        /**
         * @param $link_tree
         * @param $settings
         *
         * @return string
         */
        function menu_footer($link_tree, $settings) {
            // Sort link form
            $locale = fusion_get_locale();

            if (!empty($link_tree)) {

                $html = openform("sortlinks_form", "post", FORM_REQUEST, array("inline" => FALSE, "class" => "spacer-xs"));
                $html .= form_hidden("link_menu", "", "");
                $html .= form_hidden("link_sort", "", "");
                $html .= form_checkbox('link_bbcode', $locale['SL_0063'], $settings['links_bbcode'], [
                    'options' => get_status_opts(array()),
                    'type'    => 'radio',
                    'inline'  => TRUE,
                    'ext_tip' => $locale['SL_0047']
                ]);
                $html .= form_checkbox("links_grouping", $locale['SL_0046'], $settings['links_grouping'],
                    [
                        "options" => get_status_opts(array(), array(
                            0 => $locale['SL_0048'],
                            1 => $locale['SL_0049']
                        )),
                        "type"    => "radio",
                        "inline"  => TRUE,
                        "width"   => "250px",
                    ]
                );
                $html .= '<div id="lpp" class="row" '.($settings["links_grouping"] === FALSE ? 'style="display:none"' : '').'><div class="col-12">';
                $html .= form_text("links_per_page", $locale['SL_0043'], $settings['links_per_page'],
                    [
                        "type"        => "number",
                        "inline"      => TRUE,
                        "inner_width" => "200ppx",
                        //"width"    => "250px",
                        "required"    => TRUE,
                        "placeholder" => $locale['SL_0045'],
                        "ext_tip"     => $locale['SL_0044']
                    ]
                );
                $html .= '</div></div>';
                $html .= form_button("save_menu", "Save Menu", "save_menu", ["class" => "btn-primary ml-a"]);
                $html .= closeform();

                // Save menu
                $cookie = cookie(COOKIE_PREFIX.'user');
                add_to_jquery(/** @lang JavaScript */ "
                 let lpp = $('#lpp');
                    $('#links_grouping-0').bind('click', function(e){ lpp.slideUp(); });
                    $('#links_grouping-1').bind('click', function(e){ lpp.slideDown(); });
                
                $(document).on('click', 'button[name=\"save_menu\"]', function(e){
                    e.preventDefault();
                    let form = $(this).closest('form');
                    let form_id = form.prop('id');
                    // admin post...
                    let menu_action = new FusionPost(form_id, '$cookie', 'SL', 'update-menu');
                    menu_action.submit()
                        .then(function(response){
                            return menu_action.return();
                        })
                        .then(function(xhr){
                            let response = xhr['responseText'];
                            console.log(response);
                            // do a popper.js confirmation
                        })
                        .catch(function(error){
                            console.log('Something went wrong', error);
                        });
                });
                ");
                return $html;
            }
            return '';
        }

        $menu_id = get('link_position', FILTER_VALIDATE_INT);
        if (!$menu_id) {
            $menu_id = 1;
        }
        $link_tree = dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat", "WHERE link_position=$menu_id ORDER BY link_order ASC");

        $info = array(
            "menu_form"    => openform('menufrm', 'post', FORM_REQUEST, array("inline" => TRUE))
                .form_select('menu', 'Select a menu to edit:', '', [
                    'options'     => SiteLinks::get_SiteLinksPosition(),
                    'select_alt'  => TRUE,
                    'inline'      => TRUE,
                    'class'       => 'mr-3',
                    'label_class' => 'col-lg-4',
                ])
                .form_button('select_menu', 'Select', 'select_menu', ['class' => 'btn-primary mr-3']).
                ' <a href="'.clean_request("action=new", array("action"), FALSE).'">Create new menu</a>'
                .closeform(),
            "menu_forms"   => menu_forms($menu_id),
            "menu_heading" => menu_heading(),
            "menu_list"    => menu_list($link_tree),
            "menu_footer"  => menu_footer($link_tree, SiteLinks::getSettings($menu_id)),
        );

        echo fusion_render(ADMIN_TEMPLATES, "admin-sitelinks.twig", $info, TRUE);

        $add_success = json_encode(array(
            "toast"       => TRUE,
            "title"       => "Site Links",
            "description" => "The links are added successfully.",
            "icon"        => "fas fa-link",
        ));
        echo "<script src='".INCLUDES."jscripts/admin-post.js'></script>";
        echo "<script>
            // Add links list
            $(document).on('submit', 'form#customlinksFrm', function (e) {
                // prevent php submit
                e.preventDefault();
                /** Constructor for JS post */
                let submitCustomLinks = new FusionPost('customlinksFrm', '".cookie(COOKIE_PREFIX.'user')."', 'SL', 'add-links');
                /** Do a submit for sanitization */
                submitCustomLinks.submit()
            /** when submit is possible, do the php hook-init Ajax request */
            .then(function (response){
                //console.log(response);
                return submitCustomLinks.return ();
            })
            /** We will get our hook response output */
            .then(function (xhr){
                //console.log(xhr);
                let item = xhr['responseText'];
                $('ol.sortable').append(item);
                // use the helper function for cleaning up the form
                submitCustomLinks.bs4Success();
                // do a popper
                submitCustomLinks.showNotice('success', $add_success);
            })
            /** When sanitization fails */
            .catch(function (error){
                    // you can do a popper here or something depending on what you need.
                    console.log('Something went wrong', error);
                });
        });
        </script> ";
    }

}