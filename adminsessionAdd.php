<?php
require_once("database.php");
require_once("functions.php");
require_once("Session.php");
global $settings;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$newSession = new Session();
	//$newSession->name = $_POST['name'] == "" ? " " : $_POST['name'];
	$newSession->id = "";
	$newSession->name = $_POST['name'];
	$newSession->desc = $_POST['description'];
	$newSession->block = $_POST['block'];
	$newSession->cost = $_POST['cost'];
	$newSession->forms = $_POST['pathToForm'];
	$newSession->supervisor = $_POST['supervisor'];
	$newSession->secretary = $_POST['secretary'];
	$newSession->presenter = $_POST['presenter'];
	$newSession->room = $_POST['room'];
	$newSession->capacity = $_POST['capacity'];
	$newSession->active = "1";
	$newSession->buffer = $settings['bufferDefault'];

	if ($_POST['length'] == "double") {
		if ($_POST['block'] == "1") $newSession->linked="1,2";
		else if ($_POST['block'] == "2") $newSession->linked="2,3";
	}
	else if($_POST['length'] == "triple") {
		$newSession->linked="1,2,3";
	}	
	session_add($newSession);
	echo "Session Added!";

}
?>
<html>
<body>
<form method="POST">
<h3>Add a new session</h3>
<p>Leave anything blank as you need - you can change this later</p>
Name: <input type="text" name="name" length="50"><br/>
Description: <br/><textarea name="description" rows="8" cols="100"></textarea><br/>
Cost: <input type="text" name="cost"></br>
Path to PDF of form: <input type="text" name="pathToForm"><br/>
Schedule Block (if double/triple, list the first block): <input type="radio" name="block" value="1">1 &nbsp;&nbsp;<input type="radio" name="block" value="2"> 2 &nbsp;&nbsp;<input type="radio" name="block" value="3"> 3 </br>
Length: <br/><input type="radio" name="length" value="single">Single Session<br/>
	     <input type="radio" name="length" value="double">Double Session<br/>
	     <input type="radio" name="length" value="triple">Triple Session<br/>
Supervisor: <input type="text" name="supervisor"><br/>
Secretary: <input type="text" name="secretary"><br/>
Presenter: <input type="text" name="presenter"><br/>
Room: <input type="text" name="room"><br/>
Max # students: <input type="text" name="capacity">
<br/>
<input type="submit" value="Add New Session">


</form>
</body>
</html>
