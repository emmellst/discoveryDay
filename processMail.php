<?php
require_once("functions.php");

global $pdo,$studentTable;
$subject = "Discovery Day Registration Update";
$sessions = getAllSessions();

if ($query=$pdo->prepare("SELECT mailqueue.sid, mailqueue.timeToPrint, 
	students.fname, students.lname, students.email, students.sessions, students.paid
	FROM mailqueue
	LEFT JOIN students
	ON mailqueue.sid = students.id
	")) {
	$query->execute();
	$count = 0;
	while ($result = $query->fetch(PDO::FETCH_ASSOC)) {
		if (time() >= $result['timeToPrint']) {
			//echo "TIMED OUT";

			//Check for forms that need to go out
			$forms = array();
			
			$sessionCount = 1;
			foreach((array) json_decode($result['sessions']) as $s) {
				if ($sessions[$s]->forms != "") 
					$forms[$sessionCount]=$sessions[$s]->forms;
				$sessionCount++;
			}
			
			//Send email
			$emailResult = sendEmail($result['fname']." ".$result['lname'],
				$result['email'],
				$subject,
				generateSchedule($result['sid']),$forms);
			sleep(3);
			//Remove from Queue

			if ($emailResult == true) deleteFromQueue($result['sid']);
			else print($emailResult);
			$count++;
			if ($count > 20) die();
		}
	}
}
else {
	$error = $query->errorInfo();
	throw new Exception('MySQL Error in mail queue processing(processMail.php): ' . $error[2]);
}

?>
