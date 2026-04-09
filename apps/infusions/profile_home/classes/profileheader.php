<?php
namespace PHPFusion\Infusions\Profile_Home\Classes;

use PHPFusion\Template;

class ProfileHeader {
    private $info = [];

    public function __construct( $info ) {
        $this->info = $info;
    }

    public function show() {
        $tpl = Template::getInstance( 'up-header' );
        $tpl->set_template( __DIR__.'/../templates/profile_header.html' );

        $user_name = '';
        $user_avatar = '';
        $user_level = '';
        $basic_info = '';
        // Basic user information in the header
        if ( !empty( $this->info['core_field'] ) ) {
            // Core field put on top
            foreach ( $this->info['core_field'] as $field_id => $field_data ) {
                // Sets data to core field block
                $tpl->set_block( $field_id, $field_data );

                $skip = [
                    'profile_user_avatar',
                    'profile_user_name',
                    'profile_user_level',
                    'profile_user_group'
                ];

                if ( !in_array( $field_id, $skip ) ) {
                    $tpl->set_block( 'user_core_fields', $field_data );
                }
                // old method
                switch ( $field_id ) {
                    case 'profile_user_group':
                        if ( !empty( $field_data['value'] ) ) {
                            foreach ( $field_data['value'] as $groups ) {
                                $tpl->set_block( 'user_groups', $groups );
                            }
                        } else {
                            $tpl->set_block( 'user_group_na', [] );
                        }
                        break;
                    case 'profile_user_avatar':
                        $avatar['user_id'] = $this->info['user_id'];
                        $avatar['user_name'] = $this->info['user_name'];
                        $avatar['user_avatar'] = $field_data['value'];
                        $avatar['user_status'] = $field_data['status'];
                        $user_avatar = display_avatar( $avatar, '130px', 'profile-avatar', FALSE, 'img-responsive' );
                        break;
                    case 'profile_user_name':
                        $user_name = $field_data['value'];
                        break;
                    case 'profile_user_level':
                        $user_level = $field_data['value'];
                        break;
                    default:
                        break;
                }
            }
        }

        $tpl->set_tag( 'basic_info', $basic_info );
        $tpl->set_tag( 'user_name', $user_name );
        $tpl->set_tag( 'user_avatar', $user_avatar );
        $tpl->set_tag( 'user_level', $user_level );

        $tpl->set_tag( 'user_cover', $this->userCoverForm() );


        return $tpl->get_output();
    }


    private function userCoverForm() {

        if ( iPROFILE ) {
            // save cover php action
            if ( post( 'save_cover' ) && file_uploaded( 'user_cover' ) ) {
                // validate image is not working.
                $upload = file_sanitizer( 'user_cover', '', 'user_cover' );
                if ( !empty( $upload ) ) {
                    include INCLUDES.'photo_functions_include.php';
                    $folder = INFUSIONS.'profile_home/images/user_covers/';

                    $cover_data = [
                        'user_id'    => $this->info['user_id'],
                        'user_cover' => $upload['image_name'],
                    ];
                    // remove all cover if any
                    unlink( $folder.$this->info['user_cover'] );
                    // get position of images
                    $position = [
                        'x'  => sanitizer( 'top_x', 0, 'top_x' ),
                        'y'  => sanitizer( 'top_y', 0, 'top_y' ),
                        'y1' => sanitizer( 'bottom_y', 0, 'bottom_y' ),
                    ];

                    $current_image = $upload['image_name'];
                    $new_image = image_exists( $folder, md5( $upload['image_name'] ).'-'.implode( '-', $position ).strchr( $upload['image_name'], '.' ) );

                    if ( rename( $folder.$current_image, $folder.$new_image ) ) {
                        $cover_data['user_cover'] = $new_image;
                        // remove old cover data if any
                        dbquery_insert( DB_USERS, $cover_data, 'update' );
                        add_notice( 'success', 'Your Profile Cover was updated', 'all' );
                    }

                }
                redirect( FUSION_REQUEST );
            }


            $js = /** @lang JavaScript */
                "$('#user_cover').bind('change', function(e) {
                    $('.cover-buttons').show();
                    });
            $('#cancel_cover').bind('click', function(e) {
                e.preventDefault();
                $('.cover-buttons').hide();
                $('div#user_cover-croppie').hide();
                $('#user_cover').fileinput('reset');
                $('#user_cover-field').find('.file-input').show();
            });
            // view port
            //$(window).resize(function() {
            //    let viewport_w = $(window).width();
            //    // now for every pixel that is more than y offset
            //
            //});
            ";
            add_to_jquery( $js );


            return '<div class="user-cover-image" style="background:url('.INFUSIONS.'profile_home/images/user_covers/'.$this->info['user_cover'].') 0 center; background-size:cover;width:100%;"></div>'
                .openform( 'coverfrm', 'post', FORM_REQUEST, [ 'enctype' => TRUE ] ).
                form_fileinput( 'user_cover', '', '', [
                    'upload_path'             => INFUSIONS.'profile_home/images/user_covers/',
                    'croppie'                 => TRUE,
                    'croppie_resize'          => FALSE,
                    'croppie_zoom'            => FALSE,
                    'hide_upload'             => FALSE,
                    'croppie_viewport_width'  => '100%',
                    'croppie_box_width'       => '100%',
                    'croppie_viewport_height' => 650,
                    'croppie_box_height'      => 650,
                    'template'                => 'button',
                    'class'                   => 'profile-cover',
                    'button_text'             => ( $this->info['user_cover'] ? 'Change Cover' : 'Add Cover' ),
                    'icon'                    => 'fas fa-camera m-r-10',
                    'inline'                  => FALSE,
                    'max_width'               => 4000,
                    'max_height'              => 4000,
                    'max_byte'                => ( 10 * 1024 * 1024 ),
                ] ).'<div class="'.grid_container().'"><div class="cover-buttons" style="display:none;">'.form_button( 'save_cover', 'Save Cover', 'save_cover', [ 'class' => 'btn-success btn-file' ] ).
                form_button( 'cancel_cover', 'Cancel', 'cancel_cover', [ 'class' => 'btn-default btn-file' ] ).'</div></div>'.
                closeform();
        }
        //print_P( $this->info );
        return '<div class="user-cover-image" style="background:url('.INFUSIONS.'profile_home/images/user_covers/'.$this->info['user_cover'].') 0 center;  background-size:cover;width:100%;"></div>';

    }

    //private function getImagePosition() {
    //    $image_ext = strchr( $this->info['user_cover'], '.' );
    //    $image_name = str_replace( $image_ext, '', $this->info['user_cover'] );
    //    $image_positions = explode( '-', $image_name );
    //
    //    $image_size = getimagesize( INFUSIONS.'profile_home/images/user_covers/'.$this->info['user_cover'] );
    //    $image_height = $image_size[1] - 50; // 2160
    //    //print_P($image_height);
    //
    //    $pos_x = 0; // 976 ---- scrolltop 976.
    //    if ( isset( $image_positions[1] ) ) {
    //        $pos_x = $image_positions[1];
    //    }
    //    $pos_y = 0; // 2158
    //    if ( isset( $image_positions[2] ) ) {
    //        //$pos_y = -(($image_positions[2] / 580  * 100) + 50).'px';
    //        $pos_y = ( $image_positions[3] -  $image_positions[2] );
    //        $pos_y = -( $pos_y );
    //    }
    //
    //    if ( $pos_x ) {
    //        $pos_x = $pos_x.'px';
    //    }
    //    if ( $pos_y ) {
    //        $pos_y = $pos_y.'px';
    //    }
    //
    //
    //    return '0 '.$pos_y;
    //}

}
