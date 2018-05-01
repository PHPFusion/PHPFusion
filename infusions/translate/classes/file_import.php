<?php
namespace Translate;

class File_Imports extends Administration {

    private static function format_import_values($string) {
        return stripinput(str_replace("\n", "\\n", $string));
    }

    public static function display_locale_import_form() {
        // using file id.
        // check existing file id.
        // upload the .php file src.
        // this will check conflicts and changes.
        // also check whether in ned of override values.
        // mark also as 3 if locale did not exist earlier.
        $sql = "
        SELECT pack.*, fo.* FROM ".DB_TRANSLATE_PACKAGE." pack
        INNER JOIN ".DB_TRANSLATE_FILES." fo ON fo.file_package=pack.package_id AND fo.file_id=:file_id         
        WHERE package_id=:id
        ";
        $bind = [
            ':file_id' => $_GET[self::$file_key],
            ':id'      => $_GET[self::$package_key]
        ];
        $result = dbquery($sql, $bind);
        if (dbrows($result)) {
            $data = dbarray($result);
            $file_path_input = '';
            $file_language_input = '';
            if (isset($_POST['import_file'])) {
                $file_path_input = form_sanitizer($_POST['file_path'], '', 'file_path');
                $file_language_input = form_sanitizer($_POST['file_language'], '', 'file_language');
                if (\defender::safe()) {
                    $file_path = ltrim($file_path_input, '/');
                    $file_language = $file_language_input;
                    if (file_exists(BASEDIR.$file_path)) {
                        if (is_dir(BASEDIR.$file_path)) {
                            \defender::stop();
                            addNotice('danger', 'The specified file path is a directory. Please specify a translations file only.');
                        } elseif (is_file(BASEDIR.$file_path)) {
                            include BASEDIR.$file_path;
                            // grab and parse into a temp array of all the possible locale keys existing in the same language.
                            // because it is an update, not a save.
                            $lang_sql = "SELECT translate_locale_key, translate_locale_value 
                            FROM ".DB_TRANSLATE." WHERE translate_language=:file_language AND translate_file_id=:file_id";
                            $lang_param = [
                                ':file_language' => $_GET['translate_lang'],
                                ':file_id'       => $data['file_id']
                            ];
                            $trans_result = dbquery($lang_sql, $lang_param);
                            $current_translations_array = [];
                            if (dbrows($trans_result)) {
                                while ($tData = dbarray($trans_result)) {
                                    $current_translations_array[$tData['translate_locale_key']] = $tData['translate_locale_value'];
                                }
                                print_p($current_translations_array);

                                if (valid_language($file_language)) {
                                    $count = 0;
                                    if (!empty($locale)) {
                                        // Store the file and generate an ID.
                                        if (stristr($file_path, '/')) {
                                            $file_path = explode('/', $file_path);
                                            $arr_count = count($file_path);
                                            $file_path = $file_path[$arr_count - 1];
                                        }
                                        // Do not create a file, instead do a locale inserts. We need to pair existing locale key
                                        // and then we insert the value only. and the message is as follows:
                                        foreach ($locale as $key => $value) {
                                            // bug fix on reading array value.
                                            // Do checks with toggler in the import form.
                                            if (is_array($value)) {
                                                $format_locale_key = self::get_arr_keys($value, $key);
                                                $format_locale_val = self::get_arr_values($value, $key);
                                                if (!empty($format_locale_key)) {
                                                    foreach($format_locale_key as $cnt => $formatted) {
                                                        $array_value = !empty($format_locale_val[$cnt]) ? $format_locale_val[$cnt] : '';
                                                        $localeData = [
                                                            'translate_id'           => 0,
                                                            'translate_file_id'      => $_GET[self::$file_key],
                                                            'translate_file_path'    => '',
                                                            'translate_locale_key'   => $formatted,
                                                            'translate_locale_value' => self::format_import_values($array_value),
                                                            'translate_message'      => self::$locale['translate_0201'],
                                                            'translate_datestamp'    => TIME,
                                                            'translate_language'     => $_GET['translate_lang']
                                                        ];
                                                        dbquery_insert(DB_TRANSLATE, $localeData, 'save');
                                                        $count++;
                                                    }
                                                }
                                            } else {
                                                $localeData = [
                                                    'translate_id'           => dbresult(dbquery("SELECT translate_id 
                                                                                FROM ".DB_TRANSLATE." WHERE translate_language=:lang AND translate_locale_key=:ekey",
                                                        [
                                                            ':lang'=> $file_language,
                                                            ':ekey'=>$key
                                                        ]),0),
                                                    'translate_file_id'      => $data['file_id'],
                                                    'translate_locale_key'   => $key,
                                                    'translate_locale_value' => self::format_import_values($value),
                                                    'translate_message'      => self::$locale['translate_0201'],
                                                    'translate_datestamp'    => TIME,
                                                    'translate_language'     => $file_language
                                                ];
                                                if (!empty($current_translations_array[$key])) {
                                                    if ($localeData['translate_id']) {
                                                        dbquery_insert(DB_TRANSLATE, $localeData, 'update', ['keep_session'=>TRUE]);
                                                    } else {
                                                        dbquery_insert(DB_TRANSLATE, $localeData, 'save', ['keep_session'=>TRUE]);
                                                    }
                                                    //print_p($current_translations_array[$key]);
                                                }
                                                $count++;
                                            }
                                        }
                                        unset($locale);
                                    }
                                    addNotice('success', 'File: '.$file_path.' imported with '.format_word($count, ''.$file_language.' translation|'.$file_language.' translations'));
                                    redirect(Translate_URI::get_link('view_translations', $_GET[self::$package_key], $_GET[self::$file_key]));
                                }

                            } else {
                                addNotice('danger', 'There are no translated result');
                            }
                        } else {
                            addNotice('danger', 'No file(s) found with the path. Please check or try with another path.');
                        }
                    } else {
                        \defender::stop();
                        addNotice('danger', 'No file(s) found with the path. Please check or try with another path.');
                    }
                }
            }

            // basic implementations, choose language and import.
            add_breadcrumb(['link' => Translate_URI::get_link('view_package', $data['package_id']), 'title' => $data['package_name']]);
            add_breadcrumb(['link' => Translate_URI::get_link('view_translations', $data['package_id'], $data['file_id']), 'title' => $data['file_name']]);
            add_breadcrumb(['link' => Translate_URI::get_link('upload_translations', $data['package_id'], $_GET[self::$file_key]), 'title' => self::$locale['translate_0202']]);
            opentable(self::$locale['translate_0100']);
            echo "<h4 class='text-normal'>Package: <span class='text-light'>".$data['package_name']."</span></h4>\n";
            echo "<hr/>\n";
            echo openform('upload_transFrm', 'post', FUSION_REQUEST, ['class' => 'well']);
            echo "<h4>Import other language translations to ".$data['file_name']."</h4>\n";
            echo "<p>Import the locales from an existing file in your server. Please specify a path to your locale file that is to be imported from.</p>\n";
            echo "<br/>";
            $language = self::get_package_language();
            // current language remove
            unset($language[$_GET['translate_lang']]);
            echo form_select('file_language', '', $file_language_input, ['placeholder' => 'Select Language', 'options' => $language]);
            echo form_text('file_path', '', $file_path_input, ['placeholder' => 'infusions/locale/English/admin_reset.php', 'inline' => TRUE,
                                                 'inner_width' => '400px',
                                                 'required'    => TRUE,
                                                 'stacked'     => form_button('import_file', 'Import File', 'save_file', ['class' => 'btn btn-success m-r-10'])." or ".form_button('cancel', 'Cancel', 'cancel', ['class' => 'btn btn-link p-0'])
            ]);
            echo closeform();
            closetable();
        }
    }

