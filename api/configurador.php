<?php
error_reporting(0);
session_start();
 

$datosConexion=array(
	'host'=>'c-bd-ia.kb4xcdxantbq5p.postgres.cosmos.azure.com',
	'user'=>'citus',
	'password'=>'Megax123,',
	'port'=>'5432',
	'database'=>'citus'
);

$datosConexion=array(
	'host'=>'c-cldb-pocaud-dpi.mxbf6tfnm7lbzk.postgres.cosmos.azure.com',
	'user'=>'citus',
	'password'=>'Megax123,',
	'port'=>'5432',
	'database'=>'citus'
);

 
 $datosConexion=array(
	'host'=>'c-cldb-pocaud-dpi.mxbf6tfnm7lbzk.postgres.cosmos.azure.com',
	'user'=>'citus',
	'password'=>'Megax123,',
	'port'=>'5432',
	'database'=>'pocaud'
);
$datosConexion=array(
	'host'=>'localhost',
	'user'=>'postgres',
	'password'=>'123456',
	'port'=>'',
	'database'=>'tcpro6'
);
$datosConexion=array(
	'host'=>'c-cldb-pocaud-dpi.mxbf6tfnm7lbzk.postgres.cosmos.azure.com',
	'user'=>'citus',
	'password'=>'Megax123,',
	'port'=>'5432',
	'database'=>'pocaud'
); 
//$conn=conectar($datosConexion);
//p(1,$conn);


function p($p,$a) { 
	if($p){
		echo "<pre>"; 
		print_r($a);
		echo "</pre>"; 
	}
}; 

function conectar($datosConexion){
	$h=$datosConexion['host'];
	$d=$datosConexion['database'];
	$u=$datosConexion['user'];
	$p=$datosConexion['password'];
	try {
		$conn = new PDO("pgsql:host={$h};dbname={$d}", $u, $p);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (PDOException $e) {
		echo "Error de conexión: " . $e->getMessage();
	}
	return $conn;
}

function regresar($x){
	echo json_encode($x);
	die();
}

?>

