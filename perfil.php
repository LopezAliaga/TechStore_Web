<?php
session_start();
// Si no está logeado, lo mandamos al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

include 'header.php';
include 'includes/db.php';
$user_id = $_SESSION['usuario_id'];

// =======================
// LÓGICA DE ACTUALIZACIÓN
// =======================
$mensaje = '';
$tipo_mensaje = ''; // success o error

if (isset($_POST['actualizar_perfil'])) {
    // Escapamos datos para seguridad
    $nuevo_nombre = $conn->real_escape_string($_POST['nombre']);
    $nuevo_email = $conn->real_escape_string($_POST['email']);
    
    $foto_subida = false;
    $nombre_foto_db = ''; // Se llenará si se sube una foto
    
    // --- Manejo de la foto de perfil ---
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png");
        $filename = $_FILES['avatar']['name'];
        $filetype = $_FILES['avatar']['type'];
        $filesize = $_FILES['avatar']['size'];
        
        // Verificar extensión
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            $mensaje = 'Error: Formato de imagen no válido.';
            $tipo_mensaje = 'error';
        } elseif ($filesize > 2 * 1024 * 1024) { // Límite de 2MB
            $mensaje = 'Error: La imagen es demasiado grande (máx 2MB).';
            $tipo_mensaje = 'error';
        } else {
            // Generar nombre único para la foto
            $nombre_foto_final = "u_" . $user_id . "_" . uniqid() . "." . $ext;
            
            // Subir la foto real al servidor
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], "img/usuarios/" . $nombre_foto_final)) {
                $foto_subida = true;
                $nombre_foto_db = $nombre_foto_final;
            } else {
                $mensaje = 'Error al subir la imagen al servidor.';
                $tipo_mensaje = 'error';
            }
        }
    }

    // --- Ejecutar el UPDATE en la BD ---
    if ($mensaje == '') { // Si no hubo errores previos
        if ($foto_subida) {
            // Actualizar TODO incluyendo la nueva foto
            $query = "UPDATE usuarios SET nombre = ?, email = ?, avatar = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $nuevo_nombre, $nuevo_email, $nombre_foto_db, $user_id);
        } else {
            // Actualizar solo nombre y email
            $query = "UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $nuevo_nombre, $nuevo_email, $user_id);
        }
        
        if ($stmt->execute()) {
            // ¡Éxito! Actualizamos la sesión para que el header cambie de inmediato
            $_SESSION['nombre'] = $nuevo_nombre;
            
            echo "<script>alert('¡Perfil actualizado con éxito! La página se recargará.'); window.location.href = 'perfil.php';</script>";
            exit(); // Muy importante
        } else {
            $mensaje = 'Error al actualizar la base de datos.';
            $tipo_mensaje = 'error';
        }
        $stmt->close();
    }
}

// =======================
// JALAMOS INFO ACTUAL
// =======================
$query = "SELECT * FROM usuarios WHERE id = $user_id";
$resultado = $conn->query($query);
$u = $resultado->fetch_assoc();

