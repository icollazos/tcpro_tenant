<?php
session_start(); // Inicia la sesión
//header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
include('../api/configurador.php');
error_reporting(0);
error_reporting(E_ERROR); 
//die();
//regresar("eruheiruuwerip");

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
//pre($palabras);
$sql = "SELECT id, textotarzan FROM aaa_texto WHERE id NOT IN (SELECT idaaa_texto FROM aaa_lemapar) ORDER BY id ASC LIMIT 10;";
$sql = "SELECT id, textotarzan FROM aaa_texto WHERE analizado=0 ORDER BY id ASC LIMIT 20 ";
//pre($sql);
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { 
            $textos[$row['id']] = $row['textotarzan'];
        }
    } else {
        die();
    }
} catch (PDOException $e) {
    regresar("Error al ejecutar la consulta: " . $e->getMessage());
}


foreach ($textos as $idaaa_texto => $tarzan) {


    //pre($idaaa_texto);
    $analisisLemas=array();

    $lemaPares=lemaPares($tarzan);
    //pre($lemaPares);
    $r[]=$lemaPares;
    $lemas=$lemaPares['lemas'];
    $pares=$lemaPares['pares'];
    if(is_array( $lemas )){






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
$umbral=0;



foreach ($analisisLemas as $k=>$v) {
    $lemaPar=$k;
    $lop="L";
    $lema1=$k;
    $lema2=$k;
    $relevancia=$v['relevancia'];
    $numSocios=$v['numSocios'];
    if($relevancia>=$umbral){        
        try {
         $sql5 = "INSERT INTO aaa_lemaPar (descriptor, lemaPar, lema1, lema2, lop, relevancia, numSocios, idaaa_texto) VALUES ('$lemaPar', '$lemaPar', '$lema1', '$lema2', '$lop', '$relevancia', '$numSocios', '$idaaa_texto')";
    //pre($sql5);
         $r[]=$sql5;
         $stmt5 = $conn->prepare($sql5);
         if ($stmt5->execute()) {
          $r[]="Registro insertado correctamente.";
      } else {
          $r[]="ERROR: No se pudo insertar el registro.";
      }
  } catch (PDOException $e) {
     $r[]="ERROR: " . $e->getMessage();
 }
}
}



foreach ($pares as $key => $value) {
    $lemaPar=$key.'-'.array_keys($value)[0];
    $lop='P';
    $lema1=$key;
    $lema2=array_keys($value)[0];
    $relevancia=$analisisLemas[$key]['relevancia']  + $analisisLemas[array_keys($value)[0]]['relevancia'];
    $numSocios=0;
    if($relevancia>=$umbral){        
        try {
         $sql6 = "INSERT INTO aaa_lemaPar (descriptor, lemaPar, lema1, lema2, lop, relevancia, numSocios, idaaa_texto) VALUES ('$lemaPar', '$lemaPar', '$lema1', '$lema2', '$lop', '$relevancia', '$numSocios', '$idaaa_texto')";
         $r[]=$sql6;
         $stmt6 = $conn->prepare($sql6);
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

}

$sql6 = "UPDATE aaa_texto SET analizado=1 WHERE id=$idaaa_texto";
//pre($sql6);
$stmt6 = $conn->prepare($sql6);
if ($stmt6->execute()) {} 

}
sleep(1);
header("Location:cron_analizar_2024_12_14.php");
die();

$sql="SELECT aaa_dicdescarte.id, aaa_dicdescarte.descriptor FROM aaa_dicdescarte WHERE aaa_dicdescarte.idaaa_tipoidioma='0' OR aaa_dicdescarte.idaaa_tipoidioma='2'";
$dicDescarte = array();
try {
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) { 
            $dicDescarte[$row[0]] = $row[1]; 
        }
    }
} catch (PDOException $e) {
	//echo "Error al ejecutar la consulta: " . $e->getMessage();
}

$dicDescarte = array(); 

$hoy = date("Y-m-d");
$sql = "SELECT id AS iditem, id_tipoitem_item AS idtipoitem, descriptor AS item, ultimafecha as ultimafecha FROM v_aaa_item WHERE id>'1' AND ultimafecha <> '$hoy' ORDER BY id ASC  LIMIT 1"; //regresar($sql);
$palabras = array();
$i=0;
try {
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[$i]['id']            =$row['iditem'];
            $items[$i]['idtipoitem']    = $row['idtipoitem'];
            $items[$i]['palabraQuery']  = $row['item'];
            $items[$i]['ultimafecha']   = $row['ultimafecha'];
            $i++;
        }

    }
} catch (PDOException $e) {
	//echo json_encode(["status" => "ERROR", "message" => "Error al seleccionar registros: " . $e->getMessage()]);
	exit;
}

