<?php
session_start(); // Inicia la sesión
header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
include('configurador.php');
error_reporting(0);
$P=1;
$conn=conectar($datosConexion);
if (!$conn) {
    throw new Exception('No se pudo conectar a la base de datos.');
}
$datos=[];

$id = $_GET['id']; 
$sql="SELECT * FROM v_aaa_texto WHERE id=$id";
$reg['sql1']=$sql;

$stmt = $conn->prepare($sql);
if ($stmt->execute()) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $datos[]=$row;
    }
}
$reg['datos']=$datos;

$reg['etiquetas']=[];

$sql="SELECT descriptor_variable_valor as variable, descriptor_valor_textovalor as valor, puntaje FROM v_aaa_textovalor WHERE id_texto_textovalor = '$id' ";
$reg['sqletiquetas']=$sql;
$e=[];
$stmt = $conn->prepare($sql);
if ($stmt->execute()) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $e[]=$row;
    }
}
$reg['etiquetas']=$e;

regresar($reg);
?>