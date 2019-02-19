<?php
require_once("database.php");
require_once("functions.php");

if ($settings['regEnabled']) {
	echo "<h2>Registration currently ENABLED</h2>\n";
	echo "<button type=\"button\" class=\"btn btn-default\" onclick=\"toggleRegistration(0);\">Disable</button>";
}
else {
	echo "<h2>Registration currently DISABLED</h2>\n";
	echo "<button type=\"button\" class=\"btn btn-default\" onclick=\"toggleRegistration(1);\">Enable</button>";
}
?>
</form>
<br/><br/><br/>
<a onclick="loadStudentUpload();">Upload Students</a><br/>
<a onclick="loadSessionUpload();">Upload Sessions</a><br/>
<a onclick="loadPwdChange();">Change Admin Password</a>
