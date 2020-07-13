<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: smileys.php
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
pageAccess('SM');
require_once THEMES.'templates/admin_header.php';

class SmileysAdministration {
    private static $locale = [];
    private static $instance = NULL;
    private $smiley_files = '';
    private $actions = '';
    private $allowed_section = [ 'smiley_form', 'smiley_list' ];
    private $formaction = '';
    private static $cache_smiley = NULL;
    private $data = [
        'smiley_id'    => 0,
        'smiley_code'  => '',
        'smiley_image' => '',
        'smiley_text'  => ''
    ];

    public function __construct() {

        $aidlink = fusion_get_aidlink();

        self::$locale = fusion_get_locale( "", LOCALE.LOCALESET."admin/smileys.php" );

        $this->smiley_files = makefilelist( IMAGES.'smiley/', '.|..|.DS_Store|index.php', TRUE, 'files' );

        $this->action = get( 'action' );

        $this->action = isset( $this->action ) ? $this->action : '';

        switch ( $this->action ) {
            case 'edit':
                $smileyid = get( 'smiley_id' );
                if ( $smileyid ) {
                    $this->data = self::loadSmileys( $smileyid );
                    $this->formaction = FUSION_SELF.$aidlink."&amp;section=smiley_form&amp;action=edit&amp;smiley_id=".$smileyid;
                } else {
                    redirect( FUSION_REQUEST );
                }
                break;
            case 'delete':
                $smileyid = get( 'smiley_id' );
                $this->deleteSmileys( $smileyid );
                break;
            default:
                $smileytext = get( 'smiley_text' );
                if ( $smileytext ) {
                    $this->data['smiley_text'] = str_replace( [ '.gif', '.png', '.jpg', '.svg' ], '', $smileytext );
                    $this->data['smiley_image'] = $smileytext;
                }
                $this->formaction = FUSION_SELF.$aidlink."&amp;section=smiley_form";
                break;
        }

        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb( [ 'link' => ADMIN.'smileys.php'.fusion_get_aidlink(), 'title' => self::$locale['SMLY_403'] ] );
        self::set_smileydb();
    }

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }
       return self::$instance;
    }

    private function allSmileys() {
        if ( self::$cache_smiley === NULL ) {
            self::$cache_smiley = [];
            $result = dbquery( "SELECT smiley_id, smiley_code, smiley_image, smiley_text FROM ".DB_SMILEYS );
            if ( dbrows( $result ) > 0 ) {
                while ( $data = dbarray( $result ) ) {
                    self::$cache_smiley[] = $data;
                }
            }
        }

        return self::$cache_smiley;

    }

    private static function loadSmileys( $id ) {
        $result = dbquery( "SELECT smiley_id, smiley_code, smiley_image, smiley_text
            FROM ".DB_SMILEYS."
            WHERE smiley_id = :smileyid", [ ':smileyid' => (int)$id ]
        );

        if ( dbrows( $result ) > 0 ) {
            return dbarray( $result );
        }

        return [];
    }

    private static function verifySmileys( $id ) {
        if ( isnum( $id ) ) {
            return dbcount( "(smiley_id)", DB_SMILEYS, "smiley_id = :smileyid", [ ':smileyid' => (int)$id ] );
        }

        return FALSE;
    }

    private function deleteSmileys( $id ) {
        if ( $this->verifySmileys( $id ) ) {
            $data = $this->loadSmileys( $id );
            $messages = self::$locale['SMLY_413'];
            dbquery( "DELETE FROM ".DB_SMILEYS." WHERE smiley_id = :smileyid", [ ':smileyid' => (int)$id ] );
            if ( get( 'inactive' ) ) {
                if ( !empty( $data['smiley_image'] ) && file_exists( IMAGES.'smiley/'.$data['smiley_image'] ) ) {
                    unlink( IMAGES.'smiley/'.$data['smiley_image'] );
                }
                $messages = self::$locale['SMLY_412'];
            }
            add_notice( 'warning', $messages );
            redirect( clean_request( '', [ 'section=smiley_list', 'aid' ], TRUE ) );
        }
    }

    private function set_smileydb() {
        if ( post( 'smiley_save' ) ) {
            $smiley_code = post( 'smiley_code' );

            if (QUOTES_GPC) {
                $smiley_code = stripslashes( $smiley_code );
                $smiley_code = str_replace( [ "\"", "'", "\\", '\"', "\'", "<", ">" ], "", $smiley_code );
            }

            if ( post( 'smiley_image' ) ) {
                $this->data['smiley_image'] = form_sanitizer( $_POST['smiley_image'], '', 'smiley_image' );
            }

            if ( !empty( $_FILES['smiley_file'] ) && is_uploaded_file( $_FILES['smiley_file']['tmp_name'] ) ) {

                $upload = form_sanitizer( $_FILES['smiley_file'], '', 'smiley_file' );
                if ( $upload['error'] == 0 ) {
                    $this->data['smiley_image'] = $upload['image_name'];
                }
            }

            $this->data['smiley_id'] = post( 'smiley_id' ) ? sanitizer( 'smiley_id', '0', 'smiley_id' ) : 0;
            $this->data['smiley_code'] = isset( $smiley_code ) ? form_sanitizer( $smiley_code, '', 'smiley_code' ) : '';
            $this->data['smiley_text'] = post( 'smiley_text' ) ? form_sanitizer( $_POST['smiley_text'], '', 'smiley_text' ) : '';

            $error = "";
            $error .= empty( $this->data['smiley_image'] ) ? self::$locale['SMLY_418'] : "";
            $error .= dbcount( "(smiley_id)", DB_SMILEYS, "smiley_id != :smileyid AND smiley_code = :smileycode", [ ':smileyid' => (int)$this->data['smiley_id'], ':smileycode' => $this->data['smiley_code'] ] ) ? self::$locale['SMLY_415'] : "";
            $error .= dbcount( "(smiley_id)", DB_SMILEYS, "smiley_id != :smileyid AND smiley_text = :smileytext", [ ':smileyid' => (int)$this->data['smiley_id'], ':smileytext' => $this->data['smiley_text'] ] ) ? self::$locale['SMLY_414'] : "";

            if ( fusion_safe() ) {
                if ( $error == "" ) {
                    dbquery_insert( DB_SMILEYS, $this->data, empty( $this->data['smiley_id'] ) ? 'save' : 'update');
                    add_notice( 'success', empty( $this->data['smiley_id'] ) ? self::$locale['SMLY_410'] : self::$locale['SMLY_411']);
                    redirect( clean_request( '', [ 'section=smiley_list', 'aid' ], TRUE ) );

                } else {
                    add_notice( 'danger', $error );

                }
            }
        }
    }

    public function display_admin() {

        opentable( self::$locale['SMLY_403'] );

        $section = get( 'section' );
        $section = isset( $section ) && in_array( $section, $this->allowed_section ) ? $section : 'smiley_list';
        $edit = ($this->action == 'edit') ? $this->verifySmileys( get( 'smiley_id' ) ) : 0;

        $tab_title['title'][] = self::$locale['SMLY_400'];
        $tab_title['id'][] = 'smiley_list';
        $tab_title['icon'][] = 'fa fa-smile-o m-r-10';
        $tab_title['title'][] = $edit ? self::$locale['SMLY_402'] : self::$locale['SMLY_401'];
        $tab_title['id'][] = 'smiley_form';
        $tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';

        echo opentab( $tab_title, $section, 'smiley_list', TRUE, '', 'section', [ 'rowstart', 'action', 'smiley_id' ] );

        switch ( $section ) {
            case "smiley_form":
                $this->smileyform();
                break;
            default:
                $this->smiley_listing();
                break;
        }

        echo closetab();
        closetable();
    }

    public function smiley_listing() {
        $aidlink = fusion_get_aidlink();

        $all_smileys = $this->allSmileys();
        $smileys_list = $this->smileyList();

        echo "<div class='m-t-10'>";
        echo "<h2>".self::$locale['SMLY_404']."</h2>";

        if ( !empty( $all_smileys ) ) {
            echo "<div class='table-responsive'><table class='table table-hover table-striped'>\n";
            echo "<tr>\n";
            echo "<th class='col-xs-2'><strong>".self::$locale['SMLY_430']."</strong></th>\n";
            echo "<th class='col-xs-2'><strong>".self::$locale['SMLY_431']."</strong></th>\n";
            echo "<th class='col-xs-2'><strong>".self::$locale['SMLY_432']."</strong></th>\n";
            echo "<th class='col-xs-4'><strong>".self::$locale['SMLY_433']."</strong></th>\n";
            echo "</tr>\n";

            foreach ( $all_smileys as $info ) {
                echo "<tr>\n";
                echo "<td class='col-xs-2'>".$info['smiley_code']."</td>\n";
                echo "<td class='col-xs-2'><img style='width:20px;height:20px;' src='".IMAGES."smiley/".$info['smiley_image']."' alt='".$info['smiley_text']."' title='".$info['smiley_text']."' /></td>\n";
                echo "<td class='col-xs-2'>".$info['smiley_text']."</td>\n";
                echo "<td class='col-xs-4'><a class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=smiley_form&amp;action=edit&amp;smiley_id=".$info['smiley_id']."'>".self::$locale['edit']."<i class='fa fa-edit m-l-10'></i></a> \n";
                echo "<a id='confirm' class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=smiley_form&amp;action=delete&amp;smiley_id=".$info['smiley_id']."' onclick=\"return confirm('".self::$locale['SMLY_417']."');\">".self::$locale['SMLY_435']."<i class='fa fa-close m-l-10'></i></a> \n";
                echo "<a id='confirm' class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=smiley_form&amp;action=delete&amp;inactive=1&amp;smiley_id=".$info['smiley_id']."' onclick=\"return confirm('".self::$locale['SMLY_416']."');\">".self::$locale['delete']."<i class='fa fa-trash m-l-10'></i></a></td>\n</tr>\n";
            }
            echo "</table>\n</div>";
        } else {
            echo "<div class='well text-center'>".self::$locale['SMLY_440']."</div>\n";
        }
        echo "</div>";

        echo "<div class='m-t-10'>";
        echo "<h2>".self::$locale['SMLY_405']."</h2>";
        if ( !empty( $smileys_list ) ) {
            echo "<div class='table-responsive'><table class='table table-hover table-striped'>\n";
            foreach ( $smileys_list as $list ) {
                echo "<tr>\n";
                echo "<td class='col-xs-2'><img style='width:20px;height:20px;border:none;' src='".IMAGES."smiley/".$list."' alt='' title='' /></td>\n";
                echo "<td class='col-xs-2'>".ucwords( str_replace( [ '.gif', '.png', '.jpg', '.svg' ], '', $list ) )."</td>\n";
                echo "<td class='col-xs-2'><a id='confirm' class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=smiley_form&amp;smiley_text=".$list."'>".self::$locale['add']."<i class='fa fa-plus m-l-10'></i></a></td>\n";
                echo "</tr>\n";
            }
            echo "</table>\n</div>";
        } else {
            echo "<div class='well text-center'>".self::$locale['SMLY_441']."</div>\n";
        }
        echo "</div>";
    }

    private function smileyList() {
        $smiley_list = [];
        $smiley = [];

        foreach ( $this->allSmileys() as $filename ) {

            $smiley[] = $filename['smiley_image'];

        }

        foreach ( $this->smiley_files as $smiley_check ) {

            if ( !in_array( $smiley_check, $smiley ) ) {
                $smiley_list[] = $smiley_check;
            }

        }

        return (array)$smiley_list;
    }

    public function smileyform() {
        fusion_confirm_exit();
        $image_opts = [];
        $image_opts_ = [];

        foreach ( $this->allSmileys() as $filename ) {
            $name = explode( ".", $filename['smiley_image'] );
            $image_opts_[$filename['smiley_image']] = ucwords( $name[0] );
        }

        foreach ( $this->smiley_files as $filename ) {
            $name = explode( ".", $filename );
            $image_opts[$filename] = ucwords( $name[0] );
        }

        $smileys_opts = array_diff( $image_opts, $image_opts_ );

        echo "<div class='m-t-10'>";
        echo openform( 'smiley_form', 'post', $this->formaction, [ 'enctype' => TRUE ] );
        echo form_hidden( 'smiley_id', '', $this->data['smiley_id'] );
        if ( $this->data['smiley_image'] ) {
            echo form_select( 'smiley_image', self::$locale['SMLY_421'], $this->data['smiley_image'], [
                'options'    => $smileys_opts,
                'required'   => TRUE,
                'inline'     => TRUE,
                'error_text' => self::$locale['SMLY_438']
            ] );
        } else {
            echo form_fileinput( 'smiley_file', '', '', [
                'upload_path'     => IMAGES.'smiley/',
                'delete_original' => TRUE,
                'template'        => 'modern',
                'type'            => 'image',
                'valid_ext'       => '.jpg,.png,.PNG,.JPG,.JPEG,.gif,.GIF,.bmp,.BMP,.svg,.SVG,.tiff,.TIFF',
                'required'        => TRUE
            ] );
        }
        echo form_text( 'smiley_code', self::$locale['SMLY_420'], $this->data['smiley_code'], [
            'required'   => TRUE,
            'inline'     => TRUE,
            'error_text' => self::$locale['SMLY_437']
        ] );
        echo form_text( 'smiley_text', self::$locale['SMLY_422'], $this->data['smiley_text'], [
            'required'   => TRUE,
            'inline'     => TRUE,
            'error_text' => self::$locale['SMLY_439']
        ] );
        $smileybuton = ($this->data['smiley_id'] ? self::$locale['SMLY_424'] : self::$locale['SMLY_423']);
        echo form_button( 'smiley_save', $smileybuton, $smileybuton, [ 'class' => 'btn-primary' ] );
        echo closeform();

        if ( !empty( $smileys_opts ) ) {
            add_to_jquery( "
                function showMeSmileys(item) {
                    return '<aside class=\"pull-left\" style=\"width:20px;height:20px;\"><img style=\"height:15px;\" class=\"img-rounded\" alt=\"'+item.text+'\" src=\"".IMAGES."smiley/'+item.id+'\"/></aside> - ' + item.text;
                }
                $('#smiley_image').select2({
                formatSelection: function(m) { return showMeSmileys(m); },
                formatResult: function(m) { return showMeSmileys(m); },
                escapeMarkup: function(m) { return m; },
                });
            " );
        }
        echo "</div>";
    }

}

$smileys = SmileysAdministration::getInstance();
$smileys->display_admin();

require_once THEMES.'templates/footer.php';
