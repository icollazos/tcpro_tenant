<?php
session_start(); // Inicia la sesión
//header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
include('../api/configurador.php');
//error_reporting(0);
$P=1;
$yaml = yaml_parse_file('../YAML_datatables.yaml');
$yaml=$yaml['root'];

$conn=conectar($datosConexion);
if (!$conn) {
	throw new Exception('No se pudo conectar a la base de datos.');
}
$_SESSION['datosConexion']=$datosConexion;

try {
	$json = file_get_contents('php://input');
	$data = json_decode($json, true);
	$data=$data['argumentos'];
	$id = $data['idNuevo'] ?? $_GET['idNuevo']; 
	$id = pg_escape_string($id);
	$sql="SELECT descriptor as texto FROM aaa_texto WHERE id=$id";
	$stmt = $conn->prepare($sql);
	if ($stmt->execute()) {
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$texto=$row['texto'];
		}
	}
} catch (Exception $e) {
	echo json_encode(['error' => $e->getMessage()]);
}

$texto = str_replace(array("\r\n", "\r", "\n"), ' ', $texto);
$idaaa_item=$argumentos['idaaa_item'];
$textoLimpio=textoLimpio($texto);
$analizado=0;
$url=rand(1000,9999).rand(1000,9999).rand(1000,9999);
$longitud=strlen($texto);

$sql="UPDATE aaa_texto SET descriptor = '$texto', textoLimpio = '$textoLimpio', analizado = '$analizado', url =  '$url', longitud =  '$longitud'  WHERE id='$id';";
$stmt = $conn->prepare($sql);
if ($stmt->execute()) {}


	$tarzan=descartar($textoLimpio);

$sql="UPDATE aaa_texto SET textoTarzan='$tarzan' WHERE id='$id'";
if ($result = $conn->query($sql)) { } else { return "ERROR: ".$sql; }




	$lemaPares=lemaPares($tarzan);
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
		$idaaa_texto=$id;
		$sql="INSERT INTO aaa_lemaPar (descriptor,lemaPar,lema1,lema2,lop,relevancia,numSocios,idaaa_texto) VALUES ('$lemaPar','$lemaPar','$lema1','$lema2','$lop','$relevancia','$numSocios','$id')";
		if ($result = $conn->query($sql)) { } else { return "ERROR: ".$sql; }
	}
	foreach ($pares as $key => $value) {
		$lemaPar=$key.'-'.array_keys($value)[0];
		$lop='P';
		$lema1=$key;
		$lema2=array_keys($value)[0];
		$relevancia=$analisisLemas[$key]['relevancia']  + $analisisLemas[array_keys($value)[0]]['relevancia'];
		$numSocios=0;
		$idaaa_texto=$id;
		$sql="INSERT INTO aaa_lemaPar (descriptor,lemaPar,lema1,lema2,lop,relevancia,numSocios,idaaa_texto) VALUES ('$lemaPar','$lemaPar','$lema1','$lema2','$lop','$relevancia','$numSocios','$id');";
		if ($result = $conn->query($sql)) { } else { return "ERROR: ".$sql; }	
	}

	regresar("EXITO");












function cargaTextoIndividual($argumentos){
	$conn=conectar($_SESSION['datosConexion']);
	$texto=$argumentos['texto'];
	$texto = str_replace(array("\r\n", "\r", "\n"), ' ', $texto);
	$idaaa_item=$argumentos['idaaa_item'];
	$textoLimpio=textoLimpio($texto);
	$analizado=0;
	$url=rand(1000,9999).rand(1000,9999).rand(1000,9999);
	$longitud=strlen($texto);
	
	$sql="INSERT INTO aaa_texto (descriptor,textoLimpio,analizado,url,longitud,idaaa_item) VALUES ('$texto','$textoLimpio','$analizado','$url','$longitud','$idaaa_item');";
	if ($result = $conn->query($sql)) { } else { return "ERROR: ".$sql; }
	
	$sql="SELECT id, textoLimpio FROM aaa_texto WHERE url='$url';";
	if ($result = $conn->query($sql)) {
		if ($result->num_rows> 0){
			while ($row = $result->fetch_assoc()){
				$id=$row['id'];
				$textoLimpio=$row['textoLimpio'];
			}
		}
		$result->close();
	} else {
		return "ERROR: ".$sql;
	}

	$tarzan=descartar($textoLimpio);

	$sql="UPDATE aaa_texto SET textoLimpio='$textoLimpio' WHERE id='$id'";
	if ($result = $conn->query($sql)) { } else { return "ERROR: ".$sql; }
	$sql="UPDATE aaa_texto SET textoTarzan='$tarzan' WHERE id='$id'";
	if ($result = $conn->query($sql)) { } else { return "ERROR: ".$sql; }

	$lemaPares=lemaPares($tarzan);
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
		$idaaa_texto=$id;
		$sql="INSERT INTO aaa_lemaPar (descriptor,lemaPar,lema1,lema2,lop,relevancia,numSocios,idaaa_texto) VALUES ('$lemaPar','$lemaPar','$lema1','$lema2','$lop','$relevancia','$numSocios','$id')";
		if ($result = $conn->query($sql)) { } else { return "ERROR: ".$sql; }
	}
	foreach ($pares as $key => $value) {
		$lemaPar=$key.'-'.array_keys($value)[0];
		$lop='P';
		$lema1=$key;
		$lema2=array_keys($value)[0];
		$relevancia=$analisisLemas[$key]['relevancia']  + $analisisLemas[array_keys($value)[0]]['relevancia'];
		$numSocios=0;
		$idaaa_texto=$id;
		$sql="INSERT INTO aaa_lemaPar (descriptor,lemaPar,lema1,lema2,lop,relevancia,numSocios,idaaa_texto) VALUES ('$lemaPar','$lemaPar','$lema1','$lema2','$lop','$relevancia','$numSocios','$id');";
		if ($result = $conn->query($sql)) { } else { return "ERROR: ".$sql; }	
	}

	$conn->close();
	return "EXITO";
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
	$conn=conectar($_SESSION['datosConexion']);
	$sql="SELECT descriptor FROM aaa_dicDescarte WHERE idaaa_tipoidioma='2';";


    $stmt = $conn->prepare($sql);

    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dicDescarte[]=$row['descriptor'];
        }
    }
	$tarzan=explode(" ", $t);
	foreach ($tarzan as $key => $value) {
		if(in_array($value, $dicDescarte)){
			$tarzan[$key]="_";
		}
	}
	$tarzan=implode(" ", $tarzan);
	return $tarzan;
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

function limpiar($t){

	return $z;
}	

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



?>