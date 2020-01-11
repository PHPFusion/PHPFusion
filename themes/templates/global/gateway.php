<?php

use PHPFusion\Template;

( defined( 'IN_FUSION' ) || exit );

if ( !function_exists( 'display_gateway' ) ) {
    /**
     * @param $info
     *
     * @return string
     */
    function display_gateway( $info ) {
        $locale = fusion_get_locale();
        
        $tpl = Template::getInstance( 'gateway' );
        
        $tpl->set_locale( $locale );
        
        $tpl->set_template( __DIR__.'/tpl/gateway.html' );
        
        $table_open = fusion_get_function( 'opentable', $locale['gateway_069'] );
        
        $table_close = fusion_get_function( 'closetable' );
        
        $tpl->set_tag( 'opentable', $table_open );
        
        $tpl->set_tag( 'closetable', $table_close );
        
        $tpl->set_tag( 'sitelink', BASEDIR.fusion_get_settings( 'opening_page' ) );
        
        $tpl->set_tag( 'sitebanner', fusion_get_settings( 'sitebanner' ) );
        
        $tpl->set_tag( 'sitename', fusion_get_settings( 'sitename' ) );
        
        $tpl->set_tag( 'copyright', showcopyright() );
        
        
        if ( $info['incorrect_answer'] == TRUE ) {
            
            $tpl->set_block( 'gateway_invalid', [
                'opentable'     => $table_open,
                'closetable'    => $table_close,
                'register_link' => BASEDIR.'register.php',
            ] );
            
        } else if ( $info['showform'] == TRUE ) {
            
            $tpl->set_block( 'gateway_form', [
                'opentable'    => $table_open,
                'closetable'   => $table_close,
                'openform'     => $info['openform'],
                'closeform'    => $info['closeform'],
                'question'     => $info['gateway_question'],
                'hidden_input' => $info['hiddeninput'],
                'text_input'   => $info['textinput'],
                'button'       => $info['button'],
            ] );
            
        } else if ( !session_get( 'validated' ) ) {
            
            $tpl->set_block( 'gateway_error' );
            
        }
        
        return (string)$tpl->get_output();
    }
}
