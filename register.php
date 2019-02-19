<?php
require_once("database.php");
require_once("functions.php");
require_once("Session.php");
require_once("Student.php");

//RETURN CODES
abstract class RESULT {
	const SUCCESS 		= 1;
	const SESSION_FULL 	= 0;
	const NO_AUTH 		= -1;
	const NO_REG 		= -2;
	const PAID_ERROR	= -3;
	const UNPAID_SESSION	= -4;
}

// TO USE:    die(RESULT::SUCCESS);
auth_setup();
if (!auth_check()) { echo RESULT::NO_AUTH; die(); }
if (!canReg()) {  echo RESULT::NO_REG; die(); }

if ($_SERVER['REQUEST_METHOD'] != "POST" || !isset($_POST['session'])) die("Improper use!");

$sessionToReg = $_POST['session'];
$userid = $_SESSION['id'];

global $pdo,$studentTable,$sessionTable;
$allStudents = getAllStudents();
$allSessions = getAllSessions();
$student = $allStudents[$userid];
$session = $allSessions[$sessionToReg];

if ($session->capacity - $session->buffer - $session->filled <= 0) { echo RESULT::SESSION_FULL; die(); }

$currentSessions = $student->sessions;

//FIGURE OUT WHICH BLOCKS WE ARE TRYING TO REGISTER
$blocks2change= array();
if ($session->linked == "") array_push($blocks2change,$session->block);
else {
	foreach(explode(",",$session->linked) as $b) {
		array_push($blocks2change,$b);
	}
}


//CHECK FOR DUPLICATE NAMED SESSIONS THAT ARE ALREADY REGISTERED THEM AND ADD THEM TO THE LIST OF BLOCKS TO BE DEREGISTERED FROM
foreach($student->sessions as $s) {
	if ($allSessions[$s]->name == $session->name)
		array_push($blocks2change,$allSessions[$s]->block);
}

//ARE WE TRYING TO REGISTER INTO ANOTHER JOINT SESSION THAT MIGHT CAUSE A PROBLEM?
//GO THROUGH EACH BLOCK I'M TRYING TO CHANGE AND CHECK IF IT'S PART OF A JOINT SESSION THAT'S OUTSIDE OF WHAT WE ARE TRYING TO CHANGE -> WHICH IS THE PROBLEM
foreach($blocks2change as $b) {
	$sessionInQuestion = $allSessions[$student->sessions[$b-1]];
	if ($sessionInQuestion->linked == "") continue;
	
	foreach(explode(",",$sessionInQuestion->linked) as $l) {
		//if not in array already, add to the array to be dropped
		if (!in_array($l,$blocks2change))
			array_push($blocks2change,$l);
	}
}

//BLOCKS2CHANGE NOW CONTAINS ALL THE BLOCKS THAT NEED DE-REGISTERING SO WE CAN REGISTER THIS NEW SESSION

//**********************************
// NEED TO CHECK PMT INFO
//**********************************

//need to run "session_dec" for all unique sessions being deregistered (DECREASE NUMBER IN "FILLED" FIELD)
$sessions2dec = array();
foreach($blocks2change as $b) {
	$session = $allSessions[$student->sessions[$b-1]];
	if ($session->id == 0) continue;

	if (!in_array($session->id,$sessions2dec)) 
		array_push($sessions2dec,$session->id);
}

foreach($sessions2dec as $s) {
	session_dec($s);
}

//RUN THE INCREMENT
//need to run "session_inc" for all unique sessions being registered (INCREASE NUMBER IN "FILLED" FILED)
session_inc($sessionToReg);

//SET UP NEW SESSIONS FOR STUDENT AND UPDATE
//ITERATE THROUGH AND DROP TO 0 ANY THAT WE DE-REG'D FROM
//echo "SESSION: "; print_r($allSessions[$sessionToReg]);
//echo "BLOCK2CHANGE: ".implode(",",$blocks2change)."\n";
//echo "SESSIONS BEFORE: ".implode(",",$currentSessions)."\n";

for ($i=0;$i<sizeof($currentSessions);$i++) {
	$session = $allSessions[$currentSessions[$i]];
	if (in_array($session->block,$blocks2change)) $currentSessions[$i] = 0;
}

//ITERATE THROUGH AND ADD SESSION TO APPROPRIATE BLOCKS
//echo "SESSIONS AFTER DROP: ".implode(",",$currentSessions)."\n";

$session = $allSessions[$sessionToReg];
$linkedBlocks = explode(",",$session->linked);
for ($i=0;$i<sizeof($currentSessions);$i++) {
	if (($i+1) == $session->block)
		$currentSessions[$i] = $session->id;
	foreach($linkedBlocks as $l)
		if (($i+1) == $l)
			$currentSessions[$i] = $session->id;
}
//echo "SESSIONS AFTER ADD: ".implode(",",$currentSessions)."\n";

$student->sessions = $currentSessions;
student_update($student);

echo RESULT::SUCCESS;
?>
