<?php
require_once("functions.php");

function remove_bs($Str) {
  $StrArr = str_split($Str); $NewStr = '';
  foreach ($StrArr as $Char) {
    $CharNo = ord($Char);
    if ($CharNo == 163) { $NewStr .= $Char; continue; } // keep £
    if ($CharNo > 31 && $CharNo < 127) {
      $NewStr .= $Char;
    }
  }
  return $NewStr;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	require_once("database.php");
	require_once("functions.php");
	if ( !isset($_FILES) || empty($_FILES)){
		die("No file attached");
	}
	else {
		$students = array();
		$name = $_FILES['file']['name'];
		move_uploaded_file($_FILES['file']['tmp_name'], 'uploads/' . $name);

		$myfile = fopen('uploads/'.$name, "r") or die("Unable to open file! uploads/$name");
		
		$test = fgetcsv($myfile);
		$test[0] = remove_bs($test[0]);
		if (strtolower($test[0]) != "lastname" || strtolower($test[1]) != "firstname" || strtolower($test[2]) != "hmrm" ||
			strtolower($test[3]) != "snum" || strtolower($test[4]) != "email" || strtolower($test[5]) != "password")
			die("Invalid file format. First line should be\nlastname,firstname,hmrm,snum,email,password");
		$parsed = 1;	
		while(! feof($myfile)) {
			$newstud = new Student();
			$s = fgetcsv($myfile);
			if (feof($myfile)) continue;
			//print_r($s);
			if (!isset($s[0]) || !isset($s[1]) || !isset($s[2]) || !isset($s[3]) || !isset($s[4]) || !isset($s[5])) {
				$parsed = 0;
				print_r($s);
			}
			$newstud->lname=$s[0];
			$newstud->fname=$s[1];
			$newstud->hmrm=$s[2];
			$newstud->snum=$s[3];
			$newstud->email=$s[4];
			$newstud->password=$s[5];
			if ($newstud->lname === "" || $newstud->fname === "" || $newstud->hmrm === "" ||
				$newstud->snum === "" || $newstud->email === "" || $newstud->password === "")
				$parsed = 0;
			array_push($students,$newstud);
		}
	
		fclose($myfile);
	
		//all set to go? What to put for sessions / paid / pmts info?
		$emptyArray = array();
		$zeroArray = array();
		for($x=0;$x<$numSessions;$x++) {
			$emptyArray[$x]="";
			$zeroArray[$x]=0;
		}
		$emptyString = json_encode($emptyArray);		
		$zeroString = json_encode($zeroArray);
		if ($parsed) {
			foreach($students as $stud) {
				//if ($query = $pdo->prepare("UPDATE  `students` SET  `password` =  :pwd  WHERE  `snum` = :snum")) {
				if ($query = $pdo->prepare("INSERT INTO `students` (`snum`,`fname`,`lname`,`hmrm`,`email`,`active`,`password`,`paid`,`sessions`,`pp_pmts`)
					VALUES(:snum,:fname,:lname,:hmrm,:email,:active,:password,:paid,:sessions,:pp_pmts)")) {
					$queryArray = array(
						"snum" => trim($stud->snum),
						"fname" => trim($stud->fname),
						"lname" => trim($stud->lname),
						"hmrm" => trim($stud->hmrm),
						"email" => trim($stud->email),
						"password" => password_hash(trim($stud->password),PASSWORD_DEFAULT),
						"active" => 1,
						"paid" => $zeroString,
						"sessions" => $zeroString,
						"pp_pmts" => $emptyString
					);
					$query->execute($queryArray);
					echo $stud->lname.", ".$stud->fname." - Added\n";
					//print_r($queryArray);
				}
				else {
					echo "Bombed at $snum\n";
				}
			}
		}
	}

die();
}


?>
<h2>Bulk Student Upload</h2>
<h3>NOTE: DO NOT SUBMIT MORE THAN 500 AT A TIME</h3>
<pre>
First line should be
lastname,firstname,hmrm,snum,email,password
</pre>
<input id="uploadedFile" type="file" name="sortpic" />
<br/>
<button id="upload">Upload</button>
<hr/>
<pre>
<div id="responseBox"></div>
</pre>

<script>
$('#upload').on('click', function() {
    var file_data = $('#uploadedFile').prop('files')[0];   
    var form_data = new FormData();                  
    form_data.append('file', file_data);
    $('#responseBox').html("Loading... please wait (about 200 students/min)");
    $.ajax({
                url: 'uploadStudents.php', // point to server-side PHP script 
                dataType: 'text',  // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,                         
                type: 'post',
                success: function(php_script_response){
                    $('#responseBox').html(php_script_response);
                }
     });
});
</script>
