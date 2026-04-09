<?php
header("Cache-Control: no-cache");
header("Pragma: nocache");
header("Content-Type: text/html; charset=utf-8");
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
require_once __DIR__.'/../../maincore.php';

$search = stripinput($_POST['search_form']);
cleanurl($search);

if (!preg_match("/^[-0-9A-Z_@\s]+$/i", $search)) { 
	echo "<br /><div class='admin-message'>Unallowed Character input</div><br />";
	exit;
} else {

if($search != "" && strlen($search) < 2) {
	echo "<br /><div class='admin-message'><center><b>To short input</b><br /> min 3 chars is required</center></div><br />";
	exit;
} else {
	if (!function_exists('fusion_each')) {
		function fusion_each(&$arr) {
			$key = key($arr);
			$result = ($key === null) ? false : [$key, current($arr), 'key' => $key, 'value' => current($arr)];
			next($arr);
			return $result;
		}
	}
	$search_text=ltrim($search);
	$search_text=rtrim($search_text);
	$q = "";
	$kt = "";
	$val = "";
	$kt = explode(" ",$search_text);
	while(list($key,$val)= fusion_each($kt)){
	if($val<>" " and strlen($val) > 0){ $q.= " faq_question like '%$val%' or faq_answer like '%$val%' or ";}
}
	$q=substr($q,0,(strlen($q)-3));
	$result = dbquery("SELECT * FROM ".DB_FAQS." WHERE ".$q ." ORDER BY faq_id ASC LIMIT 25");
}

if (dbrows($result) != 0) {
$numRecords = dbrows($result);
echo "<div class='admin-message'><center>We found<b> <font color='red'>".$numRecords."</b></font> Results</center></div><br />";

echo "<div class='table-responsive'>";
echo "<table class='table table-sm table-hover'>\n";

while ($data = dbarray($result)) {
	echo "<tr><td style='padding-left:25px;' width='1%' align='left'> <a href='faq.php?faq_id=".$data['faq_id']."&amp;cat_id=".$data['faq_cat_id']."'><b>".$data['faq_question']."</b></a></td></tr>\n";
}
echo "</table>";
echo "</div>";

} else {
	echo "<br /><div class='admin-message'><center>No result</center></div><br />";
}
}

}