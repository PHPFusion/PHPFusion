<?php

function update_profile_display()
{
	if (!iMEMBER) {
		exit;
	}
	
	$parameter = [
		':uid' => fusion_get_userdata('user_id'),
		':val' => post('value', FILTER_VALIDATE_INT),
	];
	
	dbquery("UPDATE " . DB_USERS . " SET user_display=:val WHERE user_id=:uid", $parameter);
}

/**
 * @uses update_profile_display()
 */
fusion_add_hook('fusion_filters', 'update_profile_display');