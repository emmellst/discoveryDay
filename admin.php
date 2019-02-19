<?php
require_once("database.php");
require_once("functions.php");

auth_setup();
if (!auth_check() || $_SESSION['auth_user'] != "admin") header("location: index.php");
//if (!auth_check()) header("location: index.php");

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

    <title>WCSS DD Admin</title>

    <!-- Bootstrap core CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
    <link rel="stylesheet" type="text/css" href="theme.css">
    <link rel="stylesheet" type="text/css" href="css/sticky-footer.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.9/css/dataTables.bootstrap.min.css">

    <!-- Custom styles for this template -->
    <link href="css/simple-sidebar.css" rel="stylesheet">	

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
	    <li><a href="view.php">View</a></li>
	    <li><a href="registration.php">Registration</a></li>
	    <li class="active"><a href="admin.php">Admin</a></li>
            <li><a href="#" data-toggle="modal" data-target="#contactModal">Contact Us</a></li>
          </ul>
	  <ul class="nav navbar-nav navbar-right">
	    <li><a href="admin.php"><span class="glyphicon glyphicon-log-in"></span> Admin</a></li>
	  </ul>	
	</div>
<!--/.nav-collapse -->
      </div>
    </nav>

<!-- MAIN BODY OF PAGE -->
    <div id="wrapper">

        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <ul class="sidebar-nav">
                <li class="sidebar-brand">
                </li>
                <li>
                    <a href="#" onclick="loadSessions();">Sessions</a>
                </li>
                <li>
                    <a href="#" onclick="loadStudents();">Students</a>
                </li>
                <li>
                    <a href="#" onclick="loadReports();">Reports</a>
                </li>
                <li>
                    <a href="#" onclick="loadEmail();">Emails</a>
                </li>
                <li>
                    <a href="#" onclick="loadSettings();">Settings</a>
                </li>
		<li>
		    <a href="diag/logout.php">Logout</a>
		</li>
            </ul>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <div class="container-fluid">
		<div class="row">
			<br/><br/>
		    <div class="col-lg-12" id="infoBox">
			<h2>Admin Section</h2>
			<?php
			   $fullyRegistered = countRegisteredStudents();
			   $partiallyRegistered  = countPartialRegStudents();
			   $numStudents = countStudents();
			?>
			<div>
				<div class="col-md-6 text-center">
					<div class="well well-lg">
						<h3>Complete<br/><small>Registered for all sessions</small></h3>
						<h1><?php echo $fullyRegistered?><br/><small>(Full Reg: <?php echo round($fullyRegistered / $numStudents * 100)?> %)</small></h1>
					</div>
				</div>
				<div class="col-md-6 text-center">
					<div class="well well-lg">
						<h3>Partial<br/><small>Remainder who have at least one session</small></h3>
						<h1><?php echo ($partiallyRegistered-$fullyRegistered)?><br/><small>(Total students: <?php echo $numStudents?>)</small></h1>
					</div>
				</div>
			</div>
				
			<p class="lead"><strong>Warning!</strong> There is little to stop you from making mistakes! Please use your power carefully ;)</p>
			<p class="lead">This includes <br/>
				<ul>
					<li>Being able to break double / triple registrations. Please be sure to register people properly!</li>
					<li>Be sure to record the information if you are marking as Paid or UNMARKING PAID (Careful!)</li>
				</ul>
			</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- /#page-content-wrapper -->

    </div>
    <!-- /#wrapper -->
<!--
        <footer>
                <div class="container">
                        <h5 class='text-muted text-center'>Developed by S. Emmell @ West Carleton SS<br/><small><a href="mailto:stephen.emmell@ocdsb.ca">stephen.emmemmell@ocdsb.ca</a></small></h5>
                </div>
        </footer>
-->

<!-- Edit Student Modal -->
    <div id="editStudent" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content" id="editStudentContent">
 		<img src='loading.gif'/>
                

                


            </div>
        </div>
    </div>



<!-- Edit Sessions Modal -->
    <div id="editSession" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content" id="editSessionContent">
 		<img src='loading.gif'/>
                

                


            </div>
        </div>
    </div>

<!-- EXTRA JUNK -->

<?php include("contactModal.html"); ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.9/js/dataTables.bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="js/admin.js"></script>
    <script>
    //Edit Sessions Modal Controls
	$('#editSessions').on('shown.bs.modal', function () {
	    setTimeout(function(){$('#_name').focus();},100);
	});
	$('#editStudents').on('shown.bs.modal', function () {
	    setTimeout(function(){$('#editStudent input').focus();},100);
	});

	$(function() {
		$("#studentLogin").on('submit',function(e) {
			e.preventDefault();
			authenticate();
		});
	});
	</script>
  </body>
</html>
