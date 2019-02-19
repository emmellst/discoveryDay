<?php
require_once("database.php");
require_once("functions.php");
require_once("Session.php");
require_once("Student.php");
global $settings;

$allSessions = getAllSessions();

function makeSessionSelect($idName,$block,$active=0) {
	global $allSessions;
	$output = "<select id=\"$idName\" name=\"$idName\" class=\"form-control\" onchange=\"alertOld($block);\">\n";
	$output .= "<option value='0'".($active == 0 ? "selected":"").">NOT CHOSEN</option>\n";

	foreach ($allSessions as $session) {
		if ($session->id == 0 || $session->active == 0) continue;
		else if ($session->linked == "" && $session->block != $block) continue;
		else if ($session->linked == "1,2" && $block == "3") continue;
		else if ($session->linked == "2,3" && $block == "1") continue;

		if ($session->id == $active)  $output .= "<option value='".$session->id."' selected>".$session->name." (".($session->capacity-$session->buffer-$session->filled)."/".$session->capacity.")</option>\n";
		else 			      $output .= "<option value='".$session->id."'>".$session->name." (".($session->capacity-$session->buffer-$session->filled)."/".$session->capacity.")</option>\n";
	}

	$output .= "</select>";
	return $output;
}
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['f']) && $_POST['f'] == "emailIndiv") {
	updateMailQueue($_POST['id'],-50000);
	return 1;	
}

else if ($_SERVER['REQUEST_METHOD'] == "POST") {
	if (!isset($_POST['id'])) die("Invalid use!");
/*
POST:
Array
(
    [id] => 1247
    [lname] => User
    [fname] => Admin
    [email] => stephen@emmell.org
    [hmrm] => 0
    [regd] => 1
    [active] => 1
    [sessions] => array(27,27,1)
    [paid] => array(1,0,0)
    [pp_pmts] => array(PAYPAL: 1VE1523393637981S,PAYPAL: 1VE1523393637981S,)
)
*/
	//print_r($_POST); die();
	$student = getAllStudents()[$_POST['id']];
	$student->lname = $_POST['lname'];
	$student->fname = $_POST['fname'];
	$student->email = $_POST['email'];
	$student->hmrm = $_POST['hmrm'];
	$student->regd = $_POST['regd'];
	$student->active = $_POST['active'];
	$student->sessions = $_POST['sessions'];
	$student->paid = $_POST['paid'];
	$student->pp_pmts = $_POST['pp_pmts'];
	student_update($student);

	//Decrement previous sessions (DECREASE 'FILLED' QUANTITY)
	$olds = $_POST['sessOld'];
	$sessToChange = array();
	foreach ($olds as $o) {
		$sessID = explode(",",$o)[0];
		if ($sessID == 0) continue;
		if (!in_array($sessID,$sessToChange))
			array_push($sessToChange,$sessID);
	}

	foreach ($sessToChange as $s) {
		session_dec($s);
	}

	//Increment new sessions (INCREASE 'FILLED' QUANTITY)
	$news = $_POST['sessions'];
	$sessToChange = array();
	foreach ($news as $n) {
		$sessID = explode(",",$n)[0];
		if ($sessID == 0) continue;
		if (!in_array($sessID,$sessToChange))
			array_push($sessToChange,$sessID);
	}

	foreach ($sessToChange as $s) {
		session_inc($s);
	}

	
	die("1");
}
else if ($_SERVER['REQUEST_METHOD'] != "GET" || !isset($_GET['i'])) { die("Improper use of this page!"); }

$allSessions = getAllSessions();
$student = getAllStudents()[$_GET['i']];
?>

<div class="modal-header">
	<div class="form-group col-md-6"><h3 class="modal-title">Edit Student</h3></div>
	<div class="form-group col-md-6">
		<span class="pull-right">
		<label for="active">Active: </label>
		<div class="btn-group" data-toggle="buttons">
			<label class="btn btn-default<?php if ($student->active =="1") echo " active"; ?>">
				<input type="radio" name="active" value="1" <?php if($student->active=="1") echo " checked=\"checked\"";?>>YES</label>
			<label class="btn btn-default<?php if ($student->active =="0") echo " active"; ?>">
				<input type="radio" name="active" value="0" <?php if($student->active=="0") echo " checked=\"checked\"";?>>NO</label>
		</div>
		</span>
	</div>

