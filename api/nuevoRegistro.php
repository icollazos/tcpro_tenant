<?
session_start(); // Inicia la sesión
header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
include('configurador.php');
$P=1;
$yaml = yaml_parse_file('../YAML_datatables.yaml');
$yaml=$yaml['root'];

$conn=conectar($datosConexion);
if (!$conn) {
    throw new Exception('No se pudo conectar a la base de datos.');
}
try {
    $datos=[];

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $data=$data['argumentos'];

    
    $campos=$data['campos'];
    $tabla=$data['tabla'];
//    $detona=$yaml['v_'.$tabla]['detona']?$yaml['v_'.$tabla]['detona']:'';


    $cols=' ( ';
    $values=' VALUES ( ';
    foreach ($campos as $key => $value) {
        $cols .= $key.', ';
        $values .= "'$value', ";
    }
    $cols=substr($cols, 0,-2).')';
    $values=substr($values, 0,-2).')';


    /*
    $sql = "ALTER TABLE $tabla ALTER COLUMN id SET DEFAULT nextval('nombre_tabla_id_seq');";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    regresar($sql);
    */

    $sql = "INSERT INTO $tabla $cols $values RETURNING id;";
    //regresar($sql);
    $stmt = $conn->prepare($sql);
    
    $stmt->execute();
    $ultimo_id=$stmt->fetchColumn();

    if ($tabla == "aaa_item") {
        $ultima = date('Y-m-d', strtotime('-30 days'));
        $sql = "UPDATE aaa_item SET ultimafecha = :ultima WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ultima', $ultima);
        $stmt->bindParam(':id', $ultimo_id);
        $stmt->execute();
    }


} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

echo json_encode($ultimo_id);


?>