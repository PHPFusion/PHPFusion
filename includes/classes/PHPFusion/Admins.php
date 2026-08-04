<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Admins.php
| Author: Frederick MC Chan
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

/**
 * Class Admin
 * This class is called in templates/admin_header.php
 * Determine how to we set variables on 3rd party script
 */
class Admins
{

    private static $instance = NULL;
    private static $admin_pages = [];
    private static $locale = [];
    private static $admin_sections = [];
    /**
     * @var array - default section icons
     */
    public $admin_section_icons = [
        '0' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" width="20" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M5 12l-2 0l9 -9l9 9l-2 0"></path>
                            <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"></path>
                            <path d="M10 12h4v4h-4z"></path>
                        </svg>',
        '1' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" width="20" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"></path>
                            <path d="M4 12l16 0"></path>
                            <path d="M12 4l0 16"></path>
                        </svg>',
        '2' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" width="20" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path>
                            <path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path>
                            <path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855"></path>
                        </svg>',
        '3' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" width="20" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M14 6m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                            <path d="M4 6l8 0"></path>
                            <path d="M16 6l4 0"></path>
                            <path d="M8 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                            <path d="M4 12l2 0"></path>
                            <path d="M10 12l10 0"></path>
                            <path d="M17 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                            <path d="M4 18l11 0"></path>
                            <path d="M19 18l1 0"></path>
                        </svg>',
        '4' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
					<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
					<path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"></path>
					<path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
				</svg>',
        '5' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" width="20" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M4 7h3a1 1 0 0 0 1 -1v-1a2 2 0 0 1 4 0v1a1 1 0 0 0 1 1h3a1 1 0 0 1 1 1v3a1 1 0 0 0 1 1h1a2 2 0 0 1 0 4h-1a1 1 0 0 0 -1 1v3a1 1 0 0 1 -1 1h-3a1 1 0 0 1 -1 -1v-1a2 2 0 0 0 -4 0v1a1 1 0 0 1 -1 1h-3a1 1 0 0 1 -1 -1v-3a1 1 0 0 1 1 -1h1a2 2 0 0 0 0 -4h-1a1 1 0 0 1 -1 -1v-3a1 1 0 0 1 1 -1"></path>
                        </svg>'
    ];
    /**
     * Default core administration pages
     *
     * @var array
     */
    public $admin_page_icons = [
        'AD' => "<i class='admin-ico fa fa-fw fa-user-md'></i>", // Administrators
        'APWR' => "<i class='admin-ico fa fa-fw fa-medkit'></i>", // Admin Password Reset
        'B' => "<i class='admin-ico fa fa-fw fa-ban'></i>", // Blacklist
        'BB' => "<i class='admin-ico fa fa-fw fa-bold'></i>", // BB Codes
        'C' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M828-180 716-292H192q-26 0-43-17t-17-43v-416q0-26 17-43t43-17h576q26 0 43 17t17 43v588ZM192-320h536l72 72v-520q0-12-10-22t-22-10H192q-12 0-22 10t-10 22v416q0 12 10 22t22 10Zm-32 0v-480 480Z"/></svg>', // Comments
        'CP' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M232-172q-26 0-43-17t-17-43v-496q0-26 17-43t43-17h496q26 0 43 17t17 43v496q0 26-17 43t-43 17H232Zm0-28h496q12 0 22-10t10-22v-496q0-12-10-22t-22-10H232q-12 0-22 10t-10 22v496q0 12 10 22t22 10Zm-32-560v560-560Zm186 414 94-57 94 57-25-107 83-71-109-10-43-100-43 100-109 10 83 71-25 107Z"/></svg>', // Custom Pages
        'DB' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M480-172q-137 0-222.5-32.5T172-290v-390q0-45 90-76.5T480-788q128 0 218 31.5t90 76.5v390q0 53-85.5 85.5T480-172Zm0-434q85 0 171.5-23.5T757-681q-18-30-103.5-54.5T480-760q-86 0-173.5 23.5T201-685q17 30 103.5 54.5T480-606Zm0 202q41 0 81-4t76.5-12q36.5-8 67.5-20t55-27v-177q-24 15-55 27t-67.5 20q-36.5 8-76.5 12t-81 4q-43 0-83.5-4.5T320-598q-36-8-66.5-19.5T200-644v177q23 15 53.5 26.5T320-421q36 8 76.5 12.5T480-404Zm0 204q53 0 99-5.5t82-16q36-10.5 61.5-25.5t37.5-33v-159q-24 15-55 27t-67.5 20q-36.5 8-76.5 12t-81 4q-43 0-83.5-4.5T320-393q-36-8-66.5-19.5T200-439v159q12 19 37.5 33.5t61.5 25q36 10.5 82 16t99 5.5Z"/></svg>', // Database Backup
        'ERRO' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M480-200q66 0 113-47t47-113v-160q0-66-47-113t-113-47q-66 0-113 47t-47 113v160q0 66 47 113t113 47Zm-64-146h128v-28H416v28Zm0-160h128v-28H416v28Zm64 66Zm0 268q-51 0-93.5-25T318-266H212v-28h92q-11-32-11.5-65.5T292-426h-80v-28h80q0-34-.5-67.5T304-586h-92v-28h106q15-27 38-47.5t52-32.5l-74-74 18-18 84 84q22-6 44-6t44 6l86-84 18 18-74 74q29 12 51 32.5t37 47.5h106v28h-92q13 31 12.5 64.5T668-454h80v28h-80q0 33-.5 66.5T656-294h92v28H642q-26 44-68.5 69T480-172Z"/></svg>', // Error Log
        'FM' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M192-212q-26 0-43-17t-17-43v-416q0-26 17-43t43-17h187l80 80h309q26 0 43 17t17 43v336q0 26-17 43t-43 17H192Zm0-28h576q14 0 23-9t9-23v-336q0-14-9-23t-23-9H448l-80-80H192q-14 0-23 9t-9 23v416q0 14 9 23t23 9Zm-32 0v-480 480Z"/></svg>', // Fusion File Manager
        'I' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M360-172H200q-12 0-20-8t-8-20v-160q30-13 49-40t19-60q0-33-19-60t-49-40v-160q0-12 8-20t20-8h160q14-32 40.5-50t59.5-18q33 0 59.5 18t40.5 50h160q12 0 20 8t8 20v160q32 14 50 40.5t18 59.5q0 33-18 59.5T748-360v160q0 12-8 20t-20 8H560q-14-32-41-50t-59-18q-32 0-59 18t-41 50Zm-160-28h146q15-32 45.5-50t68.5-18q38 0 68.5 18t45.5 50h146v-182q35-6 51.5-29t16.5-49q0-26-16.5-49T720-538v-182H538q-6-35-29-51.5T460-788q-26 0-49 16.5T382-720H200v144q32 17 50 48.5t18 67.5q0 36-18 67.5T200-344v144Zm260-260Z"/></svg>', // Infusions
        'IM' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M232-172q-26 0-43-17t-17-43v-496q0-26 17-43t43-17h496q26 0 43 17t17 43v496q0 26-17 43t-43 17H232Zm0-28h496q12 0 22-10t10-22v-496q0-12-10-22t-22-10H232q-12 0-22 10t-10 22v496q0 12 10 22t22 10Zm86-106h332L548-442 448-318l-64-74-66 86ZM200-200v-560 560Z"/></svg>', // Images
        'LANG' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M192-252q-26 0-43-17t-17-43v-336q0-26 17-43t43-17h576q26 0 43 17t17 43v336q0 26-17 43t-43 17H192Zm0-28h576q12 0 22-10t10-22v-336q0-12-10-22t-22-10H192q-12 0-22 10t-10 22v336q0 12 10 22t22 10Zm140-52h296v-56H332v56ZM212-452h56v-56h-56v56Zm120 0h56v-56h-56v56Zm120 0h56v-56h-56v56Zm120 0h56v-56h-56v56Zm120 0h56v-56h-56v56ZM212-572h56v-56h-56v56Zm120 0h56v-56h-56v56Zm120 0h56v-56h-56v56Zm120 0h56v-56h-56v56Zm120 0h56v-56h-56v56ZM160-280v-400 400Z"/></svg>', // Language Settings
        'M' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M200-226q54-53 125.5-83.5T480-340q83 0 154.5 30.5T760-226v-502q0-12-10-22t-22-10H232q-12 0-22 10t-10 22v502Zm280-210q48 0 81-33t33-81q0-48-33-81t-81-33q-48 0-81 33t-33 81q0 48 33 81t81 33ZM232-172q-26 0-43-17t-17-43v-496q0-26 17-43t43-17h496q26 0 43 17t17 43v496q0 26-17 43t-43 17H232Zm-20-28h536q-62-59-129.5-85.5T480-312q-69 0-137.5 26.5T212-200Zm268-264q-35 0-60.5-25.5T394-550q0-35 25.5-60.5T480-636q35 0 60.5 25.5T566-550q0 35-25.5 60.5T480-464Zm0-29Z"/></svg>', // Members
        'MAIL' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M192-212q-26 0-43-17t-17-43v-416q0-26 17-43t43-17h576q26 0 43 17t17 43v416q0 26-17 43t-43 17H192Zm288-274L160-698v426q0 14 9 23t23 9h576q14 0 23-9t9-23v-426L480-486Zm0-34 304-200H176l304 200ZM160-698v-22 448q0 14 9 23t23 9h-32v-458Z"/></svg>', // Email Templates
        'MI' => '<svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M223.76-206Q182-206 153-235.17q-29-29.16-29-70.83H76v-382q0-26 17-43t43-17h520v140h84l144 194v108h-60q0 41.67-29.24 70.83-29.23 29.17-71 29.17Q682-206 653-235.17q-29-29.16-29-70.83H324q0 42-29.24 71-29.23 29-71 29Zm.24-28q30 0 51-21t21-51q0-30-21-51t-51-21q-30 0-51 21t-21 51q0 30 21 51t51 21ZM104-334h24q6-29 33.5-50.5T224-406q33 0 61.5 21t34.5 51h308v-386H136q-12 0-22 10t-10 22v354Zm620 100q30 0 51-21t21-51q0-30-21-51t-51-21q-30 0-51 21t-21 51q0 30 21 51t51 21Zm-68-180h194L724-580h-68v166ZM366-527Z"/></svg>', // Migration Tool
        'P' => "<i class='admin-ico fa fa-fw fa-desktop'></i>", // Panels
        'PI' => "<i class='admin-ico fa fa-fw fa-info-circle'></i>", // Server Info
        'PL' => "<i class='admin-ico fa fa-fw fa-puzzle-piece'></i>", // Permalinks
        'ROB' => "<i class='admin-ico fa fa-fw fa-android'></i>", // robots.txt
        'S1' => "<i class='admin-ico fa fa-fw fa-hospital-o'></i>", // Main Settings
        'S2' => "<i class='admin-ico fa fa-fw fa-clock-o'></i>", // Time and Date
        'S4' => "<i class='admin-ico fa fa-fw fa-key'></i>", // Registration Settings
        'S6' => "<i class='admin-ico fa fa-fw fa-gears'></i>", // Miscellaneous Settings
        'S7' => "<i class='admin-ico fa fa-fw fa-envelope-square'></i>", // PM Settings
        'S9' => "<i class='admin-ico fa fa-fw fa-users'></i>", // User Management
        'S12' => "<i class='admin-ico fa fa-fw fa-shield'></i>", // Security Settings
        'SB' => "<i class='admin-ico fa fa-fw fa-language'></i>", // Banners
        'SL' => "<i class='admin-ico fa fa-fw fa-link'></i>", // Site Links
        'SM' => "<i class='admin-ico fa fa-fw fa-smile-o'></i>", // Smileys
        'TS' => "<i class='admin-ico fa fa-fw fa-magic'></i>", // Theme Manager
        'U' => "<i class='admin-ico fa fa-fw fa-database'></i>", // Upgrade
        'UF' => "<i class='admin-ico fa fa-fw fa-table'></i>", // User Fields
        'UG' => "<i class='admin-ico fa fa-fw fa-users'></i>", // User Groups
        'UL' => "<i class='admin-ico fa fa-fw fa-coffee'></i>", // User Log
    ];
    /**
     * @var array
     */
//    private $admin_sections = [1 => FALSE, 2 => FALSE, 3 => FALSE, 4 => FALSE, 5 => FALSE];
    /**
     * @var array
     */
    private $admin_page_link = [];
    /**
     *    Constructor class. No Params
     */
    private $current_page = '';
    private $comment_type = [];
    private $submit_type = [];
    private $submit_link = [];
    private $link_type = [];
    private $submit_data = [];
    private $folder_permissions = [];
    private $custom_folders = [];

