<?php
require_once __DIR__."/../../maincore.php";

// You need to login to PHPFusion Administration for this to work
$result = fusion_request('admin/settings/update', 'POST', [
	'sitename' => 'PHPFusion Website (API Test)',
	'siteemail' => 'test@phpfusion.com'
]);

echo "<h3>API Request Result (External)</h3>";
echo "<pre>";
print_r($result);
echo "</pre>";