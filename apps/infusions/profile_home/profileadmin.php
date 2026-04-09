<?php
namespace PHPFusion\Infusions\Profile_Home;

use PHPFusion\Admins;
use PHPFusion\Infusions\Profile_Home\Classes\AdminCache;
use PHPFusion\Infusions\Profile_Home\Classes\JournalCats;

class ProfileAdmin {
    
    private $pages = [];
    
    public function __construct() {
        $aidlink = fusion_get_aidlink();
        $base_url = INFUSIONS.'profile_home/administration.php'.$aidlink;
        $this->cache = new AdminCache();
        $admin = Admins::getInstance();
        $admin->addAdminPage( 'PFHP', 'Dashboard', 'PFHP0', $base_url.'&amp;home', '<i class="fas fa-home m-r-10"></i>' );
        $journal_active = FALSE;
        if ( get( 'ref' ) == 'jcats' ) {
            $journal_active = TRUE;
        }
        $admin->addAdminPage( 'PFHP', 'Journal Categories', 'PFHP1', $base_url.'&amp;ref=jcats', '', [], $journal_active );
        $admin->addAdminPage( 'PFHP', 'Settings', 'PFHP2', $base_url.'&amp;ref=settings' );
        
    }
    
    private $cache;
    
    public function viewAdmin() {
        
        $this->loadPages();
        
        $this->breadCrumb();
        
        opentable( 'Profile Infusion Administration' ).$this->content().closetable();
    }
    
    
    private function loadPages() {
        
        switch ( get( 'ref' ) ) {
            default:
            case 'home':
                $this->pages = $this->showHome();
                break;
            case 'jcats':
                $jcats = new JournalCats();
                $this->pages = [
                    'content'    => $this->cache->setClass( $jcats )->cache( 'showAdmin' ),
                    'breadcrumb' => $jcats->getCrumb()
                ];
                break;
            case 'settings':
                $this->pages = $this->showSettings();
                break;
            
        }
        return $this->pages;
    }
    
    private function content() {
        
        echo $this->pages['content'];
    }
    
    private function breadCrumb() {
        if ( isset( $this->pages['breadcrumb'] ) && is_array( $this->pages['breadcrumb'] ) ) {
            add_breadcrumb( $this->pages['breadcrumb'] );
        }
        
    }
    
    private function showHome() {
    
    }
    
    private function showSettings() {
    
    }
    
}
