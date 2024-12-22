<?php
session_start(); // Inicia la sesión
header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
include('../api/configurador.php');
error_reporting(0);


$conn=conectar($datosConexion);
if (!$conn) {
	throw new Exception('No se pudo conectar a la base de datos.');
}
$_SESSION['datosConexion']=$datosConexion;
$P=1;

// Eliminar registros donde id > 1
/*
$sqlDelete = "DELETE FROM aaa_texto WHERE id > 1";
try {
	$conn->exec($sqlDelete);
} catch (PDOException $e) {
	echo json_encode(["status" => "ERROR", "message" => "Error al eliminar registros: " . $e->getMessage()]);
	exit;
}
*/

// Seleccionar registros de v_aaa_item
$sqlSelect = "
SELECT 
id AS iditem,
descriptor AS item, 
q AS query, 
id_seguimiento_item AS idseguimiento, 
descriptor_seguimiento_item AS seguimiento,
id_fuente_seguimiento AS idfuente, 
descriptor_fuente_seguimiento AS fuente
FROM v_aaa_item
WHERE id > 1 
AND id_tipoSeguimiento_fuente = 2";

$palabras = array();
try {
	$stmt = $conn->prepare($sqlSelect);
	$stmt->execute();

    // Verificar si hay resultados
	if ($stmt->rowCount() > 0) {
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$palabras[$row['iditem']]['iditem'] = $row['iditem'];
			$palabras[$row['iditem']]['idfuente'] = $row['idfuente'];
			$palabras[$row['iditem']]['item'] = $row['item'];
            // Asignar el valor de 'query' si no está vacío
			$palabras[$row['iditem']]['query'] = !empty($row['query']) ? $row['query'] : $row['iditem'];
		}
	}
} catch (PDOException $e) {
	echo json_encode(["status" => "ERROR", "message" => "Error al seleccionar registros: " . $e->getMessage()]);
	exit;
}


$sql="SELECT aaa_dicdescarte.id, aaa_dicdescarte.descriptor FROM aaa_dicdescarte WHERE aaa_dicdescarte.idaaa_tipoidioma='0' OR aaa_dicdescarte.idaaa_tipoidioma='2'";

$dicDescarte = array();

try {
    // Ejecutar la consulta
	$stmt = $conn->prepare($sql);
	$stmt->execute();

    // Verificar si hay resultados
	if ($stmt->rowCount() > 0) {
        // Obtener los resultados como un array asociativo
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) { // FETCH_NUM para obtener un array numérico
            $dicDescarte[$row[0]] = $row[1]; // Asignar el primer campo como clave y el segundo como valor
        }
    }
} catch (PDOException $e) {
	echo "Error al ejecutar la consulta: " . $e->getMessage();
}


