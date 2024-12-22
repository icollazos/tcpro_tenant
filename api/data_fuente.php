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
    $data=$data['json'];
    $tablasFuente=[];
    foreach ($data as $key => $value) {
        if(substr($key, 0,2)=='id'){
            $tablasFuente[]=substr($key, 2,1000);
        }
    }
    foreach ($tablasFuente as $tabla) {
       $alias=$data['id'.$tabla]['alias'];   
       $origen=$data['id'.$tabla]['origen'];   
       $padre=$data['id'.$tabla]['padre'];   
       $hijo=$data['id'.$tabla]['hijo'];   
       $p = $padre ? ', id'.$padre : '';
       $sql = "SELECT id , descriptor $p FROM $tabla;";
       $stmt = $conn->prepare($sql);
       if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[$tabla][]=$row;
        }
    }
}
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
regresar($datos);
?>