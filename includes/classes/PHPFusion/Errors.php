<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Errros.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion;

use PHPFusion\Database\DatabaseFactory;

/**
 * Class Errors
 * PHPFusion Error Handling
 *
 * @package PHPFusion
 */
class Errors {
    private static $instances = [];
    private static $locale = [];
    public $compressed = 0;
    private $error_status;
    private $posted_error_id;
    private $delete_status;
    private $rows;
    private $rowstart;
    private $error_id;
    private $errors = [];
    private $new_errors = [];

    /*
     * Severity when set Error Level
     */
    const E_ERROR = 1;
    const E_WARNING = 2;
    const E_PARSE = 4;
    const E_NOTICE = 8;
    const E_CORE_ERROR = 16;
    const E_CORE_WARNING = 32;
    const E_COMPILE_ERROR = 64;
    const E_COMPILE_WARNING = 128;
    const E_USER_ERROR = 256;
    const E_USER_WARNING = 512;
    const E_USER_NOTICE = 1024;
    const E_ALL = 2047;
    const E_STRICT = 2048;

    public function __construct() {

        self::$locale = fusion_get_locale('', [LOCALE.LOCALESET.'admin/errors.php', LOCALE.LOCALESET.'errors.php']);
        $this->error_status = check_post('error_status') ? (int)descript(post('error_status', FILTER_VALIDATE_INT)) : 0;
        $this->posted_error_id = check_post('error_id') ? (int)descript(post('error_id', FILTER_VALIDATE_INT)) : 0;
        $this->delete_status = check_post('delete_status') ? (int)descript(post('delete_status', FILTER_VALIDATE_INT)) : 0;
        $this->rowstart = (int)get('rowstart', FILTER_VALIDATE_INT);
        $this->error_id = (int)get('error_id', FILTER_VALIDATE_INT);

        if (check_post('error_status') && check_post('error_id')) {

            dbquery("UPDATE ".DB_ERRORS." SET error_status='".$this->error_status."' WHERE error_id=:eid", [':eid' => $this->posted_error_id]);

            $source_redirection_path = preg_replace("~".fusion_get_settings("site_path")."~", "", FUSION_REQUEST, 1);

            redirect(fusion_get_settings("siteurl").$source_redirection_path);
        }

        if (check_post('delete_entries')) {

            dbquery("DELETE FROM ".DB_ERRORS." WHERE error_status=:status", [':status' => $this->delete_status]);

            $source_redirection_path = preg_replace("~".fusion_get_settings("site_path")."~", "", FUSION_REQUEST, 1);

            redirect(fusion_get_settings("siteurl").$source_redirection_path);
        }

        $result = dbquery("SELECT * FROM ".DB_ERRORS." ORDER BY error_timestamp DESC LIMIT :rowstart,20", [':rowstart' => abs($this->rowstart)]);
        while ($data = dbarray($result)) {
            // Sanitizes callback
            foreach ($data as $key => $value) {
                $data[$key] = descript($value);
            }

            $this->errors[$data['error_id']] = $data;
        }

        $this->rows = ($this->errors ? dbcount('(error_id)', DB_ERRORS) : 0);
    }

    /**
     * Get an instance by key
     *
     * @param string $key
     *
     * @return static
     */
    public static function getInstance($key = 'default') {
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new static();
        }

