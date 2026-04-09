<?php
// PHPFusion Custom Store Development.
use PHPFusion\Installer\Batch;
require_once __DIR__.'/../store/store.php';

defined('IN_FUSION') || exit;

$contents = [
    'post'     => 'pf_post',
    'view'     => 'pf_view',
    'button'   => 'pf_button',
    'js'       => 'pf_js',
    'settings' => TRUE,
    'link'     => ( $admin_link ?? '' ),
    'title'    => 'PHPFusion Store',
    //'description' => $locale['BN_001'],
    'actions'  => [ 'post' => [ 'savesettings', 'clearcache' ], 'post_form' => 'settingsform' ],
];

function pf_button()
: string {

    return Store::getInstance()->buttons();
}

function pf_view() {

    $locale = fusion_get_locale();
    $store = Store::getInstance();
    // $batch = Batch::getInstance();
    // $batch->customBatch('store_downloads');
    // $batch->customBatch('store_notifications');
    openside('');
    // need to put a menu on top.

    $query = (new Query())->latestItems();

    $navbar = navbar('modulenavbar', $store->navlinks(),               [
                         'form' => openform('storeSearchFrm', 'POST') .
                                   form_select('search_type', '', '', [
                                       'options'     => [
                                           'keyword' => 'Keyword',
                                           'author'  => 'Author',
                                           'tag'     => 'Tag'
                                       ],
                                       'width'       => '100px',
                                       'inner_width' => '100px'
                                   ]) .
                                   form_text('search_text', '', '', [ 'placeholder' => 'Search PHPFusion Store' ]) .
                                   closeform()
                     ]);


    $html = "<div class='alert alert-info text-center m-b-20'>PHPFusion store introduction</div>";

    // if ( ! empty($this->parent->available_field_info) ) {
    //
    //     $html = '<div class="clearfix spacer-md">';
    //     // pagination
    //     $limit = 12;
    //     $fields = array_chunk($this->parent->available_field_info, $limit);
    //     $cur_page = 0;
    //     if ( count($fields) > 1 ) {
    //         $total_count = count($this->parent->available_field_info); // number of entries
    //         $rowstart = get_rowstart('r', $total_count);
    //         $pagenav_top = makepagenav($rowstart, $limit, $total_count, '3', '', 'r');
    //         $pagenav_bottom = makepagenav($rowstart, $limit, $total_count, '3', '', 'r');
    //         $cur_page = ( $rowstart / $limit ) - 1;
    //         if ( ! isset($fields[ $cur_page ]) ) {
    //             $cur_page = 0;
    //         }
    //     }
    //
    //     if ( isset($pagenav_top) ) {
    //         $html .= '<div class="text-right">' . $pagenav_top . '</div>';
    //     }
    //
    //     $html .= '<div class="row">';
    //
    //     foreach ( $fields[ $cur_page ] as $title => $mod ) {
    //         // print_p($mod);
    //
    //         $html .= '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4">
    //             <div class="pf--app-wrapper">
    //                 <div class="pf--app-body">
    //                     <img src="' . $mod['image'] . '" alt="' . $mod['title'] . '">
    //                     <div>
    //                         <div class="pull-right">
    //                              <small>v.' . $mod['version'] . '</small>
    //                              </div>
    //                         <h4>' . $mod['title'] . '</h4>
    //                         <span class="pf--app-description">' . $mod['description'] . '</span>
    //                     </div>
    //                 </div>
    //                 <div class="pf--app-footer">
    //
    //                     <div class="pf-app-ratings">
    //                          <i class="fas fa-star text-warning"></i>
    //                         <i class="fas fa-star text-warning"></i>
    //                         <i class="fas fa-star text-warning"></i>
    //                         <i class="fas fa-star text-warning"></i>
    //                         <small>0</small><br>
    //                            <small>0 Active Installations</small>
    //                     </div>
    //                 <div><a href="' . $mod['status_link'] . '" class="btn btn-inverse' . $mod['status_class'] . '"><span>' . $mod['status_text'] . '</span></a></div>
    //         </div>
    //             </div>
    //             </div>';
    //
    //     }
    //
    //     $html .= '</div>' .
    //              '</div>';
    //
    //     if ( isset($pagenav_bottom) ) {
    //         $html .= '<div class="text-right">' . $pagenav_bottom . '</div>';
    //     }
    //
    // }


    closeside();
}
