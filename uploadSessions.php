<?php
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
		$sessions = array();
		$name = $_FILES['file']['name'];
		move_uploaded_file($_FILES['file']['tmp_name'], 'uploads/' . $name);

		$myfile = fopen('uploads/'.$name, "r") or die("Unable to open file! uploads/$name");
		//name,description,cost,formPDFlocation,block,linked,supervisor,secretary,presenter,room,capacity,buffer
		$test = fgetcsv($myfile);
		$test[0] = remove_bs($test[0]);
		if (strtolower($test[0]) != "name" || strtolower($test[1]) != "description" || strtolower($test[2]) != "cost" ||
			strtolower($test[3]) != "formpdflocation" || strtolower($test[4]) != "block" || strtolower($test[5]) != "linked" ||
			strtolower($test[6]) != "supervisor" || strtolower($test[7]) != "secretary" || strtolower($test[8]) != "presenter" ||
			strtolower($test[9]) != "room" || strtolower($test[10]) != "capacity" || strtolower($test[11]) != "buffer") {
				die("Invalid file format. First line should be\nname,description,cost,formPDFlocation,block,linked,supervisor,secretary,presenter,room,capacity,buffer");
		}
		$parsed = 1;	
		while(! feof($myfile)) {
			$new = new Session();
			$s = fgetcsv($myfile);
			if (feof($myfile)) continue;
			//print_r($s);

			$new->name=$s[0];	
			$new->desc=$s[1];
			$new->cost=$s[2];
			$new->forms=$s[3];
			$new->block=$s[4];
			$new->linked=$s[5];
			$new->supervisor=$s[6];
			$new->secretary=$s[7];
			$new->presenter=$s[8];
			$new->room=$s[9];
			$new->capacity=$s[10];
			$new->buffer=$s[11];
			$new->filled=0;
			$new->active=1;

			if ($new->name === "" || $new->cost === "") {
				$parsed = 0;
				echo "Failed parsing at ";
				print_r($new);
			}
			array_push($sessions,$new);
		}
		fclose($myfile);
	
		if ($parsed) echo "Parsed successfully\n";
			
		if ($parsed) {
			foreach($sessions as $sess) {
				//if ($query = $pdo->prepare("UPDATE  `students` SET  `password` =  :pwd  WHERE  `snum` = :snum")) {
				if ($query = $pdo->prepare("INSERT INTO `sessions`(`name`,`description`,`cost`,`forms`,`block`,`linked`,`supervisor`,`secretary`, `presenter`,`room`,`capacity`,`buffer`,`filled`,`active`) 
						VALUES (:name,:desc,:cost,:forms,:block,:linked,:supervisor,:secretary,:presenter,:room,:capacity,:buffer,:filled,:active)")) {
					$queryArray = array(
						"name"		=> $sess->name,
						"desc"		=> $sess->desc,
						"cost"		=> $sess->cost,
						"forms" 	=> $sess->forms,
						"block"		=> $sess->block,
						"linked"	=> $sess->linked,
						"supervisor" 	=> $sess->supervisor,
						"secretary" 	=> $sess->secretary,
						"presenter"	=> $sess->presenter,
						"room"		=> $sess->room,
						"capacity"	=> $sess->capacity,
						"buffer"	=> $sess->buffer,
						"filled"	=> $sess->filled,
						"active"	=> 1
					);
					$query->execute($queryArray);
					echo "Added\n";
					//print_r($queryArray);
				}
				else {
					echo "Bombed at $sess->name\n";
				}
			}
		}
	}

die();
}


?>
<h2>Bulk Session Upload</h2>
<pre>
First line should be
name,description,cost,formPDFlocation,block,linked,supervisor,secretary,presenter,room,capacity,buffer
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
    $.ajax({
                url: 'uploadSessions.php', // point to server-side PHP script 
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
