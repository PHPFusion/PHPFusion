<?php
namespace Translate\Fo;

use PHPFusion\httpdownload;
use PHPFusion\Locale;
use Translate\Administration;
use Translate\Translate_URI;
use Yandex\Translate\Exception;
use Yandex\Translate\Translator;

class File_Administration extends Administration {

    public static function display_form() {

        opentable(self::$locale['translate_0100']);
        echo "<h4>Create a new file</h4>\n";
        echo "<p>Paste the entire locale code here.</p>\n";
        echo "<hr/>\n";
        echo openform('new_fileFrm', 'post', FUSION_REQUEST);
        echo form_text('file_path', '', '', ['placeholder' => '/locale/English/admin_reset.php', 'inline' => TRUE,
                                             'inner_width' => '50%',
                                             'stacked'     =>
                                             form_button('save_file', 'Save File', 'save_file', ['class' => 'btn btn-success m-r-10'])." or ".form_button('cancel', 'Cancel', 'cancel', ['class' => 'btn btn-link p-0'])
        ]);
        echo form_textarea('file_text', '', '', ['type' => 'tinymce', 'auto_size' => TRUE, 'required' => TRUE, 'required' => TRUE, 'placeholder' => 'Copy and paste locale codes here']);
        echo form_button('save_file', 'Save File', 'save_file', ['class' => 'btn btn-success m-r-10', 'input_id' => 'save_2']);
        echo closeform();
        closetable();
    }

    public static function cleanup_translations() {
        $all_sql = "SELECT tr.translate_locale_key, tr.translate_id 
        FROM ".DB_TRANSLATE." tr          
        WHERE tr.translate_file_id=:file_id AND tr.translate_language=:origin_language";
        $all_params = [
            ':file_id' => $_GET[self::$file_key],
            ':origin_language'=> $_GET['translate_lang'],
        ];
        $result = dbquery($all_sql, $all_params);
        if (dbrows($result)) {
            $origin_keys = [];
            while ($data = dbarray($result)) {
                $origin_keys[$data['translate_locale_key']] = $data['translate_id']; // these are the translated malays.
            }
        }
        if (!empty($origin_keys)) {
            $all_keys = [];
            // load all.
            $all_params = [
                ':file_id' => $_GET[self::$file_key],
                ':translate_to'=> $_GET['translate_to'],
            ];
            $load_all = "SELECT tr.translate_id, tr.translate_locale_key 
            FROM ".DB_TRANSLATE." tr              
            WHERE tr.translate_file_id=:file_id AND tr.translate_language=:translate_to";
            $sql = dbquery($load_all, $all_params);
            if (dbrows($sql)) {
                while ($data = dbarray($result)) {
                    $all_keys[$data['translate_locale_key']] = $data['translate_id'];
                }
            }
            $diff = array_diff(array_keys($all_keys), array_keys($origin_keys));
            //print_p($diff);
            //redirect( clean_request('', ['cleanup'], FALSE));
        }

    }

    public static function delete_file() {
        $del_param = [
            ':file_id' => $_GET[self::$file_key]
        ];
        if (dbcount('(file_id)', DB_TRANSLATE_FILES, 'file_id=:file_id', $del_param) && iSUPERADMIN) {
            $translations_count = dbcount('(translate_id)', DB_TRANSLATE, 'translate_file_id=:file_id', $del_param);
            if (isset($_POST['confirm_del']) || empty($translations_count)) {
                if (isset($_POST['confirm_del'])) {
                    $filename_ = form_sanitizer($_POST['file_name'], '', 'file_name');
                    $package_ = form_sanitizer($_POST['file_package'], '', 'file_package');
                    if (!dbcount('(file_id)', DB_TRANSLATE_FILES, 'file_id=:file_id AND file_name=:file_name', $del_param + [':file_name' => $filename_])) {
                        addNotice('danger', 'The specified file name is incorrect. Please try again.');
                        \defender::stop();
                    }
                }
                if (\defender::safe()) {
                    dbquery("DELETE FROM ".DB_TRANSLATE." WHERE translate_file_id=:file_id", $del_param);
                    dbquery("DELETE FROM ".DB_TRANSLATE_FILES." WHERE file_id=:file_id", $del_param);
                    addNotice('success', 'File deleted');
                    redirect(Translate_URI::get_link('view_translations', $package_, $_GET[self::$file_key]));
                }
            } else {
                // now i need to check the package.
                $file_package = dbresult(dbquery("SELECT file_package FROM ".DB_TRANSLATE_FILES." WHERE file_id=:file_id", $del_param), 0);
                opentable('Are you ABSOLUTELY sure?');
                echo "<div class='alert alert-danger'>Unexpected bad things will happen if you don’t read this!</div>\n";
                echo "<p>This action CANNOT be undone. This will permanently delete <strong>".format_word($translations_count, 'translation|translations')."</strong> in the package for <strong>all languages</strong>.</p>";
                echo "<p>Please type in the name of the file, <strong>\"".dbresult(dbquery("SELECT file_name FROM ".DB_TRANSLATE_FILES." WHERE file_id=:file_id", $del_param), 0)."\"</strong> to confirm.</p>";
                echo openform('confirmDelFileFrm', 'post', FUSION_REQUEST);
                echo form_hidden('file_package', '', $file_package);
                echo form_text('file_name', '', '', ['required' => TRUE]);
                echo form_button('confirm_del', 'I understand the consequence, delete this file', 'confirm_del');
                echo closeform();
                closetable();
            }
        } else {
            addNotice('danger', 'File ID error');
            redirect( clean_request('', ['action'], FALSE) );
        }
    }

