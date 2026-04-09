<?php

use PHPFusion\Infusions\Profile_Home\ProfileHome;
use PHPFusion\Template;
use PHPFusion\UserFields\Pages\ProfileOutput;

/**
 * @param ProfileOutput $profile_output
 *
 * @return string
 * @throws Exception
 */
function show_profile_home( ProfileOutput $profile_output ) {
    $profileHome = new ProfileHome( $profile_output );
    
    return $profileHome->show();
}

/**
 * @param        $name
 * @param        $title
 * @param        $content
 * @param string $edit_link
 * @param string $more_link
 *
 * @return string
 */
function show_profile_panel( $name, $title, $content, $edit_link = '', $more_link = '' ) {
    $tpl = Template::getInstance( 'hp-panel' );
    $tpl->set_template( __DIR__.'/templates/panel.html' );
    $tpl->set_tag( 'panel_name', $name );
    $tpl->set_tag( 'panel_title', $title );
    $tpl->set_tag( 'panel_content', $content );
    if ($edit_link) {
        $tpl->set_block('edit', [
            'link' => $edit_link
        ]);
    }
    if ($more_link) {
        $tpl->set_block('more', [
            'link' => $more_link
        ]);
    }

    return $tpl->get_output();
}

fusion_add_hook( 'fusion_profile_page', 'show_profile_home' );
