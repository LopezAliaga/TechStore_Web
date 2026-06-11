<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); } 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resena_id']) && isset($_SESSION['usuario_id'])) {
    $resena_id = $_POST['resena_id'];
    $producto_id = (int)$_POST['producto_id'];
    
    $old_respuesta = htmlspecialchars_decode($_POST['old_respuesta'], ENT_QUOTES);
    $respuesta_autor = $_POST['respuesta_autor'];
    $nueva_respuesta = strip_tags(trim($_POST['nueva_respuesta']));
    
    $mongo_ip = "172.31.36.27";

    try {
        $manager = new MongoDB\Driver\Manager("mongodb://admin_bot:bot_password_123@$mongo_ip:27017/admin");
        $bulk = new MongoDB\Driver\BulkWrite;
        
        // Filtramos buscando el ID de la reseña madre Y la respuesta exacta
        $filtro = [
            '_id' => new MongoDB\BSON\ObjectId($resena_id),
            'respuestas.usuario' => $respuesta_autor,
            'respuestas.respuesta' => $old_respuesta
        ];
        
        // El símbolo $ significa "el elemento que acabo de encontrar"
        $actualizar = [
            '$set' => [
                'respuestas.$.respuesta' => $nueva_respuesta
            ]
        ];
        
        $bulk->update($filtro, $actualizar);
        $manager->executeBulkWrite('techstore_interacciones.resenas', $bulk);
        
        echo "<script>window.location.href='detalleProducto.php?id=$producto_id';</script>";
    } catch (Exception $e) {
        echo "Error al editar respuesta: " . $e->getMessage();
    }
}
?>