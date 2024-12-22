<?php
session_start(); // Inicia la sesión
header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
include('../api/configurador.php');
$P=1;
error_reporting(0);
$conn=conectar($datosConexion);
if (!$conn) {
    throw new Exception('No se pudo conectar a la base de datos.');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $buscar=$_GET['buscar'];
    switch ($buscar) { 
        case 'ejecutarRobot':
        $r=ejecutarRobot($conn);
        break;
        case 'reiniciarRobot':
        $r=reiniciarRobot($conn);
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
        case '':
        break;
        default:
        break;
    }
    regresar($r);
} 


die();

function ejecutarRobot($conn){
    $r=[];
    $sql = "DELETE FROM aaa_textovalor WHERE roh='R'" ;
    $conn->exec($sql); 
    $sql = "SELECT DISTINCT(aaa_dicrechazo.lemapar) AS lemapar, aaa_valor.id AS idvalor, aaa_valor.descriptor AS valor 
    FROM aaa_dicrechazo 
    INNER JOIN aaa_valor ON aaa_valor.id = aaa_dicrechazo.idaaa_valor 
    WHERE aaa_dicrechazo.lemapar IS NOT NULL;";
    $stmt = $conn->prepare($sql);
    $rechazos=[];
    $i = 0;
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $rechazos[$row['idvalor']]['idvalor'] = $row['idvalor'];
                $rechazos[$row['idvalor']]['valor'] = $row['valor'];
                $rechazos[$row['idvalor']]['lemaPares'][] = $row['lemapar'];
                $rechazosLP[$row['idvalor']][] = $row['lemapar'];
                $i++;
            }
        }
    }
    $sql = "SELECT DISTINCT(aaa_dicValor.lemapar) AS lemaPar, aaa_valor.id AS idValor, aaa_valor.descriptor AS valor 
    FROM aaa_dicvalor 
    INNER JOIN aaa_valor ON aaa_valor.id = aaa_dicValor.idaaa_valor 
    WHERE  aaa_dicvalor.lemapar IS NOT NULL ";
    $stmt = $conn->prepare($sql);
    $i = 0;
    if ($stmt->execute()) {
        $valores = [];
        $valoresLP = [];
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $valores[$row['idvalor']]['idvalor'] = $row['idvalor'];
                $valores[$row['idvalor']]['valor'] = $row['valor'];
                $valores[$row['idvalor']]['lemapares'][] = $row['lemapar'];
                $valoresLP[$row['idvalor']][] = $row['lemapar'];
                $i++;
            }
        }
    }
    $r[]=$valores;
    $sql = "SELECT DISTINCT(aaa_dicTextoValor.lemapar) AS lemapar, aaa_valor.id AS idvalor, aaa_valor.descriptor AS valor 
    FROM aaa_dictextovalor 
    INNER JOIN aaa_textovalor ON aaa_textovalor.id = aaa_dictextovalor.idaaa_textovalor 
    INNER JOIN aaa_valor ON aaa_valor.id = aaa_textovalor.idaaa_valor 
    WHERE aaa_dicTextoValor.lemapar IS NOT NULL;";
    $stmt = $conn->prepare($sql);
    $i = 0;
    if ($stmt->execute()) {
        //$valores = [];
        $valoresLP = [];
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $valores[$row['idvalor']]['idvalor'] = $row['idvalor'];
                $valores[$row['idvalor']]['valor'] = $row['valor'];
                $valores[$row['idvalor']]['lemapares'][] = $row['lemapar'];
                $valoresLP[$row['idvalor']][] = $row['lemapar'];
                $i++;
            }
        }
    }
