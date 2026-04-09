<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: errors.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Errors;

defined('IN_FUSION') || exit;

//$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;

$locale = fusion_get_locale('', [ LOCALE . LOCALESET . 'admin/errors.php', LOCALE . LOCALESET . 'errors.php' ]);

$contents = [
    'post'        => 'pf_post',
    'view'        => 'pf_view',
    'button'      => 'pf_submit',
    'js'          => 'pf_js',
    'link'        => ( $admin_link ?? '' ),
    'settings'    => TRUE,
    'title'       => $locale['ERROR_400'],
    'description' => '',
    'actions'     => [ 'post' => [ 'purge' => 'purgeFrm', 'purgefile' => 'purgeFrm', 'savesettings' => 'errorFrm' ] ]
];

function pf_post() {

    $locale = fusion_get_locale();

    if ( admin_post('purge') ) {
        dbquery("TRUNCATE " . DB_ERRORS);
        add_notice('success', 'All errors have been purged successfully');
        redirect(FUSION_REQUEST);
    }

    if ( admin_post('purgefile') ) {
        if ( is_file(BASEDIR . 'fusion_error_log.log') ) {
            @unlink(BASEDIR . 'fusion_error_log.log');
            redirect(FUSION_REQUEST);
        }
    }

    if ( admin_post('savesettings') ) {

        $inputData = [
            'error_logging_enabled' => sanitizer('error_logging_enabled', '0', 'error_logging_enabled'),
            'error_logging_method'  => sanitizer('error_logging_method', 'database', 'error_logging_method'),
        ];
        if ( fusion_safe() ) {

            foreach ( $inputData as $settings_name => $settings_value ) {
                dbquery('UPDATE ' . DB_SETTINGS . ' SET settings_value=:settings_value WHERE settings_name=:settings_name',
                        [
                            ':settings_value' => $settings_value,
                            ':settings_name'  => $settings_name
                        ]);
            }
            add_notice('success', $locale['900']);
            redirect(FUSION_REQUEST);
        }
    }

}

/**
 * @return string
 */
function pf_submit() {

    $html = form_button('purge', 'Purge All Errors', 'purge', [ 'class' => 'btn-danger' ]);

    if ( fusion_get_settings('error_logging_method') == 2 ) {
        $html = form_button('purgefile', 'Purge All Errors', 'purgefile', [ 'class' => 'btn-danger' ]);
    }

    if ( ! get_error_id() ) {

        $html .= form_button('savesettings',
                             'Save Settings',
                             'savesettings',
                             [ 'class' => 'btn-primary' ]);
    }

    return $html;

}


function get_error_id() {

    static $error_id;

    if ( ! $error_id ) {
        $error_id = get('eid', FILTER_VALIDATE_INT);
    }

    return $error_id;
}


function pf_view() {

    echo openform('purgeFrm', 'POST') . closeform();

    if ( $error_id = get_error_id() ) {
        display_error_details($error_id);
    }
    else {
        display_error_settings();
    }
}

function pf_js() {

    fusion_load_script(INCLUDES . "jscripts/administration/error_log.js");

    return "errorLogger.runscript();";
}

