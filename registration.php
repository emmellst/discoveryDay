<?php
require_once("database.php");
require_once("functions.php");
require_once("Student.php");
require_once("Session.php");

auth_setup();
//if (!auth_check()) header("location: index.php");
if (!canReg()) {
	if (!(isset($_SESSION['id']) && $_SESSION['id'] == 1)) {
		echo "<HTML><h2>Registration is not yet open.<br/>Please <a href='index.php'>click here</a> to return to the front page</h2></html>";
		die();
	}
}

$allSessions = getAllSessions();
$allStudents = getAllStudents();
//print_r($allStudents); die();
$student = $allStudents[$_SESSION['id']];

//Set registration, new or coming in with chosen sessions
$sessions = array();
//$sessions[0] = $student->session1;
//$sessions[1] = $student->session2;
//$sessions[2] = $student->session3;
foreach ($student->sessions as $i=>$session) {
	$sessions[$i] = $session;
}
//print_r($sessions);  die();
//CHECK IF UNPAID SESSIONS HAVE EXPIRED!



//DISPLAY ALERT IF SUCCESSFUL REGISTRATION
$alert = "";
if (isset($_POST) && isset($_POST['session']))
	$alert = '<div class="alert alert-success"><a href="#" class="close" '.
	'data-dismiss="alert" aria-label="close">&times;</a><strong>Success!</strong><br/> '.
	'You have registered into '.$allSessions[$_POST['session']]->name.'</div>';