/*
    $r[]=$valores;
    $r[]['valoresLP']=$valoresLP;
    */
    $sql = "SELECT DISTINCT(aaa_dicvalor.lemapar) AS lemapar, aaa_valor.id AS idvalor, aaa_valor.descriptor AS valor 
    FROM aaa_dicvalor 
    INNER JOIN aaa_valor ON aaa_valor.id = aaa_dicvalor.idaaa_valor 
    WHERE  aaa_dicvalor.lemapar IS NOT NULL;";
    $stmt = $conn->prepare($sql);
    $i = 0;
    if ($stmt->execute()) {
        //$valores = [];
        $valoresLP = [];
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $valores[$row['idvalor']]['idvalor'] = $row['idvalor'];
                $valores[$row['idvalor']]['valor'] = $row['valor'];
                $valores[$row['idvalor']]['lemapares'][] = $row['lemapar'];
                $valoresLP[$row['idvalor']][] = $row['lemapar'];
                $i++;
            }
        }
    }

    $r[]=$valores;
    $r[]['valoresLP']=$valoresLP;

    $sql = "SELECT aaa_lemapar.idaaa_texto AS idtexto, aaa_lemapar.id AS idlemapar, aaa_lemapar.lemapar AS lemapar, aaa_lemapar.relevancia AS relevancia 
    FROM aaa_lemapar 
    WHERE aaa_lemapar.idaaa_texto NOT IN ( 
        SELECT aaa_textovalor.idaaa_texto FROM aaa_textoValor WHERE aaa_textovalor.roh = 'H'
    )";
    $stmt = $conn->prepare($sql);
    $i = 0;
    if ($stmt->execute()) {
        $textos = [];
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $textos[$row['idtexto']][$row['lemapar']] = $row['relevancia'];
                $i++;
            }
        }
    }
    $data['v'] = $valoresLP;
    $data['t'] = $textos;
    foreach ($valoresLP as $key=>$valor) {
        foreach ($textos as $key2 => $texto) {
            $data['distanciaPositiva'][$key][$key2]=distancia($valor,$texto);
            $data['r'][$key][$key2]=$data['distanciaPositiva'][$key][$key2];
        }
    }
    if(isset($rechazosLP)){
        foreach ($rechazosLP as $key=>$valor) {
            foreach ($textos as $key2 => $texto) {
                $data['distanciaNegativa'][$key][$key2]=distanciaRechazando($valor,$texto);
                $data['r'][$key][$key2]=$data['r'][$key][$key2]-$data['distanciaNegativa'][$key][$key2];            
            }
        }
    }
    foreach ($data['r'] as $key => $value) {
        foreach ($value as $key2 => $value2) {
            if($value2>0){
                $data2[$key][$key2]=$value2;
            }
        }
    }
    foreach ($data2 as $key => $value) {
        foreach ($value as $key2 => $value2) {
            $sql = "INSERT INTO aaa_textoValor (idaaa_texto, idaaa_valor, roh, puntaje) VALUES ($key2, $key, 'R', $value2)";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute()) {
                echo "Error al insertar: " . htmlspecialchars($stmt->errorInfo()[2]);
            }
        }
    }
    return $data2;
}




