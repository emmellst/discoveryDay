<?php
require_once("functions.php");
auth_setup();
if (!auth_check() || $_SESSION['auth_user'] != "admin") die("You do not have permission");


//UNPAID REPORT----------------------------------
if (isset($_GET['f']) && $_GET['f'] == "unpaid") {
?>
	<html><head><style>
	.footer {
		page-break-after: always;
	}
	</style></head><body onload="window.print();">
	<h2 style="text-align: center;">Unpaid Registration Report</h2>
	<ul>
<?php
	$allSessions = getAllSessions();
	$allStudents = getAllStudents();
	$x = 0;	
	foreach ($allStudents as $stud) {
		$unpaid = false;
		$sess1 = $allSessions[$stud->session1];
		$sess2 = $allSessions[$stud->session2];
		$sess3 = $allSessions[$stud->session3];
		if ($sess1->cost > 0 && $stud->paid1 == 0) $unpaid = true;
		if ($sess2->cost > 0 && $stud->paid2 == 0) $unpaid = true;
		if ($sess3->cost > 0 && $stud->paid3 == 0) $unpaid = true;

		if ($unpaid) {
			echo "<li>".$stud->lname.", ".$stud->fname." - 1st Period: ".$stud->hmrm."\n";
			echo "<ul> ";

			if ($sess1->cost > 0 && $stud->paid1 == 0) echo "<li>".$sess1->name." ($".$sess1->cost.")</li>";
			if ($sess2->cost > 0 && $stud->paid2 == 0 && $sess2->id != $sess1->id) echo "<li>".$sess2->name." ($".$sess2->cost.")</li>";
			if ($sess3->cost > 0 && $stud->paid3 == 0 && $sess3->id != $sess2->id) echo "<li>".$sess3->name." ($".$sess3->cost.")</li>";
			echo "</ul></li><br/>\n";
			$x++;
		}
	}	
	echo "</ul>\n";
	echo "<br/><h2>Total $x students with unpaid registrations</h2></body></html>";
}



//---------LISTS FOR PERMISSION FORM TRACKING------------------------
else if (isset($_GET['f']) && $_GET['f'] == "allforms") {
?>
	<html><head><style>
	.footer {
		page-break-after: always;
	}
	</style></head><body onload="window.print()">
<?php
	$allSessions = getAllSessions();
	$allStudents = getAllStudents();
	$helperText = "<p>Can you verify that the following students have submitted their permissions forms? <br/>".
			"- Students were emailed the permission forms to their school gmail accounts.<br/>".
			"- All forms should be submitted to Ms. Langford in room 105.<br/>".
			"- Ms. Lanford can also help if students can't find their permission form.<br/><br/>Thank you!</p>";
	$x = 0;	
	$reportArray = array();
	foreach ($allStudents as $stud) {

		//IF FORM REQUIRED
		$formNeeded = false;
		foreach ($stud->sessions as $s) {
			if ($allSessions[$s]->forms != "")
				$formNeeded = true;
		}
		/*
		if ($allSessions[$stud->session1]->forms != "" ||
		    $allSessions[$stud->session2]->forms != "" ||
		    $allSessions[$stud->session3]->forms != "") 
		    	$formNeeded = true;
	      	*/ 
		if ($formNeeded) {
			//echo $stud->lname.", ".$stud->fname.", ".$stud->hmrm."\n";
			//DOES HMRM HAVE ARRAY YET?
			if (!isset($reportArray[$stud->hmrm]))
				$reportArray[$stud->hmrm]=array();

			//IS STUD ALREADY THERE?
			$reportArray[$stud->hmrm][$stud->lname.",".$stud->fname] = $stud;
		}
	}

	//OUTPUT
	foreach ($reportArray as $hmrm=>$studArr) {
		echo  "<h2 style='text-align: center;'>DD Permission Form Report</h2>\n";
		echo "<h3 style='text-align: center;'>".$hmrm."</h3>\n";
		echo $helperText;
		echo "<h4>Students</h4>\n";
		echo "<ul>\n";
		foreach($studArr as $s) {
			echo "<li>".$s->lname.", ".$s->fname;

			$forms = array();
			foreach($s->sessions as $sessID) {
				if ($allSessions[$sessID]->forms != "") 
					$forms[$sessID] = $allSessions[$sessID]->id;
			}
			/*
			if ($allSessions[$s->session1]->forms != "") $forms[$s->session1] = $allSessions[$s->session1]->id;
			if ($allSessions[$s->session2]->forms != "") $forms[$s->session2] = $allSessions[$s->session2]->id;
			if ($allSessions[$s->session3]->forms != "") $forms[$s->session3] = $allSessions[$s->session3]->id;
			*/
	
			echo "<ul>";
			foreach ($forms as $f) 
				echo "<li>".$allSessions[$f]->name."</li>\n";
			echo "</ul></li><br/>\n";
		}
		echo "</ul><div class='footer'></div>\n";
	}
	echo "<br/><h2>Total $x students with permission forms due</h2></body></html>";
}




