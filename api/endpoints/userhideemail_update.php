<?php

function update_hide_email()
{
	if (!iMEMBER) {
		exit;
	}
	
	$parameter = [
		':uid' => fusion_get_userdata('user_id'),
		':val' => post('value', FILTER_VALIDATE_INT),
	];
	
	dbquery("UPDATE " . DB_USERS . " SET user_hide_email=:val WHERE user_id=:uid", $parameter);
}

/**
 * @uses update_hide_email()
 */
fusion_add_hook('fusion_filters', 'update_hide_email');