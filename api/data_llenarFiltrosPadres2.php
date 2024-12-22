<?php
session_start(); // Inicia la sesión
header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
include('configurador.php');
$P=1;
$result = yaml_parse_file('../YAML_filtrosPadres.yaml');
$filtrosPadres=array_keys($result['root']);

$conn=conectar($datosConexion);
if (!$conn) {
    throw new Exception('No se pudo conectar a la base de datos.');
}

$datos=[];

foreach ($filtrosPadres as $filtro) {
    $sql = "SELECT id, descriptor FROM $filtro ";
    $stmt = $conn->prepare($sql);

    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[$filtro][]=$row;
        }
    }
}
echo json_encode($datos);
?>