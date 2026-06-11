<?php
// Formatear precios a Soles
function precio($monto) {
    return "S/ " . number_format($monto, 2, '.', ',');
}

// Limpiar texto para evitar ataques (Seguridad)
function limpiar($datos) {
    global $conn;
    return $conn->real_escape_string(strip_tags($datos));
}
?>