    // bind a tree into the file structure for folder.
    private static function get_dir_header() {
        $html = '';
        // always unset last key
        $last_key = max(array_keys(self::$header_link));
        foreach (self::$header_link as $crumb_keys => $crumb) {
            $html .= ($crumb['link'] && $crumb_keys !== $last_key) ? "<a title='".$crumb['title']."' href='".$crumb['link']."'>".$crumb['title']."</a> /" : $crumb['title'];
        }

        return $html;
    }

    // reflect last state of locale translations file.
    // require lang
    private static function check_download_request() {
        $response = FALSE;
        $http_result = dbquery("SELECT fo.file_name, fo.file_datestamp, pack.package_name FROM ".DB_TRANSLATE_FILES." fo
         INNER JOIN ".DB_TRANSLATE_PACKAGE." pack ON fo.file_package=pack.package_id          
         WHERE file_id=:file_id", [':file_id' => $_GET[self::$file_key]]);
        if (dbrows($http_result)) {
            $http_data = dbarray($http_result);
            // build the locale
            $sql = "SELECT translate_locale_key, translate_locale_value FROM ".DB_TRANSLATE."             
                WHERE translate_language=:language AND translate_file_id=:file_id 
                ORDER BY translate_locale_key ASC
                ";
            $param = [
                ':language' => $_GET['language'],
                ':file_id'  => $_GET[self::$file_key]
            ];
            $result = dbquery($sql, $param);
            if (dbrows($result)) {
                // use the header link
                $file_ = "<?php".PHP_EOL;
                $file_ .= "/*-------------------------------------------------------+".PHP_EOL;
                $file_ .= "| PHP-Fusion Content Management System".PHP_EOL;
                $file_ .= "| Copyright (C) PHP-Fusion Inc".PHP_EOL;
                $file_ .= "| https://www.php-fusion.co.uk/".PHP_EOL;
                $file_ .= "+--------------------------------------------------------+".PHP_EOL;
                $file_ .= "| Filename: ".$http_data['file_name'].PHP_EOL;
                $file_ .= "| Author: PHP-Fusion Translations Server".PHP_EOL;
                $file_ .= "| Last updated: ".date('M d Y', $http_data['file_datestamp']).PHP_EOL;
                $file_ .= "+--------------------------------------------------------+".PHP_EOL;
                $file_ .= "| This program is released as free software under the".PHP_EOL;
                $file_ .= "| Affero GPL license. You can redistribute it and/or".PHP_EOL;
                $file_ .= "| modify it under the terms of this license which you".PHP_EOL;
                $file_ .= "| can read by viewing the included agpl.txt or online".PHP_EOL;
                $file_ .= "| at www.gnu.org/licenses/agpl.html. Removal of this".PHP_EOL;
                $file_ .= "| copyright header is strictly prohibited without".PHP_EOL;
                $file_ .= "| written permission from the original author(s).".PHP_EOL;
                $file_ .= "+--------------------------------------------------------*/".PHP_EOL;
                $file_ .= PHP_EOL;
                while ($data = dbarray($result)) {
                    if (stristr($data['translate_locale_key'], '|')) {
                        $exp_ = explode('|', $data['translate_locale_key']);
                        $file_ .= '$locale';
                        foreach($exp_ as $locale_keys) {
                            $file_ .= '[\''.$locale_keys.'\']';
                        }
                        $file_ .= ' = "'.$data['translate_locale_value'].'";'.PHP_EOL;
                    } else {
                        $file_ .= '$locale[\''.$data['translate_locale_key'].'\'] = "'.$data['translate_locale_value'].'";'.PHP_EOL;
                    }
                }

                // lets determine the current download language.
                $file_name_ = str_replace(['.php', '.js', '.inc'], [], $http_data['file_name']);
                if (valid_language($file_name_)) {
                    $folder_path = INFUSIONS.'translate/translations/'.strtolower($http_data['package_name']).'/locale/';
                    $file_path = $_GET['language'].'.php';
                } else {
                    $folder_path = INFUSIONS.'translate/translations/'.strtolower($http_data['package_name']).'/locale/'.$_GET['language'].'/';
                    $file_path = $http_data['file_name'];
                }
                if (!file_exists($folder_path)) {
                    @mkdir($folder_path, 777, TRUE);
                }
                $file_path = $folder_path.$file_path;
                write_file($file_path, $file_);
                require_once INCLUDES."class.httpdownload.php";
                $object = new httpdownload();
                $object->set_byfile($file_path);
                $object->use_resume = TRUE;
                $object->download();
                exit;
                $response = TRUE;
            }
        }

        return $response;
    }

    private static function check_actions() {
        if (isset($_GET['cleanup']) && isset($_GET[self::$file_key]) && $_GET['translate_lang'] != $_GET['translate_to']) {
            self::cleanup_translations();
        }
        // Add locale key
        elseif (isset($_GET['reg_key']) && isnum($_GET['reg_key']) && \defender::safe()) {
            if (isset($_GET[self::$file_key]) && isnum($_GET[self::$file_key])) {
                $bind = [
                    ':id' => $_GET['reg_key'], // this is locale key.
                    ':file_id' => $_GET[self::$file_key],
                ];
                if (dbcount("(translate_id)", DB_TRANSLATE, "translate_id=:id AND translate_file_id=:file_id", $bind)) {
                    dbquery("UPDATE ".DB_TRANSLATE." SET translate_status=:zero WHERE translate_id=:id", [':zero'=>0, ':id'=>$_GET['reg_key']]);
                    redirect( clean_request('', ['reg_key'], FALSE) );
                }
            } else {
                redirect( clean_request('', ['reg_key'], FALSE) );
            }
        }
        // localex API.
        elseif (isset($_GET['localex']) && isnum($_GET['localex']) && isset($_GET['translate_lang']) && isset($_GET['translate_to']) && isset($_GET[self::$file_key]) && isnum($_GET[self::$file_key])) {
            if (isset($_POST['cancel_translations'])){
                redirect( clean_request("", ['localex'], FALSE) );
            }
            if (isset($_POST['accept_translations'])) {
                if (isset($_POST['translate_choice'])) {
                    $choice_id = form_sanitizer($_POST['translate_choice'], '', 'translate_choice');
                } else {
                    \defender::stop();
                    \defender::setInputError('translate_choice');
                }
                if (\defender::safe()) {
                    $find_sql = "SELECT translate_id, translate_locale_value FROM ".DB_TRANSLATE." WHERE translate_id=:choice_id";
                    $find_param = [':choice_id' => $choice_id];
                    $find_result = dbquery($find_sql, $find_param);
                    if (dbrows($find_result)) {
                        $finderData = dbarray($find_result);
                        // get current locale key
                        $locale_key_sql = "SELECT translate_id, translate_locale_key FROM ".DB_TRANSLATE." WHERE translate_id=:id";
                        $locale_key_params = [':id' => $_GET['localex']];
                        $locale_result = dbquery($locale_key_sql, $locale_key_params);
                        if (dbrows($locale_result)) {
                            $tData = dbarray($locale_result);
                            $translateData = [
                                'translate_id'           => 0,
                                'translate_language'     => $_GET['translate_to'],
                                'translate_file_id'      => $_GET[self::$file_key],
                                'translate_locale_key'   => $tData['translate_locale_key'],
                                'translate_locale_value' => $finderData['translate_locale_value'],
                                'translated_message'     => 'Copied from Translations ID '.$tData['translate_id'],
                                'translate_datestamp'    => TIME
                            ];
                            if (\defender::safe()) {
                                dbquery_insert(DB_TRANSLATE, $translateData, 'save');
                                $id = addNotice('success', 'Translations Copied.');
                                redirect(clean_request('', ['localex'], FALSE).'#l_'.$id);
                            }
                        } else {
                            // redirect because locale key not found from ur/.
                            addNotice('danger', 'Locale key error.');
                            redirect( clean_request('', ['localex'], FALSE));
                        }
                    } else {
                        addNotice('danger', 'No identical previous translations can be found.');
                        redirect( clean_request('', ['localex'], FALSE));
                    }
                }
            }

            $sql = "SELECT tr2.translate_id, tr2.translate_file_id, tr2.translate_locale_key, tr2.translate_locale_value 
            FROM ".DB_TRANSLATE." tr1
            INNER JOIN ".DB_TRANSLATE." tr2 ON tr2.translate_locale_value=tr1.translate_locale_value
            WHERE tr1.translate_id=:localex";
            $param = [':localex' => $_GET['localex']];
            $result = dbquery($sql, $param);
            $_suggestions = [];
            if (dbrows($result)) {
                // Search for suggestions
                while ($data = dbarray($result)) {
                    $searching_for = $data['translate_locale_value'];
                    // now find any translations of the locale_key in the file_id of the same locale_key in BM and present me the locale value.
                    $cSql = "SELECT translate_id, translate_locale_key, translate_locale_value
                    FROM ".DB_TRANSLATE." WHERE translate_locale_key=:locale_key AND translate_file_id=:file_id AND translate_language=:language";
                    $cParam = [
                        ':locale_key' => $data['translate_locale_key'],
                        ':file_id' => $data['translate_file_id'],
                        ':language' => $_GET['translate_to']
                    ];
                    $c_result = dbquery($cSql, $cParam);
                    if (dbrows($c_result)) {
                        while ($cData = dbarray($c_result)) {
                            $_suggestions[$cData['translate_locale_value']] = $cData['translate_id'];
                        }
                        $suggestions = array_flip($_suggestions);
                    }
                }
                if (!empty($suggestions)) {
                    $modal = openmodal('suggest_modal', format_word(count($suggestions), 'translation suggestion|translation suggestions').' found', ['static'=>TRUE, 'class'=>'modal-lg']);
                    $modal .= openform('localex_frm', 'post', FUSION_REQUEST);
                    $modal .= "<h4>Phrase: ".$searching_for."</h4>\n";
                    $modal .= "<p>Choose an acceptable translations from previous translations of the same value for <strong>".self::get_package_language($_GET['translate_to'])."</strong>:</p>\n";
                    $modal .= form_checkbox('translate_choice', '', '', [
                        'options' => $suggestions,
                        'type' => 'radio',
                        'required' => TRUE,
                    ]);
                    $modal .= form_button('accept_translations', 'Accept as Translations', 'accept_translations', ['class'=>'btn-success']);
                    $modal .= form_button('cancel_translations', 'Cancel', 'cancel', ['class'=>'btn-link']);
                    $modal .= closemodal();
                    $modal .= closemodal();
                    add_to_footer($modal);
                } else {
                    addNotice('success', 'No translated values for this language can be found');
                    redirect( clean_request('', ['localex'], FALSE) );
                }
            } else {
                addNotice('success', 'No identical translations found');
                redirect( clean_request('', ['localex'], FALSE) );
            }
        }
        // yandex API.
        elseif (isset($_GET['yandex']) && isnum($_GET['yandex']) && isset($_GET['translate_lang']) && isset($_GET['translate_to']) && isset($_GET[self::$file_key]) && isnum($_GET[self::$file_key])) {
            $sql = "SELECT translate_locale_key, translate_locale_value FROM ".DB_TRANSLATE." WHERE translate_id=:yandex";
            $param = [':yandex' => $_GET['yandex']];
            $result = dbquery($sql, $param);
            if (dbrows($result)) {
                $data = dbarray($result);
                $translate_from_iso = Locale::get_iso($_GET['translate_lang'], FALSE);
                $translate_to_iso = Locale::get_iso($_GET['translate_to'], FALSE);
                $translated = self::request_yandex($data['translate_locale_value'], $translate_from_iso, $translate_to_iso);
                // can make append to use a modal in translation settings later, but we do not need now because I need translations now.
                // for confirmation and choices for multiple translations results.
                $translateData = [
                    'translate_id'           => 0,
                    'translate_language'     => $_GET['translate_to'],
                    'translate_file_id'      => $_GET[self::$file_key],
                    'translate_locale_key'   => $data['translate_locale_key'],
                    'translate_locale_value' => stripinput($translated),
                    'translated_message'     => 'Translated from Online Source',
                    'translate_datestamp'    => TIME
                ];
                if (\defender::safe()) {
                    $id = dbquery_insert(DB_TRANSLATE, $translateData, 'save');
                    addNotice('success', 'Yandex has successfully translated the text. Please check the translations accordingly.');
                    redirect(clean_request('', ['yandex'], FALSE).'#l_'.$id);
                }
            }
        }
        elseif (isset($_GET['download_file']) && isset($_GET['language']) && isset($_GET[self::$file_key]) && isnum($_GET[self::$file_key])) {
            $response = self::check_download_request();
            if ($response == true) {
                redirect(clean_request('', array("download_file", "language"), false));
            }
        } elseif (isset($_GET['scan']) && isset($_GET[self::$file_key]) && isnum($_GET[self::$file_key])) {
            $duplicate_status = 2;
            $param = [':file_id' => $_GET[self::$file_key], ':lang' => $_GET['translate_lang'], ':file_status' => $duplicate_status];
            $key_locale = '';
            switch ($_GET['scan']) {
                case 'keys':
                    $alert = [];
                    $list = [];
                    $select = "SELECT translate_locale_key FROM ".DB_TRANSLATE." WHERE translate_file_id=:file_id AND translate_language=:lang AND translate_status !=:file_status";
                    $result = dbquery($select, $param);
                    if (dbrows($result)) {
                        while ($data = dbarray($result)) {
                            if (isset($list[$data['translate_locale_key']])) {
                                $alert[] = [
                                    'translate_locale_key' => $data['translate_locale_key'],
                                    'translate_message'    => 'There is a duplicate of this array key in previous array key #'.$list[$data['translate_locale_value']],
                                ];
                            } else {
                                $list[$data['translate_locale_key']] = $data['translate_locale_key'];
                            }
                        }
                    }
                    $key_locale = 'duplicated locale keys';
                    break;
                case 'values':
                    $alert = [];
                    $list = [];
                    $select = "SELECT translate_locale_key, translate_locale_value FROM ".DB_TRANSLATE." WHERE translate_file_id=:file_id AND translate_language=:lang AND translate_status !=:file_status ORDER BY translate_locale_key ASC";
                    $result = dbquery($select, $param);
                    if (dbrows($result)) {
                        while ($data = dbarray($result)) {
                            if (isset($list[$data['translate_locale_value']])) {
                                $alert[] = [
                                    'translate_locale_key' => $data['translate_locale_key'],
                                    'translate_message'    => 'There is a duplicate of this array value in previous array key #'.$list[$data['translate_locale_value']],
                                ];
                            } else {
                                $list[$data['translate_locale_value']] = $data['translate_locale_key'];
                            }
                        }
                    }
                    $key_locale = 'duplicated locale values';
                    break;
                default:
                    redirect(clean_request('', ['scan'], FALSE));
            }
            if (count($alert)) {
                foreach ($alert as $error_data) {
                    dbquery("UPDATE ".DB_TRANSLATE." SET translate_status=:status, translate_message=:message WHERE translate_locale_key=:locale_key AND translate_file_id=:file_id", [
                        ':status'     => 2,
                        ':locale_key' => $error_data['translate_locale_key'],
                        ':message'    => $error_data['translate_message'],
                        ':file_id'    => $_GET[self::$file_key]
                    ]);
                }
                addNotice('warning', "Scan found ".count($alert)." ".$key_locale);
            } else {
                addNotice('success', "There are no problems found");
            }
            redirect(clean_request('', ['scan'], FALSE));
        } elseif (isset($_POST['add_key'])) {
            $translateData = [
                'translate_id'           => 0,
                'translate_locale_key'   => form_sanitizer($_POST['translate_locale_key'], '', 'translate_locale_key'),
                'translate_file_id'      => $_GET[self::$file_key],
                'translate_locale_value' => '',
                'translate_message'      => 'Locale key created',
                'translate_language'     => LANGUAGE,
                'translate_datestamp'    => TIME,
            ];
            if (dbcount('(translate_id)', DB_TRANSLATE, 'translate_locale_key=:locale_key AND translate_language=:language AND translate_file_id=:file_id',
                [
                    ':locale_key' => $translateData['translate_locale_key'],
                    ':language'   => LANGUAGE,
                    ':file_id'    => $_GET[self::$file_key]
                ])) {
                \defender::stop();
                addNotice('danger', 'The locale key already exists. Please try with a different locale key');
            }
            if (\defender::safe()) {
                dbquery_insert(DB_TRANSLATE, $translateData, 'save');
                addNotice('success', 'New locale key created');
                redirect(FUSION_REQUEST);
            }
        } elseif (isset($_POST['save_translations'])) {
            $translateData = [
                'translate_id'           => isnum($_POST['translate_id']) && !empty($_POST['translate_id']) ? intval($_POST['translate_id']) : 0,
                'translate_locale_key'   => stripinput($_POST['save_translations']),
                'translate_locale_value' => stripinput(array_values($_POST['translate_locale_value']))[0],
                'translate_file_id'      => $_GET[self::$file_key],
                'translate_message'      => 'Translations Updated',
                'translate_language'     => stripinput(array_keys($_POST['translate_locale_value']))[0],
                'translate_datestamp'    => TIME,
            ];
            if (!$translateData['translate_id']) {
                $translateData['translate_message'] = 'Locale translations created';
            }
            if ($translateData['translate_id']) {
                dbquery_insert(DB_TRANSLATE, $translateData, 'update');
                addNotice('success', 'Translations updated');
            } else {
                $translateData['translate_id'] = dbquery_insert(DB_TRANSLATE, $translateData, 'save');
                addNotice('success', 'Translations created');
            }
            redirect(FUSION_REQUEST."#l_".$translateData['translate_id']);
        } elseif (isset($_GET['del_key'])) {
            $delete_key = stripinput($_GET['del_key']);
            $delete_param = [
                ':locale_key' => $delete_key,
                ':file_id'    => $_GET[self::$file_key]
            ];
            if (dbcount("(translate_id)", DB_TRANSLATE, "translate_locale_key=:locale_key AND translate_file_id=:file_id", $delete_param)) {
                dbquery("DELETE FROM ".DB_TRANSLATE." WHERE translate_locale_key=:locale_key AND translate_file_id=:file_id", $delete_param);
                addNotice('success', 'Locale key deleted from the package');
                redirect(clean_request('', ['del_key'], FALSE));
            }
        }
    }

    private static function request_yandex($text, $from_lang, $to_lang) {
        if (defined('YANDEX_KEY')) {
            try {
                $translator = new Translator(YANDEX_KEY);
                $translation = $translator->translate($text, "$from_lang-$to_lang");

                return $translation;
            } catch (Exception $e) {
                addNotice('danger', $e);
            }
        }
    }

    public static function display() {


        $package_bind = [':id' => $_GET[self::$package_key]];
        if (dbcount('(package_id)', DB_TRANSLATE_PACKAGE, "package_id=:id", $package_bind)) {
            add_to_head("<style>
            .hero_label {
                width: 80%;
            }
            .btn-primary-inverse {
                float:left !important;
                display: inline-block !important;
                padding: 15px 0 !important;
                text-align: center !important;
                border: 0 !important;
                color: #0366d6;
                background-color: #fff;
                font-size: 15px;
                font-weight: 500;
                line-height: 20px;
            }
            .btn-primary-inverse:not(:first-child) {
                border-left: 1px solid #e1e4e8 !important;
            }
            .btn-primary-inverse:hover,
            .btn-primary-inverse:focus,
            .btn-primary-inverse:active {
                color: #fff;
                text-decoration: none;
                background-color: #0366d6;
                background-image: none;
            }
            .btn-primary-inverse:active {                
                background-image: none;
                border-color: rgba(27,31,35,0.35);
                box-shadow: inset 0 0.15em 0.3em rgba(27,31,35,0.15);
            }   
            </style>");
            self::check_actions();
            echo "<h4 class='text-normal'>Package: <span class='text-light'>".self::get_dir_header()."</span></h4>\n";
            echo "<div class='panel panel-default'>\n";
            echo "<div class='panel-heading p-5 p-l-15 p-r-15 clearfix'>\n";
            //if (isset($_GET[self::$file_key])) {
                echo openform('trls', 'post', FUSION_REQUEST, ['class' => 'display-inline-block m-r-10', 'inline' => TRUE]);
                echo "<div class='display-inline-block' style='margin-bottom:-10px;'>\n";
                echo form_select('translate_lang', '', $_GET['translate_lang'], [
                    'inner_width' => '150px',
                    'inline'      => TRUE,
                    'class'       => 'display-inline',
                    'options'     => parent::get_package_language(),
                ]);
                echo "</div>\n";
                echo "<div class='display-inline-block' style='margin-bottom:-10px; width: 30px; text-align:center'>\n";
                echo "<i class='fa fa-angle-right'></i>";
                echo "</div>\n";
                echo "<div class='display-inline-block' style='margin-bottom:-10px;'>\n";
                echo form_select('translate_to', '', $_GET['translate_to'], [
                    'inner_width' => '150px',
                    'inline'      => TRUE,
                    'class'       => 'display-inline',
                    'options'     => parent::get_package_language(),
                ]);
                echo "</div>\n";
                echo form_button('translate', 'Translate', 'translate', ['class' => 'btn-primary']);
                echo closeform();
            //}

            echo "<div class='btn-group pull-right m-l-10'>\n";
            echo "<a class='btn btn-default' href='".Translate_URI::get_link('new_file', $_GET[self::$package_key])."'>".self::$locale['translate_0107']."</a>\n";
            echo "<a class='btn btn-default' href='".Translate_URI::get_link('upload_file', $_GET[self::$package_key])."'>".self::$locale['translate_0108']."</a>\n";
            if (isset($_GET[self::$file_key])) {
                echo "<a class='btn btn-default' href='".Translate_URI::get_link('upload_translations', $_GET[self::$package_key], $_GET[self::$file_key])."'>".self::$locale['translate_0202']."</a>\n";
            }
            echo "</div>\n";
            echo "<div class='dropdown pull-right m-l-10'>\n";
            echo "<a class='btn btn-success dropdown-toggle text-white' data-toggle='dropdown' href='".Translate_URI::get_link('download_pack', $_GET[self::$package_key])."'>".self::$locale['translate_0109']." <span class='fa fa-angle-down'></a>\n";
            echo "<ul class='dropdown-menu' style='min-width: 350px; padding:0;'>\n";
            echo "<li class='p-10'><h4 style='margin: 10px 0; font-weight: 400; font-size: 19px;'>Build and Download Translations</h4>
            <span>Files will automatically be built and download with the latest translations.</span>
            </li>\n";
            echo "<li style='border-top:1px solid #ccc'>\n";
            if (isset($_GET[self::$file_key]) && isnum($_GET[self::$file_key])) {
                echo "<div class='btn-choice'>\n";
                echo "<a href='".Translate_URI::get_link('download_file', $_GET[self::$package_key], $_GET[self::$file_key], $_GET['translate_lang'])."' class='btn-primary-inverse' style='width:50%;'>Download ".self::get_package_language($_GET['translate_lang'])."</a>\n";
                if ($_GET['translate_to'] != $_GET['translate_lang']) {
                    echo "<a href='".Translate_URI::get_link('download_file', $_GET[self::$package_key], $_GET[self::$file_key], $_GET['translate_to'])."' class='btn-primary-inverse' style='width:50%; border-left:1px solid #eee;'>Download ".self::get_package_language($_GET['translate_to'])."</a>\n";
                }
                echo "</div>\n";
            } else {
                echo "<div class='btn-choice'>\n";
                echo "<a href='".Translate_URI::get_link('download_package', $_GET[self::$package_key])."' class='btn-primary-inverse'>Download Package</a>\n";
                echo "</div>\n";
            }
            echo "</li>\n";
            echo "</ul>\n";
            echo "</div>\n";
            echo "</div>\n";

            $ipp = 50;
            if ((isset($_GET[self::$file_key]) && isnum($_GET[self::$file_key]))) {
                $translations_rows = dbcount("(translate_id)", DB_TRANSLATE, "translate_file_id=:file_id AND translate_language=:lang", [
                        ':file_id' => $_GET[self::$file_key],
                        ':lang'    => $_GET['translate_lang']]
                );
                $_GET['file_rows'] = isset($_GET['file_rows']) && isnum($_GET['file_rows']) && $_GET['file_rows'] <= $translations_rows ? intval($_GET['file_rows']) : 0;
                if ($translations_rows) {
                    $sql = "SELECT tr.*, 
                        ts.translate_id 'translated_id',
                        ts.translate_locale_key 'translated_locale_key',
                        ts.translate_locale_value 'translated_locale_value',
                        ts.translate_datestamp 'translated_datestamp',
                        ts.translate_status 'translated_status'
                        FROM ".DB_TRANSLATE." tr 
                        INNER JOIN ".DB_TRANSLATE_FILES." fo ON fo.file_id=tr.translate_file_id
                        LEFT JOIN ".DB_TRANSLATE." ts ON ts.translate_locale_key=tr.translate_locale_key AND ts.translate_language=:ts_language AND ts.translate_file_id=:file_id2
                        WHERE fo.file_package=:id AND fo.file_id=:file_id AND tr.translate_language=:language
                        GROUP BY tr.translate_locale_key, ts.translate_locale_key 
                        ORDER BY tr.translate_locale_key ASC
                        LIMIT :rowstart, :ipp
                        ";
                    $sql_param = [
                        ':id'          => $_GET[self::$package_key],
                        ':file_id'     => $_GET[self::$file_key],
                        ':file_id2'    => $_GET[self::$file_key],
                        ':language'    => $_GET['translate_lang'],
                        ':rowstart'    => $_GET['file_rows'],
                        ':ipp'         => $ipp,
                        ':ts_language' => $_GET['translate_to']
                    ];
                    $result = dbquery($sql, $sql_param);
                    $rows = dbrows($result);

                    echo "<div class='panel-body br-0'>\n";
                    echo "<div class='btn-group pull-left m-r-15'>\n";
                    echo "<a href='".clean_request('scan=keys', ['scan'], FALSE)."' class='btn btn-default'>Scan Keys</a>\n";
                    echo "<a href='".clean_request('scan=values', ['scan'], FALSE)."' class='btn btn-default'>Scan Values</a>\n";
                    echo "<a href='".clean_request('cleanup=1', ['scan'], FALSE)."' class='btn btn-default'>Cleanup Translations</a>\n";
                    echo "</div>\n";

                    echo openform('trll', 'post', FUSION_REQUEST, ['class' => 'display-inline-block m-r-15']);
                    echo form_text('translate_locale_key', '', '', [
                        'placeholder'        => 'Add new locale key to the file',
                        'required'           => TRUE,
                        'append'             => TRUE,
                        'append_button'      => TRUE,
                        'append_type'        => 'submit',
                        'append_form_value'  => 'add_key',
                        'append_class'       => 'btn-default',
                        'append_value'       => 'Add Locale Key',
                        'append_button_name' => 'add_key',
                        'inner_width'        => '300px',
                        'class'              => 'm-b-0'
                    ]);
                    echo closeform();
                    if ($translations_rows > $rows) {
                        echo "<div class='pull-right'>\n";
                        $current_page = Translate_URI::get_link('view_translations', $_GET[self::$package_key], $_GET[self::$file_key]);
                        echo makepagenav($_GET['file_rows'], $ipp, $translations_rows, 3, $current_page.'&amp;', 'file_rows');
                        echo "</div>\n";
                    }
                    echo "</div>\n";
                    echo "<div class='panel-body p-0 br-0'>\n";

                    /**
                     * Translations Table
                     */
                    echo "<table class='table table-hover m-b-0'>\n";
                    if ($rows) {
                        echo "<thead>\n<tr>\n";
                        echo "<th></th><th></th>\n";
                        echo "<th class='col-xs-1'>Locale key</th>
                        <th class='col-xs-4'>".self::get_package_language($_GET['translate_lang'])."</th>
                        <th class='col-xs-1'></th>
                        <th class='col-xs-4'>".self::get_package_language($_GET['translate_to'])."</th>
                        <th class='col-xs-2'>Last Change</th></tr>\n";
                        echo "</thead><tbody>\n";
                        $field_options = [
                            'hide_value' => TRUE,
                            'label_tag'  => 'span',
                        ];
                        while ($data = dbarray($result)) {
                            $field_option_origin = [
                                    'field'  => [
                                        'function_type' => ((strlen($data['translate_locale_value']) > 50 || strlen($data['translated_locale_value']) > 50) ? 'form_textarea' : 'form_text'),
                                        'name'          => 'translate_locale_value['.$_GET['translate_lang'].']',
                                        'label'         => '',
                                        'options'       => ['class' => 'm-b-5 form-group-lg'],
                                        'max_length'    => 500,
                                    ],
                                    'hidden' => [
                                        'name'  => 'translate_id',
                                        'value' => $data['translate_id'],
                                    ],
                                    'save'   => [
                                        'name'    => 'save_translations',
                                        'label'   => fusion_get_locale('save_changes'),
                                        'value'   => $data['translate_locale_key'],
                                        'options' => array('class' => 'btn-success'),
                                    ]
                                ] + $field_options;
                            $field_option_remotes = [
                                    'field'  => [
                                        'function_type' => ((strlen($data['translate_locale_value']) > 50 || strlen($data['translated_locale_value']) > 50) ? 'form_textarea' : 'form_text'),
                                        'name'          => 'translate_locale_value['.$_GET['translate_to'].']',
                                        'label'         => '',
                                        'options'       => ['class' => 'm-b-5 form-group-lg'],
                                        'max_length'    => 500,
                                    ],
                                    'hidden' => [
                                        'name'  => 'translate_id',
                                        'value' => $data['translated_id'],
                                    ],
                                    'save'   => [
                                        'name'    => 'save_translations',
                                        'label'   => fusion_get_locale('save_changes'),
                                        'value'   => $data['translate_locale_key'],
                                        'options' => array('class' => 'btn-success'),
                                    ]
                                ] + $field_options;

                            /**
                             * Status Documentation
                             * ----------------------
                             * 0    regular status
                             * 1    lock the translations
                             * 2    abnormalities - will warning
                             * 3    comparable additional - will success
                             * 4    comparable missing - will danger
                             * 5    open to public suggestions (pair to submissions)
                             */

                            $value = "<span class='mid-opacity'>Nothing to translate</span>";
                            if ($_GET['translate_to'] != $_GET['translate_lang']) {
                                $value = form_hero('tr_'.($data['translated_id'] ?: $data['translate_id']), 'post', FUSION_REQUEST, $data['translated_locale_value'], $data['translated_locale_value'], $field_option_remotes);
                            }
                            echo "<tr ".($data['translate_status'] == 2 ? "class='warning'" : '').">\n";
                            echo "<td>\n";
                            echo "<a href='".clean_request('del_key='.$data['translate_locale_key'], ['del_key'], FALSE)."'><i class='fa fa-trash-o'></i></a>\n";
                            echo "</td>\n<td>\n";
                            if ($data['translate_status'] > 0 && $data['translate_status'] < 5) {
                                echo "<a class='btn btn-xs' href='".clean_request('reg_key='.$data['translate_id'], ['reg_key'], FALSE)."'>Resolve</a>\n";
                            }
                            echo "</td>";
                            echo "<td>".$data['translate_locale_key']."</td>\n";
                            echo "<td id='l_".$data['translate_id']."'>\n";
                            if ($data['translate_status'] == 2) {
                                echo "<span data-toggle='tooltip' title='".$data['translate_message']."' class='fa fa-question-circle-o pull-left m-r-10'></span>\n";
                            }
                            echo form_hero('tt_'.$data['translate_id'], 'post', FUSION_REQUEST, $data['translate_locale_value'], $data['translate_locale_value'], $field_option_origin);
                            echo "</td>\n";
                            echo "<td>\n";
                            // find possible values of the same in another language
                            echo "<div class='btn-group m-0'>\n";
                            if ($data['translate_locale_value']) {
                                echo "<a class='btn btn-xs btn-default".(!empty($data['translated_locale_value']) ? " disabled" : "")."' href='".clean_request('localex='.$data['translate_id'], ['localex'], FALSE)."'>Find</a>\n";
                            }
                            if (defined('YANDEX_KEY') && $data['translate_locale_value']) {
                                echo "<a class='btn btn-xs btn-default".(!empty($data['translated_locale_value']) ? " disabled" : "")."' href='".clean_request('yandex='.$data['translate_id'], ['yandex'], FALSE)."'>Translate</a>";
                            }
                            echo "</div>\n";
                            echo "</td>\n";
                            echo "<td id='l_".$data['translated_id']."'>$value</td>\n";
                            echo "<td class='text-right'>".timer($data['translated_datestamp'] ? $data['translated_datestamp'] : $data['translate_datestamp'])."</td>\n";
                            echo "</tr>\n";
                        }
                    } else {
                        echo "<tr><td class='text-center'>There are no translations</td></tr>\n";
                    }
                    echo "</table></div></div>\n";
                } else {
                    redirect(Translate_URI::get_link('view_package', $_GET[self::$package_key]));
                }
            } else {

                $select = "SELECT * FROM ".DB_TRANSLATE_FILES." WHERE file_package=:package_id AND file_language=:language AND file_status=1 ORDER BY file_name ASC";
                $bind = [
                    ':package_id' => $_GET[self::$package_key],
                    ':language'   => $_GET['translate_lang'],
                ];
                $result = dbquery($select, $bind);
                if (dbrows($result)) {
                    echo "<div class='panel-body p-0 br-0'>\n";
                    echo "<table class='table table-hover m-b-0'>\n";
                    while ($data = dbarray($result)) {
                        $count_sql = "SELECT count(t.translate_id) 'translations_count', count(tt.translate_id) 'translated_count'
                        FROM ".DB_TRANSLATE." t 
                        LEFT JOIN ".DB_TRANSLATE." tt ON tt.translate_locale_key=t.translate_locale_key AND tt.translate_file_id=:file_id_00 AND tt.translate_language=:translate_to AND tt.translate_locale_value !=''
                        WHERE t.translate_language=:translate_lang AND t.translate_file_id=:file_id_01
                        ";
                        $count_param = [
                            ':file_id_00' => $data['file_id'],
                            ':file_id_01' => $data['file_id'],
                            ':translate_lang' => $_GET['translate_lang'],
                            ':translate_to' => $_GET['translate_to']
                        ];
                        $count_result = dbquery($count_sql, $count_param);
                        $count_data = dbarray($count_result);

                        $file_pcg = "";
                        if ($_GET['translate_lang'] != $_GET['translate_to']) {
                            $file_pcg = $count_data['translations_count'] ? (round($count_data['translated_count'] / $count_data['translations_count'], 2) * 100).' % translated' : 'File is blank!';
                        }
                        echo "
                        <tr>
                            <td class='min'><a href='".Translate_URI::get_link('delete_file', $data['file_id'])."'><i class='fa fa-trash-o'></i></a></td>
                            <td class='col-xs-4'>
                                <a href='".Translate_URI::get_link('view_translations', $_GET[self::$package_key], $data['file_id'])."'>".$data['file_name']."</a>
                            </td>
                            <td>".$data['file_message']."</td>
                        <td>".$file_pcg."</td>
                        <td class='col-xs-2 text-right'>".timer($data['file_datestamp'])."</td>
                        </tr>\n";
                    }
                    echo "</tbody>\n</table>\n";
                } else {
                    echo "<div class='p-25 text-center'>\n<h5>There are no files. You can add files by creating a new file, or import a file.</h5>\n</div>\n";
                }
            }

            echo "</div>\n</div>\n";
            add_to_jquery("
            $('[data-toggle=\"tooltip\"]').tooltip()
            ");


        }
    }
}

require_once(DYNAMICS.'includes/form_hero.php');
require_once(INFUSIONS.'translate/translator_keys.inc');
if (defined('YANDEX_KEY')) {
    require_once(INFUSIONS.'translate/classes/yandex/Translation.php');
    require_once(INFUSIONS.'translate/classes/yandex/Translator.php');
    require_once(INFUSIONS.'translate/classes/yandex/Exception.php');
}