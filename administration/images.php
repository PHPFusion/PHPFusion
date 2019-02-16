<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: images.php
| Author: PHP-Fusion Development Team
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
pageAccess('IM');

require_once THEMES.'templates/admin_header.php';

use \PHPFusion\BreadCrumbs;

class ImagesAdministration {
    private static $locale = [];
    private static $settings = [];
    private static $instances = [];
    private $data = [
        'image_list'  => 0,
        'image_count' => 0,
        'afolder'     => "",
        'folders'     => [],
    ];

    /**
     * @param $val
     *
     * @return int
     */
    private static function get_server_limits($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $kb = 1024;
        $mb = 1024 * $kb;
        $gb = 1024 * $mb;
        switch ($last) {
            case 'g':
                $val = (int)$val * $gb;
                break;
            case 'm':
                $val = (int)$val * $mb;
                break;
            case 'k':
                $val = (int)$val * $kb;
                break;
        }

        return (int)$val;
    }

    public static function max_server_upload() {
        //select maximum upload size
        $max_upload = self::get_server_limits(ini_get('upload_max_filesize'));
        //select post limit
        $max_post = self::get_server_limits(ini_get('post_max_size'));
        //select memory limit
        $memory_limit = self::get_server_limits(ini_get('memory_limit'));

        // return the smallest of them, this defines the real limit
        return min($max_upload, $max_post, $memory_limit);
    }

