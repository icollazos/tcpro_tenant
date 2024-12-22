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
	$json = file_get_contents('php://input');
	$data = json_decode($json, true);
	$data=$data['argumentos'];
	$id = $data['idTexto'] ?? $_GET['idTexto']; 
	$id = pg_escape_string($id);
	$sql="SELECT textolimpio as texto FROM aaa_texto WHERE id=$id";
	$stmt = $conn->prepare($sql);
	if ($stmt->execute()) {
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$texto=explode(" ", $row['texto']);
		}
	}
} catch (Exception $e) {
	echo json_encode(['error' => $e->getMessage()]);
}
$i=0;
foreach ($texto as $t) {
	$t2[$i]['palabra']=$t;
	$t2[$i]['tipo']=true;
	$i++;
}
regresar($t2);


?>