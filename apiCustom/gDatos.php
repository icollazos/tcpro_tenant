<?php

session_start(); // Inicia la sesión
//header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
include('../api/configurador.php');
include ('clausulasFiltroTextoValor.php');
error_reporting(E_ALL);
$P=1;
$conn=conectar($datosConexion);
if (!$conn) {
    throw new Exception('No se pudo conectar a la base de datos.');
}
//p(1,$_GET);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $buscar=$_GET['buscar'];
    switch ($buscar) { 
        case 'proyectos':
        $r=buscaProyectos($conn);
        break;
        case 'seguimientos':
        $r=buscaSeguimientos($conn);
        break;
        case 'variables':
        $r=buscaVariables($conn);
        break;
        case 'valores':
        $r=buscaValores($conn);
        break;
        case 'datosG1':
        $r=buscaDatosG1($conn);
        break;
        case 'datosNube':
        $r=buscaDatosNube($conn);
        break;
        case 'datosArbol':
        $r=buscaDatosArbol($conn);
        break;
        default:
            # code...
        break;
    }
    regresar($r);
} 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $data=$data['argumentos'];
    $buscar=$data['buscar'];
    switch ($buscar) {
        case 'x':
        break;
        default:
        break;
    }
    regresar($r);
} 


die();


function buscaDatosArbol($conn){

    $datos=[];
    $sql="SELECT CONCAT(id_seguimientoespejo_variable,'. ',descriptor_seguimientoespejo_variable) as seguimiento, CONCAT(id_variable_valor,'. ',descriptor_variable_valor) as variable, CONCAT(id_valor_dicvalor,'. ',descriptor_valor_dicvalor) as valor, CONCAT(id,'. ',lemapar) as lemapar FROM v_aaa_dicvalor WHERE id>1";
    $sql="SELECT DISTINCT  CONCAT(id_variable_valor,'. ',descriptor_variable_valor) as variable, CONCAT(id_valor_dicvalor,'. ',descriptor_valor_dicvalor) as valor FROM v_aaa_dicvalor WHERE id>1";
    $stmt = $conn->prepare($sql);
    $i=0;
    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[$i]=$row;
            $i++;
        }
    } 
    return($datos);
} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function buscaDatosNube($conn){

    $idvariable=$_GET['idvariable'];

    $idvalor = isset($_GET['idvalor']) ? $_GET['idvalor'] : '';
    $palabra = isset($_GET['palabra']) ? $_GET['palabra'] : '';
    $excepto = isset($_GET['excepto']) ? $_GET['excepto'] : '';
    $fi = isset($_GET['fechaInicial']) ? $_GET['fechaInicial'] : '';
    $ff = isset($_GET['fechaFinal']) ? $_GET['fechaFinal'] : '';

    $clausulaPalabras=clausulaPalabras($palabra);
    $clausulaValores=clausulaValores($idvalor);
    $clausulaExcepto=clausulaExcepto($excepto);
    $clausulaFechaI=clausulaFechaI($fi);
    $clausulaFechaF=clausulaFechaF($ff);



    $datos=[];
    $s=[];
    $sql="SELECT SUM(aaa_lemapar.relevancia) as cuenta, aaa_lemapar.descriptor as descriptor FROM aaa_texto INNER JOIN aaa_lemapar ON aaa_texto.id=aaa_lemapar.idaaa_texto INNER JOIN v_aaa_textovalor ON aaa_texto.id=v_aaa_textovalor.id_texto_textovalor WHERE v_aaa_textovalor.id_variable_valor = '$idvariable' $clausulaValores $clausulaPalabras $clausulaExcepto $clausulaFechaI $clausulaFechaF aND aaa_lemapar.lop='L' GROUP BY ( aaa_lemapar.descriptor ) ORDER BY cuenta DESC LIMIT 100";
    $reg[]=$sql;

    $stmt = $conn->prepare($sql);
    $i=0;
    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[$i]['cuenta']=$row['cuenta'];
            $datos[$i]['descriptor']=$row['descriptor'];
            $i++;
        }
    } 
    return($datos);
    return($clausulaValores);
    return($sql);
    return($reg);
} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function buscaDatosG1($conn){

    //return($_GET);
    $idvariable=$_GET['idvariable'];

    $idvalor = isset($_GET['idvalor']) ? $_GET['idvalor'] : '';
    $palabra = isset($_GET['palabra']) ? $_GET['palabra'] : '';
    $excepto = isset($_GET['excepto']) ? $_GET['excepto'] : '';
    $fi = isset($_GET['fechaInicial']) ? $_GET['fechaInicial'] : '';
    $ff = isset($_GET['fechaFinal']) ? $_GET['fechaFinal'] : '';
    $inputidvalor=isset($_GET['inputidvalor']) ? $_GET['inputidvalor'] : '';



    $clausulaPalabras=clausulaPalabras($palabra);
    $clausulaValores=clausulaValores($inputidvalor);
    $clausulaExcepto=clausulaExcepto($excepto);
    $clausulaFechaI=clausulaFechaI($fi);
    $clausulaFechaF=clausulaFechaF($ff);

    //return($clausulaValores);



    $datos=[];
    $s=[];
    $sql="SELECT count(v_aaa_textovalor.id) as cuenta, v_aaa_textovalor.descriptor_valor_textovalor as descriptor FROM v_aaa_textovalor INNER JOIN aaa_texto ON aaa_texto.id=v_aaa_textovalor.id_texto_textovalor WHERE id_variable_valor = '$idvariable' GROUP BY ( v_aaa_textovalor.descriptor_valor_textovalor );";
    $sql="SELECT count(v_aaa_textovalor.id) as cuenta, v_aaa_textovalor.descriptor_valor_textovalor as descriptor FROM v_aaa_textovalor INNER JOIN aaa_texto ON aaa_texto.id=v_aaa_textovalor.id_texto_textovalor WHERE id_variable_valor = '$idvariable' $clausulaValores $clausulaPalabras $clausulaExcepto $clausulaFechaI $clausulaFechaF GROUP BY ( v_aaa_textovalor.descriptor_valor_textovalor );";
    //return $clausulaPalabras.' '.$clausulaExcepto;
    //return($sql);

    $reg[]=$sql;

    $stmt = $conn->prepare($sql);
    $i=0;
    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[$i]['cuenta']=$row['cuenta'];
            $datos[$i]['descriptor']=$row['descriptor'];
            $i++;
        }
    } 
    return($datos);
    return($sql);
    return($clausulaValores);
    return($reg);
} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function buscaProyectos($conn){
    $sql="SELECT id, descriptor FROM v_aaa_proyecto;";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[]=$row;
        }
        return($datos);
    } 
}

function buscaSeguimientos($conn){
    $idproyecto=$_GET['idproyecto'];
    $sql="SELECT id, descriptor FROM aaa_seguimiento WHERE idaaa_proyecto='$idproyecto';";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[]=$row;
        }
        return($datos);
    } 
    return($sql);
}

function buscaVariables($conn){
    $idseguimiento=$_GET['idseguimiento'];
    $sql="SELECT id, descriptor FROM aaa_variable WHERE idaaa_seguimientoespejo='$idseguimiento';";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[]=$row;
        }
        return($datos);
    } 
    return($sql);
}

function buscaValores($conn){
    $idvariable=$_GET['idvariable'];
    $sql="SELECT id, descriptor FROM aaa_valor WHERE idaaa_variable='$idvariable';";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[]=$row;
        }
        return($datos);
    } 
    return($sql);
}




?>