// Ruta final de la foto (por seguridad, si es NULL en BD, usa default)
$ruta_foto_final = ($u['avatar'] && file_exists("img/usuarios/" . $u['avatar'])) ? "img/usuarios/" . $u['avatar'] : "img/usuarios/default.png";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil - <?php echo $u['nombre']; ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        /* ✅ APLICAMOS LAS LETRAS: Titulos Tech y Body Limpio */
        h1, h2, h3, h4, .logo, .neon-title { font-family: 'Rajdhani', sans-serif; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        body, p, label, .info-text { font-family: 'Poppins', sans-serif; font-weight: 400; line-height: 1.6; }

        body { background-color: #0f172a; color: white; margin: 0; padding: 0; }
        .container { max-width: 1100px; margin: 50px auto; padding: 0 15px; }
        
        .layout-perfil { display: grid; grid-template-columns: 1fr 1.8fr; gap: 40px; align-items: start; }
        
        /* COLUMNA IZQUIERDA (Avatar y Fijo) */
        .fijo-col { background: #1e293b; padding: 30px; border-radius: 15px; text-align: center; position: sticky; top: 120px; box-shadow: 0 4px 6px rgba(0,0,0,0.2); }
        
        /* Foto circular neón */
        .avatar-wrap { position: relative; width: 150px; height: 150px; margin: 0 auto 20px auto; border-radius: 50%; overflow: hidden; border: 4px solid #10b981; box-shadow: 0 0 15px rgba(16, 185, 129, 0.5); }
        .avatar-wrap img { width: 100%; height: 100%; object-fit: cover; }
        
        .role-badge { background: rgba(16, 185, 129, 0.2); color: #10b981; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; border: 1px solid #10b981; display: inline-block; margin-top: 10px; }

        /* COLUMNA DERECHA (Formulario editable) */
        .editable-col { background: #1e293b; padding: 40px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.2); }
        
        .form-group { margin-bottom: 25px; }
        label { display: block; margin-bottom: 8px; color: #94a3b8; font-size: 14px; text-transform: uppercase; }
        input[type="text"], input[type="email"], input[type="file"] { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #334155; background: #0f172a; color: white; outline: none; transition: 0.3s; box-sizing: border-box; }
        input[type="text"]:focus, input[type="email"]:focus { border-color: #10b981; box-shadow: 0 0 10px rgba(16, 185, 129, 0.3); }

        /* Estilo especial para el input file */
        input[type="file"] { padding: 10px; font-size: 13px; color: #94a3b8; border: 1px dashed #334155; cursor: pointer; }
        input[type="file"]::-webkit-file-upload-button { background: #334155; border: none; color: white; padding: 5px 10px; border-radius: 4px; margin-right: 10px; cursor: pointer; }

        .btn-guardar { width: 100%; background: #10b981; color: black; border: none; padding: 15px; border-radius: 8px; font-weight: bold; cursor: pointer; text-transform: uppercase; font-size: 14px; transition: 0.3s; font-family: 'Poppins', sans-serif; }
        .btn-guardar:hover { background: #059669; box-shadow: 0 0 20px #10b981; transform: translateY(-2px); }

        .logout-link { display: block; margin-top: 30px; text-align: center; color: #ef4444; text-decoration: none; font-weight: bold; font-size: 14px; text-transform: uppercase; }
        .logout-link:hover { text-decoration: underline; }

        @media (max-width: 768px) { .layout-perfil { grid-template-columns: 1fr; } .fijo-col { position: static; } }
    </style>
</head>
<body>
    <div class="container layout-perfil">
        
        <div class="fijo-col">
            <div class="avatar-wrap">
                <img src="<?php echo $ruta_foto_final; ?>" alt="Foto de perfil">
            </div>
            <h2 style="color: #10b981; margin: 0 0 5px 0; font-size: 20px;"><?php echo $u['nombre']; ?></h2>
            <p style="color: #94a3b8; margin: 0; font-size: 14px;"><?php echo $u['email']; ?></p>
            <span class="role-badge">Rango: <?php echo strtoupper($u['rol']); ?></span>
            
            <a href="logout.php" class="logout-link"><i class="fa-solid fa-power-off"></i> Cerrar Sesión</a>
        </div>
        
        <div class="editable-col">
            <h1 style="color: #10b981; margin-top: 0; margin-bottom: 30px; border-bottom: 2px solid #334155; padding-bottom: 10px;">Editar Mi Perfil</h1>
            
            <form action="perfil.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label><i class="fa-solid fa-user"></i> Nombre Completo:</label>
                    <input type="text" name="nombre" value="<?php echo $u['nombre']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fa-solid fa-envelope"></i> Correo Electrónico:</label>
                    <input type="email" name="email" value="<?php echo $u['email']; ?>" required>
                </div>
                
                <div class="form-group" style="margin-bottom: 35px;">
                    <label><i class="fa-solid fa-image"></i> Cambiar Foto de Perfil (JPG, PNG - Máx 2MB):</label>
                    <input type="file" name="avatar" accept="image/jpeg, image/png">
                    <small style="color: #64748b; font-size: 11px;">(Deja vacío si no quieres cambiar la foto)</small>
                </div>
                
                <button type="submit" name="actualizar_perfil" class="btn-guardar">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Guardar Cambios
                </button>
                
            </form>

            <h2 style="color: white; margin-top: 50px; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 10px;">Resumen de Actividad</h2>
            <div style="background: #0f172a; padding: 20px; border-radius: 8px;">
                <p style="color: #10b981; margin: 0; font-size: 15px;">🛍️ Tienes <strong style="font-size: 20px;">3</strong> productos esperando en tu carrito.</p>
                <a href="carrito.php" style="color: #94a3b8; font-size: 13px; text-decoration: none;">Ir a pagar →</a>
            </div>

        </div>
    </div>
</body>
</html>