/* Display errors table */
function display_error_settings() {

    $settings = fusion_get_settings();
    $locale = fusion_get_locale();
    echo openform('errorFrm', 'POST');
    openside('<label class="control-label" for="enable_logging">Enable Error Logging</label>',
             form_checkbox('error_logging_enabled',
                           '',
                           $settings['error_logging_enabled'],
                           [ 'toggle' => TRUE, 'class' => 'm-l-a' ]));
    closeside();
    openside('Error Logging Type', TRUE);
    echo form_checkbox('error_logging_method', 'Choose Error Log Type', $settings['error_logging_method'], [
        'type' => 'radio', 'options' => [
            'database' => 'Database Logging<small>Enhanced debugging options, performs slower</small>',
            'file' => 'File system Logging<small>x10 faster than Database logging with simple features</small>'
        ]
    ]);
    closeside();
    echo closeform();

    $tab['title'][] = 'All Unresolved';
    $tab['title'][] = 'For Review';
    $tab['title'][] = 'Ignored';
    $tab['id'][] = 'all';
    $tab['id'][] = 'review';
    $tab['id'][] = 'ignored';

    $error_q = 0;
    if ( $d = get('d') ) {
        if ( $d == 'review' ) {
            $error_q = 1;
        }
        else if ( $d == 'ignored' ) {
            $error_q = 2;
        }
    }

    $tab_active = tab_active($tab, 0, 'd');

    // Move security features here.. like Enable Error Logs, Who can Access Error Logs, etc.
    // @todo: work on the filter
    // @todo: work on the micro error log - do simplification to just remove everything without other option. But do not purge everything.

    echo opentab($tab, $tab_active, 'error', TRUE, NULL, 'd');
    openside();
    if ( $settings['error_logging_method'] == 'database' ) {

        echo openform('errorLogFrm', 'POST');
        echo '<table id="' . fusion_table('errorLogTable', [
                'ordering'          => FALSE,
                'col_resize'        => FALSE,
                'col_reorder'       => FALSE,
                'hide_search_input' => FALSE,
            ]) . '" class="table"><thead><tr>';

        echo '<th><span class="display-inline-block m-r-10">' . form_checkbox('checkall',
                                                                              '',
                                                                              '',
                                                                              [ 'class' => 'm-b-10' ]) . '</span>' .
             form_button('resolve',
                         'Resolve',
                         'resolve',
                         [ 'class' => 'btn-success m-r-5', 'icon' => 'fal fa-check' ]) .
             form_button('ignore',
                         'Ignore',
                         'ignore',
                         [ 'class' => 'btn-default m-r-5', 'icon' => 'fal fa-volume-mute' ]) .
             form_button('reviewed',
                         'Mark Reviewed',
                         'reviewed',
                         [ 'class' => 'btn-default m-r-5', 'icon' => 'fal fa-archive' ]);
        echo '</th>';
        echo '<th>Events</th>';
        echo '<th>Users</th>';
        echo '<th>Assigned</th>';
        echo '</tr></thead><tbody>';

        $error_class = ( new Errors() );

        if ( $errors = $error_class->getErrors($error_q) ) {

            $baselink = clean_request('', [ 'error_id', 'action' ], FALSE);

            foreach ( $errors as $data ) {

                $error_link = $baselink . '&eid=' . $data['error_id'];
                $assign_link = $error_link . '&action=assign';
                $review_link = $error_link . '&action=review';
                $solved_link = $error_link . '&action=solved';
                $resolved_link = $error_link . '&action=resolved';

                $status_text = 'Options<i class="m-l-5 far fa-angle-down"></i>';

                if ( $data['error_assign_user'] ) {
                    $status_text = 'Assigned: <strong>' . fusion_get_username($data['error_assign_user']) . '</strong><i class="m-l-5 far fa-angle-down"></i>';
                }

                $issue_status = '';
                if ( $data['error_status'] == 1 ) {
                    $issue_status = '<span class="label label-default m-r-10"><i class="fal fa-telescope m-r-10"></i>Review</span>';

                    if ( $data['error_review_user'] ) {
                        $issue_status = '<span class="label label-success m-r-10"><i class="fal fa-archive m-r-10"></i>Reviewed</span>';
                        $status_text = 'Reviewed: <strong>' . fusion_get_username($data['error_review_user']) . '</strong><i class="m-l-5 far fa-angle-down"></i>';
                    }
                }

                $data['error_message'] = trim_text($data['error_message'], 150);

                echo '<tr id="pf-error-' . $data['error_id'] . '"><td><div class="flex">' . form_checkbox('error_sel[]',
                                                                                                          '',
                                                                                                          '',
                                                                                                          [
                                                                                                              'input_id' => 'error_' . $data['error_id'],
                                                                                                              'class'    => 'm-b-10',
                                                                                                              'value'    => $data['error_id']
                                                                                                          ]) . '<div>
                <a class="text-bigger" href="' . $error_link . '">' . strip_tags(strtr(htmlspecialchars_decode($data['error_message']),
                                                                                       [ '#' => '<br/>#' ])) . '</a> ' . $data['error_page'] . '<br>
                <small><span id="pf-stat-' . $data['error_id'] . '">' . $issue_status . '</span>' . $error_class->getErrorTypes($data['error_level']) . '
                <i class="fal fa-clock m-l-5 m-r-5"></i> ' . timer($data['error_updated_datestamp']) . ' - ' . timer($data['error_timestamp']) . '</small>
                </div></div></td>
                <td>' . $data['error_count'] . '</td>
                <td>' . $data['error_user_count'] . '</td>
                <td class="col-xs-2"><div class="dropdown">
                <a id="pf-toggle-text-' . $data['error_id'] . '" href="#" data-toggle="dropdown" class="dropdown-toggle btn btn-default">' . $status_text . '</a>
                <ul class="dropdown-menu dropdown-menu-right">
                <li><a data-issue-action="assign" data-value="' . $data['error_id'] . '" href="' . $assign_link . '" class="flex"><i class="fal fa-compass text-inverse fa-2x fa-fw m-r-5"></i><span><strong>Assign to me</strong><small>Take responsibility to fix the issue</small></span></a></li>
                <li><a data-issue-action="review" data-value="' . $data['error_id'] . '" href="' . $review_link . '" class="flex"><i class="fal fa-subscript fa-2x text-info fa-fw m-r-5"></i><span><strong>Mark for Review</strong><small>Mark issue for further review</small></span></a></li>
                <li><a data-issue-action="solved" data-value="' . $data['error_id'] . '" href="' . $solved_link . '" class="flex"><i class="fal fa-archive fa-2x text-warning fa-fw m-r-5"></i><span><strong>Mark as Reviewed</strong><small>Mark issue as reviewed</small></span></a></li>
                <li><a data-issue-action="resolved" data-value="' . $data['error_id'] . '" href="' . $resolved_link . '" class="flex"><i class="fal fa-check fa-2x text-danger fa-fw m-r-5"></i><span><strong>Mark as Resolved</strong><small>Mark issue as resolved</small></span></a></li>
                </ul>
                </div></td></tr>';
            }
        }

    }
    else {


        echo openform('deletelog', 'post', FUSION_REQUEST);
        echo form_button('delete_log',
                         $locale['delete'],
                         'delete_log',
                         [ 'class' => 'btn-danger', 'icon' => 'fa fa-trash' ]);
        echo closeform();

        if ( file_exists(BASEDIR . 'fusion_error_log.log') ) {
            echo '<textarea class="form-control m-t-20" rows="15" disabled>' . file_get_contents(BASEDIR . 'fusion_error_log.log') . '</textarea>';
        }
        else {
            echo "<div class='text-center well m-t-20'>" . $locale['ERROR_418'] . "</div>\n";
        }
    }
    echo '</tbody></table>';
    echo closeform();
    closeside();
    echo closetab();
}