    private static function get_arr_keys($array, $parent = null) {
        static $result = array();
        if (is_array($array) * count($array) > 0)
        {
            foreach ($array as $key => $value) {

                self::get_arr_keys($value, "$parent|$key");
            }
        } else {
            $result[] = ltrim($parent, "");
        }
        return $result;
    }

    private static function get_arr_values($array, $parent = null) {
        static $result = array();
        if (is_array($array) * count($array) > 0)
        {
            foreach ($array as $key => $value) {
                self::get_arr_values($value, "$parent|$key");
            }
        } else {
            $result[] = ltrim($array, "");
        }
        return $result;
    }


    public static function display_import_form() {
        $sql = "SELECT * FROM ".DB_TRANSLATE_PACKAGE." WHERE package_id=:id";
        $bind = [':id' => $_GET[self::$file_key]]; // This is a trap - file key instead of package key to disallow bots matching
        $result = dbquery($sql, $bind);
        if (dbrows($result)) {
            $data = dbarray($result);
            $file_path_input = '';
            if (isset($_POST['import_file'])) {
                $file_path_input = form_sanitizer($_POST['file_path'], '', 'file_path');
                if (\defender::safe()) {
                    $file_path = ltrim($file_path_input, '/');
                    if (file_exists(BASEDIR.$file_path)) {
                        if (is_dir(BASEDIR.$file_path)) {
                            $file_root = BASEDIR.$file_path;
                            $file_list = makefilelist($file_root, '.|..|index.php', 'files', TRUE);
                            if (!empty($file_list)) {
                                foreach ($file_list as $file_name) {
                                    if (file_exists($file_root.$file_name)) {
                                        $_file_path = $file_root.$file_name;
                                        include $_file_path;
                                        $count = 0;
                                        if (!empty($locale)) {
                                            // Store the file and generate an ID.
                                            $fileData = [
                                                'file_id'        => 0,
                                                'file_path'      => '',
                                                'file_package'   => $data['package_id'],
                                                'file_name'      => $file_name,
                                                'file_language'  => $_GET['translate_lang'],
                                                'file_message'   => self::$locale['translate_0200'],
                                                'file_datestamp' => TIME,
                                                'file_status'    => 1,
                                            ];
                                            $file_id = dbquery_insert(DB_TRANSLATE_FILES, $fileData, 'save');
                                            foreach ($locale as $key => $value) {

                                                if (is_array($value)) {
                                                    $format_locale_key = self::get_arr_keys($value, $key);
                                                    $format_locale_val = self::get_arr_values($value, $key);
                                                    if (!empty($format_locale_key)) {
                                                        foreach($format_locale_key as $cnt => $formatted) {
                                                            $array_value = !empty($format_locale_val[$cnt]) ? $format_locale_val[$cnt] : '';
                                                            $localeData = [
                                                                'translate_id'           => 0,
                                                                'translate_file_id'      => $file_id,
                                                                'translate_file_path'    => '',
                                                                'translate_locale_key'   => $formatted,
                                                                'translate_locale_value' => self::format_import_values($array_value),
                                                                'translate_message'      => self::$locale['translate_0201'],
                                                                'translate_datestamp'    => TIME,
                                                                'translate_language'     => $_GET['translate_lang']
                                                            ];
                                                            dbquery_insert(DB_TRANSLATE, $localeData, 'save');
                                                            $count++;
                                                        }
                                                    }

                                                } else {
                                                    $localeData = [
                                                        'translate_id'           => 0,
                                                        'translate_file_id'      => $file_id,
                                                        'translate_file_path'    => '',
                                                        'translate_locale_key'   => $key,
                                                        'translate_locale_value' => self::format_import_values($value),
                                                        'translate_message'      => self::$locale['translate_0201'],
                                                        'translate_datestamp'    => TIME,
                                                        'translate_language'     => $_GET['translate_lang']
                                                    ];
                                                    dbquery_insert(DB_TRANSLATE, $localeData, 'save');
                                                    $count++;
                                                }
                                            }
                                            unset($locale);
                                        }
                                    } else {
                                        break;
                                        addNotice('danger', $file_path.' cannot be found or has run-time errors');
                                    }
                                }
                                addNotice('success', 'Filename: '.$file_name.' imported with '.format_word($count, 'translation|translations'));
                                redirect(Translate_URI::get_link('view_package', $_GET[self::$file_key]));
                            }
                        } elseif (is_file(BASEDIR.$file_path)) {
                            include BASEDIR.$file_path;
                            $count = 0;
                            if (!empty($locale)) {
                                // Store the file and generate an ID.
                                if (stristr($file_path, '/')) {
                                    $file_path = explode('/', $file_path);
                                    $arr_count = count($file_path);
                                    $file_path = $file_path[$arr_count - 1];
                                }
                                $fileData = [
                                    'file_id'        => 0,
                                    'file_path'      => $file_path,
                                    'file_package'   => $data['package_id'],
                                    'file_name'      => $file_path,
                                    'file_message'   => self::$locale['translate_0200'],
                                    'file_language'  => $_GET['translate_lang'],
                                    'file_datestamp' => TIME,
                                    'file_status'    => 1,
                                ];
                                $file_id = dbquery_insert(DB_TRANSLATE_FILES, $fileData, 'save');
                                foreach ($locale as $key => $value) {
                                    if (is_array($value)) {
                                        $format_locale_key = self::get_arr_keys($value, $key);
                                        $format_locale_val = self::get_arr_values($value, $key);
                                        if (!empty($format_locale_key)) {
                                            foreach($format_locale_key as $cnt => $formatted) {
                                                $array_value = !empty($format_locale_val[$cnt]) ? $format_locale_val[$cnt] : '';
                                                $localeData = [
                                                    'translate_id'           => 0,
                                                    'translate_file_id'      => $file_id,
                                                    'translate_file_path'    => '',
                                                    'translate_locale_key'   => $formatted,
                                                    'translate_locale_value' => self::format_import_values($array_value),
                                                    'translate_message'      => self::$locale['translate_0201'],
                                                    'translate_datestamp'    => TIME,
                                                    'translate_language'     => $_GET['translate_lang']
                                                ];
                                                dbquery_insert(DB_TRANSLATE, $localeData, 'save');
                                                $count++;
                                            }
                                        }
                                    } else {
                                        $localeData = [
                                            'translate_id'           => 0,
                                            'translate_file_id'      => $file_id,
                                            'translate_file_path'    => '',
                                            'translate_locale_key'   => $key,
                                            'translate_locale_value' => self::format_import_values($value),
                                            'translate_message'      => self::$locale['translate_0201'],
                                            'translate_datestamp'    => TIME,
                                            'translate_language'     => $_GET['translate_lang']
                                        ];
                                        dbquery_insert(DB_TRANSLATE, $localeData, 'save');
                                        $count++;
                                    }
                                }
                                unset($locale);
                            }
                            addNotice('success', 'Filename: '.$file_path.' imported with '.format_word($count, 'translation|translations'));
                            redirect(Translate_URI::get_link('view_package', $_GET[self::$file_key]));
                        }
                    } else {
                        \defender::stop();
                        addNotice('danger', 'No file(s) found with the path. Please check or try with another path.');
                    }
                }
            }
            add_breadcrumb(['link' => Translate_URI::get_link('view_package', $data['package_id']), 'title' => $data['package_name']]);
            add_breadcrumb(['link' => Translate_URI::get_link('upload_file', $data['package_id']), 'title' => 'Import']);
            opentable(self::$locale['translate_0100']);
            echo "<h4 class='text-normal'>Package: <span class='text-light'>".$data['package_name'].", ".parent::get_package_language($_GET['translate_lang'])."</span></h4>\n";
            echo "<hr/>\n";
            echo openform('upload_fileFrm', 'post', FUSION_REQUEST, ['class' => 'well']);
            echo "<h4>Import files from system</h4>\n";
            echo "<p>Import the locales from an existing file in your server. Please specify a path to your locale file that is to be imported from.</p>\n";
            echo "<br/>";
            echo form_text('file_path', '', $file_path_input, ['placeholder'   => 'infusions/locale/English/admin_reset.php', 'inline' => TRUE,
                                                 'inner_width'   => '400px',
                                                 'required'      => TRUE,
                                                 'prepend'       => TRUE,
                                                 'ext_tip'       => 'Switch system language to import as other language',
                                                 'prepend_value' => LANGUAGE.' locale',
                                                 'stacked'       =>
                                                     form_button('import_file', 'Import File', 'save_file', ['class' => 'btn btn-success m-r-10'])." or ".form_button('cancel', 'Cancel', 'cancel', ['class' => 'btn btn-link p-0'])
            ]);
            echo closeform();
            closetable();
        }
    }

}