//---------UNPAID REPORT -> CSV---------------------
else if (isset($_GET['f']) && $_GET['f'] == "unpaidExport") {
	$allSessions = getAllSessions();
	$allStudents = getAllStudents();
	$outputString = "\"Last Name\",\"First Name\",Homeroom,Session,Cost\n";
	foreach ($allStudents as $stud) {
		$unpaid = false;
		$sess1 = $allSessions[$stud->session1];
		$sess2 = $allSessions[$stud->session2];
		$sess3 = $allSessions[$stud->session3];
		if ($sess1->cost > 0 && $stud->paid1 == 0) $unpaid = true;
		if ($sess2->cost > 0 && $stud->paid2 == 0) $unpaid = true;
		if ($sess3->cost > 0 && $stud->paid3 == 0) $unpaid = true;

		if ($unpaid) {
			if ($sess1->cost > 0 && $stud->paid1 == 0) {
				$outputString .= $stud->lname.",".$stud->fname.",\"".$stud->hmrm."\",\"".$sess1->name."\",$".$sess1->cost."\n";
			}
			if ($sess2->cost > 0 && $stud->paid2 == 0 && $sess2->id != $sess1->id) {
				$outputString .= $stud->lname.",".$stud->fname.",\"".$stud->hmrm."\",\"".$sess2->name."\",$".$sess2->cost."\n";
			}
			if ($sess3->cost > 0 && $stud->paid3 == 0 && $sess3->id != $sess2->id) {
				$outputString .= $stud->lname.",".$stud->fname.",\"".$stud->hmrm."\",\"".$sess3->name."\",$".$sess3->cost."\n";
			}
		}
	}	
	$timestamp = date("Y-m-d h:i:s");
	header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=DD-2016-UnpaidRegistrations-$timestamp.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
	echo $outputString;
}

//------------------UNREGISTERED---------------------------------------
else if (isset($_GET['f']) && $_GET['f'] == "unregd") {
	$helperText = "<p>Can you take a minute to remind / encourage the following students to complete their Discovery Day registration?<br/>".
			"- All students on this list have at least some part of their registration not yet complete<br/>".
			"- Ms. Lanford can also help if students are having difficulty.<br/><br/>Thank you!</p>\n";
	$allStudents = getAllStudents();
	$homerooms = array();

	foreach($allStudents as $s) {
		if (!isset($homerooms[$s->hmrm])) {
			$homerooms[$s->hmrm] = array();
		}
		$unregd = false;
		foreach ($s->sessions as $sess)
			if ($sess == 0) $unregd = true;

		if ($unregd)
			array_push($homerooms[$s->hmrm],$s->lname.", ".$s->fname);
	}
?>
	<html><head><style>
	.footer {
		page-break-after: always;
	}
	</style></head><body onload="window.print();">
<?php
	foreach($homerooms as $hmrm=>$studNames) {
?>
		<h2 style="text-align: center;">No Registration Report - <?php echo $hmrm?></h2>
		<?php echo $helperText?>	
	<?php
		echo "<h4>Students</h4>\n";
		echo "<ul>\n";
			foreach ($studNames as $name) echo "<li>".$name."</li>\n";
		echo "</ul>\n";
		echo "<div class='footer'></div>";
	}

	echo "</body></html>";
}


