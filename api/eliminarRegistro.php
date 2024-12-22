<?php
session_start(); 
include('configurador.php');
$P=1;

$conn=conectar($datosConexion);

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener el cuerpo de la solicitud
    $data = json_decode(file_get_contents("php://input"));


    // Validar que se haya recibido el ID
    if (isset($data->id)) {
        $id = $data->id; // ID del registro a eliminar
        $tabla = $data->vistaActual; // Valor del parámetro 'v'


        // Preparar la consulta para eliminar el registro
        $sql = "DELETE FROM $tabla WHERE id = $id"; // Usamos un placeholder para evitar inyecciones SQL
        $stmt = $conn->prepare($sql);

	    if ($stmt->execute()) {
	    	regresar(1);
	    }
    } else {
        // ID no proporcionado
        http_response_code(400);
        echo json_encode(array("message" => "ID no especificado."));
    }
}



// Verificar que la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    //regresar(implode(" ___ ", $_GET));
    // Obtener el ID y la vista actual desde los parámetros de la URL
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    $tabla = isset($_GET['vistaActual']) ? $_GET['vistaActual'] : null;

    // Validar que se haya recibido el ID
    if ($id !== null && $tabla !== null) {
        // Preparar la consulta para eliminar el registro
        $sql = "DELETE FROM $tabla WHERE id = $id"; // Usamos un placeholder para evitar inyecciones SQL
        //regresar($sql);
        $stmt = $conn->prepare($sql);

        if ($stmt->execute()) {
            regresar(1);
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Error al eliminar el registro."));
        }
    } else {
        // ID o tabla no proporcionados
        http_response_code(400);
        echo json_encode(array("message" => "ID o vista no especificados."));
    }
}



// Cerrar la conexión a la base de datos
pg_close($conn);
?>
