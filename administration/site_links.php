<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: site_links.php
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
require_once "../maincore.php";
require_once THEMES."templates/admin_header.php";

class SiteLinks_Admin extends PHPFusion\SiteLinks {

    private static $siteLinksAdmin_instance = NULL;
    private $data = array(
        'link_id' => 0,
        'link_name' => '',
        'link_url' => '',
        'link_icon' => '',
        'link_cat' => 0,
        'link_language' => LANGUAGE,
        'link_visibility' => 0,
        'link_order' => 0,
        'link_position' => 1,
        'link_position_id' => 0,
        'link_window' => 0,
        'link_position_id' => 0,
    );
    private $language_opts = array();
    private $link_index = array();
    private $form_action = '';

    private function __construct() {

        $aidlink = fusion_get_aidlink();

        $locale = fusion_get_locale("", LOCALE.LOCALESET."admin/sitelinks.php");
        $this->language_opts = fusion_get_enabled_languages();
        $this->link_index = dbquery_tree(DB_SITE_LINKS, 'link_id', 'link_cat');

        $_GET['link_id'] = isset($_GET['link_id']) && isnum($_GET['link_id']) ? $_GET['link_id'] : 0;
        $_GET['link_cat'] = isset($_GET['link_cat']) && isnum($_GET['link_cat']) ? $_GET['link_cat'] : 0;
        $_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';

        self::link_breadcrumbs($this->link_index); // must move this out.

        add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery-ui.js'></script>");
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
				$('#info').load('".ADMIN."includes/site_links_updater.php".$aidlink."&' +order+ '&link_cat=".intval($_GET['link_cat'])."');
				ul.find('.num').each(function(i) {
					$(this).text(i+1);
				});
				ul.find('li').removeClass('tbl2').removeClass('tbl1');
				ul.find('li:odd').addClass('tbl2');
				ul.find('li:even').addClass('tbl1');
				window.setTimeout('closeDiv();',2500);
			}
		});

		function checkLinkPosition( val ) {
            if ( val == 4 ) {
                $('#link_position_id').prop('disabled', false).show();
            } else {
                $('#link_position_id').prop('disabled', true).hide();
            }
        }
		");

        switch ($_GET['action']) {
            case 'edit':
                $this->data = self::get_sitelinks($_GET['link_id']);
                $this->data['link_position_id'] = 0;
                if (!$this->data['link_id']) {
                    redirect(FUSION_SELF.$aidlink);
                }
                $this->form_action = FUSION_SELF.$aidlink."&amp;action=edit&amp;section=nform&amp;link_id=".$_GET['link_id']."&amp;link_cat=".$_GET['link_cat'];
                add_breadcrumb(
                    array(
                        "link" => $this->form_action,
                        "title" => $locale['SL_0011']
                    )
                );
                break;
            case 'delete':
                $result = self::delete_sitelinks($_GET['link_id']);
                if ($result) {
                    addNotice("success", $locale['SL_0017']);
                    redirect(FUSION_SELF.$aidlink);
                }
                break;
            default:
                $this->form_action = FUSION_SELF.$aidlink."&amp;section=link_form";
                add_breadcrumb(
                    array(
                        "link" => $this->form_action,
                        "title" => $locale['SL_0010']
                    )
                );
                break;
        }
    }

    /**
     * For Administration panel only
     * @param $link_index
     */
    private static function link_breadcrumbs($link_index) {

        global $aidlink;

        $locale = fusion_get_locale();

        /* Make an infinity traverse */
        if (!function_exists("breadcrumb_arrays")) {
            function breadcrumb_arrays($index, $id) {
                global $aidlink;
                $crumb = &$crumb;
                //$crumb += $crumb;
                if (isset($index[get_parent($index, $id)])) {
                    $_name = dbarray(dbquery("SELECT link_id, link_name FROM ".DB_SITE_LINKS." WHERE link_id='".$id."'"));
                    $crumb = array(
                        'link' => ADMIN.'site_links.php'.$aidlink."&amp;link_cat=".$_name['link_id'],
                        'title' => $_name['link_name']
                    );
                    if (isset($index[get_parent($index, $id)])) {
                        if (get_parent($index, $id) == 0) {
                            return $crumb;
                        }
                        $crumb_1 = breadcrumb_arrays($index, get_parent($index, $id));
                        $crumb = array_merge_recursive($crumb, $crumb_1); // convert so can comply to Fusion Tab API.
                    }
                }

                return $crumb;
            }
        }

        // then we make a infinity recursive function to loop/break it out.
        $crumb = breadcrumb_arrays($link_index, $_GET['link_cat']);
        // then we sort in reverse.
        if (count($crumb['title']) > 1) {
            krsort($crumb['title']);
            krsort($crumb['link']);
        }
        // then we loop it out using Dan's breadcrumb.
        add_breadcrumb(array('link' => ADMIN.'site_links.php'.$aidlink, 'title' => $locale['SL_0001']));
        if (count($crumb['title']) > 1) {
            foreach ($crumb['title'] as $i => $value) {
                add_breadcrumb(array('link' => $crumb['link'][$i], 'title' => $value));
            }
        } elseif (isset($crumb['title'])) {
            add_breadcrumb(array('link' => $crumb['link'], 'title' => $crumb['title']));
        }
    }

    public static function Administration() {
        if (empty(self::$siteLinksAdmin_instance)) {
            self::$siteLinksAdmin_instance = new SiteLinks_Admin();
        }

        return self::$siteLinksAdmin_instance;
    }

    public function display_administration_form() {

        pageAccess("SL");

        $aidlink = fusion_get_aidlink();

        $locale = fusion_get_locale("", LOCALE.LOCALESET."admin/sitelinks.php");

        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.$aidlink);
        }

        $title = $locale['SL_0001'];
        if (isset($_GET['ref']) && $_GET['ref'] == "link_form") {
            $title = isset($_GET['link_id']) && $this->verify_sitelinks($_GET['link_id']) ? $locale['SL_0011'] : $locale['SL_0010'];
        }

        $master_title['title'][] = $title;
        $master_title['id'][] = "links";
        $master_title['icon'][] = '';

        $master_title['title'][] = $locale['SL_0041'];
        $master_title['id'][] = "settings";
        $master_title['icon'][] = '';

        $link_index = dbquery_tree(DB_SITE_LINKS, "link_id", "link_cat");
        $link_data = dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat");
        make_page_breadcrumbs($link_index, $link_data, "link_id", "link_name", "link_cat");

        opentable($locale['SL_0012']);

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

            } else {
                $this->display_sitelinks_list();
            }

        }
        echo closetab();
        closetable();
    }

    /**
     * Site Links Settings Administration Form
     */
    private function display_sitelinks_settings() {

        $locale = fusion_get_locale("", LOCALE.LOCALESET."admin/sitelinks.php");

        fusion_confirm_exit();

        add_to_title($locale['SL_0041']);

        $settings = array(
            "links_per_page" => fusion_get_settings("links_per_page"),
            "links_grouping" => fusion_get_settings("links_grouping")
        );

        /**
         * @silent upgrade
         * @todo: Remove this line on 31/12/2016
         * */
        if ($settings['links_per_page'] === NULL) {
            dbquery("INSERT INTO ".DB_SETTINGS." (settings_name, settings_value) VALUES ('links_per_page', 8)");
            dbquery("INSERT INTO ".DB_SETTINGS." (settings_name, settings_value) VALUES ('links_grouping', 1)");
        }

        if (isset($_POST['save_settings'])) {

            $settings = array(
                "links_per_page" => form_sanitizer($_POST['links_per_page'], 1, "links_per_page"),
                "links_grouping" => form_sanitizer($_POST['links_grouping'], 0, "links_grouping")
            );
            if (\defender::safe()) {
                foreach ($settings as $key => $value) {
                    dbquery("UPDATE ".DB_SETTINGS." SET settings_value = '$value' WHERE settings_name = '$key'");
                }
                addNotice("success", $locale['SL_0018']);
                redirect(FUSION_REQUEST);
            }

        }

        echo openform("sitelinks_settings", "post", FUSION_REQUEST, array("class" => "m-t-20 m-b-20"));

        echo "<div class='well'>\n";
        echo $locale['SL_0042'];
        echo "</div>\n";

        echo "<div class='row'>\n<div class='col-xs-12 col-sm-3'><strong>".$locale['SL_0046']."</strong><br/>".$locale['SL_0047']."</div>";
        echo "<div class='col-xs-12 col-sm-9'>\n";
        echo form_checkbox("links_grouping", "", $settings['links_grouping'],
                           array(
                               "options" => array(
                                   0 => $locale['SL_0048'],
                                   1 => $locale['SL_0049']
                               ),
                               "type" => "radio",
                               "inline" => TRUE,
                               "width" => "250px",
                           )
        );
        echo "</div>\n</div>\n";


        echo "<div id='lpp' class='row' ".($settings['links_grouping'] == FALSE ? "style='display:none'" : "").">\n<div class='col-xs-12 col-sm-3'><strong>".$locale['SL_0043']."</strong><br/>".$locale['SL_0044']."</div>";
        echo "<div class='col-xs-12 col-sm-9'>\n";
        echo form_text("links_per_page", $locale['SL_0045'], $settings['links_per_page'],
                       array(
                           "type" => "number",
                           "inline" => FALSE,
                           "width" => "250px",
                           "required" => TRUE,
                       )
        );
        echo "</div>\n</div>\n";
        add_to_jquery("
        var lpp = $('#lpp');
        $('#links_grouping-0').bind('click', function(e){ lpp.slideUp(); });
        $('#links_grouping-1').bind('click', function(e){ lpp.slideDown(); });
        ");

        echo form_button('save_settings', $locale['save_changes'], $locale['save_changes'],
                         array('class' => 'btn-primary'));
        echo closeform();
    }

    /**
     * Site Links Form
     */
    private function display_sitelinks_form() {

        $locale = fusion_get_locale();

        fusion_confirm_exit();

        if (isset($_POST['savelink'])) {

            $this->data = array(
                "link_id" => form_sanitizer($_POST['link_id'], 0, 'link_id'),
                "link_cat" => form_sanitizer($_POST['link_cat'], 0, 'link_cat'),
                "link_name" => form_sanitizer($_POST['link_name'], '', 'link_name'),
                "link_url" => form_sanitizer($_POST['link_url'], '', 'link_url'),
                "link_icon" => form_sanitizer($_POST['link_icon'], '', 'link_icon'),
                "link_language" => form_sanitizer($_POST['link_language'], '', 'link_language'),
                "link_visibility" => form_sanitizer($_POST['link_visibility'], '', 'link_visibility'),
                "link_position" => form_sanitizer($_POST['link_position'], '', 'link_position'),
                "link_order" => form_sanitizer($_POST['link_order'], '', 'link_order'),
                "link_window" => form_sanitizer(isset($_POST['link_window']) && $_POST['link_window'] == 1 ? 1 : 0, 0,
                                                'link_window')
            );
            if ($this->data['link_position'] > 3) {
                $this->data['link_position'] = form_sanitizer($_POST['link_position_id'], 3, 'link_position_id');
            }

            if (empty($this->data['link_order'])) {

                $max_order_query = "SELECT MAX(link_order) 'link_order' FROM ".DB_SITE_LINKS."
                ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")."
                link_cat='".$this->data['link_cat']."'";

                $this->data['link_order'] = dbresult(dbquery($max_order_query), 0) + 1;
            }

            if (\defender::safe()) {
                if (!empty($this->data['link_id'])) {

                    dbquery_order(DB_SITE_LINKS, $this->data['link_order'], "link_order", $this->data['link_id'],
                                  "link_id",
                                  $this->data['link_cat'], "link_cat", multilang_table("SL"), "link_language",
                                  "update");

                    dbquery_insert(DB_SITE_LINKS, $this->data, 'update');

                    addNotice("success", $locale['SL_0016']);

                } else {

                    dbquery_order(DB_SITE_LINKS, $this->data['link_order'], "link_order", $this->data['link_id'],
                                  "link_id",
                                  $this->data['link_cat'], "link_cat", multilang_table("SL"), "link_language", "save");

                    dbquery_insert(DB_SITE_LINKS, $this->data, 'save');

                    addNotice("success", $locale['SL_0015']);

                }

                redirect(clean_request("link_cat=".$this->data['link_cat'], array('ref'), FALSE));
            }
        }

        echo "<div class='m-t-20'>\n";
        echo openform('link_administration_frm', 'post', FUSION_REQUEST);
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-8 col-lg-8'>\n";
        echo form_hidden('link_id', '', $this->data['link_id']);
        echo form_textarea('link_name', $locale['SL_0020'], $this->data['link_name'], array(
            'max_length' => 100,
            'required' => TRUE,
            'error_text' => $locale['SL_0085'],
            'form_name' => 'linkform',
            'type' => 'bbcode',
            'inline' => TRUE
        ));
        echo form_text('link_icon', $locale['SL_0020a'], $this->data['link_icon'], array(
            'max_length' => 100,
            'inline' => TRUE
        ));
        echo form_text('link_url', $locale['SL_0021'], $this->data['link_url'], array(
            'required' => TRUE,
            'error_text' => $locale['SL_0086'],
            'inline' => TRUE
        ));
        echo form_text('link_order', $locale['SL_0023'], $this->data['link_order'], array(
            'class' => 'pull-left',
            'inline' => TRUE,
            'width' => '250px',
            'type' => 'number'
        ));

        // There will be a trick to manipulate the situation here
        if ($this->data['link_position'] > 3) {
            $this->data['link_position_id'] = $this->data['link_position'];
            $this->data['link_position'] = 4;
        }

        echo form_select('link_position', $locale['SL_0024'], $this->data['link_position'],
                         array(
                             'options' => self::get_SiteLinksPosition(),
                             'inline' => TRUE,
                             'stacked' => form_text('link_position_id', '', $this->data['link_position_id'],
                                                    array(
                                                        'required' => TRUE,
                                                        'placeholder' => 'ID',
                                                        'type' => 'number',
                                                        'type' => 'number',
                                                        'width' => '150px'
                                                    )
                             )
                         ));


        add_to_jquery("
        checkLinkPosition( ".$this->data['link_position']." );
        $('#link_position').bind('change', function(e) {
            checkLinkPosition( $(this).val() );
        });
        ");


        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-4 col-lg-4'>\n";

        echo form_select_tree("link_cat", $locale['SL_0029'], $this->data['link_cat'], array(
            'input_id' => 'link_categorys',
            "parent_value" => $locale['parent'],
            'width' => '100%',
            'query' => (multilang_table("SL") ? "WHERE link_language='".LANGUAGE."'" : ''),
            'disable_opts' => $this->data['link_id'],
            'hide_disabled' => 1
        ), DB_SITE_LINKS, "link_name", "link_id", "link_cat");

        echo form_select('link_language', $locale['global_ML100'], $this->data['link_language'], array(
            'options' => $this->language_opts,
            'placeholder' => $locale['choose'],
            'width' => '100%'
        ));
        echo form_select('link_visibility', $locale['SL_0022'], $this->data['link_visibility'], array(
            'options' => self::get_LinkVisibility(),
            'placeholder' => $locale['choose'],
            'width' => '100%'
        ));
        echo form_checkbox('link_window', $locale['SL_0028'], $this->data['link_window']);
        echo "</div>\n";
        echo "</div>\n";
        echo form_button('savelink', $locale['SL_0040'], $locale['SL_0040'],
                         array('class' => 'btn-primary m-r-10', 'input_id' => 'savelink_2'));
        echo form_button("cancel", $locale['cancel'], "cancel", array('input_id' => 'cancel2'));
        echo closeform();
        echo "</div>\n";
    }

    /**
     * Form for Listing Menu
     */
    private function display_sitelinks_list() {

        $aidlink = fusion_get_aidlink();
        $locale = fusion_get_locale();
        $visibility = self::get_LinkVisibility();
        $position_opts = self::get_SiteLinksPosition();

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
					data: { q: $(this).data('id'), token: '".$aidlink."' },
					success: function(e) {
						$('#sl_id').val(e.link_id);
						$('#sl_name').val(e.link_name);
						$('#sl_icon').val(e.link_icon);

						// switch to custom
						$('#sl_position').select2('val', e.link_position);
						if (e.link_position > 3) {
						    $('#link_position_id').val(e.link_position);
						    $('#sl_link_position').val(4);
						}
						checkLinkPosition(e.link_position);

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

        $result = dbquery("SELECT * FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_cat='".intval($_GET['link_cat'])."' ORDER BY link_order");

        echo "<div class='m-t-20 m-b-20'>\n";
        echo "<a href='".clean_request("ref=link_form", array("ref"), FALSE)."' class='btn btn-success'>".$locale['SL_0010']."</a>\n";
        echo "</div>\n";
        echo "<hr/>";

        echo "<div id='info'></div>\n";

        echo "<div class='m-t-20'>\n";
        echo "<table class='table table-striped table-responsive'>\n";
        echo "<tr>\n";
        echo "<th>\n</th>\n";
        echo "<th>".$locale['SL_0073']."</th>";
        echo "<th class='col-xs-12 col-sm-4 col-md-4 col-lg-4'>".$locale['SL_0050']."</th>\n";
        echo "<th>".$locale['SL_0070']."</th>";
        echo "<th>".$locale['SL_0071']."</th>";
        echo "<th>".$locale['SL_0072']."</th>";
        echo "<th>".$locale['SL_0051']."</th>";
        echo "<th>".$locale['SL_0052']."</th>";
        echo "</tr>\n";

        // Load form data. Then, if have data, show form.. when post, we use back this page's script.
        if (isset($_POST['link_quicksave'])) {

            $this->data = array(
                "link_id" => form_sanitizer($_POST['link_id'], 0, "link_id"),
                "link_name" => form_sanitizer($_POST['link_name'], "", "link_name"),
                "link_icon" => form_sanitizer($_POST['link_icon'], "", "link_icon"),
                "link_language" => form_sanitizer($_POST['link_language'], "", "link_language"),
                "link_position" => form_sanitizer($_POST['link_position'], "", "link_position"),
                "link_visibility" => form_sanitizer($_POST['link_visibility'], "", "link_visibility"),
                "link_window" => isset($_POST['link_window']) ? TRUE : FALSE,
            );

            if ($this->data['link_position'] > 3) {
                $this->data['link_position'] = form_sanitizer($_POST['link_position_id'], 3, 'link_position_id');
            }

            if (\defender::safe()) {
                dbquery_insert(DB_SITE_LINKS, $this->data, "update");
                addNotice("success", $locale['SL_0016']);
                redirect(FUSION_SELF.$aidlink."&amp;section=links&amp;link_cat=".$_GET['link_cat']);
            }
        }

        echo "<tr class='qform'>\n";
        echo "<td colspan='8'>\n";
        echo "<div class='list-group-item m-t-20 m-b-20'>\n";
        echo openform('quick_edit', 'post', FUSION_SELF.$aidlink."&amp;section=links&amp;link_cat=".$_GET['link_cat']);
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-5 col-md-12 col-lg-6'>\n";
        echo form_hidden("link_id", "", $this->data['link_id'], array('input_id' => 'sl_id'));
        echo form_textarea('link_name', $locale['SL_0020'], '', array(
            'placeholder' => 'Link Title', "input_id" => "sl_name", "type" => 'bbcode', 'form_name' => 'quick_edit'
        ));
        echo form_text('link_icon', $locale['SL_0030'], $this->data['link_icon'],
                       array('max_length' => 100, "input_id" => "sl_icon"));
        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
        echo form_select('link_language', $locale['global_ML100'], $this->data['link_language'], array(
            'options' => $this->language_opts,
            'input_id' => 'sl_language',
            'width' => '100%'
        ));

        echo form_select('link_position', $locale['SL_0024'], $this->data['link_position'],
                         array(
                             'options' => self::get_SiteLinksPosition(),
                             'input_id' => 'sl-link_position',
                             'stacked' => form_text('link_position_id', '', $this->data['link_position_id'],
                                                    array(
                                                        'class' => 'm-t-20',
                                                        'required' => TRUE,
                                                        'placeholder' => 'ID',
                                                        'type' => 'number',
                                                        'type' => 'number',
                                                        'width' => '150px'
                                                    )
                             )
                         )
        );


        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-3'>\n";
        echo form_select('link_visibility', $locale['SL_0022'], $this->data['link_visibility'], array(
            'options' => self::get_LinkVisibility(),
            'input_id' => 'sl_visibility',
            'width' => '100%'
        ));
        echo form_checkbox('link_window', $locale['SL_0028'], $this->data['link_window'],
                           array('input_id' => 'sl_window', 'reverse_label' => TRUE));
        echo "</div>\n";
        echo "</div>\n";
        echo "<div class='m-t-10 m-b-10'>\n";
        echo form_button('cancel', $locale['cancel'], 'cancel', array(
            'class' => 'btn btn-default m-r-10',
            'type' => 'button'
        ));
        echo form_button('link_quicksave', $locale['save'], 'save', array('class' => 'btn btn-primary'));
        echo "</div>\n";
        echo closeform();

        echo "</div>\n";
        echo "</td>\n";
        echo "</tr>\n";

        echo "<tbody id='site-links' class='connected'>\n";

        if (dbrows($result) > 0) {
            $i = 0;
            while ($data = dbarray($result)) {

                $data['link_name'] = parsesmileys(parseubb($data['link_name']));

                $link_position = $locale['custom']." ID #".$data['link_position'];
                if (isset($position_opts[$data['link_position']]) && $data['link_position'] < 4) {
                    $link_position = $position_opts[$data['link_position']];
                }

                echo "<tr id='listItem_".$data['link_id']."' data-id='".$data['link_id']."' class='list-result '>\n";
                echo "<td></td>\n";
                echo "<td><i class='pointer handle fa fa-arrows' title='".$locale['SL_0074']."'></i></td>\n";
                echo "<td>\n";
                echo "<a class='text-dark' href='".FUSION_SELF.$aidlink."&amp;section=links&amp;link_cat=".$data['link_id']."'>".$data['link_name']."</a>\n";
                echo "<div class='actionbar text-smaller' id='sl-".$data['link_id']."-actions'>
				<a href='".FUSION_SELF.$aidlink."&amp;section=links&amp;ref=link_form&amp;action=edit&amp;link_id=".$data['link_id']."&amp;link_cat=".$data['link_cat']."'>".$locale['edit']."</a> |
				<a class='qedit pointer' data-id='".$data['link_id']."'>".$locale['qedit']."</a> |
				<a class='delete' href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;link_id=".$data['link_id']."' onclick=\"return confirm('".$locale['SL_0080']."');\">".$locale['delete']."</a> |
				";
                if (strstr($data['link_url'], "http://") || strstr($data['link_url'], "https://")) {
                    echo "<a href='".$data['link_url']."'>".$locale['view']."</a>\n";
                } else {
                    echo "<a href='".BASEDIR.$data['link_url']."'>".$locale['view']."</a>\n";
                }
                echo "</div>";
                echo "</td>\n";
                echo "<td><i class='".$data['link_icon']."'></i></td>\n";
                echo "<td>".($data['link_window'] ? $locale['yes'] : $locale['no'])."</td>\n";
                echo "<td>$link_position</td>\n";

                echo "<td>".$visibility[$data['link_visibility']]."</td>\n";
                echo "<td class='num'>".$data['link_order']."</td>\n";
                echo "</tr>\n";
                $i++;
            }
        } else {
            echo "<tr>\n";
            echo "<td colspan='7' class='text-center'>".$locale['SL_0062']."</td>\n";
            echo "</tr>\n";
        }
        echo "</tbody>\n";
        echo "</table>\n";
        echo "</div>\n";
    }
}

SiteLinks_Admin::Administration()->display_administration_form();
require_once THEMES."templates/footer.php";