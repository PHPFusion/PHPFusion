<?php

namespace PHPFusion\Infusions\Profile_Home;

use PHPFusion\Template;
use PHPFusion\UserFields;


/**
 * Class ProfileHome
 */
class ProfileHome {

    private $current_user_id = 0;

    private $profile_id = 0;

    private $profile_data = [];

    /**
     * ProfileHome constructor.
     *
     * @param UserFields\Pages\ProfileOutput $user_fields
     */
    public function __construct( UserFields\Pages\ProfileOutput $user_fields ) {

        $this->current_user_id = fusion_get_userdata( 'user_id' );

        $this->profile_data = $user_fields->user_data;//fusion_get_user( $this->profile_id );

        $this->profile_id = $this->profile_data['user_id'];

    }

    /**
     * @throws \Exception
     */
    private function installDefaultPanels() {
        if ( $this->profile_id ) {
            if ( !dbcount( "(panel_id)", DB_PROFILE_PANEL, 'panel_user=:pid', [ ':pid' => (int)$this->profile_id ] ) ) {
                // install the default panels which can be set in the admin later.
                $default_config = [
                    [
                        'panel_id'       => 0,
                        'panel_user'     => $this->profile_id,
                        'panel_name'     => 'mpitems',
                        'panel_position' => 0,
                        'panel_order'    => 1,
                    ],
                    [
                        'panel_id'       => 0,
                        'panel_user'     => $this->profile_id,
                        'panel_name'     => 'mpcollections',
                        'panel_position' => 1,
                        'panel_order'    => 1,
                    ],
                    [
                        'panel_id'       => 0,
                        'panel_user'     => $this->profile_id,
                        'panel_name'     => 'followers',
                        'panel_position' => 1,
                        'panel_order'    => 2,
                    ],
                    [
                        'panel_id'       => 0,
                        'panel_user'     => $this->profile_id,
                        'panel_name'     => 'following',
                        'panel_position' => 1,
                        'panel_order'    => 3,
                    ],
                    [
                        'panel_id'       => 0,
                        'panel_user'     => $this->profile_id,
                        'panel_name'     => 'about',
                        'panel_position' => 2,
                        'panel_order'    => 1
                    ],
                    [
                        'panel_id'       => 0,
                        'panel_user'     => $this->profile_id,
                        'panel_name'     => 'posts',
                        'panel_position' => 2,
                        'panel_order'    => 2
                    ],
                    [
                        'panel_id'       => 0,
                        'panel_user'     => $this->profile_id,
                        'panel_name'     => 'comments',
                        'panel_position' => 2,
                        'panel_order'    => 3
                    ],
                ];
                foreach ( $default_config as $panel_ins ) {
                    dbquery_insert( DB_PROFILE_PANEL, $panel_ins, 'save' );
                }
                //add_notice( 'success', 'Welcome to your Profile Page' );
                redirect( FUSION_REQUEST );
            }
        }

    }

    /**
     * @return string
     * @throws \Exception
     */
    public function show() {
        if ( $this->loadPanels() ) {

            add_to_footer( "<script src='".INFUSIONS."profile_home/templates/js/ph.js'></script>" );

            $tpl = Template::getInstance( 'profile-home' );
            $tpl->set_template( __DIR__.'/templates/layout.html' );
            $tpl->set_tag( 'top', HP_TOP );
            $tpl->set_tag( 'left', HP_LEFT );
            $tpl->set_tag( 'right', HP_RIGHT );

            return $tpl->get_output();
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function loadPanels() {
        $result = dbquery( "SELECT * FROM ".DB_PROFILE_PANEL." WHERE panel_user=:uid ORDER BY panel_position ASC, panel_order ASC", [
            ':uid' => (int)$this->profile_id
        ] );
        if ( dbrows( $result ) ) {
            $panel_cache[0] = [];
            $panel_cache[1] = [];
            $panel_cache[2] = [];
            while ( $data = dbarray( $result ) ) {
                $panel_path = INFUSIONS.'profile_home/panels/'.$data['panel_name'].'/panel.php';
                try {

                    ob_start();

                    include $panel_path; // echo based panel only

                    $panels = fusion_filter_current_hook('profile_home_panels', $this->profile_data);
                    //fusion_remove_hook('profile_home_panels');

                    if ( !empty( $panels ) ) {

                        $ob_get_clean = $panels;

                        $panel_cache[ $data['panel_position'] ][] = $ob_get_clean;
                    }

                    //ob_end_clean();

                } catch ( \Exception $exception ) {
                    $exception->getMessage();
                }
            }
            define( 'HP_TOP', implode( '', $panel_cache[0] ) );
            define( 'HP_LEFT', implode( '', $panel_cache[1] ) );
            define( 'HP_RIGHT', implode( '', $panel_cache[2] ) );
            return TRUE;
        }

        $this->installDefaultPanels();

        return FALSE;
    }
}