</div>
<div class="modal-body">
<fieldset>
	<form role="form" id="editStudent" method="POST" action="adminStudentUpdate.php">
	<div class="form-group col-md-6">
		<label class="control-label" for="lname">Last Name:</label>
		<input type="text" name="lname" id="lname" class="form-control input-md" value="<?php echo $student->lname?>">
	</div>
	<div class="form-group col-md-6">
		<label class="control-label" for="fname">First Name:</label>
		<input type="text" name="fname" id="fname" class="form-control input-md" value="<?php echo $student->fname; ?>">
	</div>

<!-- BREAK -->
	<div class="form-group col-md-6">
		<label for="hmrm">Homeroom: </label>
		<input class="form-control" type="text" name="hmrm" id="hmrm" value="<?php echo $student->hmrm?>">
	</div>
	<div class="form-group col-md-6">
		<label for="email">Email: </label>
		<input class="form-control" type="text" name="email" id="email" value="<?php echo $student->email?>">
	</div>

<!-- BREAK -->
	<!--
	<div class="form-group col-md-6">
		<label for="regd">Registered: </label>
		<div class="btn-group" data-toggle="buttons">
			<label class="btn btn-default<?php if ($student->regd=="1") echo " active"; ?>">
				<input type="radio" name="regd" value="1" <?php if($student->regd=="1") echo " checked=\"checked\"";?>>YES</label>
			<label class="btn btn-default<?php if ($student->regd=="0") echo " active"; ?>">
				<input type="radio" name="regd" value="0" <?php if($student->regd=="0") echo " checked=\"checked\"";?>>NO</label>
		</div>
			
	</div>
	<div class="form-group col-md-12">
		<label for="active">Active: </label>
		<div class="btn-group" data-toggle="buttons">
			<label class="btn btn-default<?php if ($student->active =="1") echo " active"; ?>">
				<input type="radio" name="active" value="1" <?php if($student->active=="1") echo " checked=\"checked\"";?>>YES</label>
			<label class="btn btn-default<?php if ($student->active =="0") echo " active"; ?>">
				<input type="radio" name="active" value="0" <?php if($student->active=="0") echo " checked=\"checked\"";?>>NO</label>
		</div>
	</div>
	-->
<?php
//REPEAT SESSION & PAID BLOCKS FOR EACH
for ($x=0;$x<$numSessions;$x++) {
?>
<!-- BREAK -->
	<div class="col-md-12" id="session<?php echo $x?>alert"></div>
	<div class="form-group col-md-7">
		<label for="session<?php echo ($x+1)?>">Session<?php ($x+1)?>: <small>(available / capacity)</small></label>
		<input type="hidden" id="sessOld<?php echo ($x)?>" name="sessOld<?php echo ($x)?>" value="<?=$student->sessions[$x]?>,<?=$allSessions[$student->sessions[$x]]->name?>"/>
		<?php echo makeSessionSelect("session$x",($x+1),$student->sessions[$x]);?>
	</div>
	<div class="form-group col-md-5">
		<label for="pp_pmts<?php echo ($x+1)?>">Paid<?php echo ($x+1)?>: </label>
		<div class="input-group">
			<span class="input-group-addon"><input name="paid1" id="paid1" type="checkbox" <?php if ($student->paid[$x] == "1") echo "checked";?>></span>
			<input id="pp_pmts<?php echo ($x+1)?>" name="pp_pmts<?php echo $x?>" class="form-control" type="text" placeholder="Pmt info" value="<?php echo $student->pp_pmts[$x]?>">
		</div>
	</div>
<?php
}
?>
 <input type="hidden" name="id" value="<?php echo $student->id?>">
</fieldset>
<div class="modal-footer">
	<button class="btn btn-default pull-left" onclick="adminAddToEmailQueue(<?php echo $student->id?>);">Email Schedule</button>
	<input type="button" class="btn btn-success" value="UPDATE Student" onclick="adminUpdateStudentSubmit();">
	<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
	</form>
</div>
