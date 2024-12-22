<?php

class AzureStorageSession implements SessionHandlerInterface {
    private $accountName;
    private $accountKey;
    private $containerName;
    
    public function __construct($accountName, $accountKey, $containerName = 'phpsessions') {
        $this->accountName = $accountName;
        $this->accountKey = $accountKey;
        $this->containerName = $containerName;
    }

    private function getAuthHeader($method, $contentLength, $contentType = '', $date = '') {
        $stringToSign = "$method\n\n$contentType\n$date\n/$this->accountName/$this->containerName";
        $signature = base64_encode(hash_hmac('sha256', utf8_encode($stringToSign), base64_decode($this->accountKey), true));
        return "SharedKey $this->accountName:$signature";
    }

    private function sendRequest($url, $method, $data = null) {
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $contentLength = $data ? strlen($data) : 0;
        $contentType = $data ? 'application/octet-stream' : '';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $headers = array(
            "x-ms-date: $date",
            "x-ms-version: 2020-04-08",
            "Authorization: " . $this->getAuthHeader($method, $contentLength, $contentType, $date)
        );
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $headers[] = "Content-Type: $contentType";
            $headers[] = "Content-Length: $contentLength";
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return array($response, $httpCode);
    }

    public function open($path, $name) {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($id) {
        $url = "https://{$this->accountName}.blob.core.windows.net/{$this->containerName}/sess_$id";
        list($response, $httpCode) = $this->sendRequest($url, 'GET');
        
        if ($httpCode === 200) {
            return $response;
        }
        return '';
    }

    public function write($id, $data) {
        $url = "https://{$this->accountName}.blob.core.windows.net/{$this->containerName}/sess_$id";
        list(, $httpCode) = $this->sendRequest($url, 'PUT', $data);
        return $httpCode === 201;
    }

    public function destroy($id) {
        $url = "https://{$this->accountName}.blob.core.windows.net/{$this->containerName}/sess_$id";
        $this->sendRequest($url, 'DELETE');
        return true;
    }

    public function gc($maxlifetime) {
        // La implementación de gc es más compleja sin el SDK
        // Se podría implementar listando blobs y verificando metadata
        return true;
    }
}