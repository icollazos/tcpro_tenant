<?php
session_start(); // Inicia la sesión
//header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
include('../api/configurador.php');
error_reporting(0);
error_reporting(E_ERROR); 
//die();
//regresar("eruheiruuwerip");

//reinicia los intentos una vez al dia

$conn=conectar($datosConexion);
if (!$conn) {
	throw new Exception('No se pudo conectar a la base de datos.');
}
$P=1;

$sql = "UPDATE aaa_item SET intentos = 0";
echo $sql;
$stmt = $conn->prepare($sql);
$stmt->execute();

die();



