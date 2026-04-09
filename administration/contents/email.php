<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: email.php
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


defined('IN_FUSION') || exit;
$locale = fusion_get_locale('', LOCALE . LOCALESET . 'admin/emails.php');

$contents = [
    'post'        => 'pf_post',
    'view'        => 'pf_view',
    'button'      => 'pf_submit',
    'js'          => 'pf_js',
    'link'        => ( $admin_link ?? '' ),
    'settings'    => TRUE,
    'title'       => $locale['MAIL_000'],
    'description' => '',
    'left_nav'    => 'pf_form',
    'fullwidth'   => TRUE,
    'files'       => [
        INCLUDES . 'sendmail_include.php'
    ],
    'actions'     => [ 'post' => [ 'cancel' => 'emailtemplateFrm', 'savetemplate' => 'emailtemplateFrm', 'testtemplate' => 'emailtemplateFrm' ] ]
];

function pf_post() {

    $locale = fusion_get_locale();
    if ( admin_post('cancel') ) {
        redirect(ADMIN_CURRENT_DIR);
    }
    else if ( admin_post('savetemplate') ) {

        // omit template data.
        $data = [
            'template_id'           => sanitizer('template_id', 0, 'template_id'),
            'template_key'          => sanitizer('template_key', '', 'template_key'),
            'template_title'        => sanitizer('template_title', '0', 'template_title'),
            'template_header'       => sanitizer('template_header', 'clean', 'template_header'),
            'template_header_align' => sanitizer('template_header_align', 'center', 'template_header_align'),
            'template_body'         => sanitizer('template_body', 'clean', 'template_body'),
            'template_body_align'   => sanitizer('template_body_align', 'left', 'template_body_align'),
            'template_image'        => sanitizer('template_image', '1', 'template_image'),
            'template_footer'       => sanitizer('template_footer', '', 'template_footer'),
            'template_active'       => sanitizer('template_active', '0', 'template_active')
            // 'template_subject'=> sanitizer('template_subject', '', 'template_subject'),
            // 'template_content'=> sanitizer('template_content', '', 'template_content'),
        ];
        if ( ! $data['template_id'] || ! $data['template_key'] ) {
            fusion_stop('Template could not be updated due to an invalid template key');
        }
        if ( fusion_safe() ) {
            dbquery_insert(DB_EMAIL_TEMPLATES, $data, 'update');
            add_notice('success', $locale['MAIL_001']);
            redirect(ADMIN_CURRENT_DIR);
        }
    }
    else if ( admin_post('testtemplate') ) {
        $data = get_email_admin_data();
        $userdata = fusion_get_userdata();
        $settings = fusion_get_settings();
        $email_subject = $data['template_subject'];
        $email_body = get_email_templates($data['template_subject'], $data['template_content']);
        $email_send = $userdata['user_email'];
        $email_from = $settings['siteemail'];
        $email_user_from = $settings['siteusername'];
        $email_user_send = $userdata['user_name'];
        try {
            $email = sendemail($email_user_send,
                               $email_send,
                               $email_user_from,
                               $email_from,
                               $email_subject,
                               $email_body,
                               'html');
            if ( $email ) {
                add_notice('success', 'Email sent');
                add_notice('info', sprintf($locale['MAIL_007'], $email_send));
                redirect(FUSION_REQUEST);
            }
        } catch ( \Exception $e ) {
            set_error(E_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine());
        }
    }


    else if ( isset($_POST['test_template']) ) {
        $data = [
            'template_id'           => form_sanitizer($_POST['template_id'], '', 'template_id'),
            'template_key'          => form_sanitizer($_POST['template_key'], '', 'template_key'),
            'template_format'       => form_sanitizer($_POST['template_format'], '', 'template_format'),
            'template_subject'      => form_sanitizer($_POST['template_subject'], '', 'template_subject'),
            'template_content'      => form_sanitizer($_POST['template_content'], '', 'template_content'),
            'template_active'       => form_sanitizer($_POST['template_active'], '', 'template_active'),
            'template_sender_name'  => form_sanitizer($_POST['template_sender_name'], '', 'template_sender_name'),
            'template_sender_email' => form_sanitizer($_POST['template_sender_email'], '', 'template_sender_email'),
        ];
        if ( \defender::safe() ) {
            require_once INCLUDES . 'sendmail_include.php';
            dbquery_insert(DB_EMAIL_TEMPLATES, $data, 'update');
            fusion_sendmail($data['template_key'],
                            $locale['MAIL_002'],
                            $locale['MAIL_003'],
                            $locale['MAIL_004'],
                            $locale['MAIL_005'],
                            $locale['MAIL_006'],
                            fusion_get_userdata('user_email'),
                            $data['template_sender_name'],
                            $data['template_sender_email']);
            add_notice('success', sprintf($locale['MAIL_007'], fusion_get_userdata('user_email')));
            redirect(FUSION_SELF . fusion_get_aidlink() . '&amp;template_id=' . $data['template_id']);
        }
    }
}

