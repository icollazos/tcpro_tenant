<?php
session_start(); // Inicia la sesión
header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
include('../api/configurador.php');
error_reporting(0);
error_reporting(E_ERROR); 
$P=1;
$conn=conectar($datosConexion);
if (!$conn) {
    throw new Exception('No se pudo conectar a la base de datos.');
}

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
        case 'textos':
        $r=buscaTextos($conn);
        break;
        case 'cargarValoresAsignados':
        $r=cargarValoresAsignados($conn);
        break;
        case 'palabrasFrecuentes':
        $r=palabrasFrecuentes($conn);
        break;
        case 'borrarSobrantes':
        $r=borrarSobrantes($conn);
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
        case 'grabar':
        $r=grabarTextoValor($conn,$data);
        break;
        default:
            # code...
        break;
    }
    regresar($r);
} 


die();

function borrarSobrantes($conn) {
    // Consulta para eliminar los sobrantes
    $sqlDelete = "UPDATE aaa_texto SET borrar=1 WHERE id NOT IN (SELECT id_texto_textovalor FROM v_aaa_textovalor)";
    $stmtDelete = $conn->prepare($sqlDelete);
    
    if ($stmtDelete->execute()) {
        // Consulta para actualizar el campo ultimafecha
        $fechaLimite = date('Y-m-d', strtotime('-90 days'));
        $sqlUpdate = "UPDATE aaa_texto SET ultimafecha = :fechaLimite";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':fechaLimite', $fechaLimite);
        
        if ($stmtUpdate->execute()) {
            return "EXITO BORRANDO Y ACTUALIZANDO FECHA";
        } else {
            return "ERROR AL ACTUALIZAR FECHA";
        }
    } else {
        return "ERROR AL BORRAR SOBRANTES";
    }
}
function palabrasFrecuentes($conn){
    $sql="SELECT SUM(relevancia) as suma, descriptor FROM v_aaa_lemapar WHERE lop='L' GROUP BY descriptor ORDER BY suma DESC LIMIT 100;";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        $q=0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[$q]['id']=$row['descriptor'];
            $datos[$q]['descriptor']=$row['descriptor'];
            $q++;
        }
        return($datos);
    } 
}


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
    //$idseguimiento=$_GET['idseguimiento'];
    $sql="SELECT id, descriptor FROM aaa_variable ";
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

function limpiar($x){
    $x=strtolower($x);
    $busca =        ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ü', 'Ü'];
    $reemplaza =    ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'n', 'n', 'u', 'u'];
    $x = str_replace($busca, $reemplaza, $x);
    return preg_replace('/[^a-z0-9]/', '', $x);
}


