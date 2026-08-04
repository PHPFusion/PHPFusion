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

use Mpdf\Gif\Image;
use PHPFusion\Database\DatabaseFactory;

/**
 * Class Errors
 * PHPFusion Error Handling
 *
 * @package PHPFusion
 */
class Errors
{
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

    public function __construct()
    {

        self::$locale = fusion_get_locale('', [LOCALE . LOCALESET . 'admin/errors.php', LOCALE . LOCALESET . 'errors.php']);
        $this->error_status = check_post('error_status') ? (int)descript(post('error_status', FILTER_VALIDATE_INT)) : 0;
        $this->posted_error_id = check_post('error_id') ? (int)descript(post('error_id', FILTER_VALIDATE_INT)) : 0;
        $this->delete_status = check_post('delete_status') ? (int)descript(post('delete_status', FILTER_VALIDATE_INT)) : 0;
        $this->rowstart = (int)get('rowstart', FILTER_VALIDATE_INT);
        $this->error_id = (int)get('error_id', FILTER_VALIDATE_INT);

        if (check_post('error_status') && check_post('error_id')) {

            dbquery("UPDATE " . DB_ERRORS . " SET error_status='" . $this->error_status . "' WHERE error_id=:eid", [':eid' => $this->posted_error_id]);

            $source_redirection_path = preg_replace("~" . fusion_get_settings("site_path") . "~", "", FUSION_REQUEST, 1);

            // Disabled: redirecting through the configured site URL can drop the active admin cookie.
            // redirect(fusion_get_settings("siteurl") . $source_redirection_path);
			redirect(FORM_REQUEST);
        }

        if (check_post('delete_entries')) {

            dbquery("DELETE FROM " . DB_ERRORS . " WHERE error_status=:status", [':status' => $this->delete_status]);

            $source_redirection_path = preg_replace("~" . fusion_get_settings("site_path") . "~", "", FUSION_REQUEST, 1);

            // Disabled: redirecting through the configured site URL can drop the active admin cookie.
            // redirect(fusion_get_settings("siteurl") . $source_redirection_path);
			redirect(FORM_REQUEST);
        }

        $result = dbquery("SELECT * FROM " . DB_ERRORS . " ORDER BY error_timestamp DESC LIMIT :rowstart,20", [':rowstart' => abs($this->rowstart)]);
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
    public static function getInstance($key = 'default')
    {
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new static();
        }

        return self::$instances[$key];
    }

    /**
     * Custom error handler for PHP processor
     *
     * @param int $error_level Severity
     * @param string $error_message $e->message
     * @param string $error_file The file in question, run a debug_backtrace()[2] in the file
     * @param int $error_line The line in question, run a debug_backtrace()[2] in the file
     */
    public function setError($error_level, $error_message, $error_file, $error_line)
    {
        $userdata = fusion_get_userdata();
        $showLiveError = TRUE; // directly show error - push to another instance

        $db = DatabaseFactory::getConnection();
        $result = $db->query("
            SELECT * FROM " . DB_ERRORS . "
            WHERE error_file = :file AND error_line = :line
            ORDER BY error_timestamp DESC LIMIT 1", [
            ':file' => $error_file,
            ':line' => $error_line
        ]);

