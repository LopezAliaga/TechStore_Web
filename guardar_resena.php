<?php
// Modo chismoso por si acaso
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) { session_start(); } 

// Verificamos que los datos lleguen por POST y que el usuario esté logueado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producto_id']) && isset($_SESSION['usuario_id'])) {
    
    // Recolectamos los datos del formulario
    $producto_id = (int)$_POST['producto_id'];
    $estrellas = (int)$_POST['estrellas'];
    
    // CORRECCIÓN: Quitamos el preg_replace que devolvía null. 
    // Usamos strip_tags para limpiar cosas maliciosas de código, pero guarda el texto intacto.
    $comentario = strip_tags(trim($_POST['comentario'])); 
    
    $nombre_usuario = $_SESSION['nombre']; 
    
    // IP DE TU MONGODB
    $mongo_ip = "172.31.36.27";

    try {
        $manager = new MongoDB\Driver\Manager("mongodb://admin_bot:bot_password_123@$mongo_ip:27017/admin");
        $bulk = new MongoDB\Driver\BulkWrite;

        // Estructura del documento NoSQL para la reseña
        $documento = [
            'producto_id' => $producto_id,
            'usuario'     => $nombre_usuario,
            'estrellas'   => $estrellas,
            'comentario'  => $comentario,
            'fecha'       => new MongoDB\BSON\UTCDateTime()
        ];

        // Lo insertamos en la colección 'resenas'
        $bulk->insert($documento);
        $manager->executeBulkWrite('techstore_interacciones.resenas', $bulk);

        echo "<script>
            alert('Valoracion guardada correctamente.');
            window.location.href = 'detalleProducto.php?id=" . $producto_id . "';
        </script>";
        exit();

    } catch (Exception $e) {
        echo "Error guardando la reseña en BD: " . $e->getMessage();
    }
} else {
    header("Location: productos.php");
    exit();
}
?>