<?php
include_once("database.php");
require_once("Student.php");
require_once("Session.php");
require_once("PHPMailer/PHPMailerAutoload.php");
require_once("mail-credentials.php");
$sessionTable = "sessions";
$studentTable = "students";
$logTable = "log";
$TIMEOUT_TIME = 1800;
$numSessions = 2;
$fullPrefix = "http://dev.emmell.org/discovery/";

if (isset($_POST['func']) && $_POST['func'] == "auth") {
	$result = auth_login($_POST['user'],$_POST['pass']);
	if ($result != "1") return $result;
}

//-------STATS FUNCTIONS--------


function countStudents() {
	global $pdo,$studentTable;
	$query=$pdo->prepare("SELECT COUNT(*) FROM $studentTable");
	$query->execute();
	return $query->fetch(PDO::FETCH_ASSOC)['COUNT(*)'];
}

//All sessions picked
function countRegisteredStudents() {
	global $pdo,$studentTable;
	if ($query=$pdo->prepare("SELECT `sessions` FROM $studentTable")) {
		$query->execute();
		$count = 0;
		while ($s = $query->fetch(PDO::FETCH_ASSOC)) {
			$foundZero = false;
			foreach((array)json_decode($s['sessions']) as $num) {
				$num = (int) $num;
				if ($num == 0)
					$foundZero = true;
			}
			if (!$foundZero) $count++;
		}
		return $count;

	}
        else {
                $error = $query->errorInfo();
                throw new Exception('MySQL Error in countFinalizedStudents(functions.php): ' . $error[2]);
        }
}

//At least one session picked
function countPartialRegStudents() {
	global $pdo,$studentTable;
	if ($query=$pdo->prepare("SELECT `sessions` FROM $studentTable")) {
		$query->execute();
		$count = 0;
		while ($s = $query->fetch(PDO::FETCH_ASSOC)) {
			$foundNonZero = false;
			foreach((array)json_decode($s['sessions']) as $num) {
				$num = (int)$num;
				if ($num != 0)
					$foundNonZero = true;
			}
			if ($foundNonZero) $count++;
		}
		return $count;

	}
        else {
                $error = $query->errorInfo();
                throw new Exception('MySQL Error in countFinalizedStudents(functions.php): ' . $error[2]);
        }

}

//BIG GETTERS
function getAllSessions() {
	global $pdo, $sessionTable;
	$allSessions = array();
	if ($query=$pdo->prepare("SELECT * FROM $sessionTable ORDER BY `name`")) {
		$query->execute();
		while ($result = $query->fetch(PDO::FETCH_ASSOC)) {
			$session = new Session();
			$session->populateFromSQL($result);
			//array_push($allSessions,$session);
			$allSessions[$session->id] = $session;
		}
		return $allSessions;
	}
        else {
                $error = $query->errorInfo();
                throw new Exception('MySQL Error in getAllSessions(functions.php): ' . $error[2]);
	}
}

function getAllStudents() {
	global $pdo, $sessiontable, $studentTable;
	$allStudents  = array();
        if($query=$pdo->prepare("SELECT * FROM $studentTable ORDER BY lname,fname"))
        {
                $queryArray = array();
                $query->execute($queryArray);
		while ($result = $query->fetch(PDO::FETCH_ASSOC)) {
			$student = new Student();
			$student->populateFromSQL($result);
			$allStudents[$student->id] = $student;
		}
		return $allStudents;	
        }
        else {
                $error = $query->errorInfo();
                throw new Exception('MySQL Error in getAllStudents(functions.php): ' . $error[2]);
        }
}