function ejecutarRobot______($conn){
    $r=[];
    $idvariable = $_GET['idvariable'];
    $sql = "DELETE FROM aaa_textovalor WHERE roh='R'" ;
    $conn->exec($sql); 
    $sql = "SELECT DISTINCT(aaa_dicrechazo.lemapar) AS lemapar, aaa_valor.id AS idvalor, aaa_valor.descriptor AS valor FROM aaa_dicrechazo INNER JOIN aaa_valor ON aaa_valor.id = aaa_dicrechazo.idaaa_valor WHERE aaa_dicrechazo.lemapar IS NOT NULL;";
    $stmt = $conn->prepare($sql);
    $rechazos=[];
    $i = 0;
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $rechazos[$row['idvalor']]['idvalor'] = $row['idvalor'];
                $rechazos[$row['idvalor']]['valor'] = $row['valor'];
                $rechazos[$row['idvalor']]['lemaPares'][] = $row['lemapar'];
                $rechazosLP[$row['idvalor']][] = $row['lemapar'];
                $i++;
            }
        }
    }
    $sql = "SELECT DISTINCT(aaa_dicValor.lemapar) AS lemaPar, aaa_valor.id AS idValor, aaa_valor.descriptor AS valor FROM aaa_dicValor INNER JOIN aaa_valor ON aaa_valor.id = aaa_dicValor.idaaa_valor WHERE  aaa_dicvalor.lemapar IS NOT NULL ";
    $stmt = $conn->prepare($sql);
    $i = 0;
    if ($stmt->execute()) {
        $valores = [];
        $valoresLP = [];
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $valores[$row['idvalor']]['idvalor'] = $row['idvalor'];
                $valores[$row['idvalor']]['valor'] = $row['valor'];
                $valores[$row['idvalor']]['lemapares'][] = $row['lemapar'];
                $valoresLP[$row['idvalor']][] = $row['lemapar'];
                $i++;
            }
        }
    }
    $r[]=$valores;
    $sql = "SELECT DISTINCT(aaa_dicTextoValor.lemapar) AS lemapar, aaa_valor.id AS idvalor, aaa_valor.descriptor AS valor FROM aaa_dictextovalor INNER JOIN aaa_textovalor ON aaa_textovalor.id = aaa_dictextovalor.idaaa_textovalor INNER JOIN aaa_valor ON aaa_valor.id = aaa_textovalor.idaaa_valor WHERE aaa_dicTextoValor.lemapar IS NOT NULL;";
    $stmt = $conn->prepare($sql);
    $i = 0;
    if ($stmt->execute()) {
        //$valores = [];
        $valoresLP = [];
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $valores[$row['idvalor']]['idvalor'] = $row['idvalor'];
                $valores[$row['idvalor']]['valor'] = $row['valor'];
                $valores[$row['idvalor']]['lemapares'][] = $row['lemapar'];
                $valoresLP[$row['idvalor']][] = $row['lemapar'];
                $i++;
            }
        }
    }
    $sql = "SELECT DISTINCT(aaa_dicvalor.lemapar) AS lemapar, aaa_valor.id AS idvalor, aaa_valor.descriptor AS valor FROM aaa_dicvalor INNER JOIN aaa_valor ON aaa_valor.id = aaa_dicvalor.idaaa_valor WHERE  aaa_dicvalor.lemapar IS NOT NULL;";
    $stmt = $conn->prepare($sql);
    $i = 0;
    if ($stmt->execute()) {
        //$valores = [];
        $valoresLP = [];
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $valores[$row['idvalor']]['idvalor'] = $row['idvalor'];
                $valores[$row['idvalor']]['valor'] = $row['valor'];
                $valores[$row['idvalor']]['lemapares'][] = $row['lemapar'];
                $valoresLP[$row['idvalor']][] = $row['lemapar'];
                $i++;
            }
        }
    }

    $r[]=$valores;
    $r[]['valoresLP']=$valoresLP;
    //regresar($r);
    /*
    $sql = "SELECT idaaa_seguimientoEspejo AS idSeguimiento FROM aaa_variable WHERE id = $idvariable";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $idSeguimiento = $row['idseguimiento'];
            }
        }
    }
    $sql = "SELECT aaa_lemapar.idaaa_texto AS idtexto, aaa_lemapar.id AS idlemapar, aaa_lemapar.lemapar AS lemapar, aaa_lemapar.relevancia AS relevancia FROM aaa_lemapar INNER JOIN aaa_texto ON aaa_texto.id = aaa_lemapar.idaaa_texto INNER JOIN aaa_item ON aaa_item.id = aaa_texto.idaaa_item WHERE aaa_item.idaaa_seguimiento = $idSeguimiento AND aaa_lemapar.idaaa_texto NOT IN ( SELECT aaa_textovalor.idaaa_texto FROM aaa_textoValor WHERE aaa_textovalor.roh = 'H')";
    */
    $sql = "SELECT aaa_lemapar.idaaa_texto AS idtexto, aaa_lemapar.id AS idlemapar, aaa_lemapar.lemapar AS lemapar, aaa_lemapar.relevancia AS relevancia FROM aaa_lemapar WHERE aaa_lemapar.idaaa_texto NOT IN ( SELECT aaa_textovalor.idaaa_texto FROM aaa_textoValor WHERE aaa_textovalor.roh = 'H')";
    $stmt = $conn->prepare($sql);
    $i = 0;
    if ($stmt->execute()) {
        $textos = [];
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $textos[$row['idtexto']][$row['lemapar']] = $row['relevancia'];
                $i++;
            }
        }
    }
    $data['v'] = $valoresLP;
    $data['t'] = $textos;
    foreach ($valoresLP as $key=>$valor) {
        foreach ($textos as $key2 => $texto) {
            $data['distanciaPositiva'][$key][$key2]=distancia($valor,$texto);
            $data['r'][$key][$key2]=$data['distanciaPositiva'][$key][$key2];
        }
    }
    if(isset($rechazosLP)){
        foreach ($rechazosLP as $key=>$valor) {
            foreach ($textos as $key2 => $texto) {
                $data['distanciaNegativa'][$key][$key2]=distanciaRechazando($valor,$texto);
                $data['r'][$key][$key2]=$data['r'][$key][$key2]-$data['distanciaNegativa'][$key][$key2];            
            }
        }
    }
    foreach ($data['r'] as $key => $value) {
        foreach ($value as $key2 => $value2) {
            if($value2>0){
                $data2[$key][$key2]=$value2;
            }
        }
    }
    foreach ($data2 as $key => $value) {
        foreach ($value as $key2 => $value2) {
            $sql = "INSERT INTO aaa_textoValor (idaaa_texto, idaaa_valor, roh, puntaje) VALUES ($key2, $key, 'R', $value2)";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute()) {
                echo "Error al insertar: " . htmlspecialchars($stmt->errorInfo()[2]);
            }
        }
    }
    return $data2;
}



