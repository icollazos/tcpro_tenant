<?php
session_start(); // Inicia la sesión
header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
include('configurador.php');
$P=1;
$conn=conectar($datosConexion);
if (!$conn) {
    throw new Exception('No se pudo conectar a la base de datos.');
}

try {
    $datos=[];

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $data=$data['argumentos'];

    $tabla = $data['tabla'] ?? $_GET['tabla']; 
    $tabla = pg_escape_string($tabla);

    $campoFiltro = $data['campoFiltro'] ?? $_GET['campoFiltro']; 
    $campoFiltro = pg_escape_string($campoFiltro);

    $valorFiltro = $data['valorFiltro'] ?? $_GET['valorFiltro']; 
    $valorFiltro = pg_escape_string($valorFiltro);

    $sql = "SELECT id, descriptor FROM $tabla WHERE $campoFiltro = '$valorFiltro' ";
    
    $stmt = $conn->prepare($sql);

    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[]=$row;
        }
    }
} catch (Exception $e) {
    echo json_encode($e->getMessage());
}

echo json_encode($datos);
?>