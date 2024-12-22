<?php
include('api/configurador.php');
$yaml=explode(",",'datatables,funcionesGenerales,menu,vistas,filtrosPadres,create');
foreach ($yaml as $y) {
	$result = yaml_parse_file('YAML_'.$y.'.yaml');
	$jsonContent = json_encode($result, JSON_PRETTY_PRINT);
	$jsonFilePath = 'YAML_'.$y.'.json';
	if (file_put_contents($jsonFilePath, $jsonContent) !== false) {
		echo "El archivo JSON ha sido guardado como: $jsonFilePath\n";
	} else {
		echo "Error al guardar el archivo JSON.\n";
	}
}

die();

$data = json_decode(file_get_contents('php://input'), true);
if (!$data){
	$fuente=$_GET['fuente'];
} else {
	$fuente = pg_escape_string($data['fuente']);
}

if ($fuente) {
	$result = yaml_parse_file('YAML_'.$fuente.'.yaml');
	regresar($result);
} else {
	echo json_encode(['error' => 'Datos no válidos']);
}