function pf_form() {

    $locale = fusion_get_locale();
    ob_start();

    if ( $id = get('id') ) {

        $res = dbquery('SELECT * FROM ' . DB_EMAIL_TEMPLATES . ' WHERE template_key=:key LIMIT 1',
                       [ ':key' => $id ]);

        if ( dbrows($res) ) {

            $data = dbarray($res);

            echo openform('emailtemplateFrm', 'POST');
            echo form_hidden('template_id', '', $data['template_id']);
            echo form_hidden('template_key', '', $data['template_key']);
            echo form_hidden('template_active', '', $data['template_active']);

            openside('');
            echo form_checkbox('template_title',
                               'Template title',
                               $data['template_title'],
                               [
                                   'toggle' => TRUE, 'inline' => TRUE,
                               ]);
            echo form_select('template_header', 'Header style', $data['template_header'], [
                'options'          => [
                    'serif' => 'Classic serif',
                    'clean' => 'Clean sans-serif',
                ],
                'select2_disabled' => TRUE,
                'inline'           => TRUE,
            ]);
            echo form_btngroup('template_header_align', 'Header Alignment', $data['template_header_align'], [
                'inline'  => TRUE,
                'options' => [
                    'left'   => '<i class="far fa-align-left"></i>',
                    'center' => '<i class="far fa-align-center"></i>',
                    'right'  => '<i class="far fa-align-right"></i>',
                ]
            ]);
            echo form_select('template_body', 'Body style', $data['template_body'], [
                'options'          => [
                    'serif' => 'Classic serif',
                    'clean' => 'Clean sans-serif',
                ],
                'select2_disabled' => TRUE,
                'inline'           => TRUE,
            ]);
            echo form_btngroup('template_body_align', 'Body Alignment', $data['template_body_align'], [
                'inline'  => TRUE,
                'options' => [
                    'left'   => '<i class="far fa-align-left"></i>',
                    'center' => '<i class="far fa-align-center"></i>',
                    'right'  => '<i class="far fa-align-right"></i>',
                ]
            ]);
            echo form_checkbox('template_image',
                               'Featured image',
                               $data['template_image'],
                               [
                                   'toggle' => TRUE,
                                   'inline' => TRUE,
                               ]);
            echo form_textarea('template_footer', 'Email footer', $data['template_footer'], [
                'inline'  => FALSE,
                'ext_tip' => 'Any extra information or legal text'
            ]);
            closeside();
            echo closeform();

            //
            //
            // $html .= form_select('template_active', $locale['MAIL_010'], $data['template_active'], [
            //     'options'     => [ $locale['disable'], $locale['enable'] ],
            //     'placeholder' => $locale['choose'],
            //     'inline'      => TRUE
            // ]);
            //
            //
            // $html .= "<div id='active_info' " . $html_helper . '>' . sprintf($locale['MAIL_011'], $html_text) . "</div>\n";
            // $html .= "<div id='inactive_info' " . $text_helper . ' >' . $locale['MAIL_012'] . "</div>\n";
            //
            //
            // $html .= form_select('template_format', $locale['MAIL_013'], $data['template_format'], [
            //     'options' => [
            //         'html'  => $locale['MAIL_008'],
            //         'plain' => $locale['MAIL_009']
            //     ],
            //     'inline'  => TRUE
            // ]);
            //
            //
            // $html .= "<div id='html_info' class='m-t-10'>" . $locale['MAIL_014'] . "</div>\n";
            //
            // $html .= form_text('template_sender_name',
            //                $data['template_key'] == 'CONTACT' ? $locale['MAIL_015'] : $locale['MAIL_016'],
            //                $data['template_sender_name'],
            //                [
            //                    'required'   => TRUE,
            //                    'error_text' => $locale['MAIL_017'],
            //                    'inline'     => TRUE,
            //                    'class'      => 'm-b-0',
            //                    'ext_tip'    => ( $data['template_key'] == 'CONTACT' ? '' : $locale['MAIL_018'] )
            //                ]);
            //
            // $html .= form_text('template_sender_email',
            //                $data['template_key'] == 'CONTACT' ? $locale['MAIL_019'] : $locale['MAIL_020'],
            //                $data['template_sender_email'],
            //                [
            //                    'required'   => TRUE,
            //                    'error_text' => $locale['MAIL_021'],
            //                    'inline'     => TRUE,
            //                    'class'      => 'm-b-0'
            //
            //                ]);
            //
            // // openside('');
            // // echo form_button('save_template', $locale['save'], $locale['save'], [ 'class' => 'btn-primary' ]);
            // // echo form_button('test_template', $locale['MAIL_023'], $locale['MAIL_023'], [ 'class' => 'btn-default' ]);
            // // echo form_button('reset', $locale['MAIL_024'], $locale['MAIL_024'], [ 'class' => 'btn-default' ]);
            // // closeside();
            //
            //
            //
            // // openside('');
            // // $html .= form_text('template_subject', $locale['MAIL_025'], $data['template_subject'], [
            // //     'required'   => TRUE,
            // //     'error_text' => $locale['MAIL_026'],
            // //     'autosize'   => TRUE
            // // ]);
            //
            // // echo "<div class='btn-group'>\n";
            // // echo "<button type='button' class='btn btn-default button' value='[SITENAME]' onclick=\"insertText('template_subject', '[SITENAME]', 'emailtemplateform');\">" . $locale['MAIL_027'] . "</button>\n";
            // // echo "<button type='button' class='btn btn-default button' value='[SITEURL]' onclick=\"insertText('template_subject', '[SITEURL]', 'emailtemplateform');\">" . $locale['MAIL_028'] . "</button>\n";
            // // echo "<button type='button' class='btn btn-default button' value='[SUBJECT]' onclick=\"insertText('template_subject', '[SUBJECT]', 'emailtemplateform');\">" . $locale['MAIL_025'] . "</button>\n";
            // // echo "<button type='button' class='btn btn-default button' value='[USER]' onclick=\"insertText('template_subject', '[USER]', 'emailtemplateform');\">" . $locale['user'] . "</button>\n";
            // // echo "<button type='button' class='btn btn-default button' value='[SENDER]' onclick=\"insertText('template_subject', '[SENDER]', 'emailtemplateform');\">" . $locale['MAIL_029'] . "</button>\n";
            // // echo "<button type='button' class='btn btn-default button' value='[RECEIVER]' onclick=\"insertText('template_subject', '[RECEIVER]', 'emailtemplateform');\">" . $locale['MAIL_030'] . "</button>\n";
            // // echo "</div>\n";
            //
            // //
            // // echo "<div class='m-t-20 m-b-20'>\n";
            // // echo "<a class='pointer' data-target='#email_tutorial' data-toggle='collapse' aria-expanded='false' aria-controls='email_tutorial'>" . $locale['MAIL_031'] . '</a>';
            // // echo "</div>\n";
            // // echo "<div id='email_tutorial' class='collapse'>\n";
            // // echo "<div class='table-responsive'><table class='table'>\n";
            // // echo "<tr>\n";
            // // echo '<th>' . $locale['MAIL_032'] . "</th>\n";
            // // echo '<th>' . $locale['MAIL_033'] . "</th>\n";
            // // echo "</tr>\n";
            // // echo "<tbody>\n";
            // // echo "<tr>\n";
            // // echo "<td>[SITENAME]</td>\n";
            // // echo '<td>' . fusion_get_settings('sitename') . "</td>\n";
            // // echo "</tr>\n<tr>\n";
            // // echo "<td>[SITEURL]</td>\n";
            // // echo '<td>' . fusion_get_settings('siteurl') . "</td>\n";
            // // echo "</tr>\n<tr>\n";
            // // echo "<td>[SUBJECT]</td>\n";
            // // echo '<td>' . $locale['MAIL_034'] . "</td>\n";
            // // echo "</tr>\n<tr>\n";
            // // echo "<td>[MESSAGE]</td>\n";
            // // echo '<td>' . $locale['MAIL_035'] . "</td>\n";
            // // echo "</tr>\n<tr>\n";
            // // echo "<td>[USER]</td>\n";
            // // echo '<td>' . $locale['MAIL_036'] . "</td>\n";
            // // echo "</tr>\n<tr>\n";
            // // echo "<td>[SENDER]</td>\n";
            // // echo '<td>' . $locale['MAIL_037'] . "</td>\n";
            // // echo "</tr>\n<tr>\n";
            // // echo "<td>[RECEIVER]</td>\n";
            // // echo '<td>' . $locale['MAIL_038'] . "</td>\n";
            // // echo "</tr>\n<tr>\n";
            // // echo "<td>[THREAD_URL]</td>\n";
            // // echo '<td>' . $locale['MAIL_039'] . "</td>\n";
            // // echo "</tr>\n";
            // // echo "</tbody>\n</table>\n</div>";
            // // echo "</div>\n";
            // // closeside();
            //
            // // openside('');
            // // if ( $data['template_format'] == 'plain' ) {
            // //     add_to_head('<style>#template_content { border: none; }</style>');
            // // }
            // // $html .= form_textarea('template_content', $locale['MAIL_040'], $data['template_content'], [
            // //     'required'   => TRUE,
            // //     'error_text' => $locale['MAIL_041'],
            // //     'autosize'   => TRUE,
            // //     'preview'    => $data['template_format'] == 'html',
            // //     'html'       => $data['template_format'] == 'html',
            // //     'inputform'  => 'emailtemplateform'
            // // ]);
            // // echo "<div class='btn-group'>\n";
            // // echo "<button type='button' class='btn btn-default button' value='[SUBJECT]' onclick=\"insertText('template_content', '[SUBJECT]', 'emailtemplateform');\">" . $locale['MAIL_025'] . "</button>\n";
            // // echo "<button type='button' class='btn btn-default button' value='[MESSAGE]' onclick=\"insertText('template_content', '[MESSAGE]', 'emailtemplateform');\">" . $locale['MAIL_040'] . "</button>\n";
            // // echo "<button type='button' class='btn btn-default button' value='[SENDER]' onclick=\"insertText('template_content', '[SENDER]', 'emailtemplateform');\">" . $locale['MAIL_029'] . "</button>\n";
            // // echo "<button type='button' class='btn btn-default button' value='[RECEIVER]' onclick=\"insertText('template_content', '[RECEIVER]', 'emailtemplateform');\">" . $locale['MAIL_030'] . "</button>\n";
            // // echo "<button type='button' class='btn btn-default button' value='[USER]' onclick=\"insertText('template_content', '[USER]', 'emailtemplateform');\">" . $locale['user'] . "</button>\n";
            // // echo "<button type='button' class='btn btn-default button' value='[SITENAME]' onclick=\"insertText('template_content', '[SITENAME]', 'emailtemplateform');\">" . $locale['MAIL_027'] . "</button>\n";
            // // echo "<button type='button' class='btn btn-default button' value='[SITEURL]' onclick=\"insertText('template_content', '[SITEURL]', 'emailtemplateform');\">" . $locale['MAIL_028'] . "</button>\n";
            // // echo "<button type='button' class='btn btn-default button' value='[THREAD_URL]' onclick=\"insertText('template_content', '[THREAD_URL]', 'emailtemplateform');\">" . $locale['MAIL_042'] . "</button>\n";
            // // echo "</div>\n";
            // // echo "<div id='html_buttons' class='" . ( $data['template_format'] == 'html' ? 'm-t-5' : 'display-none' ) . "'>\n";
            // // echo "<button type='button' class='btn btn-default button' value='" . $locale['MAIL_043'] . "' onMousedown=\"this.form.template_content.focus();this.form.template_content.select();\" onmouseup=\"addText('template_content', '&lt;body style=\'background-color:#D7F9D7;\'&gt;', '&lt;/body&gt;', 'emailtemplateform');\">\n" . $locale['MAIL_043'] . "</button>\n";
            // // $folder = BASEDIR . 'images/';
            // // $image_files = makefilelist($folder, '.|..|index.php', TRUE);
            // // $opts = [];
            // // foreach ( $image_files as $image ) {
            // //     $opts[ $image ] = $image;
            // // }
            // // echo form_select('insertimage', '', '', [
            // //     'options'     => $opts,
            // //     'placeholder' => $locale['MAIL_044'],
            // //     'allowclear'  => TRUE
            // // ]);
            // // echo "</div>\n";
            // // closeside();

            // // echo form_button('test_template',
            // //                  $locale['MAIL_023'],
            // //                  $locale['MAIL_023'],
            // //                  [ 'class' => 'btn-default', 'input_id' => 'test_template2' ]);
            // // echo form_button('reset',
            // //                  $locale['MAIL_024'],
            // //                  $locale['MAIL_024'],
            // //                  [ 'class' => 'btn-default', 'input_id' => 'reset2' ]);
            //
            // $html .= closeform();
            return ob_get_clean();
        }
    }
}

