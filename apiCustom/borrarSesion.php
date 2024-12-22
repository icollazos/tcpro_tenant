<?php
include('../api/configurador.php');
session_start(); // Inicia la sesión
foreach ($_SESSION as $key => $value) {
	unset($_SESSION[$key]);
}
header("location:../index.html");