<?php
require_once("functions.php");
auth_setup();
auth_logout(); 

header("Location: index.php");
?>
