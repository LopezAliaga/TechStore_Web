<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') { header("Location: index.php"); exit(); }
include 'header.php'; include 'includes/db.php'; 

if (isset($_POST['guardar'])) {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $desc = $conn->real_escape_string($_POST['descripcion']);
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $cat = $_POST['categoria_id'];
    $img = $_POST['imagen'];
    $conn->query("INSERT INTO productos (nombre, descripcion, precio, stock, stock_minimo, categoria_id, imagen) VALUES ('$nombre', '$desc', $precio, $stock, 5, $cat, '$img')");
    echo "<script>alert('¡Añadido!'); window.location.href='admin.php';</script>";
}
?>

<div class="container" style="max-width: 600px;">
    <div class="producto-card" style="text-align: left; padding: 30px;">
        <h2 style="color: var(--primary); text-align: center;"><i class="fa-solid fa-plus-circle"></i> Nuevo Producto</h2>
        <form method="POST">
            <input type="text" name="nombre" placeholder="Nombre" required style="width:100%; padding:10px; margin-bottom:15px; background:#0a0c10; color:white; border:1px solid #333;">
            <select name="categoria_id" style="width:100%; padding:10px; margin-bottom:15px; background:#0a0c10; color:white; border:1px solid #333;">
                <?php $res = $conn->query("SELECT * FROM categorias WHERE padre_id IS NOT NULL");
                while($c = $res->fetch_assoc()) { echo "<option value='".$c['id']."'>".$c['nombre']."</option>"; } ?>
            </select>
            <textarea name="descripcion" placeholder="Descripción" style="width:100%; padding:10px; margin-bottom:15px; background:#0a0c10; color:white; border:1px solid #333;"></textarea>
            <div style="display:flex; gap:10px;">
                <input type="number" step="0.01" name="precio" placeholder="Precio S/" required style="flex:1; padding:10px; background:#0a0c10; color:white; border:1px solid #333;">
                <input type="number" name="stock" placeholder="Stock" required style="flex:1; padding:10px; background:#0a0c10; color:white; border:1px solid #333;">
            </div>
            <input type="text" name="imagen" value="img/producto.jpg" style="width:100%; padding:10px; margin:15px 0; background:#0a0c10; color:white; border:1px solid #333;">
            <button type="submit" name="guardar" class="btn" style="width:100%;">Registrar Componente</button>
        </form>
    </div>
</div>
</body></html>