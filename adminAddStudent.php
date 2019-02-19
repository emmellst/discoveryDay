<?php
require_once("database.php");
require_once("functions.php");
global $pdo;

$snum = "S199785214";
$fname = "Jordan";
$lname = "Benson";
$hmrm = "RCR2O.-01";
$email = "jbens2@ocdsb.ca";
$password = "6919256055";



if ($query = $pdo->prepare("INSERT INTO `students`( 
				`snum`,	`fname`, `lname`, `hmrm`, `email`, `password`,`regd`, `paid1`, 
				`paid2`, `paid3`, `session1`, `session2`, `session3`, `active`, `pp_pmt_id1`, `pp_pmt_id2`, `pp_pmt_id3`) 

			VALUES (
				:snum, :fname, :lname, :hmrm, :email, :password,0, 0, 0, 0, 0, 0, 0, :active,'','','');")) {
	$queryArray = array(
		"snum"		=> $snum,
		"fname"		=> $fname,
		"lname"		=> $lname,
		"hmrm"		=> $hmrm,
		"email"		=> $email,
		"password"	=> password_hash($password,PASSWORD_DEFAULT),
		"active"	=> 1
	);
	$query->execute($queryArray);
}
else {
	$error = $query->errorInfo();
	throw new Exception('MySQL Error in student_insert: ' . $error[2]);
}

