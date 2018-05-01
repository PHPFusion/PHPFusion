<?php
namespace Translate\Pc;

use Translate\Administration;
use Translate\Translate_URI;

class Package_Administration extends Administration {

    public static function delete_package() {
        if (\defender::safe()) {
            $result = dbquery("SELECT file_id FROM ".DB_TRANSLATE_FILES." WHERE file_package=:package_id", [':package_id' => $_GET[self::$package_key]]);
            if (dbrows($result)) {
                while ($data = dbarray($result)) {
                    $file_param = [':file_id' => $data['file_id']];
                    dbquery("DELETE FROM ".DB_TRANSLATE." WHERE translate_file_id=:file_id", $file_param);
                }
                dbquery("DELETE FROM ".DB_TRANSLATE_FILES." WHERE file_id=:file_id", $file_param);
            }
            dbquery("DELETE FROM ".DB_TRANSLATE_PACKAGE." WHERE package_id=:package_id", [':package_id' => $_GET[self::$package_key]]);
            addNotice('success', 'Package Deleted');
            redirect(self::get_exit_link());
        }
    }

    public static function display_form() {

        if (isset($_POST['save_package'])) {
            self::$package_data = [
                'package_id' => form_sanitizer($_POST['package_id'], 0, 'package_id'),
                'package_name' => form_sanitizer($_POST['package_name'], '', 'package_name'),
                'package_meta' => form_sanitizer($_POST['package_meta'], '', 'package_meta'),
                'package_description' => form_sanitizer($_POST['package_description'], '', 'package_description'),
                'package_status' => isset($_POST['package_status']) ? 1 : 0,
                'package_datestamp' => TIME,
            ];
            if (\defender::safe()) {
                if (self::$package_data['package_id']) {
                    dbquery_insert(DB_TRANSLATE_PACKAGE, self::$package_data, 'update');
                } else {
                    dbquery_insert(DB_TRANSLATE_PACKAGE, self::$package_data, 'save');
                }
                redirect(clean_request());
            }
        }
        $edit = FALSE;
        if ($_GET[self::$action_key] == 'edit_package' && isset($_GET[self::$package_key]) && isnum($_GET[self::$package_key])) {
            $sql = "SELECT * FROM ".DB_TRANSLATE_PACKAGE." WHERE package_id=:package_id";
            $bind = [':package_id' => intval($_GET[self::$package_key])];
            $result = dbquery($sql, $bind);
            if (dbrows($result)) {
                $edit = TRUE;
                self::$package_data = dbarray($result);
            } else {
                redirect(clean_request(self::get_exit_link()));
            }
        }
        opentable(self::$locale['translate_0100']);
        add_breadcrumb(['link' => clean_request(),'title' => 'Create a Package']);
        echo "<h4>".($edit ? "Edit Package" : "Create a Package")."</h4>\n";
        echo "<p>A package contains all the locale translations for your project, including all system languages.</p>";
        echo "<hr/>\n";
        echo openform('package_frm', 'post', FUSION_REQUEST, ['class' => 'spacer-sm']);
        echo form_hidden('package_id', '', self::$package_data['package_id']);
        echo form_text('package_name', 'Package name', self::$package_data['package_name'],[
            'placeholder'=> 'Package Title',
            'inner_class' => 'input-lg',
            'inline' => FALSE, 'required' => TRUE
        ]);
        echo form_select('package_meta', 'Package Meta', self::$package_data['package_meta'], [
            'tags' => TRUE,
            'multiple' => TRUE,
            'inner_width' => '100%',
            'width' => '100%',
            'delimiter' => ','
        ]);
        echo form_text('package_description', 'Description', self::$package_data['package_description'], [
            'placeholder' => '(optional)',
            'inner_class' => 'input-lg',
            'inline' => FALSE,
        ]);
        echo "<hr/>\n";
        // Set as fork only available once we have more than 1 count.
        echo form_checkbox('package_status', 'Package Status:', self::$package_data['package_status'], [
                'type' => 'radio',
                'options' =>
                    [
                        1 => 'Open for Translations',
                        0 => 'Closed',
                    ],
                'inline' => TRUE
            ]
        );
        echo form_button('save_package', 'Create Package', 'save_package', ['class' => 'btn-success m-r-10']);
        echo form_button('cancel', 'Cancel', 'cancel', ['class'=>'btn-link']);
        echo closeform();
        closetable();
    }

