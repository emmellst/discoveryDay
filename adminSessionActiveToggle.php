<?php
require_once("database.php");
require_once("functions.php");

if ($_SERVER['REQUEST_METHOD'] != "POST" || !isset($_POST['i'])) die("Improper");

$session = getAllSessions()[$_POST['i']];

if ($session->active) $session->active = 0;
else $session->active = 1;

session_update($session);
die("1");

?>
