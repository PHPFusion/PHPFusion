<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Material/classes/Search.inc
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
namespace Material;

use PHPFusion\Admins;

class Search {
    private $result = [
        'data'   => [],
        'count'  => 0,
        'status' => ''
    ];

    public function __construct() {
        $locale = fusion_get_locale('', MDT_LOCALE);

        if ($this->AuthorizeAid()) {
            if (\defender::safe()) {
                $result = [];
                $search_string = (string)filter_input(INPUT_GET, 'pagestring');

                if (isset($search_string)) {
                    $available_pages = Admins::getInstance()->getAdminPages();

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

                $result['status'] = !empty($this->result['status']) ? $locale['material_'.$this->result['status']] : '';

                if (!empty($this->result)) {
                    $this->SetResult($this->result);

                    if (!empty($this->result['data'])) {
                        foreach ($this->result['data'] as $data) {
                            if (stristr($data['admin_link'], '/infusions/')) {
                                $link = fusion_get_settings('siteurl').'infusions/'.$data['admin_link'];
                            } else {
                                $link = fusion_get_settings('siteurl').'administration/'.$data['admin_link'];
                            }

                            $link = $link.fusion_get_aidlink();

                            $title = $data['admin_title'];

                            if ($data['admin_page'] !== 5) {
                                $title = isset($locale[$data['admin_rights']]) ? $locale[$data['admin_rights']] : $title;
                            }

                            $icon = strtr(get_image('ac_'.$data['admin_rights']), [
                                INFUSIONS => fusion_get_settings('siteurl').'infusions/',
                                ADMIN     => fusion_get_settings('siteurl').'administration/'
                            ]);

                            if (checkrights($data['admin_rights'])) {
                                $result[] = [
                                    'title' => $title,
                                    'link'  => $link,
                                    'icon'  => $icon
                                ];
                            }
                        }
                    }
                }

                header('Content-Type: application/json');

                echo json_encode($result);
            } else {
                $this->result['status'] = 101;
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

    private function SetResult($result) {
        $this->result = $result;
    }
}
