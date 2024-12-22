<?php
include('../api/configurador.php');
header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta
session_start(); // Inicia la sesión
regresar($_SESSION);
