<?php
require_once("database.php");
require_once("functions.php");
//return;
auth_setup();
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
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
    <!-- Custom styles for this template -->
    <link href="css/starter-template.css" rel="stylesheet">
    <link href="css/sticky-footer.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
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
	    <li class="active"><a href="index.php">Home</a></li>
	    <li><a href="view.php">View</a></li>
	    <!--<li><a href="registration.php">Registration</a></li>-->
		<?php if (!$settings['regEnabled']) { ?>
			<li class="disabled"><a href="#">Registration</a></li>
		<?php } else {
			if (auth_check()) {?>
				<li><a href="registration.php">Registration</a></li>
			<?php } else {?>
				<li><a href="#" data-toggle="modal" data-target="#studentLogin" >Registration</a></li>
			<?php }
		} ?>

            <li><a href="#" data-toggle="modal" data-target="#contactModal">Contact Us</a></li>
          </ul>
	  <ul class="nav navbar-nav navbar-right">
	    <li><a href="#" data-toggle="modal" data-target="#adminLogin"><span class="glyphicon glyphicon-log-in"></span> Admin</a></li>
	  </ul>	
	</div>
<!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container">

      <div class="starter-template">
        <h1>Welcome to WCSS Discovery Day!!</h1>
<p class="lead">On Wednesday, November 1st students in Grades 10, 11 &amp; 12 will participate in a variety of workshops, presentations, and activities involving Career Exploration, Health &amp; Wellness and Post-Secondary Opportunities. These workshops will be presented by staff members, community members as well as former WCSS Graduates.</p>
<p class="lead">This year Discovery Day will be a half-day event. You will attend regular classes for Periods 1 &amp; 4. During the other two timeslots, each student will have the opportunity to select 2 sessions/workshops to attend. Read the session write-ups to help you with your choices. Not all sessions are offered in every timeslot, so read and choose your selections wisely. There are 4 field trip options to consider as well. Read the details carefully to determine cost and timing of each field trip (each one is different).</p>
<p class="lead">Some sessions will have a cost associated with them. This cost will cover supplies provided at the workshop and/or transportation to your field trip location plus entry fee where required. These payments can be made using <a href="https://ocdsb.schoolcashonline.com/">School Cash Online</a>. The link can also be found on our school website at <a href="http://westcarletonss.ca/">westcarletonss.ca</a> (left hand side of the page). You will need to set up an account... consider doing this before October 23rd so you are ready when registration opens!</p>
<h3>Important Notes</h3>
<p>After you complete your registration, you will receive an email with your schedule for November 1st. Check your school Gmail account to find this schedule<p>
		<br/><br/>
		<?php if (auth_check()) {?>
			<a href="registration.php" class="btn btn-lg btn-primary <?php echo !$settings['regEnabled']?"disabled":""?>" ?>Register / Change Registration</a><br/><br/>
		<?php } else {?>
		<a href="#" data-toggle="modal" data-target="#studentLogin" class="btn btn-lg btn-primary <?php echo !$settings['regEnabled']?"disabled":""?>">Register / Change Registration</a><br/><br/>
		<?php } ?>
		<a href="view.php" class="btn btn-lg btn-primary">Get Information About Discovery Day Sessions</a>
	</p>
      </div>
    </div><!-- /.container -->
<!--
	<footer>
		<div class="container">
			<h5 class='text-muted text-center'>Developed by S. Emmell @ West Carleton SS<br/><small><a href="mailto:stephen.emmell@ocdsb.ca">stephen.emmell@ocdsb.ca</a></small></h5>
		</div>
	</footer>
-->
	<!-- Student Login dialogs -->
	<div id="studentLogin" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Login to Registration Section</h4>
				</div>
				<div class="modal-body">
					<div id="studentLoginResult"></div>
					<form role="form" id="studentLoginForm" name="studentLoginForm">
						<div class="form-group">
						 <label for="username">Username: (Ex: S11111111)</label>
						 <input type="text" class="form-control" id="username" placeholder="Normal username: S123456789">
						</div>
						<div class="form-group">
						 <label for="password">Password: (Ex: 1234554321)</label>
						 <input type="password" class="form-control" id="password" placeholder="School password: 1234567890">
						</div>
						<!-- <button type="button" class="btn-lg" onclick="authenticate();">Login</button>-->
						<input type="submit" class="btn-lg" value="Login"/>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>

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

<?php include("contactModal.html"); ?>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="js/registration.js"></script>
    <script>
    //Student Login Modal Controls
	$('#studentLogin').on('shown.bs.modal', function () {
	    $('#username').focus();
	});
	$('#studentLogin').on('hidden.bs.modal', function () {
		document.getElementById("studentLoginForm").reset();
		$('#studentLoginResult').html("");
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
		$("#studentLogin").on('submit',function(e) {
			e.preventDefault();
			authenticate();
		});
		$("#adminLogin").on('submit',function(e) {
			e.preventDefault();
			authenticateToAdmin();
		});
	});
	</script>
  </body>
</html>
