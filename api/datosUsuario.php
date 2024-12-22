<?php
include('configurador.php');
session_start(); // Inicia la sesión
header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
regresar($_SESSION['datosUsuario']);
