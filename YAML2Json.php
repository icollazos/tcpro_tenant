<?php
include('api/configurador.php');
function convertYamlToJson($yamlFile) {
    // Leer el archivo YAML y convertirlo a un array
    $data = yaml_parse_file($yamlFile);
    
    // Comprobar si la lectura fue exitosa
    if ($data === false) {
        echo "Error al leer el archivo YAML: $yamlFile\n";
        return;
    }

    // Convertir el array a JSON
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo $json;
    // Generar el nombre del archivo JSON
    $jsonFile = preg_replace('/\.yaml$/', '.json', $yamlFile);
    
    // Guardar el JSON en un archivo
    file_put_contents($jsonFile, $json);
    
    echo "Convertido: $yamlFile -> $jsonFile\n";
}

// Directorio donde están los archivos YAML
$directory = ''; // Cambia esto por tu ruta

// Leer todos los archivos YAML en el directorio
foreach (glob("*.yaml") as $yamlFile) {
    convertYamlToJson($yamlFile);
}