//------------------UNREGISTERED OLD---------------------------------------
else if (isset($_GET['f']) && $_GET['f'] == "unregdOLD") {
?>
	<html><head><style>
	.footer {
		page-break-after: always;
	}
	</style></head><body onload="window.print();">
	<h2 style="text-align: center;">No Registration Report</h2>
	<h3 style="text-align: center;">(Not including gr. 9s)</h3>
	<ul>
<?php
	$allStudents = getAllStudents();
	$x = 0;	
	foreach ($allStudents as $stud) {
		//if ($stud->active == "1" && $stud->session1 == 0 && $stud->session2 == 0 && $stud->session3 == 0) {
		$allZero = true;
		foreach($stud->sessions as $s)	
			if ($s != 0) 
				$allZero = false;

		if ($stud->active == "1" && $allZero) {
			echo "<li>".$stud->lname.", ".$stud->fname." - 1st Period: ".$stud->hmrm."</li>";
			$x++;
		}
	}	
	echo "</ul>\n";
	echo "<br/><h2>Total $x students with no registrations</h2></body></html>";
}


//--------------------PARTIAL REGISTRATION REPORT-------------------------
else if (isset($_GET['f']) && $_GET['f'] == "partialReg") {
?>
	<html><head><style>
	.footer {
		page-break-after: always;
	}
	</style></head><body>
	<h2 style="text-align: center;">Partial Registration Report</h2>
	<ul>
<?php
	$allStudents = getAllStudents();
	$x = 0;	
	foreach ($allStudents as $stud) {
		$atLeastOneEmpty = false;
		$allEmpty = true;
		foreach ($stud->sessions as $s) {
			if ($s == 0) {
				$atLeastOneEmpty = true;
			}
			if ($s != 0) {
				$allEmpty = false;
			}
		}
		if ($atLeastOneEmpty && !$allEmpty) {
			$x++;
			echo "<li>".$stud->lname.", ".$stud->fname." - 1st Period: ".$stud->hmrm."</li>";
		}
	}	
	echo "</ul>\n";
	echo "<br/><h2>Total $x students with partial registrations</h2></body></html>";
}


//-------------------BIG SCHEDULE REPORT -> GENERATES A SCHEDULE FOR ALL STUDENTS---------------------------
else if (isset($_GET['f']) && $_GET['f'] == "allsched") {
?>
	<html><head><style>
	.footer {
		page-break-after: always;
	}
	</style></head><body onload="window.print();">
<?php
	$allStudents = getAllStudents();
	$allSessions = getAllSessions();
	$x=1;
	foreach ($allStudents as $stud) {
		if ($x % 3 == 0) { // start new page
			echo "<table class='page'>";
			echo "<tr style='height: 550px;' valign='top'><td>";
		} 
		else {	
			echo "<tr><td>";
		}
		$sessions = array();
		for($i=0;$i<sizeof($stud->sessions);$i++) {
			array_push($sessions,$allSessions[$stud->sessions[$i]]);
		}
		echo generateScheduleSimple($allStudents[$stud->id],$sessions);
		
		if (($x+3) % 3 ==  0) {	// (end page)
			echo "</td></tr></table>\n";
			echo "<div class='footer'></div>";
		}
		else {		
			echo "</td></tr>";
		}
		$x++;
	}	
	echo "</body></html>";
}



//--------------------UNREGISTERED CSV EXPORT---------------------------
else if (isset($_GET['f']) && $_GET['f'] == "unregdExport") {
	$allStudents = getAllStudents();
	$outputString = "\"Last Name\",\"First Name\",Homeroom\n";

	foreach ($allStudents as $stud) {
		$allZero = true;
		foreach($stud->sessions as $s)	
			if ($s != 0) 
				$allZero = false;

		if ($stud->active == "1" && $allZero) {
			$outputString .= "\"".$stud->lname."\",\"".$stud->fname."\",\"".$stud->hmrm."\"\n";	
		}
	}	
	$timestamp = date("Y-m-d h:i:s");
	header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=DD-".date("Y")."-NoRegistrations-$timestamp.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
	echo $outputString;
}

