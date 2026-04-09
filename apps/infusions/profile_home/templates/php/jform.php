<?php
require_once __DIR__.'/../../../../maincore.php';
require_once INCLUDES.'ajax_include.php';

$response = [
    'error'      => TRUE,
    'journal_id' => 0,
];
$post_button = post( 'post_button' );

if ( $post_button && iMEMBER ) {
    
    if ( $post_button == 'save_draft' || $post_button == 'save_journal' ) {
        
        $jcats = [
            'journal_id'       => sanitizer( 'journal_id', 0, 'journal_id' ),
            'journal_cat'      => sanitizer( 'journal_cat', '', 'journal_cat' ),
            'journal_subject'  => sanitizer( 'journal_subject', '', 'journal_subject' ),
            'journal_text'     => sanitizer( 'journal_text', '', 'journal_text' ),
            'journal_keywords' => sanitizer( 'journal_keywords', '', 'journal_keywords' ),
            'journal_draft'    => 0,
            'journal_sticky'   => sanitizer( 'journal_sticky', 0, 'journal_sticky' ),
            'journal_uid'      => fusion_get_userdata( 'user_id' )
        ];
        
        if ( $post_button == 'save_draft' ) {
            $jcats['journal_draft'] = 1;
        }
        
        if ( fusion_safe() ) {
            
            if ( $jcats['journal_id'] ) {
                dbquery_insert( DB_PROFILE_JOURNALS, $jcats, 'update' );
                $id = $jcats['journal_id'];
            } else {
                $id = dbquery_insert( DB_PROFILE_JOURNALS, $jcats, 'save' );
            }
            
            $response = [
                'error'      => FALSE,
                'journal_id' => $id,
            ];
        }
        
    }
}

echo json_encode( $response );