function getStudentsInSession($sessionID) {
	global $pdo,$sessionsTable,$studentsTable;
	if($query=$pdo->prepare("SELECT students.id,sessions.name,students.lname,students.fname
		FROM students
		LEFT JOIN sessions 
		ON sessions.id=students.session1 OR sessions.id=students.session2 OR sessions.id=students.session3
		WHERE sessions.id=:id ORDER BY sessions.name, students.lname, students.fname")) {
			$queryArray = array("id"=>$sessionID);
			$query->execute($queryArray);
			$result = $query->fetchAll();
			return $result;
	}
        else {
                $error = $query->errorInfo();
                throw new Exception('MySQL Error in getStudentsInSession(functions.php): ' . $error[2]);
        }
}

//before dynamic session number
function getStudentsAllSessionsOLD() {
	$allSessions = getAllSessions();
	$returnArray = array();

	foreach ($allSessions as $sess) {
		if ($sess->id == 0) continue;
		$returnArray[$sess->id] = getStudentsInSession($sess->id);
	}
	return $returnArray;
}

function getStudentsAllSessions() {
	global $pdo;
	$allSessions = getAllSessions();
	$allStudents = getAllStudents();

	$toReturn = array();

	foreach($allSessions as $s) {
		$id = $s->id;
		$toReturn[$id] = array();
		foreach($allStudents as $stud) {
			if (in_array($id,$stud->sessions)) array_push($toReturn[$id],$stud->id);
		}
	}
	return $toReturn;
}


//SESSIONS
function session_add($_newSession) {
	global $pdo, $sessionTable;

	if ($query = $pdo->prepare("INSERT INTO `sessions`(`name`, `description`, `cost`, `forms`, `block`, `linked`, `supervisor`, 
					     `secretary`, `presenter`, `room`, `capacity`, `buffer`, `filled`, `active`) 
				     VALUES (:name,:description,:cost,:forms,:block,:linked,:supervisor,:secretary,
						     :presenter,:room,:capacity,:buffer,:filled,:active)")) {
		$queryArray = array(
			"name"		=>$_newSession->name,		
			"description"	=>$_newSession->desc,		
			"cost"		=>$_newSession->cost,		
			"forms"		=>$_newSession->forms,		
			"block"		=>$_newSession->block,		
			"linked"	=>$_newSession->linked,		
			"supervisor"	=>$_newSession->supervisor,		
			"secretary"	=>$_newSession->secretary,
			"presenter"	=>$_newSession->presenter,		
			"room"		=>$_newSession->room,		
			"capacity"	=>$_newSession->capacity,		
			"buffer"	=>$_newSession->buffer,		
			"filled"	=>$_newSession->filled,		
			"active"	=>$_newSession->active
		);
		$query->execute($queryArray);
	}
        else {
                $error = $query->errorInfo();
                throw new Exception('MySQL Error in session_add(functions.php): ' . $error[2]);
        }

}

function session_update($_newSession) {
	global $pdo, $sessionTable;

	if ($query = $pdo->prepare("UPDATE `sessions` SET `name`=:name, `description`=:description, `cost`=:cost, `forms`=:forms, `block`=:block, `linked`=:linked, 
					`supervisor`=:supervisor, `secretary`=:secretary, `presenter`=:presenter, `room`=:room, `capacity`=:capacity, 
					`buffer`=:buffer, `filled`=:filled, `active`=:active WHERE `id`=:id")) {
		$queryArray = array(
			"id"		=>$_newSession->id,
			"name"		=>$_newSession->name,		
			"description"	=>$_newSession->desc,		
			"cost"		=>$_newSession->cost,		
			"forms"		=>$_newSession->forms,		
			"block"		=>$_newSession->block,		
			"linked"	=>$_newSession->linked,		
			"supervisor"	=>$_newSession->supervisor,		
			"secretary"	=>$_newSession->secretary,
			"presenter"	=>$_newSession->presenter,		
			"room"		=>$_newSession->room,		
			"capacity"	=>$_newSession->capacity,		
			"buffer"	=>$_newSession->buffer,		
			"filled"	=>$_newSession->filled,		
			"active"	=>$_newSession->active
		);
		$query->execute($queryArray);
	}
        else {
                $error = $query->errorInfo();
                throw new Exception('MySQL Error in session_update(functions.php): ' . $error[2]);
        }

}

function session_inc($_sessionID) {
	global $pdo,$sessionTable;
	$session = getAllSessions()[$_sessionID];
        if ($query = $pdo->prepare("UPDATE $sessionTable SET `filled`=:newFilled WHERE `id`=:id")) {
                $queryArray = array(
                        "newFilled" => $session->filled+1,
                        "id" => $session->id
                );
                $query->execute($queryArray);
        }
        else {
                $error = $query->errorInfo();
                throw new Exception("MySQL Error in session_inc (functions.php): ".$error[2]);
        }
}

function session_dec($_sessionID) {
	global $pdo,$sessionTable;
	$session = getAllSessions()[$_sessionID];
        if ($query = $pdo->prepare("UPDATE $sessionTable SET `filled`=:newFilled WHERE `id`=:id")) {
                $queryArray = array(
                        "newFilled" => $session->filled-1,
                        "id" => $session->id
                );
                $query->execute($queryArray);
        }
        else {
                $error = $query->errorInfo();
                throw new Exception("MySQL Error in session_dec (functions.php): ".$error[2]);
        }
}

//STUDENTS

//THIS IS BROKEN!
function student_validateReg() {
	return 0;

	$allSessions = getAllSessions();
	$stud = getAllStudents()[$_SESSION['id']];

	$session1 = $allSessions[$stud->session1];
	$session2 = $allSessions[$stud->session2];
	$session3 = $allSessions[$stud->session3];
	
	$prob1 = $prob2 = $prob3 = false;

	if ($session1->cost > 0 && $stud->paid1 == 0 && $session1->filled >= ($session1->capacity - $session1->buffer))
		$prob1 = true;

	if ($session2->cost > 0 && $stud->paid2 == 0 && $session2->filled >= ($session2->capacity - $session2->buffer))
		$prob2 = true;

	if ($session3->cost > 0 && $stud->paid3 == 0 && $session3->filled >= ($session3->capacity - $session3->buffer))
		$prob3 = true;

	if ($prob1 || $prob2 || $prob3)
		student_changeReg( $_SESSION['id'],  $prob1 ? 0:$session1->id , $prob2 ? 0:$session2->id , $prob3 ? 0:$session3->id);
}

//THIS IS BROKEN!
function student_changeReg($id, $new1,$new2,$new3) {
	return 0;

	$student = getAllStudents()[$id];
	$allSessions = getAllSessions();

	//Decrement previous sessions
        $oldSess1 = $student->session1;
        $oldSess2 = $student->session2;
        $oldSess3 = $student->session3;

        $sessToDec = array();
        if (!in_array($oldSess1,$sessToDec)) array_push($sessToDec,$oldSess1);
        if (!in_array($oldSess2,$sessToDec)) array_push($sessToDec,$oldSess2);
        if (!in_array($oldSess3,$sessToDec)) array_push($sessToDec,$oldSess3);

        foreach ($sessToDec as $x) if ($allSessions[$x]->cost == 0) session_dec($x);

        //Increment new sessions
        $sessToInc = array();
        if (!in_array($new1,$sessToInc)) array_push($sessToInc,$new1);
        if (!in_array($new2,$sessToInc)) array_push($sessToInc,$new2);
        if (!in_array($new3,$sessToInc)) array_push($sessToInc,$new3);

        foreach ($sessToInc as $x) if ($x != 0) session_inc($x);
		
	//ACTUALLY UPDATE THE STUDENT
	$student->session1 = $new1;	
	$student->session2 = $new2;	
	$student->session3 = $new3;

	student_update($student);
}

function student_update($_newStudent) {
	global $pdo, $studentTable;

	if ($query = $pdo->prepare("UPDATE $studentTable SET 
						`fname`=:fname,
						`lname`=:lname,
						`snum` =:snum,
						`hmrm`=:hmrm,
						`email`=:email,
						`regd`=:regd,
						`paid`=:paid,
						`regcode` =:regcode,
						`sessions`=:sessions,
						`active`=:active,
						`pp_pmts`=:pp_pmts,
						`timeremain`=:time 
						WHERE `id`=:id")) {
		$queryArray = array(
			"id"		=>$_newStudent->id,
			"fname"		=>$_newStudent->fname,
			"lname"		=>$_newStudent->lname,
			"snum"		=>$_newStudent->snum,
			"hmrm"		=>$_newStudent->hmrm,
			"email"		=>$_newStudent->email,
			"regd"		=>$_newStudent->regd,
			"paid"		=>json_encode($_newStudent->paid),
			"regcode"	=>$_newStudent->regcode,
			"sessions"	=>json_encode($_newStudent->sessions),
			"active"	=>$_newStudent->active,
			"pp_pmts"	=>json_encode($_newStudent->pp_pmts),
			"time"		=>$_newStudent->timeremain
		);
		$query->execute($queryArray);
	}
        else {
                $error = $query->errorInfo();
                throw new Exception('MySQL Error in student_update(functions.php): ' . $error[2]);
        }

}


function compareDisplaySessions($a,$b) {
	if ($a['name'] == $b['name']) return 0;
	return strcasecmp($a['name'],$b['name']);
}

function getSessionsDisplay() {
	$allSessions = getAllSessions();
	$toReturn = array();
	foreach ($allSessions as $session) {
		if ($session->active == 0) continue;
		$roomLeft = $session->capacity-$session->buffer-$session->filled;
		$toReturn[$session->id] = array("name"=>$session->name,"desc"=>$session->desc,"id"=>$session->id,"cost"=>$session->cost,"forms"=>$session->forms,
						"block"=>$session->block,"linked"=>$session->linked,"capacity"=>$session->capacity,"remainingSlots"=>$roomLeft);
	}
	uasort($toReturn,'compareDisplaySessions');	
	return $toReturn;
}
function getSessionsAccordionInfoOnly($block) {
	global $numSessions;
	if ($block == $numSessions+1) return finishMessage();
	$sessions = getSessionsDisplay();

	$output = "<h3>Session $block</h3>\n";
	foreach($sessions as $session) {
		//only display valid choices for this block	
		if ($session['id'] == 0) continue;
		if ($session['linked'] == "" && $session['block'] != $block) continue;

		
	//		if ($session['linked'] == "1,2" && $block  == 3) continue;
	//		if ($session['linked'] == "2,3" && $block  == 1) continue;
                $found = false;
                foreach(explode(",",$session['linked']) as $runningBlock) {
                        if ($runningBlock == $block) $found = true;
                }
                if ($session['block'] != $block && !$found) continue;
	
		$output=$output.'<div class="panel panel-default">'."\n";
		$output=$output.' <div class="panel-heading" data-toggle="collapse" data-parent="#accordion'.$block.'" data-target="#collapse'.$block."-".$session['id'].'" >'."\n";
		$output=$output.'  <h4 class="panel-title">'."\n";
		$output=$output.'	'.$session['name']."\n";
		$output=$output.'    </h4>'."\n";
		$output=$output.'  </div>'."\n";
		$output=$output.'  <div id="collapse'.$block."-".$session['id'].'" class="panel-collapse collapse">'."\n";
		$output=$output.'    <div class="panel-body">'.$session['desc']."<br/><br/>\n";
		$output=$output.'    <div class="text-center">'."\n";
		if ($session['cost'] != 0)
			$output=$output.'        <strong>Please note!<br>Cost is $'.$session['cost'].'&nbsp;</strong><br/>'."\n";
		$output=$output."There are a total of ".$session['capacity']." spots available\n<br/><br/>\n";
		$output=$output."</div></div>\n";
		$output=$output.'  </div>'."\n";
		$output=$output.'</div>'."\n";
	}
	return $output;
}

function getSessionsAccordion($block) {
	global $numSessions;
	if ($block == $numSessions+1) return finishMessage();
	session_start();
	$student = getAllStudents()[$_SESSION['id']];
      	$sessions = getSessionsDisplay();
	$output = "<h3>Available sessions for session $block<br/><small>Please select a session for more information or to register.</small></h3>";
	foreach($sessions as $session) {
		//only display valid choices for this block
		if ($session['id'] == 0) continue;
		if ($session['linked'] == "" && $session['block'] != $block) continue;
		
		//if ($session['linked'] == "1,2" && $block  == 3) continue;
		//if ($session['linked'] == "2,3" && $block  == 1) continue;
		$found = false;
		foreach(explode(",",$session['linked']) as $runningBlock) {
			if ($runningBlock == $block) $found = true;
		}
		if ($session['block'] != $block && !$found) continue;
		
		//$selected = false;
		//if (($block == "1" && $student->session1 == $session['id']) || 
		//    ($block == "2" && $student->session2 == $session['id']) || 
		//    ($block == "3" && $student->session3 == $session['id']))
		//    $selected = true;
		$selected = false;
		foreach($student->sessions as $s) {
			if ($session['id'] == $s && $session['block'] == $block) $selected = true;
		}
		if (!$selected && $session['remainingSlots'] <= 0) continue;

		$output=$output.'<div class="panel panel-default">'."\n";
		$output=$output.' <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" data-target="#collapse'.$session['id'].'" onclick="clearAlerts();">'."\n";
		$output=$output.'  <h4 class="panel-title">'."\n";
		$output=$output.'	'.$session['name']."\n";
		$output=$output.'    </h4>'."\n";
		$output=$output.'  </div>'."\n";
		$output=$output.'  <div id="collapse'.$session['id'].'" class="panel-collapse collapse';

	//IF ALREADY REGISTERED - open accordion at that session
		if ($selected) $output = $output." in";
		
		$output=$output.'">'."\n";
		$output=$output.'    <div class="panel-body">'.$session['desc']."<br/><br/>\n";
		$output=$output.'    <div class="text-right">'."\n";
		if ($session['cost'] != 0)
			$output=$output.'        <strong>Please note! This is not a free session. Cost is $'.$session['cost'].'&nbsp;&nbsp;</strong><br/>'."\n";
	
		if ($selected)
			$output=$output.'        '.($session['remainingSlots']>=0?$session['remainingSlots']:0).' spots free <button class="btn btn-default disabled");">You are registered for this session</button></div>'."\n";
	
		else if ($session['remainingSlots'] <= 0) 
			$output=$output.'        '.($session['remainingSlots']>=0?$session['remainingSlots']:0).' spots free <button class="btn btn-default disabled");">There is no room to register for this session</button></div>'."\n";
		
		else
			$output=$output.'        '.($session['remainingSlots']>=0?$session['remainingSlots']:0).' spots free <button class="btn btn-info" onclick="register('.$session['id'].');">Register for this session</button></div>'."\n";
		
		$output=$output."</div>\n";
		$output=$output.'  </div>'."\n";
		$output=$output.'</div>'."\n";
	}
	return $output;
}

function get_student_id($username) {
	global $pdo,$studentTable;
	if ($query=$pdo->prepare("SELECT id FROM $studentTable WHERE snum = :snum")) {
		$queryArray = array("snum"=>$username);
		$query->execute($queryArray);
		return $query->fetchAll();
	}
        else {
                $error = $query->errorInfo();
                throw new Exception('MySQL Error in get_student_id(functions.php): ' . $error[2]);
        }
}

// MONEY MATTERS
function get_student_paid($block) {
	$student = getAllStudents()[$_SESSION['id']];
	
	if ($block == 1)  { $session = getAllSessions()[$student->session1]; $paid = $student->paid1; }
	if ($block == 2)  { $session = getAllSessions()[$student->session2]; $paid = $student->paid2; }
	if ($block == 3)  { $session = getAllSessions()[$student->session3]; $paid = $student->paid3; }

	if ($session->cost > 0 && $paid == 0) return FALSE;
	return TRUE;
}

function get_student_paid_by_id($block,$id) {
	$student = getAllStudents()[$id];
	
	if ($block == 1)  { $session = getAllSessions()[$student->session1]; $paid = $student->paid1; }
	if ($block == 2)  { $session = getAllSessions()[$student->session2]; $paid = $student->paid2; }
	if ($block == 3)  { $session = getAllSessions()[$student->session3]; $paid = $student->paid3; }

	if ($session->cost > 0 && $paid == 0) return FALSE;
	return TRUE;
}


function getUnpaidSessions() {
	$student = getAllStudents()[$_SESSION['id']];
	$allSessions = getAllSessions();

	$unpaidSessions = array();
	//Block 1
	if ($student->paid1 == 0 && $allSessions[$student->session1]->cost > 0) 
		if(!in_array($allSessions[$student->session1], $unpaidSessions)) 
			$unpaidSessions[$student->session1] = $allSessions[$student->session1];

	//Block 2
	if ($student->paid2 == 0 && $allSessions[$student->session2]->cost > 0) 
		if(!in_array($allSessions[$student->session2], $unpaidSessions)) 
			$unpaidSessions[$student->session2] = $allSessions[$student->session2];	

	//Block 3
	if ($student->paid3 == 0 && $allSessions[$student->session3]->cost > 0) 
		if(!in_array($allSessions[$student->session3], $unpaidSessions)) 
			$unpaidSessions[$student->session3] = $allSessions[$student->session3];

	return $unpaidSessions;
}

function markPaid($session, $pp_pmt_id) {
	global $pdo,$studentTable;

	$applicableBlocks = array();
	if ($session->linked == "") $applicableBlocks[0] = $session->block;
	else {
		foreach(explode(",",$session->linked) as $linked) array_push($applicableBlocks,$linked);
	}
	
	$student = getAllStudents()[$_SESSION['id']];

	$result = false;
	foreach($applicableBlocks as $block) {
		if ($block == "1") $queryString = "UPDATE $studentTable SET `paid$block` = 1, `pp_pmt_id1`=:pp WHERE `id`=:id";	
		if ($block == "2") $queryString = "UPDATE $studentTable SET `paid$block` = 1, `pp_pmt_id2`=:pp WHERE `id`=:id";	
		if ($block == "3") $queryString = "UPDATE $studentTable SET `paid$block` = 1, `pp_pmt_id3`=:pp WHERE `id`=:id";	
		if ($query=$pdo->prepare($queryString)) {
			$queryArray = array(
				"id"=>$_SESSION['id'],
				"pp" => $pp_pmt_id
			);
			$query->execute($queryArray);
			$result = true;
		}
        else {
                $error = $query->errorInfo();
                throw new Exception('MySQL Error in markPaid(functions.php): ' . $error[2]);
        }
	}
	return $result;
}

// AUTH MATTERS
function auth_login($user,$pass) {
	global $pdo, $studentTable;
        if($query=$pdo->prepare("SELECT * FROM $studentTable WHERE `snum` = :snum AND `active`=1"))
        {
                $queryArray = array("snum"=>$user);
                $query->execute($queryArray);
		$results=$query->fetchAll();
		//echo "Comparing $pass - ".$results[0]['password'];
		if (!isset($results[0]['password'])) return false;
		return password_verify($pass,$results[0]['password']);
        }
        else {
                $error = $query->errorInfo();
                throw new Exception('MySQL Error in auth_login(functions.php): ' . $error[2]);
        }
}


function auth_check() {
	if (!isset($_SESSION['auth_user'])) return false;
	$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
	updateMailQueue($_SESSION['id']);
	return true;
}

function auth_setup() {

	//FORCE USE OF HTTPS
	if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off"){
	    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	    header('HTTP/1.1 301 Moved Permanently');
	    header('Location: ' . $redirect);
	    exit();
	}

	session_start();
	global $TIMEOUT_TIME;
	if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $TIMEOUT_TIME)) {
	    // last request was more than 30 minutes ago
	    session_unset();     // unset $_SESSION variable for the run-time 
	    session_destroy();   // destroy session data in storage
	}
}

