<?php
session_start(); // Inicia la sesión
//header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
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
$sqlSelect = "SELECT 
    id AS iditem,
    id_tipoitem_item AS idtipoitem,
    descriptor AS item, 
    q AS query, 
    id_seguimiento_item AS idseguimiento, 
    descriptor_seguimiento_item AS seguimiento,
    id_fuente_seguimiento AS idfuente, 
    descriptor_fuente_seguimiento AS fuente
FROM v_aaa_item
WHERE id > 1 
AND id_tipoSeguimiento_fuente = 2
AND status IS NULL
LIMIT 1";
pre($sql);

$palabras = array();
try {
	$stmt = $conn->prepare($sqlSelect);
	$stmt->execute();

    // Verificar si hay resultados
	if ($stmt->rowCount() > 0) {
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $palabras[$row['iditem']]['iditem'] = $row['iditem'];
            $palabras[$row['iditem']]['idtipoitem'] = $row['idtipoitem'];
			$palabras[$row['iditem']]['idfuente'] = $row['idfuente'];
			$palabras[$row['iditem']]['item'] = $row['item'];
            // Asignar el valor de 'query' si no está vacío
			$palabras[$row['iditem']]['query'] = !empty($row['query']) ? $row['query'] : $row['iditem'];
            $pp[]=$row['iditem'];
		}
	}
} catch (PDOException $e) {
	echo json_encode(["status" => "ERROR", "message" => "Error al seleccionar registros: " . $e->getMessage()]);
	exit;
}
//pre($palabras);
if(!isset($_SESSION['pal'])){
    $_SESSION['pal']=$pp;
}

//pre($palabras);
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
if($_SESSION['iniciado']!=true){
    $_SESSION['palabras']=$palabras;
    $_SESSION['iniciado']=true;
}

#p($P,$sql);
//pre($_SESSION['pal']);
foreach ($palabras as $k) {
    pre('--------------------------------------');
    pre($k);
    //$k=$palabras[$kk];
    $iditem = $k['iditem'];
    $tipoitem = $k['idtipoitem'];
    $query = trim($k['query']);
    $query = strip_tags($query);
    // Reemplazar espacios por %20
    $query = str_replace(" ", "%20", $query);
    //$query="+venezuela+japon";
    // Inicializar la variable URL
    $url = '';
    //pre($_SESSION);
    $q=0;
    if(!isset($_SESSION['page'])){
        $_SESSION['page']='.';
    }
    if($_SESSION['page']=='FIN'){
        $_SESSION['page']='.';
        /*
        if (($clave = array_search($kk, $_SESSION['pal'])) !== false) {
            unset($_SESSION['pal'][$clave]);
        }
        */        
    }
    while ($_SESSION['page']!='FIN') {
        $page='';
        if($_SESSION['page']!='.'){
            $page="&page=".$_SESSION['page'];
        }
        if ($k['idtipoitem']==2){
            $q='&country=cl&category='.$query;            
        }
        if ($k['idtipoitem']==3){
            $q="&country=cl&q=".$query;            
        }
        $q=$q.$page;            
        $from_date="&from_date=2024-09-01";
        $url = "https://newsdata.io/api/1/archive?apikey=pub_5999094320976f0c3bfbdacc9fc90e5ed30ad".$from_date.$q;
        pre($url);
        ejecutarNewsApi($conn,$url, $iditem); 
    }
    $q++;
}

die();

################################################################################################################

