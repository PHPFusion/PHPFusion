<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: AdminLTE/classes/Search.inc
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace AdminLTE;

require_once INCLUDES.'classes/PHPFusion/Admins.inc';

use PHPFusion\Admins;

function flatten_array($result) {
    return call_user_func_array('array_merge', $result);
}

class Search {
    private $result = [
        'data'   => [],
        'count'  => 0,
        'status' => ''
    ];

    public function __construct() {
        if ($this->AuthorizeAid()) {
            $this->SearchPages();
            $message = !empty($this->result['status']) ? self::SetLocale($this->result['status']) : '';

            if (!empty($message)) {
                echo '<li class="header text-white">'.$message.'</li>';
            }

            if (!empty($this->result)) {
                $this->SetResult($this->result);
                $this->DisplayResult();
            }
        } else {
            $this->result['status'] = 100;
        }
    }

    private function AuthorizeAid() {
        if (isset($_GET['aid']) && iAUTH == $_GET['aid']) {
            return TRUE;
        }

        return FALSE;
    }

    private function SearchPages() {
        if (isset($_GET['pagestring'])) {
            $available_pages = Admins::getInstance()->getAdminPages();
            $search_string = $_GET['pagestring'];

            if (strlen($search_string) >= 2) {
                $pages = flatten_array($available_pages);
                $result_rows = 0;

                if (!empty($pages)) {
                    foreach ($pages as $page) {
                        if (stristr($page['admin_title'], $search_string) == TRUE || stristr($page['admin_link'], $search_string) == TRUE) {
                            $this->result['data'][] = $page;
                            $result_rows++;
                        }
                    }
                } else {
                    $this->result['status'] = 102;
                }

                if ($result_rows > 0) {
                    $this->result['count'] = $result_rows;
                } else {
                    $this->result['status'] = 104;
                }
            } else {
                $this->result['status'] = 103;
            }
        }
    }

    private function SetResult($result) {
        $this->result = $result;
    }

    private function DisplayResult() {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        if (!empty($this->result['data'])) {
            foreach ($this->result['data'] as $data) {
                if (stristr($data['admin_link'], '/infusions/')) {
                    $link = $settings['siteurl'].'infusions/'.$data['admin_link'];
                } else {
                    $link = $settings['siteurl'].'administration/'.$data['admin_link'];
                }

                $link = $link.fusion_get_aidlink();

                $title = $data['admin_title'];

                if ($data['admin_page'] !== 5) {
                    $title = isset($locale[$data['admin_rights']]) ? $locale[$data['admin_rights']] : $title;
                }

                if (checkrights($data['admin_rights'])) {
                    echo '<li><a href="'.$link.'">'.$title.'</a></li>';
                }
            }
        }
    }

    private function SetLocale($lc = NULL) {
        $locale = [];

        if (file_exists(ADMINLTE.'locale/'.LANGUAGE.'.php')) {
            include ADMINLTE.'locale/'.LANGUAGE.'.php';
        } else {
            include ADMINLTE.'locale/English.php';
        }

        return $locale['ALT_'.$lc];
    }
}
