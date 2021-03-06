function register(_session) {
	var formData ={session:_session};
	$("#accordion").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");
	$.ajax({
		type: "POST",
		url: "register.php",
		data: formData,
		success: function(result) {
			console.log(result);
			window.scrollTo(0,0);
			if (result == "1") {
				//window.location.href = "registration.php?session=" + _session;
				$("#alertBox").html("<form id='confirmForm' method='POST'><input type='hidden' name='session' value='" + _session + "'/></form>");
				$("#confirmForm").submit();
			}
			else {
				$("#alertBox").html("<form id='confirmForm' method='POST'><input type='hidden' name='result' value='"+ result + "'/></form>");
				$("#confirmForm").submit();
			}
		}
	});
}

function updatePaymentBox() {
	return;
	//REMOVE PAYMENT INFO

	$.ajax({
		url: "getPaymentBox.php",
		success: function(result) {
			$("#paymentBox").html(result);
		}
	});
}

function timedOut() {
	$.ajax({
		url: "getTimedOut.php",
		success: function(result) {
			if (result == "TRUE") {
				$('#modalTimeout').modal('show');
				$('#modalTimeout').on('hidden.bs.modal',function (e) {
  					window.location.replace("index.php");
				});
			}
			//console.log("Timed out: " + result);
		}
	});
}

function authenticate() {
	var _user = document.getElementById("username").value;
	var _pass = document.getElementById("password").value;
	var formData = {user:_user,pass:_pass,func:"auth",headless:"1"};
	$.ajax({
		type: "POST",
		url:"login.php",
		data: formData,
		success: function(result) {
			console.log(result);
			if (result == 1) window.location.href="registration.php";
			else $('#studentLoginResult').html("<p class='bg-danger'>Login failed - please try again</p>");
		}
	});

}

function authenticateToAdmin() {
	var _user = document.getElementById("adminusername").value;
	var _pass = document.getElementById("adminpassword").value;
	var formData = {user:_user,pass:_pass,func:"authadmin",headless:"1"};
	$.ajax({
		type: "POST",
		url:"login.php",
		data: formData,
		success: function(result) {
			console.log(result);
			if (result == 1) window.location.href="admin.php";
			else $('#adminLoginResult').html("<p class='bg-danger'>Login failed - please try again</p>");
		}
	});

}
function loadIntro() {
	populateInfoBox(1);
}

function populateInfoBox(block,_session) {
	//Set default value for _session
	if (typeof _session === 'undefined') { _session = 0; }

	$("#accordion").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");
	$.ajax({
		url:"getAccordionSessions.php?block="+block,
		success: function(result) {
			$("#session1button").removeClass("btn-success").addClass("btn-primary");
			$("#session2button").removeClass("btn-success").addClass("btn-primary");
			//$("#session3button").removeClass("btn-success").addClass("btn-primary");
			if (block == "1") $("#session1button").removeClass("btn-primary").addClass("btn-success");
			if (block == "2") $("#session2button").removeClass("btn-primary").addClass("btn-success");
			//if (block == "3") $("#session3button").removeClass("btn-primary").addClass("btn-success");
			$("#accordion").hide();
			$("#accordion").html(result);
			$("#accordion").fadeIn();

			if (_session != 0) {
				var elem = $("#collapse"+_session);
				$('html, body').animate({scrollTop: elem.offset().top-300}, 500);
			}
			else {
				var elem = $("#accordion");
				$('html, body').animate({scrollTop: elem.offset().top-50}, 500);
			}
		}
	});
}

function finished() {
	$.ajax({
		url:"finished.php",
		success: function(result) {
			//console.log("Finished results: " + result);
			if (result == "1") window.location.href="index.php";
		}
	});
}
