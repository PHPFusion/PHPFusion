<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: images.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageaccess('IM');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/image_uploads.php');

add_breadcrumb(['link' => ADMIN.'images.php'.fusion_get_aidlink(), 'title' => $locale['IMGUP_460']]);

require_once INCLUDES.'infusions_include.php';

class ImagesAdministration {
    private static $locale = [];
    private static $settings = [];
    private $data = [];

    public function __construct() {
        self::$locale = fusion_get_locale();
        self::$settings = fusion_get_settings();

        $this->data['afolder'] = check_get('ifolder') && get('ifolder', FILTER_UNSAFE_RAW) ? $this->getImgFolders()[get('ifolder')]['path'] : IMAGES;

        switch (get('action')) {
            case 'delete':
                unlink($this->data['afolder'].get('view', FILTER_UNSAFE_RAW));
                addnotice('success', self::$locale['401']);
                redirect(clean_request("", ["section", "action", "view"], FALSE));
                break;
            case 'update':
                addnotice('success', self::$locale['IMGUP_465']);
                redirect(clean_request("", ["section", "action", "view"], FALSE));
                break;
            default:
                break;
        }
    }

    public static function getInstance() {
        return new static();
    }

    public function displayAdmin() {
        opentable(self::$locale['IMGUP_460']);

        if (check_get('section') && get('section') == 'back') {
            redirect(clean_request("", ["section", "action", "view"], FALSE));
        }

        $tabs['title'][] = self::$locale['IMGUP_460'];
        $tabs['id'][] = 'list';
        $tabs['icon'][] = 'fa fa-picture-o';

        $tabs['title'][] = self::$locale['IMGUP_420'];
        $tabs['id'][] = 'upload';
        $tabs['icon'][] = 'fa fa-plus';

        $allowed_sections = ['list', 'upload'];
        $sections = in_array(get('section'), $allowed_sections) ? get('section') : 'list';
        echo opentab($tabs, $sections, 'list', TRUE);

        switch ($sections) {
            case "upload":
                $this->addImageForm();
                break;
            default:
                $this->imageList();
                break;
        }

        echo closetab();
        closetable();
    }

    private function imageList() {
        $aidlink = fusion_get_aidlink();

        $ifolder = check_get('ifolder') && get('ifolder', FILTER_UNSAFE_RAW) ? get('ifolder') : 'images';

        echo "<div class='text-center m-b-15'>";
        echo "<div class='btn-group'>\n";
        foreach ($this->getImgFolders() as $key => $value) {
            if ($value['count'] != 0) {

                echo "<a class='btn btn-default ".($ifolder == $key ? "active" : "")."' href='".FUSION_SELF.$aidlink."&ifolder=$key'>".$value['locale']."</a>\n";
            }
        }
        echo "</div>\n</div>\n";

        $images = makefilelist($this->data['afolder'], ".|..", TRUE, "files", "php|js|ico|DS_Store|SVN");

        if ($images) {
            echo '<div class="row">';
            for ($i = 0; $i < count($images); $i++) {
                $delete_link = clean_request("section=list&action=delete&view=".$images[$i], ["section", "action", "view"], FALSE);
                $img_name = $images[$i];

                echo '<div class="col-xs-6 col-sm-2 text-center m-b-15">';
                echo '<div class="overflow-hide thumbnail m-b-5" style="height: 120px">';
                echo '<img class="img-responsive center-y" style="max-height:100%;" src="'.$this->data['afolder'].$img_name.'" alt="'.$img_name.'">';
                echo '</div>';
                echo '<div class="text-overflow-hide" title="'.$img_name.'">'.$img_name.'</div>';
                echo "<a class='text-danger' href='".$delete_link."' onclick=\"return confirm('".self::$locale['IMGUP_470']."');\">".self::$locale['delete']."</a>";
                echo '</div>';
            }
            echo '</div>';

            if (self::$settings['tinymce_enabled'] == 1) {
                echo "<div class='text-center well'><a href='".clean_request("action=update", ["action"], FALSE)."'>".self::$locale['IMGUP_464']."</a></div>\n";
            }
        } else {
            echo "<div class='well text-center'>".self::$locale['IMGUP_463']."</div>\n";
        }
    }

