<?php
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/header.php';

echo "<div class='".grid_container()."'>";

if ( post( 'test_token' ) ) {
    if ( fusion_safe() ) {
        add_notice( 'success', 'This token is valid' );
        redirect( FUSION_REQUEST );
    }
    add_notice( "danger", 'Token is invalid' );
    redirect( FUSION_REQUEST );
}

echo "<form class='spacer-lg' name='someform' method='post' action='".FORM_REQUEST."'>";
echo "<h4>Token validator</h4>";
echo form_text( 'form_id', 'Enter Form ID', '', [ 'required' => TRUE ] );
echo form_text( 'fusion_token', 'Enter Token Value', '', [ 'required' => TRUE ] );
echo form_button( 'test_token', 'Validate Token', 'test_token', [ 'class' => 'btn-success' ] );
echo "</form>";
echo "</div>";

require_once THEMES.'templates/footer.php';
