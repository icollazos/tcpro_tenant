<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();  
include('configurador.php');
$P=1;
$conn=conectar($datosConexion);
$requestMethod = $_SERVER["REQUEST_METHOD"];
if ($requestMethod === 'GET') {
    if (isset($_GET['vista']) && isset($_GET['campo'])) {
        $vista = $_GET['vista'];
        $campo = $_GET['campo'];
        $campoDescriptor='descriptor_'.substr($campo, 3,100);
        $sql = "SELECT DISTINCT($campo) as id, $campoDescriptor as descriptor FROM $vista ORDER BY $campo ";
        //regresar($sql);
        $stmt = $conn->prepare($sql);
        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $datos[]=$row;
            }
        }
        echo json_encode($datos);
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Faltan parámetros", 'recibido'=>$_GET, 'sql'=>$sql ]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido"]);
}


die();

?>