function get_email_admin_data() {

    static $data;
    $settings = fusion_get_settings();
    $userdata = fusion_get_userdata();

    if ( empty($value) ) {
        if ( $id = get('id') ) {

            $result = dbquery('SELECT * FROM ' . DB_EMAIL_TEMPLATES . ' WHERE template_key=:key LIMIT 1',
                              [ ':key' => $id ]);
            if ( dbrows($result) ) {

                $data = dbarray($result);

                // This is demo data
                $replacements = [
                    '[USER]'           => $settings['siteusername'],
                    '[SITEUSER]'       => $settings['siteusername'],
                    '[SITENAME]'       => $settings['sitename'],
                    '[SITEURL]'        => $settings['siteurl'],
                    '[SITEEMAIL]'      => $settings['siteemail'],
                    '[RECEIVER]'       => $userdata['user_name'],
                    '[SUBJECT]'        => '@Captioned Subject',
                    '[MESSAGE]'        => '@Captioned Message',
                    '[SENDER]'         => $settings['siteusername'],
                    '[LINK]'           => '<a href="#">Caption Link</a>',
                    '[LINK_BUTTON]'    => '<a href="#" class="btn btn-inverse">Caption Button</a>',
                    '[LINK_URL]'       => '#',
                    '[ADMIN_USERNAME]' => $userdata['user_name'],
                    '[REASON]'         => '@Captioned Reason',
                ];


                $data['template_subject'] = strtr($data['template_subject'], $replacements);
                $data['template_content'] = strtr($data['template_content'], $replacements);

                return $data;
            }
        }
        redirect(ADMIN_CURRENT_DIR);

    }

    return $data;
}