function buscaTextos($conn){
    $idseguimiento=$_GET['idseguimiento'];
    $idvariable=$_GET['idvariable'];
    $clausulaFechaI=EticlausulaFechaI($_GET['fechaInicial']);
    $clausulaFechaF=EticlausulaFechaF($_GET['fechaFinal']);
    $clausulaPalabras=EticlausulaPalabras($_GET['palabra']);
    $clausulaExcepto=EticlausulaExcepto($_GET['excepto']);
    $clausulaFrecuentes=EticlausulaFrecuentes($_GET['palabrasFrecuentes']);



    $datos=[];
    $s=[];
    $reg['clausulaPalabras']=$clausulaPalabras;
    $sql="SELECT v_aaa_textovalor.id_texto_textovalor as idtexto, v_aaa_textovalor.descriptor_texto_textovalor as descriptortexto, v_aaa_textovalor.id_valor_textovalor as idvalor, v_aaa_textovalor.roh as roh, v_aaa_textovalor.puntaje as puntaje, v_aaa_textovalor.descriptor_valor_textovalor as descriptorvalor, v_aaa_textovalor.id as id FROM v_aaa_textovalor WHERE id_variable_valor = '$idvariable' ORDER BY v_aaa_textovalor.id_texto_textovalor DESC;";
    
    $sql="SELECT v_aaa_textovalor.id_texto_textovalor as idtexto, v_aaa_textovalor.descriptor_texto_textovalor as descriptortexto, v_aaa_textovalor.id_valor_textovalor as idvalor, v_aaa_textovalor.roh as roh, v_aaa_textovalor.puntaje as puntaje, v_aaa_textovalor.descriptor_valor_textovalor as descriptorvalor, v_aaa_textovalor.id as id, aaa_texto.borrar as borrar FROM v_aaa_textovalor INNER JOIN aaa_texto ON aaa_texto.id=v_aaa_textovalor.id_texto_textovalor WHERE id_variable_valor = '$idvariable' AND v_aaa_textovalor.roh='H' $clausulaFechaI  $clausulaFechaF $clausulaPalabras $clausulaExcepto $clausulaFrecuentes ORDER BY v_aaa_textovalor.puntaje DESC;";
    //echo $sql;
    $reg['sqlH']=$sql;
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        $q=0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[$q]['id']=$row['id'];
            $datos[$q]['idtexto']=$row['idtexto'];
            $datos[$q]['descriptortexto']=substr($row['descriptortexto'],0,20);
            $datos[$q]['descriptortexto']=$row['descriptortexto'];
            $datos[$q]['idvalor']=$row['idvalor'];
            $datos[$q]['descriptorvalor']=$row['descriptorvalor'];
            $datos[$q]['roh']=$row['roh'];
            $datos[$q]['puntaje']=$row['puntaje'];
            $datos[$q]['borrar']=$row['borrar'];
            $q++;
        }
    } 
    $reg['datosH']=$datos;
    $datos=[];
    //$reg[]=$datos;

    $sql="SELECT v_aaa_textovalor.id_texto_textovalor as idtexto, v_aaa_textovalor.descriptor_texto_textovalor as descriptortexto, v_aaa_textovalor.id_valor_textovalor as idvalor, v_aaa_textovalor.roh as roh, v_aaa_textovalor.puntaje as puntaje, v_aaa_textovalor.descriptor_valor_textovalor as descriptorvalor, v_aaa_textovalor.id as id, aaa_textovalor.borrar as borrar FROM v_aaa_textovalor INNER JOIN aaa_textovalor ON aaa_textovalor.id=v_aaa_textovalor.id INNER JOIN aaa_texto ON aaa_texto.id=v_aaa_textovalor.id_texto_textovalor WHERE id_variable_valor = '$idvariable' AND v_aaa_textovalor.roh='R' $clausulaFechaI  $clausulaFechaF $clausulaPalabras $clausulaExcepto $clausulaFrecuentes ORDER BY v_aaa_textovalor.puntaje DESC;";
    //echo $sql;

    $reg['sqlR']=$sql;
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        $q=0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[$q]['id']=$row['id'];
            $datos[$q]['idtexto']=$row['idtexto'];
            $datos[$q]['descriptortexto']=substr($row['descriptortexto'],0,20);
            $datos[$q]['descriptortexto']=$row['descriptortexto'];
            $datos[$q]['idvalor']=$row['idvalor'];
            $datos[$q]['descriptorvalor']=$row['descriptorvalor'];
            $datos[$q]['roh']=$row['roh'];
            $datos[$q]['puntaje']=$row['puntaje'];
            $datos[$q]['borrar']=$row['borrar'];
            $q++;
        }
    } 
    $reg['datosR']=$datos;
    $datos=[];
    //$reg[]=$datos;

    $clausulaPalabras=EticlausulaPalabras_X($_GET['palabra']);

    $sql2="SELECT v_aaa_texto.id as idtexto, v_aaa_texto.descriptor as descriptortexto, aaa_texto.borrar as borrar FROM v_aaa_texto INNER JOIN aaa_texto ON aaa_texto.id=v_aaa_texto.id  WHERE (1=1) $clausulaPalabras AND aaa_texto.borrar=0 AND v_aaa_texto.id NOT IN (SELECT v_aaa_textovalor.id_texto_textovalor as idtexto FROM v_aaa_textovalor WHERE v_aaa_textovalor.id_variable_valor='$idvariable') ORDER BY v_aaa_texto.id ASC LIMIT 500;";
    //return($sql2);
    $reg['sqlX']=$sql2;
    $stmt = $conn->prepare($sql2);
    if ($stmt->execute()) {
        $q=0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[$q]['id']=$row['idtexto'];
            $datos[$q]['descriptortexto']= substr($row['descriptortexto'],0,20);
            $datos[$q]['descriptortexto']= $row['descriptortexto'];
            $datos[$q]['borrar']=$row['borrar'];
            $q++;
        }
    } 
    $reg['datosX']=$datos;
    $datos=[];


    $sql2="SELECT v_aaa_texto.id as idtexto, v_aaa_texto.descriptor as descriptortexto, aaa_texto.borrar as borrar FROM v_aaa_texto INNER JOIN aaa_texto ON aaa_texto.id=v_aaa_texto.id  WHERE (1=1) $clausulaPalabras AND aaa_texto.borrar=1 AND v_aaa_texto.id NOT IN (SELECT v_aaa_textovalor.id_texto_textovalor as idtexto FROM v_aaa_textovalor WHERE v_aaa_textovalor.id_variable_valor='$idvariable') ORDER BY v_aaa_texto.id ASC LIMIT 500;";
    //return($sql2);
    $reg['sqlD']=$sql2;
    $stmt = $conn->prepare($sql2);
    if ($stmt->execute()) {
        $q=0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[$q]['id']=$row['idtexto'];
            $datos[$q]['descriptortexto']= substr($row['descriptortexto'],0,20);
            $datos[$q]['descriptortexto']= $row['descriptortexto'];
            $datos[$q]['borrar']=$row['borrar'];
            $q++;
        }
    } 
    $reg['datosD']=$datos;
    $datos=[];


    $i=0;
    $array=[];




    return($reg);
    regresar($array);


} //////////////////////////////////////////////



