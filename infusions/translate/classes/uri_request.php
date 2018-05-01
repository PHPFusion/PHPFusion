<?php
namespace Translate;

class Translate_URI extends Administration {

    public static function get_link_exclusions() {
        return [
            self::$action_key,
            self::$package_key,
            self::$file_key,
            self::$item_key,
            'download',
            'language',
            'file_rows'
        ];
    }

    public static function get_link($key, $id = 0, $sub_id = 0, $language = LANGUAGE) {
        $custom_keys = self::get_link_exclusions();
        $array = array(
            'new_package' => clean_request(self::$action_key."=new_package", $custom_keys, FALSE),
            'edit_package' => clean_request(self::$action_key."=edit_package&".self::$package_key."=".$id, $custom_keys, FALSE),
            'delete_package' => clean_request(self::$action_key."=delete_package&".self::$package_key."=".$id, $custom_keys, FALSE),

            'view_package' => clean_request(self::$package_key."=".$id, $custom_keys, FALSE),

            'new_file' => clean_request(self::$action_key."=new_file&".self::$file_key."=".$id, $custom_keys, FALSE),
            'delete_file' => clean_request(self::$action_key."=del_file&".self::$file_key."=".$id, $custom_keys, FALSE),

            'upload_file' => clean_request(self::$action_key."=import_file&".self::$file_key."=".$id, $custom_keys, FALSE),
            'upload_translations' => clean_request(self::$action_key."=import_locale&".self::$package_key."=".$id."&".self::$file_key."=".$sub_id, $custom_keys, FALSE),

            'view_translations' => clean_request(self::$package_key."=".$id."&".self::$file_key."=".$sub_id, $custom_keys, FALSE),

            'download_file' => clean_request('download_file=1&'.self::$package_key."=".$id."&".self::$file_key."=".$sub_id."&language=".$language, $custom_keys, FALSE),
            'download_pack' => clean_request('download_pack=1&'.self::$package_key."=".$id."&language=".$language, $custom_keys, FALSE),
        );
        if (isset($array[$key])) {
            return $array[$key];
        }

        return NULL;
    }


}