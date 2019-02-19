<?php
require_once("database.php");
require_once("functions.php");
require_once("Session.php");
global $settings;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$allSessions = getAllSessions();
	$session = $allSessions[$_POST['id']];
	
	$session->name = $_POST['name'];
	$session->desc = $_POST['description'];
	$session->cost = $_POST['cost'];	
	$session->forms = $_POST['pathToForm'];	
	$session->block = $_POST['block'];	
	if ($_POST['length'] == "double" && $_POST['block'] == "1") $session->linked = "1,2";	
	else if ($_POST['length'] == "double" && $_POST['block'] == "2") $session->linked = "2,3";	
	else if ($_POST['length'] == "triple") $session->linked = "1,2,3";
	else $session->linked="";
	$session->supervisor = $_POST['supervisor'];	
	$session->secretary = $_POST['secretary'];	
	$session->presenter = $_POST['presenter'];	
	$session->room= $_POST['room'];	
	$session->capacity= $_POST['capacity'];	

	session_update($session);
	//echo "POST:\n"; print_r($_POST);
	//echo "\n\nSession: \n"; print_r($session);
	die("1");
}
else if ($_SERVER['REQUEST_METHOD'] != "GET" || !isset($_GET['i'])) { die("Improper use of this page!"); }

$allSessions = getAllSessions();
$session = $allSessions[$_GET['i']];
?>

<div class="modal-header">
	<h3 class="modal-title">Edit session</h3>
</div>
<div class="modal-body">
	<form role="form" id="editSession" method="POST" action="adminSessionUpdate.php">
	<div class="form-group">
		<label for="_name">Name: </label>
		<input type="text" name="_name" id="_name" class="form-control" value="<?php echo $session->name?>">
	</div>
	<div class="form-group">
		<label for="description">Description: </label>
		<textarea class="form-control" name="description" id="description" rows="5" ><?php echo $session->desc?></textarea>
	</div>
	<div class="form-group">
		<label for="cost">Cost: </label>
		<input class="form-control" type="text" name="cost" id="cost" value="<?php echo $session->cost?>">
	</div>
	<div class="form-group">
		<label for="pathToForm">Path to PDF of form: </label>
		<input class="form-control" type="text" name="pathToForm" id="pathToForm" value="<?php echo $session->forms?>">
	</div>
	<div class="form-group">
		<label for="block">Schedule Block (if double/triple, list the first block): </label>
		<div class="btn-group" data-toggle="buttons">
<?php
	for ($x=0;$x<$numSessions;$x++) {
?>
		<label class="btn btn-default<?php echo $session->block== ($x+1) ? " active":"" ?>"><input type="radio" name="block" value="<?php echo ($x+1)?>" <?php echo $session->block==($x+1)?"checked=\"checked\"":""?>/><?php echo ($x+1)?></label> 
<?php	
	}
?>
        </div>	
	</div>
	<div class="form-group">
		<label for="length">Length: </label>
		<div class="btn-group" data-toggle="buttons">
<?php
/*	    <label class="btn btn-default<?=$session->linked==""? " active":"" ?>"><input type="radio" name="length" value="single" <?=$session->linked==""?"checked=\"checked\"":""?>>Single Session</label>
            <label class="btn btn-default<?=($session->linked=="1,2" || $session->linked=="2,3") ? " active":"" ?>"><input type="radio" name="length" value="double" <?=($session->linked=="1,2" || $session->linked=="2,3")?"checked=\"checked\"":""?>>Double Session</label>
            <label class="btn btn-default<?=$session->linked=="1,2,3" ? " active":"" ?>"><input type="radio" name="length" value="triple" <?=$session->linked=="1,2,3"?"checked=\"checked\"":""?>>Triple Session</label>
*/
	$sessionLength = sizeof(explode(",",$session->linked));
	if ($sessionLength == 0) $sessionLength = 1;
	
	$names = array("single","double","triple","quad","quint");
	for ($x=0;$x<$numSessions;$x++) {
?>
	    <label class="btn btn-default<?php echo $sessionLength== ($x+1) ? " active":"" ?>">
		<input type="radio" name="length" value="<?php echo $names[$x]?>" <?php if ($sessionLength == ($x+1)) echo "checked='checked'";?>>
			<?php echo ucwords($names[$x])?> Session</label>
<?php
	}
?>
        </div>	
	</div>	

	<div class="form-group">
		<label for="Supervisor">Supervisor: </label>
		<input class="form-control" type="text" name="supervisor" id="supervisor" value="<?php echo $session->supervisor?>">
	</div>
	<div class="form-group">	
		<label for="secretary">Secretary: </label>
		<input class="form-control" type="text" name="secretary" id="secretary" value="<?php echo $session->secretary?>">
	</div>
	<div class="form-group">
		<label for="presenter">Presenter: </label>
		<input class="form-control" type="text" name="presenter" id="presenter" value="<?php echo $session->presenter?>">
	</div>
	<div class="form-group">
		<label for="room">Room: </label>
		<input class="form-control" type="text" name="room" id="room" value="<?php echo $session->room?>">
	</div>
	<div class="form-group">
		<label for="capacity">Max # students: </label>
		<input class="form-control" type="text" name="capacity" id="capacity" value="<?php echo $session->capacity?>">
	</div>
	<input type="hidden" name="id" value="<?php echo $session->id?>">
</div>
<div class="modal-footer">
	<input type="button" class="btn btn-success" value="UPDATE Session" onclick="adminUpdateSessionSubmit();">
	<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
	</form>
</div>
