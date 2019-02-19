function changeAdminPwd() {
//change the password
//display confirmation

//change button to spinny wheel
	$("#buttonHolder").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");

//check if newPwd & newPwd-conf are the same
	if($("#newPwd").val() !== $("#newPwd-conf").val()) {
		$("#messageWindow").addClass("alert");
		$("#messageWindow").addClass("alert-danger");
		$("#messageWindow").html("<strong>Passwords do not match</strong>, please try again");
		return;
	}
	
	var _newPwd = $("#newPwd").val();	
	$.ajax({
		type: "POST",
		url: "adminPwdChange.php",
		data: {"pwd":_newPwd},
		success: function(result) {
			console.log(result);
			$("#messageWindow").addClass("alert");
			$("#messageWindow").addClass("alert-success");
			$("#messageWindow").html("<h3>Successfully updated</h3>");
			$("#buttonHolder").html("");
		}
		,error: function(result) {console.log(result);}
	});	
}

function toggleRegistration(_reg) {
	$.ajax({
		type: "POST",
		url: "changeRegistrationStatus.php",
		data: {"reg":_reg},
		success: function(result) {
			console.log(result);
			loadSettings();
		}
	});	
}	

function loadSessionUpload() {
	$("#infoBox").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");
	$.ajax({
		url: "uploadSessions.php",
		success: function(result) {
			$("#infoBox").html(result);
			$('#sessionTable').DataTable({"iDisplayLength":25});
			$('#sessionTable_filter input').focus();
		}
	});
}



function loadSessions() {
	$("#infoBox").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");
	$.ajax({
		url: "adminSessions.php",
		success: function(result) {
			$("#infoBox").html(result);
			$('#sessionTable').DataTable({"iDisplayLength":25});
			$('#sessionTable_filter input').focus();
		}
	});
}

function loadStudentUpload() {
	$("#infoBox").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");
	$.ajax({
		url: "uploadStudents.php",
		success: function(result) {
			$("#infoBox").html(result);
			$('#sessionTable').DataTable({"iDisplayLength":25});
			$('#sessionTable_filter input').focus();
		}
	});
}

function loadStudents() {
	$("#infoBox").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");
	$.ajax({
		url: "adminStudents.php",
		success: function(result) {
			$("#infoBox").html(result);
			$('#studentTable').DataTable({"iDisplayLength":25});
			$('#studentTable_filter input').focus();
		}
	});
}

function loadReports() {
	$("#infoBox").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");
	$.ajax({
		url: "adminreports.php",
		success: function(result) {
			$("#infoBox").html(result);
		}
	});
}

function loadPwdChange() {
	$("#infoBox").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");
	$.ajax({
		url: "adminPwdChange.php",
		success: function(result) {
			$("#infoBox").html(result);
		}
	});
}

function loadSettings() {
	$("#infoBox").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");
	$.ajax({
		url: "settings.php",
		success: function(result) {
			$("#infoBox").html(result);
		}
	});
}

function loadEmail() {
	$("#infoBox").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");
	$.ajax({
		url: "adminEmail.php",
		success: function(result) {
			$("#infoBox").html(result);
		}
	});
}

function adminUpdateSession(_session) {
	$.ajax({
		url: "adminSessionUpdate.php?i="+_session,
		success: function(result) {
			$("#editSessionContent").html(result);
			$('#_name').focus();
		}
	});	
}

function adminUpdateStudent(_studentID) {
	$.ajax({
		url: "adminStudentUpdate.php?i="+_studentID,
		success: function(result) {
			$("#editStudentContent").html(result);
			$('#lname').focus();
		}
	});	
}

function toggleActiveStudent(_studentID) {
	$("#infoBox").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");
	$.ajax({
		type: "POST",
		url: "adminStudentActiveToggle.php",
		data: {"i":_studentID},
		success: function(result) {
			console.log(result);
			loadStudents();
		}
	});	
}

