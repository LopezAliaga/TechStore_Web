<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') { header("Location: index.php"); exit(); }
include 'header.php'; include 'includes/db.php';

if(isset($_POST['add_cat'])) {
    $nom = $conn->real_escape_string($_POST['nombre']);
    $padre = $_POST['padre_id'] == "" ? "NULL" : $_POST['padre_id'];
    $conn->query("INSERT INTO categorias (nombre, padre_id) VALUES ('$nom', $padre)");
    echo "<script>alert('Categoría Creada');</script>";
}
?>

<div class="container" style="max-width: 500px;">
    <div class="producto-card" style="text-align: left;">
        <h2 style="color: var(--primary); text-align: center;"><i class="fa-solid fa-tags"></i> Gestionar Estructura</h2>
        <form method="POST">
            <label style="color:#8b949e; font-size:13px;">Nombre de la Categoría</label>
            <input type="text" name="nombre" required style="width:100%; padding:12px; margin:10px 0; background:#0a0c10; border:1px solid #333; color:white; border-radius:8px;">
            
            <label style="color:#8b949e; font-size:13px;">¿Depende de otra? (Padre)</label>
            <select name="padre_id" style="width:100%; padding:12px; margin:10px 0; background:#0a0c10; border:1px solid #333; color:white; border-radius:8px;">
                <option value="">Es categoría principal</option>
                <?php $paps = $conn->query("SELECT * FROM categorias WHERE padre_id IS NULL");
                while($p = $paps->fetch_assoc()) echo "<option value='".$p['id']."'>".$p['nombre']."</option>"; ?>
            </select>
            <button type="submit" name="add_cat" class="btn" style="width:100%; margin-top:15px;">Crear Ahora</button>
            <a href="admin.php" style="display:block; text-align:center; margin-top:20px; color:#8b949e; text-decoration:none;">Regresar al Panel</a>
        </form>
    </div>
</div>
</body></html>