function preview_window() {

    require_once INCLUDES . 'sendmail_include.php';

    $settings = fusion_get_settings();
    $userdata = fusion_get_userdata();

    if ( $data = get_email_admin_data() ) {

        add_breadcrumb([ 'link' => FUSION_REQUEST, 'title' => $data['template_key'] ]);

        echo '<div class="theme-preview-container">';
        echo '<div class="browser">';
        echo '<div class="browser-elem">';
        echo '<ul><li></li><li></li><li></li></ul>';
        echo '<div><span class="favicon"></span><span class="site-title">Email: ' . $data['template_subject'] . '</span></div>';
        echo '</div>';
        echo '<div class="browser-frame">';
        echo '<div class="frame">';
        echo '<div class="email-preview-frame">';
        echo '<div class="email-wrapper">';
        echo '<div class="email-header">';
        echo '<p><span class="strong">' . $settings['sitename'] . '</span>' . htmlspecialchars('<') . $settings['siteemail'] . htmlspecialchars('>') . '</p>';
        echo '<p><span>To:</span> ' . $userdata['user_name'] . htmlspecialchars('<') . $userdata['user_email'] . htmlspecialchars('>') . '</p>';
        echo '</div>';
        echo '<div class="email-body">';
        echo get_email_templates($data['template_subject'], $data['template_content']);
        echo '</div>';
        echo '</div></div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

    }


}

