<?php
header("Content-type: text/xml; charset=utf-8");
include 'includes/db.php';

echo "<?xml version='1.0' encoding='UTF-8'?>\n";
echo "<rss xmlns:g='http://base.google.com/ns/1.0' version='2.0'>\n";
echo "<channel>\n";
echo "<title>Catalogo TechStore</title>\n";
echo "<link>https://techstorepe.duckdns.org</link>\n";
echo "<description>Catalogo oficial de productos para Facebook e Instagram Shopping</description>\n";

$res = $conn->query("SELECT * FROM productos");
while($p = $res->fetch_assoc()) {
    echo "<item>\n";
    echo "  <g:id>".$p['id']."</g:id>\n";
    echo "  <g:title>".htmlspecialchars($p['nombre'], ENT_XML1, 'UTF-8')."</g:title>\n";
    echo "  <g:description>".htmlspecialchars($p['descripcion'], ENT_XML1, 'UTF-8')."</g:description>\n";
    echo "  <g:link>https://techstorepe.duckdns.org/detalleProducto.php?id=".$p['id']."</g:link>\n";
    
    $img = filter_var($p['imagen'], FILTER_VALIDATE_URL) ? $p['imagen'] : "https://techstorepe.duckdns.org/img/productos/".trim($p['imagen']);
    echo "  <g:image_link>".$img."</g:image_link>\n";
    
    echo "  <g:availability>".($p['stock'] > 0 ? 'in stock' : 'out of stock')."</g:availability>\n";
    echo "  <g:price>".$p['precio']." PEN</g:price>\n";
    echo "  <g:condition>new</g:condition>\n";
    echo "</item>\n";
}

echo "</channel>\n</rss>";
?>