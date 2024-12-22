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


/*
try {
*/
    $datos=[];

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $data=$data['argumentos'];
    
    $clausulaSelects='';
    $selects=$data['selects'];
    foreach ($selects as $value) {
        $k=array_keys($value);
        $k=$k[0];
        $v=$value[$k];
        if($v!=0){
            $clausulaSelects .= " AND $k = '$v'"; 
        }
    }


    //regresar($_SESSION[$data['paginaOrigen']]);

    $vista = $data['vista'] ?? $_GET['vista']; 
    $vista = pg_escape_string($vista);


    //$_SESSION['valoresDefecto']['dt'][$vista]=$data;
    

    //$result = yaml_parse_file('../YAML_datatables.yaml');
    $t=file_get_contents('../YAML_datatables.json');
    $t = preg_replace('/[\r\n\t]+/', '', $t);
    $t = preg_replace('/\s+/', ' ', trim($t)); // Eliminar espacios consecutivos y trim para quitar espacios al inicio y final

    $t=json_decode($t);
    $campos=$t->root->$vista->campos;

    foreach ($campos as $key => $value) {
        $campos2[]=$key . ' as ' . str_replace(' ','_',$value);
    }

    $clausulaCampos=implode(", ", $campos2);


    $where=' WHERE (1=1) ';

    $fechaInicial=$data['fechaInicial'];
    $whereFi = (strlen($fechaInicial) === 10 ? " AND fechahora >= '$fechaInicial' " : '');
    //regresar($whereFi);

    $fechaFinal=$data['fechaFinal'];
    $whereFf = (strlen($fechaFinal) === 10 ? " AND fechahora <= '$fechaFinal' " : '');
    //regresar($whereFf);

    $data['palabra']=strtolower($data['palabra']);
    if(strlen($data['palabra']) > 0 ){
        $palabras=explode(",",$data['palabra']);
        foreach ($palabras as $key => $value) { $palabras[$key]="'%".trim(strtolower($value))."%'"; }
        $wherePalabras = " AND ( LOWER(descriptor) LIKE " . implode(" OR LOWER(descriptor) LIKE ", $palabras) . ' ) ';        
    } else {
        $wherePalabras = '';
    }
    //regresar($wherePalabras);

    $data['excepto']=strtolower($data['excepto']);
    if(strlen($data['excepto']) > 0 ){
        $excepto=explode(",",$data['excepto']);
        foreach ($excepto as $key => $value) { $excepto[$key]="'%".trim(strtolower($value))."%'"; }
        $whereExcepto = " AND ( LOWER(descriptor) NOT LIKE " . implode(" AND LOWER(descriptor) NOT LIKE ", $excepto) . ' ) ';
    } else {
        $whereExcepto='';
    }
    //regresar($whereExcepto);

    $padres=$data['selects'];
    //regresar($padres);

    $wherePadres='';
//regresar(count($padres));
    if(is_array($padres) && count($padres)>1){
        foreach ($padres as $p) {
            $k=array_keys($p);
            $k=$k[0];
            $v=$p[$k];
            if($v>0){
                $wherePadres .= " AND $k = '$v' ";
            }
        }        
    }
    //regresar("----");
    //regresar("----".$wherePadres);

    //$sql = "SELECT $clausulaCampos FROM $vista $where $whereFi $whereFf $wherePalabras $whereExcepto ORDER BY id DESC ";
    $sql = "SELECT $clausulaCampos FROM $vista $where $whereFi $whereFf $wherePalabras $whereExcepto $wherePadres $clausulaSelects ORDER BY id DESC ";

    $stmt = $conn->prepare($sql);

    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[]=$row;
        }
    }
/*
} catch (Exception $e) {
    $z[]=json_encode(['error' => $e->getMessage()]);
}
*/
//$z=[];
$z['sql']=$sql;
$z['resultados']=$datos;
$z['selects']=$clausulaSelects;
//$z['session']=$_SESSION['valoresDefecto'][$data['paginaOrigen']];
regresar($z);
?>