function auth_logout() {
	session_unset();
	session_destroy();
}

//REGISTRATION STUFF

function enableRegistration($reg) {
	global $pdo,$sessionTable, $table_settings;

    if ($query = $pdo->prepare("UPDATE $table_settings SET `value`=:reg WHERE `name`=\"regEnabled\"")) {
            $queryArray = array(
                    "reg" => $reg
            );
            $query->execute($queryArray);

            $settings['regEnabled'] = $reg;
    }
    else {
            $error = $query->errorInfo();
            throw new Exception("MySQL Error in enableRegistration (functions.php): ".$error[2]);
    }

}

function canReg() {
	global $settings;
	if ($settings['regEnabled']) return true;
	return false;
}

function markRegistered() {
	global $pdo,$studentTable;

	if ($query=$pdo->prepare("UPDATE $studentTable SET `regd`=1 WHERE `id`=:sid")) {
		$queryArray = array(
			"sid" => $_SESSION['id']
		);
		$query->execute($queryArray);
		//echo "Done";
	}
	else {
		$error = $query->errorInfo();
		throw new Exception('MySQL Error in markRegistered(functions.php): ' . $error[2]);
	}
}

function markUnRegistered() {
	global $pdo,$studentTable;

	if ($query=$pdo->prepare("UPDATE $studentTable SET `regd`=0 WHERE `id`=:sid")) {
		$queryArray = array(
			"sid" => $_SESSION['id']
		);
		$query->execute($queryArray);
		//echo "Done";
	}
	else {
		$error = $query->errorInfo();
		throw new Exception('MySQL Error in markUnRegistered(functions.php): ' . $error[2]);
	}
}