        if ($db->countRows($result) == 0) {
            $db->query("INSERT INTO " . DB_ERRORS . " (
                error_level, error_message, error_file, error_line, error_page,
                error_user_level, error_user_ip, error_user_ip_type, error_status, error_timestamp
            ) VALUES (
                :level, :message, :file, :line, :page,
                '" . $userdata['user_level'] . "', '" . USER_IP . "', '" . USER_IP_TYPE . "',
                '0', '" . time() . "'
            )", [
                ':level' => $error_level,
                ':message' => addslashes($error_message),
                ':file' => $error_file,
                ':page' => FUSION_REQUEST,
                ':line' => $error_line,
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
                "error_id" => $errorId,
                "error_level" => $error_level,
                "error_file" => $error_file,
                "error_line" => $error_line,
                "error_page" => FUSION_REQUEST,
                "error_message" => descript($error_message),
                "error_timestamp" => time(),
                "error_status" => 0,
            ];
        }
    }

    /**
     * @param array $data
     *
     * @return string
     */
    private function showErrorRows($data)
    {
        $aidlink     = fusion_get_aidlink();
        $link_title  = $this->getMaxFolders($data['error_file'], 3);
        $msg         = str_replace('&#039;', "'", $data['error_message']);
        $msg_short   = htmlspecialchars(substr(strip_tags(strtr(stripslashes($msg), ['#' => ' '])), 0, 130));
        $sev_class   = $this->getSeverityClass($data['error_level']);
        $type_label  = $this->getErrorTypeLabel($data['error_level']);
        $status_map  = [0 => 'is-new', 1 => 'is-solved', 2 => 'is-ignored'];
        $status_text = [0 => 'New', 1 => 'Solved', 2 => 'Ignored'];
        $status_cls  = $status_map[$data['error_status']] ?? 'is-new';
        $status_name = $status_text[$data['error_status']] ?? 'New';
        $detail_url  = ADMIN . "errors.php" . $aidlink . "&rowstart=" . $this->rowstart . "&error_id=" . $data['error_id'];
        $clip_text   = htmlspecialchars(
            'File: ' . $link_title . "\n" .
            'Page: ' . $data['error_page'] . "\n" .
            'Line: ' . $data['error_line'] . "\n" .
            'Error: ' . strip_tags(strtr(stripslashes($msg), ['#' => ' '])),
            ENT_QUOTES,
            'UTF-8'
        );

        $html  = "<tr class='errtk-issue {$status_cls}' id='issue-{$data['error_id']}' data-status='{$data['error_status']}' data-level='{$data['error_level']}'>";
        $html .= "<td><div class='errtk-issue-summary'>";
        $html .= "<span class='errtk-severity {$sev_class}'><i class='fa fa-exclamation' aria-hidden='true'></i></span>";
        $html .= "<div class='errtk-summary-copy'><a href='{$detail_url}' class='errtk-issue-msg'>{$msg_short}</a><span class='errtk-level {$sev_class}'>" . htmlspecialchars($type_label) . "</span></div>";
        $html .= "</div></td>";
        $html .= "<td><div class='errtk-source'><code>" . htmlspecialchars($link_title) . "</code><span>Line " . (int)$data['error_line'] . "</span><small>" . htmlspecialchars($data['error_page']) . "</small></div></td>";
        $html .= "<td><span class='errtk-status-label st-{$data['error_status']}'><span class='errtk-status-dot st-{$data['error_status']}'></span>{$status_name}</span></td>";
        $html .= "<td><span class='errtk-age'>" . timer($data['error_timestamp']) . "</span></td>";
        $html .= "<td class='errtk-actions-cell'><div class='dropdown'>";
        $html .= "<button type='button' class='errtk-icon-btn' data-bs-toggle='dropdown' aria-expanded='false' aria-label='Open error actions'><i class='fa fa-ellipsis-h' aria-hidden='true'></i></button>";
        $html .= "<div class='dropdown-menu dropdown-menu-end errtk-menu'>";
        $html .= "<a class='dropdown-item' href='{$detail_url}'><i class='fa fa-arrow-right fa-fw'></i> Open details</a>";
        $html .= "<button type='button' class='dropdown-item copy-error' data-clipboard-target='#errcp-{$data['error_id']}'><i class='fa fa-copy fa-fw'></i> Copy details</button>";
        $html .= "<div class='dropdown-divider'></div>";
        $html .= "<button type='button' class='dropdown-item move_error_log' data-id='{$data['error_id']}' data-type='0'><i class='fa fa-circle-o fa-fw'></i> Mark as new</button>";
        $html .= "<button type='button' class='dropdown-item move_error_log' data-id='{$data['error_id']}' data-type='1'><i class='fa fa-check fa-fw'></i> Mark as solved</button>";
        $html .= "<button type='button' class='dropdown-item move_error_log' data-id='{$data['error_id']}' data-type='2'><i class='fa fa-eye-slash fa-fw'></i> Ignore</button>";
        $html .= "<div class='dropdown-divider'></div>";
        $html .= "<button type='button' class='dropdown-item text-danger move_error_log' data-id='{$data['error_id']}' data-type='999'><i class='fa fa-trash fa-fw'></i> Delete</button>";
        $html .= "</div></div><textarea class='errtk-clipboard' id='errcp-{$data['error_id']}'>{$clip_text}</textarea></td>";
        $html .= "</tr>";

        return $html;
    }

    /**
     * Administration Console — Sentry-style issue tracker
     */
    public function displayAdministration()
    {
        pageaccess('ERRO');

        $aidlink = fusion_get_aidlink();
        $locale  = self::$locale;

        define("NO_DEBUGGER", TRUE);

        $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;

        add_breadcrumb(['link' => ADMIN . "errors.php" . $aidlink, 'title' => $locale['ERROR_400']]);

        $this->injectStyles();

        opentable($locale['ERROR_400']);

        if (fusion_get_settings('error_logging_method') === 'database') {
            echo $this->error_id ? $this->showErrorDetail() : $this->getErrorLogs();
        } else {
            if (isset($_POST['delete_log'])) {
                if (file_exists(BASEDIR . 'fusion_error_log.log')) {
                    @unlink(BASEDIR . 'fusion_error_log.log');
                    redirect(FUSION_REQUEST);
                }
            }
            echo openform('deletelog', 'post', FUSION_REQUEST);
            echo form_button('delete_log', $locale['delete'], 'delete_log', ['class' => 'btn-danger mb-2', 'icon' => 'fa fa-trash']);
            echo closeform();

            echo '<div class="errtk">';
            if (file_exists(BASEDIR . 'fusion_error_log.log')) {
                echo '<div class="errtk-section-body"><div class="errtk-code-wrap"><pre style="color:#EAE4F2;font-size:12px;margin:0;padding:16px;overflow:auto">' . htmlspecialchars(file_get_contents(BASEDIR . 'fusion_error_log.log')) . '</pre></div></div>';
            } else {
                echo '<div class="errtk-empty"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg><h4>' . $locale['ERROR_418'] . '</h4></div>';
            }
            echo '</div>';
        }

        closetable();
    }

    /**
     * @return string
     */
    private function getErrorLogs()
    {
        $aidlink = fusion_get_aidlink();
        $locale  = self::$locale;

        fusion_load_script(INCLUDES . "jscripts/clipboard.js");
        add_to_jquery('new ClipboardJS(".copy-error");');

        // Status counts
        $counts = ['total' => 0, 'new' => 0, 'solved' => 0, 'ignored' => 0];
        $counts['total'] = (int)dbcount('(error_id)', DB_ERRORS);
        $cnt_result = dbquery("SELECT error_status, COUNT(*) AS cnt FROM " . DB_ERRORS . " GROUP BY error_status");
        while ($row = dbarray($cnt_result)) {
            if ($row['error_status'] == 0)      $counts['new']     = (int)$row['cnt'];
            elseif ($row['error_status'] == 1)  $counts['solved']  = (int)$row['cnt'];
            elseif ($row['error_status'] == 2)  $counts['ignored'] = (int)$row['cnt'];
        }

        $resolution_rate = $counts['total'] > 0 ? (int)round(($counts['solved'] / $counts['total']) * 100) : 100;
        $health_label = $counts['new'] > 0 ? $counts['new'] . ' incidents need attention' : 'All tracked incidents resolved';

        $html  = '<div class="errtk">';

        // ── Header ────────────────────────────────────────────────
        $html .= '<div class="errtk-hd">';
        $html .= '<div class="errtk-hd-top">';
        $html .= '<div class="errtk-title-block">';
        $html .= '<h2>Errors</h2>';
        $html .= '<div class="errtk-health ' . ($counts['new'] > 0 ? 'has-open' : 'is-clear') . '"><span></span>' . htmlspecialchars($health_label) . '</div>';
        $html .= '</div>';
        $html .= openform('error_logform', 'post', clean_request('', [], FALSE));
        $html .= '<div class="errtk-maintenance">';
        $html .= '<select name="delete_status" class="form-select form-select-sm" aria-label="Status to clear">';
        foreach ($this->getErrorLogTypes() as $k => $v) {
            $html .= '<option value="' . $k . '">' . htmlspecialchars($v) . '</option>';
        }
        $html .= '</select>';
        $html .= '<button type="submit" name="delete_entries" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash fa-fw"></i> Clear status</button>';
        $html .= '</div>';
        $html .= closeform();
        $html .= '</div>';

        // Stats tabs
        $html .= '<div class="errtk-stats">';
        $html .= '<button type="button" class="errtk-stat active" data-filter="all"><span class="errtk-stat-label">Total incidents</span><span class="errtk-stat-num">' . $counts['total'] . '</span><span class="errtk-stat-note"><i class="fa fa-database"></i> All recorded events</span></button>';
        $html .= '<button type="button" class="errtk-stat" data-filter="new"><span class="errtk-stat-label">Open queue</span><span class="errtk-stat-num is-danger">' . $counts['new'] . '</span><span class="errtk-stat-note"><i class="fa fa-bolt"></i> Awaiting triage</span></button>';
        $html .= '<button type="button" class="errtk-stat" data-filter="solved"><span class="errtk-stat-label">Resolved</span><span class="errtk-stat-num is-success">' . $counts['solved'] . '</span><span class="errtk-stat-note"><i class="fa fa-check-circle"></i> ' . $resolution_rate . '% resolution rate</span></button>';
        $html .= '<button type="button" class="errtk-stat" data-filter="ignored"><span class="errtk-stat-label">Muted</span><span class="errtk-stat-num is-muted">' . $counts['ignored'] . '</span><span class="errtk-stat-note"><i class="fa fa-eye-slash"></i> Excluded from triage</span></button>';
        $html .= '</div>';
        $html .= '</div>';

        // ── Toolbar ───────────────────────────────────────────────
        $html .= '<div class="errtk-tb">';
        $html .= '<div class="errtk-search-wrap"><i class="fa fa-search" aria-hidden="true"></i><input type="search" class="form-control errtk-search" id="errtk-search" placeholder="Search incidents, files, or routes" autocomplete="off"></div>';
        $html .= '<div class="errtk-filter-label"><i class="fa fa-filter" aria-hidden="true"></i> Severity</div>';
        $html .= '<select class="form-select form-select-sm" id="errtk-sev-filter" aria-label="Filter by severity">';
        $html .= '<option value="all">All levels</option>';
        $html .= '<option value="error">Fatal and error</option>';
        $html .= '<option value="warn">Warning</option>';
        $html .= '<option value="notice">Notice</option>';
        $html .= '</select>';
        $html .= '<span class="errtk-visible-count"><strong id="errtk-visible-count">' . count($this->new_errors + $this->errors) . '</strong> shown</span>';
        $html .= '</div>';

        // ── Issue list ────────────────────────────────────────────
        $html .= '<div class="errtk-list"><div class="table-responsive">';
        $html .= '<table class="errtk-table"><thead><tr><th>Issue</th><th>Source</th><th>Status</th><th>Last seen</th><th><span class="visually-hidden">Actions</span></th></tr></thead><tbody id="errtk-issues">';

        if (!empty($this->errors) || !empty($this->new_errors)) {
            foreach ($this->new_errors as $data) {
                $html .= $this->showErrorRows($data);
            }
            foreach ($this->errors as $data) {
                $html .= $this->showErrorRows($data);
            }
        } else {
            $html .= '<tr><td colspan="5"><div class="errtk-empty">';
            $html .= '<i class="fa fa-check-circle-o" aria-hidden="true"></i>';
            $html .= '<h4>No issues found</h4><p>' . $locale['ERROR_418'] . '</p>';
            $html .= '</div></td></tr>';
        }

        $html .= '</tbody></table></div></div>';

        if ($this->rows > 20) {
            $html .= '<div class="errtk-pager">' . makepagenav($this->rowstart, 20, $this->rows, 3, ADMIN . "errors.php" . $aidlink . "&") . '</div>';
        }

        $html .= '</div>';

        $this->errorJs();
        return $html;
    }

    /**
     * @return array
     */
    private function getErrorLogTypes()
    {
        $locale = self::$locale;

        return [
            '0' => $locale['ERROR_450'],
            '1' => $locale['ERROR_451'],
            '2' => $locale['ERROR_452']
        ];
    }

    /**
     * @param string $url
     * @param int $level
     *
     * @return string
     */
    private function getMaxFolders($url, $level = 2)
    {
        $return = "";
        $tmpUrlArr = explode("/", $url);
        if (count($tmpUrlArr) > $level) {
            $tmpUrlArr = array_reverse($tmpUrlArr);
            for ($i = 0; $i < $level; $i++) {
                $return = $tmpUrlArr[$i] . ($i > 0 ? "/" . $return : "");
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
    private function getErrorTypes($type)
    {
        $locale = self::$locale;
        $error_types = [
            self::E_ERROR => ["E_ERROR", $locale['E_ERROR']],
            self::E_WARNING => ["E_WARNING", $locale['E_WARNING']],
            self::E_PARSE => ["E_PARSE", $locale['E_PARSE']],
            self::E_NOTICE => ["E_NOTICE", $locale['E_NOTICE']],
            self::E_CORE_ERROR => ["E_CORE_ERROR", $locale['E_CORE_ERROR']],
            self::E_CORE_WARNING => ["E_CORE_WARNING", $locale['E_CORE_WARNING']],
            self::E_COMPILE_ERROR => ["E_COMPILE_ERROR", $locale['E_COMPILE_ERROR']],
            self::E_COMPILE_WARNING => ["E_COMPILE_WARNING", $locale['E_COMPILE_WARNING']],
            self::E_USER_ERROR => ["E_USER_ERROR", $locale['E_USER_ERROR']],
            self::E_USER_WARNING => ["E_USER_WARNING", $locale['E_USER_WARNING']],
            self::E_USER_NOTICE => ["E_USER_NOTICE", $locale['E_USER_NOTICE']],
            self::E_ALL => ["E_ALL", $locale['E_ALL']],
            // self::E_STRICT          => ["E_STRICT", $locale['E_STRICT']]
            self::E_STRICT => ["E_STRICT", '']
        ];
        if (isset($error_types[$type])) {
            return $error_types[$type][1];
        }

        return FALSE;
    }

    /**
     * JS for issue tracker interactions
     */
    private function errorJs()
    {
        if (checkrights("ERRO") || !defined("iAUTH") || !isset($_GET['aid']) || hash_equals(iAUTH, (string)$_GET['aid'])) {
            add_to_jquery("
    function errtkApplyFilters() {
        var status = String($('.errtk-stat.active').data('filter') || 'all');
        var query = String($('#errtk-search').val() || '').toLowerCase();
        var severity = String($('#errtk-sev-filter').val() || 'all');
        var statusClasses = {new:'is-new', solved:'is-solved', ignored:'is-ignored'};
        var visible = 0;

        $('.errtk-issue').each(function() {
            var issue = $(this);
            var matchesStatus = status === 'all' || issue.hasClass(statusClasses[status]);
            var matchesQuery = !query || issue.text().toLowerCase().indexOf(query) !== -1;
            var matchesSeverity = severity === 'all' || issue.find('.errtk-severity').hasClass('sev-' + severity);
            var show = matchesStatus && matchesQuery && matchesSeverity;
            issue.toggle(show);
            if (show) visible++;
        });

        $('#errtk-visible-count').text(visible);
    }

    // ── Status update via AJAX ────────────────────────────────────
    $(document).on('click', '.move_error_log', function() {
        var btn   = $(this);
        var eid   = btn.data('id');
        var etype = btn.data('type');
        $.ajax({
            url: '" . ADMIN . "includes/?api=error-logs-updater',
            dataType: 'json',
            method: 'GET',
            data: { 'aidlink': '" . fusion_get_aidlink() . "', 'error_id': eid, 'error_type': etype },
            success: function(e) {
                if (e.status === 'RMD') {
                    var issue = $('#issue-' + e.fusion_error_id);
                    issue.fadeOut(200, function() { issue.remove(); errtkApplyFilters(); });
                    var total = Math.max(0, parseInt($('.errtk-stat[data-filter=\"all\"] .errtk-stat-num').text()) - 1);
                    $('.errtk-stat[data-filter=\"all\"] .errtk-stat-num').text(total);
                    var fromKey = {'0':'new','1':'solved','2':'ignored'}[e.from];
                    if (fromKey) {
                        var fc = Math.max(0, parseInt($('.errtk-stat[data-filter=\"'+fromKey+'\"] .errtk-stat-num').text()) - 1);
                        $('.errtk-stat[data-filter=\"'+fromKey+'\"] .errtk-stat-num').text(fc);
                    }
                } else if (e.status === 'OK') {
                    var issue   = $('#issue-' + e.fusion_error_id);
                    var fromSt  = String(e.from);
                    var toSt    = String(e.to);
                    var stMap   = {'0':'is-new','1':'is-solved','2':'is-ignored'};
                    var keyMap  = {'0':'new','1':'solved','2':'ignored'};
                    issue.removeClass('is-new is-solved is-ignored').addClass(stMap[toSt] || 'is-new');
                    issue.attr('data-status', toSt);
                    issue.find('.errtk-status-label').attr('class', 'errtk-status-label st-'+toSt);
                    issue.find('.errtk-status-dot').attr('class', 'errtk-status-dot st-'+toSt);
                    if (keyMap[fromSt]) {
                        var fc = Math.max(0, parseInt($('.errtk-stat[data-filter=\"'+keyMap[fromSt]+'\"] .errtk-stat-num').text()) - 1);
                        $('.errtk-stat[data-filter=\"'+keyMap[fromSt]+'\"] .errtk-stat-num').text(fc);
                    }
                    if (keyMap[toSt]) {
                        var tc = parseInt($('.errtk-stat[data-filter=\"'+keyMap[toSt]+'\"] .errtk-stat-num').text()) + 1;
                        $('.errtk-stat[data-filter=\"'+keyMap[toSt]+'\"] .errtk-stat-num').text(tc);
                    }
                    issue.find('.errtk-status-label').contents().filter(function() { return this.nodeType === 3; }).last().replaceWith({'0':'New','1':'Solved','2':'Ignored'}[toSt] || 'New');
                    errtkApplyFilters();
                }
            }
        });
    });

    // ── Stat filter tabs ──────────────────────────────────────────
    $('.errtk-stat').on('click', function() {
        $('.errtk-stat').removeClass('active');
        $(this).addClass('active');
        errtkApplyFilters();
    });

    // ── Live search ───────────────────────────────────────────────
    $('#errtk-search').on('input', function() {
        errtkApplyFilters();
    });

    // ── Severity filter ───────────────────────────────────────────
    $('#errtk-sev-filter').on('change', function() {
        errtkApplyFilters();
    });

    // ── Detail page tab switcher ───────────────────────────────────
    $(document).on('click', '.errtk-tab', function() {
        var tab = $(this).data('tab');
        $('.errtk-tab').removeClass('active');
        $(this).addClass('active');
        $('.errtk-tab-pane').removeClass('active');
        $('#errtk-' + tab).addClass('active');
    });

    // ── Footer debug modal ────────────────────────────────────────
    $('a#footer_debug').on('click', function(e) {
        if (window.bootstrap && window.bootstrap.Modal) {
            e.preventDefault();
            window.bootstrap.Modal.getOrCreateInstance(document.getElementById('errorLogModal')).show();
        }
    });
    ");
        }
    }

    /**
     * @param string $source_code
     * @param int $starting_line
     * @param string $error_line
     * @param array $error_message
     * @param null $title
     *
     * @return false|string
     */
    private function printCode($source_code, $starting_line, $error_line = "", array $error_message = [], $title = NULL)
    {
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
        
        $error_message = "<div class='card m-10'>
        <div class='card-header'>".get_svg("bug")." Line " . $error_line . " -- " . timer($error_message['time']) . "</div>
        <div class='card-body'>" . strtr(stripslashes($error_message['text']), ['#' => '<br/>#']) . "</div>";
        foreach ($source_code as $code_line) {
            $code_line = $this->codeWrap($code_line, 145);
            $line_class = ($line_count == $error_line ? "err_tbl-error-line" : "");
            $formatted_code .= "<tr>\n<td style='text-align:right;width:1%;'>" . $line_count . "</td>\n";
            if (preg_match('#<\?(php)?[^[:graph:]]#', $code_line)) {
                $formatted_code .= "<td class='" . $line_class . "'>" . str_replace(['<code>', '</code>'], '', highlight_string($code_line, TRUE)) . "</td>\n</tr>\n";
            } else {
                $formatted_code .= "<td class='" . $line_class . "'>" . preg_replace('#(&lt;\?php&nbsp;)+#', '', str_replace(['<code>', '</code>'], '', highlight_string('<?php ' . $code_line, TRUE))) . "
                </td>\n</tr>\n";
                if ($line_count == $error_line) {
                    $formatted_code .= "<tr>\n<td colspan='2'>" . $error_message . "</td></tr>\n";
                }
            }
            $line_count++;
        }

        $title = !empty($title) ? '<thead><tr><th colspan="2" class="p-10">' . $title . '</th></tr></thead>' : '';

        return "<table class='table-bordered err_tbl-border center' cellspacing='0' cellpadding='0'>" . $title . "<tbody>" . $formatted_code . "</tbody></table>";
    }

    /**
     * @param string $code
     * @param int $maxLength
     *
     * @return string
     */
    private function codeWrap($code, $maxLength = 150)
    {
        $lines = explode("\n", $code);
        $count = count($lines);
        for ($i = 0; $i < $count; ++$i) {
            preg_match('`^\s*`', $code, $matches);
            $lines[$i] = wordwrap($lines[$i], $maxLength, "\n$matches[0]\t", TRUE);
        }

        return implode("\n", $lines);
    }

    /**
     * Inject scoped CSS for the Sentry-like error tracker
     */
    private function injectStyles()
    {
        static $styles_loaded = FALSE;

        if ($styles_loaded) {
            return;
        }
        $styles_loaded = TRUE;

        add_to_head('<style>
/* Error Tracker — structural additions only; all colors via --tblr-* / Bootstrap */
.errtk{border:1px solid var(--tblr-border-color);border-radius:var(--tblr-border-radius);overflow:hidden}
.errtk *{box-sizing:border-box}
.errtk-hd{padding:14px 18px 0;border-bottom:1px solid var(--tblr-border-color)}
.errtk-hd-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:10px}
.errtk-stats{display:flex}
.errtk-stat{padding:7px 18px;text-align:center;border-top:2px solid transparent;cursor:pointer;font-size:12px;user-select:none;transition:.15s;color:var(--tblr-secondary-color);border-right:1px solid var(--tblr-border-color)}
.errtk-stat:last-child{border-right:none}
.errtk-stat:hover{background:var(--tblr-active-bg);color:var(--tblr-body-color)}
.errtk-stat.active{border-top-color:var(--tblr-primary);color:var(--tblr-body-color)}
.errtk-stat.active .errtk-stat-num{color:var(--tblr-primary)}
.errtk-stat-num{display:block;font-size:18px;font-weight:700;line-height:1.2;margin-bottom:2px}
.errtk-tb{display:flex;align-items:center;gap:8px;padding:10px 18px;background:var(--tblr-bg-surface);border-bottom:1px solid var(--tblr-border-color);flex-wrap:wrap}
.errtk-search{flex:1;min-width:180px}
.errtk-issue{display:flex;align-items:stretch;border-bottom:1px solid var(--tblr-border-color);transition:background .12s}
.errtk-issue:hover{background:var(--tblr-active-bg)}
.errtk-issue.is-solved{opacity:.65}
.errtk-issue.is-ignored{opacity:.45}
.errtk-sev-bar{width:4px;flex-shrink:0}
.errtk-sev-bar.sev-error{background:var(--tblr-danger)}
.errtk-sev-bar.sev-warn{background:var(--tblr-warning)}
.errtk-sev-bar.sev-notice{background:var(--tblr-info)}
.errtk-sev-bar.sev-other{background:var(--tblr-secondary)}
.errtk-issue-body{flex:1;padding:12px 14px;min-width:0}
.errtk-issue-top{display:flex;align-items:center;gap:6px;margin-bottom:3px;flex-wrap:wrap}
.errtk-issue-file{font-size:12px;font-weight:600;font-family:var(--tblr-font-monospace,monospace)}
.errtk-issue-line{font-size:11px;font-family:var(--tblr-font-monospace,monospace);color:var(--tblr-secondary-color)}
.errtk-status-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;display:inline-block;vertical-align:middle}
.errtk-status-dot.st-0{background:var(--tblr-danger)}
.errtk-status-dot.st-1{background:var(--tblr-success)}
.errtk-status-dot.st-2{background:var(--tblr-secondary)}
.errtk-issue-msg{font-size:12px;color:var(--tblr-secondary-color);margin-bottom:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:680px}
.errtk-issue-meta{display:flex;align-items:center;gap:12px;font-size:11px;color:var(--tblr-secondary-color);flex-wrap:wrap}
.errtk-issue-meta span{display:inline-flex;align-items:center;gap:3px}
.errtk-issue-meta code{max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;vertical-align:middle}
.errtk-issue-actions{display:flex;flex-direction:column;align-items:flex-end;justify-content:center;padding:12px 14px;gap:6px;min-width:200px}
.errtk-action-row{display:flex;gap:4px;align-items:center;flex-wrap:wrap}
.errtk-pill{background:transparent;border:1px solid var(--tblr-border-color);border-radius:var(--tblr-border-radius-sm);padding:3px 9px;font-size:11px;color:var(--tblr-secondary-color);cursor:pointer;transition:.12s;font-weight:500}
.errtk-pill:hover{border-color:var(--tblr-primary);color:var(--tblr-primary)}
.errtk-pill.p-active.st-0{border-color:var(--tblr-danger);color:var(--tblr-danger);background:rgba(var(--tblr-danger-rgb),.08)}
.errtk-pill.p-active.st-1{border-color:var(--tblr-success);color:var(--tblr-success);background:rgba(var(--tblr-success-rgb),.08)}
.errtk-pill.p-active.st-2{border-color:var(--tblr-secondary);color:var(--tblr-secondary-color)}
.errtk-pill-del{background:transparent;border:1px solid transparent;border-radius:var(--tblr-border-radius-sm);padding:3px 6px;font-size:11px;color:var(--tblr-secondary-color);cursor:pointer;transition:.12s;display:inline-flex;align-items:center}
.errtk-pill-del:hover{border-color:var(--tblr-danger);color:var(--tblr-danger)}
.errtk-link-btn{background:transparent;border:1px solid var(--tblr-border-color);border-radius:var(--tblr-border-radius-sm);padding:3px 9px;font-size:11px;color:var(--tblr-secondary-color);cursor:pointer;transition:.12s;text-decoration:none;display:inline-flex;align-items:center;gap:4px}
.errtk-link-btn:hover{border-color:var(--tblr-primary);color:var(--tblr-primary)}
.errtk-empty{padding:48px 20px;text-align:center;color:var(--tblr-secondary-color)}
.errtk-empty svg{display:block;margin:0 auto 10px;opacity:.3}
.errtk-pager{padding:12px 18px;display:flex;justify-content:center;border-top:1px solid var(--tblr-border-color)}
.errtk-detail-hd{padding:16px 18px;border-bottom:1px solid var(--tblr-border-color)}
.errtk-back{display:inline-flex;align-items:center;gap:5px;font-size:12px;text-decoration:none;margin-bottom:10px;color:var(--tblr-secondary-color)}
.errtk-back:hover{color:var(--tblr-body-color)}
.errtk-detail-title{margin:0 0 6px;font-size:15px;font-weight:600;word-break:break-word;line-height:1.45}
.errtk-detail-file{font-size:12px;font-family:var(--tblr-font-monospace,monospace);display:flex;align-items:center;gap:6px;color:var(--tblr-secondary-color)}
.errtk-section{border-bottom:1px solid var(--tblr-border-color)}
.errtk-section-hd{padding:9px 18px;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--tblr-secondary-color);background:var(--tblr-bg-surface);display:flex;align-items:center;gap:6px;border-bottom:1px solid var(--tblr-border-color)}
.errtk-section-body{padding:16px 18px}
.errtk-meta-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(175px,1fr));gap:14px}
.errtk-meta-item label{display:block;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--tblr-secondary-color);margin-bottom:3px}
.errtk-meta-item span{font-size:13px;font-family:var(--tblr-font-monospace,monospace)}
.errtk-tabs{display:flex;border-bottom:1px solid var(--tblr-border-color);background:var(--tblr-bg-surface)}
.errtk-tab{padding:10px 16px;font-size:12px;font-weight:500;color:var(--tblr-secondary-color);cursor:pointer;border-bottom:2px solid transparent;display:inline-flex;align-items:center;gap:5px;user-select:none;transition:.12s}
.errtk-tab:hover{color:var(--tblr-body-color);background:var(--tblr-active-bg)}
.errtk-tab.active{color:var(--tblr-primary);border-bottom-color:var(--tblr-primary)}
.errtk-tab-pane{display:none}
.errtk-tab-pane.active{display:block}
.errtk-code-wrap{overflow:auto;font-size:12px;font-family:var(--tblr-font-monospace,monospace);background:var(--tblr-bg-surface-dark)}
.errtk-code-wrap table{width:100%;border-collapse:collapse}
.errtk-code-wrap td{padding:1.5px 6px;vertical-align:top;line-height:1.65}
.errtk-code-wrap .ln{width:1%;text-align:right;padding-right:14px;min-width:38px;font-size:11px;user-select:none;opacity:.35}
.errtk-code-wrap .error-line{background:rgba(var(--tblr-danger-rgb),.12)}
.errtk-code-wrap .error-line .ln{opacity:1;color:var(--tblr-danger)}
.errtk-code-msg{border-left:3px solid var(--tblr-danger);padding:8px 12px;font-size:12px;background:rgba(var(--tblr-danger-rgb),.07);margin:0;color:var(--tblr-danger)}
/* Premium observability console */
.errtk{--errtk-ink:#172033;--errtk-muted:#667085;--errtk-line:#e4e8ef;--errtk-surface:#f7f8fa;--errtk-panel:#fff;background:var(--errtk-panel);border-color:var(--errtk-line);border-radius:8px;box-shadow:0 12px 32px rgba(20,29,45,.08)}
.errtk-hd{padding:24px 24px 0;background:var(--errtk-panel);border-color:var(--errtk-line)}
.errtk-hd-top{align-items:flex-start;margin-bottom:22px;gap:24px}
.errtk-title-block{min-width:240px}
.errtk-eyebrow{display:block;margin-bottom:7px;color:#7b8496;font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
.errtk-title-block h2{display:flex;align-items:center;gap:10px;margin:0;color:var(--errtk-ink);font-size:21px;font-weight:700;letter-spacing:0;line-height:1.25}
.errtk-title-icon{display:inline-flex;width:34px;height:34px;align-items:center;justify-content:center;border:1px solid #dce2ea;border-radius:7px;background:#f4f6f9;color:#334155;font-size:14px}
.errtk-health{display:flex;align-items:center;gap:7px;margin:9px 0 0 44px;color:var(--errtk-muted);font-size:11px;font-weight:600}
.errtk-health>span{width:7px;height:7px;border-radius:50%;background:#22a06b;box-shadow:0 0 0 3px rgba(34,160,107,.12)}
.errtk-health.has-open>span{background:#e5484d;box-shadow:0 0 0 3px rgba(229,72,77,.12)}
.errtk-maintenance{display:flex;align-items:center;gap:8px;padding-top:4px}
.errtk-maintenance .form-select{width:auto;min-width:132px}
.errtk-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));margin:0 -24px;background:var(--errtk-surface);border-top:1px solid var(--errtk-line)}
.errtk-stat{position:relative;display:flex;min-width:0;flex-direction:column;align-items:flex-start;padding:17px 22px;border:0;border-right:1px solid var(--errtk-line);border-top:3px solid transparent;background:transparent;color:var(--errtk-muted);text-align:left;cursor:pointer;transition:background .16s ease,border-color .16s ease}
.errtk-stat:last-child{border-right:0}
.errtk-stat:hover{background:#fff;color:var(--errtk-ink)}
.errtk-stat.active{border-top-color:#2f6feb;background:#fff;color:var(--errtk-ink)}
.errtk-stat-label{font-size:11px;font-weight:700;color:#6f7889}
.errtk-stat-num{display:block;margin:5px 0 4px;color:var(--errtk-ink);font-size:27px;font-weight:750;line-height:1}
.errtk-stat-num.is-danger{color:#d63939}.errtk-stat-num.is-success{color:#16835f}.errtk-stat-num.is-muted{color:#6b7280}
.errtk-stat-note{display:flex;max-width:100%;align-items:center;gap:5px;color:#8991a1;font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.errtk-tb{display:grid;grid-template-columns:minmax(220px,1fr) auto 170px auto;gap:10px;padding:13px 18px;background:#fff;border-color:var(--errtk-line)}
.errtk-search-wrap{position:relative;min-width:0}
.errtk-search-wrap>i{position:absolute;z-index:2;top:50%;left:12px;color:#929bad;transform:translateY(-50%)}
.errtk-search{height:36px;padding-left:34px;border-color:#dce1e8;border-radius:6px;background:#f9fafb;font-size:12px}
.errtk-search:focus{border-color:#7aa7f7;background:#fff;box-shadow:0 0 0 3px rgba(47,111,235,.1)}
.errtk-filter-label{display:flex;align-items:center;gap:6px;color:#7b8496;font-size:11px;font-weight:700}
#errtk-sev-filter{width:170px;height:36px;border-radius:6px}
.errtk-visible-count{display:flex;align-items:center;gap:4px;padding-left:8px;color:#8a93a3;font-size:11px;white-space:nowrap}
.errtk-visible-count strong{color:var(--errtk-ink)}
.errtk-list{background:#fff}
.errtk-issue{display:grid;grid-template-columns:40px minmax(0,1fr) auto;align-items:stretch;border-color:var(--errtk-line);background:#fff;opacity:1;transition:background .16s ease,box-shadow .16s ease}
.errtk-issue:hover{position:relative;z-index:1;background:#fbfcfe;box-shadow:inset 3px 0 #2f6feb}
.errtk-issue.is-solved,.errtk-issue.is-ignored{opacity:1}
.errtk-issue.is-solved .errtk-issue-msg,.errtk-issue.is-ignored .errtk-issue-msg{color:#667085}
.errtk-severity{display:flex;width:40px;align-items:flex-start;justify-content:center;padding-top:18px;border-right:1px solid var(--errtk-line);background:#fafbfc;color:#98a2b3;font-size:10px}
.errtk-severity.sev-error{color:#d63939}.errtk-severity.sev-warn{color:#c47c10}.errtk-severity.sev-notice{color:#2274a5}.errtk-severity.sev-other{color:#667085}
.errtk-issue-body{padding:14px 16px 13px}
.errtk-issue-kicker{display:flex;align-items:center;gap:9px;margin-bottom:5px}
.errtk-level{font-size:9px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.errtk-level.sev-error{color:#c9363e}.errtk-level.sev-warn{color:#a76508}.errtk-level.sev-notice{color:#1d6b91}.errtk-level.sev-other{color:#667085}
.errtk-status-label,.errtk-age{display:inline-flex;align-items:center;gap:5px;color:#8a93a3;font-size:10px;font-weight:600}
.errtk-age{margin-left:auto}
.errtk-status-dot{width:6px;height:6px}
.errtk-issue-msg{display:block;max-width:none;margin:0 0 7px;color:var(--errtk-ink);font-size:13px;font-weight:650;line-height:1.4;white-space:nowrap;overflow:hidden;text-decoration:none;text-overflow:ellipsis}
.errtk-issue-msg:hover{color:#245fca;text-decoration:none}
.errtk-issue-file,.errtk-issue-route{display:flex;min-width:0;align-items:center;gap:6px;color:#7d8798;font-size:10px}
.errtk-issue-file{margin-bottom:4px}
.errtk-issue-file code,.errtk-issue-route code{display:block;max-width:520px;padding:0;background:transparent;color:#596273;font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.errtk-issue-file span{padding-left:6px;border-left:1px solid #d7dce4;white-space:nowrap}
.errtk-issue-actions{min-width:226px;align-items:flex-end;padding:14px 16px;border-left:1px solid var(--errtk-line);background:#fcfcfd}
.errtk-quick-actions{display:flex;gap:5px}
.errtk-icon-btn{display:inline-flex;width:29px;height:29px;align-items:center;justify-content:center;padding:0;border:1px solid #dce1e8;border-radius:6px;background:#fff;color:#667085;font-size:11px;cursor:pointer;transition:.14s}
.errtk-icon-btn:hover{border-color:#9ab6e7;color:#245fca;background:#f6f9ff}
.errtk-icon-btn.errtk-delete:hover{border-color:#e7a5aa;color:#c9363e;background:#fff7f7}
.errtk-status-control{display:flex;overflow:hidden;border:1px solid #dce1e8;border-radius:6px;background:#fff}
.errtk-pill{min-width:51px;padding:5px 8px;border:0;border-right:1px solid #dce1e8;border-radius:0;background:#fff;color:#788294;font-size:10px;font-weight:700}
.errtk-pill:last-child{border-right:0}
.errtk-pill:hover{background:#f5f7fa;color:#344054}
.errtk-pill.p-active.st-0{border-color:#dce1e8;background:#fff0f1;color:#bd3038}.errtk-pill.p-active.st-1{border-color:#dce1e8;background:#eaf8f2;color:#137557}.errtk-pill.p-active.st-2{background:#eef1f5;color:#525d6d}
.errtk-empty{background:#fff}
.errtk-footer-signal{display:flex;align-items:center;gap:9px;color:#7c8596;font-size:11px}
.errtk-footer-link{display:inline-flex;align-items:center;gap:7px;color:inherit;text-decoration:none}
.errtk-footer-link:hover{color:var(--tblr-primary);text-decoration:none}
.errtk-footer-icon{display:inline-flex;width:25px;height:25px;align-items:center;justify-content:center;border:1px solid var(--tblr-border-color);border-radius:6px;background:var(--tblr-bg-surface);color:var(--tblr-danger)}
.errtk-footer-count{padding-left:9px;border-left:1px solid var(--tblr-border-color);font-variant-numeric:tabular-nums}
.errtk-modal{overflow:hidden;border:0;border-radius:8px;box-shadow:0 24px 70px rgba(15,23,42,.24)}
.errtk-modal .modal-header{padding:14px 18px;border-color:var(--errtk-line);background:#fff}
.errtk-modal .modal-title{display:flex;align-items:center;gap:9px;color:var(--errtk-ink);font-size:14px;font-weight:700}
.errtk-modal .modal-body{padding:0;background:#f4f6f8}
.errtk-modal .modal-body>.errtk{border:0;border-radius:0;box-shadow:none}
.errtk-modal .modal-footer{padding:10px 18px;border-color:var(--errtk-line);background:#fff}
[data-bs-theme=dark] .errtk{--errtk-ink:#eef2f7;--errtk-muted:#a0a8b7;--errtk-line:#303846;--errtk-surface:#1b2029;--errtk-panel:#202631;box-shadow:0 14px 36px rgba(0,0,0,.28)}
[data-bs-theme=dark] .errtk-title-icon,[data-bs-theme=dark] .errtk-search,[data-bs-theme=dark] .errtk-stat:hover,[data-bs-theme=dark] .errtk-stat.active,[data-bs-theme=dark] .errtk-tb,[data-bs-theme=dark] .errtk-list,[data-bs-theme=dark] .errtk-issue,[data-bs-theme=dark] .errtk-icon-btn,[data-bs-theme=dark] .errtk-status-control,[data-bs-theme=dark] .errtk-pill,[data-bs-theme=dark] .errtk-empty,[data-bs-theme=dark] .errtk-modal .modal-header,[data-bs-theme=dark] .errtk-modal .modal-footer{background:var(--errtk-panel);border-color:var(--errtk-line)}
[data-bs-theme=dark] .errtk-severity,[data-bs-theme=dark] .errtk-issue-actions{background:#1c222c;border-color:var(--errtk-line)}
[data-bs-theme=dark] .errtk-search:focus{background:#252c37}
[data-bs-theme=dark] .errtk-issue:hover{background:#242b36}
[data-bs-theme=dark] .errtk-issue-file code,[data-bs-theme=dark] .errtk-issue-route code{color:#aeb6c3}
[data-bs-theme=dark] .errtk-modal .modal-body{background:#171b22}
@media(max-width:992px){.errtk-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.errtk-stat:nth-child(2){border-right:0}.errtk-stat:nth-child(-n+2){border-bottom:1px solid var(--errtk-line)}.errtk-issue{grid-template-columns:36px minmax(0,1fr)}.errtk-severity{width:36px}.errtk-issue-actions{grid-column:1/-1;min-width:0;flex-direction:row;align-items:center;justify-content:space-between;border-top:1px solid var(--errtk-line);border-left:0;padding:9px 14px 9px 52px}}
@media(max-width:768px){.errtk-hd{padding:18px 16px 0}.errtk-hd-top{display:block}.errtk-maintenance{margin-top:16px}.errtk-stats{margin:0 -16px}.errtk-tb{grid-template-columns:1fr 150px}.errtk-filter-label,.errtk-visible-count{display:none}#errtk-sev-filter{width:150px}.errtk-stat{padding:14px}.errtk-stat-note{display:none}.errtk-issue-actions{padding-left:14px}.errtk-status-control{overflow-x:auto}.errtk-modal .errtk-hd-top form{width:100%}}
@media(max-width:520px){.errtk-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.errtk-tb{grid-template-columns:1fr}.errtk-search-wrap,#errtk-sev-filter{width:100%}.errtk-issue{grid-template-columns:30px minmax(0,1fr)}.errtk-severity{width:30px}.errtk-age{display:none}.errtk-issue-actions{align-items:stretch;flex-direction:column}.errtk-quick-actions{justify-content:flex-end}.errtk-status-control{width:100%}.errtk-pill{flex:1}.errtk-maintenance{align-items:stretch;flex-direction:column}.errtk-maintenance .form-select{width:100%}}
/* shadcn-inspired console overrides */
.errtk{--errtk-ink:var(--tblr-body-color,#09090b);--errtk-muted:var(--tblr-secondary-color,#71717a);--errtk-line:var(--tblr-border-color,#e4e4e7);--errtk-panel:var(--tblr-bg-surface,#fff);--errtk-subtle:var(--tblr-bg-surface-secondary,#fafafa);overflow:visible;border:1px solid var(--errtk-line);border-radius:8px;background:var(--errtk-panel);box-shadow:none;color:var(--errtk-ink)}
.errtk-hd{padding:20px;border-bottom:0;background:transparent}
.errtk-hd-top{align-items:center;margin-bottom:20px;gap:16px}
.errtk-title-block{min-width:0}
.errtk-title-block h2{display:block;margin:0;color:var(--errtk-ink);font-size:18px;font-weight:600;line-height:1.4}
.errtk-health{margin:4px 0 0;color:var(--errtk-muted);font-size:12px;font-weight:400}
.errtk-health>span{width:6px;height:6px;box-shadow:none}
.errtk-maintenance{padding:0}
.errtk-maintenance .form-select,.errtk-maintenance .btn{height:36px;border-radius:6px;font-size:12px}
.errtk-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:0;border:0;background:transparent}
.errtk-stat{display:flex;min-width:0;min-height:92px;align-items:flex-start;padding:14px 16px;border:1px solid var(--errtk-line);border-radius:7px;background:var(--errtk-panel);color:var(--errtk-muted);text-align:left;box-shadow:none}
.errtk-stat:last-child{border-right:1px solid var(--errtk-line)}
.errtk-stat:hover{border-color:color-mix(in srgb,var(--errtk-ink) 25%,var(--errtk-line));background:var(--errtk-panel);color:var(--errtk-ink)}
.errtk-stat.active{border:1px solid var(--errtk-ink);background:var(--errtk-panel);color:var(--errtk-ink);box-shadow:0 0 0 1px var(--errtk-ink)}
.errtk-stat-label{font-size:11px;font-weight:500;color:var(--errtk-muted)}
.errtk-stat-num{margin:7px 0 5px;color:var(--errtk-ink);font-size:24px;font-weight:600;line-height:1}
.errtk-stat-note{color:var(--errtk-muted);font-size:10px}
.errtk-tb{display:grid;grid-template-columns:minmax(220px,1fr) auto 170px auto;gap:10px;padding:12px 20px;border-top:1px solid var(--errtk-line);border-bottom:1px solid var(--errtk-line);background:var(--errtk-panel)}
.errtk-search{height:36px;border:1px solid var(--errtk-line);border-radius:6px;background:var(--errtk-panel);color:var(--errtk-ink);font-size:12px;box-shadow:none}
.errtk-search:focus,#errtk-sev-filter:focus,.errtk-icon-btn:focus-visible,.errtk-stat:focus-visible{border-color:var(--errtk-ink);outline:2px solid color-mix(in srgb,var(--errtk-ink) 18%,transparent);outline-offset:2px;box-shadow:none}
#errtk-sev-filter{height:36px;border-color:var(--errtk-line);border-radius:6px;background-color:var(--errtk-panel);font-size:12px}
.errtk-filter-label,.errtk-visible-count{color:var(--errtk-muted);font-size:11px}
.errtk-list{overflow:visible;border-radius:0 0 8px 8px;background:var(--errtk-panel)}
.errtk-list .table-responsive{overflow:visible}
.errtk-table{width:100%;border-collapse:collapse;color:var(--errtk-ink);table-layout:fixed}
.errtk-table th{padding:10px 14px;border-bottom:1px solid var(--errtk-line);background:var(--errtk-subtle);color:var(--errtk-muted);font-size:10px;font-weight:600;letter-spacing:.04em;text-align:left;text-transform:uppercase}
.errtk-table th:nth-child(1){width:36%}.errtk-table th:nth-child(2){width:31%}.errtk-table th:nth-child(3){width:12%}.errtk-table th:nth-child(4){width:14%}.errtk-table th:nth-child(5){width:52px}
.errtk-table td{padding:12px 14px;border-bottom:1px solid var(--errtk-line);vertical-align:middle}
.errtk-table tbody tr:last-child td{border-bottom:0}
.errtk-table .errtk-issue{display:table-row;background:var(--errtk-panel);opacity:1;box-shadow:none;transition:background-color .15s ease}
.errtk-table .errtk-issue:hover{background:var(--errtk-subtle);box-shadow:none}
.errtk-issue-summary{display:flex;min-width:0;align-items:flex-start;gap:10px}
.errtk-severity{display:inline-flex;width:28px;height:28px;flex:0 0 28px;align-items:center;justify-content:center;padding:0;border:1px solid var(--errtk-line);border-radius:6px;background:var(--errtk-panel);font-size:9px}
.errtk-summary-copy{min-width:0;padding-top:1px}
.errtk-issue-msg{display:block;margin:0 0 4px;color:var(--errtk-ink);font-size:12px;font-weight:500;line-height:1.4;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.errtk-issue-msg:hover{color:var(--errtk-ink);text-decoration:underline;text-underline-offset:3px}
.errtk-level{display:block;font-size:9px;font-weight:600;letter-spacing:.04em}
.errtk-source{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:2px 8px;min-width:0}
.errtk-source code{overflow:hidden;padding:0;background:transparent;color:var(--errtk-ink);font-size:10px;text-overflow:ellipsis;white-space:nowrap}
.errtk-source>span{color:var(--errtk-muted);font-size:10px;white-space:nowrap}
.errtk-source small{grid-column:1/-1;overflow:hidden;color:var(--errtk-muted);font-size:10px;text-overflow:ellipsis;white-space:nowrap}
.errtk-status-label{display:inline-flex;align-items:center;gap:6px;padding:3px 8px;border:1px solid var(--errtk-line);border-radius:999px;background:var(--errtk-panel);color:var(--errtk-ink);font-size:10px;font-weight:500;white-space:nowrap}
.errtk-status-label.st-0{border-color:color-mix(in srgb,var(--tblr-danger,#dc2626) 28%,var(--errtk-line));background:color-mix(in srgb,var(--tblr-danger,#dc2626) 7%,var(--errtk-panel))}
.errtk-status-label.st-1{border-color:color-mix(in srgb,var(--tblr-success,#16a34a) 28%,var(--errtk-line));background:color-mix(in srgb,var(--tblr-success,#16a34a) 7%,var(--errtk-panel))}
.errtk-age{color:var(--errtk-muted);font-size:11px;white-space:nowrap}
.errtk-actions-cell{text-align:right}
.errtk-icon-btn{display:inline-flex;width:32px;height:32px;align-items:center;justify-content:center;padding:0;border:1px solid transparent;border-radius:6px;background:transparent;color:var(--errtk-muted);cursor:pointer}
.errtk-icon-btn:hover{border-color:var(--errtk-line);background:var(--errtk-subtle);color:var(--errtk-ink)}
.errtk-menu{min-width:190px;padding:4px;border:1px solid var(--errtk-line);border-radius:7px;background:var(--errtk-panel);box-shadow:0 8px 24px rgba(0,0,0,.12)}
.errtk-menu .dropdown-item{display:flex;align-items:center;gap:8px;padding:7px 9px;border-radius:4px;color:var(--errtk-ink);font-size:11px}
.errtk-menu .dropdown-item:hover,.errtk-menu .dropdown-item:focus{background:var(--errtk-subtle);color:var(--errtk-ink)}
.errtk-menu .dropdown-divider{margin:4px -4px;border-color:var(--errtk-line)}
.errtk-clipboard{position:absolute;width:1px;height:1px;overflow:hidden;padding:0;border:0;clip:rect(0,0,0,0)}
.errtk-empty{padding:48px 20px;background:var(--errtk-panel);color:var(--errtk-muted)}
.errtk-empty>i{display:block;margin-bottom:10px;font-size:24px}
.errtk-empty h4{margin-bottom:4px;color:var(--errtk-ink);font-size:13px;font-weight:600}
.errtk-modal{border:1px solid var(--errtk-line);border-radius:8px;background:var(--errtk-panel);box-shadow:0 24px 64px rgba(0,0,0,.18)}
.errtk-modal .modal-header,.errtk-modal .modal-footer{border-color:var(--errtk-line);background:var(--errtk-panel)}
.errtk-modal .modal-body{background:var(--errtk-panel)}
[data-bs-theme=dark] .errtk,[data-bs-theme=dark] .errtk-table .errtk-issue,[data-bs-theme=dark] .errtk-table .errtk-issue:hover,[data-bs-theme=dark] .errtk-tb,[data-bs-theme=dark] .errtk-list,[data-bs-theme=dark] .errtk-stat,[data-bs-theme=dark] .errtk-stat:hover,[data-bs-theme=dark] .errtk-stat.active,[data-bs-theme=dark] .errtk-search,[data-bs-theme=dark] .errtk-severity,[data-bs-theme=dark] .errtk-status-label,[data-bs-theme=dark] .errtk-icon-btn,[data-bs-theme=dark] .errtk-empty,[data-bs-theme=dark] .errtk-menu,[data-bs-theme=dark] .errtk-modal .modal-body{background:var(--errtk-panel)}
@media(max-width:992px){.errtk-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.errtk-stat:nth-child(2){border-right:1px solid var(--errtk-line)}.errtk-stat:nth-child(-n+2){border-bottom:1px solid var(--errtk-line)}.errtk-table th:nth-child(2),.errtk-table td:nth-child(2){display:none}.errtk-table th:nth-child(1){width:54%}.errtk-table th:nth-child(3){width:18%}.errtk-table th:nth-child(4){width:18%}}
@media(max-width:768px){.errtk-hd{padding:16px}.errtk-hd-top{display:block}.errtk-maintenance{margin-top:14px}.errtk-stats{margin:0}.errtk-tb{grid-template-columns:minmax(0,1fr) 140px;padding:10px 16px}.errtk-filter-label,.errtk-visible-count{display:none}#errtk-sev-filter{width:140px}.errtk-table th:nth-child(4),.errtk-table td:nth-child(4){display:none}.errtk-table th:nth-child(1){width:65%}.errtk-table th:nth-child(3){width:25%}.errtk-table td{padding:11px 12px}.errtk-icon-btn{width:44px;height:44px}}
@media(max-width:520px){.errtk-stats{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.errtk-stat{min-height:78px;padding:12px}.errtk-stat-note{display:none}.errtk-tb{grid-template-columns:1fr}.errtk-search-wrap,#errtk-sev-filter{width:100%}.errtk-table th:nth-child(3),.errtk-table td:nth-child(3){display:none}.errtk-table th:nth-child(1){width:auto}.errtk-issue-msg{font-size:11px}.errtk-maintenance{align-items:stretch;flex-direction:column}.errtk-maintenance .form-select{width:100%}}
@media(prefers-reduced-motion:reduce){.errtk *{scroll-behavior:auto!important;transition:none!important}}
</style>');
    }

    /**
     * Sentry-style issue detail page
     *
     * @return string
     */
    private function showErrorDetail()
    {
        $aidlink = fusion_get_aidlink();
        $locale  = self::$locale;

        if (!defined('no_debugger')) {
            define('no_debugger', 1);
        }

        $data = dbarray(dbquery("SELECT * FROM " . DB_ERRORS . " WHERE error_id=:eid LIMIT 1", [':eid' => $this->error_id]));
        if (!$data) {
            redirect(ADMIN . "errors.php" . $aidlink);
        }

        foreach ($data as $key => $value) {
            $data[$key] = descript($value);
        }

        $sev_class   = $this->getSeverityClass($data['error_level']);
        $badge_class = $this->getSeverityBadgeClass($data['error_level']);
        $type_label  = $this->getErrorTypeLabel($data['error_level']);
        $type_desc   = $this->getErrorTypes($data['error_level']);
        $file_title = self::getMaxFolders($data['error_file'], 4);
        $error_msg  = strtr(stripslashes($data['error_message']), ['#' => '<br>']);

        $file_lines  = is_file($data['error_file']) ? file($data['error_file']) : [];
        $line_start  = max($data['error_line'] - 10, 1);
        $line_end    = min($data['error_line'] + 10, count($file_lines));
        $src_snippet = implode("", array_slice($file_lines, $line_start - 1, $line_end - $line_start + 1));
        $page_path   = BASEDIR . ltrim($data['error_page'], '/');
        $page_src    = is_file($page_path) ? file_get_contents($page_path) : '';
        $list_url    = ADMIN . "errors.php" . $aidlink . "&rowstart=" . $this->rowstart;

        $icon_back = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>';
        $icon_file = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>';
        $icon_info = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
        $icon_code = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>';

        $html  = '<div class="errtk">';

        // ── Header ─────────────────────────────────────────────────
        $html .= '<div class="errtk-detail-hd">';
        $html .= '<a href="' . $list_url . '" class="errtk-back">' . $icon_back . ' Back to Issues</a>';
        $html .= '<div style="display:flex;align-items:flex-start;gap:10px;flex-wrap:wrap">';
        $html .= '<span class="' . $badge_class . '" style="margin-top:3px">' . htmlspecialchars($type_label) . '</span>';
        $html .= '<div style="flex:1;min-width:0">';
        $html .= '<h2 class="errtk-detail-title">' . $error_msg . '</h2>';
        $html .= '<div class="errtk-detail-file">' . $icon_file . ' ' . htmlspecialchars($file_title) . '<span class="text-secondary"> &nbsp;·&nbsp; Line ' . (int)$data['error_line'] . '</span></div>';
        $html .= '</div></div>';
        $html .= '</div>';

        $html .= '<div class="errtk-detail-body">';

        // ── Event Details ──────────────────────────────────────────
        $html .= '<div class="errtk-section">';
        $html .= '<div class="errtk-section-hd">' . $icon_info . ' Event Details</div>';
        $html .= '<div class="errtk-section-body">';
        $html .= '<div class="errtk-meta-grid">';

        $meta_items = [
            'File'       => htmlspecialchars($file_title),
            'Line'       => (int)$data['error_line'],
            'Page'       => htmlspecialchars(self::getMaxFolders($data['error_page'], 3)),
            'User Level' => (int)$data['error_user_level'],
            'IP Address' => htmlspecialchars($data['error_user_ip']),
            'Timestamp'  => showdate("longdate", $data['error_timestamp']),
            'Age'        => timer($data['error_timestamp']),
        ];
        foreach ($meta_items as $label => $value) {
            $html .= '<div class="errtk-meta-item"><label>' . $label . '</label><span>' . $value . '</span></div>';
        }

        if ($type_desc) {
            $html .= '<div class="errtk-meta-item" style="grid-column:1/-1"><label>Severity</label>';
            $html .= '<span><span class="' . $badge_class . '">' . htmlspecialchars($type_label) . '</span> &nbsp;<span class="text-secondary" style="font-size:12px">' . htmlspecialchars($type_desc) . '</span></span></div>';
        }

        $html .= '</div>';

        // Status form
        $html .= '<div style="margin-top:18px">';
        $html .= '<div class="errtk-meta-item" style="margin-bottom:8px"><label>Change Status</label></div>';
        $html .= openform('logform', 'post', ADMIN . 'errors.php' . $aidlink . "&rowstart=" . $this->rowstart . "&error_id=" . $data['error_id']);
        $html .= form_hidden('error_id', '', $data['error_id']);
        $html .= '<div class="errtk-status-form">';
        $html .= '<select name="error_status" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">';
        foreach ($this->getErrorLogTypes() as $k => $v) {
            $sel = ($data['error_status'] == $k) ? ' selected' : '';
            $html .= '<option value="' . $k . '"' . $sel . '>' . htmlspecialchars($v) . '</option>';
        }
        $html .= '</select>';
        $html .= '</div>';
        $html .= closeform();
        $html .= '</div>';

        $html .= '</div></div>'; // section-body, section

        // ── Code viewer with tabs ──────────────────────────────────
        $html .= '<div class="errtk-section">';
        $html .= '<div class="errtk-tabs">';
        $html .= '<div class="errtk-tab active" data-tab="src-code">' . $icon_code . ' Source Code <span style="font-size:10px;color:var(--tblr-secondary-color);margin-left:4px">Lines ' . $line_start . '–' . $line_end . '</span></div>';
        $html .= '<div class="errtk-tab" data-tab="src-page">' . $icon_file . ' Source Page <span style="font-size:10px;color:var(--tblr-secondary-color);margin-left:4px">' . htmlspecialchars(self::getMaxFolders($data['error_page'], 2)) . '</span></div>';
        $html .= '</div>';

        $html .= '<div id="errtk-src-code" class="errtk-tab-pane active">';
        $html .= '<div class="errtk-code-wrap">';
        $html .= $this->printCodeSentry($src_snippet, $line_start, (int)$data['error_line'], $error_msg);
        $html .= '</div></div>';

        $html .= '<div id="errtk-src-page" class="errtk-tab-pane">';
        $html .= '<div class="errtk-code-wrap">';
        if ($page_src !== '') {
            $html .= $this->printCodeSentry($page_src, 1, null, '');
        } else {
            $html .= '<div style="padding:18px;color:var(--tblr-secondary-color);font-size:12px">Source file not found or not readable.</div>';
        }
        $html .= '</div></div>';

        $html .= '</div>'; // section
        $html .= '</div>'; // detail-body
        $html .= '</div>'; // errtk

        $this->errorJs();
        return $html;
    }

    /**
     * Render a syntax-highlighted code table for the Sentry viewer
     *
     * @param string   $source_code
     * @param int      $starting_line
     * @param int|null $error_line
     * @param string   $error_message
     *
     * @return string
     */
    private function printCodeSentry($source_code, $starting_line, $error_line = null, $error_message = '')
    {
        if (is_array($source_code)) {
            return '';
        }

        $lines      = explode("\n", str_replace(["\r\n", "\r"], "\n", $source_code));
        $line_count = $starting_line;
        $out        = '<table>';

        foreach ($lines as $code_line) {
            $code_line = $this->codeWrap($code_line, 145);
            $is_error  = ($error_line !== null && $line_count == $error_line);
            $row_class = $is_error ? ' class="error-line"' : '';

            if (preg_match('#<\?(php)?[^[:graph:]]#', $code_line)) {
                $highlighted = str_replace(['<code>', '</code>'], '', highlight_string($code_line, TRUE));
            } else {
                $highlighted = preg_replace('#(&lt;\?php&nbsp;)+#', '', str_replace(['<code>', '</code>'], '', highlight_string('<?php ' . $code_line, TRUE)));
            }

            $out .= "<tr{$row_class}><td class='ln'>{$line_count}</td><td>{$highlighted}</td></tr>";

            if ($is_error && $error_message !== '') {
                $out .= "<tr><td></td><td><div class='errtk-code-msg'>" . $error_message . "</div></td></tr>";
            }

            $line_count++;
        }

        return $out . '</table>';
    }

    /**
     * Map an error level integer to a CSS severity class
     *
     * @param int $level
     *
     * @return string sev-error | sev-warn | sev-notice | sev-other
     */
    private function getSeverityClass($level)
    {
        $level   = (int)$level;
        $errors  = [self::E_ERROR, self::E_CORE_ERROR, self::E_COMPILE_ERROR, self::E_USER_ERROR, self::E_PARSE];
        $warns   = [self::E_WARNING, self::E_CORE_WARNING, self::E_COMPILE_WARNING, self::E_USER_WARNING];
        $notices = [self::E_NOTICE, self::E_USER_NOTICE, self::E_STRICT];

        if (in_array($level, $errors))  return 'sev-error';
        if (in_array($level, $warns))   return 'sev-warn';
        if (in_array($level, $notices)) return 'sev-notice';
        return 'sev-other';
    }

    private function getSeverityBadgeClass($level)
    {
        $level   = (int)$level;
        $errors  = [self::E_ERROR, self::E_CORE_ERROR, self::E_COMPILE_ERROR, self::E_USER_ERROR, self::E_PARSE];
        $warns   = [self::E_WARNING, self::E_CORE_WARNING, self::E_COMPILE_WARNING, self::E_USER_WARNING];
        $notices = [self::E_NOTICE, self::E_USER_NOTICE, self::E_STRICT];

        if (in_array($level, $errors))  return 'badge text-bg-danger';
        if (in_array($level, $warns))   return 'badge text-bg-warning';
        if (in_array($level, $notices)) return 'badge text-bg-info';
        return 'badge text-bg-secondary';
    }

    /**
     * Map an error level integer to its PHP constant name
     *
     * @param int $level
     *
     * @return string
     */
    private function getErrorTypeLabel($level)
    {
        $map = [
            self::E_ERROR         => 'E_ERROR',
            self::E_WARNING       => 'E_WARNING',
            self::E_PARSE         => 'E_PARSE',
            self::E_NOTICE        => 'E_NOTICE',
            self::E_CORE_ERROR    => 'E_CORE_ERROR',
            self::E_CORE_WARNING  => 'E_CORE_WARNING',
            self::E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            self::E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            self::E_USER_ERROR    => 'E_USER_ERROR',
            self::E_USER_WARNING  => 'E_USER_WARNING',
            self::E_USER_NOTICE   => 'E_USER_NOTICE',
            self::E_ALL           => 'E_ALL',
            self::E_STRICT        => 'E_STRICT',
        ];
        return $map[(int)$level] ?? 'E_UNKNOWN';
    }

    /**
     * Use this function to show error logs
     */
    public function showFooterErrors()
    {
        $locale     = self::$locale;
        $aidlink    = fusion_get_aidlink();
        $student_id = fusion_get_userdata('student_id') ??0;
        
        $html = '';
        if ((iADMIN && checkrights("ERRO") || $student_id == 1) && (count($this->errors) || count($this->new_errors))) {
            $this->injectStyles();

            $logged_count = count($this->errors);
            $new_count = count($this->new_errors);
            $footer_link = str_replace(["[ERROR_LOG_URL]", "[/ERROR_LOG_URL]"],
                [
                    "<a id='footer_debug' class='errtk-footer-link' href='" . ADMIN . "errors.php" . $aidlink . "' data-bs-toggle='modal' data-bs-target='#errorLogModal'><span class='errtk-footer-icon'><i class='fa fa-line-chart' aria-hidden='true'></i></span><span>",
                    "</span></a>"
                ], $locale['err_101']);

            $html .= "<div class='errtk-footer-signal'>";
            $html .= $footer_link;
            $html .= "<span class='errtk-footer-count'><strong>" . $new_count . "</strong> new &nbsp; <strong>" . $logged_count . "</strong> tracked</span>";
            $html .= "</div>";

            $cHtml = '<div class="modal fade" id="errorLogModal" tabindex="-1" aria-labelledby="errorLogModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content errtk-modal">
                        <div class="modal-header">
                            <h5 class="modal-title" id="errorLogModalLabel">' . $locale['ERROR_464'] . '</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">' . $this->getErrorLogs() . '</div>
                        <div class="modal-footer">
                            <a class="btn btn-sm btn-primary" href="' . ADMIN . 'errors.php' . $aidlink . '"><i class="fa fa-external-link fa-fw"></i> Open full console</a>
                        </div>
                    </div>
                </div>
              </div>';
            add_to_footer($cHtml);
        }

        return $html;
    }
}