/**
 * Detail report
 *
 * @param $error_id
 */
function display_error_details($error_id) {

    $settings = fusion_get_settings();
    $locale = fusion_get_locale();

    fusion_load_script(ADMIN_THEMES . 'Pro/prism.css', 'css');
    fusion_load_script(INCLUDES . 'jquery/prism.js');

    add_breadcrumb([
                       'link'  => ADMIN_CURRENT_DIR . '&eid=' . $error_id,
                       'title' => 'Issue #' . $error_id
                   ]);
    // @todo: work on the item details. URL: https://docs.sentry.io/static/8bce13c96cf484065f2560ae06da2804/2b608/issue-details.png, https://medium.com/@Snaverz/bitbucket-pipelines-sentry-io-releases-and-deploys-29800f7bdc2d
    $result = dbquery("SELECT * FROM " . DB_ERRORS . " WHERE error_id=:id", [ ':id' => $error_id ]);
    if ( dbrows($result) ) {
        $data = dbarray($result);
        $data['error_message'] = str_replace('&#039;', "'", $data['error_message']);

        $user = fusion_get_user($data['error_user_id']);
        $error_class = ( new Errors() );

        $baselink = ADMIN_CURRENT_DIR;
        $error_link = $baselink . '&eid=' . $data['error_id'];
        $assign_link = $error_link . '&action=assign';
        $review_link = $error_link . '&action=review';
        $solved_link = $error_link . '&action=solved';
        $resolved_link = $error_link . '&action=resolved';
        $status_text = 'Options<i class="m-l-5 far fa-angle-down"></i>';

        $issue_status = '';
        $review_text = '-';
        $assigned_text = '-';
        if ( $data['error_assign_user'] ) {
            $userdata = fusion_get_user($data['error_assign_user']);
            $status_text = 'Assigned: <strong>' . $userdata['user_name'] . '</strong><i class="m-l-5 far fa-angle-down"></i>';
            $assigned_text = '<div class="flex ac gap-10">' . display_avatar($userdata,
                                                                             '30px') . profile_link($userdata['user_id'],
                                                                                                    $userdata['user_name'],
                                                                                                    $userdata['user_status']) . '</div>';
        }

        if ( $data['error_status'] == 1 ) {
            $issue_status = '<span class="label label-default m-r-10"><i class="fal fa-telescope m-r-10"></i>Review</span>';

            if ( $data['error_review_user'] ) {
                $userdata = fusion_get_user($data['error_review_user']);
                $review_text = '<div class="flex ac gap-10">' . display_avatar($userdata,
                                                                               '30px') . profile_link($userdata['user_id'],
                                                                                                      $userdata['user_name'],
                                                                                                      $userdata['user_status']) . '</div>';
                $issue_status = '<span class="label label-success m-r-10"><i class="fal fa-archive m-r-10"></i>Reviewed</span>';
                $status_text = 'Reviewed: <strong>' . $userdata['user_name'] . '</strong><i class="m-l-5 far fa-angle-down"></i>';
            }
        }

        openside();
        echo openform('errorLogFrm', 'POST');
        echo form_hidden('error_sel[]', '', $error_id);
        echo '<div class="row"><div class="col-xs-12 col-sm-8">';
        echo '<h3><strong>' . trimlink(strip_tags(strtr(htmlspecialchars_decode($data['error_message']),
                                                        [ '#' => '<br/>#' ])),
                                       30) . '</strong><small class="m-l-15">' . $data['error_page'] . '</small></h3>';

        echo '<span class="badge">e</span> ' . $error_class->getErrorTypes($data['error_level']) . ' <span class="badge">f</span> ' . $data['error_file'];
        echo '</div><div class="col-xs-12 col-sm-4">';
        echo '<div class="flex gap-15 jb">';
        echo '<div class="text-right"><small class="text-uppercase">Issue#</small><h4>' . $data['error_id'] . '</h4></div>';
        echo '<div  class="text-right"><small class="text-uppercase">Events</small><h4>' . $data['error_count'] . '</h4></div>';
        echo '<div  class="text-right"><small class="text-uppercase">Users</small><h4>' . $data['error_user_count'] . '</h4></div>';
        echo '<div  class="text-right"><small class="text-uppercase">Assigned</small>
        <div class="dropdown m-t-10">
        <a id="pf-toggle-text-' . $error_id . '" href="#" data-toggle="dropdown" class="dropdown-toggle flex ac">' . $status_text . '</a>
        <ul class="dropdown-menu dropdown-menu-right">
        <li><a data-issue-action="assign" data-value="' . $data['error_id'] . '" href="' . $assign_link . '" class="flex"><i class="fal fa-compass text-inverse fa-2x fa-fw m-r-5"></i><span><strong>Assign to me</strong><small>Take responsibility to fix the issue</small></span></a></li>
        <li><a data-issue-action="review" data-value="' . $error_id . '" href="' . $review_link . '" class="flex"><i class="fal fa-subscript fa-2x text-info fa-fw m-r-5"></i><span><strong>Mark for Review</strong><small>Mark issue for further review</small></span></a></li>
        <li><a data-issue-action="solved" data-value="' . $error_id . '" href="' . $solved_link . '" class="flex"><i class="fal fa-archive fa-2x text-warning fa-fw m-r-5"></i><span><strong>Mark as Reviewed</strong><small>Mark issue as reviewed</small></span></a></li>
        <li><a data-issue-action="resolved" data-value="' . $error_id . '" href="' . $resolved_link . '" class="flex"><i class="fal fa-check fa-2x text-danger fa-fw m-r-5"></i><span><strong>Mark as Resolved</strong><small>Mark issue as resolved</small></span></a></li>
        </ul>
        </div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        // Second row
        echo '<div class="flex spacer-sm m-b-0">';
        echo form_button('resolve',
                         'Resolve',
                         'resolve',
                         [ 'icon' => 'far fa-check', 'class' => 'btn-success m-r-10' ]);
        echo form_button('ignore', 'Ignore', 'ignore', [ 'icon' => 'far fa-volume-mute', 'class' => 'btn-inverse' ]);
        echo '</div>';
        echo closeform();
        closeside();

        $browser_name = 'Generic';
        $browser_version = '';
        $browser_image = '<span class="error-img default"></span>';
        if ( $browser = explode('/', $data['error_browser']) ) {
            $browser_name = $browser[0];
            $browser_version = $browser[1];
            $browser_image = '<span class="error-img"><img alt="" src="' . get_image($browser_name) . '"/></span>';
        }

        $os_name = 'Generic';
        $os_version = '';
        $os_image = '<span class="error-img default"></span>';
        if ( $os = explode('/', $data['error_os']) ) {
            $os_name = $os[0];
            $os_version = $os[1];
            $os_image = '<span class="error-img"><img alt="" src="' . get_image($os_name) . '"/></span>';
        }

        openside();
        echo '<div class="flex">';
        echo '<div class="flex col-xs-4"><span class="error-img default"></span><span><a href="">' . $user['user_name'] . '</a><br>ID:' . $user['user_id'] . '</span></div>';
        echo '<div class="flex col-xs-4">' . $browser_image . '<span><strong>' . $browser_name . '</strong><br>Version: ' . $browser_version . '</span></div>';
        echo '<div class="flex col-xs-4">' . $os_image . '<span><strong>' . $os_name . '</strong><br>Version: ' . $os_version . '</span></div>';
        echo '</div>';
        closeside();

        openside();
        echo '<span id="pf-stat-' . $data['error_id'] . '">' . $issue_status . '</span>';
        echo '<div class="row"><div class="col-xs-12 col-sm-8">';
        echo '<h4>' . $error_class->getErrorTypes($data['error_level']) . '</h4>';
        echo '<code>' . $data['error_message'] . '</code>';

        echo '<div class="codebox line-numbers">';
        echo '<div class="heading">' . $error_class->getMaxFolders($data['error_file']) . ' at line ' . $data['error_line'] . '</div>';
        echo display_error_sourcecode($data);
        echo '</div>';

        echo '</div><div class="col-xs-12 col-sm-4">';
        echo '<div class="spacer-sm">';
        echo '<h6 class="text-uppercase m-0">Last Seen</h6>';
        echo timer($data['error_updated_datestamp'], FALSE, 'ago');
        echo '</div>';
        echo '<div class="spacer-md">';
        echo '<h6 class="text-uppercase m-0">First discovered</h6>';
        echo timer($data['error_timestamp'], FALSE, 'ago');
        echo '</div>';

        echo '<div class="spacer-md">';
        echo '<h6 class="text-uppercase m-0">Assigned</h6>';
        echo $assigned_text;
        echo '</div>';

        echo '<div class="spacer-md">';
        echo '<h6 class="text-uppercase m-0">Reviewed</h6>';
        echo $review_text;
        echo '</div>';

        echo '</div></div>';
        closeside();

    }
    else {

        redirect(clean_request('', [ 'eid' ], FALSE));
    }

}