    public static function display() {

        echo "<div class='clearfix'>\n";
        echo "<div class='pull-right'>\n";
        echo "<a class='btn btn-success' href='".Translate_URI::get_link('new_package')."'><i class='fa fa-code-fork fa-lg m-r-5'></i>".self::$locale['translate_0101']."</a>\n";
        echo "</div>\n";
        echo openform('package_search_frm', 'post', FUSION_REQUEST, ['inline'=>TRUE]);
        echo form_text('package_search_txt', '', '', [
            'placeholder'=>'Search Package',
            'inner_width' =>'350px',
            'class' => 'm-b-0',
        ]);
        echo form_button('search_server', 'Search', 'search_server');
        echo closeform();
        echo "</div>\n";
        echo "<hr/>\n";

        // you build package, list as english, and mark it as origin.
        // you need at least 1 package to be able to check as fork.
        // build package, list as Russian - mark as a fork.
        // build package, list as Swedish - mark as a fork.

        // now the files. we can see how many files in origin, and follow suit the file count.
        if (dbcount('(package_id)', DB_TRANSLATE_PACKAGE)) {
            /*
             *   echo "<thead>\n<tr>\n";
            echo "<th>Package</th>\n"; // displays the package subject.
            echo "<th>Total Files</th>\n"; // display how many language is available.
            echo "<th>Translations</th>\n"; // display how many language is available.
            echo "<th>Status</th>\n"; // display how many language is available.
            echo "<th>Package Last Updated</th>\n"; // display how many language is available.
            echo "<th></th>\n"; // display how many language is available.
            echo "</tr>\n</thead>\n<tbody>\n";
             */
            // no, use list
            // package description
            // package tags
            // status
            // language available and do analysis
            $sql = "SELECT pack.*, count(fo.file_id) 'sub_origin_files'
                FROM ".DB_TRANSLATE_PACKAGE." pack                 
                LEFT JOIN ".DB_TRANSLATE_FILES." fo ON fo.file_package=pack.package_id 
                GROUP BY pack.package_id ORDER BY pack.package_name ASC, pack.package_datestamp DESC
                ";
            $bind = [':root' => 0];
            $result = dbquery($sql, $bind);
            if (dbrows($result)) {
                while ($data = dbarray($result)) {
                    $meta = '';
                    if ($data['package_meta']) {
                        $meta = explode(',', $data['package_meta']);
                        $meta = array_map(function($e) {
                            return "<label class='label label-info m-r-10'><a href='".INFUSIONS."translate/translate_admin.php?meta=".$e."'>".$e."</a></label>\n";
                        }, $meta);
                        $meta = "<div class='meta'>".implode('',$meta)."</div>\n";

                    }
                    echo "<div class='clearfix'>";
                    echo "<div class='spacer-xs'>\n";
                    echo "<h4><a href='".Translate_URI::get_link('view_package', $data['package_id'])."'>".$data['package_name']."</a></h4>\n";
                    echo "<p>".$data['package_description']."</p>\n";
                    echo $meta;
                    echo "</div>\n";

                    echo "<div class='pull-right'>\n";
                    echo "<a class='btn btn-default' href='".Translate_URI::get_link('edit_package', $data['package_id'])."'>Edit</a> 
                    <a class='btn btn-default' href='".Translate_URI::get_link('delete_package', $data['package_id'])."'>Delete</a>\n";
                    echo "</div>\n";

                    echo "<div>\n";
                    echo "<span class='m-r-15'><i class='fa fa-dropbox fa-lg'></i> ".($data['package_status'] ? "Open for translations" : "Closed")."</span>";
                    echo "<span class='m-r-15'><i class='fa fa-file-archive-o fa-lg'></i> ".format_word($data['sub_origin_files'], 'file|files')."</span>\n";
                    echo "<small>Updated ".timer($data['package_datestamp'])."</small>\n";
                    echo "</div>\n";

                    echo "</div>\n";

                    echo "<hr/>\n";

                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>".self::$locale['translate_0106']."</td>";
            }
            echo "</tbody></table>\n";
        } else {
            echo "<div class='well'>".self::$locale['translate_0106']."</div>";
        }
    }


}