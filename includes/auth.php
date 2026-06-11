<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Función para proteger el panel de Admin
function soloAdmin() {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
        header("Location: login.php");
        exit();
    }
}

// Función para proteger el Carrito (solo clientes logueados)
function soloLogueados() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }
}
?>