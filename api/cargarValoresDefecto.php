<?php
include('configurador.php');
session_start(); // Inicia la sesión
header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
error_reporting(0);

try {
    $vista = isset($_GET['vista']) ? $_GET['vista'] : null;
    $pagina = isset($_GET['pagina']) ? $_GET['pagina'] : null;
    $funcion = isset($_GET['funcion']) ? $_GET['funcion'] : null;

    // Inicialización de variables
    $q = 0;
    $i = array();
    $inputs = json_decode($_GET['inputs'], true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error al decodificar inputs JSON: ' . json_last_error_msg());
    }

    foreach ($inputs as $key => $value) {
        $i[$q]['id'] = $key;
        $i[$q]['valor'] = $value;
        $q++;
    }
    
    $r['inputs'] = $i;

    // Procesar selects
    $q = 0;
    $s = array();
    if (isset($_GET['selects'])){
	    $selects = json_decode($_GET['selects'], true);
	    
	    if (json_last_error() !== JSON_ERROR_NONE) {
	        throw new Exception('Error al decodificar selects JSON: ' . json_last_error_msg());
	    }

	    foreach ($selects as $key => $value) {
	        $s[$q]['id'] = $key;
	        $s[$q]['valor'] = $value;
	        $q++;
	    }
	    
    }
    $r['selects'] = $s;    	

    // Manejo de funciones
    switch ($funcion) {
        case 'get':
            if (!isset($_SESSION['valoresDefecto'][$pagina][$vista]['inputs'])) {
                $_SESSION['valoresDefecto'][$pagina][$vista]['inputs'] = $i;
            }
            if (!isset($_SESSION['valoresDefecto'][$pagina][$vista]['selects'])) {
                $_SESSION['valoresDefecto'][$pagina][$vista]['selects'] = $s;
            }
            break;

        case 'set':
            $_SESSION['valoresDefecto'][$pagina][$vista]['inputs'] = $i;
            $_SESSION['valoresDefecto'][$pagina][$vista]['selects'] = $s;
            break;

        default:
            throw new Exception('Función no válida: ' . htmlspecialchars($funcion));
            break;
    }

    // Preparar la respuesta
    if (isset($_SESSION['valoresDefecto'][$pagina][$vista])) {
        $r = $_SESSION['valoresDefecto'][$pagina][$vista];
        echo json_encode($r);
    } else {
        throw new Exception('No se encontraron valores por defecto.');
    }

} catch (Exception $e) {
    // En caso de error, devolver un mensaje de error en formato JSON
    echo json_encode(['error' => $e->getMessage()]);
}
