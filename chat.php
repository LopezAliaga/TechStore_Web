<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

$mongo_ip = "172.31.36.27";
$historialHtml = "";

// VERIFICAMOS SI ESTÁ LOGUEADO
$esta_logueado = isset($_SESSION['usuario_id']);
$nombre_usuario = $esta_logueado ? $_SESSION['nombre'] : "";
$id_usuario = $esta_logueado ? $_SESSION['usuario_id'] : null;

if ($esta_logueado) {
    try {
        $manager = new MongoDB\Driver\Manager("mongodb://admin_bot:bot_password_123@$mongo_ip:27017/admin");
        
        // Ahora filtramos el chat por su ID de usuario de la Base de Datos (Se guarda para siempre)
        $query = new MongoDB\Driver\Query(['usuario_id' => $id_usuario], ['sort' => ['fecha' => 1]]);
        $cursor = $manager->executeQuery('techstore_chat.mensajes', $query);

        foreach ($cursor as $documento) {
            if (!empty($documento->usuario_mensaje)) {
                $historialHtml .= '<p class="msg-line user-msg"><b>' . htmlspecialchars($nombre_usuario) . ':</b> ' . htmlspecialchars($documento->usuario_mensaje) . '</p>';
            }
            if (!empty($documento->bot_respuesta)) {
                $historialHtml .= '<p class="msg-line bot-msg"><b>Bot:</b> ' . htmlspecialchars($documento->bot_respuesta) . '</p>';
            }
        }
    } catch (Exception $e) {
        // Silencioso
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0d1117; color: #c9d1d9; display: flex; flex-direction: column; height: 100vh; }
        .chat-header { padding: 12px; background: #161b22; color: #00ff88; font-weight: bold; border-bottom: 1px solid #21262d; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
        #mensajes { flex: 1; overflow-y: auto; padding: 15px; display: flex; flex-direction: column; gap: 10px; background: #0d1117; }
        .msg-line { margin: 0; font-size: 13px; line-height: 1.4; }
        .bot-msg { color: #58a6ff; }
        .user-msg { color: #ff7b72; }
        .system-msg { color: #00ff88; font-style: italic; }
        #chat-form { padding: 10px; background: #161b22; border-top: 1px solid #21262d; display: flex; gap: 8px; }
        #msg { flex: 1; background: #0d1117; border: 1px solid #30363d; color: #fff; padding: 8px 12px; border-radius: 20px; outline: none; font-size: 13px; }
        #msg:focus { border-color: #00ff88; }
        button { background: #00ff88; color: #05070a; border: none; padding: 8px 15px; border-radius: 20px; font-weight: bold; cursor: pointer; font-size: 13px; transition: 0.2s; }
        button:hover { background: #00cc6a; transform: scale(1.05); }
    </style>
</head>
<body>
    <div class="chat-header">SOPORTE IA - TECHSTORE</div>
    
    <div id="mensajes">
        <?php if($esta_logueado): ?>
            <p class="msg-line bot-msg"><b>Bot:</b> Hola <?php echo htmlspecialchars($nombre_usuario); ?>. Soy el asistente de TechStore. En que te puedo ayudar hoy?</p>
            <p class="msg-line system-msg"><i>Sugerencias: "reembolso", "contacto", "envio" o "garantia".</i></p>
            <?php echo $historialHtml; ?>
        <?php else: ?>
            <p class="msg-line system-msg" style="text-align: center; margin-top: 50px;"><i>Debes iniciar sesion en la pagina principal para usar el soporte.</i></p>
        <?php endif; ?>
    </div>

    <?php if($esta_logueado): ?>
        <form id="chat-form" action="guardar_mensaje.php" method="POST">
            <input type="text" id="msg" name="msg" placeholder="Escribe tu mensaje aqui..." autocomplete="off" required>
            <button type="submit">Enviar</button>
        </form>
    <?php else: ?>
        <div id="chat-form" style="justify-content: center;">
            <a href="login.php" target="_parent" style="color: #00ff88; text-decoration: none; font-weight: bold; font-size: 13px; padding: 5px;">IR A INICIAR SESION</a>
        </div>
    <?php endif; ?>

    <script>
        var chatDiv = document.getElementById("mensajes");
        chatDiv.scrollTop = chatDiv.scrollHeight;
    </script>
</body>
</html>