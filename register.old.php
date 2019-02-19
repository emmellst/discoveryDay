<?
require_once("database.php");
require_once("functions.php");
require_once("Session.php");
require_once("Student.php");

//ERROR CODES
// 0 - FULL
// -1 - NO AUTH
// -2 - REG TURNED OFF
// -3 - PREVIOUS SESSION HAS BEEN PAID, SO CAN'T DROP
// -4 - REGd OK, BUT UNPAID SESSION PRESENT

auth_setup();
if (!auth_check()) die("-1");
if (!canReg()) die("-2");

if ($_SERVER['REQUEST_METHOD'] != "POST" || !isset($_POST['session'])) die("Improper use!");

$sessionToReg = $_POST['session'];
$id = $_SESSION['id'];

global $pdo,$studentTable,$sessionTable;
$allStudents = getAllStudents();
$allSessions = getAllSessions();
$student = $allStudents[$id];
$session = $allSessions[$sessionToReg];

if ($session->capacity - $session->buffer - $session->filled <= 0) die("0");

//De-register if already registered in sessions

//Which blocks to replace? Must check if they've been paid for and thus can't register
$blocksToDrop= array();
if ($session->linked == "") array_push($blocksToDrop,$session->block);
else foreach(explode(",",$session->linked) as $block) array_push($blocksToDrop,$block);

//Are there any sessions that overlapped oddly that need addressing?
$oldLinkedBlocks = array();
foreach ($blocksToDrop as $block) {
	switch ($block) {
	case 1: 
		if ($allSessions[$student->session1]->linked != "") {					//if session's linked blocks aren't already accounted for
			foreach (explode(",",$allSessions[$student->session1]->linked) as $oldLinked)		// record it, so we can adjust later
				if (!in_array($oldLinked,$blocksToDrop))
					array_push($oldLinkedBlocks,$oldLinked);
		}
		break;
	case 2: 
		if ($allSessions[$student->session2]->linked != "") {					//if session's linked blocks aren't already accounted for
			foreach (explode(",",$allSessions[$student->session2]->linked) as $oldLinked)		// record it, so we can adjust later
				if (!in_array($oldLinked,$blocksToDrop))
					array_push($oldLinkedBlocks,$oldLinked);
		}
		break;
	case 3: 
		if ($allSessions[$student->session3]->linked != "") {					//if session's linked blocks aren't already accounted for
			foreach (explode(",",$allSessions[$student->session3]->linked) as $oldLinked)		// record it, so we can adjust later
				if (!in_array($oldLinked,$blocksToDrop))
					array_push($oldLinkedBlocks,$oldLinked);
		}
		break;
	}
}

$canDropSessions = true;
foreach ($blocksToDrop as $block)
	switch ($block) {
		case 1: if ($student->paid1 == 1) $canDropSessions = false; break;
		case 2: if ($student->paid2 == 1) $canDropSessions = false; break;
		case 3: if ($student->paid3 == 1) $canDropSessions = false; break;
	}

if (!$canDropSessions) die("-3");

//Ok - allowed to de reg, now to figure out which session(s) need deregistering. 
//  Double session overwriting two singles, etc

$sessionsToUnreg = array();
foreach ($blocksToDrop as $block) {
	switch ($block) {
	case "1": 
		if (!in_array($allSessions[$student->session1],$sessionsToUnreg))
			array_push($sessionsToUnreg,$student->session1);
		break;
	case "2": 
		if (!in_array($allSessions[$student->session2],$sessionsToUnreg))
			array_push($sessionsToUnreg,$student->session2);
		break;
	case "3": 
		if (!in_array($allSessions[$student->session3],$sessionsToUnreg))
			array_push($sessionsToUnreg,$student->session3);
		break;
	}
}
/*
 * ACTUALLY DECREMENT "FILLED" in the sessions being dropped
 */
foreach ($sessionsToUnreg as $dropMe) {
	if ($dropMe == "0") continue;
	if ($allSessions[$dropMe]->block == "1" && $allSessions[$dropMe]->cost > 0 && $student->paid1 == 0) continue;
	if ($allSessions[$dropMe]->block == "2" && $allSessions[$dropMe]->cost > 0 && $student->paid2 == 0) continue;
	if ($allSessions[$dropMe]->block == "3" && $allSessions[$dropMe]->cost > 0 && $student->paid3 == 0) continue;

	if ($query = $pdo->prepare("UPDATE $sessionTable SET `filled`=:newFilled WHERE `id`=:id")) {
		$queryArray = array(
			"newFilled" => $allSessions[$dropMe]->filled - 1,
			"id" => $dropMe
		);
		$query->execute($queryArray);
	}
	else {
		$error = $query->errorInfo();
		throw new Exception("MySQL Error in register.php: ".$error[2]);
	}
}


//$session->linked or $oldLinkedBlocks

$newSessions = array();
if ($session->linked == "") {
	$newSessions[$session->block] = $session->id;
} else {
	foreach (explode(",",$session->linked) as $x)
		$newSessions[$x] = $session->id;
}
$anyEmptySessions = false;
foreach ($oldLinkedBlocks as $x) {
	$newSessions[$x] = 0;
	$anyEmptySessions = true;
}
if ($anyEmptySessions) markUnRegistered();

//In case nothing touched existing registrations...
if (!isset ($newSessions[1])) $newSessions[1] = $student->session1;
if (!isset ($newSessions[2])) $newSessions[2] = $student->session2;
if (!isset ($newSessions[3])) $newSessions[3] = $student->session3;

if ($session->cost == 0) {
	//Update SESSION INFO (increment "filled")
	if ($query = $pdo->prepare("UPDATE $sessionTable SET `filled`=:newFilled WHERE `id`=:id")) {
		$queryArray = array(
			"newFilled" => $session->filled+1,
			"id" => $session->id
		);
		$query->execute($queryArray);
	}
	else {
		$error = $query->errorInfo();
		throw new Exception("MySQL Error in register.php: ".$error[2]);
	}
}
//Register STUDENT INFO of new session

if ($query = $pdo->prepare("UPDATE $studentTable SET `session1`=:sessID1, `session2`=:sessID2, `session3`=:sessID3 WHERE `id`=:id")) {
	$queryArray = array(
		"sessID1" => $newSessions[1],
		"sessID2" => $newSessions[2],
		"sessID3" => $newSessions[3],
		"id" => $id
	);
	$query->execute($queryArray);
}
else {
	$error = $query->errorInfo();
	throw new Exception("MySQL Error in register.php: ".$error[2]);
}

if ($session->cost > 0) echo "-4";
else echo "1";
?>
