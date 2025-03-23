<?php

namespace PHPFusion\Administration;

/**
 * Class Sitelinks
 *
 * @package PHPFusion\Administration
 */
class SiteLinks extends \PHPFusion\SiteLinks
{

    private static $siteLinksAdmin_instance = NULL;

    private $data = [
        'link_id'          => 0,
        'link_name'        => '',
        'link_description' => '',
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

    private $menu_data = [
        'menu_id' => 0,
        'menu_name' => '',
        'menu_branding' => '',
        'menu_header' => '',
        'menu_image' => '',
        'menu_visibility' => 0,
        'menu_language' => LANGUAGE,
        'menu_status' => 1,
        'menu_grouping' => 5
    ];

    private $language_opts;

    private $link_index;

    private $form_action;
    
    private $aidlink;
    
    private $locale;
    
    private $id;
    
    private $link_cat;
    
    private $title;

    private $refs;

    private $section;

    private $form_uri;

    private $action;

    private $link_actions;

    private $menu;

    /**
     * Sitelinks constructor.
     */
    private function __construct()
    {

        fusion_load_script(INCLUDES . "jscripts/admin.js");

        $this->aidlink = fusion_get_aidlink();

        $this->locale = fusion_get_locale("", LOCALE . LOCALESET . "admin/sitelinks.php") + fusion_get_locale("", [LOCALE . LOCALESET . "admin/html_buttons.php"]);

        $this->language_opts = fusion_get_enabled_languages();

        $this->link_index = dbquery_tree(DB_SITE_LINKS, 'link_id', 'link_cat');

        $this->id = (int)get("id", FILTER_VALIDATE_INT);

        $this->link_cat = (int)get("cat", FILTER_VALIDATE_INT);

        $this->title = $this->locale['SL_0012'];

        $this->refs = get("refs"); // subsection control

        $this->section = get("section") ?? 'links'; // section control

        $this->action = get("action");

        $this->menu = get('menu', FILTER_VALIDATE_INT) ?? 1;

        $this->form_action = FUSION_SELF . $this->aidlink . "&amp;section=form";
    }

    /**
     * @return Sitelinks|null
     */
    public static function Admin()
    {
        if (empty(self::$siteLinksAdmin_instance)) {
            self::$siteLinksAdmin_instance = new Sitelinks();
        }
        return self::$siteLinksAdmin_instance;
    }

    private function setListingActions()
    {
        // buttons
        $this->link_actions = '<div class="display-flex gap-10">';
        $this->link_actions .= '<a href="' . FUSION_SELF . $this->aidlink . '&section=links&refs=form&nrefs=' . $this->refs . '&cat=' . $this->link_cat . '" class="btn btn-primary">' . get_image('add') . $this->locale['SL_0010'] . '</a>';
        $this->link_actions .= '<a href="' . FUSION_SELF . $this->aidlink . '&section=links&refs=nform" class="btn btn-primary">' . get_image('add') . $this->locale['SL_008'] . '</a>';

        if ($this->refs == "form" || $this->refs == 'nform') {

            if ($this->section == 'menu') {

                $this->link_actions .= "<a href='" . FUSION_SELF . $this->aidlink . "&refs=" . (int)get("nrefs", FILTER_VALIDATE_INT) . "&section=menu' class='btn btn-default m-l-10'>" . $this->locale["cancel"] . "</a>";
            } else {

                $this->link_actions .= "<a href='" . FUSION_SELF . $this->aidlink . "&refs=" . (int)get("nrefs", FILTER_VALIDATE_INT) . "&section=links&nrefs=$this->refs&cat=" . $this->link_cat . "' class='btn btn-default m-l-10'>" . $this->locale["cancel"] . "</a>";
            }
        } else {

            if ($this->section == 'menu') {

                $this->link_actions .= '<div class="dropdown"><a class="btn btn-default dropdown-toggle" data-toggle="dropdown">' . get_image('ellipsis') . '</a><ul class="dropdown dropdown-menu dropdown-menu-right">';
                $this->link_actions .= '<li><a id="menu_publish">' . get_image('publish') . $this->locale['SL_0078'] . '</a></li>';
                $this->link_actions .= '<li><a id="menu_unpublish">' . get_image('unpublish') . $this->locale['SL_0079'] . '</a></li>';
                $this->link_actions .= '<li><a id="menu_del" class="text-danger">' . get_image('delete') . $this->locale['SL_0082'] . '</a></li>';
            } else {
                // move as link to dropdown.
                $this->link_actions .= '<div class="dropdown"><a class="btn btn-default dropdown-toggle" data-toggle="dropdown">' . get_image('ellipsis') . '</a><ul class="dropdown dropdown-menu dropdown-menu-right">';
                // $this->link_actions .= '<li class="dropdown-header">' . $this->locale['SL_0001'] . '</li>';
                $this->link_actions .= '<li><a id="link_move">' . get_image('move') . $this->locale['SL_0074'] . '</a></li>';
                $this->link_actions .= '<li><a id="link_publish">' . get_image('publish') . $this->locale['SL_0076'] . '</a></li>';
                $this->link_actions .= '<li><a id="link_unpublish">' . get_image('unpublish') . $this->locale['SL_0077'] . '</a></li>';
                $this->link_actions .= '<li><a id="link_del" class="text-danger">' . get_image('delete') . $this->locale['SL_0081'] . '</a></li>';
            }

            // use jquery to bind the actions into a hidden form field and submit.        
            $this->link_actions .= '</ul></div>';
        }

        $this->link_actions .= '</div>';
    }
    /**
     * @throws Exception
     */
    public function adminForm()
    {
        pageaccess("SL");

        // Add sitelinks breadcrumb
        $this->breadcrumbs();

        $this->setListingActions();

        $master_title['title'][] = $this->locale["SL_0012"];
        $master_title['id'][] = "links";
        $master_title['icon'][] = '';

        $master_title['title'][] = $this->locale['SL_0013'];
        $master_title['id'][] = 'menu';
        $master_title['icon'][] = '';

        $master_title['title'][] = $this->locale['SL_0041'];
        $master_title['id'][] = "settings";
        $master_title['icon'][] = '';

        opentable($this->locale["SL_0001"]);

        echo opentab($master_title, $this->section, 'link', TRUE, "nav-tabs", "section", ['refs', 'action', 'id', 'cat']);

        switch ($this->section) {
            case "settings":

                $this->settingsFrm();

                break;

            default:

                $this->doLinkURIActions();

                if ($this->refs == 'nform') {

                    $this->sitelinksMenuForm();
                } elseif ($this->refs == "form") {

                    $this->sitelinksForm();
                } else {
                    
                    $this->doMenuAction();
                    
                    if ($this->section == 'menu') {

                        $this->menuListing();
                    } else {

                        $this->listing();
                    }
                }
        }
        echo closetab() . closetable();
    }

    /**
     * Settings Form
     * @throws Exception
     */
    private function settingsFrm()
    {

        add_to_title($this->locale['SL_0041']);

        $settings = fusion_get_settings();

        if (post("save_settings")) {

            $settings = [                
                'link_bbcode'    => (post("link_bbcode", FILTER_VALIDATE_INT) ? "1" : "0"),                
            ];

            if (fusion_safe()) {
                foreach ($settings as $key => $value) {
                    $sql = "UPDATE " . DB_SETTINGS . " SET settings_value = '$value' WHERE settings_name = '$key'";
                    dbquery($sql);
                }
                addnotice("success", $this->locale['SL_0018']);
                redirect(FUSION_REQUEST);
            }
        }

        echo openform("slsettingsfrm", "POST");
        echo form_checkbox('link_bbcode', $this->locale["SL_0063"].'<div class="small text-normal">'.$this->locale['SL_0064'].'</div>', $settings['link_bbcode'], [
            'inline' => false,
            'type'    => "toggle",
            'toggle' => TRUE,                                  
        ]);
        echo '<hr>';
        echo form_button('save_settings', $this->locale['save_changes'], $this->locale['save_changes'], ['class' => 'btn-primary']);
        echo closeform();
    }

    private function doLinkURIActions()
    {
        if ($this->section == 'menu') {
            // Link actions
            switch ($this->action) {
                case "edit":
                    if ($this->id) {
                        $this->title = $this->verifyMenu($this->id) ? $this->locale['SL_009'] : $this->locale['SL_008'];
                    }
                    $this->menu_data = self::getMenu($this->id);
                    if (empty($this->menu_data['menu_id'])) {
                        redirect(FUSION_SELF . $this->aidlink . '&section=menu');
                    }
                    $this->form_uri = FUSION_SELF . $this->aidlink . "&section=menu&action=edit&refs=nform&id=" . $this->id;
                    break;
                case "del":
                    if (!dbcount("(link_id)", DB_SITE_LINKS, 'link_position=:menuID', array(':menuID' => intval($this->id)))) {
                        dbquery("DELETE FROM  " . \DB_SITE_LINK_MENUS . " WHERE menu_id=:menuID", [":menuID" => intval($this->id)]);
                        addnotice("success", $this->locale['SL_0069']);
                        redirect(FUSION_SELF . $this->aidlink . "&section=menu&refs=$this->id");
                    } else {
                        addnotice("danger", $this->locale['SL_0089']);
                    }

                    break;
                default:
                    $this->form_uri = FUSION_SELF . $this->aidlink . "&section=menu&refs=nform";
            }
        } else {

            // Link actions
            switch ($this->action) {
                case "edit":
                    if ($this->id) {
                        $this->title = $this->verifySiteLink($this->id) ? $this->locale['SL_0011'] : $this->locale['SL_0010'];
                    }
                    $this->data = self::getSiteLinks($this->id);
                    if (empty($this->data['link_id'])) {
                        redirect(FUSION_SELF . $this->aidlink);
                    }
                    $this->form_uri = FUSION_SELF . $this->aidlink . "&amp;action=edit&refs=form&id=" . $this->id . "&link_cat=" . $this->link_cat;
                    $this->data['link_position_id'] = 0;
                    break;
                case "del":
                    $link_order = dbresult(dbquery("SELECT link_order FROM " . DB_SITE_LINKS . " " . (multilang_table("SL") ? "WHERE link_language='" . LANGUAGE . "' AND" : "WHERE") . " link_id=:id", [":id" => $this->id]), 0);
                    dbquery("UPDATE " . DB_SITE_LINKS . " SET link_order=link_order-1 " . (multilang_table("SL") ? "WHERE link_language='" . LANGUAGE . "' AND" : "WHERE") . " link_order > :order", [":order" => (int)$link_order]);
                    dbquery("DELETE FROM  " . DB_SITE_LINKS . " WHERE link_id=:id", [":id" => $this->id]);
                    addnotice("success", $this->locale['SL_0017']);
                    redirect(FUSION_SELF . $this->aidlink . "&section=links&menu=" . $this->id . "&cat=" . $this->link_cat);
                    break;
                default:
                    $this->form_uri = FUSION_SELF . $this->aidlink . "&refs=form";
            }
        }
    }

    /**
     *  Site Links Form
     *
     * @throws Exception
     */
    private function sitelinksForm()
    {
        add_to_jquery(
            /** @lang JavaScript */
            "slAdmin.slForm();"
        );

        if ($this->link_cat && isnum($this->link_cat)) {
            $this->data["link_cat"] = $this->link_cat;
        }
        if ($menu = get("nrefs", FILTER_VALIDATE_INT)) {
            $this->data["link_position"] = $menu;
        }

        if (check_post("save_link")) {

            $this->data = [
                "link_id"          => sanitizer('link_id', 0, 'link_id'),
                "link_cat"         => sanitizer('link_cat', 0, 'link_cat'),
                "link_name"        => sanitizer('link_name', '', 'link_name'),
                "link_url"         => sanitizer('link_url', '', 'link_url'),
                "link_icon"        => sanitizer('link_icon', '', 'link_icon'),
                'link_description' => sanitizer('link_description', '', 'link_description'),
                "link_language"    => sanitizer('link_language', LANGUAGE, 'link_language'),
                "link_visibility"  => sanitizer('link_visibility', 0, 'link_visibility'),
                "link_position"    => sanitizer('link_position', 0, 'link_position'),
                'link_status'      => sanitizer('link_status', 0, 'link_status'),
                "link_order"       => sanitizer('link_order', 0, 'link_order'),
                "link_window"      => (check_post('link_window') ? '1' : '0'),
                "link_position_id" => 0,
            ];

            if (empty($this->data['link_order'])) {
                $max_order_query = "SELECT MAX(link_order) 'link_order' FROM " . DB_SITE_LINKS . " " . (multilang_table("SL") ? "WHERE link_language='" . LANGUAGE . "' AND" : "WHERE") . " link_cat='" . $this->data['link_cat'] . "'";
                $this->data['link_order'] = dbresult(dbquery($max_order_query), 0) + 1;
            }

            if (fusion_safe()) {
                if (!empty($this->data['link_id'])) {

                    dbquery_order(DB_SITE_LINKS, $this->data['link_order'], "link_order", $this->data['link_id'], "link_id", $this->data['link_cat'], "link_cat", multilang_table("SL"), "link_language");

                    dbquery_insert(DB_SITE_LINKS, $this->data, 'update');

                    $child = get_child($this->link_index, $this->data['link_id']);
                    if (!empty($child)) {
                        foreach ($child as $child_id) {
                            dbquery("UPDATE " . DB_SITE_LINKS . " SET link_position='" . $this->data['link_position'] . "' WHERE link_id='$child_id'");
                        }
                    }
                    addnotice("success", $this->locale['SL_0016']);
                } else {

                    dbquery_order(DB_SITE_LINKS, $this->data['link_order'], "link_order", $this->data['link_id'], "link_id", $this->data['link_cat'], "link_cat", multilang_table("SL"), "link_language", "save");

                    dbquery_insert(DB_SITE_LINKS, $this->data, 'save');
                    // New link will not have child
                    addnotice("success", $this->locale['SL_0015']);
                }

                redirect(FUSION_SELF . $this->aidlink . "&section=links&refs=" . (int)$this->data["link_position"] . "&cat=" . (int)$this->data["link_cat"]);
            }
        }

        echo "<div class='clearfix'>";
        echo "<div class='pull-right'>" . $this->link_actions . "</div>";
        echo "<h4>$this->title</h4>";
        echo "<hr/>";
        echo "</div>";

        echo openform('link_administration_frm', 'POST', $this->form_uri);
        echo "<div class='row'>";
        echo "<div class='col-xs-12 col-sm-8 col-lg-9'>";

        echo form_hidden('link_id', '', $this->data['link_id']);
        echo form_text('link_name', $this->locale['SL_0020'], $this->data['link_name'], [
            'max_length' => 100,
            'required'   => TRUE,
            'error_text' => $this->locale['SL_0085'],
        ]);
        echo form_text('link_description', $this->locale['SL_0046'], $this->data['link_description'], ['inline'=>FALSE]);

        echo form_text('link_icon', $this->locale['SL_0020a'], $this->data['link_icon'], [
            'max_length' => 100,
        ]);
        echo form_text('link_url', $this->locale['SL_0021'], $this->data['link_url'], [
            'error_text' => $this->locale['SL_0086'],
        ]);
        echo form_text('link_order', $this->locale['SL_0023'], $this->data['link_order'], [
            'width'  => '250px',
            'type'   => 'number',
            'ext_tip' => $this->locale['SL_0026'],
        ]);

        echo form_checkbox('link_window', $this->locale['SL_0028'], $this->data['link_window'], ["default_checked" => FALSE]);

        echo "</div><div class='col-xs-12 col-sm-4 col-lg-3'>";

        openside("");
        echo form_select('link_status', $this->locale['SL_0031'], $this->data['link_status'], [
            'options' => [1 => $this->locale['publish'], 0 => $this->locale['unpublish']],
        ]);
        echo form_select(
            'link_position',
            $this->locale['SL_0024'],
            $this->data["link_position"],
            [
                'options'     => self::getSiteLinksPosition(),
            ]
        );

        // Integrate this to slFormJS function
        add_to_jquery("
        $(document).on('change', '#link_position', function(e) {
            $.get('" . ADMIN . "includes/" . fusion_get_aidlink() . "&api=sitelinks-cat', { val: $(this).val() })
                .done(function (data) {
                    if (data['response'] == 200) {
                        let linkCat = $('#link_categories');                

                        if (!data || !data.data) {
                            console.error('Invalid response format');
                            return;
                        }

                        linkCat.html('').trigger('change');
                        // Convert the response data into a format compatible with Select2
                        $.each(data['data'], function (key, value) {
                                linkCat.append(new Option(value, key, false, false));
                        });

                        // Reinitialize Select2 with new data                  
                        linkCat.trigger('change');                          
                    }
                })
                .fail(function () {
                    console.error('Error fetching data.');
                });
        });
        ");

        echo form_select("link_cat", $this->locale['SL_0029'], $this->data['link_cat'], [
            'input_id'        => "link_categories",
            "parent_value"    => $this->locale['parent'],
            'width'           => '100%',
            'query'           => (multilang_table("SL") ? "WHERE link_language='" . LANGUAGE . "'" : ''),
            'disable_opts'    => $this->data['link_id'],
            'hide_disabled'   => 1,
            "add_parent_opts" => TRUE,
            "db"              => DB_SITE_LINKS,
            "title_col"       => "link_name",
            "id_col"          => "link_id",
            "cat_col"         => "link_cat",
        ]);

        echo form_select('link_language', $this->locale['global_ML100'], $this->data['link_language'], [
            'options'     => $this->language_opts,
            'placeholder' => $this->locale['choose'],
            'width'       => '100%',
            "inline"      => FALSE,
        ]);

        echo form_select('link_visibility', $this->locale['SL_0022'], $this->data['link_visibility'], [
            'options'     => self::getLinkVisibility(),
            'placeholder' => $this->locale['choose'],
            'width'       => '100%',
        ]);

        closeside();
        echo "</div></div>";
        echo form_button('save_link', $this->locale['SL_0040'], $this->locale['SL_0040'], ['class' => 'btn-success m-r-10', 'input_id' => 'savelink_2']);
        echo closeform();
    }

    private function sitelinksMenuForm()
    {
        add_to_jquery("slAdmin.smForm();");

        if (check_post('save_menu')) {

            $data = array(
                'menu_id' => $this->id,
                'menu_name' => sanitizer('menu_name', '', 'menu_name'),
                'menu_branding' => sanitizer('menu_branding', '', 'menu_branding'),
                'menu_header' => sanitizer('menu_header', '', 'menu_header'),
                'menu_image' => sanitizer('menu_image', '', 'menu_image'),
                'menu_grouping' => sanitizer('menu_grouping', '', 'menu_grouping'),
                'menu_status' => sanitizer('menu_status', '', 'menu_status'),
                'menu_visibility' => sanitizer('menu_visibility', '', 'menu_visibility'),
                'menu_language' => sanitizer('menu_language', '', 'menu_language'),
            );

            if (fusion_safe()) {

                dbquery_insert(DB_SITE_LINK_MENUS, $data, ($data['menu_id'] ? 'update' : 'save'));

                addnotice('success', $data['menu_id'] ?  $this->locale['SL_0068'] : $this->locale['SL_0067']);

                redirect(FUSION_SELF . $this->aidlink . "&section=menu");
            }
        }

        echo "<div class='clearfix'>";
        echo "<div class='pull-right'>" . $this->link_actions . "</div>";
        echo "<h4>$this->title</h4>";
        echo "<hr/>";
        echo "</div>";

        echo openform('menuFrm', 'POST');
        echo "<div class='row'>";
        echo "<div class='col-xs-12 col-sm-8 col-lg-9'>";

        echo form_text('menu_name', $this->locale['SL_0050'], $this->menu_data['menu_name'], [
            'required' => TRUE,
            'max_length' => 100,
        ]);
        openside('');
        echo form_select('menu_branding', $this->locale['SL_0057'], $this->menu_data['menu_branding'], [
            'options' => array(
                0 => $this->locale['SL_0060'],
                1 => $this->locale['SL_0058'],
                2 => $this->locale['SL_0059']
            )
        ]);
        echo '<div ' . ($this->menu_data['menu_branding'] == 1 ? '' : 'style="display:none;"') . ' id="menu_header_wrapper">';
        echo form_text('menu_header', $this->locale['SL_0065'], $this->menu_data['menu_header']);
        echo '</div>';
        echo '<div ' . ($this->menu_data['menu_branding'] == 2 ? '' : 'style="display:none;"') . ' id="menu_image_wrapper">';
        echo form_text('menu_image', $this->locale['SL_0066'], $this->menu_data['menu_image'], ['prepend' => TRUE, 'prepend_value' => '../']);
        echo '</div>';
        closeside();

        echo form_text('menu_grouping', $this->locale['SL_0055'], $this->menu_data['menu_grouping'], [
            'type' => 'number',
            'width' => '200px',
            'inner_width' => '200px',
            'ext_tip' => $this->locale['SL_0047']
        ]);

        echo "</div><div class='col-xs-12 col-sm-4 col-lg-3'>";
        openside("");
        echo form_select('menu_status', $this->locale['SL_0027'], $this->menu_data['menu_status'], [
            'options' => [
                0 => $this->locale['unpublish'],
                1 => $this->locale['publish'],
            ]
        ]);

        echo form_select('menu_visibility', $this->locale['SL_0051'], $this->menu_data['menu_visibility'], [
            'type' => 'number',
            'options' => self::getLinkVisibility()
        ]);

        echo form_select('menu_language', $this->locale['SL_0054'], $this->menu_data['menu_language'], [
            'inline' => FALSE,
            'options' => \fusion_get_enabled_languages()
        ]);
        closeside();
        echo "</div></div>";

        echo form_button('save_menu', $this->locale['save_changes'], $this->locale['save_changes'], ['class' => 'btn-success']);

        echo closeform();
    }

    private function breadcrumbs()
    {
        add_breadcrumb([
            "title" => $this->locale['SL_0001'],
            "link"  => FUSION_SELF . $this->aidlink,
        ]);

        if ($this->section == "settings") {

            add_breadcrumb(['link' => FUSION_SELF . $this->aidlink . "&section=settings", 'title' => $this->locale["SL_0041"]]);
        } elseif ($this->refs == "form") {
            if ($this->action == "edit") {
                add_breadcrumb(['link' => $this->form_action, 'title' => $this->locale['SL_0011']]);
            } else {
                $this->title = $this->locale["SL_0010"];
                add_breadcrumb(['link' => FUSION_SELF . $this->aidlink . "&refs=form", 'title' => $this->locale["SL_0010"]]);
            }
        } elseif ($this->refs == 'nform') {

            if ($this->action == "edit") {
                add_breadcrumb(['link' => $this->form_action, 'title' => $this->locale['SL_0010']]);
            } else {
                $this->title = $this->locale["SL_0010"];
                add_breadcrumb(['link' => FUSION_SELF . $this->aidlink . "&refs=form", 'title' => $this->locale["SL_008"]]);
            }
        } else {
            if ($this->section == 'menu') {

                add_breadcrumb(['link' => FUSION_SELF . $this->aidlink, 'title' => $this->locale["SL_0013"]]);
            } else {
                add_breadcrumb(['link' => FUSION_SELF . $this->aidlink, 'title' => $this->locale["SL_0012"]]);
            }
        }

        $link_index = dbquery_tree(DB_SITE_LINKS, "link_id", "link_cat");

        $link_data = dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat");

        make_page_breadcrumbs($link_index, $link_data, "link_id", "link_name", "cat");
    }

    /**
     * Form for Listing Menu
     */
    private function listing()
    {

        fusion_load_script(INCLUDES . "jquery/jquery-ui/jquery-ui.min.js");

        add_to_jquery(
            /** @lang JavaScript */
            "slAdmin.slListing({
                'SL_0080' : '" . $this->locale["SL_0080"] . "',
                'SL_0016' : '" . $this->locale["SL_0016"] . "',
                'error_preview' : '" . $this->locale["error_preview"] . "',
                'error_preview_text' : '" . $this->locale["error_preview_text"] . "',
            }, '" . fusion_get_token("sitelinks_order", 10) . "');"
        );

        $menus = $this->menuList();

        $cat = get("cat", FILTER_VALIDATE_INT) ?: 0;

?>
        <div class="display-flex align-items-center">
            <?php
            echo form_select('menu', '', $this->menu, [
                'class' => 'm-0',
                'options'     => $menus,
                'placeholder' => $this->locale['SL_0013'],
                'width'       => '300px',
                'onchange'    => "window.location='" . FUSION_SELF . $this->aidlink . "&menu='+this.value"
            ]);
            ?>
            <div class="m-l-a"><?php echo $this->link_actions ?></div>
        </div>
        <hr>
    <?php
        // now do the listing
        $table_api = fusion_table("sitelink", [
            "remote_file" => ADMIN . "includes/?api=sitelinks-list&refs=" . $this->menu . "&cat=$cat",
            "server_side" => TRUE,
            "processing"  => TRUE,
            //"responsive"  => TRUE,
            "debug"       => FALSE,
            "zero_locale" => $this->locale["SL_0062"],
            "columns"     => [
                ["data" => "link_checkbox", "width" => "30", "orderable" => FALSE],
                ["data" => "link_name", "width" => "45%", "className" => "all"],
                ["data" => "link_count", "width" => "10%", "className" => "not-mobile"],
                ["data" => "link_status", "width" => "10%", "className" => "not-mobile"],
                ["data" => "link_window", "width" => "10%"],
                ["data" => "link_visibility", "className" => "not-mobile"],
                ["data" => "link_order", "width" => "50"],
            ]
        ]);
        echo openform("fusion_sltable_form", "POST");
        echo "<table id='$table_api' class='table table-bordered table-striped table-hover'><thead>";
        echo "<tr>";
        echo "<th class='text-center'>" . form_checkbox('check_all', '', "", ["input_value" => 1, "input_id" => "check_all", "default_checked" => FALSE]) . "</th>";
        echo "<th>" . $this->locale["SL_0020"] . "</th>";
        echo "<th>" . $this->locale["SL_0035"] . "</th>";
        echo "<th>" . $this->locale["SL_0031"] . "</th>";
        echo "<th>" . $this->locale["SL_0071"] . "</th>";
        echo "<th>" . $this->locale["SL_0022"] . "</th>";
        echo "<th>" . $this->locale["SL_0052"] . "</th>";
        echo "</tr>";
        echo "</thead><tbody class='sort'></tbody></table>";
        echo form_hidden('table_action');
        echo closeform();
        echo closetabbody();
        echo closetab();
    }

