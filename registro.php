<?php 
include 'header.php'; 
include 'includes/db.php'; 

if (isset($_POST['registrar'])) {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $email = $conn->real_escape_string($_POST['email']);
    $pass = $_POST['password'];

    $check = $conn->query("SELECT id FROM usuarios WHERE email = '$email'");
    if($check->num_rows > 0) {
        echo "<script>alert('Ese correo ya está registrado.');</script>";
    } else {
        $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES ('$nombre', '$email', '$pass', 'cliente')";
        if($conn->query($sql)) {
            echo "<script>alert('🚀 Registro exitoso. ¡Bienvenido al equipo!'); window.location.href='login.php';</script>";
        }
    }
}
?>
<div class="container" style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
    <div class="producto-card" style="width: 100%; max-width: 400px; padding: 40px;">
        <h2 class="neon-text" style="text-align: center; margin-bottom: 30px;">REGISTRO DE USUARIO</h2>
        <form method="POST">
            <label style="color: var(--primary); font-size: 11px; letter-spacing: 1px;">NOMBRE COMPLETO</label>
            <input type="text" name="nombre" required style="width:100%; padding:12px; margin:10px 0 20px 0; background:rgba(0,0,0,0.5); border:1px solid #333; color:white; border-radius:8px;">
            
            <label style="color: var(--primary); font-size: 11px; letter-spacing: 1px;">CORREO ELECTRÓNICO</label>
            <input type="email" name="email" required style="width:100%; padding:12px; margin:10px 0 20px 0; background:rgba(0,0,0,0.5); border:1px solid #333; color:white; border-radius:8px;">
            
            <label style="color: var(--primary); font-size: 11px; letter-spacing: 1px;">CONTRASEÑA</label>
            <input type="password" name="password" required style="width:100%; padding:12px; margin:10px 0 30px 0; background:rgba(0,0,0,0.5); border:1px solid #333; color:white; border-radius:8px;">
            
            <button type="submit" name="registrar" class="btn-neon" style="width:100%;">CREAR CUENTA</button>
        </form>
        <p style="text-align: center; margin-top: 20px; font-size: 12px; color: #888;">
            ¿Ya tienes acceso? <a href="login.php" style="color: var(--primary); text-decoration: none;">Logueate aquí</a>
        </p>
    </div>
</div>
</body></html>