function toggleActiveSession(_session) {
	$("#infoBox").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");
	$.ajax({
		type: "POST",
		url: "adminSessionActiveToggle.php",
		data: {"i":_session},
		success: function(result) {
			console.log(result);
			loadSessions();
		}
	});	
}	
function adminUpdateStudentSubmit() {
	var id = $("#editStudent input[name=id]").val();
	var lname = $("#editStudent input[name=lname]").val();
	var fname = $("#editStudent input[name=fname]").val();
	var email = $("#editStudent input[name=email]").val();
	var hmrm = $("#editStudent input[name=hmrm]").val();
	var regd = $("#editStudent input[name=regd]:checked").val();
	var active = $("#editStudent input[name=active]:checked").val();

	//ALL SESSION SELECTIONS
	var mySessions = $("[name^='session'] option:selected");	
	var sessions = new Array;
	for (var i=0; i < mySessions.length; i++) {
		sessions[i] = mySessions[i].value;
	}

	//ALL 'PAID' CHECKBOXES	
	//var paid = $("[name^=paid]:checked").val() == "on" ? 1:0;
	var paids = $("[name^=paid]");
	var paid = new Array;
	for (var i=0; i < paids.length; i++) {
		paid[i] = paids[i].checked ? 1 : 0;
	}
	
	//ALL PMT INFO
	var pp_pmts = $("[name^=pp_pmts]");
	var pmts = new Array;
	for (var i=0; i < pp_pmts.length; i++) {
		pmts[i] = pp_pmts[i].value;
	}

	//ALL OLD SESSIONS	
	var sessOldArr = $("[name^=sessOld]");
	var sessOlds = new Array;
	for (var i=0; i < sessOldArr.length; i++) {
		sessOlds[i] = sessOldArr[i].value;
	}
	
	$.ajax({
		type: "POST",
		url: "adminStudentUpdate.php",
		data: { 
			"id":id,
			"lname":lname,
			"fname":fname,
			"email":email,
			"hmrm":hmrm,
			"regd":regd,
			"active":active,
			"sessions":sessions,
			"paid":paid,
			"pp_pmts":pmts,
			"sessOld":sessOlds
		},
		success: function(msg) {
			$(function () {
			   $('#editStudent').modal('toggle');
			});
			loadStudents();
			console.log(msg);
		}
	});
 }

function adminAddToEmailQueue(_studID) {
	$("#infoBox").html("<div class='row text-center'><br/><br/><img src='loading.gif'/></div>");
	$('#editStudent').modal('toggle');
	$.ajax({
		type: "POST",
		url: "adminStudentUpdate.php",
		data: {
			"id":_studID,
			"f":"emailIndiv"
		      },
		success: function (result) {
			$("#infoBox").html("<h3 class='bg-success'>Email queued</h3>");
			setTimeout(function() { loadStudents(); }, 1000);
		}
	});
}

function adminUpdateSessionSubmit() {
	var id = $("#editSession input[name=id]").val();
	var name = $("#editSession input[name=_name]").val();
	var description = $("textarea#description").val();
	var cost = $("#editSession input[name=cost]").val();
	var pathToForm = $("#editSession input[name=pathToForm]").val();
	var block = $("#editSession input[name=block]:checked").val();
	var length = $("#editSession input[name=length]:checked").val();
	var supervisor = $("#editSession input[name=supervisor]").val();
	var secretary = $("#editSession input[name=secretary]").val();
	var presenter = $("#editSession input[name=presenter]").val();
	var room = $("#editSession input[name=room]").val();
	var capacity = $("#editSession input[name=capacity]").val();

	$.ajax({
		type: "POST",
		url: "adminSessionUpdate.php",
		data: {
			"id":id,
			"name":name,
			"description":description,
			"cost":cost,
			"pathToForm":pathToForm,
			"block":block,
			"length":length,
			"supervisor":supervisor,
			"secretary":secretary,
			"presenter":presenter,
			"room":room,
			"capacity":capacity
		},
		success: function(msg) {
			console.log(msg);
			$(function () {
			   $('#editSession').modal('toggle');
			});
			loadSessions();
		}
	});
 }

function alertOld(block) {
	var sessOldArr = $("[name^=sessOld]");
	var oldName = sessOldArr[block-1].value.split(",")[1];
	$("#session"+(block-1)+"alert").html("<p class='bg-danger'>Session" + block + " was previously " + oldName + "</p>");
}