    private function menuListing()
    {
        add_to_jquery(
            "slAdmin.smListing({
        'SL_0081' : '" . $this->locale["SL_0081"] . "',            
        });"
        );

    ?>
        <div class="display-flex align-items-center">
            <div class="m-l-a"><?php echo $this->link_actions ?></div>
        </div>
        <hr>
<?php
        // now do the listing
        $table_api = fusion_table("siteMenu", [
            "remote_file" => ADMIN . "includes/?api=sitelinks-menulist",
            "server_side" => TRUE,
            "processing"  => TRUE,
            //"responsive"  => TRUE,
            "debug"       => FALSE,
            "zero_locale" => $this->locale["SL_0025"],
            "columns"     => [
                ["data" => "menu_checkbox", "width" => "30", "orderable" => FALSE],
                ["data" => "menu_name", "className" => "all"],
                ["data" => "menu_item_count", "className" => "not-mobile"],
                ["data" => "menu_grouping", "className" => "not-mobile"],
                ["data" => "menu_status", "className" => "not-mobile"],
                ["data" => "menu_visibility", "className" => "not-mobile"],
            ]
        ]);
        echo openform("fusion_smtable_form", "POST");
        echo "<table id='$table_api' class='table table-bordered table-striped table-hover'><thead>";
        echo "<tr>";
        echo "<th class='text-center'>" . form_checkbox('check_all', '', "", ["input_value" => 1, "input_id" => "check_all", "default_checked" => FALSE]) . "</th>";
        echo "<th>" . $this->locale["SL_0050"] . "</th>";
        echo "<th>" . $this->locale["SL_0056"] . "</th>";
        echo "<th>" . $this->locale["SL_0055"] . "</th>";
        echo "<th>" . $this->locale["SL_0027"] . "</th>";
        echo "<th>" . $this->locale["SL_0051"] . "</th>";
        echo "</tr>";
        echo "</thead><tbody class='sort'></tbody></table>";
        echo form_hidden('table_action');
        echo closeform();
        echo closetabbody();
        echo closetab();
    }


    /**
     * Perform site links modifications
     */
    private function doMenuAction()
    {
        
        if ($action = post("table_action")) {

            if ($this->section == 'menu') {

                if (in_array($action, ['menu_del', 'menu_publish', 'menu_unpublish'])) {

                    $menu_id = sanitizer(['menu_id'], '', 'menu_id');

                    $menu_array = explode(",", $menu_id);

                    if (!empty($menu_id)) {

                        // Perform menu action
                        foreach ($menu_array as $menu_id) {
                            // check input table
                            if (self::verifyMenu($menu_id) && fusion_safe()) {

                                switch ($action) {

                                    case "menu_publish":
                                        dbquery("UPDATE " . DB_SITE_LINK_MENUS . " SET menu_status='1' WHERE menu_id=:id", [":id" => intval($menu_id)]);
                                        break;
                                    case "menu_unpublish":
                                        dbquery("UPDATE " . DB_SITE_LINK_MENUS . " SET menu_status='0' WHERE menu_id=:id", [":id" => intval($menu_id)]);
                                        break;
                                    case "menu_del":
                                        if (dbcount("(link_id)", \DB_SITE_LINKS, 'link_position=:menuID', [':menuID' => intval($menu_id)])) {
                                            addnotice('danger', $this->locale['SL_0089']);
                                        }
                                        dbquery("DELETE FROM  " . DB_SITE_LINK_MENUS . " WHERE menu_id=:menuID", [":menuID" => intval($menu_id)]);
                                        break;
                                    default:
                                        addnotice("danger", "Invalid action");
                                        redirect(FUSION_SELF . $this->aidlink . '&section=menu');
                                }
                            }
                        }

                        addnotice("success", $this->locale['SL_0068']);

                    } else {

                        addnotice("danger", $this->locale['SL_0087']);
                    }

                    redirect(FUSION_REQUEST);

                } else {
                    addnotice("danger", "Invalid action");
                    redirect(FUSION_REQUEST);
                }

            } else {

                if (in_array($action, ["link_move", "move_confirm", "link_del", 'publish', 'unpublish'])) {

                    $link_id = sanitizer(["link_id"], "", "link_id");
                    $link_array = explode(",", $link_id);

                    if (!empty($link_id)) {

                        if ($action === "link_move") {

                            $modal = openmodal('move_to_mdl', $this->locale['SL_0036'], ['static' => TRUE]);
                            $modal .= openform('move_frm', 'POST');
                            $modal .= form_select('move_to_id', $this->locale['SL_0037'], '', [
                                "db"           => DB_SITE_LINKS,
                                "id_col"       => "link_id",
                                "title_col"    => "link_name",
                                "cat_col"      => "link_cat",
                                "parent_value" => $this->locale['SL_0032'],
                                "inline"       => FALSE,
                                "optgroup"     => FALSE,
                                "custom_query" => "SELECT * FROM " . DB_SITE_LINKS . " " . (multilang_table("SL") ? "WHERE link_language='" . LANGUAGE . "' AND" : "WHERE") . " link_id NOT IN ($link_id) ORDER BY link_name",
                            ]);

                            foreach ($link_array as $link_id) {
                                $modal .= form_hidden('link_id[]', '', $link_id);
                            }

                            $modal .= form_button('table_action', $this->locale['SL_0039'], 'move_confirm', ['class' => 'btn-primary']);
                            $modal .= form_button('link_clear', $this->locale['cancel'], 'cancel', ['class' => 'btn-default m-l-10']);
                            $modal .= closeform();
                            $modal .= closemodal();

                            add_to_footer($modal);
                        } else {

                            // Perform menu action
                            foreach ($link_array as $link_id) {
                                // check input table
                                if (self::verifySiteLink($link_id) && fusion_safe()) {
                                    switch ($action) {
                                        case "publish":
                                            dbquery("UPDATE " . DB_SITE_LINKS . " SET link_status='1' WHERE link_id=:id", [":id" => (int)$link_id]);
                                            break;
                                        case "unpublish":
                                            dbquery("UPDATE " . DB_SITE_LINKS . " SET link_status='0' WHERE link_id=:id", [":id" => (int)$link_id]);
                                            break;
                                        case "move_confirm":
                                            $link_move_to = (check_post("move_to_id") ? sanitizer('move_to_id', 0, 'move_to_id') : 0);
                                            dbquery("UPDATE " . DB_SITE_LINKS . " SET link_cat=:mid WHERE link_id=:id", [":mid" => (int)$link_move_to, "id" => (int)$link_id]);

                                            break;
                                        case "link_del":
                                            $link_order = dbresult(dbquery("SELECT link_order FROM " . DB_SITE_LINKS . " " . (multilang_table("SL") ? "WHERE link_language='" . LANGUAGE . "' AND" : "WHERE") . " link_id=:id", [":id" => (int)$link_id]), 0);
                                            dbquery("UPDATE " . DB_SITE_LINKS . " SET link_order=link_order-1 " . (multilang_table("SL") ? "WHERE link_language='" . LANGUAGE . "' AND" : "WHERE") . " link_order > :order", [":order" => (int)$link_order]);
                                            dbquery("DELETE FROM  " . DB_SITE_LINKS . " WHERE link_id=:id", [":id" => (int)$link_id]);
                                            break;
                                        default:
                                            addnotice("danger", "Invalid action");
                                            redirect(FUSION_SELF . $this->aidlink);
                                    }
                                }
                            }
                            addnotice("success", $this->locale['SL_0016']);
                            redirect(FUSION_REQUEST);
                        }
                    } else {
                        addnotice("danger", $this->locale['SL_0087']);
                    }
                } else {
                    addnotice("danger", "Invalid action");
                    redirect(FUSION_REQUEST);
                }
            }
        }
    }

    /**
     * @return array
     */
    private function menuList()
    {
        $result = dbquery("SELECT menu_id, menu_name FROM " . DB_SITE_LINK_MENUS . " WHERE menu_language=:lang ORDER BY menu_id ", [":lang" => LANGUAGE]);
        if (dbrows($result)) {
            while ($rows = dbarray($result)) {
                $list[$rows['menu_id']] = $rows['menu_name'];
            }
            return $list;
        }
        return array();
    }
}
