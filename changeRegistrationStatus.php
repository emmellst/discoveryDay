<?php

require_once("database.php");
require_once("functions.php");

	if (isset($_POST['reg'])) {
		if ($_POST['reg'] == 1) enableRegistration(1);
		else if ($_POST['reg'] == 0) enableRegistration(0);
		echo "1";
	}

?>