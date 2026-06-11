<?php
// IP PRIVADA DE TU SERVIDOR DE BD
$mongo_ip = "172.31.36.27"; 

try {
    $manager = new MongoDB\Driver\Manager("mongodb://admin_bot:bot_password_123@$mongo_ip:27017/admin");
    $doc = ['usuario' => 'Prueba', 'mensaje' => 'Conexion establecida', 'fecha' => new MongoDB\BSON\UTCDateTime()];
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->insert($doc);
    $manager->executeBulkWrite('techstore_chat.mensajes', $bulk);
    echo "¡Conexión exitosa! Ya puedes ver esto en tu MongoDB.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