function borrarEtiqueta($argumentos){
    $conn=conectar($_SESSION['datosConexion']);
    $idTextoValor=$argumentos['idTextoValor'];
    $sql="DELETE FROM aaa_textoValor WHERE id='$idTextoValor'";
    if ($result = $conn->query($sql)) {
        return "Borrado con exito..." . $sql;
    } else {
        return $sql;
    }
}


function reiniciarRobot($conn){
    $idvariable=$_GET['idvariable'];
    $sql="DELETE FROM aaa_textovalor WHERE roh='R';";
    $i=0;
    if ($result = $conn->query($sql)) {
        return "Borrado con exito:    ".$sql;
    } else {
        return "Error en:     ".$sql;
    }
}


function eliminarTexto($argumentos){
    $conn=conectar($_SESSION['datosConexion']);
    $idTexto=$argumentos['idTexto'];
    $sql="DELETE FROM aaa_texto WHERE id='$idTexto'";
    $i=0;
    if ($result = $conn->query($sql)) {} 
        return "Borrado con exito";
}


function distancia($valor, $texto) {
    return array_sum(array_intersect_key($texto, array_flip($valor)));
}


function distanciaRechazando($rechazos,$texto){
    foreach ($rechazos as $v) {
        $puntaje+=$texto[$v];
    }
    return $puntaje;
}


function iniciarTablaV($argumentos) {

    //return(1);

    // Conectar a la base de datos
    $conn = conectar($_SESSION['datosConexion']);

    try {
        $idvariable = $argumentos['idvariable'];
        $idTexto = $argumentos['idTexto'];

        // Preparar la consulta SQL
        $sql = "SELECT aaa_valor.id AS id, aaa_valor.descriptor AS descriptor 
        FROM aaa_textoValor 
        INNER JOIN aaa_valor ON aaa_valor.id = aaa_textoValor.idaaa_valor 
        WHERE aaa_valor.idaaa_variable = $idvariable 
        AND aaa_textoValor.idaaa_texto = $idTexto";

        // Preparar la consulta
        $stmt = $conn->prepare($sql);

        // Vincular parámetros

        // Ejecutar la consulta
        if ($stmt->execute()) {
            $filas = []; // Inicializar el array para las filas
            // Comprobar si hay resultados
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $filas[] = [
                        'value' => $row['id'],
                        'text' => $row['descriptor']
                    ];
                }
            }
        } else {
            throw new Exception("Error al ejecutar la consulta.");
        }

    } catch (PDOException $e) {
        echo "Error: " . htmlspecialchars($e->getMessage());
    } 
    return $filas; // Devolver las filas obtenidas
}


function iniciarSelect($argumentos){
    $conn = conectar($_SESSION['datosConexion']);

    try {
        $idvariable = $argumentos['idvariable'];
        $idTexto = $argumentos['idTexto'];

    // Preparar la consulta SQL
        $sql = "SELECT aaa_valor.id AS id, aaa_valor.descriptor AS t 
        FROM aaa_valor 
        WHERE idaaa_variable = :idvariable 
        AND aaa_valor.id NOT IN (
        SELECT idaaa_valor FROM aaa_textoValor WHERE idaaa_texto = :idTexto
    )";

    // Preparar la consulta
    $stmt = $conn->prepare($sql);

    // Vincular parámetros
    $stmt->bindParam(':idvariable', $idvariable, PDO::PARAM_INT);
    $stmt->bindParam(':idTexto', $idTexto, PDO::PARAM_INT);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        $opciones = []; // Inicializar el array para las opciones
        // Comprobar si hay resultados
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $opciones[] = [
                    'value' => $row['id'],
                    'text' => $row['t']
                ];
            }
        }
    } else {
        throw new Exception("Error al ejecutar la consulta.");
    }

} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
} finally {
        // Cerrar la conexión si es necesario
        $conn = null; // Cerrar conexión si se ha abierto anteriormente.
    }

    // Devolver el array de opciones
    return $opciones;
}




function contarElementosRepetidos($array) {
    $conteo = array_count_values($array);
    $repetidos = array_filter($conteo, function($valor) {
        return $valor > 1;
    });    
    return $repetidos;
}


