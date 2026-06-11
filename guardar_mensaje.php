<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// SEGURIDAD: Si no está logueado, lo regresamos
if (!isset($_SESSION['usuario_id'])) {
    header("Location: chat.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$mongo_ip = "172.31.36.27"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['msg'])) {
    
    $mensajeUsuario = preg_replace('/[^a-zA-Z0-9\s?.,!]/', '', trim($_POST['msg']));
    $mensajeBot = "Lo siento, no logre entender tu consulta. Intenta escribir 'reembolso' o 'contacto' para poder ayudarte.";
    $inputLower = strtolower($mensajeUsuario);

    if (strpos($inputLower, 'reembolso') !== false || strpos($inputLower, 'devolucion') !== false) {
        $mensajeBot = "Para solicitar un reembolso, envia un correo a pagos@techstore.com con tu numero de orden. Te responderemos en 48 horas.";
    } elseif (strpos($inputLower, 'contacto') !== false || strpos($inputLower, 'soporte') !== false) {
        $mensajeBot = "Puedes contactarnos via WhatsApp al +51 999 888 777 de lunes a viernes.";
    } elseif (strpos($inputLower, 'tienda') !== false || strpos($inputLower, 'horario') !== false) {
        $mensajeBot = "Nuestra tienda fisica esta en Av. Las Flores 456, Lima. Atendemos de lunes a sabado.";
    } elseif (strpos($inputLower, 'hola') !== false || strpos($inputLower, 'buenos') !== false) {
        $mensajeBot = "Hola! Que gusto saludarte. En que te puedo asesorar el dia de hoy?";
    } elseif (strpos($inputLower, 'envio') !== false || strpos($inputLower, 'delivery') !== false) {
        $mensajeBot = "Hacemos envios a todo el Peru. En Lima es gratis y a provincias cuesta 15 soles.";
    } elseif (strpos($inputLower, 'garantia') !== false) {
        $mensajeBot = "Todos nuestros componentes tienen 12 meses de garantia directo de fabrica.";
    } elseif (strpos($inputLower, 'pago') !== false || strpos($inputLower, 'tarjeta') !== false || strpos($inputLower, 'yape') !== false) {
        $mensajeBot = "Aceptamos Yape, Plin, transferencias y todas las tarjetas de credito o debito.";
    }

    try {
        $manager = new MongoDB\Driver\Manager("mongodb://admin_bot:bot_password_123@$mongo_ip:27017/admin");
        $bulk = new MongoDB\Driver\BulkWrite;

        $id_usuario = $_SESSION['usuario_id'];
        $nombre_usuario = $_SESSION['nombre'];

        $documento = [
            'usuario_id'      => $id_usuario, // Amarrado a tu cuenta
            'usuario_mensaje' => $mensajeUsuario,
            'bot_respuesta'   => $mensajeBot,
            'fecha'           => new MongoDB\BSON\UTCDateTime()
        ];

        $bulk->insert($documento);
        $manager->executeBulkWrite('techstore_chat.mensajes', $bulk);

        // Agregamos tu nombre en vivo al chat cuando envías el mensaje
        echo "<script>
            var cajaMensajes = parent.document.getElementById('mensajes');
            if(cajaMensajes) {
                cajaMensajes.innerHTML += '<p class=\"msg-line user-msg\"><b>" . addslashes($nombre_usuario) . ":</b> " . addslashes($mensajeUsuario) . "</p>';
                cajaMensajes.innerHTML += '<p class=\"msg-line bot-msg\"><b>Bot:</b> " . addslashes($mensajeBot) . "</p>';
                cajaMensajes.scrollTop = cajaMensajes.scrollHeight;
            }
            window.location.href = 'chat.php';
        </script>";
        exit();

    } catch (Exception $e) {
        echo "Error de BD: " . $e->getMessage();
    }
} else {
    header("Location: chat.php");
    exit();
}
?>