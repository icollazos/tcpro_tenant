<?php
session_start(); // Inicia la sesión
//header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
include('../api/configurador.php');
//error_reporting(0);
$P=1;
$yaml = yaml_parse_file('../YAML_datatables.yaml');
$yaml=$yaml['root'];

$conn=conectar($datosConexion);
if (!$conn) {
	throw new Exception('No se pudo conectar a la base de datos.');
}
$_SESSION['datosConexion']=$datosConexion;

try {
	$sql="SELECT descriptor as descriptor, max(id) as max FROM aaa_dicvalor GROUP BY descriptor";
	echo $sql;
	$stmt = $conn->prepare($sql);
	if ($stmt->execute()) {
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$max=$row['max'];
			$descriptor=$row['descriptor'];
		}
	}
} catch (Exception $e) {
	echo json_encode(['error' => $e->getMessage()]);
}

$sql="UPDATE aaa_dicvalor SET lemapar = '$descriptor', lop = 'L'WHERE id='$max';";
echo $sql;
$stmt = $conn->prepare($sql);
if ($stmt->execute()) {}

regresar("EXITO");

?>