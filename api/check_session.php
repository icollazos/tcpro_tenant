<?php
session_start(); // Inicia la sesión
error_reporting(0);
header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
include('configurador.php');

try {
    // Verificar si hay una sesión activa
    if (isset($_SESSION['activa'])) {
        // Si hay un ID de usuario en la sesión, se considera que la sesión está activa
        echo json_encode(['loggedIn' => true]);
    } else {
        // No hay sesión activa
        echo json_encode(['loggedIn' => false]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

die();


