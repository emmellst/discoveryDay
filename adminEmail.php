<?php
require_once("database.php");
require_once("Session.php");
require_once("functions.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	print_r($_POST);
	$x = 0;
	$sendTime = -50000;
	$ids = explode(",",$_POST['ids']);
	foreach ($ids as $id) {
		updateMailQueue($id,$sendTime);
		if ($x % 50 == 0) $sendTime += 5*60;
		$x++;
	}
}
$students = getAllStudents();
?>
<div id="alertBox"></div>
<form id="emailForm">
  <label for="emailList">Select students to email (hold CTRL to select multiple):</label>
  <select multiple class="form-control" id="emailList" size="20">
<?php
	foreach ($students as $stud) {
		if ($stud->active != "1") continue;
		echo "<option value='".$stud->id."'>".$stud->lname.", ".$stud->fname."</option>\n";
	}
?>
  </select>
</form>
<p class="lead">Please be careful!</p>
<button class="btn-lg btn-default" onclick="addEmailsToQueue();">Send Emails</button>


<script>
	function addEmailsToQueue() {
		$("#alertBox").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");
		var selectedIDs = $("#emailList option:selected").map(function(){ return this.value }).get().join(", ");
	       $.ajax({
			type: "POST",
			url:"adminEmail.php",
			data: {ids:selectedIDs},
			success: function(result) {
				//console.log(result);
				$("#alertBox").html("<h3 class='bg-success'>Emails Queued for Sending</h3>");
			}
		});
	}
</script>