$fecha=date("Y-m-d", strtotime('now') );
$fecha=substr($fecha, 4, 6);
$mes=substr($fecha, 0,3);
$mes=str_replace(array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"), array("01","02","03","04","05","06","07","08","09","10","11","12"), $mes);
$fecha="2017".$mes."-". substr($fecha, 4, 2);
$analizado='NO';
$estado='PENDIENTE';


/*
$sql="DELETE FROM texto WHERE idaaa_item='$iditem'";
try {
    $sql = "DELETE FROM texto WHERE idaaa_item = :iditem RETURNING *"; 
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':iditem', $iditem, PDO::PARAM_INT);
    $stmt->execute();
    $deletedRows = $stmt->fetchAll(PDO::FETCH_ASSOC);    
} catch (PDOException $e) {
    echo "Error al ejecutar la consulta: " . $e->getMessage();
}
*/

#p($P,$sql);

foreach ($palabras as $k) {
	$iditem = $k['iditem'];
	$query = trim($k['query']);
	$query = strip_tags($query);

    // Reemplazar espacios por %20
	$query = str_replace(" ", "%20", $query);
    //$query="+venezuela+japon";

    // Inicializar la variable URL
	$url = '';

	switch ($k['idfuente']) {
		case 2:
		$url = "https://newsapi.org/v2/everything?apiKey=bc9aacf309b84b51ae926997ccbef62d&language=es&q=$query";
		ejecutarNewsApi($conn,$url, $iditem); 
		//regresar($url);
		break;

		case 3:
		$url = "https://newsdata.io/api/1/news?apikey=pub_51161c909c303f52b197087bc13d670a2cada$query";
		ejecutarNewsData($url, $iditem);
		break;

		default:
            // Manejar otros casos si es necesario
		break;
	}

    // Concatenar el iditem a la URL (esto puede no ser necesario según tu lógica)
	$url .= $k['iditem'];
}

die();

################################################################################################################

//header('Location: textoBandejaEntrada.php');
function ejecutarNewsData($url,$iditem){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'User-Agent: TCPRO/1.0' ]);
	$response = curl_exec($ch);
	if (curl_errno($ch)) {
		echo 'Error en cURL: ' . curl_error($ch);
	} else {
		$data = json_decode($response, true);
			//p(1,$data);
		$data=extraeTextosNewsData($data);
		$data=array_slice($data, 0, 10);
			//p(1,$data); // Muestra la respuesta
		foreach ($data as $registro) {
			cargaTextoIndividual($registro,$iditem);
					//break;
		}
	}
	curl_close($ch);
}
function extraeTextosNewsData($array){
	$array=$array['results'];
		//p(1,$array);
	$i=0;
	foreach ($array as $key => $value) {
		$t=trim($value['title']);
		$c=trim($value['title']);
		$tc=$t.'. '.$c;
		$tc= str_replace(array("\r", "\n"), '', $tc);
		$mapa = [
			'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U',
			'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
			'Ñ' => 'n', 'ñ' => 'n'
		];
		$tl = strtr($tc, $mapa);
		$z[$i]['texto']=$tc;
		$z[$i]['url']=$value['link'];
		$z[$i]['fecha']=substr($value['pubDate'],0,10);
		$z[$i]['textoLimpio']=$tl;
		$i++;
	}
	return $z;
}
function ejecutarNewsApi($conn,$url,$iditem){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'User-Agent: TCPRO/1.0' ]);
	$response = curl_exec($ch);
	if (curl_errno($ch)) {
		echo 'Error en cURL: ' . curl_error($ch);
	} else {
		$data = json_decode($response, true);
		//print_r($data);
		$data=extraeTextosNewsApi($data);
				#p(1,$data); // Muestra la respuesta
		foreach ($data as $registro) {
			cargaTextoIndividual($conn,$registro,$iditem);
		}
	}
	curl_close($ch);
}
function extraeTextosNewsApi($array){
	$array=$array['articles'];
	$i=0;
	foreach ($array as $key => $value) {
		$t=trim($value['title']);
		$c=trim($value['content']);
		$tc=$t.'. '.$c;
		$tc= str_replace(array("\r", "\n"), '', $tc);
		$mapa = [
			'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U',
			'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
			'Ñ' => 'n', 'ñ' => 'n'
		];
		$tl = strtr($tc, $mapa);
		$z[$i]['texto']=$tc;
		$z[$i]['url']=$value['url'];
		$z[$i]['fecha']=substr($value['publishedAt'],0,10);
		$z[$i]['textoLimpio']=$tl;
		$i++;
	}
	return $z;
}
function cargaTextoIndividual($conn, $registro, $iditem) {
	print_r($registro);
	$texto = $registro['texto'];
	$texto = str_replace(array("\r\n", "\r", "\n"), ' ', $texto);
    $textoLimpio = textoLimpio($texto); // Asegúrate de que esta función esté definida
    $urlTexto = $registro['url'];
    $controlador = rand(1000, 9999) . rand(1000, 9999) . rand(1000, 9999) . rand(1000, 9999);
    if ($urlTexto == '') {
    	$urlTexto = $controlador;
    }
    $longitud = strlen($texto);
    $fecha = $registro['fecha'];
    $analizado = 0;

    try {
    	$sql = "INSERT INTO aaa_texto (controlador, descriptor, textoLimpio, idaaa_item, fecha, analizado, url, longitud) VALUES ('$controlador', '$texto', '$textoLimpio', '$iditem', '$fecha', '$analizado', '$urlTexto', '$longitud')";  
    	$stmt = $conn->prepare($sql);
    	/*
    	$stmt->bindParam(':controlador', $controlador);
        $stmt->bindParam(':descriptor', $texto); // Asumiendo que 'descriptor' es el texto original
        $stmt->bindParam(':textoLimpio', $textoLimpio);
        $stmt->bindParam(':idaaa_item', $iditem);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':analizado', $analizado);
        $stmt->bindParam(':urlTexto', $urlTexto);
        $stmt->bindParam(':longitud', $longitud);
        */
        $r=[];
        if ($stmt->execute()) {
        	$sql2 = "SELECT MAX(id) as idActual FROM aaa_texto";
        	$result2 = $conn->query($sql2);
        	$r[]=$sql2;

        	if ($result2 && $result2->rowCount() > 0) {
        		while ($row = $result2->fetch(PDO::FETCH_ASSOC)) {
        			$idActual=$row['idactual'];
       				$sql3 = "UPDATE aaa_texto SET textoLimpio = '$textoLimpio' WHERE id = $idActual";
	        	$r[]=$sql3;
        			$tarzan=descartar($textoLimpio);
        			$r[]=$tarzan;
        			try {
        				$stmt3 = $conn->prepare($sql3);
        				if ($stmt3->execute()) {
        					$r[]="Registro actualizado correctamente.";
        				} else {
        					$r[]="ERROR: No se pudo actualizar el registro.";
        				}
        			} catch (PDOException $e) {
        				return "ERROR: " . $e->getMessage();
        			}
        			try {
        				$sql4 = "UPDATE aaa_texto SET textoTarzan = '$tarzan' WHERE id = $idActual";    
        				$r[]=$sql4;
        				$stmt4 = $conn->prepare($sql4);
        				/*
        				$stmt4->bindParam(':textoTarzan', $tarzan);
        				$stmt4->bindParam(':idActual', $idActual, PDO::PARAM_INT);
        				*/
        				if ($stmt4->execute()) {
        					$r[]="Registro actualizado correctamente.";
        				} else {
        					$r[]="ERROR: No se pudo actualizar el registro.";
        				}
        			} catch (PDOException $e) {
        				return "ERROR: " . $e->getMessage();
        			}
        			$r[]='LPLP';
        			$lemaPares=lemaPares($tarzan);
        			$r[]=$lemaPares;
        			$lemas=$lemaPares['lemas'];
        			$pares=$lemaPares['pares'];
        			$lemasCompacto=array_unique($lemas);
        			foreach ($lemasCompacto as $lc) {
        				$analisisLemas[$lc]['fi']=0;
        				foreach ($lemas as $lema) {
        					if($lema==$lc){$analisisLemas[$lc]['fi']++;}
        				}
        			}
        			foreach ($lemasCompacto as $l) {
        				foreach ($pares as $key => $value) {
        					if($key==$l){
        						foreach ($value as $key2 => $value2) {
        							$xx[$l][]=$key2;
        						}
        					}
        				}
        			}
        			foreach ($xx as $key => $value) {
        				$analisisLemas[$key]['numSocios']=count($value);
        				$analisisLemas[$key]['socios']=$pares[$key];
        				$analisisLemas[$key]['relevancia']=$analisisLemas[$key]['numSocios']*$analisisLemas[$key]['fi'];
        			}
        			foreach ($analisisLemas as $k=>$v) {
        				$lemaPar=$k;
        				$lop="L";
        				$lema1=$k;
        				$lema2=$k;
        				$relevancia=$v['relevancia'];
        				$numSocios=$v['numSocios'];
        				$idaaa_texto=$idActual;
        				try {
        					$sql5 = "INSERT INTO aaa_lemaPar (descriptor, lemaPar, lema1, lema2, lop, relevancia, numSocios, idaaa_texto) VALUES ('$lemaPar', '$lemaPar', '$lema1', '$lema2', '$lop', '$relevancia', '$numSocios', '$idaaa_texto')";
        					$r[]=$sql5;
        					$stmt5 = $conn->prepare($sql5);
        					/*
        					$stmt5->bindParam(':descriptor', $lemaPar);
        					$stmt5->bindParam(':lemaPar', $lemaPar);
        					$stmt5->bindParam(':lema1', $lema1);
        					$stmt5->bindParam(':lema2', $lema2);
        					$stmt5->bindParam(':lop', $lop);
        					$stmt5->bindParam(':relevancia', $relevancia);
        					$stmt5->bindParam(':numSocios', $numSocios);
        					$stmt5->bindParam(':idaaa_texto', $idActual);
        					*/
        					if ($stmt5->execute()) {
        						$r[]="Registro insertado correctamente.";
        					} else {
        						$r[]="ERROR: No se pudo insertar el registro.";
        					}
        				} catch (PDOException $e) {
        					$r[]="ERROR: " . $e->getMessage();
        				}
        			}
        			foreach ($pares as $key => $value) {
        				$lemaPar=$key.'-'.array_keys($value)[0];
        				$lop='P';
        				$lema1=$key;
        				$lema2=array_keys($value)[0];
        				$relevancia=$analisisLemas[$key]['relevancia']  + $analisisLemas[array_keys($value)[0]]['relevancia'];
        				$numSocios=0;
        				$idaaa_texto=$idActual;
        				try {
        					$sql6 = "INSERT INTO aaa_lemaPar (descriptor, lemaPar, lema1, lema2, lop, relevancia, numSocios, idaaa_texto) VALUES ('$lemaPar', '$lemaPar', '$lema1', '$lema2', '$lop', '$relevancia', '$numSocios', '$idaaa_texto')";
        					$r[]=$sql6;
        					$stmt6 = $conn->prepare($sql6);
        					/*
        					$stmt6->bindParam(':descriptor', $lemaPar);
        					$stmt6->bindParam(':lemaPar', $lemaPar);
        					$stmt6->bindParam(':lema1', $lema1);
        					$stmt6->bindParam(':lema2', $lema2);
        					$stmt6->bindParam(':lop', $lop);
        					$stmt6->bindParam(':relevancia', $relevancia);
        					$stmt6->bindParam(':numSocios', $numSocios);
        					$stmt6->bindParam(':idaaa_texto', $idActual);
        					*/
        					if ($stmt6->execute()) {
        						$r[]="Registro insertado correctamente.";
        					} else {
        						$r[]="ERROR: No se pudo insertar el registro.";
        					}
        				} catch (PDOException $e) {
        					$r[]="ERROR: " . $e->getMessage();
        				}
        			}
        		}
        	} else {
        		$r[]="ERROR: No se pudo obtener el ID del registro.";
        	}
        } else {
        	$r[]="ERROR: No se pudo insertar el registro.";
        }
    } catch (PDOException $e) {
    	$r[]="ERROR: " . $e->getMessage();
    }
    print_r($r);      
    //regresar("EXITO");
}

