<?php
namespace PHPFusion;

class Images {
    private static $locale = array();
    private static $settings = array();
    private static $instances = array();
    private $data = [
        'image_list' => 0,
        'image_count' => 0,
        'afolder' => "",
        'folders' => array(),
    ];

    public function __construct() {
	require_once INCLUDES."infusions_include.php";
	$settings_inf = [
		'blog' => db_exists(DB_BLOG) ? get_settings("blog") : '',
		'news' => db_exists(DB_NEWS) ? get_settings("news") : '',
		];
	self::$locale = fusion_get_locale("", LOCALE.LOCALESET."admin/image_uploads.php");

	self::$settings = fusion_get_settings();


	$folders = [
	    "images" => [
	    	'locale'  => self::$locale['422'],
	    	'link'    => IMAGES,
	    	'count'   => TRUE,
	    	'fileinp' => [
 		   		'max_width'  => 1800,
	    		'max_height' => 1600,
	    		'max_byte'   => 2000000,
	    		],
	    	],
	    "imagesa" => [
	    	'locale'  => self::$locale['423'],
	    	'link'    => IMAGES_A,
	    	'count'   => db_exists(DB_ARTICLES),
	    	'fileinp' => [
	    		'max_width'  => 1800,
	    		'max_height' => 1600,
	    		'max_byte'   => 500000,
	    		],
	    	],
	    "imagesn" => [
	    	'locale'  => self::$locale['424'],
	    	'link'    => IMAGES_N,
	    	'count'   => db_exists(DB_NEWS),
	    	'fileinp' => [
	    		'max_width'  => !empty($settings_inf['news']) ? $settings_inf['news']['news_photo_max_w'] : '',
	    		'max_height' => !empty($settings_inf['news']) ? $settings_inf['news']['news_photo_max_h'] : '',
	    		'max_byte'   => !empty($settings_inf['news']) ? $settings_inf['news']['news_photo_max_b']: '',
	    		],
	    	],
	    "imagesnc" => [
	    	'locale'  => self::$locale['427'],
	    	'link'    => IMAGES_NC,
	    	'count'   => db_exists(DB_NEWS),
	    	'fileinp' => [
	    		'max_width'  => 40,
	    		'max_height' => 40,
	    		'max_byte'   => 1800,
	    		],
	    	],
	    "imagesb" => [
	    	'locale'  => self::$locale['428'],
	    	'link'    => IMAGES_B,
	    	'count'   => db_exists(DB_BLOG),
	    	'fileinp' => [
	    		'max_width'  => !empty($settings_inf['blog']) ? $settings_inf['blog']['blog_photo_max_w'] : '',
	    		'max_height' => !empty($settings_inf['blog']) ? $settings_inf['blog']['blog_photo_max_h'] : '',
	    		'max_byte'   => !empty($settings_inf['blog']) ? $settings_inf['blog']['blog_photo_max_b'] : '',
	    		],
	    	],
	    "imagesbc" => [
	    	'locale'  => self::$locale['429'],
	    	'link'    => IMAGES_BC,
	    	'count'   => db_exists(DB_BLOG),
	    	'fileinp' => [
	    		'max_width'  => 40,
 		   		'max_height' => 40,
	    		'max_byte'   => 1800,
	    		],
	    	],
	];


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
            default:
                break;
        }

		$this->data['image_list'] = makefilelist($this->data['afolder'], ".|..", TRUE, "files", "php|js|ico|DS_Store|SVN");
		if ($this->data['image_list']) {
		    $this->data['image_count'] = count($this->data['image_list']);
		}