function lemaPares($t){
    $t=str_replace("_ ", "", $t);
    $oraciones=explode(".", $t);
    foreach ($oraciones as $oracion) {
        $lemas=explode(" ", $oracion);
        for ($i=0; $i < count($lemas)-1; $i++) { 
            if($lemas[$i]!='' AND $lemas[$i+1]!=''){
                if(!isset($pares[$lemas[$i]][$lemas[$i+1]])){ 
                    $pares[$lemas[$i]][$lemas[$i+1]]=0;
                }
                $pares[$lemas[$i]][$lemas[$i+1]]++;
            }
            if($lemas[$i]!='' AND $lemas[$i-1]!=''){
                if(!isset($pares[$lemas[$i]][$lemas[$i-1]])){
                    $pares[$lemas[$i]][$lemas[$i-1]]=0;
                }
                $pares[$lemas[$i]][$lemas[$i-1]]++;
            }
            if($lemas[$i]!=''){
                $l[]=$lemas[$i];
            }
        }
    }
    $z['lemas']=$l;
    $z['pares']=$pares;
    return $z;
}


function descartar($t){
    // Conectar a la base de datos
    $conn = conectar($_SESSION['datosConexion']);

    try {
        // Preparar la consulta SQL
        $sql = "SELECT descriptor FROM aaa_dicDescarte WHERE idaaa_idioma = :idioma";
        $stmt = $conn->prepare($sql);

        // Vincular el parámetro
        $idioma = 2; // Idioma específico
        $stmt->bindParam(':idioma', $idioma, PDO::PARAM_INT);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            $dicDescarte = []; // Inicializar el array para los descriptores
            // Comprobar si hay resultados
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $dicDescarte[] = $row['descriptor'];
                }
            }
        } else {
            throw new Exception("Error al ejecutar la consulta.");
        }

    } catch (PDOException $e) {
        echo "Error: " . htmlspecialchars($e->getMessage());
    } finally {
        // Cerrar la conexión si es necesario
        $conn = null; // Cerrar conexión si se ha abierto anteriormente.
    }

    // Procesar el texto
    $tarzan = explode(" ", $t);
    foreach ($tarzan as $key => $value) {
        if (in_array($value, $dicDescarte)) {
            $tarzan[$key] = "_"; // Reemplazar con un guion bajo si está en dicDescarte
        }
    }
    
    // Volver a unir el array en una cadena
    $tarzan = implode(" ", $tarzan);
    
    return $tarzan; // Devolver el texto procesado
}


function textoLimpio($t){
    $t=strtolower($t);
    $busca =        ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ü', 'Ü'];
    $reemplaza =    ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'n', 'n', 'u', 'u'];
    $t = str_replace($busca, $reemplaza, $t);
    $t = str_replace(array("\r\n", "\r", "\n"), ' ', $t);
    $patron = '/[^a-zA-Z0-9. ]/';
    $t = preg_replace($patron, '', $t);
    for ($i=0; $i < 5; $i++) { 
        $t = str_replace("..", ".", $t);
    }
    for ($i=0; $i < 5; $i++) { 
        $t = str_replace(".", " . ", $t);
    }
    for ($i=0; $i < 5; $i++) { 
        $t = str_replace("  ", " ", $t);
    }
    $t=trim($t);
    return $t;
}

function analizar($t){
    $z=dicDescarte();
    $z="cucucucuc";
    return $z;
}


function lemas($t){ return $z; }


function pares($t){ return $z; }


function dicDescarte($argumentos){
   // Conectar a la base de datos
    $conn = conectar($_SESSION['datosConexion']);
    try {
        // Inicializar el array para los resultados
        $z = [];
        // Preparar la consulta SQL
        $sql = "SELECT id, descriptor FROM aaa_dicDescarte WHERE idaaa_idioma = :idioma";
        $stmt = $conn->prepare($sql);
        // Vincular el parámetro
        $stmt->bindParam(':idioma', $idioma, PDO::PARAM_INT);
        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Comprobar si hay resultados
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $z[$row['id']] = $row['descriptor'];
                }
            }
        } else {
            throw new Exception("Error al ejecutar la consulta.");
        }
    } catch (PDOException $e) {
        echo "Error: " . htmlspecialchars($e->getMessage());
    } finally {
        // Cerrar la conexión si es necesario
        $conn = null; // Cerrar conexión si se ha abierto anteriormente.
    }
    return $z; // Devolver el array de descriptores
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

function limpiar($x){
    $x=strtolower($x);
    $busca =        ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ü', 'Ü'];
    $reemplaza =    ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'n', 'n', 'u', 'u'];
    $x = str_replace($busca, $reemplaza, $x);
    return preg_replace('/[^a-z0-9]/', '', $x);
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