function EticlausulaPalabras($x){
    $palabra = isset($x) ? $x : '';
    $clausulaPalabras = '';

    if ($palabra) {
        $palabrasArray = array_map('trim', explode(",", $palabra));
        $palabrasArray = array_map(function($word) {
        return "'%".strtolower($word)."%'"; 
    }, $palabrasArray);
        $clausulaPalabras = " AND ( LOWER(v_aaa_textovalor.descriptor_texto_textovalor) LIKE ";
        $clausulaPalabras .= implode(" AND LOWER(v_aaa_textovalor.descriptor_texto_textovalor) LIKE ", $palabrasArray) . ") ";
    }
    return($clausulaPalabras);
}   


function EticlausulaPalabras_X($x){
    $palabra = isset($x) ? $x : '';
    $clausulaPalabras = '';

    if ($palabra) {
        $palabrasArray = array_map('trim', explode(",", $palabra));
        $palabrasArray = array_map(function($word) {
        return "'%".strtolower($word)."%'"; 
    }, $palabrasArray);
        $clausulaPalabras = " AND ( LOWER(v_aaa_texto.descriptor) LIKE ";
        $clausulaPalabras .= implode(" AND LOWER(v_aaa_texto.descriptor) LIKE ", $palabrasArray) . ") ";
    }
    return($clausulaPalabras);
}   


function EticlausulaExcepto($x){
    $excepto = isset($x) ? $x : '';
    $clausulaExcepto = '';
    if ($excepto) {
        $exceptoArray = array_map('trim', explode(",", $excepto));
        $exceptoArray = array_map(function($word) {
        return "'%".strtolower($word)."%'"; 
    }, $exceptoArray);
        $clausulaExcepto = " AND ( LOWER(v_aaa_textovalor.descriptor_texto_textovalor) NOT LIKE ";
        $clausulaExcepto .= implode(" OR LOWER(v_aaa_textovalor.descriptor_texto_textovalor) NOT LIKE ", $exceptoArray) . ") ";
    }
    return($clausulaExcepto);
}    