set_image('Edge', IMAGES . 'errors/edge.svg');
set_image('Chrome', IMAGES . 'errors/chrome.svg');
set_image('Firefox', IMAGES . 'errors/firefox.svg');
set_image('IE', IMAGES . 'errors/ie.svg');
set_image('Opera', IMAGES . 'errors/opera.svg');
set_image('Safari', IMAGES . 'errors/safari.svg');
set_image('Windows', IMAGES . 'errors/windows.svg');
set_image('OSX', IMAGES . 'errors/mac.svg');
set_image('Ubuntu', IMAGES . 'errors/linux.svg');


/* Display source code */
function display_error_sourcecode($data) {

    $thisFileContent = is_file($data['error_file']) ? file($data['error_file']) : [];

    $starting_line = max($data['error_line'] - 20, 1);

    $line_end = min($data['error_line'] + 15, count($thisFileContent));

    $source_code = implode("", array_slice($thisFileContent, $starting_line - 1, $line_end - $starting_line + 1));

    $error_line = $data['error_line'];

    $source_code = explode("\n", str_replace([ "\r\n", "\r", '\n\n' ], "\n", $source_code));
    $source_code = implode("\n", $source_code);

    return '<pre class="line-numbers" data-start="' . $starting_line . '" data-line="' . $error_line . '" tabindex="0"><code class="language-php">' .
           htmlspecialchars($source_code) .
           '</code></pre>';

}


/**
 * @param     $code
 * @param int $maxLength
 *
 * @return string
 */
function code_wrap($code, int $maxLength = 150) {

    $lines = explode("\n", $code);
    $count = count($lines);
    for ( $i = 0; $i < $count; ++$i ) {
        preg_match('`^\s*`', $code, $matches);
        $lines[ $i ] = wordwrap($lines[ $i ], $maxLength, "\n$matches[0]\t", TRUE);
    }

    return implode("\n", $lines);
}
