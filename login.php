<?php
require_once("database.php");
require_once("functions.php");
auth_setup();

//STUDENT LOGIN
if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['func']) && $_POST['func']=="auth") {
	if (!isset($_POST['user']) || !isset($_POST['pass'])) die("0");
	$result = false;
	if (auth_login($_POST['user'],$_POST['pass'])) {
		$_SESSION['auth_user']=strtolower($_POST['user']);
		$_SESSION['id'] = get_student_id($_POST['user'])[0]['id'];
		$result = true;
	} else {
		if (isset($_SESSION)) {
			session_unset();
			session_destroy();
		}
		$notValid = true;
	}
	if (isset($_POST['headless']) && ($_POST['headless'] == "1")) {
		echo ($result ? "1":"0");
		die();
	}
	else {
		if ($result == true) 
			header("location: registration.php");
		else {
			echo "FAILED";
		}
	}
}

//ADMIN LOGIN
else if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['func']) && $_POST['func']=="authadmin") {
	if (!isset($_POST['user']) || !isset($_POST['pass'])) die("0");

	if ($_POST['user'] == "admin" && auth_login($_POST['user'],$_POST['pass'])) {
		$_SESSION['auth_user']=strtolower($_POST['user']);
		$_SESSION['id'] = get_student_id($_POST['user'])[0]['id'];		
		echo "1";
		die();
	}
	else {
		echo "0";
		die();
	}
}
?>
<!DOCTYPE html>
<html>
<body>
<?php
if (isset($notValid) && $notValid) {
	echo "Invalid credentials, please try again.<hr/><br/>";
}?>
<form method="POST">
Username: <input type="text" name="user"><br/>
Password: <input type="password" name="pass"><br/><br/>

<input type="submit" value="LOG IN"/>
</form>
</body>
</html>