function EticlausulaFechaI($x){
    $clausulaFechaI = ' AND aaa_texto.fechahora >= ' .'\'2000/01/01\'';
    if($x!=''){
    $clausulaFechaI = ' AND aaa_texto.fechahora >= \'' . $x . '\'';
    }
    return($clausulaFechaI);
}

function EticlausulaFechaF($x){
    $clausulaFechaF = ' AND aaa_texto.fechahora <= ' .'\'3000/01/01\'';
    if($x!=''){
    $clausulaFechaF = ' AND aaa_texto.fechahora <= \'' . $x . '\'';
    }
    return($clausulaFechaF);
}

function EticlausulaFrecuentes($x){
    $clausulaFrecuentes = ' ';
    if($x!='' AND $x!='0'){
    $clausulaFrecuentes = ' AND LOWER(v_aaa_textovalor.descriptor_texto_textovalor) LIKE \'%'.strtolower($x).'%\' ';
    }
    return($clausulaFrecuentes);
}



function creaTextoValor($conn, $argumentos) {
    // Conectar a la base de datos PostgreSQL

    $valor = $argumentos['valor'];
    $idTexto = $argumentos['idTexto'];
    $lemapares = $argumentos['lemapares'];
    $i = 0;
    $puntaje = count($lemapares);
    
    // Insertar en aaa_textoValor
    $sql = "INSERT INTO aaa_textoValor (roh, puntaje, idaaa_texto, idaaa_valor) VALUES ('H', :puntaje, :idTexto, :valor)";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':puntaje', $puntaje);
        $stmt->bindParam(':idTexto', $idTexto);
        $stmt->bindParam(':valor', $valor);
        $stmt->execute();
        
        // Obtener el ID máximo
        $sql = "SELECT MAX(id) as m FROM aaa_textoValor";
        $result = $conn->query($sql);
        if ($result) {
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $idMax = $row['m'];
        } else {
            return "ERROR: " . $sql;
        }

        // Procesar lemapares
        foreach ($lemapares as $key => $value) {
            if (isset($lemapares[$key + 1]) && ($lemapares[$key]['id'] + 1 == $lemapares[$key + 1]['id'])) {
                // Caso donde hay un par
                $LOP = "P";
                $x[$i]['t'] = $lemapares[$key]['value'];
                $x[$i]['lop'] = "L";
                $i++;
                
                // Concatenar valores
                $x[$i]['t'] = $lemapares[$key]['value'] . '-' . $lemapares[$key + 1]['value'];
                $x[$i]['lop'] = "P";
                $i++;
            } else {
                // Caso donde no hay par
                $LOP = "L";
                $x[$i]['t'] = $lemapares[$key]['value'];
                $x[$i]['lop'] = "L";
                $i++;
            }
        }

        // Insertar en aaa_dicTextoValor
        foreach ($x as $value) {
            $t = $value['t'];
            $lop = $value['lop'];
            // Preparar la consulta para insertar en aaa_dicTextoValor
            $sql = "INSERT INTO aaa_dicTextoValor (descriptor, lemaPar, LOP, idaaa_textoValor) VALUES (:descriptor, :lemaPar, :lop, :idMax)";
            
            // Ejecutar la inserción
            try {
                $stmt = $conn->prepare($sql);
                // Bind de parámetros
                $stmt->bindParam(':descriptor', $t);
                $stmt->bindParam(':lemaPar', $t);
                $stmt->bindParam(':lop', $lop);
                $stmt->bindParam(':idMax', $idMax);
                if (!$stmt->execute()) {
                    return "ERROR: " . implode(", ",$stmt->errorInfo());
                }
            } catch (PDOException $e) {
                return "ERROR: " . $e->getMessage();
            }
        }
        
        return ("EXITO");
        
    } catch (PDOException $e) {
        return "ERROR: " . $e->getMessage();
    }
}



?>