//------------------ATTENDANCE TRACKING FOR NON-FREE SESSIONS---------------------------
else if (isset($_GET['f']) && $_GET['f'] == "nonfreeattendance") {
?>
	<html><head><style>
	.footer {
		page-break-after: always;
	}
	.list {
		border-collapse: collapse;
	}
	.list td {
		border: 1px solid black;
		
	}
	</style></head><body onload="window.print();">
<?php
	$allSessions = getAllSessions();
	$allStudents = getAllStudents();
	$registrations = getStudentsAllSessions();
	
	foreach($registrations as $sessID=>$students) {
		$sess = $allSessions[$sessID];
		if ($sess->cost == 0) continue;

		/*
		echo "SESSION: ".$sessID." :: ".$sess->name;
		print_r($students);
		continue;
		*/

		echo "<h1 style='text-align: center;'>WCSS Discovery Day - Session Attendance</h1>\n";
		echo "<h2>".$sess->name."</h2>\n";
		echo "<table class='list'>\n";
		$num = 0;
		$numPaid = 0;
		foreach($students as $stud) {
			$reg = $allStudents[$stud];
			echo "<tr>";
			echo "<td style='width: 250px'>".$reg->lname.", ".$reg->fname."</td>";
			echo "<td style='width:250px'>paid: ";
			if ($reg->paid[$sess->block-1])  {
				echo "YES - ";
				$numPaid++;
			} else
				echo "NO - ";
			echo $reg->pp_pmts[($sess->block-1)]."</td></tr>\n";
			$num++;
		}
		echo "</tr></table>";
		echo "<h4>Session in Block ";
		echo $sess->block;
		echo "<br/>Total Registered Students: ".$num." (".$numPaid." paid)<br/>Cost: ".($sess->cost == 0 ? "FREE":"$".$sess->cost)."</h4>\n";


		echo "<div class='footer'></div>\n";
	}


}



