<?php
require_once("database.php");
require_once("functions.php");

if ($_SERVER['REQUEST_METHOD'] != "POST" || !isset($_POST['i'])) die("Improper");

$student = getAllStudents()[$_POST['i']];
$student->active = !$student->active;

student_update($student);

die("1");

?>
