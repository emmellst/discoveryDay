<?php
require_once("database.php");
require_once("Session.php");
require_once("functions.php");

$allSessions = getAllSessions();
$allStudents = getAllStudents();
?>
<table id="studentTable" class="table table-condensed display">
	<thead>
	<tr>
		<th>Name (Click to Edit)</th>
		<th>Student #</th>
		<th>Email</th>
<?php
	for($i=0;$i<$numSessions;$i++) {
		echo "<th>Session ".($i+1)."</th>\n";
	}
?>	
		<th>Active</th>
	</tr>
	</thead>
	<tbody>

<?php
foreach($allStudents as $student) {
?>
	<tr>
	<td><a href="#" data-toggle="modal" data-target="#editStudent" onclick="adminUpdateStudent(<?php echo $student->id?>);"><?php echo $student->lname.", ".$student->fname; ?></a></td>
	<td><?php echo $student->snum?></td>
	<td><?php echo $student->email?></td>
<?php
	for($i=0;$i<$numSessions;$i++) {
		echo "<td>";
		if ($student->sessions[$i] != 0) 
			echo $allSessions[$student->sessions[$i]]->name;
		else
			echo "";		
		echo "</td>\n";
	}
?>
	<td><a onclick="toggleActiveStudent(<?php echo $student->id?>);"><?php echo $student->active == "1" ? "YES" : "NO"; ?></a></td>
	</tr>
<?php

}
?>
</tbody>
