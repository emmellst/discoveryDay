<?php
require_once("database.php");
require_once("functions.php");

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
    <link href="view.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
    <!-- Custom styles for this template -->
    <link href="css/starter-template.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="assets/js/ie-emulation-modes-warning.js"></script>

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
	    <li><a href="index.php">Home</a></li>
	    <li class="active"><a href="view.php">View</a></li>
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
        <h1>Welcome to WCSS Discovery Day!</h1>
	<p class="lead">
		Click on the titles below to read more about each session.
	</p>
	<?php 
		$division = 12/$numSessions;
		for ($i = 0; $i < $numSessions; $i++) {
	?>
			<div class="col-sm-<?php echo $division?>">
				<div class="panel-group" id="accordion<?php echo ($i+1)?>">
				<?php echo getSessionsAccordionInfoOnly(($i+1));?>
				</div>
			</div>
	<?php
		}
	?>
      </div>

    </div><!-- /.container -->

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
						 <label for="username">Username: </label>
						 <input type="text" class="form-control" id="username" placeholder="Normal username: S123456789">
						</div>
						<div class="form-group">
						 <label for="password">Password: </label>
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
<!--
        <footer>
                <div class="container">
                        <h5 class='text-muted text-center'>Developed by S. Emmell @ West Carleton SS<br/><small><a href="mailto:stephen.emmell@ocdsb.ca">stephen.emm
                </div>
        </footer>
-->
<?php include("contactModal.html"); ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
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