function pf_js() {

    return "
    let toggleEmailHeading = function() {
         $('.pf--email-heading').hide();
         if ($('#template_title').is(':checked')) {
            $('.pf--email-heading').show();
         }
     },
      toggleEmailTitle = function() {
        $('.pf--email-title').removeClass('serif');
        if ($('#template_header').val() == 'serif') {
            $('.pf--email-title').addClass('serif');
         }
     },
       toggleEmailBody = function() {
        $('.pf--email-body').removeClass('serif');
        if ($('#template_body').val() == 'serif') {
            $('.pf--email-body').addClass('serif');
         }
     },
     toggleEmailBodyAg = function() {
        let v = $('#template_body_align-text').val();
        $('.pf--email-body').removeClass('left');
        $('.pf--email-body').removeClass('right');
        $('.pf--email-body').removeClass('center'); 
        if (v.length) {
            $('.pf--email-body').addClass(v);
        }        
     },
    toggleEmailTitleAg = function() {
      let v = $('#template_header_align-text').val();
        $('.pf--email-title').removeClass('left');
        $('.pf--email-title').removeClass('right');
        $('.pf--email-title').removeClass('center'); 
        if (v.length) {
            $('.pf--email-title').addClass(v);
        }        
    },
    toggleEmailImage = function() {
        $('.pf--email-image-wrapper').hide();
         if ($('#template_image').is(':checked')) {
            $('.pf--email-image-wrapper').show();
         }
    },
    toggleEmailFooter = function() {
    let val =  $('#template_footer').val()
        // .replace('\\n', '<br>')
        // .replace(/ /g, '&nbsp')
        .replace(/&/g, '&')
        .replace(/</g, '<')
        .replace(/>/g, '>')
        .replace(/\"/g, '\"')
        .replace(/'/g, '\'');
        $('.pf--email-ext-footer').text( val );    
    };
        
    toggleEmailHeading();
    toggleEmailTitle();
    toggleEmailBody();
    toggleEmailBodyAg();
    toggleEmailImage();
    toggleEmailTitleAg();
   
    $(document).on('change', '#template_title', function(e) { toggleEmailHeading() });
    $(document).on('change', '#template_image', function(e) { toggleEmailImage() });
    $(document).on('change', '#template_header', function(e) { toggleEmailTitle() });
    $(document).on('change', '#template_body', function(e) { toggleEmailBody() });
    $(document).on('click', '#template_body_align > button', function(e) { toggleEmailBodyAg() });
    $(document).on('click', '#template_header_align > button', function(e) { toggleEmailTitleAg() });
    $(document).on('keyup paste change', '#template_footer', function(e) { toggleEmailFooter() });
   
    /* Ui */
    $(document).on('click', 'input[name=\"template_status\"]', function(e) {
        // target form
        $('#template_active').val( $(this).val() );
        $('#testtemplate').attr('disabled', 'disabled');
    });

    $(document).on('click', '.email-body a, .email-body button', function(e) {
        e.preventDefault();
    });
        
    $(document).on('change', '#emailtemplateFrm', function() {
        $('#testtemplate').attr('disabled', 'disabled');
     });         
    ";
}

function listing() {

    $locale = fusion_get_locale();

    $result = dbquery('SELECT * FROM ' . DB_EMAIL_TEMPLATES . ( multilang_table('ET') ? " WHERE template_language='" . LANGUAGE . "'" : '' ) . '
        ORDER BY template_id');


    echo '<section class="clearfix">';
    echo '<ol class="pf--post-list">';
    echo '<li class="pf--post-header pf--post-list-row">
        <div class="pf--post-list-header">Templates</div>
        <div class="pf--post-list-header">Key</div>
        <div class="pf--post-list-header">Status</div>
        </li>';
    if ( dbrows($result) ) {
        while ( $data = dbarray($result) ) {

            echo '<li class="pf--post-list-row">
                <a class="pf--post-list-item" href="' . ADMIN_CURRENT_DIR . '&pg=form&id=' . $data['template_key'] . '"><h4>' . $data['template_name'] . '</h4></a>
                <a href="' . ADMIN_CURRENT_DIR . '&pg=form&id=' . $data['template_key'] . '" class="pf--post-list-item">' . $data['template_key'] . '</a>
                <a href="' . ADMIN_CURRENT_DIR . '&pg=form&id=' . $data['template_key'] . '" class="pf--post-list-item"><span class="label ' . ( $data['template_active'] ? 'label-default' : 'label-danger' ) . '">' . ( $data['template_active'] ? $locale['published'] : $locale['unpublished'] ) . '</span></a>
                </li>
                ';

        }
    }


    echo '</ol>';
    echo '</section>';


    $template = [];

}

function pf_view() {

    if ( get('pg') == 'form' && get('id') ) {

        preview_window();
    }
    else {

        listing();
    }

}

function pf_submit()
: string {

    $locale = fusion_get_locale();

    if ( $id = get('id') ) {

        $status = dbresult(dbquery("SELECT template_active FROM " . DB_EMAIL_TEMPLATES . " WHERE template_key=:id",
                                   [ ':id' => $id ]),
                           0);

        return
            pf_admin_status_ui('template_status', $locale['MAIL_045'], $status, [
                'options'     => [
                    '0' => $locale['unpublished'] . '<small>' . $locale['MAIL_046'] . '</small>',
                    '1' => $locale['published'] . '<small>' . $locale['MAIL_047'] . '</small>',
                ],
                'submit_name' => 'savetemplate',
                'cancel_name' => 'cancel',
            ]) .
            form_button('testtemplate', $locale['MAIL_023'], $locale['MAIL_023'], [ 'class' => 'btn-primary' ]);

    }

    return '';
}
