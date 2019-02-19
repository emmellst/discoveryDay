<?php
require_once("functions.php");

if (!isset($_GET['block']))  die ("Invalid Use - include block & student ID!");

echo getSessionsAccordion($_GET['block']);
?>