function generateScheduleSimple($student,$sessions) {
	global $fullPrefix;
	
	$output = "<!DOCTYPE html>\n<head>\n";	
	$output .='<style type="text/css">
		table.schedule tr  { 	border: 1px solid black; }
		table.schedule tr.tabrow.tabrow td.col_width_1  { width: 30%; }
		table.schedule tr.tabrow.tabrow td.col_width_2  { width: 70%; }
		table.schedule  { 
			border-collapse:collapse;background-color: transparent;}
		table.schedule tr td{ 
			border: 1px solid black; text-align: left;font-style: normal;font-weight: normal;padding: 5px;background-color: transparent;}
		table.schedule tr th{ 
			text-align: right;font-style: normal;font-weight: normal;background-color:#d8d8d8; padding: 10px;}
		.notChosen{
			background-color: red;
		}
		</style>';

	$output .="</head>\n<body>\n";

	$output .= "<h2>Discovery Day Schedule</h2>\n";
	$output .= "<p>".$student->fname." ".$student->lname.", thank you for editing your online registration for Discovery Day.<br/><br/>We hope you are happy with your selections. If you change your mind, you can log in to change them.</p>\n";	
	$output .= "<p>You are now registered for the following sessions:<br/>\n";
	
	$output .="<table class='schedule'>\n";

	//$output .= "<h3>".$student->lname.", ".$student->fname."</h3>\n";
	
	$count = 1;
	$needsPayment = false;
	foreach($student->sessions as $s) {	
		$name = $sessions[$s];
		$output.="<tr";
		if ($s == 0) $output.= " class='notChosen'";
		$output .="><th>Session ".$count."</th><td>".$sessions[$s]->name."</td><td style='text-align: center;'> Room: ".$sessions[$s]->room;
		if ($sessions[$s]->forms != "") 
			$output.=" - <strong>Please see attached form</strong>";
		$output.="</td></tr>\n";
		$count++;

		if ($sessions[$s]->cost > 0) $needsPayment = true;
	}	
	
	$output.="</table>\n";

	if ($needsPayment) {
		$output.="<br/><strong>You have selected a session that requires payment.<br/>Please make sure you have gone to <a href='ocdsb.schoolcashonline.com'>School Cash Online</a> to make payment.<br/>YOUR REGISTRATION IS NOT FINAL UNTIL YOU HAVE PAID.</strong>";
	}
	$output.="</body></html>\n";
	return $output;
	

}


function generateSchedule($studentID) {
	global $fullPrefix;
	$student = getAllStudents()[$studentID];
	$sessions = getAllSessions();
	
	$output = "<!DOCTYPE html>\n<head>\n";	
	$output .='<style type="text/css">
		table.schedule tr  { 	border: 1px solid black; }
		table.schedule tr.tabrow.tabrow td.col_width_1  { width: 30%; }
		table.schedule tr.tabrow.tabrow td.col_width_2  { width: 70%; }
		table.schedule  { 
			border-collapse:collapse;background-color: transparent;}
		table.schedule tr td{ 
			border: 1px solid black; text-align: left;font-style: normal;font-weight: normal;padding: 5px;background-color: transparent;}
		table.schedule tr th{ 
			text-align: right;font-style: normal;font-weight: normal;background-color:#d8d8d8; padding: 10px;}
		.notChosen{
			background-color: red;
		}
		</style>';

	$output .="</head>\n<body>\n";

	$output .= "<h2>Discovery Day Schedule</h2>\n";
	$output .= "<p>".$student->fname." ".$student->lname.", thank you for editing your online registration for Discovery Day.<br/><br/>We hope you are happy with your selections. If you change your mind, you can log in to change them.</p>\n";	
	$output .= "<p>You are now registered for the following sessions:<br/>\n";
	
	$output .="<table class='schedule'>\n";

	//$output .= "<h3>".$student->lname.", ".$student->fname."</h3>\n";
	
	$count = 1;
	$needsPayment = false;
	foreach($student->sessions as $s) {	
		$name = $sessions[$s];
		$output.="<tr";
		if ($s == 0) $output.= " class='notChosen'";
		$output .="><th>Session ".$count."</th><td>".$sessions[$s]->name."</td><td style='text-align: center;'> Room: ".$sessions[$s]->room;
		if ($sessions[$s]->forms != "") 
			$output.=" - <strong>Please see attached form</strong>";
		$output.="</td></tr>\n";
		$count++;

		if ($sessions[$s]->cost > 0) $needsPayment = true;
	}	
	
	$output.="</table>\n";

	if ($needsPayment) {
		$output.="<br/><strong>You have selected a session that requires payment.<br/>Please make sure you have gone to <a href='ocdsb.schoolcashonline.com'>School Cash Online</a> to make payment.<br/>YOUR REGISTRATION IS NOT FINAL UNTIL YOU HAVE PAID.</strong>";
	}
	$output.="</body></html>\n";
	return $output;
	

}

// EMAIL CONTROLS
function generateEmail($studentID) {
	return generateSchedule($studentID);

	//Wholly deprecated?
	global $fullPrefix;
	$student = getAllStudents()[$studentID];
	$sessions = getAllSessions();
	
	$output = "<HTML><body><h2>Discovery Day Registration Update</h2>\n";
	$output .= "<p>".$student->fname." ".$student->lname.", thank you for completing your online registration for Discovery Day. We hope you are happy with your selections. If they are free, or you have yet to pay, you can still log in to change them.</p>\n";	
	$output .= "<p>You are now registered for the following sessions:<br/>\n";

	//SINGLES
	if ($sessions[$student->session1]->linked == "" &&
		$sessions[$student->session2]->linked == "" &&
		$sessions[$student->session3]->linked == "") {
		$type = "single";
		$output .="<ul>\n";
		$output .="<li>Session 1: ".$sessions[$student->session1]->name."</li>\n";
				if (!get_student_paid_by_id(1,$studentID)) $output.= "<ul><li><strong>You have not paid for this session, so your place has not been reserved.</strong></li></ul>\n";
		
		$output.= "<li>Session 2: ".$sessions[$student->session2]->name."</li>\n";
				if (!get_student_paid_by_id(2,$studentID)) $output.= "<ul><li><strong>You have not paid for this session, so your place has not been reserved.</strong></li></ul>\n";
	
		$output.= "<li>Session 3: ".$sessions[$student->session3]->name."</li>\n";
				if (!get_student_paid_by_id(3,$studentID)) $output.= "<ul><li><strong>You have not paid for this session, so your place has not been reserved.</strong></li></ul>\n";
		
		$output .="</ul>\n";
	}

	//DOUBLE 1-2
	else if ($sessions[$student->session1]->linked == "1,2") {
		$type = "double12";
		$output .="<ul>\n";
		$output .="<li>Sessions 1 &amp; 2: ".$sessions[$student->session1]->name."</li>\n";
				if (!get_student_paid_by_id(1,$studentID)) $output.= "<ul><li><strong>You have not paid for this session, so your place has not been reserved.</strong></li></ul>\n";
		
		$output .="<li>Session 3: ".$sessions[$student->session3]->name."</li>\n";
				if (!get_student_paid_by_id(3,$studentID)) $output.= "<ul><li><strong>You have not paid for this session, so your place has not been reserved.</strong></li></ul>\n";
		
		$output .="</ul>\n";
	}
	
	//DOUBLE 2-3
	else if ($sessions[$student->session2]->linked == "2,3") {
		$type = "double23";
		$output .="<ul>\n";
		
		$output .="<li>Session 1: ".$sessions[$student->session1]->name."</li>\n";
				if (!get_student_paid_by_id(1,$studentID)) $output.= "<ul><li><strong>You have not paid for this session, so your place has not been reserved.</strong></li></ul>\n";
		
		$output .="<li>Sessions 2 &amp; 3: ".$sessions[$student->session2]->name."</li>\n";
				if (!get_student_paid_by_id(2,$studentID)) $output.= "<ul><li><strong>You have not paid for this session, so your place has not been reserved.</strong></li></ul>\n";
		
		$output .="</ul>\n";
	}

	//TRIPLE
	else if ($sessions[$student->session1]->linked == "1,2,3") {
		$type = "triple";
		$output .="<ul>\n";
		$output .="<li>Sessions 1, 2 &amp; 3: ".$sessions[$student->session1]->name."</li>\n";
				if (!get_student_paid_by_id(1,$studentID)) $output.= "<ul><li><strong>You have not paid for this session, so your place has not been reserved.</strong></li></ul>\n";

		$output .="</ul>\n";
	}

	//CHECK FOR ATTACHMENTS

	$forms1 = $sessions[$student->session1]->forms;
	$forms2 = $sessions[$student->session2]->forms;
	$forms3 = $sessions[$student->session3]->forms;
	
	if ($forms1 != "" || $forms2 != "" || $forms3 != "") {
		$output.="<p>One of the sessions you have selected requires a Permission Form.  Attached to this email you will find a two-page document.</p>\n"; 
		$output.="<p>The first page contains important information about your session...read it carefully!<br/>\n"; 
		$output.="You can also download any of your forms here: ";
		if ($forms1 != "") $output.="<a href='".$fullPrefix.$forms1."'>Session1 Form</a> ";
		if ($forms2 != "") $output.="<a href='".$fullPrefix.$forms2."'>Session2 Form</a> ";
		if ($forms3 != "") $output.="<a href='".$fullPrefix.$forms3."'>Session3 Form</a> ";
		$output.="</p>\n";
		$output.="<p>The second page is your Permission Form...print it off, get it signed and return it to Room 105 by Monday, November 2nd.</p>\n";
	}
	$output.="<p>If you have any questions, please see Mrs. Langford in Room 105.</p>\n";
	$output.="<br/>Thank you!";
	
	$output.="<br/><br/><h2>Your schedule</h2>\n";

	$output.='<style type="text/css">
		table.schedule tr  { 	border: 1px solid black; }
		table.schedule tr.tabrow.tabrow td.col_width_1  { width: 30%; }
		table.schedule tr.tabrow.tabrow td.col_width_2  { width: 70%; }
		table.schedule  { 
			border-collapse:collapse;background-color: transparent;}
		table.schedule tr td{ 
			border: 1px solid black; text-align: left;font-style: normal;font-weight: normal;padding: 5px;background-color: transparent;}
		table.schedule tr th{ 
			text-align: right;font-style: normal;font-weight: normal;background-color:#d8d8d8; padding: 10px;}</style>';

	$output.="<table class='schedule'>\n";
	
	//CUSTOMIZE THE SCHEDULE, TRIPLES DO **NOT** HAVE FIRST SESSION IN THE 'KICK-OFF'						
	
	$output.="<tr><th>8:00 - 8:15</th><td colspan='2'>Period 2 Class</td></tr>\n";
	if ($type != "triple") {
		$output.="<tr><th>8:15 - 9:15</th><td colspan='2'>Discovery Day Kick-Off in Large Gym</td>\n";
		$output.="<tr><th>9:25 - 10:25</th><td><strong>Session1: </strong>".$sessions[$student->session1]->name."</td><td style='text-align: center;'>".$sessions[$student->session1]->room."</td></tr>\n";
		$output.="<tr><th>10:40 - 11:40</th><td><strong>Session2: </strong>".$sessions[$student->session2]->name."</td><td style='text-align: center;'>".$sessions[$student->session2]->room."</td></tr>\n";
		$output.="<tr><th>11:40 - 1:00</th><td colspan='2'>Lunch</td>\n";
		$output.="<tr><th>1:00 - 2:00</th><td><strong>Session3: </strong>".$sessions[$student->session3]->name."</td><td style='text-align:center;'>".$sessions[$student->session3]->room."</td></tr>\n";
	}
	else {
		$output.="<tr><th>8:15 - 2:15</th><td>".$sessions[$student->session1]->name."</td><td>".$sessions[$student->session1]->room."</td></tr>\n";
	}
	$output.="</table>\n";
	$output.="</body></html>\n";
	return $output;
	

}

function deleteFromQueue($studentID) {
	global $pdo;

	if ($query=$pdo->prepare("DELETE FROM `mailqueue` WHERE `sid`=:sid")) {
		$queryArray = array(
			"sid" => $studentID
		);
		$query->execute($queryArray);
	}
	else {
		$error = $query->errorInfo();
		throw new Exception('MySQL Error in deleteFromQueue(functions.php): ' . $error[2]);
	}
}

function updateMailQueue($studentID,$_TIMEOUT_TIME = -1) {
	global $pdo,$TIMEOUT_TIME;
	if ($_TIMEOUT_TIME == -1) $_TIMEOUT_TIME = $TIMEOUT_TIME;
	//global $TIMEOUT_TIME;
	//$TIMEOUT_TIME = 1*60;
	
	//if ($query=$pdo->prepare("UPDATE `mailqueue` SET `timeToPrint`=:time WHERE `sid` = :sid")) {
	if ($query=$pdo->prepare("INSERT INTO mailqueue (`sid`,`timeToPrint`)
		VALUES (:sid,:time)
		ON DUPLICATE KEY UPDATE 
		  `timeToPrint`=:time2")) {
	
		$queryArray = array(
			"time" => time() + $_TIMEOUT_TIME,
			"time2" => time() + $_TIMEOUT_TIME,
			"sid" => $studentID
		);
		$query->execute($queryArray);
	}
	else {
		$error = $query->errorInfo();
		throw new Exception('MySQL Error in updateMailQueue(functions.php): ' . $error[2]);
	}
}


function sendEmail($addrname,$addr,$subj,$body,$attached="") {
	global $SERVER,$PORT,$EMAIL_USER,$EMAIL_PWD;
	//$attached IS AN ARRAY!
		
	$debug = false;
	$debugName = "Stephen Emmell";
	$debugEmail = "stephen.emmell@ocdsb.ca";

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = 0;

		$mail->Debugoutput = 'html';

		$mail->Host = $SERVER;
		$mail->Port = $PORT;
		$mail->SMTPSecure = 'tls';
		$mail->SMTPAuth = true;
		$mail->Username = $EMAIL_USER;
		$mail->Password = $EMAIL_PWD;
		$mail->From = $mail->Username;
		$mail->FromName = "WCSS Discovery Day";
		$mail->AddReplyTo('tracie.langford@ocdsb.ca',"Ms. Langford");
		
		if ($debug) {
			$addr = $debugEmail;
			$addrname = $debugName;
			$subj = "Sent to $addrname <$addr>: $subj";
		}
		$mail->addAddress($addr,$addrname);
		$mail->Subject  = $subj;
		$mail->msgHTML($body);

		if ($attached != "") {
			foreach ($attached as $form)
				$mail->addAttachment($form);
		}
		
		$mail->send();
		return true;
	} catch (phpmailerException $e) {
		addTolog("EMAIL","EMAIL FAIL: ".$e->errorMessage());
		return false;
	}
}

// MISC
function timedOut() {
	session_start();
	global $TIMEOUT_TIME;
	if ((time() - $_SESSION['LAST_ACTIVITY']) < $TIMEOUT_TIME) echo "FALSE";
	else echo "TRUE";
}

function finishmessage() {
	$output = "<div class='text-center'><h3>It looks like you might be finished!</h3>\n".
		"<p>You can continue to make changes to your registration,<br/>or you can come back later to make more changes.</p>\n".
		"<h3>REMEMBER: If you have registered for a paid session and have not yet paid <strong>YOU HAVE NOT RESERVED A PLACE</strong><br/>\n".
		"You must continue to <a href='https://ocdsb.schoolcashonline.com/'>School Cash Online</a> to make your payment.</h3>\n";
	$output .= "<br/><button type='button' class='btn btn-success btn-block' onclick='finished();'>Finished for now (logout)</button></div>";
	return $output;
}

// LOGGING
function addTolog($category,$message) {
	global $pdo,$logTable;
	$date = new DateTime();
	$date = date_format($date, 'Y-m-d H:i:s');
	$message = $date." -- ".$message;

	if ($query=$pdo->prepare("INSERT INTO $logTable (`category`,`message`) VALUES (:cat,:message)")) {
		$queryArray = array(
			"cat" => $category,
			"message" => $message
		);
		$query->execute($queryArray);
	} else {
		$error = $query->errorInfo();
		throw new Exception('MySQL Error in log(functions.php): ' . $error[2]);
	}
}