die();




function contarElementosRepetidos($array) {
	$conteo = array_count_values($array);
	$repetidos = array_filter($conteo, function($valor) {
		return $valor > 1;
	});    
	return $repetidos;
}
function lemaPares($t){
//	return("EWLIERVWEBVIEBVIYEWBVIYEVW");
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


function descartar($t) {
    $conn = conectar($_SESSION['datosConexion']);
        $dicDescarte = [];
    try {
        $sql = "SELECT descriptor FROM aaa_dicDescarte WHERE idaaa_tipoidioma = '2' or idaaa_tipoidioma = '0'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $dicDescarte[] = $row['descriptor'];
            }
        }
    } catch (PDOException $e) {
        echo "ERROR: " . $e->getMessage();
        return null; // O manejar el error de otra manera
    } finally {
        $conn = null;
    }
    $tarzan = explode(" ", $t);
    foreach ($tarzan as $key => $value) {
        if (in_array($value, $dicDescarte)) {
            $tarzan[$key] = "_"; // Reemplazar el valor por "_"
        }
    }
    return implode(" ", $tarzan);
}

function textoLimpio($t){
	$t=strtolower($t);
	$busca = 		['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ü', 'Ü'];
	$reemplaza = 	['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'n', 'n', 'u', 'u'];
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
function cargaSeguimientos($argumentos){
	$conn=conectar($_SESSION['datosConexion']);
	$idaaa_proyecto=$argumentos['idaaa_proyecto'];
	$sql="SELECT id, descriptor FROM aaa_seguimiento WHERE idaaa_proyecto=$idaaa_proyecto;";
	$i=0;
	if ($result = $conn->query($sql)) {
		if ($result->num_rows> 0){
			while ($row = $result->fetch_assoc()){
				$z[$i]['value']=$row['id'];
				$z[$i]['text']=$row['descriptor'];
				$i++;
			}
		}
		$result->close();
	}
	$conn->close();
	return $z;
}
function cargaItems($argumentos){
	$conn=conectar($_SESSION['datosConexion']);
	$idaaa_seguimiento=$argumentos['idaaa_seguimiento'];
	$sql="SELECT id, descriptor FROM aaa_item WHERE idaaa_seguimiento=$idaaa_seguimiento;";
	$i=0;
	if ($result = $conn->query($sql)) {
		if ($result->num_rows> 0){
			while ($row = $result->fetch_assoc()){
				$z[$i]['value']=$row['id'];
				$z[$i]['text']=$row['descriptor'];
				$i++;
			}
		}
		$result->close();
	}
	$conn->close();
	return $z;
}
function limpiar($t){ return $z; }	
function analizar($t){
	$z=dicDescarte();
	$z="cucucucuc";
	return $z;
}
function lemas($t){

	return $z;
}
function pares($t){

	return $z;
}
function dicDescarte($argumentos){
	$conn=conecta();
	return $conn;
	$clausulaMisDatos='';
	$sql="SELECT id, descriptor FROM aaa_dicDescarte WHERE idaaa_idioma='$idioma';";
	if ($result = $conn->query($sql)) {
		if ($result->num_rows> 0){
			while ($row = $result->fetch_assoc()){
				$z[$row['id']]=$row['descriptor'];
			}
		}
		$result->close();
	}
	$conn->close();
	return $z;
}

die();


