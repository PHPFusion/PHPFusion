<?php
require_once __DIR__.'/../../../../maincore.php';
include INCLUDES.'ajax_include.php';
include FORUM.'forum_include.php';

function post_image_upload() {

    $forum_settings = get_settings('forum');
    $settings = fusion_get_settings();
    $output = [
        'error'         => 'Invalid actions',
        'user_id'       => post('user_id', FILTER_VALIDATE_INT),
        'thread_id'     => post('thread_id', FILTER_VALIDATE_INT),
        'defender_safe' => fusion_safe()
    ];

    if (fusion_safe()) {

        if (!empty($_FILES['upload']['tmp_name']) && $output['thread_id'] && $output['user_id']) {

            $result = dbquery("SELECT forum_post, forum_lock, forum_allow_attach FROM ".DB_FORUM_THREADS." tt INNER JOIN ".DB_FORUMS." fo ON fo.forum_id=tt.forum_id WHERE tt.thread_id=:tid", [
                ':tid' => (int)$output['thread_id']
            ]);
            if (dbrows($result)) {
                // can post.
                $forum_data = dbarray($result);
                if (checkgroup($forum_data['forum_post']) && $forum_data['forum_lock'] == FALSE && $forum_data['forum_allow_attach'] == 1) {
                    foreach ($_FILES['upload']['tmp_name'] as $index => $file_path) {

                        $image_info['error'] = '';

                        if (is_uploaded_file($file_path)) {

                            $target_name = $_FILES['upload']['name'][$index];
                            $target_size = $_FILES['upload']['size'][$index];
                            //$tmp_name = $_FILES['upload']['tmp_name'][$index];

                            if ($target_name != "" && !preg_match("/[^a-zA-Z0-9_-]/", $target_name)) {
                                $image_name = $target_name;
                            } else {
                                $image_name = stripfilename(substr($target_name, 0, strrpos($target_name, ".")));
                            }

                            $image_ext = strtolower(strrchr($target_name, '.'));

                            $image_info = [
                                "image"         => '',
                                "target_folder" => FORUM.'attachments/',
                                "valid_ext"     => ['.jpg', '.jpeg', '.png', '.png', '.svg', '.gif', '.bmp'],
                                "max_size"      => $forum_settings['forum_attachmax'],
                                'image_name'    => $image_name.$image_ext,
                                'image_ext'     => $image_ext,
                                'image_size'    => $_FILES['upload']['size'][$index],
                                'thumb1'        => TRUE,
                                'thumb1_name'   => $image_name.'_t1'.$image_ext,
                                'thumb2'        => TRUE,
                                'thumb2_name'   => $image_name.'_t2'.$image_ext,
                                'error'         => 0,
                            ];

                            if ($target_size) {

                                if (\Defender\ImageValidation::mime_check($file_path, $image_ext, $image_info['valid_ext']) === TRUE) {

                                    $image_res = getimagesize($file_path);
                                    $image_info['image_width'] = $image_res[0];
                                    $image_info['image_height'] = $image_res[1];

                                    if ($target_size > $image_info['max_size']) {
                                        // Invalid file size
                                        $image_info['error'] = 'Image exceeded allowable filesize';

                                    } else if ($settings['mime_check'] && !verify_image($file_path)) {
                                        // Failed payload scan
                                        $image_info['error'] = 'Image contains malicious payload';
                                    } else if ($image_res[0] > 1920 || $image_res[1] > 1080) {
                                        // Invalid image resolution
                                        $image_info['error'] = 'Image exceeded allowable resolution size of 1920 x 1080';
                                    } else {
                                        if (!file_exists($image_info['target_folder'])) {
                                            mkdir($image_info['target_folder'], 0755);
                                        }
                                        $image_name_full = filename_exists($image_info['target_folder'], $image_name.$image_ext);

                                        $image_info['image_name'] = $image_name_full;
                                        $image_info['image'] = TRUE;

                                        move_uploaded_file($file_path, $image_info['target_folder'].$image_name_full);

                                        if (function_exists("chmod")) {
                                            chmod($image_info['target_folder'].$image_name_full, 0755);
                                        }

                                        // run a dbquery
                                        $finfo = new \finfo(FILEINFO_MIME_TYPE);

                                        $attach_data = [
                                            'attach_id'    => 0,
                                            'thread_id'    => (int)$output['thread_id'],
                                            'post_id'      => 0,
                                            'post_user'    => (int)$output['user_id'],
                                            'attach_name'  => $image_name_full,
                                            'attach_mime'  => $finfo->file($image_info['target_folder'].$image_name_full),
                                            'attach_size'  => $target_size,
                                            'attach_count' => 0
                                        ];

                                        $attach_id = dbquery_insert(DB_FORUM_ATTACHMENTS, $attach_data, 'save');

                                        $tpl = \PHPFusion\Template::getInstance('attach');
                                        $tpl->set_template(get_forum_template('forum_qr_attach'));
                                        $tpl->set_block('attachments', [
                                            'attach_container_id'   => 'atc_'.$attach_id,
                                            'image_path'            => $image_info['target_folder'].$image_name_full,
                                            'image_name'            => $image_name_full,
                                            'thumbnail_insert_link' => '<a href="#" class="insert-image" data-size="thumbnail" data-id="'.$attach_id.'">Thumbnail</a>',
                                            'sm_insert_link'        => ' <a href="#" class="insert-image" data-size="sm" data-id="'.$attach_id.'">Small</a>',
                                            'md_insert_link'        => '<a href="#" class="insert-image" data-size="md" data-id="'.$attach_id.'">Medium</a>',
                                            'lg_insert_link'        => '<a href="#" class="insert-image" data-size="md" data-id="'.$attach_id.'">Large</a>',
                                            'fs_insert_link'        => '<a href="#" class="insert-image" data-size="fs" data-id="'.$attach_id.'">Fullsize</a>',
                                            'remove_link'           => '<a href="#" class="insert-image" data-size="remove" data-id="'.$attach_id.'">Remove</a>',
                                        ]);
                                        $image_info['html'] = $tpl->get_output();

                                    }
                                } else {
                                    // Invalid mime check
                                    $image_info = ["error" => 'Invalid mime check'];
                                }
                            } else {
                                // The image is invalid
                                $image_info['error'] = 'Invalid image specified';
                            }
                        } else {
                            $image_info['error'] = 'Image not specified';
                        }

                        if ($image_info['error']) {
                            //         $image_info['html'] = '<hr/><div class="clearfix">
                            // <div class="pull-left m-r-10"><img src="'.get_image('imagenotfound').'" style="width:50px;"/></div>
                            // <div class="overflow-hide"><span class="alert alert-warning p-5">'.$image_info['error'].'</span></div>
                            // </div>';
                        }

                        $output[$index] = $image_info;
                    }
                }
            }
        }
        if (!empty($image_info)) {
            $output['error'] = '';
        }
    }
    return (array)$output;
}

echo json_encode(post_image_upload());
exit(); // terminate

