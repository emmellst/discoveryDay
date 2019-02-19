<?php
require_once("functions.php");
auth_setup();

$allSessions = getAlLSessions();
$student = getAllStudents()[$_SESSION['id']];
$sessions = array($student->session1,$student->session2,$student->session3);

$isTriple = ($allSessions[$sessions[0]]->linked=="1,2,3") ? TRUE:FALSE;
$isDouble12 = ($allSessions[$sessions[0]]->linked=="1,2") ? TRUE:FALSE;
$isDouble23 = ($allSessions[$sessions[1]]->linked=="2,3") ? TRUE:FALSE;

$paid = true;
$emptySessions = false;

echo "<h4>Payment Summary</h4>\n";
echo "<div class='well'>\n";

//Handle Triples
if ($isTriple) {
	$emptySessions = false;
	if (!get_student_paid(1)) {
		$paid = false;
	}
	$isFree = ($allSessions[$sessions[0]]->cost == 0) ? TRUE : FALSE;
	echo '<li class="list-group-item">Triple Session (1-2-3): ';
	if (!$isFree) {
		echo ($paid ? "PAID":"UNPAID <strong>$".$allSessions[$sessions[0]]->cost).'</strong></li>'."\n"; 
	}
	else {
		echo "FREE</li>\n";
	}
}

//Handle 1-2 Doubles
// ONE SPECIAL LIST ITEM ABOUT 1/2
// ONE NORMAL LIST ITEM ABOUT 3
else if ($isDouble12) {
	if ($sessions[2] == 0) $emptySessions = true;
	if (!get_student_paid(1) || !get_student_paid(3)) {
		$paid = false;
	}
	$isDoubleFree = ($allSessions[$sessions[0]]->cost == 0) ? TRUE : FALSE;
	$isSingleFree = ($allSessions[$sessions[2]]->cost == 0) ? TRUE : FALSE;
	echo '<li class="list-group-item">Double Session (1-2): ';
	if (!$isDoubleFree) {
		echo (get_student_paid(1) ? "PAID":"UNPAID <strong>$".$allSessions[$sessions[0]]->cost).'</strong></li>'."\n"; 
	}
	else {
		echo "FREE</li>\n";
	}
	echo '<li class="list-group-item">Session 3: ';
	if (!$isSingleFree) {
		echo (get_student_paid(3) ? "PAID":"UNPAID <strong>$".$allSessions[$sessions[2]]->cost).'</strong></li>'."\n"; 
	}
	else {
		echo "FREE</li>\n";
	}
}


//Handle 2-3 Doubles
// ONE NORMAL LIST ITEM ABOUT 3
// ONE SPECIAL LIST ITEM ABOUT 2/3
else if ($isDouble23) {
	if ($sessions[0] == 0) $emptySessions = true;
	if (!get_student_paid(1) || !get_student_paid(2)) {
		$paid = false;
	}
	$isSingleFree = ($allSessions[$sessions[0]]->cost == 0) ? TRUE : FALSE;
	$isDoubleFree = ($allSessions[$sessions[1]]->cost == 0) ? TRUE : FALSE;
	echo '<li class="list-group-item">Session 1: ';
	if (!$isSingleFree) {
		echo (get_student_paid(1) ? "PAID":"UNPAID <strong>$".$allSessions[$sessions[0]]->cost).'</strong></li>'."\n"; 
	}
	else {
		echo "FREE</li>\n";
	}
	echo '<li class="list-group-item">Double Session (2-3): ';
	if (!$isDoubleFree) {
		echo (get_student_paid(2) ? "PAID":"UNPAID <strong>$".$allSessions[$sessions[1]]->cost).'</strong></li>'."\n"; 
	}
	else {
		echo "FREE</li>\n";
	}	
}


//Handle Singles
// THREE SINGLES
else {
	$x = 1;
	foreach ($sessions as $sess) { 
		if ($allSessions[$sess]->id == 0) $emptySessions = TRUE;
		if (!get_student_paid($allSessions[$sess]->block)) {
			$paid = FALSE;
		}
		$isFree = ($allSessions[$sessions[$x-1]]->cost == 0) ? TRUE : FALSE;
		echo '<li class="list-group-item">Session '.$x.': ';
		if (!$isFree) {
			echo (get_student_paid($x) ? "PAID":"UNPAID <strong>$".$allSessions[$sessions[$x-1]]->cost).'</strong></li>'."\n"; 
		}
		else {
			echo ($sessions[$x-1] == 0 ? "UNCHOSEN</li>\n" : "FREE</li>\n");
		}
		$x++;
	}
}




/*
for ($x=0;$x<3;$x++) {
	echo '<li class="list-group-item">Session '.($x+1).': ';
	if ($sessions[$x] == 0) {
		echo "NOT YET CHOSEN</li>\n";
		$emptySessions = true;
		continue;
	}
	if ($allSessions[$sessions[$x]]->cost > 0) {
		echo "$".$allSessions[$sessions[$x]]->cost." - ";
		if (!get_student_paid($x+1)) {
			echo 'UNPAID</li>'."\n";
			$paid = false;
		}
		else
			echo "PAID</li>\n";
	}
	else
		echo 'FREE'."\n";
	//if (get_student_paid(($x+1))) echo "TRUE"; else echo "FALSE";
}
*/

//IF NOT PAID
if (!$paid && !$emptySessions) echo "<li class='list-group-item'><a data-target='#modalPaypalWait' data-toggle='modal' role='button' class='btn btn-warning btn-block' onclick='$(\"#paymentForm\").submit();'>PAY NOW</a></li>\n";

//IF NOT PAID BUT HAS OUTSTANDING GAPS IN REGISTRATION
else if (!$paid) echo "<li class='list-group-item'>Please finish selecting sessions, then you will be given the option to pay</li>\n";

//FORM TO SEND TO PAYPAL
echo "<form id='paymentForm' action='paypal/process.php' method='POST'>\n";
echo "<input type='hidden' name='blocksToPayFor' value='";
$output = "";
foreach (getUnpaidSessions() as $sess) { $output = $output.$sess->block.","; }
$output = rtrim($output,",");
echo $output."'>\n";
echo "</form>\n";
echo "</div>\n";
?>
