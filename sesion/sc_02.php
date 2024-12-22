<?php

$accountName = 'TU_CUENTA_STORAGE';
$accountKey = 'TU_CLAVE_STORAGE';

// Crear el manejador
$handler = new AzureStorageSession($accountName, $accountKey);
session_set_save_handler($handler, true);

// Iniciar sesión
session_start();

class AzureStorageSession implements SessionHandlerInterface {
    private $accountName;
    private $accountKey;
    private $containerName;

    public function __construct($accountName, $accountKey, $containerName = 'phpsessions') {
        $this->accountName = $accountName;
        $this->accountKey = $accountKey;
        $this->containerName = $containerName;
        $this->createContainerIfNotExists();
    }

    private function createContainerIfNotExists() {
        // Implementación para crear el contenedor si no existe
        $url = "https://{$this->accountName}.blob.core.windows.net/{$this->containerName}?restype=container";
        list(, $httpCode) = $this->sendRequest($url, 'PUT');
        if ($httpCode !== 201 && $httpCode !== 409) {
            throw new Exception("No se pudo crear el contenedor: {$this->containerName}");
        }
    }

    private function getAuthHeader($method, $contentLength, $contentType = '', $date = '') {
        $stringToSign = "$method\n\n$contentType\n$date\n/$this->accountName/$this->containerName";
        $signature = base64_encode(hash_hmac('sha256', utf8_encode($stringToSign), base64_decode($this->accountKey), true));
        return "SharedKey {$this->accountName}:$signature";
    }

    private function sendRequest($url, $method, $data = null) {
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $contentLength = $data ? strlen($data) : 0;
        $contentType = $data ? 'application/octet-stream' : '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Configuración de cabeceras
        $headers = [
            "x-ms-date: $date",
            "x-ms-version: 2020-04-08",
            "Authorization: " . $this->getAuthHeader($method, $contentLength, $contentType, $date)
        ];

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $headers[] = "Content-Type: $contentType";
            $headers[] = "Content-Length: $contentLength";
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Ejecutar la solicitud
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Error en la solicitud: ' . curl_error($ch));
        }
        
        // Obtener código de respuesta HTTP
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$response, $httpCode];
    }

    public function open($path, $name) {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($id) {
        // Leer datos de la sesión
        $url = "https://{$this->accountName}.blob.core.windows.net/{$this->containerName}/sess_$id";
        list($response, $httpCode) = $this->sendRequest($url, 'GET');

        if ($httpCode === 200) {
            return (string)$response; // Asegúrate de devolver una cadena
        }
        
        return ''; // Si no se encuentra la sesión
    }

public function write($id, $data) {
    // Escribir datos de la sesión
    $url = "https://{$this->accountName}.blob.core.windows.net/{$this->containerName}/sess_$id";
    
    // Llamar a sendRequest y capturar la respuesta
    $responseAndHttpCode = $this->sendRequest($url, 'PUT', (string)$data);
    
    // Verificar si se obtuvo una respuesta válida
    if (is_array($responseAndHttpCode) && count($responseAndHttpCode) >= 2) {
        list(, $httpCode) = $responseAndHttpCode;
        return ($httpCode === 201); // Retorna verdadero si se creó correctamente
    }
    
    return false; // Si no se pudo obtener el código HTTP
}

public function destroy($id) {
    // Eliminar sesión
    $url = "https://{$this->accountName}.blob.core.windows.net/{$this->containerName}/sess_$id";
    
    // Llamar a sendRequest y capturar la respuesta
    $responseAndHttpCode = $this->sendRequest($url, 'DELETE');
    
    // Verificar si se obtuvo una respuesta válida
    if (is_array($responseAndHttpCode) && count($responseAndHttpCode) >= 2) {
        list(, ) = $responseAndHttpCode; // No es necesario usar el primer valor
        return true; // No se necesita verificar el código de respuesta aquí
    }
    
    return false; // Si no se pudo obtener el código HTTP
}


    public function destroy($id) {
        // Eliminar sesión
        $url = "https://{$this->accountName}.blob.core.windows.net/{$this->containerName}/sess_$id";
        
        list(, ) = $this->sendRequest($url, 'DELETE');
        
        return true; // No se necesita verificar el código de respuesta aquí
    }

    public function gc($maxlifetime) {
       // Implementación simplificada para garbage collection
       // En un entorno real deberías listar los blobs y verificar su tiempo de creación/modificación.
       return true;
    }
}