    public function __construct()
    {
        self::$locale = fusion_get_locale('', LOCALE . LOCALESET . 'admin/main.php');
    }

    /**
     * Add instance
     *
     * @return static
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Cache the Current Field Inputs within Login session.
     *
     * @param string $form_id
     * @param string $form_type
     * @param int $item_id
     * @param array $callback_fields
     * @param int $cache_time
     *
     * @return string
     */
    public function requestCache($form_id, $form_type, $item_id, array $callback_fields = [], $cache_time = 30000)
    {
        add_to_jquery("
        function timedCacheRequest(timeout) {
            setTimeout(UpdateAdminCache, timeout);
        }
        function UpdateAdminCache(poll) {
            var input_fields = $('#$form_id').serialize();
            var ttl = '$cache_time';
            $.ajax({
            url: '" . ADMIN . "includes/?api=cache-update',
            type: 'post',
            dataType: 'html',
            data: {
                'fusion_token': '" . fusion_get_token($form_id) . "',
                'aidlink': '" . fusion_get_aidlink() . "',
                'fields': input_fields,
                'form_id' : '$form_id',
                'form_type':'$form_type',
                'item_id': '$item_id',
                'callback':'set_cache',
            },
            dataType: 'json',
            success: function (e) {
                console.log(e);
                console.log(poll);
                if (typeof poll === 'undefined') {
                    //console.log('we are starting a long poll now');
                    timedCacheRequest(ttl);
                }
            }
            });
        }
        // add abort long poll once we change fields.
        $('#" . $form_id . " :input').blur(function(e) {
            UpdateAdminCache(1);
        });
        ");
        if (!isset($_GET['autosave'])) {
            // not to pass prefix xhr for security reason.
            add_to_jquery("setTimeout( function(e) { UpdateAdminCache() }, $cache_time);");
        }

        $html = "";
        if (!empty($_SESSION['form_cache'][$form_id][$form_type][$item_id])) {

            $html .= "<div class='list-group text-normal m-t-10 m-b-10'><div class='list-group-item'>" . self::$locale['290'] . " <a href='" . clean_request('autosave=view', ['autosave'], FALSE) . "'>" . self::$locale['291'] . "</a></div></div>";

            if (isset($_GET['autosave']) && $_GET['autosave'] == 'view') {

                $html .= "<div id='rev-window'>\n";
                $html .= fusion_get_function('openside', "<h4><i class='fas fa-thumbtack m-r-10'></i>" . self::$locale['292'] . "</h4>");
                $session = htmlspecialchars_decode($_SESSION['form_cache'][$form_id][$form_type][$item_id]);
                $session = str_replace('&#039;', "'", $session);
                parse_str($session, $data);
                unset($data['form_id']);
                unset($data['fusion_token']);

                $html .= "<dl id='restore_results' class='dl-horizontal'>\n";
                $fill_js = "";
                foreach ($data as $field_name => $value) {
                    $value = descript($value);
                    if (isset($callback_fields[$field_name])) {
                        $html .= "<dt>" . $callback_fields[$field_name] . "</dt>\n";
                        $html .= "<dd class='m-b-15'><samp>";
                        $html .= nl2br(html_entity_decode($value));
                        $html .= "</samp>" . form_hidden('s_' . $field_name, '', str_replace("'", '&#039;', $value)) . "</dd>\n";
                        $fill_js .= "
                            var c = $('#s_$field_name').val();
                            $('#" . $field_name . "').val(c);
                        ";
                    }
                }
                $html .= "</dl>\n";
                $html .= "<div class='text-right'>\n";
                $html .= "<button name='cancel_session' type='button' class='btn btn-default' value='cancel_session'>" . self::$locale['cancel'] . "</button>\n";
                $html .= "<button name='fill_session' type='button' class='btn btn-primary' value='fill_session'>" . self::$locale['293'] . "</button>\n";
                $html .= "</div>\n";
                $html .= fusion_get_function('closeside', '');
                $html .= "</div>\n";
                add_to_jquery("
                    $('button[name^=\"fill_session\"]').bind('click', function(e) {
                        $fill_js
                        $('#rev-window').hide();
                        UpdateAdminCache();
                    });
                    $('button[name^=\"cancel_session\"]').bind('click', function(e) {
                        $('#rev-window').hide();
                        UpdateAdminCache();
                    });
            ");
            }
        }

        return $html;
    }

    /**
     * @param array $elements
     * @param int   $parentId
     * @param int   $level
     *
     * @return array
     */
    function buildTree(array $elements, int $parentId = 0, int $level = 0): array
    {
        $branch = [];

        foreach ($elements as $element) {

            $pass_check = (checkrights($element['admin_rights']) or empty($element['admin_page']));
            if ((int)$element['admin_page'] === $parentId && $pass_check) {
                $element['level'] = $level;
                $children = $this->buildTree($elements, (int)$element['admin_id'], $level + 1);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    function flattenTree(array $tree, array &$flat = []): array
    {
        foreach ($tree as $node) {
            $flat[] = $node;
            if (!empty($node['children'])) {
                $this->flattenTree($node['children'], $flat);
            }
        }
        return $flat;
    }

private static $new_admin_pages;
    private static $admin_pages_tree;

    /**
     * Set admin sections
     */
    public function setAdminPages()
    {

        $res = dbquery("SELECT 
            *  
            FROM ".DB_ADMIN." 
            WHERE admin_language=:language
            ORDER BY admin_page, admin_order", [
            ':language' => LANGUAGE
        ]);

        if (dbrows($res)) {

            $admins = [];

            // Build Admin Tree Data
            while ($data = dbarray($res)) {

                // Backwards compat
                if ($data['admin_page'] == '0') {
                    self::$admin_sections[$data['admin_id']] = $data['admin_title'];
                }
                if (checkrights($data['admin_rights']) && $data['admin_link'] != "reserved") {
                    $data['admin_title'] = $locale[$data['admin_rights']] ?? $data['admin_title'];
                }
                $data['admin_icon'] = match ($data['admin_idisplay']) {
                    '1' => $data['admin_image'] ? '<img src="' . $data['admin_image'] . '">' : '',
                    '2' => $data['admin_glyph'] ? '<i class="' . $data['admin_glyph'] . '"></i>' : '',
                    default => $data['admin_svg'] ? ImageRepo::getSVG($data['admin_svg']) : '',
                };

                $admins[] = $data;
            }

            self::$admin_pages_tree = $this->buildTree($admins);

            self::$new_admin_pages = $this->flattenTree(self::$admin_pages_tree);

            self::$admin_pages = self::$new_admin_pages;
        }

        $this->current_page = $this->currentPage();
    }

    public function getAdminPageTree() {
        return self::$admin_pages_tree;
    }

    /**
     * @param int $page
     *
     * @return array
     */
    public function getAdminPages($page = NULL)
    {
        return $page === NULL ? self::$new_admin_pages : (self::$new_admin_pages[$page] ?? self::$new_admin_pages);
    }

    /**
     * Build a return that always synchronize with the DB_ADMIN url.
     */
    public function currentPage()
    {
        $path = $_SERVER['PHP_SELF'];
        if (defined('START_PAGE')) {
            $path_info = pathinfo(strtok(START_PAGE, '?'));
            if (stristr(FUSION_REQUEST, '/administration/')) {
                $path = $path_info['filename'] . '.php';
            } else {
                $path = '../' . $path_info['dirname'] . '/' . $path_info['filename'] . '.php'.fusion_get_query();
            }
        }

        return $path;
    }

    /**
     * Subquery matcher
     * @param $admin_link
     * @return bool
     */
    public function isLinkActive($admin_link) {

        // Parse $admin_link query string
        $parsed_admin = parse_url(html_entity_decode($admin_link));
        $admin_params = [];
        if (!empty($parsed_admin['query'])) {
            parse_str($parsed_admin['query'], $admin_params);
        }
        if (empty($admin_params)) {
            return Admins::getInstance()->getCurrentPage() == $admin_link;
        }
        // Parse current browser URL query string
        $parsed_browser = parse_url(html_entity_decode($_SERVER['REQUEST_URI']));
        $browser_params = [];
        if (!empty($parsed_browser['query'])) {
            parse_str($parsed_browser['query'], $browser_params);
        }

        // Check if all key/value pairs in $admin_link exist in browser's query params
        foreach ($admin_params as $key => $value) {
            if (!array_key_exists($key, $browser_params) || (string)$browser_params[$key] !== (string)$value) {

                return false;
            }
        }

        return true;
    }

    /**
     * Check if a link or any of its children is active
     *
     * @param string $admin_link
     * @param array  $children
     * @return bool
     */
    public function isLinkOrChildrenActive(string $admin_link, array $children = []): bool {
        // First, check the link itself
        if ($this->isLinkActive($admin_link)) {
            return true;
        }

        // Then, check all children recursively
        foreach ($children as $child) {
            if (!empty($child['admin_link']) && $this->isLinkActive($child['admin_link'])) {
                return true;
            }
            if (!empty($child['children']) && $this->isLinkOrChildrenActive($child['admin_link'], $child['children'])) {
                return true;
            }
        }

        return false;
    }



    /**
     * @param int $page 0-5 is core section pages. 6 and above are free to use.
     * @param string $section_title Section title
     * @param string $icon Section icon
     */
    public function addAdminSection($page, $section_title, $icon)
    {
        self::$admin_sections[$page] = $section_title;
        $this->admin_section_icons[$page] = $icon;
        self::$admin_pages[$page] = [];
    }

    /**
     * Set admin breadcrumbs
     */
    public function setAdminBreadcrumbs()
    {
        add_breadcrumb([
            'link' => ADMIN . 'index.php' . fusion_get_aidlink() . '&amp;pagenum=0',
            'title' => self::$locale['ac10']
        ]);
        $acTab = (isset($_GET['pagenum']) && isnum($_GET['pagenum'])) ? $_GET['pagenum'] : ''; //$this->isActive();
        if ($acTab != 0 && $acTab <= 5) {
//            add_breadcrumb([
//                'link' => ADMIN . fusion_get_aidlink() . "&amp;pagenum=" . $acTab,
//                'title' => self::$locale['ac0' . $acTab]
//            ]);
        }

    }

    /**
     * @return array
     */
    public function getAdminPageIcons()
    {
        return $this->admin_page_icons;
    }

    /**
     * @param string $rights
     * @param string $icons
     */
    public function setAdminPageIcons($rights, $icons)
    {
        $this->admin_page_icons[$rights] = $icons;
    }

    /**
     * @param string $type
     *
     * @return array|mixed|null
     */
    public function getLinkType($type = NULL)
    {
        return ($type !== NULL ? (isset($this->link_type[$type]) ? $this->link_type[$type] : NULL) : $this->link_type);
    }

    /**
     * @param string $type Link prefix
     * @param string $link Link url
     */
    public function setLinkType($type, $link)
    {
        $this->link_type[$type] = $link;
    }

    /**
     * Get submit type
     *
     * @param string $type submit stype prefix
     *
     * @return array|mixed|null
     */
    public function getSubmitType($type = NULL)
    {
        return ($type !== NULL ? (isset($this->submit_type[$type]) ? $this->submit_type[$type] : NULL) : $this->submit_type);
    }

    /**
     * @param string $type Submissions prefix
     * @param string $title Title
     */
    public function setSubmitType($type, $title)
    {
        $this->submit_type[$type] = $title;
    }

    /**
     * @param string $type
     *
     * @return array|mixed|null
     */
    public function getSubmitData($type = NULL)
    {
        return ($type !== NULL ? (isset($this->submit_data[$type]) ? $this->submit_data[$type] : NULL) : $this->submit_data);
    }

    /**
     * @param string $type Submissions prefix
     * @param array $options array(infusion_name, link, submit_link, submit_locale, title,admin_link)
     */
    public function setSubmitData($type, array $options = [])
    {
        if (defined(strtoupper($options['infusion_name']) . '_EXISTS')) {
            $this->submit_data[$type] = $options;
        }
    }

    /**
     * @param string $type
     *
     * @return array|mixed|null
     */
    public function getSubmitLink($type = NULL)
    {
        return ($type !== NULL ? (isset($this->submit_link[$type]) ? $this->submit_link[$type] : NULL) : $this->submit_link);
    }

    /**
     * @param string $link Admin submission url
     * @param string $type Submissions stype prefix
     */
    public function setSubmitLink($type, $link)
    {
        $this->submit_link[$type] = $link;
    }

    /**
     * @param string $type
     *
     * @return array|mixed|null
     */
    public function getCommentType($type = NULL)
    {
        return ($type !== NULL ? (isset($this->comment_type[$type]) ? $this->comment_type[$type] : NULL) : $this->comment_type);
    }

    /**
     * @param string $type Comment prefix
     * @param string $title Title
     */
    public function setCommentType($type, $title)
    {
        $this->comment_type[$type] = $title;
    }

    /**
     * @param string $type Infusion name
     */
    public function getFolderPermissions($type = NULL)
    {
        return ($type !== NULL ? (isset($this->folder_permissions[$type]) ? $this->folder_permissions[$type] : NULL) : $this->folder_permissions);
    }

    /**
     * @param string $type Infusion name
     * @param array $options array(image_folder => TRUE or FALSE)
     */
    public function setFolderPermissions($type, array $options = [])
    {
        if (defined(strtoupper($type) . '_EXISTS')) {
            $this->folder_permissions[$type] = $options;
        }
    }

    /**
     * @param string $rights
     *
     * @return array|null
     */
    public function getCustomFolders($rights = NULL)
    {
        return ($rights !== NULL ? (isset($this->custom_folders[$rights]) ? $this->custom_folders[$rights] : NULL) : $this->custom_folders);
    }

    /**
     * A custom folder that appears in the file manager
     *
     * @param string $rights
     * @param array $options setCustomFolder('N', [['path' => IMAGES_N, 'URL' => fusion_get_settings('siteurl').'infusions/news/images/', 'alias' => 'news']]);
     */
    public function setCustomFolder($rights, $options = [])
    {
        $this->custom_folders[$rights] = $options;
    }

    /**
     * @return array
     */
    public function getAdminPageLink()
    {
        return $this->admin_page_link;
    }

    /**
     * @return string
     */
    public function getCurrentPage()
    {
        return $this->current_page;
    }

    /**
     * @return array
     */
    public function getAdminSections()
    {
        return self::$admin_sections;
    }

    /**
     * @param int $page_number
     *
     * @return string
     */
    public function getAdminSectionIcons($page_number)
    {
        if (!empty($this->admin_section_icons[$page_number]) && $this->admin_section_icons[$page_number]) {
            return $this->admin_section_icons[$page_number];
        }

        return FALSE;
    }

    /**
     * Replace admin page icons
     *
     * @param int $page
     * @param string $icon
     */
    public function setAdminSectionIcons($page, $icon)
    {
        if (isset($this->admin_section_icons[$page])) {
            $this->admin_section_icons[$page] = $icon;
        }
    }

    /**
     * Get the administration page icons
     *
     * @param string $admin_rights
     *
     * @return bool
     */
    public function getAdminIcons($admin_rights)
    {
        // admin rights might not yield an icon & admin_icons override might not have the key.
        if (isset($this->admin_page_icons[$admin_rights]) && $this->admin_page_icons[$admin_rights]) {
            return $this->admin_page_icons[$admin_rights];
        }

        return FALSE;
    }

    /**
     * Displays vertical collapsible administration navigation
     *
     * @param bool $image_icon
     *
     * @return string
     */
    public function verticalAdminNav($image_icon = FALSE)
    {
        $aidlink = fusion_get_aidlink();
        $admin_sections = self::getAdminSections();
        $admin_pages = self::getAdminPages();

        add_to_jquery('$("[data-toggle=collapse]").click(function () {$(this).find(".adl-drop i").toggleClass("fa-angle-left fa-angle-down");});');

        $html = "<ul id='adl' class='admin-vertical-link'>\n";

        foreach ($admin_sections as $i => $section_name) {
            $active = ((isset($_GET['pagenum']) && $_GET['pagenum'] == $i) || (!isset($_GET['pagenum']) && $this->isActive() == $i));

            $html .= "<li class='" . ($active ? 'active panel' : 'panel') . "' >\n";

            if (!empty($admin_pages[$i]) && is_array($admin_pages[$i])) {
                $html .= "<a class='adl-link " . ($active ? '' : 'collapsed') . "' data-parent='#adl' data-toggle='collapse' href='#adl-$i' aria-expanded='false' aria-controls='#adl-$i'>" . $this->getAdminSectionIcons($i) . " <span class='adl-section-name'>" . $section_name . "</span> " . ($i > 0 ? "<span class='adl-drop pull-right'><i class='fa fa-angle-" . ($active ? "left" : "down") . "'></i></span>" : '') . "</a>\n";
                $html .= "<ul id='adl-$i' class='admin-submenu collapse " . ($active ? 'in' : '') . "'>\n";
                foreach ($admin_pages[$i] as $data) {
                    $secondary_active = $data['admin_link'] == $this->currentPage();
                    $icons = ($image_icon === TRUE) ? "<img class='admin-image' src='" . get_image("ac_" . $data['admin_rights']) . "' alt='" . $data['admin_title'] . "'>" : $this->getAdminIcons($data['admin_rights']);

                    $html .= checkrights($data['admin_rights']) ? "<li" . ($secondary_active ? " class='active'" : '') . "><a href='" . ADMIN . $data['admin_link'] . $aidlink . "'>" . $icons . " <span class='adl-submenu-title'>" . $data['admin_title'] . "</span></a></li>" : '';
                }

                $html .= "</ul>";
            } else {
                $html .= "<a class='adl-link' href='" . ADMIN . "index.php" . $aidlink . "&amp;pagenum=0'>" . $this->getAdminSectionIcons($i) . " <span class='adl-section-name'>" . $section_name . "</span> " . ($i > 0 ? "<span class='adl-drop pull-right'></span>" : '') . "</a>";
            }
            $html .= "</li>\n";
        }

        $html .= "</ul>\n";

        return $html;
    }

    /**
     * Displays horizontal administration navigation
     *
     * @param bool $icon_only
     *
     * @return string
     */
    public function horizontalAdminNav($icon_only = FALSE)
    {
        $aidlink = fusion_get_aidlink();
        $html = "<ul class='admin-horizontal-link'>\n";
        foreach ($this->admin_sections as $i => $section_name) {
            $active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && $this->isActive() == $i) ? 1 : 0;
            $admin_text = $icon_only == FALSE ? " " . $section_name : "";
            $html .= "<li " . ($active ? "class='active'" : '') . "><a title='" . $section_name . "' href='" . ADMIN . $aidlink . "&amp;pagenum=$i'>" . $this->getAdminSectionIcons($i) . $admin_text . "</a></li>\n";
        }
        $html .= "</ul>\n";

        return $html;
    }

    /**
     * Build a return that always synchronize with the DB_ADMIN url.
     *
     * @deprecated use currentPage()
     */
    public function _currentPage()
    {
        return $this->currentPage();
    }

    /**
     * Determine which section is currently active.
     *
     * @return int|string
     *
     * @deprecated use isActive()
     */
    public function _isActive()
    {
        return $this->isActive();
    }

    /**
     * Displays vertical collapsible administration navigation
     *
     * @param bool $image_icon
     *
     * @return string
     *
     * @deprecated use verticalAdminNav()
     */
    public function vertical_admin_nav($image_icon = FALSE)
    {
        return $this->verticalAdminNav($image_icon);
    }

    /**
     * Displays horizontal administration navigation
     *
     * @param bool $icon_only
     *
     * @return string
     * @deprecated use horizontalAdminNav()
     */
    public function horizontal_admin_nav($icon_only = FALSE)
    {
        return $this->horizontalAdminNav($icon_only);
    }

    /**
     * Get the administration page icons
     *
     * @param string $admin_rights
     *
     * @return bool
     * @deprecated use getAdminIcons()
     */
    public function get_admin_icons($admin_rights)
    {
        return $this->getAdminIcons($admin_rights);
    }

    /**
     * @param int $page_number
     *
     * @return string
     * @deprecated use getAdminSectionIcons()
     */
    public function get_admin_section_icons($page_number)
    {
        return $this->getAdminSectionIcons($page_number);
    }
}
