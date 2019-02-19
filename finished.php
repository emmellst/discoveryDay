<?
require_once("functions.php");

auth_setup();
updateMailQueue($_SESSION['id'],-50000);
markRegistered();	
	
auth_logout();
echo "1";
?>
