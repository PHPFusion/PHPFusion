<?php
require_once "../../maincore.php";

$this_response = array('fusion_error_id'=>$id, 'from'=>'0', 'status'=>'Pending');

$aid = isset($_POST['token']) ? explode('=', $_POST['token']) : '';
if (!empty($aid)) {
	$aid = $aid[1];
}
$id = isset($_POST['i']) && isnum($_POST['i']) ? $_POST['i'] : 0;
$type = isset($_POST['t']) && isnum($_POST['t']) ? $_POST['t'] : 0;
$debug_ajax = array(
	'post' => $_POST,
	'checkrights' => checkrights('ERRO') ? 1 : 0,
	'defined' => defined('iAUTH') ? 1 : 0,
	'token' => $_POST['token'],
	'aid' => $aid == iAUTH ? 1 : 0,
);
//print_p($debug_ajax);

if (checkrights("ERRO") && defined("iAUTH") && $aid == iAUTH) {
	$this_response = array('fusion_error_id'=>$id, 'from'=>0, 'status'=>'Not Updated');
	$result = dbquery("SELECT error_status	FROM ".DB_ERRORS." WHERE error_id='".intval($id)."'");
	if (dbrows($result)>0) {
		$data = dbarray($result);
		if ($type == 999) {
			$result = dbquery("DELETE FROM ".DB_ERRORS." WHERE error_id='".intval($id)."'");
			if ($result) {
				$this_response = array('fusion_error_id'=>$id, 'from'=>$data['error_status'], 'to'=>$type, 'status'=>'RMD');
			}
		} else {
			$result = dbquery("UPDATE ".DB_ERRORS." SET error_status='".intval($type)."' WHERE error_id='".intval($id)."'");
			if ($result) {
				$this_response = array('fusion_error_id'=>$id, 'from'=>$data['error_status'], 'to'=>$type, 'status'=>'OK');
			}
		}
	} else {
		$this_response = array('fusion_error_id'=>$id, 'from'=>0, 'status'=>'Invalid ID');
	}
} else {
	$this_response = array('fusion_error_id'=>$id, 'from'=>0, 'status'=>'Invalid Token or Insufficient Rights');
}
echo json_encode($this_response);

?>