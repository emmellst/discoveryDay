<?php
require_once("database.php");

	if (!empty($_POST)) {
		$pwd = $_POST['pwd'];
		
		if($query = $pdo->prepare("UPDATE `students` SET `password`=:newPass WHERE `id`=1")) {
			$queryArray = array(
				"newPass" => password_hash(trim($pwd),PASSWORD_DEFAULT)
			);
			$query->execute($queryArray);
		}
		die(1);

	}
?>
<div id="messageWindow"></div>
<form>
  <div class="form-group">
    <label for="newPwd">Password:</label>
    <input type="password" class="form-control" id="newPwd">
  </div>
  <div class="form-group">
    <label for="newPwd-conf">Confirm Password:</label>
    <input type="password" class="form-control" id="newPwd-conf">
  </div>
  <div id="buttonHolder"><button type="button" onClick="changeAdminPwd()" class="btn btn-default">Submit</button></div>
</form>
