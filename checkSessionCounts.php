<?php
require("functions.php");

$allSess = getAllSessions();
$allStuds = getAllStudents();

echo "<html><body><ul>\n";
foreach ($allSess as $sess) {
	if ($sess->id == 0) continue;
	$count = 0;
	foreach($allStuds as $stud) {
		if (in_array($sess->id,$stud->sessions)) $count++;
	}
	echo "<li>".$sess->name." (".$count." found / ".$sess->filled." expected)";
	if ($count != $sess->filled)
		echo "<strong> - PROBLEM!!!</strong>";
	echo "</li>\n";
}
echo "</ul></body></html>";