$rango = 30;

$textos=array();
foreach ($items as $key => $value) {

    $x=0;

    $query = strip_tags($value['palabraQuery']);
    $query = str_replace(" ", "%20", $query);
    $url = '';
    $iditem=$value['id'];
    //pre($iditem);

    switch ($value['idtipoitem']) {
        case '2':
        $q='&country=cl&category='.$query;            
        break;
        case '3':
        $q="&country=cl&q=".$query;            
        break;
        default:
        break;
    }

    $ultimafecha=$value['ultimafecha'];

    $ultimafechaTimestamp = strtotime($ultimafecha);
    $fechaFinTimestamp = strtotime("+$rango days", $ultimafechaTimestamp);
    $fechaFin = date("Y-m-d", $fechaFinTimestamp);
    $fechaHoy = date("Y-m-d");
    if ($fechaFin > $fechaHoy) {
        $fechaFin = $fechaHoy;
    }
    $fechaInicioTimestamp = strtotime($ultimafecha);
    $fechaFinTimestamp = strtotime($fechaFin);

    for ($i = $fechaInicioTimestamp; $i <= $fechaFinTimestamp; $i += 86400) {
        $page = '';
        $fechaEstudio = date("Y-m-d", $i);
        $from_date = "&from_date=" . $fechaEstudio;
        $to_date = "&to_date=" . $fechaEstudio;
        $control = 1;
        while ($control == 1) {
            if (checkInternetConnection()) {
                pre("Si hay conexion ---");
                $url = "https://newsdata.io/api/1/archive?apikey=" . urlencode($apikey) . $from_date . $to_date . $q . $page;
                pre($url);
                $z = ejecutarNewsApi($conn, $url, $iditem);
                if(is_array($z['textos'])){
                    pre(count($z['textos']));
                } else {
                    pre("TOTAL: 0 Textos");
                }
                foreach ($z['textos'] as $t) {
                    $textos[$iditem][$x]=$t;
                    $x++;
                }
                if (isset($z['nextPage'])) {
                    $page = "&page=" . intval($z['nextPage']);
                } else {
                    break; 
                }
                if (isset($z['control'])) {
                    $control = $z['control'];
                } else {
                    break; 
                }
            } else {
            //echo "No hay conexión a Internet. Esperando...\n";
                sleep(5); 
            }
        }
        $sql = "UPDATE aaa_item SET ultimafecha = '$fechaEstudio' WHERE id=$iditem;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }
}

//pre($textos);




foreach ($textos as $iditem => $value) {
    foreach ($value as $keytexto => $texto) {

        cargaTextoIndividual($conn,$texto,$iditem);

    }
}

sleep(2);
//header("Location:http://localhost/tcpro4/apiCustom/cron_analizar_2024_12_14.php");
die();





function checkInternetConnection($host = 'www.google.com', $port = 80, $timeout = 10) {
    $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($connection) {
        fclose($connection);
        return true; // Hay conexión
    }
    return false; // No hay conexión
}



################################################################################################################

function pre($x){
    echo "<pre>";
    print_r($x);
    echo "</pre>";
}



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
        //echo "ERROR: " . $e->getMessage();
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


function queries(){
    $x="IMACEC,
    Indice mensual de actividad economica,
    PIB,
    Producto interno Bruto,
    RPM,
    Reunion de Politica  Monetaria,
    IPoM,
    Informe de politica monetaria,
    IEF,
    Informe de estabilidad financiera,
    RPF,
    Reunion de politica financiera,
    Rossana Costa,
    Presidenta Banco Central de Chile,
    Stefany Griffith Johns,
    Vicepresidenta Banco Central de Chile,
    Banco Central de Chile,
    Funcionarios Banco Central de Chile,
    Instituto emisor,
    Ejecutivos Banco Central de Chile,
    Consejo Banco Central de Chile,
    Tasa de Politica Monetaria,
    tasas de interes,
    Inflación,
    Inversión,
    Liquidez,
    EURO,
    DÓLAR,
    Cobre,
    Deuda,
    Coyuntura,
    Proyecciones economicas,
    Petroleo,
    Caso facturas,
    Factop,
    Fraude financiero,
    Robo millonario,
    Estabilidad financiera,
    Política Cambiaria,
    Política de Deuda,
    Política Financiera,
    Crecimiento,
    Crecimiento estructural,
    Fondo de estabilizacion,
    Deuda,
    Coyuntura,
    Proyecciones economicas,
    Octopuss S.A.,
    Prestadora de Servicios Cognosonline Limitada,
    TTH Comercios e Inversiones SpA,
    Comercializadora DMC SpA,
    BTCO S.A.,
    Comercial Tecnológico SpA,
    Insside Información Inteligente SpA,
    Asesorías y Consultoría Cybertrust Latam Limitada,
    Cybertrust,
    Peña e Hijos Ltda.,
    Servicios de Administración de Riesgos Ltda.,
    EY Consultores Limitada,
    EY,
    EY Servicios Profesionales de Auditoría y Asesorías Ltda.,
    Lizama Abogados y Compañía Limitada,
    Representaciones Aerotech SpA,
    Servicios Empresariales Global Management Solutions Limitada,
    Daycro Electrónica Ltda.,
    Surlatina Consultores en Gestión Limitada,
    Gallyas Telecom S.A.,
    KPMG Auditores Consultores Ltda.,
    KPMG  ,
    Prosegur Chile S.A,
    Inversiones Bursátiles S.A.,
    Depósito Central de Valores S.A. Depósito de Valores,
    TÜV Rheinland Chile S.A.,
    Dictuc S.A.,
    Laborum Chile Online S.A.,
    GPS Chile SpA,
    UBS Asset Management (Americas) Inc.,
    Allianz Global Investors GmbH,
    Aon Consulting, Inc.,
    Beyul Accounting Corporation,
    Bloomberg Finance LP,
    BNP Paribas Asset Management USA, Inc.,
    CGI IT UK Limited,
    EPFR, Inc.,
    F24 Servicios de Comunicación S.L.U.,
    F24,
    Global Projection Model Network,
    Industrial and Commercial Bank of China Limited,
    MarketAxess Corp.,
    Mercer Investment LLC,
    MSCI Limited,
    Tradeweb LLC,
    Servicios de Mantenimiento Instaplan Ltda.,
    E-Money Chile S.A.,
    Powerdata América Ltda.,
    Equinix Chile SpA,
    Hitachi Vantara (Chile) Limitada,
    Telefonica Empresas Chile S.A.,
    GTD Teleductos S.A,
    Empresa Nacional de Telecomunicaciones S.A.,
    Importaciones y Servicios Advanced Computing Technologies S.A.,
    HRI S.A.,
    Orange Business Services Chile S.A.,
    Comunicaciones y Tecnología S.A.,
    Central de Restaurantes Aramark Limitada,
    Neanderthal Refrigeración y Servicios Integrados Limitada,
    EBG Ingeniería y Construcción Limitada,
    Clínica MEDS La Dehesa SpA,
    FCOM SpA,
    H&H Constructora y Servicios Limitada,
    Morita Gourmet SpA,
    Constructora Deck SpA,
    Enetec SpA,
    Ingeniería de Servicios de Electricidad Computación, Asesorías y Proyectos SpA,
    Mam Promociones Limitada,
    Comercial Multiexpress SpA,
    Servicios de Aseo y Jardines Maclean Limitada,
    Constructora Lenti Limitada,
    Mago Chic Aseo Industrial S.A.,
    Sonda S.A.,
    Ingeniería e Innovación S.A.,
    Servicios de Respaldo de Energía Teknica Limitada,
    Novared Chile SpA,
    Elecnor Chile S.A.,
    Equans Servicios de Mantención SpA,
    Novis S.A.,
    Permaquim Chile SpA,
    Sociedad Comercial de Packaging SpA,
    Empresa de Transportes Compañía de Seguridad de Chile Ltda.,
    Brink´s Chile S.A.,
    Inkas Transporte de Valores SpA,
    Servicios Prosegur Ltda.,
    Wagner Seguridad, Custodia y Transportes de Valores SpA,
    ETV";
    return($x);
}

die();



