<?php
include('api/configurador.php');
$params = json_decode(file_get_contents('php://input'), true);
if (!$params){
	$fuente=$_GET['fuente'];
} else {
	$fuente = pg_escape_string($params['fuente']);
}

if ($fuente) {
	$result = json_encode(file_get_contents($fuente));
	$result = file_get_contents($fuente);
	regresar($result);
} else {
	echo json_encode(['error' => 'Datos no válidos']);
}