//------------------------ATTENDANCE REPORTS----------------------------------------
else if (isset($_GET['f']) && $_GET['f'] == "attendance") {
?>
	<!DOCTYPE html><head><style>
	.footer {
		page-break-after: always;
	}
	.list {
		border-collapse: collapse;
	}
	.list td {
		border: 1px solid black;
		
	}
	.list th {
		text-align: left;
	}

	</style></head><body>
<?php
	$allSessions = getAllSessions();
	$allStudents = getAllStudents();
	$sessionReg = getStudentsAllSessions();
	
	foreach($sessionReg as $sessID=>$students) {
		$sess = $allSessions[$sessID];

		//SKIP SESSION 0  (NOT CHOSEN)
		if ($sessID == 0) continue;
		/*
		echo "SESSION: ".$sessID." :: ".$sess->name;
		print_r($students);
		continue;
		*/

		echo "<h1 style='text-align: center;'>WCSS Discovery Day - Attendance</h1>\n";
		echo "<h2>".$sess->name."</h2>\n";
		echo "<table class='list'>\n";
		$x = 0;
		echo "<tr><th></th><th style='width:250px'>Name</th>";
		if ($sess->cost > 0) echo "<th>Payment Info</th>";
		echo "</tr>\n";
		foreach($students as $stud) {
			$reg = $allStudents[$stud];
		
			//DISCUSS PAID OR NOT
			//TODO
	
			$x++;
			echo "<tr>\n";
			echo "<td style='width:30px'>&nbsp;</td>";
			echo "<td>".$reg->lname.", ".$reg->fname."</td>";
			if ($sess->cost > 0) {
				if ($reg->paid[$sess->block-1])
					echo "<td>PAID: ".$reg->pp_pmts[($sess->block)-1]."</td>\n";
				else
					echo "<td>".$reg->pp_pmts[($sess->block)-1]."</td>\n";
			}
			echo "</tr>\n";
		}
		echo "</tr></table>\n";
		
		echo "<h4>Session: ";
		if ($sess->linked == "") echo $sess->block;
		else if ($sess->linked == "1,2") echo "DOUBLE 1-2";
		echo "<br/>Total Registered Students: ".$x."<br/>Cost: ".($sess->cost == 0 ? "FREE":"$".$sess->cost)."</h4>\n";


		echo "<div class='footer'></div>\n";
	}


}
else {
?>
	<script>
	function unpaid(){
		//Display waiting anim
		$("#tempContainer").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");	
		
		//Ajax for report results
		$.ajax({
			url: "adminreports.php?f=unpaid",
			success: function(result) {
				//New window with contents
				myWindow = window.open();
				myWindow.document.write(result);
				myWindow.focus();
				myWindow.print();

				//Reload tempContainer with report options
				$.ajax({
					url: "adminreports.php",
					success: function(result) {
						$("#tempContainer").html(result);
					}
				});	
			}
		});	
	}
	
	function unregistered(){
		//Display waiting anim
		$("#tempContainer").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");	
		
		//Ajax for report results
		$.ajax({
			url: "adminreports.php?f=unregd",
			success: function(result) {
				//New window with contents
				myWindow = window.open();
				myWindow.document.write(result);
				myWindow.focus();
				myWindow.print();

				//Reload tempContainer with report options
				$.ajax({
					url: "adminreports.php",
					success: function(result) {
						$("#tempContainer").html(result);
					}
				});	
			}
		});	
	}
	function attendanceAll(){
		//Display waiting anim
		$("#tempContainer").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");	
		
		//Ajax for report results
		$.ajax({
			url: "adminreports.php?f=attendance",
			success: function(result) {
				//New window with contents
				myWindow = window.open();
				myWindow.document.write(result);
				myWindow.focus();
				myWindow.print();
				
				//Reload tempContainer with report options
				$.ajax({
					url: "adminreports.php",
					success: function(result) {
						$("#tempContainer").html(result);
					}
				});	
			}
		});	
	}
	function attendanceNonFree(){
		//Display waiting anim
		$("#tempContainer").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");	
		
		//Ajax for report results
		$.ajax({
			url: "adminreports.php?f=nonfreeattendance",
			success: function(result) {
				//New window with contents
				myWindow = window.open();
				myWindow.document.write(result);
				myWindow.focus();
				myWindow.print();

				//Reload tempContainer with report options
				$.ajax({
					url: "adminreports.php",
					success: function(result) {
						$("#tempContainer").html(result);
					}
				});	
			}
		});	
	}

	function allforms(){
		//Display waiting anim
		$("#tempContainer").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");	
		
		//Ajax for report results
		$.ajax({
			url: "adminreports.php?f=allforms",
			success: function(result) {
				//New window with contents
				myWindow = window.open();
				myWindow.document.write(result);
				myWindow.focus();
				myWindow.print();

				//Reload tempContainer with report options
				$.ajax({
					url: "adminreports.php",
					success: function(result) {
						$("#tempContainer").html(result);
					}
				});	
			}
		});	
	}
	
	function partialReg(){
		//Display waiting anim
		$("#tempContainer").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");	
		
		//Ajax for report results
		$.ajax({
			url: "adminreports.php?f=partialReg",
			success: function(result) {
				//New window with contents
				myWindow = window.open();
				myWindow.document.write(result);
				myWindow.focus();
				myWindow.print();

				//Reload tempContainer with report options
				$.ajax({
					url: "adminreports.php",
					success: function(result) {
						$("#tempContainer").html(result);
					}
				});	
			}
		});	
	}
	function allsched(){
		//Display waiting anim
		$("#tempContainer").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");	
		
		//Ajax for report results
		$.ajax({
			url: "adminreports.php?f=allsched",
			success: function(result) {
				//New window with contents
				myWindow = window.open();
				myWindow.document.write(result);
				myWindow.focus();
				myWindow.print();

				//Reload tempContainer with report options
				$.ajax({
					url: "adminreports.php",
					success: function(result) {
						$("#tempContainer").html(result);
					}
				});	
			}
		});	
	}

	</script>
	<div id="tempContainer">
		<ul class="list-group">
			<li class="list-group-item"><a onclick="attendanceAll();">All Sessions Attendance Report</a></li>
			<li class="list-group-item"><a onclick="attendanceNonFree();">Non-Free Sessions Report</a></li>
			<li class="list-group-item"><a onclick="unpaid();">Unpaid Registration Report (BROKEN)</a> <a href="adminreports.php?f=unpaidExport">(HERE FOR SPREADSHEET)</a></li>
			<li class="list-group-item"><a onclick="partialReg();">Partial Registration Report</a</li>
			<li class="list-group-item"><a onclick="unregistered();">No Registration Report </a> <a href="adminreports.php?f=unregdExport">(HERE FOR SPREADSHEET)</a></li>
			<li class="list-group-item"><a onclick="allforms();">Permission Form Report by Homeroom</a></li>
			<li class="list-group-item"><a onclick="allsched();">All Schedules Report (SLOW)</a></li>
		</ul>
	</div>






<?php
}
?>