	\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'images.php'.fusion_get_aidlink(), 'title' => self::$locale['460']]);
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
	    redirect(clean_request("", array("section", "action", "view"), FALSE));
    }

    public function display_admin() {

    opentable(self::$locale['460']);
		$allowed_section = array("list", "upload", 'edit');
        if (isset($_GET['section']) && $_GET['section'] == "back") {
            redirect(clean_request("", array("section", "action", "view"), FALSE));
        }
		$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'list';
        $edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $_GET['ifolder'] : '';

        if ($edit) {
            $tab_title['title'][] = self::$locale['back'];
            $tab_title['id'][] = "back";
            $tab_title['icon'][] = "fa fa-fw fa-arrow-left";
        }
        if (!$edit) {
        $tab_title['title'][] = self::$locale['460'];
        $tab_title['id'][]    = 'list';
        $tab_title['icon'][]  = '';
        }
        if ($edit) {
        $tab_title['title'][] = self::$locale['440'];
        $tab_title['id'][]    = 'edit';
        $tab_title['icon'][]  = '';
        }
        $tab_title['title'][] = self::$locale['420'];
        $tab_title['id'][]    = 'upload';
        $tab_title['icon'][]  = 'fa fa-plus-square m-r-10';

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
		$aidlink = fusion_get_aidlink();
		if (isset($_GET['view']) && in_array($_GET['view'], $this->data['image_list'])) {
			echo "<div class='text-center'>\n";
			$delete_link = clean_request("section=list&action=delete&view=".stripinput($_GET['view']), array("section", "action", "view"), FALSE);
			echo "<a class='btn btn-danger' href='".$delete_link."' onclick=\"return confirm('".self::$locale['470']."');\">".self::$locale['442']."</a>";
			echo "</div>\n";
			echo "<div class='text-center'>\n";
			$image_ext = strrchr($this->data['afolder'].stripinput($_GET['view']), ".");
			if (in_array($image_ext, array(".gif", ".GIF", ".ico", ".jpg", ".JPG", ".jpeg", ".JPEG", ".png", ".PNG", ".svg", ".SVG"))) {
				echo "<img class='img-responsive img-thumbnail' src='".$this->data['afolder'].stripinput($_GET['view'])."' title='".stripinput($_GET['view'])."' /><br /><br />\n";
			} else {
				echo "<div class='alert alert-info text-center'>".self::$locale['441']."</div>\n";
	        }
        echo "</div>\n";
		}
    }

    public function image_list() {
        $aidlink = fusion_get_aidlink();

		echo "<div class='alert alert-danger text-center well'>";
        echo "<div class='btn-group'>\n";
			foreach ($this->data['folders'] as $key => $value) {
		        if ($value['count'] != 0) {
			        echo "<a class='btn btn-default ".($_GET['ifolder'] == $key ? "active" : "")."' href='".FUSION_SELF.$aidlink."&amp;ifolder=$key'>".$value['locale']."</a>\n";
		        }
			}
        echo "</div>\n</div>\n";
        if ($this->data['image_list']) {
        echo "<table class='table table-responsive table-hover'>\n<tr>\n";
            for ($i = 0; $i < $this->data['image_count']; $i++) {
			$edit_link = clean_request("section=edit&action=edit&view=".$this->data['image_list'][$i], array("section", "action", "view"), FALSE);
			$delete_link = clean_request("section=list&action=delete&view=".$this->data['image_list'][$i], array("section", "action", "view"), FALSE);
                         echo "<tr>\n<td >".$this->data['image_list'][$i]."</td>\n";
                echo "<td align='right' width='1%' style='white-space:nowrap'>\n";
                echo "<a href='".$edit_link."'>".self::$locale['461']."</a> -\n";
                echo "<a href='".$delete_link."' onclick=\"return confirm('".self::$locale['470']."');\">".self::$locale['462']."</a></td>\n";
                echo "</tr>\n";
            }
            if (self::$settings['tinymce_enabled'] == 1) {
                echo "<tr>\n<td align='center' colspan='2' class='tbl1'><a href='".FUSION_SELF.$aidlink."&amp;ifolder=".$_GET['ifolder']."&amp;action=update'>".self::$locale['464']."</a></td>\n</tr>\n";
            }
        echo "</table>\n";
        } else {
		echo "<div class='alert alert-info text-center'>".self::$locale['463']."</div>\n";
        }
    }

    public function add_image_form() {
        $aidlink = fusion_get_aidlink();
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
	                    redirect(clean_request("", array("section"), FALSE));
	                }
	            }
        	}
    	}

		}
	    echo openform('uploadform', 'post', FUSION_REQUEST, array('enctype' => TRUE));
		echo form_fileinput("myfile", self::$locale['421'], "", array(
	        'upload_path' => $this->data['afolder'],
			'type'        => 'image',
	        'valid_ext'   => '.jpg,.png,.PNG,.JPG,.JPEG,.gif,.GIF,.bmp,.BMP,.svg,.SVG,.tiff,.TIFF',
	        'max_width'   => $this->data['folders'][$_GET['ifolder']]['fileinp']['max_width'],
	        'max_height'  => $this->data['folders'][$_GET['ifolder']]['fileinp']['max_height'],
	        'max_byte'    => $this->data['folders'][$_GET['ifolder']]['fileinp']['max_byte'],
	        'required'    => TRUE
	    ));
	    echo "<div class='small m-b-10'>".sprintf(self::$locale['425'], parsebytesize($this->data['folders'][$_GET['ifolder']]['fileinp']['max_byte']))."</div>\n";

        echo form_button('uploadimage', self::$locale['420'], self::$locale['420'], array('class' => 'btn-primary'));
	    echo closeform();
    }

}
