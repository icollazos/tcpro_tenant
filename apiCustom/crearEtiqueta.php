<?php
session_start(); // Inicia la sesión
header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
include('../api/configurador.php');
$P=1;
$conn=conectar($datosConexion);
if (!$conn) {
    throw new Exception('No se pudo conectar a la base de datos.');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $buscar=$_GET['buscar'];
    switch ($buscar) { 
        case 'variables':
        $r=buscaVariables($conn);
        break;
        case 'valores':
        $r=buscaValores($conn);
        break;
        case 'lpvalores':
        $r=buscaLPValores($conn);
        break;
        case 'cargarValoresAsignados':
        $r=cargarValoresAsignados($conn);
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
function buscaVariables($conn){
    $idtexto = $_GET['idtexto'];
    $sql="SELECT id, descriptor FROM v_aaa_variable";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[]=$row;
        }
        return($datos);
    } 
}

function buscaValores($conn){
    $idvariable = $_GET['idvariable'];
    $idtexto=$_GET['idtexto'];
    $sql="SELECT id_valor_textovalor as id, descriptor_valor_textovalor as descriptor FROM v_aaa_textovalor WHERE v_aaa_textovalor.id_variable_valor = '$idvariable' AND v_aaa_textovalor.id NOT IN (SELECT v_aaa_textovalor.id_valor_textovalor FROM v_aaa_textovalor );";
    $sql="SELECT v_aaa_valor.id as id, v_aaa_valor.descriptor as descriptor FROM v_aaa_valor WHERE v_aaa_valor.id_variable_valor = '$idvariable' AND v_aaa_valor.id NOT IN (SELECT v_aaa_textovalor.id_valor_textovalor FROM v_aaa_textovalor WHERE v_aaa_textovalor.id_texto_textovalor='$idtexto' );";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[]=$row;
        }
        regresar($datos);
    }
}

function limpiar($x){
    $x=strtolower($x);
    $busca =        ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ü', 'Ü'];
    $reemplaza =    ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'n', 'n', 'u', 'u'];
    $x = str_replace($busca, $reemplaza, $x);
    return preg_replace('/[^a-z0-9]/', '', $x);
}

function cargarValoresAsignados($conn){
 $idtexto = $_GET['idtexto'];
    $idvariable = $_GET['idvariable'];

    // Inicializar el array de datos
    $datos = [];

    // Usar parámetros en la consulta SQL para mayor seguridad
    $sql = "SELECT descriptor_valor_textovalor as d, lemapar as lp FROM v_aaa_dictextovalor WHERE id_variable_valor = :idvariable";
    $stmt = $conn->prepare($sql);
    
    // Asignar el parámetro
    $stmt->bindParam(':idvariable', $idvariable, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Concatenar correctamente los valores
            if (isset($datos[$row['d']])) {
                $datos[$row['d']] .= $row['lp'] . ' ';
            } else {
                $datos[$row['d']] = $row['lp'] . ' ';
            }
        }
    }

    return $datos;
}

function grabarTextoValor($conn, $data) {
    $idvalor = $data['idvalor'];
    $idtexto = $data['idtexto'];
    $l[0]['id']=27;
    $l[0]['value']='salsa';
    $lemapares = isset($data['lemapares']) ? $data['lemapares'] : $l;
    $i = 0;
    $puntaje = count($lemapares);
    $sql = "INSERT INTO aaa_textoValor (roh, puntaje, idaaa_texto, idaaa_valor) VALUES ('H', '$puntaje', '$idtexto', '$idvalor')";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $sql = "SELECT MAX(id) as m FROM aaa_textoValor";
        $result = $conn->query($sql);
        
        if ($result) {
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $idMax = $row['m'];
        } else {
            return "ERROR: " . $sql;
        }
        foreach ($lemapares as $key => $value) {
            if (isset($lemapares[$key + 1]) && ($lemapares[$key]['id'] + 1 == $lemapares[$key + 1]['id'])) {
                $LOP = "P";
                $x[$i]['t'] = $lemapares[$key]['value'];
                $x[$i]['lop'] = "L";
                $i++;
                $x[$i]['t'] = $lemapares[$key]['value'] . '-' . $lemapares[$key + 1]['value'];
                $x[$i]['lop'] = "P";
                $i++;
            } else {
                $LOP = "L";
                $x[$i]['t'] = $lemapares[$key]['value'];
                $x[$i]['lop'] = "L";
                $i++;
            }
        }
        foreach ($x as $value) {
            $lemapar = $value['t'];
            $lop = $value['lop'];
            $sql = "INSERT INTO aaa_dicTextoValor (descriptor, lemapar, LOP, idaaa_textoValor) VALUES ('$lemapar', '$lemapar', '$lop', '$idMax')";
            try {
                $stmt = $conn->prepare($sql);
                if (!$stmt->execute()) {
                    return "ERROR: " . implode(", ",$stmt->errorInfo());
                }
            } catch (PDOException $e) {
                return "ERROR: " . $e->getMessage();
            }

            $sql = "INSERT INTO aaa_dicvalor (descriptor, lemapar, LOP, idaaa_valor) VALUES ('$lemapar', '$lemapar', '$lop', '$idvalor')";
            try {
                $stmt = $conn->prepare($sql);
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

function buscaLPValores($conn){
    $idvalor = $_GET['idvalor'];
    $idtexto = $_GET['idtexto'];
    $sql = "SELECT id, descriptor, textolimpio, textotarzan FROM aaa_texto WHERE id = $idtexto";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $t['id'] = $row['id'];
            $t['descriptor'] = $row['descriptor'];
            $t['textoLimpio'] = $row['textolimpio'];
            $t['textoTarzan'] = $row['textotarzan'];
        }
    }
    $sql = "SELECT aaa_dicDescarte.descriptor as d FROM aaa_dicDescarte WHERE idaaa_tipoidioma = 2";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dd[] = strtolower($row['d']);
        }
    }
    $tt = explode(" ", $t['descriptor']);
    $q = 0;
    foreach ($tt as $key => $value) {
        $t2[$key]['original'] = $value;
        if (!in_array(strtolower($value), $dd)) {
            $t2[$key]['clase'] = "palabra";
            $t2[$key]['idnuevo'] = $q;
            $q++;
        } else {
            $t2[$key]['clase'] = "gris";
        }
        $t2[$key]['limpio'] = limpiar($value);
    }
    $t3='';
    foreach ($t2 as $key => $value) {
        $value['idnuevo'] = isset($value['idnuevo'])?$value['idnuevo']:false;
        $t3.= '&nbsp;<span id="'.$value['idnuevo'].'" value="'.$value['limpio'].'"class="'.$value['clase'].'"> '.$value['original'].'</span>';
    }
    return($t3); 
}




?>