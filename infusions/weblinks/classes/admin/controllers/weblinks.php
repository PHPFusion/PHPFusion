<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/admin/controllers/weblinks.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Weblinks;

class WeblinksAdmin extends WeblinksAdminModel {

    private static $instance = NULL;
    private $locale = array();
    private $form_action = FUSION_REQUEST;
    private $weblinksSettings = array();
    private $weblink_data = array();

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function displayWeblinksAdmin() {
        pageAccess("W");
        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }
        $this->locale = fusion_get_locale("", WEBLINK_ADMIN_LOCALE);
        $this->weblinksSettings = self::get_weblink_settings();

        if (isset($_GET['ref']) && $_GET['ref'] == "weblinkform") {
            $this->display_weblinks_form();
        } else {
            $this->display_weblinks_listing();
        }
    }

    /**
     * Displays Weblinks Form
     */
    private function display_weblinks_form() {

        // Delete Weblink
        self::execute_Delete();

        // Update Weblink
        self::execute_Update();

        /**
         * Global vars
         */
        if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['weblink_id']) && isnum($_POST['weblink_id'])) || (isset($_GET['weblink_id']) && isnum($_GET['weblink_id']))) {
            $result = dbquery("SELECT * FROM ".DB_WEBLINKS." WHERE weblink_id='".(isset($_POST['weblink_id']) ? $_POST['weblink_id'] : $_GET['weblink_id'])."'");
            if (dbrows($result)) {
                $this->weblink_data = dbarray($result);
            } else {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }

        // Data
        $this->weblink_data += $this->default_weblink_data;
        self::weblinkContent_form();
    }

    /**
     * Create or Update a Weblink
     */
    private function execute_Update() {

        if ((isset($_POST['save'])) or (isset($_POST['save_and_close']))) {

            // Check posted Informations

            $this->weblink_data = array(
                "weblink_id" => form_sanitizer($_POST['weblink_id'], 0, "weblink_id"),
                "weblink_name" => form_sanitizer($_POST['weblink_name'], "", "weblink_name"),
                "weblink_cat" => form_sanitizer($_POST['weblink_cat'], 0, "weblink_cat"),
                "weblink_url" => form_sanitizer($_POST['weblink_url'], "", "weblink_url"),
                "weblink_description" => form_sanitizer($_POST['weblink_description'], "", "weblink_description"),
                "weblink_datestamp" => form_sanitizer($_POST['weblink_datestamp'], "", "weblink_datestamp"),
                "weblink_visibility" => form_sanitizer($_POST['weblink_visibility'], 0, "weblink_visibility"),
                "weblink_status" => isset($_POST['weblink_status']) ? "1" : "0",
                "weblink_language" => form_sanitizer($_POST['weblink_language'], LANGUAGE, "weblink_language"),
            );

            // Handle
            if (\defender::safe()) {

                // Update
                if (dbcount("('weblink_id')", DB_WEBLINKS, "weblink_id='".$this->weblink_data['weblink_id']."'")) {
                $this->weblink_data['weblink_datestamp'] = isset($_POST['update_datestamp']) ? time() : $this->weblink_data['weblink_datestamp'];
                    dbquery_insert(DB_WEBLINKS, $this->weblink_data, "update");
                    addNotice("success", $this->locale['WLS_0031']);


                // Create
                } else {
                    $this->weblink_data['weblink_id'] = dbquery_insert(DB_WEBLINKS, $this->weblink_data, "save");
                    addNotice("success", $this->locale['WLS_0030']);
                }

                // Redirect
                if (isset($_POST['save_and_close'])) {
                    redirect(clean_request("", array("ref", "action", "weblink_id"), FALSE));
                } else {
                    redirect(clean_request('action=edit&weblink_id='.$this->weblink_data['weblink_id'], array('action', 'weblink_id'), FALSE));
                }
            }
        }
    }

    /**
     * Display Form for Weblink
     */
    private function weblinkContent_form() {

        // Textarea Settings
        if (!fusion_get_settings("tinymce_enabled")) {
            $ExtendedSettings = array(
                "required" => ($this->weblinksSettings['links_extended_required'] ? true : false), "preview" => true, "html" => true, "autosize" => true, "placeholder" => $this->locale['WLS_0255'],
                "error_text" => $this->locale['WLS_0270'], "form_name" => "weblinkform", "wordcount" => true
            );
        } else {
            $ExtendedSettings = array("required" => ($this->weblinksSettings['links_extended_required'] ? true : false), "type" => "tinymce", "tinymce" => "advanced", "error_text" => $this->locale['WLS_0270']);
        }

        // Start Form
        echo openform("weblinkform", "post", $this->form_action);
        self::display_weblinkButtons("formstart", true);
        echo form_hidden("weblink_id", "", $this->weblink_data['weblink_id']);
        ?>

        <!-- Display Form -->
        <div class="row">

            <!-- Display Left Column -->
            <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8">
                <?php
                echo form_text("weblink_name", $this->locale['WLS_0201'], $this->weblink_data['weblink_name'], array(
                    "required" => true, "placeholder" => $this->locale['WLS_0201'], "error_text" => $this->locale['WLS_0252']
                ));

                echo form_text("weblink_url", $this->locale['WLS_0253'], $this->weblink_data['weblink_url'], array(
                    "required" => TRUE, "type" => "url", "placeholder" => "http://"
                ));

                echo form_textarea("weblink_description", $this->locale['WLS_0254'], $this->weblink_data['weblink_description'], $ExtendedSettings);
                ?>
            </div>

            <!-- Display Right Column -->
            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4">
                <?php

                openside($this->locale['WLS_0260']);

                echo form_select_tree("weblink_cat", $this->locale['WLS_0101'], $this->weblink_data['weblink_cat'], array(
                        "required" => TRUE, "no_root" => TRUE, "placeholder" =>  $this->locale['choose'],
                        "query" => (multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."'" : "")
                        ), DB_WEBLINK_CATS, "weblink_cat_name", "weblink_cat_id", "weblink_cat_parent");

                echo form_select("weblink_visibility", $this->locale['WLS_0103'], $this->weblink_data['weblink_visibility'], array(
                    "options" => fusion_get_groups(), "placeholder" => $this->locale['choose']
                ));

                if (multilang_table("WL")) {
                    echo form_select("weblink_language", $this->locale['language'], $this->weblink_data['weblink_language'], array(
                        "options" => fusion_get_enabled_languages(), "placeholder" => $this->locale['choose']
                    ));
                } else {
                    echo form_hidden("weblink_language", "", $this->weblink_data['weblink_language']);
                }

                echo form_hidden("weblink_status", "", 1);
                echo form_hidden("weblink_datestamp", "", $this->weblink_data['weblink_datestamp']);

                if (!empty($_GET['action']) && $_GET['action'] == 'edit') {
                    echo form_checkbox("update_datestamp", $this->locale['WLS_0259'], "");
                }

                closeside();

                ?>

            </div>
        </div>
        <?php
        self::display_weblinkButtons("formend", false);
        echo closeform();
    }

    /**
     * Generate sets of push buttons for weblinks content form
     *
     * @param      $unique_id
     * @param bool $breaker
     */
    private function display_weblinkButtons($unique_id, $breaker = true) {
        echo "<div class='m-t-20'>\n";
        echo form_button("cancel", $this->locale['cancel'], $this->locale['cancel'], array("class" => "btn-default m-r-10", "icon" => "fa fa-fw fa-times", "input-id" => "cancel-".$unique_id.""));
        echo form_button("save", $this->locale['save'], $this->locale['save'], array("class" => "btn-success m-r-10", "icon" => "fa fa-fw fa-hdd-o", "input-id" => "save-".$unique_id.""));
        echo form_button("save_and_close", $this->locale['save_and_close'], $this->locale['save_and_close'], array("class" => "btn-primary m-r-10", "icon" => "fa fa-fw fa-floppy-o", "input-id" => "save_and_close-".$unique_id.""));
        echo "</div>\n";
        if ($breaker) { echo "<hr />\n"; }
    }

    /**
     * Displays Weblinks Listing
     */
    private function display_weblinks_listing() {

        // Run functions
        $allowed_actions = array_flip(array("publish", "unpublish", "delete", "verify", "weblink_display"));

        // Table Actions
        if (isset($_POST['table_action']) && isset($allowed_actions[$_POST['table_action']])) {

            $input = (isset($_POST['weblink_id'])) ? explode(",", form_sanitizer($_POST['weblink_id'], "", "weblink_id")) : "";
            if (empty($input) && $_POST['table_action'] == "verify") {
            self::verifyLink();
            redirect(FUSION_REQUEST);
            }

            if (!empty($input)) {
                foreach ($input as $weblink_id) {
                    // check input table
                    if (dbcount("('weblink_id')", DB_WEBLINKS, "weblink_id='".intval($weblink_id)."'") && \defender::safe()) {

                        switch ($_POST['table_action']) {
                            case "publish":
                                dbquery("UPDATE ".DB_WEBLINKS." SET weblink_status='1' WHERE weblink_id='".intval($weblink_id)."'");
                                addNotice("success", $this->locale['WLS_0035']);
                                break;
                            case "unpublish":
                                dbquery("UPDATE ".DB_WEBLINKS." SET weblink_status='0' WHERE weblink_id='".intval($weblink_id)."'");
                                addNotice("warning", $this->locale['WLS_0036']);
                                break;
                            case "delete":
                                dbquery("DELETE FROM ".DB_WEBLINKS." WHERE weblink_id='".intval($weblink_id)."'");
                                addNotice("warning", $this->locale['WLS_0032']);
                                break;
                            case "verify":
                                self::verifyLink($weblink_id);
                                break;
                            default:
                                redirect(FUSION_REQUEST);
                        }
                    }
                }
                redirect(FUSION_REQUEST);
            }
            addNotice("warning", $this->locale['WLS_0034']);
            redirect(FUSION_REQUEST);
        }

        // Clear
        if (isset($_POST['weblink_clear'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        // Search
        $sql_condition = "";
        $search_string = array();
        if (isset($_POST['p-submit-weblink_name'])) {
            $search_string['weblink_name'] = array(
                "input" => form_sanitizer($_POST['weblink_name'], "", "weblink_name"), "operator" => "LIKE", "option" => "AND"
            );
            $search_string['weblink_url'] = array(
                "input" => form_sanitizer($_POST['weblink_name'], "", "weblink_name"), "operator" => "LIKE", "option" => "OR"
            );
            $search_string['weblink_description'] = array(
                "input" => form_sanitizer($_POST['weblink_name'], "", "weblink_name"), "operator" => "LIKE", "option" => "OR"
            );
        }

        if (!empty($_POST['weblink_status']) && isnum($_POST['weblink_status']) && $_POST['weblink_status'] == "1") {
            $search_string['weblink_status'] = array("input" => 1, "operator" => "=", "option" => "AND");
        }

        if (!empty($_POST['weblink_visibility'])) {
            $search_string['weblink_visibility'] = array(
                "input" => form_sanitizer($_POST['weblink_visibility'], "", "weblink_visibility"), "operator" => "=", "option" => "AND"
            );
        }

        if (!empty($_POST['weblink_cat'])) {
            $search_string['weblink_cat'] = array(
                "input" => form_sanitizer($_POST['weblink_cat'], "", "weblink_cat"), "operator" => "=", "option" => "AND"
            );
        }

        if (!empty($_POST['weblink_language'])) {
            $search_string['weblink_language'] = array(
                "input" => form_sanitizer($_POST['weblink_language'], "", "weblink_language"), "operator" => "=", "option" => "AND"
            );
        }

        if (!empty($search_string)) {
            foreach ($search_string as $key => $values) {
                $sql_condition .= " ".$values['option']." `$key` ".$values['operator'].($values['operator'] == "LIKE" ? "'%" : "'").$values['input'].($values['operator'] == "LIKE" ? "%'" : "'");
            }
        }

        $default_display = 16;
        $limit = $default_display;
        if ((!empty($_POST['weblink_display']) && isnum($_POST['weblink_display'])) || (!empty($_GET['weblink_display']) && isnum($_GET['weblink_display']))) {
            $limit = (!empty($_POST['weblink_display']) ? $_POST['weblink_display'] : $_GET['weblink_display']);
        }

        $max_rows = dbcount("(weblink_id)", DB_WEBLINKS);
        $rowstart = 0;
        if (!isset($_POST['weblink_display'])) {
            $rowstart = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_rows ? $_GET['rowstart'] : 0);
        }

        // Query
        $weblink_query = "
            SELECT
                a.*, ac.*
            FROM ".DB_WEBLINKS." a
            LEFT JOIN ".DB_WEBLINK_CATS." ac ON ac.weblink_cat_id=a.weblink_cat
            WHERE ".(multilang_table("WL") ? "weblink_language='".LANGUAGE."'" : "")."
            $sql_condition
            ORDER BY weblink_status DESC, weblink_datestamp DESC
            LIMIT $rowstart, $limit
        ";
        $result2 = dbquery($weblink_query);
        $weblink_rows = dbrows($result2);
        $weblink_cats = dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, "");

        // Filters
        $filter_values = array(
            "weblink_name" => !empty($_POST['weblink_name']) ? form_sanitizer($_POST['weblink_name'], "", "weblink_name") : "",
            "weblink_status" => !empty($_POST['weblink_status']) ? form_sanitizer($_POST['weblink_status'], "", "weblink_status") : "",
            "weblink_cat" => !empty($_POST['weblink_cat']) ? form_sanitizer($_POST['weblink_cat'], "", "weblink_cat") : "",
            "weblink_visibility" => !empty($_POST['weblink_visibility']) ? form_sanitizer($_POST['weblink_visibility'], "", "weblink_visibility") : "",
            "weblink_language" => !empty($_POST['weblink_language']) ? form_sanitizer($_POST['weblink_language'], "", "weblink_language") : "",
        );

        $filter_empty = TRUE;
        foreach ($filter_values as $val) {
            if ($val) {
                $filter_empty = FALSE;
            }
        }

        ?>
        <div class="m-t-15">
            <?php echo openform("weblink_filter", "post", FUSION_REQUEST); ?>

            <!-- Display Buttons and Search -->
            <div class="clearfix">
                <div class="pull-right">
                    <?php if ($weblink_cats) { ?>
                        <a class="btn btn-success btn-sm m-r-10" href="<?php echo clean_request("ref=weblinkform", array("ref"), false); ?>"><i class="fa fa-fw fa-plus"></i> <?php echo $this->locale['WLS_0002']; ?></a>
                    <?php } ?>
                    <a class="btn btn-default btn-sm m-r-10" onclick="run_admin('verify');"><i class="fa fa-fw fa-globe"></i> <?php echo $this->locale['WLS_0261']; ?></a>
                    <a class="btn btn-default btn-sm m-r-10" onclick="run_admin('publish');"><i class="fa fa-fw fa-check"></i> <?php echo $this->locale['publish']; ?></a>
                    <a class="btn btn-default btn-sm m-r-10" onclick="run_admin('unpublish');"><i class="fa fa-fw fa-ban"></i> <?php echo $this->locale['unpublish']; ?></a>
                    <a class="btn btn-danger btn-sm m-r-10" onclick="run_admin('delete');"><i class="fa fa-fw fa-trash-o"></i> <?php echo $this->locale['delete']; ?></a>
                </div>

                <div class="display-inline-block pull-left m-r-10" style="width: 300px;">
                <?php echo form_text("weblink_name", "", $filter_values['weblink_name'], array(
                    "placeholder" => $this->locale['WLS_0120'],
                    "append_button" => TRUE,
                    "append_value" => "<i class='fa fa-search'></i>",
                    "append_form_value" => "search_weblink",
                    "width" => "250px",
                    "group_size" => "sm"
                )); ?>
                </div>

                <div class="display-inline-block" style="vertical-align: top;">
                  <a class="btn btn-sm m-r-10 <?php echo ($filter_empty ? "btn-default" : "btn-info"); ?>" id="toggle_options" href="#">
                    <?php echo $this->locale['WLS_0121']; ?>
                    <span id="filter_caret" class="fa fa-fw <?php echo ($filter_empty ? "fa-caret-down" : "fa-caret-up"); ?>"></span>
                  </a>
                  <?php echo form_button("weblink_clear", $this->locale['WLS_0122'], "clear", array("class" => "btn-default btn-sm")); ?>
                </div>
            </div>

            <!-- Display Filters -->
            <div id="weblink_filter_options"<?php echo ($filter_empty ? " style='display: none;'" : ""); ?>>
              <div class="display-inline-block">
                <?php
                    echo form_select("weblink_status", "", $filter_values['weblink_status'], array(
                        "allowclear" => TRUE, "placeholder" => "- ".$this->locale['WLS_0123']." -", "options" => array(0 => $this->locale['WLS_0124'], 1 => $this->locale['draft'])
                    ));
                ?>
              </div>
              <div class="display-inline-block">
                <?php
                    echo form_select("weblink_visibility", "", $filter_values['weblink_visibility'], array(
                        "allowclear" => TRUE, "placeholder" => "- ".$this->locale['WLS_0125']." -", "options" => fusion_get_groups()
                    ));
                ?>
              </div>
              <div class="display-inline-block">
                <?php
                echo form_select_tree("weblink_cat", "", $filter_values['weblink_cat'], array(
                        "query" => (multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."'" : ""),
                        "parent_value" => $this->locale['WLS_0127'],
                        "placeholder" => "- ".$this->locale['WLS_0126']." -",
                        "allowclear" => TRUE
                    ), DB_WEBLINK_CATS, "weblink_cat_name", "weblink_cat_id", "weblink_cat_parent");
                ?>
              </div>
              <div class="display-inline-block">
                <?php
                    $language_opts = array(0 => $this->locale['WLS_0129']);
                    $language_opts += fusion_get_enabled_languages();
                    echo form_select("weblink_language", "", $filter_values['weblink_language'], array(
                        "allowclear" => TRUE, "placeholder" => "- ".$this->locale['WLS_0128']." -", "options" => $language_opts
                    ));
                ?>
              </div>
              <div class="display-inline-block">
              </div>
            </div>

           <?php echo closeform(); ?>
        </div>

        <?php echo openform("weblink_table", "post", FUSION_REQUEST); ?>
        <?php echo form_hidden("table_action", "", ""); ?>

        <!-- Display Items -->
        <div class="display-block">
            <div class="display-inline-block m-l-10">
                <?php
                    echo form_select("weblink_display", $this->locale['WLS_0132'], $limit, array(
                        "width" => "100px", "options" => array(5 => 5, 10 => 10, 16 => 16, 25 => 25, 50 => 50, 100 => 100)
                    ));
                ?>
            </div>
            <?php if ($max_rows > $weblink_rows) : ?>
                <div class="display-inline-block pull-right">
                    <?php echo makepagenav($rowstart, $limit, $max_rows, 3, FUSION_SELF.fusion_get_aidlink()."&weblink_display=$limit&amp;") ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Display Table -->
        <div class="table-responsive"><table id="links-table" class="table table-striped">
            <thead>
            <tr>
                <th></th>
                <th class="strong"><?php echo $this->locale['WLS_0100'] ?></th>
                <th class="strong"><?php echo $this->locale['WLS_0101'] ?></th>
                <th class="strong"><?php echo $this->locale['WLS_0102'] ?></th>
                <th class="strong"><?php echo $this->locale['WLS_0103'] ?></th>
                <th class="strong"><?php echo $this->locale['language'] ?></th>
                <th class="strong"><?php echo $this->locale['WLS_0104'] ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (dbrows($result2) > 0) :
                while ($data = dbarray($result2)) : ?>
                    <?php
                    $cat_edit_link = clean_request("section=weblinks_category&ref=weblink_cat_form&action=edit&cat_id=".$data['weblink_cat_id'], array("section", "ref", "action", "cat_id"),     FALSE);
                    $edit_link     = clean_request("section=weblinks&ref=weblinkform&action=edit&weblink_id=".$data['weblink_id'],              array("section", "ref", "action", "weblink_id"), FALSE);
                    $delete_link   = clean_request("section=weblinks&ref=weblinkform&action=delete&weblink_id=".$data['weblink_id'],            array("section", "ref", "action", "weblink_id"), FALSE);
                    ?>
                    <tr id="link-<?php echo $data['weblink_id']; ?>" data-id="<?php echo $data['weblink_id']; ?>">
                        <td><?php echo form_checkbox("weblink_id[]", "", "", array("value" => $data['weblink_id'], "class" => "m-0", 'input_id' => 'link-id-'.$data['weblink_id'])) ?></td>
                        <td><span class="text-dark"><?php echo $data['weblink_name']; ?></span></td>
                        <td>
                            <a class="text-dark" href="<?php echo $cat_edit_link ?>">
                                <?php echo $data['weblink_cat_name']; ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge"><?php echo $data['weblink_status'] ? $this->locale['yes'] : $this->locale['no']; ?></span>
                        </td>
                        <td><span class="badge"><?php echo getgroupname($data['weblink_visibility']); ?></span></td>
                        <td><?php echo $data['weblink_language'] ?></td>
                        <td>
                            <a href="<?php echo $edit_link; ?>" title="<?php echo $this->locale['edit']; ?>"><?php echo $this->locale['edit']; ?></a>&nbsp;|&nbsp;
                            <a href="<?php echo $delete_link; ?>" title="<?php echo $this->locale['delete']; ?>" onclick="return confirm('<?php echo $this->locale['WLS_0111']; ?>')"><?php echo $this->locale['delete']; ?></a>
                        </td>
                    </tr>
                    <?php
                    add_to_jquery('$("#link-id-'.$data['weblink_id'].'").click(function() {
                        if ($(this).prop("checked")) {
                            $("#link-'.$data['weblink_id'].'").addClass("active");
                        } else {
                            $("#link-'.$data['weblink_id'].'").removeClass("active");
                        }
                    });');
                endwhile;
            ?><th colspan='7'><?php
                echo form_checkbox('check_all', $this->locale['WLS_0206'], '', array('class' => 'm-b-0', 'reverse_label'=>TRUE));

                add_to_jquery("
                    $('#check_all').bind('click', function() {
                        if ($(this).is(':checked')) {
                            $('input[name^=weblink_id]:checkbox').prop('checked', true);
                            $('#links-table tbody tr').addClass('active');
                        } else {
                            $('input[name^=weblink_id]:checkbox').prop('checked', false);
                            $('#links-table tbody tr').removeClass('active');
                        }
                    });
                ");
            ?></th>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center"><?php echo ($weblink_cats ? ($filter_empty ? $this->locale['WLS_0112'] : $this->locale['WLS_0113']) : $this->locale['WLS_0114']); ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table></div>
        <?php
        closeform();

        // jQuery
        add_to_jquery("
            // Toggle Filters
            $('#toggle_options').bind('click', function(e) {
                e.preventDefault();
                $('#weblink_filter_options').slideToggle();
                var caret_status = $('#filter_caret').hasClass('fa-caret-down');
                if (caret_status == 1) {
                    $('#filter_caret').removeClass('fa-caret-down').addClass('fa-caret-up');
                    $(this).removeClass('btn-default').addClass('btn-info');
                } else {
                    $('#filter_caret').removeClass('fa-caret-up').addClass('fa-caret-down');
                    $(this).removeClass('btn-info').addClass('btn-default');
                }
            });

            // Select Change
            $('#weblink_status, #weblink_visibility, #weblink_cat, #weblink_language, #weblink_display').bind('change', function(e){
                $(this).closest('form').submit();
            });
        ");

        // Javascript
        add_to_footer("
            <script type='text/javascript'>
                function run_admin(action) {
                    $('#table_action').val(action);
                    $('#weblink_table').submit();
                }
            </script>
        ");

    }


    private function verify_link($url) {

        if (function_exists('curl_version')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($httpcode >= 200 && $httpcode < 307 ? TRUE : FALSE);
        } elseif (filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) === FALSE) {
            return FALSE;
        }
    }

    private function verifyLink($id = 0) {
        $result = dbquery("
            SELECT * FROM ".DB_WEBLINKS." WHERE ".($id != 0 ? "weblink_id=".$id." AND " : "")."weblink_status='1'
        ");
        if (dbrows($result) > 0) {
            $i=0;
            while ($cdata = dbarray($result)) {
                if (self::verify_link($cdata['weblink_url']) == FALSE) {
                    dbquery("UPDATE ".DB_WEBLINKS." SET weblink_status='0' WHERE weblink_id='".intval($cdata['weblink_id'])."'");
                    $i++;
                }
            }
        addNotice("success", sprintf($this->locale['WLS_0115'], $i));
        $i > 0 ? addNotice("success", $this->locale['WLS_0116']) : "";
        }
    }

    // Weblinks Delete Function
    private function execute_Delete() {

        if (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['weblink_id']) && isnum($_GET['weblink_id'])) {
            $weblink_id = intval($_GET['weblink_id']);

            if (dbcount("(weblink_id)", DB_WEBLINKS, "weblink_id='$weblink_id'")) {
                dbquery("DELETE FROM ".DB_WEBLINKS." WHERE weblink_id='$weblink_id'");
                addNotice("success", $this->locale['WLS_0032']);
            }
            redirect(clean_request("", array("ref", "action", "cat_id"), FALSE));
        }
    }
}
