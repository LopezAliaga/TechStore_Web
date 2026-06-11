<?php include 'header.php'; include 'includes/db.php'; ?>
<div class="container" style="display: flex; justify-content: center; align-items: center; min-height: 75vh;">
    <div class="producto-card" style="max-width: 450px; width: 100%; text-align: center;">
        <h2 class="neon-text">RESCATE DE CUENTA</h2>
        <p style="color: #8b949e; font-size: 13px; margin-bottom: 30px;">Se enviará una notificación de seguridad a tu correo.</p>
        
        <?php
        if(isset($_POST['recuperar'])) {
            $email = $conn->real_escape_string($_POST['email']);
            $res = $conn->query("SELECT * FROM usuarios WHERE email = '$email'");
            if($res->num_rows > 0) {
                echo "<div style='border: 1px solid var(--primary); padding: 15px; margin-top: 20px; border-radius: 10px; background: rgba(0,255,136,0.05);'>";
                echo "<p style='color: var(--primary); margin:0;'>✅ Notificación enviada a: <b>$email</b></p>";
                echo "<small style='color: #666;'>[LOG]: Simulation-Link: reset_pass.php?id=99</small>";
                echo "</div>";
            } else {
                echo "<p style='color: #ff4a4a; margin-top: 20px;'>❌ El correo no figura en el sistema.</p>";
            }
        }
        ?>

        <form method="POST" style="margin-top: 20px;">
            <input type="email" name="email" placeholder="Ingresa tu email" required class="input-cyber" style="width: 100%; padding: 15px; margin-bottom: 20px;">
            <button type="submit" name="recuperar" class="btn-neon" style="width: 100%;">ENVIAR NOTIFICACIÓN</button>
        </form>
        <a href="login.php" style="display: block; margin-top: 20px; color: #8b949e; text-decoration: none; font-size: 12px;">Volver al Acceso</a>
    </div>
</div>
</body></html>