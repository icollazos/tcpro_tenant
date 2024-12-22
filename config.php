<?php
// Configuración global de sesiones
ini_set('session.save_handler', 'files');
ini_set('session.save_path', '/home/site/sessions');

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