    public function __construct() {

        require_once INCLUDES."infusions_include.php";

        $settings_inf = [
            'blog' => defined('BLOG_EXIST') ? get_settings('blog') : '',
            'news' => defined('NEWS_EXIST') ? get_settings('news') : '',
        ];

        self::$locale = fusion_get_locale("", LOCALE.LOCALESET."admin/image_uploads.php");

        self::$settings = fusion_get_settings();

        $maxed_out_settings = [
            'max_width'  => 24000,
            'max_height' => 24000,
            'max_byte'   => (self::max_server_upload() ?: 3 * 1000 * 1000 * 100)
        ];

        $folders = [
            "images"   => [
                'locale'  => self::$locale['422'],
                'link'    => IMAGES,
                'count'   => TRUE,
                'fileinp' => $maxed_out_settings,
            ]
        ];

        if (defined('ARTICLES_EXIST')) {
            $folders += [
                "imagesa"  => [
                    'locale'  => self::$locale['423'],
                    'link'    => IMAGES_A,
                    'count'   => defined('ARTICLES_EXIST'),
                    'fileinp' => $maxed_out_settings,
                ]
            ];
        }

        if (defined('NEWS_EXIST')) {
            $folders += [
                "imagesn"  => [
                    'locale'  => self::$locale['424'],
                    'link'    => IMAGES_N,
                    'count'   => defined('NEWS_EXIST'),
                    'fileinp' => [
                        'max_width'  => !empty($settings_inf['news']) ? $settings_inf['news']['news_photo_max_w'] : 0,
                        'max_height' => !empty($settings_inf['news']) ? $settings_inf['news']['news_photo_max_h'] : 0,
                        'max_byte'   => !empty($settings_inf['news']) ? $settings_inf['news']['news_photo_max_b'] : 0,
                    ],
                ],
                "imagesnc" => [
                    'locale'  => self::$locale['427'],
                    'link'    => IMAGES_NC,
                    'count'   => defined('NEWS_EXIST'),
                    'fileinp' => [
                        'max_width'  => !empty($settings_inf['news']) ? $settings_inf['news']['news_photo_max_w'] : 0,
                        'max_height' => !empty($settings_inf['news']) ? $settings_inf['news']['news_photo_max_h'] : 0,
                        'max_byte'   => !empty($settings_inf['news']) ? $settings_inf['news']['news_photo_max_b'] : 0,
                    ],
                ]
            ];
        }

        if (defined('BLOG_EXIST')) {
            $folders += [
                "imagesb"  => [
                    'locale'  => self::$locale['428'],
                    'link'    => IMAGES_B,
                    'count'   => defined('BLOG_EXIST'),
                    'fileinp' => [
                        'max_width'  => !empty($settings_inf['blog']) ? $settings_inf['blog']['blog_photo_max_w'] : 0,
                        'max_height' => !empty($settings_inf['blog']) ? $settings_inf['blog']['blog_photo_max_h'] : 0,
                        'max_byte'   => !empty($settings_inf['blog']) ? $settings_inf['blog']['blog_photo_max_b'] : 0,
                    ],
                ],
                "imagesbc" => [
                    'locale'  => self::$locale['429'],
                    'link'    => IMAGES_BC,
                    'count'   => defined('BLOG_EXIST'),
                    'fileinp' => [
                        'max_width'  => !empty($settings_inf['blog']) ? $settings_inf['blog']['blog_photo_max_w'] : 0,
                        'max_height' => !empty($settings_inf['blog']) ? $settings_inf['blog']['blog_photo_max_h'] : 0,
                        'max_byte'   => !empty($settings_inf['blog']) ? $settings_inf['blog']['blog_photo_max_b'] : 0,
                    ],
                ],
            ];
        }

        $this->data['folders'] = $folders;

        if (isset($_GET['ifolder']) && ctype_alnum($_GET['ifolder']) == 1 && isset($folders[$_GET['ifolder']]['link'])) {
            $_GET['ifolder'] = stripinput($_GET['ifolder']);
            $this->data['afolder'] = $folders[$_GET['ifolder']]['link'];
        } else {
            $_GET['ifolder'] = "images";
            $this->data['afolder'] = IMAGES;
        }

        $_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
        switch ($_GET['action']) {
            case 'delete':
                self::delete_images($_GET['view']);
                break;
            case 'update':
                include INCLUDES."buildlist.php";
                break;
            default:
                break;
        }

        $this->data['image_list'] = makefilelist($this->data['afolder'], ".|..", TRUE, "files", "php|js|ico|DS_Store|SVN");
        if ($this->data['image_list']) {
            $this->data['image_count'] = count($this->data['image_list']);
        }

        BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'images.php'.fusion_get_aidlink(), 'title' => self::$locale['460']]);
    }

    public static function getInstance($key = 'default') {
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new static();
        }

        return self::$instances[$key];
    }

    private function delete_images($id) {
        unlink($this->data['afolder'].stripinput($id));
        if (self::$settings['tinymce_enabled'] == 1) {
            include INCLUDES."buildlist.php";
        }
        addNotice('warning', self::$locale['401']);
        redirect(clean_request("", ["section", "action", "view"], FALSE));
    }

    public function display_admin() {
        opentable(self::$locale['460']);
        $allowed_section = ["list", "upload", 'edit'];
        if (isset($_GET['section']) && $_GET['section'] == "back") {
            redirect(clean_request("", ["section", "action", "view"], FALSE));
        }
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'list';
        $edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $_GET['ifolder'] : '';

        if ($edit) {
            $tab_title['title'][] = self::$locale['back'];
            $tab_title['id'][] = "back";
            $tab_title['icon'][] = "fa fa-fw fa-arrow-left";
        }

        $tab_title['title'][] = $edit ? self::$locale['440'] : self::$locale['460'];
        $tab_title['id'][] = $edit ? 'edit' : 'list';
        $tab_title['icon'][] = $edit ? 'fa fa-eye' : 'fa fa-picture-o';

        $tab_title['title'][] = self::$locale['420'];
        $tab_title['id'][] = 'upload';
        $tab_title['icon'][] = 'fa fa-plus';

        echo opentab($tab_title, $_GET['section'], 'list', TRUE);

        switch ($_GET['section']) {
            case "upload":
                $this->add_image_form();
                break;
            case "edit":
                $this->edit_image();
                break;
            default:
                $this->image_list();
                break;
        }

        echo closetab();
        closetable();
    }

    public function edit_image() {
        if (isset($_GET['view']) && in_array($_GET['view'], $this->data['image_list'])) {
            echo "<div class='text-center m-t-20'>\n";
            $image_ext = strrchr($this->data['afolder'].stripinput($_GET['view']), ".");
            if (in_array($image_ext, [".gif", ".GIF", ".ico", ".jpg", ".JPG", ".jpeg", ".JPEG", ".png", ".PNG", ".svg", ".SVG"])) {
                echo "<img class='img-responsive img-thumbnail' src='".$this->data['afolder'].stripinput($_GET['view'])."' title='".stripinput($_GET['view'])."' alt='".stripinput($_GET['view'])."'/><br /><br />\n";
            } else {
                echo "<div class='alert alert-info text-center'>".self::$locale['441']."</div>\n";
            }
            echo "</div>\n";
            echo "<div class='text-center'>\n";
            $delete_link = clean_request("section=list&action=delete&view=".stripinput($_GET['view']), ["section", "action", "view"], FALSE);
            echo "<a class='btn btn-danger' href='".$delete_link."' onclick=\"return confirm('".self::$locale['470']."');\">".self::$locale['442']."</a>";
            echo "</div>\n";
        }
    }

    public function image_list() {
        $aidlink = fusion_get_aidlink();

        echo "<div class='well text-center m-t-15'>";
        echo "<div class='btn-group'>\n";
        foreach ($this->data['folders'] as $key => $value) {
            if ($value['count'] != 0) {
                echo "<a class='btn btn-default ".($_GET['ifolder'] == $key ? "active" : "")."' href='".FUSION_SELF.$aidlink."&amp;ifolder=$key'>".$value['locale']."</a>\n";
            }
        }
        echo "</div>\n</div>\n";
        if ($this->data['image_list']) {
            echo "<div class='table-responsive'><table class='table table-hover'>\n";
            for ($i = 0; $i < $this->data['image_count']; $i++) {
                $edit_link = clean_request("section=edit&action=edit&view=".$this->data['image_list'][$i], ["section", "action", "view"], FALSE);
                $delete_link = clean_request("section=list&action=delete&view=".$this->data['image_list'][$i], ["section", "action", "view"], FALSE);
                echo "<tr>\n<td>".$this->data['image_list'][$i]."</td>\n";
                echo "<td class='text-right'>\n";
                echo "<a href='".$edit_link."'>".self::$locale['461']."</a> -\n";
                echo "<a href='".$delete_link."' onclick=\"return confirm('".self::$locale['470']."');\">".self::$locale['delete']."</a></td>\n";
                echo "</tr>\n";
            }
            echo "</table>\n</div>";
            if (self::$settings['tinymce_enabled'] == 1) {
                $upd_link = clean_request("action=update", ["action"], FALSE);
                echo "<div class='text-center well'><a href='".$upd_link."'>".self::$locale['464']."</a></div>\n";
            }
        } else {
            echo "<div class='alert alert-info text-center'>".self::$locale['463']."</div>\n";
        }
    }

    public function add_image_form() {

        if (isset($_POST['uploadimage'])) {
            $data = [
                'myfile' => ''
            ];

            if (\defender::safe()) {
                if (!empty($_FILES['myfile'])) {
                    $upload = form_sanitizer($_FILES['myfile'], '', 'myfile');

                    if (!empty($upload) && $upload['error'] == 0) {
                        $data['myfile'] = $upload['image_name'];
                        if (self::$settings['tinymce_enabled'] == 1) {
                            include INCLUDES."buildlist.php";
                        }
                        if (\defender::safe()) {
                            addNotice('success', self::$locale['420']);
                            redirect(clean_request("", ["section"], FALSE));
                        }
                    }
                }
            }
        }

        echo openform('uploadform', 'post', FUSION_REQUEST, ['enctype' => TRUE, 'class' => 'm-t-15']);
        echo form_fileinput("myfile", self::$locale['421'], "", [
            'upload_path' => $this->data['afolder'],
            'type'        => 'image',
            'valid_ext'   => '.jpg,.png,.PNG,.JPG,.JPEG,.gif,.GIF,.bmp,.BMP,.svg,.SVG,.tiff,.TIFF',
            'max_width'   => $this->data['folders'][$_GET['ifolder']]['fileinp']['max_width'],
            'max_height'  => $this->data['folders'][$_GET['ifolder']]['fileinp']['max_height'],
            'max_byte'    => $this->data['folders'][$_GET['ifolder']]['fileinp']['max_byte'],
            'required'    => TRUE
        ]);
        echo "<div class='small m-b-10'>".sprintf(self::$locale['425'], parsebytesize($this->data['folders'][$_GET['ifolder']]['fileinp']['max_byte']))."</div>\n";

        echo form_button('uploadimage', self::$locale['420'], self::$locale['420'], ['class' => 'btn-primary']);
        echo closeform();
    }
}

$image = ImagesAdministration::getInstance(TRUE);
$image->display_admin();

require_once THEMES.'templates/footer.php';
