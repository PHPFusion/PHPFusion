<?php
if (!defined("IN_FUSION")) { die("Access Denied"); }

if (!defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

$login_error = "";
$admin_password = "";

// Check if the user has admin password set
if ($userdata['user_admin_password']) {
	// Check if a password was entered
	if (isset($_POST['admin_password'])) {
		$login_error = $locale['global_182'];

		// Verify the token before setting the admin cookie
		if (verify_token('admin_login', 1, '')) {
			$admin_password = stripinput($_POST['admin_password']);
			set_admin_pass($admin_password);
		} else {
			$login_error = $locale['token_error'];
		}
	}
} else {
	$login_error = $locale['global_199'];
}

// Check if admin password is set
if (!check_admin_pass($admin_password)) {
	require_once THEMES."templates/admin_header.php";

	// Show error, if any
	if ($login_error) {
		echo "<div class='admin-message'>".$login_error."</div>\n";
	}

	opentable($locale['270']);
	echo "<form class='admin-login-form' name='admin_login' method='post' action='".FUSION_SELF."?".FUSION_QUERY."'>\n";
	echo "<input type='hidden' name='fusion_token' value='".generate_token('admin_login', 1)."' />"; // form token
	// Keep $_POST data if user is forced to relogin due to cookie expiration
	//foreach ($_POST as $key => $value) {
	//echo "<input type='hidden' name='".stripinput($key)."' value='".stripinput($value)."' />";
	//}
	echo "<table align='center' cellspacing='0' cellpadding='0'>\n<tr>\n";
	echo "<td class='tbl'><label for='admin_password'>".$locale['271']."</label></td>\n";
	echo "<td class='tbl'><input type='password' id='admin_password' name='admin_password' value='' class='textbox' style='width:150px;' autocomplete='off' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'>\n";
	echo "<input class='button' type='submit' name='admin_login' value='".$locale['global_100']."' /></td>\n";
	echo "</tr>\n</table>\n</form>\n";
	closetable();

	require_once THEMES."templates/footer.php";

	exit();
}

unset($login_error, $admin_password);
?>