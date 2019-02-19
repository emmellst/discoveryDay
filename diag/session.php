<?
require_once("../database.php");
require_once("../functions.php");

auth_setup();
echo "<HTML><PRE>\n\n";
print_r($_SESSION);
echo "\n\n</PRE></HTML>";
?>