        return self::$instances[$key];
    }

    /**
     * Custom error handler for PHP processor
     *
     * @param int    $error_level   Severity
     * @param string $error_message $e->message
     * @param string $error_file    The file in question, run a debug_backtrace()[2] in the file
     * @param int    $error_line    The line in question, run a debug_backtrace()[2] in the file
     */
    public function setError($error_level, $error_message, $error_file, $error_line) {
        $userdata = fusion_get_userdata();
        $showLiveError = TRUE; // directly show error - push to another instance

        $db = DatabaseFactory::getConnection();
        $result = $db->query("
            SELECT * FROM ".DB_ERRORS."
            WHERE error_file = :file AND error_line = :line
            ORDER BY error_timestamp DESC LIMIT 1", [
            ':file' => $error_file,
            ':line' => $error_line
        ]);

        if ($db->countRows($result) == 0) {
            $db->query("INSERT INTO ".DB_ERRORS." (
                error_level, error_message, error_file, error_line, error_page,
                error_user_level, error_user_ip, error_user_ip_type, error_status, error_timestamp
            ) VALUES (
                :level, :message, :file, :line, :page,
                '".$userdata['user_level']."', '".USER_IP."', '".USER_IP_TYPE."',
                '0', '".time()."'
            )", [
                ':level'   => $error_level,
                ':message' => addslashes($error_message),
                ':file'    => $error_file,
                ':page'    => FUSION_REQUEST,
                ':line'    => $error_line,
            ]);
            $errorId = $db->getLastId();

        } else {

            $data = $db->fetchAssoc($result);

            $errorId = $data['error_id'];

            if ($data['error_status'] == 2) {
                $showLiveError = FALSE;
            }
        }

        if ($showLiveError && $db->countRows($result) == 0) {
            $this->new_errors[$errorId] = [
                "error_id"        => $errorId,
                "error_level"     => $error_level,
                "error_file"      => $error_file,
                "error_line"      => $error_line,
                "error_page"      => FUSION_REQUEST,
                "error_message"   => descript($error_message),
                "error_timestamp" => time(),
                "error_status"    => 0,
            ];
        }
    }

    /**
     * @param array $data
     *
     * @return string
     */
    private function showErrorRows($data) {
        $locale = self::$locale;
        $link_title = $this->getMaxFolders($data['error_file'], 1);
        $data['error_message'] = str_replace('&#039;', "'", $data['error_message']);

        $html = "<tr id='rmd-".$data['error_id']."'>";
        $html .= "<td class='word-break' style='text-align:left;'>";
        $html .= "<a data-toggle='collapse' data-target='#err_rmd-".$data['error_id']."' aria-expanded='false' aria-controls='#err_rmd-".$data['error_id']."' class='accordion-toggle strong' title='".$locale['show']."' style='font-size:15px;'>".$link_title."</a><br/>\n";
        $html .= "<code class='error_page'>".$data['error_page']." <span class='label label-success'>**</span></code><br/>\n";
        $html .= "<strong>".$locale['ERROR_415']." ".$data['error_line']."</strong><br/>\n";
        $html .= "<small>".timer($data['error_timestamp'])."</small>\n";
        $html .= "</td>\n<td>\n";

        $html .= "<div class='btn-group'>\n";
        $html .= "<a class='btn btn-sm btn-default ' href='".ADMIN."errors.php".fusion_get_aidlink()."&rowstart=".$this->rowstart."&error_id=".$data['error_id']."#file' target='new_window'><i class='fa fa-eye m-0'></i></a>\n";
        $html .= "<button class='btn btn-sm btn-default copy-error' data-clipboard-target='#error-".$data['error_id']."'><i class='fa fa-copy m-0'></i></button>\n";
        $html .= "</div>\n";
        $html .= "</td>\n";

        $html .= "<td id='ecmd_".$data['error_id']."' style='white-space:nowrap;'>\n";
        $html .= "<a data-id='".$data['error_id']."' data-type='0' class='btn btn-sm".($data['error_status'] == 0 ? ' active' : '')." e_status_0 button btn-default  move_error_log'>".$locale['ERROR_450']."</a>\n";
        $html .= "<a data-id='".$data['error_id']."' data-type='1' class='btn btn-sm".($data['error_status'] == 1 ? ' active' : '')." e_status_1 button btn-default  move_error_log'>".$locale['ERROR_451']."</a>\n";
        $html .= "<a data-id='".$data['error_id']."' data-type='2' class='btn btn-sm".($data['error_status'] == 2 ? ' active' : '')." e_status_2 button btn-default  move_error_log'>".$locale['ERROR_452']."</a>\n";
        $html .= "<a data-id='".$data['error_id']."' data-type='999' class='btn btn-sm e_status_999 button btn-default move_error_log'>".$locale['delete']."</a>\n";
        $html .= "</td>\n";
        $html .= "</tr>\n";
        /* Toggle Info */
        $error_message = strtr(stripslashes($data['error_message']), ['#' => '<br/>#']);
        $html .= "<tr class='collapse' id='err_rmd-".$data['error_id']."'><td colspan='4' class='hiddenRow no-border'>\n";
        $html .= "<p><strong>".$locale['ERROR_454']."</strong> : ".$this->getErrorTypes($data['error_level'])."</p>";
        $html .= "<div class='alert alert-info'>".$error_message."</div>\n";
        $html .= "</td></tr>\n";

        $html .= '<textarea style="position:absolute;width:.1px;height:.1px;border:0;padding:0;" id="error-'.$data['error_id'].'">';
        $html .= 'File: '.$link_title.PHP_EOL;
        $html .= 'Page: '.$data['error_page'].PHP_EOL;
        $html .= 'Line: '.$data['error_line'].PHP_EOL;
        $html .= 'Error message: '.$error_message;
        $html .= '</textarea>';
        return $html;
    }

    /**
     * Administration Console
     */
    public function displayAdministration() {
        $aidlink = fusion_get_aidlink();

        $locale = self::$locale;

        define("NO_DEBUGGER", TRUE);

        $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;

        $tab_title['title'][] = $locale['ERROR_460'];
        $tab_title['id'][] = 'errors-list';
        $tab_title['icon'][] = 'fa fa-bug m-r-10';

        if ($this->error_id) {
            $tab_title['title'][] = $locale['ERROR_461'];
            $tab_title['id'][] = 'error-file';
            $tab_title['icon'][] = 'fa fa-medkit m-r-10';
            $tab_title['title'][] = $locale['ERROR_465'];
            $tab_title['id'][] = 'src-file';
            $tab_title['icon'][] = 'fa fa-stethoscope m-r-10';
        }
        $tab_active = tab_active($tab_title, $this->error_id ? 1 : 0);

        add_breadcrumb(['link' => ADMIN."errors.php".$aidlink, 'title' => $locale['ERROR_400']]);

        opentable($locale['ERROR_400']);

        echo opentab($tab_title, $tab_active, 'error_tab');
        echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active);

        if (fusion_get_settings('error_logging_method') === 'database') {
            echo $this->getErrorLogs();
        } else {
            if (isset($_POST['delete_log'])) {
                if (file_exists(BASEDIR.'fusion_error_log.log')) {
                    @unlink(BASEDIR.'fusion_error_log.log');
                    redirect(FUSION_REQUEST);
                }
            }
            echo openform('deletelog', 'post', FUSION_REQUEST);
            echo form_button('delete_log', $locale['delete'], 'delete_log', ['class' => 'btn-danger m-b-10', 'icon' => 'fa fa-trash']);
            echo closeform();

            if (file_exists(BASEDIR.'fusion_error_log.log')) {
                echo '<textarea class="form-control" rows="15" disabled>'.file_get_contents(BASEDIR.'fusion_error_log.log').'</textarea>';
            } else {
                echo "<div class='text-center well'>".$locale['ERROR_418']."</div>\n";
            }
        }

        echo closetabbody();

        if ($this->error_id) {
            // dump 1 and 2
            add_to_head("<link rel='stylesheet' href='".THEMES."templates/errors.css' type='text/css' media='all' />");
            define('no_debugger', 1);
            $data = dbarray(dbquery("SELECT * FROM ".DB_ERRORS." WHERE error_id=:errorid LIMIT 1", [':errorid' => $this->error_id]));
            if (!$data) {
                redirect(FUSION_SELF.$aidlink);
            }

            $thisFileContent = is_file($data['error_file']) ? file($data['error_file']) : [];
            $line_start = max($data['error_line'] - 10, 1);
            $line_end = min($data['error_line'] + 10, count($thisFileContent));
            $output = implode("", array_slice($thisFileContent, $line_start - 1, $line_end - $line_start + 1));
            $pageFilePath = BASEDIR.$data['error_page'];
            $pageContent = is_file($pageFilePath) ? file_get_contents($pageFilePath) : '';

            add_to_jquery("$('#error_status_sel').bind('change', function(e){this.form.submit();});");

            echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active); ?>
            <div class='display-inline text-lighter'>
                <strong><?php echo $locale['ERROR_419'] ?></strong>: <?php echo self::getMaxFolders($data['error_file'], 3); ?>
                <label class='label label-success'><?php echo $locale['ERROR_415']." ".number_format($data['error_line']); ?></label>
            </div>

            <div class='m-t-10'>
                <div class='display-inline-block m-r-20'>
                    <i class='fa fa-file-code-o'></i> <strong><?php echo $locale['ERROR_411']; ?></strong>:
                    <a data-toggle="tab" data-target="#src-file" role="tab" href='<?php echo FUSION_SELF.$aidlink."&rowstart=".$_GET['rowstart']."&error_id=".$data['error_id'] ?>#src-file' title='<?php echo $data['error_page'] ?>'>
                        <?php echo self::getMaxFolders($data['error_page'], 3); ?>
                    </a>
                </div>
                <div>
                    <span class='text-lighter'><?php echo $locale['ERROR_463'] ?></span>
                    <span class='label label-info'><strong><?php echo $locale['ERROR_412']."-".$locale['ERROR_416'].$data['error_user_level']; ?> -- <?php echo $locale['ERROR_417']." ".$data['error_user_ip']; ?></strong></span>
                    <span class='text-lighter'><?php echo lcfirst($locale['on']); ?></span>
                    <span class='label label-info'><strong class='m-r-10'><?php echo showdate("longdate", $data['error_timestamp']) ?></strong></span>
                </div>
            </div>
            <div class='m-t-10 display-inline-block' style='width:300px'>
                <?php
                echo openform('logform', 'post', ADMIN.'errors.php'.$aidlink."&rowstart=".$_GET['rowstart']."&error_id=".$data['error_id']."#file");
                echo form_hidden('error_id', '', $data['error_id']);
                echo form_select('error_status', $locale['mark_as'], $data['error_status'], [
                    'inline'  => TRUE,
                    'options' => $this->getErrorLogTypes()
                ]);
                echo closeform();
                ?>
            </div>

            <div class='m-t-10'>
                <div class="table-responsive">
                    <?php echo $this->printCode($output, $line_start, $data['error_line'], [
                        'time' => $data['error_timestamp'],
                        'text' => $data['error_message']
                    ], '<strong>'.$locale['ERROR_421'].'</strong> ('.$locale['ERROR_415'].' '.$line_start.' - '.$line_end.')'); ?>
                </div>
            </div>
            <?php
            echo closetabbody();
            echo opentabbody($tab_title['title'][2], $tab_title['id'][2], $tab_active);
            ?>
            <div class='m-t-10'>
                <div class="table-responsive">
                    <?php echo $this->printCode($pageContent, 1, NULL, [], '<strong>'.$locale['ERROR_411'].': '.self::getMaxFolders($data['error_page'], 3).'</strong>'); ?>
                </div>
            </div>
            <?php
            echo closetabbody();
        }

        echo closetab();
        closetable();
    }

    /**
     * @return string
     */
    private function getErrorLogs() {
        $aidlink = fusion_get_aidlink();
        $locale = self::$locale;

        fusion_load_script(INCLUDES."jscripts/clipboard.js");
        add_to_jquery('new ClipboardJS(".copy-error");');

        // Use clean request for absolute escape from SEO converging form path
        $html = openform('error_logform', 'post', clean_request('', [], FALSE));
        $html .= '<div class="text-center well m-t-5 m-b-5">';
        $html .= "<div class='display-inline-block text-right m-r-10'>".$locale['ERROR_440']."</div>\n";
        $html .= "<div class='display-inline-block'>\n";
        $html .= form_select('delete_status', '', '0', ['options' => $this->getErrorLogTypes(), 'select2_disabled' => TRUE, 'inline' => TRUE, 'class' => 'input-group-sm']);
        $html .= form_button('delete_entries', $locale['ERROR_453'], $locale['ERROR_453'], ['class' => 'm-l-10 btn-primary btn-sm']);
        $html .= "</div>\n";
        $html .= "</div>\n";
        $html .= closeform();

        if (!empty($this->errors) or !empty($this->new_errors)) {
            $html .= "<div class='table-responsive'><table id='error_logs_table' class='table center'>";
            $html .= "<tr>";
            $html .= "<th>".$locale['ERROR_410']."</th>";
            $html .= "<th class='col-xs-2'>".$locale['ERROR_462']."</th>";
            $html .= "<th class='col-xs-4'>".$locale['ERROR_414']."</th>\n";
            $html .= "</tr>\n";

            if (!empty($this->new_errors)) {
                foreach ($this->new_errors as $data) {
                    $html .= $this->showErrorRows($data);
                }
            }

            if (!empty($this->errors)) {
                foreach ($this->errors as $data) {
                    $html .= $this->showErrorRows($data);
                }
            }

            $html .= "</table>\n</div>";

            if ($this->rows > 20) {
                $html .= "<div class='m-t-10 text-center'>\n";
                $html .= makepagenav($this->rowstart, 20, $this->rows, 3, ADMIN."errors.php".$aidlink."&");
                $html .= "</div>\n";
            }
        } else {
            $html .= "<div class='text-center well'>".$locale['ERROR_418']."</div>\n";
        }
        $this->errorJs();

        return $html;
    }

    /**
     * @return array
     */
    private function getErrorLogTypes() {
        $locale = self::$locale;

        return [
            '0' => $locale['ERROR_450'],
            '1' => $locale['ERROR_451'],
            '2' => $locale['ERROR_452']
        ];
    }

    /**
     * @param string $url
     * @param int    $level
     *
     * @return string
     */
    private function getMaxFolders($url, $level = 2) {
        $return = "";
        $tmpUrlArr = explode("/", $url);
        if (count($tmpUrlArr) > $level) {
            $tmpUrlArr = array_reverse($tmpUrlArr);
            for ($i = 0; $i < $level; $i++) {
                $return = $tmpUrlArr[$i].($i > 0 ? "/".$return : "");
            }
        } else {
            $return = implode("/", $tmpUrlArr);
        }

        return $return;
    }

    /**
     * @param int $type
     *
     * @return false|mixed
     */
    private function getErrorTypes($type) {
        $locale = self::$locale;
        $error_types = [
            self::E_ERROR           => ["E_ERROR", $locale['E_ERROR']],
            self::E_WARNING         => ["E_WARNING", $locale['E_WARNING']],
            self::E_PARSE           => ["E_PARSE", $locale['E_PARSE']],
            self::E_NOTICE          => ["E_NOTICE", $locale['E_NOTICE']],
            self::E_CORE_ERROR      => ["E_CORE_ERROR", $locale['E_CORE_ERROR']],
            self::E_CORE_WARNING    => ["E_CORE_WARNING", $locale['E_CORE_WARNING']],
            self::E_COMPILE_ERROR   => ["E_COMPILE_ERROR", $locale['E_COMPILE_ERROR']],
            self::E_COMPILE_WARNING => ["E_COMPILE_WARNING", $locale['E_COMPILE_WARNING']],
            self::E_USER_ERROR      => ["E_USER_ERROR", $locale['E_USER_ERROR']],
            self::E_USER_WARNING    => ["E_USER_WARNING", $locale['E_USER_WARNING']],
            self::E_USER_NOTICE     => ["E_USER_NOTICE", $locale['E_USER_NOTICE']],
            self::E_ALL             => ["E_ALL", $locale['E_ALL']],
            // self::E_STRICT          => ["E_STRICT", $locale['E_STRICT']]
            self::E_STRICT          => ["E_STRICT", '']
        ];
        if (isset($error_types[$type])) {
            return $error_types[$type][1];
        }

        return FALSE;
    }

    /**
     * JS code
     */
    private function errorJs() {
        if (checkrights("ERRO") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] == iAUTH) {
            // Show the "Apply"-button only when javascript is disabled
            add_to_jquery("
            $('a#footer_debug').bind('click', function(e) {
                e.preventDefault();
            });
            $('.change_status').hide();
            $('#top').click(function(){
                jQuery('html, body').animate({scrollTop:0}, 'slow');
                return false;
            });

            $('.move_error_log').bind('click', function() {
                var form = $('#error_logform');
                var data = {
                    'aidlink' : '".fusion_get_aidlink()."',
                    'error_id' : $(this).data('id'),
                    'error_type' : $(this).data('type')
                };
                var sendData = form.serialize() + '&' + $.param(data);
                $.ajax({
                    url: '".ADMIN."includes/?api=error-logs-updater',
                    dataType: 'json',
                    method : 'GET',
                    type: 'json',
                    data: sendData,
                    success: function(e) {
                        console.log(e);
                        if (e.status == 'OK') {
                            var target_group_add  = $('tr#rmd-'+e.fusion_error_id+' > td > a.e_status_'+ e.to);
                            var target_group_remove = $('tr#rmd-'+e.fusion_error_id+' > td > a.e_status_'+ e.from)
                            target_group_add.addClass('active');
                            target_group_remove.removeClass('active');
                        }
                        else if (e.status == 'RMD') {
                             $('tr#rmd-'+e.fusion_error_id).remove();
                             $('tr#err_rmd-'+e.fusion_error_id).remove();
                        }
                    },
                    error : function(e) {
                        console.log('fail');
                    }
                });
            });
        ");
        }
    }

    /**
     * @param string $source_code
     * @param int    $starting_line
     * @param string $error_line
     * @param array  $error_message
     * @param null   $title
     *
     * @return false|string
     */
    private function printCode($source_code, $starting_line, $error_line = "", array $error_message = [], $title = NULL) {
        $locale = fusion_get_locale();

        if (is_array($source_code)) {
            return FALSE;
        }

        $error_message = [
            'time' => !empty($error_message['time']) ? $error_message['time'] : time(),
            'text' => !empty($error_message['text']) ? $error_message['text'] : $locale['na'],];
        $source_code = explode("\n", str_replace(["\r\n", "\r"], "\n", $source_code));
        $line_count = $starting_line;
        $formatted_code = "";
        $error_message = "<div class='panel panel-default m-10'>
        <div class='panel-heading'><i class='fa fa-bug'></i> Line ".$error_line." -- ".timer($error_message['time'])."</div>
        <div class='panel-body'>".strtr(stripslashes($error_message['text']), ['#' => '<br/>#'])."</div>";
        foreach ($source_code as $code_line) {
            $code_line = $this->codeWrap($code_line, 145);
            $line_class = ($line_count == $error_line ? "err_tbl-error-line" : "err_tbl1");
            $formatted_code .= "<tr>\n<td class='err_tbl2' style='text-align:right;width:1%;'>".$line_count."</td>\n";
            if (preg_match('#<\?(php)?[^[:graph:]]#', $code_line)) {
                $formatted_code .= "<td class='".$line_class."'>".str_replace(['<code>', '</code>'], '', highlight_string($code_line, TRUE))."</td>\n</tr>\n";
            } else {
                $formatted_code .= "<td class='".$line_class."'>".preg_replace('#(&lt;\?php&nbsp;)+#', '', str_replace(['<code>', '</code>'], '', highlight_string('<?php '.$code_line, TRUE)))."
                </td>\n</tr>\n";
                if ($line_count == $error_line) {
                    $formatted_code .= "<tr>\n<td colspan='2'>".$error_message."</td></tr>\n";
                }
            }
            $line_count++;
        }

        $title = !empty($title) ? '<thead><tr><th colspan="2" class="p-10">'.$title.'</th></tr></thead>' : '';

        return "<table class='table-bordered err_tbl-border center' cellspacing='0' cellpadding='0'>".$title."<tbody>".$formatted_code."</tbody></table>";
    }

    /**
     * @param string $code
     * @param int    $maxLength
     *
     * @return string
     */
    private function codeWrap($code, $maxLength = 150) {
        $lines = explode("\n", $code);
        $count = count($lines);
        for ($i = 0; $i < $count; ++$i) {
            preg_match('`^\s*`', $code, $matches);
            $lines[$i] = wordwrap($lines[$i], $maxLength, "\n$matches[0]\t", TRUE);
        }

        return implode("\n", $lines);
    }

    /**
     * Use this function to show error logs
     */
    public function showFooterErrors() {
        $locale = self::$locale;
        $aidlink = fusion_get_aidlink();
        $html = '';
        if (iADMIN && checkrights("ERRO") && (count($this->errors) || count($this->new_errors)) && !defined("NO_DEBUGGER") || defined('DEVELOPER_MODE')) {
            $html .= "<div class='display-block'>";
            $html .= "<i class='fa fa-bug fa-lg'></i></button>\n";
            $html .= str_replace(["[ERROR_LOG_URL]", "[/ERROR_LOG_URL]"],
                [
                    "<a id='footer_debug' href='".ADMIN."errors.php".$aidlink."'>",
                    "</a>"
                ], $locale['err_101']);
            $html .= "<span class='badge m-l-10'>L: ".count($this->errors)."</span>\n";
            $html .= "<span class='badge m-l-10'>N: ".count($this->new_errors)."</span>\n";
            $html .= "</div>\n";

            $cHtml = openmodal('tbody', $locale['ERROR_464'], ['class' => 'modal-lg modal-center zindex-boost errorlogmodal', 'button_id' => 'footer_debug']);
            $cHtml .= $this->getErrorLogs();
            $cHtml .= closemodal();
            add_to_footer($cHtml);
        }

        return $html;
    }
}