function pre($x){
    echo "<pre>";
    print_r($x);
    echo "</pre>";
}
function ejecutarNewsApi($conn,$url,$iditem){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'User-Agent: TCPRO/1.0' ]);
	$response = curl_exec($ch);
        //print_r("erugewrygierw");
	if (curl_errno($ch)) {
		echo 'Error en cURL: ' . curl_error($ch);
	} else {
		$data = json_decode($response, true);
        $_SESSION['page']=$data['nextPage'];
        if(!$data['nextPage']) {
            $_SESSION['page']='FIN';

            $sql="UPDATE aaa_item SET status=1 WHERE id=$iditem";
            pre($sql);
            $stmt = $conn->prepare($sql);
            if ($stmt->execute()) {}
        }
        //pre(array_keys($data));
        //pre($_SESSION['page']);
        $data=extraeTextosNewsApi($data);
		//pre($data); // Muestra la respuesta
        $i=0;
        foreach ($data as $registro) {
            cargaTextoIndividual($conn,$registro,$iditem);
            $i++;
        }
    }
    curl_close($ch);
    //pre("--------------------------");
}
function extraeTextosNewsApi($array){
	$array=$array['results'];
	$i=0;

	foreach ($array as $key => $value) {
        $campos=array_keys($value);
        foreach ($campos as $c) {
            if(is_array($value[$c])){
                $z[$i][$c]=implode(",",$value[$c]);
            } else {
                $z[$i][$c]=$value[$c];                
            }
            $z[$i][$c]=trim($z[$i][$c]);
            $z[$i][$c]= str_replace(array("\r", "\n"), '', $z[$i][$c]);
        }

        $mapa = [ 'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'Ñ' => 'n', 'ñ' => 'n' ];

        $z[$i]['content'] =  $z[$i]['title'] . ' '. $z['content'] ;
        $z[$i]['content'] = substr(str_replace(array("\r", "\n"), '', ( $z[$i]['content'] ) ) ,0, 2500);
        $z[$i]['content'] = str_replace("'", "", $z[$i]['content']);
        $z[$i]['content'] = str_replace('"', '', $z[$i]['content']);
        $z[$i]['pubdate'] =substr($value['pubDate'],0,10);
        $z[$i]['textolimpio'] = strtr($z[$i]['content'], $mapa);
        //pre($z[$i]['title']);
        $i++;
    }
    return $z;
}
function cargaTextoIndividual($conn, $registro, $iditem) {

    $r=$registro;

    $source = $registro['source'];
    $texto = $registro['texto'];
    $author = $registro['author'];
    $title = $registro['title'];
    $texto = str_replace(array("\r\n", "\r", "\n"), ' ', $texto);
    $textoLimpio = textoLimpio($texto); // Asegúrate de que esta función esté definida
    $urlTexto = $registro['url'];
    $controlador = rand(1000, 9999) . rand(1000, 9999) . rand(1000, 9999) . rand(1000, 9999);
    if ($urlTexto == '') {
    	$urlTexto = $controlador;
    }
    $b1='<a class="btn btn-sm btn-success" href="'.$urlTexto.'">Ver</a>';
    $b1=$urlTexto;
    $longitud = strlen($texto);
    $fecha = $registro['publishedAt'];
    $urlToImage = $registro['urlToImage'];
    $analizado = 0;

    try {
        $idaaa_source=1;

        $r['descriptor']=$r['content'];
        $r['fechahora']=$r['pubDate'];
        $r['borrar']=0;
        $r['textolimpio']=textoLimpio($r['textolimpio']);
        $r['analizado']=0;
        $r['longitud']=strlen($r['descriptor']);
        $r['textotarzan']='';
        $r['fecha']=$r['pubDate'];
        $r['controlador']=$r['article_id'];
        $r['fuente']=$r['source_name'];
        $r['idaaa_item']=$iditem;

        $sql = "INSERT INTO aaa_texto ( descriptor, fechahora, borrar, textolimpio, analizado, longitud, textotarzan, fecha, controlador, fuente, creator, title, link, article_id, source_id, image_url, keywords, video_url, description, content , ai_org, source_priority, source_name, source_url, source_icon, language, country, category, ai_tag, ai_region, sentiment, idaaa_item ) VALUES ( '".$r['descriptor']."', '".$r['fechahora']."', '".$r['borrar']."', '".$r['textolimpio']."', '".$r['analizado']."', '".$r['longitud']."', '".$r['textotarzan']."', '".$r['fecha']."', '".$r['controlador']."', '".$r['fuente']."', '".$r['creator']."', '".$r['title']."', '".$r['link']."', '".$r['article_id']."', '".$r['source_id']."', '".$r['image_url']."', '".$r['keywords']."', '".$r['video_url']."', '".$r['description']."', '".$r['content']." ', '".$r['ai_org']."', '".$r['source_priority']."', '".$r['source_name']."', '".$r['source_url']."', '".$r['source_icon']."', '".$r['language']."', '".$r['country']."', '".$r['category']."', '".$r['ai_tag']."', '".$r['ai_region']."', '".$r['sentiment']."', '".$r['idaaa_item']."');";
        $textoLimpio=$r['textolimpio'];

        $r[]=$sql;
        $stmt = $conn->prepare($sql);
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
            //pre($sql_q);
        	$r[]="ERROR: No se pudo insertar el registro.";
        }
    } catch (PDOException $e) {
    	$r[]="ERROR: " . $e->getMessage();
    }
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



