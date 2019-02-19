<?php
$mysql_hostname = "localhost";
$mysql_username = "wcss_disco";
$mysql_password = "WkAVlCsDVrXeeC2f";
$mysql_database = "wcss_discovery3";
$table_settings = "settings";

$dsn = "mysql:host=".$mysql_hostname.";dbname=".$mysql_database;

$debug = false;
try
{
	$pdo= new PDO($dsn, $mysql_username,$mysql_password, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
}
catch (PDOException $e)
{
	echo 'PDO error: could not connect to '.$mysql_database.' DB, error: '.$e;
}

//Load all settings
$settings = array();
if($query=$pdo->prepare("SELECT * FROM $table_settings")) {
		$queryArray = array(                                                                                                 
			
		);	
		$query->execute($queryArray);                                                                          
		while ($result = $query->fetch(PDO::FETCH_ASSOC)){
			$settings[$result['name']] = $result['value'];
		}
}
else {                                                                                                                            
	$error = $query->errorInfo();                                                                                        
	echo 'Could not read settings - MySQL Error: ' . $error;
	return false;
}
