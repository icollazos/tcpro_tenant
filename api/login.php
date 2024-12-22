<?php
session_start(); // Inicia la sesión
header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
include('configurador.php');
error_reporting(0);

$conn=conectar($datosConexion);
if (!$conn) {
    throw new Exception('No se pudo conectar a la base de datos.');
}

try {
    // Obtener los datos del POST
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $data=$data['argumentos'];
    //regresar($data['argumentos']);

    if ($data === null) {
        regresar(['error' => 'No se pudo decodificar el JSON']);
        exit;
    }

    // Acceder a los argumentos enviados
    $usuario = $data['usuario'] ?? $_GET['u']; 
    $clave = $data['clave'] ?? $_GET['c']; 

    $usuario = pg_escape_string($usuario);
        //$clave = md5(pg_escape_string($data['clave'])); // Hashing simple para la contraseña        
    $clave = pg_escape_string($clave); 

    $datosUsuario=array('usuario'=>$usuario, 'clave'=>$clave);
    $datosUsuario['id']=0;
    $datosUsuario['loggedIn']=false;            
    $datosUsuario['id_perfil_usuario']=false;

    if ($usuario && $clave) {

        // Consulta para verificar las credenciales del usuario
        $sql = "SELECT id, id_perfil_usuario FROM v_adm_usuario WHERE descriptor = '$usuario' AND clave = '$clave' ";

        $stmt = $conn->prepare($sql);

        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $idUsuario=$row['id'];
                $idPerfilUsuario=$row['id_perfil_usuario'];
            }
        }
        if(isset($idUsuario))  {
            $_SESSION['loggedIn']=true;
            $datosUsuario['id']=$idUsuario;
            $datosUsuario['id_perfil_usuario']=$idPerfilUsuario;
            $datosUsuario['loggedIn']=true;            
        } else {
            $datosUsuario['id']=false;
            $datosUsuario['loggedIn']=false;            
        }
    } else {
     $datosUsuario['loggedIn']=false;            
 }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
$_SESSION['datosUsuario']=$datosUsuario;
regresar($datosUsuario);
?>