    private function addImageForm() {
        $ifolder = check_get('ifolder') && get('ifolder', FILTER_UNSAFE_RAW) ? get('ifolder') : 'images';

        if (check_post('uploadimage')) {
            if (fusion_safe()) {
                if (!empty($_FILES['myfile'])) {
                    $upload = form_sanitizer($_FILES['myfile'], '', 'myfile');

                    if (!empty($upload) && $upload['error'] == 0) {
                        if (fusion_safe()) {
                            addnotice('success', self::$locale['IMGUP_420']);
                            redirect(clean_request("", ["section"], FALSE));
                        }
                    }
                }
            }
        }

        echo openform('uploadform', 'post', FUSION_REQUEST, ['enctype' => TRUE]);
        echo form_fileinput("myfile", self::$locale['IMGUP_421'], "", [
            'upload_path' => $this->data['afolder'],
            'type'        => 'image',
            'valid_ext'   => '.jpg,.jpeg,.png,.gif,.bmp,.svg,.tiff,.webp',
            'max_width'   => $this->getImgFolders()[$ifolder]['fileinp']['max_width'],
            'max_height'  => $this->getImgFolders()[$ifolder]['fileinp']['max_height'],
            'max_byte'    => $this->getImgFolders()[$ifolder]['fileinp']['max_byte'],
            'required'    => TRUE
        ]);

        echo "<div class='small m-b-10'>".sprintf(self::$locale['IMGUP_425'], parsebytesize($this->getImgFolders()[$ifolder]['fileinp']['max_byte']))."</div>\n";

        echo form_button('uploadimage', self::$locale['IMGUP_420'], self::$locale['IMGUP_420'], ['class' => 'btn-primary']);
        echo closeform();
    }

    private function getImgFolders() {
        $settings_inf = [
            'blog' => defined('BLOG_EXISTS') ? get_settings('blog') : '',
            'news' => defined('NEWS_EXISTS') ? get_settings('news') : '',
        ];

        $maxed_out_settings = [
            'max_width'  => 24000,
            'max_height' => 24000,
            'max_byte'   => (max_server_upload() ?: 3 * 1000 * 1000 * 100)
        ];

        $folders = [
            "images" => [
                'locale'  => self::$locale['IMGUP_422'],
                'path'    => IMAGES,
                'count'   => TRUE,
                'fileinp' => $maxed_out_settings,
            ]
        ];

        if (defined('ARTICLES_EXISTS')) {
            $folders += [
                "imagesa" => [
                    'locale'  => self::$locale['IMGUP_423'],
                    'path'    => IMAGES_A,
                    'count'   => defined('ARTICLES_EXISTS'),
                    'fileinp' => $maxed_out_settings,
                ]
            ];
        }

        if (defined('NEWS_EXISTS')) {
            $folders += [
                "imagesn"  => [
                    'locale'  => self::$locale['IMGUP_424'],
                    'path'    => IMAGES_N,
                    'count'   => defined('NEWS_EXISTS'),
                    'fileinp' => [
                        'max_width'  => $settings_inf['news']['news_photo_max_w'],
                        'max_height' => $settings_inf['news']['news_photo_max_h'],
                        'max_byte'   => $settings_inf['news']['news_photo_max_b'],
                    ],
                ],
                "imagesnc" => [
                    'locale'  => self::$locale['IMGUP_427'],
                    'path'    => IMAGES_NC,
                    'count'   => defined('NEWS_EXISTS'),
                    'fileinp' => [
                        'max_width'  => $settings_inf['news']['news_photo_max_w'],
                        'max_height' => $settings_inf['news']['news_photo_max_h'],
                        'max_byte'   => $settings_inf['news']['news_photo_max_b'],
                    ],
                ]
            ];
        }

        if (defined('BLOG_EXISTS')) {
            $folders += [
                "imagesb"  => [
                    'locale'  => self::$locale['IMGUP_428'],
                    'path'    => IMAGES_B,
                    'count'   => defined('BLOG_EXISTS'),
                    'fileinp' => [
                        'max_width'  => !empty($settings_inf['blog']) ? $settings_inf['blog']['blog_photo_max_w'] : 0,
                        'max_height' => !empty($settings_inf['blog']) ? $settings_inf['blog']['blog_photo_max_h'] : 0,
                        'max_byte'   => !empty($settings_inf['blog']) ? $settings_inf['blog']['blog_photo_max_b'] : 0,
                    ],
                ],
                "imagesbc" => [
                    'locale'  => self::$locale['IMGUP_429'],
                    'path'    => IMAGES_BC,
                    'count'   => defined('BLOG_EXISTS'),
                    'fileinp' => [
                        'max_width'  => !empty($settings_inf['blog']) ? $settings_inf['blog']['blog_photo_max_w'] : 0,
                        'max_height' => !empty($settings_inf['blog']) ? $settings_inf['blog']['blog_photo_max_h'] : 0,
                        'max_byte'   => !empty($settings_inf['blog']) ? $settings_inf['blog']['blog_photo_max_b'] : 0,
                    ],
                ],
            ];
        }

        return $folders;
    }
}

ImagesAdministration::getInstance()->displayAdmin();

require_once THEMES.'templates/footer.php';
