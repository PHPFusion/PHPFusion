<?php
declare( strict_types=1 );
namespace PHPFusion\Infusions\Profile_Home\Panels\Followers;

use PHPFusion\Infusions\Marketplace\Classes\Marketplace;
use PHPFusion\Template;
use PHPFusion\UserFields;
use PHPFusion\UserRelations;

class Followers {
    
    private $data = [];
    
    /**
     * mpItems constructor.
     *
     * @param $data
     */
    public function __construct( $data ) {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function viewFollowers() {
        
        $html = '<div class="panel panel-profile"><div class="text-center"><h4>Grow your Audience</h4>
		<p>Reach out to other fusioneers to get people following you.</p>
		<a href="'.BASEDIR.'members.php" class="text-success small text-uppercase strong">Browse</a></div></div>';
        
        $relations = new UserRelations( new UserFields() );
        $follows_list = $relations->getUserFollowers( $this->data['user_id'] );
        
        if ( $follows_list ) {
            $itemtpl = Template::getInstance( 'user-item' );
            foreach ( $follows_list as $followers ) {
                // we view it out.
                $user = fusion_get_user( $followers['user_id'] );
                if ( !empty( $user['user_id'] ) && !$user['user_status'] ) {
                    $followers['user_name'] = $user['user_name'];
                    $followers['user_avatar'] = display_avatar( $user, '30px', '', FALSE );
                    $followers['profile_link'] = BASEDIR.'profile.php?lookup='.$followers['user_id'];
                    
                    $itemtpl->set_template( __DIR__.'/item.html' );
                    $itemtpl->set_block( 'item', $followers );
                }
            }
            $html = $itemtpl->get_output();
        }
        
        return $html;
    }
    
}
