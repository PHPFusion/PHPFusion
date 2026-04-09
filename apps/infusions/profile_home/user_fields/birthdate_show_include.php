<?php
( defined( 'IN_FUSION' ) || exit );

if ( $profile_method == 'input' ) {

    $user_fields = form_select( 'user_birthdate_show', 'Birthday Display', $user_data['user_birthdate_show'], [
        'options' => [
            0 => "Don't show my birthdate publicly",
            1 => "Show only month and day publicly",
            2 => "Show my full birthdate and age publicly",
        ],
        'select_alt' => TRUE,
    ] );
    
}


