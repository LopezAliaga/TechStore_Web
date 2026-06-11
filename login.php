<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php';

if (isset($_POST['ingresar'])) {

    // Recibimos los datos limpios de espacios
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);

    // 🛡️ ESCUDO ANTI-HACKERS: CONSULTA PREPARADA (PREPARED STATEMENT) 🛡️
    // Los signos de interrogación evitan que los hackers inyecten código SQL
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ? AND password = ?");
    
    // Las "ss" significan que estamos enviando dos variables de tipo String (Texto)
    $stmt->bind_param("ss", $email, $pass);
    $stmt->execute();
    
    // Obtenemos el resultado seguro
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {

        $u = $res->fetch_assoc();

        $_SESSION['usuario_id'] = $u['id'];
        $_SESSION['nombre'] = $u['nombre'];
        $_SESSION['rol'] = $u['rol'];

        session_write_close();

        if($u['rol']=="administrador"){
            header("Location: admin.php");
        } else {
            header("Location: index.php");
        }

        exit();

    } else {
        echo "<script>alert('Datos incorrectos, acceso denegado.');</script>";
    }
}

include 'header.php';
?>

<div class="container" style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
    <div class="producto-card" style="width: 100%; max-width: 400px; padding: 40px; background: var(--card-bg); border: 1px solid var(--search-border); border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <h2 class="neon-text" style="text-align: center; margin-bottom: 30px; font-family: 'Poppins', sans-serif;">ACCESO AL SISTEMA</h2>
        
        <form method="POST">
            <input type="email" name="email" placeholder="Correo Electrónico" required style="width:100%; box-sizing: border-box; padding:12px; margin-bottom:20px; border-radius: 8px; border: 1px solid var(--search-border); background: var(--search-bg); color: var(--text-main); font-family: 'Poppins', sans-serif; outline: none;">
            <input type="password" name="password" placeholder="Contraseña" required style="width:100%; box-sizing: border-box; padding:12px; margin-bottom:10px; border-radius: 8px; border: 1px solid var(--search-border); background: var(--search-bg); color: var(--text-main); font-family: 'Poppins', sans-serif; outline: none;">
            
            <div style="text-align: right; margin-bottom: 25px;">
                <a href="recuperar.php" style="color: var(--text-muted); text-decoration: none; font-size: 12px; transition: 0.3s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <button type="submit" name="ingresar" class="btn-neon" style="width:100%; padding: 15px; font-size: 14px; text-transform: uppercase;">ENTRAR AL SETUP</button>
        </form>

        <p style="text-align: center; margin-top: 30px; font-size: 13px; color: var(--text-muted); font-family: 'Poppins', sans-serif;">
            ¿Eres nuevo en el equipo? <br><br>
            <a href="registro.php" style="color: var(--primary); text-decoration: none; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                Crea tu cuenta aquí
            </a>
        </p>
    </div>
</div>
</body>
</html>