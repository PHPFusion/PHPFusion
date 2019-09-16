<?php
require_once __DIR__.'/../../../../maincore.php';
include INCLUDES.'ajax_include.php';

function attach_insert() {
    $image_info['error'] = 'Invalid file specified.';
    $aid = post('attach_id', FILTER_VALIDATE_INT);
    $user_id = post('user_id', FILTER_VALIDATE_INT);
    $tid = post('thread_id', FILTER_VALIDATE_INT);
    $size = post('attach_size');

    if ($aid && $user_id  &&  $tid && $size) {
        if (\Defender::safe()) {
            $image_info['error'] = 'Original file unspecified.';
            $result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE attach_id=:aid AND post_user=:uid AND thread_id=:tid",
                [':aid' => (int)$aid,
                 ':uid' => (int)$user_id,
                 ':tid' => (int)$tid
                ]);
            if (dbrows($result)) {
                $data = dbarray($result);
                $image_width = 0;
                $thumb_folder = FORUM.'attachments/thumbs/';
                $folder = FORUM.'attachments/';
                if (file_exists($folder.$data['attach_name'])) {
                    $image_ext = strtolower(strrchr($data['attach_name'], "."));
                    $image_name = substr($data['attach_name'], 0, strrpos($data['attach_name'], "."));
                    switch ($image_ext) {
                        case '.gif':
                            $filetype = 1;
                            break;
                        case '.jpg':
                            $filetype = 2;
                            break;
                        case '.png':
                            $filetype = 3;
                            break;
                        default:
                            $filetype = FALSE;
                    }
                    $image_res = getimagesize(FORUM.'attachments/'.$data['attach_name']);
                    if (!file_exists($thumb_folder)) {
                        mkdir($thumb_folder, 0755, TRUE);
                    }
                    $image_info['image_name'] = '[attach]attachid='.$data['attach_id'].'&amp;size=fs[/attach]';
                    $col = '';
                    switch ($size) {
                        case 'remove':

                            // remove the whole attachment
                            dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE attach_id=:aid AND post_user=:uid", [
                                ':aid' => (int) $aid,
                                ':uid' => (int) $user_id
                            ]);

                            $image_info['image_name'] = '';

                            return $image_info;

                        case 'thumbnail':
                            $image_width = 120;
                            $col = 'attach_t1_name';
                            break;
                        case 'sm':
                            $image_width = 200;
                            $col = 'attach_t2_name';
                            break;
                        case 'md':
                            $image_width = 330;
                            $col = 'attach_t3_name';
                            break;
                        case 'lg':
                            $image_width = 1440;
                            $col = 'attach_t4_name';
                            break;
                        case 'fs':
                            break;
                    }
                    $image_height = $image_width;
                    if ($image_res[0] > $image_width || $image_res[1] > $image_height) {
                        $image_name = $image_name.'_'.$size.$image_ext; //filename_exists($thumb_folder, $image_name.'_'.$size.$image_ext);
                        $image_info['error'] = '';
                        require_once INCLUDES.'photo_functions_include.php';
                        createthumbnail($filetype, FORUM.'attachments/'.$data['attach_name'], $thumb_folder.$image_name, $image_width, $image_height);

                        if ($col) {
                            dbquery("UPDATE ".DB_FORUM_ATTACHMENTS." SET `$col` =:i_name WHERE attach_id=:aid", [
                                ':aid'=> (int)$aid,
                                ':i_name' => $image_name
                            ]);
                        }

                        $image_info['image_name'] = '[attach]attachid='.$data['attach_id'].'&amp;size='.$size.'[/attach]';
                    }
                }
            }
        }
    }

    return $image_info;
}

echo json_encode(attach_insert());
