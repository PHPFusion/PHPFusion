<?php
namespace Defender;
/**
 * Class Mimecheck
 * Check file types of the uploaded file with known mime types list to
 * prevent uploading unwanted files if enabled. This feature is to detect payload
 * in image extensions that has been partly encoded.
 */
class ImageValidation {

    public static function ValidateMime() {
        if (fusion_get_settings('mime_check')) {
            if (isset($_FILES) && count($_FILES)) {
                require_once (INCLUDES. 'mimetypes_include.php');
                $mime_types = mimeTypes();

                foreach ($_FILES as $each) {

                    if (isset($each['name'])
                        && !empty($each['name']) && !empty($each['tmp_name'])) {

                        $file_info = pathinfo($each['name']);
                        $extension = $file_info['extension'];

                        if (array_key_exists($extension, $mime_types)) {

                            if (is_array($mime_types[$extension])) {
                                $valid_mimetype = FALSE;
                                foreach ($mime_types[$extension] as $each_mimetype) {
                                    if ($each_mimetype == $each['type']) {
                                        $valid_mimetype = TRUE;
                                        break;
                                    }
                                }
                                if (!$valid_mimetype) {
                                    die('Prevented an unwanted file upload attempt!');
                                }
                                unset($valid_mimetype);
                            } else {
                                if ($mime_types[$extension] != $each['type']) {
                                    die('Prevented an unwanted file upload attempt!');
                                }
                            }
                        }
                        unset($file_info, $extension);
                    }
                }
                unset($mime_types);
            }
        }
    }
}