else if (isset($_POST) && isset($_POST['cancelPay'])) {
	//MESSAGE ABOUT CANCELLED PAYMENT
	$alert = '<div class="alert alert-warning"><a href="#" class="close" '.
	'data-dismiss="alert" aria-label="close">&times;</a><strong>Payment Cancelled!</strong><br/> '.
	'Please be sure to revisit this page to pay for your sessions. Your registration is NOT COMPLETE</div>';
}
else if (isset($_POST) && isset($_POST['successPay'])) {
	$alert = '<div class="alert alert-success"><a href="#" class="close" '.
	'data-dismiss="alert" aria-label="close">&times;</a><strong>Paid!</strong><br/> '.
	'You have paid for your session(s). Thank you! If all your sessions are now paid, then you\\\'re all set!</div>';
}
else if (isset($_POST) && isset($_POST['result'])) {
	switch ($_POST['result']) {
	case "0":
		$alert = '<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'.
			 '<strong>Sorry!</strong> Hate to break it to you, this session is now full</div>';
		break;
	case "-1":
		$alert ='<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'.
			'<strong>Failed!</strong> You are not signed in. <br/>Please <a href="index.php">click here</a> to return to the front page</div>';
		break;
	case "-2":
		$alert = '<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'.
			'<strong>Failed!</strong> Registration is closed. How did you get here?</div>';
		break;
	case "-4":
		$alert = '<div class="alert alert-warning"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'.
			'<strong>Registered!</strong> You have successfully chosen this session, but you will not be fully registered until you have made payment</div>';
		break;
	default:
		$alert = '<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'.
			'<strong>Failed!</strong> Something went wrong.<br/>Have you paid for a session and tried to change it?<br/>'.
			'Please speak with <strong><a href="mailto:tracie.lanford@ocdsb.ca">Ms. Langford</a> or <a href="mailto:stephen.emmell@ocdsb.ca">Mr. Emmell</a></strong> for help with this</div>';	
	}
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="author" content="Stephen Emmell">
    <link rel="icon" href="../../favicon.ico">

    <title>WCSS Discovery Day</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/sticky-footer.css" rel="stylesheet">
    <link href="registration.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
    <!-- Custom styles for this template -->
    <link href="css/starter-template.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body onload="loadIntro();runAlerts();updatePaymentBox();">
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php">WCSS Discovery Day</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
	    <li><a href="index.php">Home</a></li>
	    <li><a href="view.php">View</a></li>
	    <li class="active"><a href="registration.php">Registration</a></li>
            <li><a href="#" data-toggle="modal" data-target="#contactModal">Contact Us</a></li>
          </ul>
	  <ul class="nav navbar-nav navbar-right">
	    <li><a href="logout.php"><span class="glyphicon glyphicon-log-in"></span> Not <?=$allStudents[$_SESSION['id']]->fname?> <?=$allStudents[$_SESSION['id']]->lname?>?</a></li>
	    <li><a href="#" data-toggle="modal" data-target="#adminLogin"><span class="glyphicon glyphicon-log-in"></span> Admin</a></li>
	  </ul>	
	</div>
<!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container">

	<h2>Register or Change Registration for <?=$allStudents[$_SESSION['id']]->fname?> <?=$allStudents[$_SESSION['id']]->lname?></h2>
	<div id="alertBox"></div>	
	<div class="col-sm-4">
		<h3>Current Registration<br/><small>Select a time slot to see choices</small></h3>
		<div class="btn-group-vertical btn-block">
			<? for ($x=0;$x<$numSessions;$x++) { ?>
				Session<?=($x+1)?>: <button id="session<?=($x+1)?>button" type="button" class="btn btn-primary" onclick="clearAlerts(); populateInfoBox(<?=($x+1)?>,<?=$sessions[$x]?>);"><?=$allSessions[$sessions[$x]]->name?></button><br/>
			<? } ?>

			<br/>
			<br/>
			<button id="finishedButton" type="button" class="btn btn-warning" onclick="finished();">Click here if finished (Logout)</button>
		</div>
		<div id="paymentBox"></div>
	</div>
	<div class="col-sm-8">
		<div class="panel-group" id="accordion">
		</div>
	</div>

	<!-- Send To Paypal Dialog-->
	<div id="modalPaypalWait" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Redirecting you to Paypal</h4>
				</div>
				<div class="modal-body">
					<h3 class='row text-center'>Please wait<h3>
					<div class='row text-center'><br/><br/><img src='loading.gif'/></div>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Session Timed Out Dialog -->
	<div id="modalTimeout" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Your session has timed out</h4>
				</div>
				<div class="modal-body">
					<h3>Your session has been inactive for 30 minutes<h3>
					<h4><a href="index.php">Please click here to return to the front page to sign in again</a></h4>
				</div>
			</div>
		</div>
	</div>
</div>
<!--
        <footer>
                <div class="container">
                        <h5 class='text-muted text-center'>Developed by S. Emmell @ West Carleton SS<br/><small><a href="mailto:stephen.emmell@ocdsb.ca">stephen.emmell@ocdsb.ca</a></small></h5>
                </div>
        </footer>
-->
	<!-- Admin Login dialog -->
	<div id="adminLogin" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Login to Administration</h4>
				</div>
				<div class="modal-body">
					<div id="adminLoginResult"></div>
					<form role="form" id="adminLoginForm" name="adminLoginForm">
						<div class="form-group">
						 <label for="adminusername">Username: </label>
						 <input type="text" class="form-control" id="adminusername">
						</div>
						<div class="form-group">
						 <label for="adminpassword">Password: </label>
						 <input type="password" class="form-control" id="adminpassword">
						</div>
						<input type="submit" class="btn-lg" value="Login"/>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>

<? include("contactModal.html"); ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="js/registration.js"></script>
	<script>
	<?
		//Set the session block to load as the first unchosen session (session ID of 0)
		$blockToLoad = $numSessions+1;
		foreach ($sessions as $index => $x) {
			if ($x == 0) { $blockToLoad = ($index+1); break; }
		}
	?>
	function loadIntro() {
		populateInfoBox(<?=$blockToLoad?>);
	}
	function clearAlerts() {
		$("#alertBox").fadeOut();
	}
	function runAlerts() {
		$("#alertBox").html('<?=addSlashes($alert)?>');
	}
	$(document).ready(function() {
		setInterval(timedOut,60000);
	});

	//Admin Login Modal Controls
	$('#adminLogin').on('shown.bs.modal', function () {
	    $('#adminusername').focus();
	});
	$('#adminLogin').on('hidden.bs.modal', function () {
		document.getElementById("adminLoginForm").reset();
		$('#adminLoginResult').html("");
	});

	$(function() {
		$("#adminLogin").on('submit',function(e) {
			e.preventDefault();
			authenticateToAdmin();
		});
	});
	</script> 
  </body>
</html>
