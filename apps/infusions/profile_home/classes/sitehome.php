<?php
namespace PHPFusion\Infusions\Profile_Home\Classes;

use PHPFusion\Infusions\Marketplace\Classes\Marketplace;
use PHPFusion\Infusions\Profile_Home\ProfileJournals;
use PHPFusion\SiteLinks;
use PHPFusion\Template;

/**
 * Class SiteHome
 * Integrated into display_home();
 *
 * @package PHPFusion\Infusions\Profile_Home\Classes
 */
class SiteHome {

    private $menu_filters;

    public function __construct() {

    }

    public function view() {
        $tpl = Template::getInstance( 'sitehome' );
        $tpl->set_template( __DIR__.'/../templates/sitehome-layout.html' );
        $tpl->set_tag( 'menu', $this->siteMenu() );
        $tpl->set_tag( 'content', $this->siteView() );
        return $tpl->get_output();
    }

    private function siteMenu() {
        $menu_filters = [ 'commissions', 'mp', 'journals' ];
        $callback = [
            0 => [
                'mp'          => [
                    'link_id'   => 'mp',
                    'link_name' => 'Marketplace',
                    'link_url'  => clean_request( 'galaxy=daily', $menu_filters, FALSE ),
                ],
                'journals'    => [
                    'link_id'   => 'journals',
                    'link_name' => 'Journals',
                    'link_url'  => clean_request( 'galaxy=daily', $menu_filters, FALSE ),
                ],
                'commissions' => [
                    'link_id'   => 'commissions',
                    'link_name' => 'Commissions',
                    'link_url'  => clean_request( 'galaxy=daily', $menu_filters, FALSE ),
                ],
            ],
        ];

        return SiteLinks::setSubLinks( [
            'container'          => TRUE,
            'navbar_class'       => 'navbar-inverse m-0',
            'callback_data'      => $callback,
            'id'                 => 'shmenu',
            'show_banner'          => true,
            'show_header'          => true,
            'custom_banner'      => 'Browse',
            'custom_banner_link' => BASEDIR.'home.php'
        ] )->showSubLinks();
    }

    private function siteView() {
        switch ( get( 'view' ) ) {
                //
                //$marketplace = new Marketplace();
                //$output = $marketplace->siteView();
                //return $output;
                //break;
            default:
            case 'journals':
                $journals = new ProfileJournals();
                return $journals->siteView();
                break;
            case 'commissions':
                $hadc = new Hadc();
                return $hadc->siteView();
                break;
        }
    }

}
