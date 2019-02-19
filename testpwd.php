<?php
require_once("database.php");
echo password_hash("1234",PASSWORD_DEFAULT);
//echo "\n\n";
//echo password_verify("4004121939",'2y$10$QogU5ERzKmTEdUuTjgWRzOIp6mqsCj03.GaaxsfMu0XG9Ra.GZxgK');
die();
$accts = array(
	"SXXXXXXXXX"=>"6135551212"
	);
print_r($accts);

foreach ($accts as $snum=>$pwd) {
	if ($query = $pdo->prepare("UPDATE  `students` SET  `password` =  :pwd  WHERE  `snum` = :snum")) {
		$queryArray = array(
			"snum" => $snum,
			"pwd" => password_hash($pwd,PASSWORD_DEFAULT)
		);
		$query->execute($queryArray);
	} 
	else {
		echo "Bombed at $snum\n";
	}


}
?>
