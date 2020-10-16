<?php
require_once __DIR__.'/../../maincore.php';

function get_forum_attach() {
    $attach_id = get("attachid", FILTER_VALIDATE_INT);
    $attach_size = get('size');
    if ($attach_id && $attach_size) {
        $col = 'attach_name';
        $thumb_folder = FORUM.'attachments/';
        switch($attach_size) {
            case 'thumbnail':
                $col = 'attach_t1_name';
                $thumb_folder = FORUM.'attachments/thumbs/';
                break;
            case 'sm':
                $col = 'attach_t2_name';
                $thumb_folder = FORUM.'attachments/thumbs/';
                break;
            case 'md':
                $col = 'attach_t3_name';
                $thumb_folder = FORUM.'attachments/thumbs/';
                break;
            case 'lg':
                $col = 'attach_t4_name';
                $thumb_folder = FORUM.'attachments/thumbs/';
                break;
        }
        $result = dbquery("SELECT $col, attach_mime FROM ".DB_FORUM_ATTACHMENTS." WHERE attach_id=:aid", [':aid' => (int) $attach_id]);
        if (dbrows($result)) {
            $image_name = dbresult($result, 0);
            $image_mime = dbresult($result, 1);
            if ($image_name && file_exists($thumb_folder.$image_name)) {
                $image = file_get_contents($thumb_folder.$image_name);
                header('Content-type: '.$image_mime);
                header('Content-Length: '. strlen($image));
                echo $image;
            }
        } else {
            echo 'Invalid file specified';
        }
    } else {
        echo 'Invalid file request';
    }
}

get_forum_attach();
