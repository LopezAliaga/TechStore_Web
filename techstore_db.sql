/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.6-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: techstore_db
-- ------------------------------------------------------
-- Server version	11.8.6-MariaDB-0+deb13u1 from Debian

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `carrito`
--

DROP TABLE IF EXISTS `carrito`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrito` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrito`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `carrito` WRITE;
/*!40000 ALTER TABLE `carrito` DISABLE KEYS */;
/*!40000 ALTER TABLE `carrito` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `padre_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_padre` (`padre_id`),
  CONSTRAINT `fk_padre` FOREIGN KEY (`padre_id`) REFERENCES `categorias` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES
(1,'Procesadores',NULL),
(2,'Tarjetas de Video',NULL),
(3,'Periféricos',NULL),
(4,'Memoria RAM',NULL),
(5,'Almacenamiento SSD',NULL),
(6,'PCs Armadas',NULL),
(7,'Intel Core',1),
(8,'AMD Ryzen',1),
(9,'NVIDIA GeForce',2),
(10,'AMD Radeon',2),
(11,'Teclados Gamer',3),
(12,'Mouses Pro',3),
(13,'Memorias DDR4',4),
(14,'Memorias DDR5',4),
(15,'Discos M.2 NVMe',5),
(16,'Discos SSD SATA',5);
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `detalle_pedidos`
--

DROP TABLE IF EXISTS `detalle_pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_id` (`pedido_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `detalle_pedidos_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  CONSTRAINT `detalle_pedidos_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_pedidos`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `detalle_pedidos` WRITE;
/*!40000 ALTER TABLE `detalle_pedidos` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_pedidos` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `estado` enum('pendiente','procesando','enviado','entregado') DEFAULT 'pendiente',
  `direccion_envio` text DEFAULT NULL,
  `fecha_pedido` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
/*!40000 ALTER TABLE `pedidos` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria_id` int(11) DEFAULT NULL,
  `nombre` varchar(150) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `stock_minimo` int(11) DEFAULT 5,
  `imagen` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES
(1,8,'AMD Ryzen 9 7950X','Procesador de 16 núcleos y 32 hilos. Potencia pura.',2500.00,10,5,'img/ryzen.jpg'),
(2,9,'NVIDIA RTX 4090 24GB','La tarjeta de video más potente del mercado.',7500.00,3,5,'https://cdn-icons-png.flaticon.com/512/9698/9698188.png'),
(3,11,'Teclado Mecánico RGB','Switches Cherry MX Red, ideal para gaming.',350.00,20,10,'https://cdn-icons-png.flaticon.com/512/3342/3342157.png'),
(4,12,'Mouse Logitech G Pro X','Mouse inalámbrico ultraligero para e-sports.',450.00,15,5,'https://cdn-icons-png.flaticon.com/512/2880/2880598.png'),
(5,4,'Corsair Vengeance RGB 32GB','Memoria RAM DDR5 a 6000MHz, ideal para Ryzen 7000.',650.00,15,5,'https://cdn-icons-png.flaticon.com/512/9630/9630324.png'),
(6,15,'Samsung 990 PRO 2TB','Unidad SSD NVMe M.2 PCIe 4.0. Velocidad extrema.',850.00,8,3,'https://cdn-icons-png.flaticon.com/512/8755/8755030.png'),
(7,6,'PC Gamer Extreme','Ensamblada: Ryzen 7, RTX 4070, 32GB RAM, 1TB SSD NVMe, Case de vidrio templado.',5500.00,2,1,'https://cdn-icons-png.flaticon.com/512/3081/3081559.png'),
(8,7,'Intel Core i9-14900K','24 núcleos, hasta 6.0 GHz.',2600.00,5,2,'img/i9.jpg'),
(9,7,'Intel Core i7-14700K','20 núcleos, gran rendimiento.',1900.00,8,3,'img/i7.jpg'),
(10,7,'Intel Core i5-14600K','14 núcleos, ideal gaming.',1400.00,12,5,'img/i5.jpg'),
(11,7,'Intel Core i3-13100','4 núcleos, oficina y hogar.',600.00,15,5,'img/i3.jpg'),
(12,7,'Intel Core i5-12400F','El rey de la calidad/precio.',750.00,20,5,'img/i5-12.jpg'),
(13,8,'AMD Ryzen 9 7950X3D','El mejor para gaming extremo.',2800.00,4,2,'img/r9.jpg'),
(14,8,'AMD Ryzen 7 7800X3D','Favorito de los gamers.',1850.00,10,3,'img/r7.jpg'),
(15,8,'AMD Ryzen 5 7600X','6 núcleos potente y moderno.',1100.00,15,5,'img/r5.jpg'),
(16,8,'AMD Ryzen 7 5700G','Con gráficos integrados Vega.',950.00,20,5,'img/r7g.jpg'),
(17,8,'AMD Ryzen 5 5600G','Calidad precio con gráficos.',650.00,25,5,'img/r5g.jpg'),
(18,9,'NVIDIA RTX 4080 Super','16GB GDDR6X, potencia pura 4K.',4500.00,5,2,'img/rtx4080.jpg'),
(19,9,'NVIDIA RTX 4070 Super','12GB GDDR6X, el punto dulce.',3200.00,8,3,'img/rtx4070s.jpg'),
(20,9,'NVIDIA RTX 4060 Ti','8GB GDDR6, ideal para 1080p.',1800.00,12,4,'img/rtx4060ti.jpg'),
(21,9,'NVIDIA RTX 3060 12GB','La vieja confiable con mucha VRAM.',1400.00,15,5,'img/rtx3060.jpg'),
(22,9,'NVIDIA RTX 4090 Rog Strix','La bestia definitiva de ASUS.',8500.00,2,1,'img/rtx4090.jpg'),
(23,10,'AMD Radeon RX 7900 XTX','24GB VRAM, tope de gama AMD.',4200.00,4,2,'img/rx7900.jpg'),
(24,10,'AMD Radeon RX 7800 XT','16GB VRAM, excelente en 1440p.',2400.00,7,2,'img/rx7800.jpg'),
(25,10,'AMD Radeon RX 7700 XT','Rendimiento sólido gama media.',2000.00,10,3,'img/rx7700.jpg'),
(26,10,'AMD Radeon RX 7600','Económica para 1080p competitivo.',1300.00,15,5,'img/rx7600.jpg'),
(27,10,'AMD Radeon RX 6600','La mejor calidad precio de entrada.',1000.00,20,5,'img/rx6600.jpg'),
(28,11,'Razer BlackWidow V4','Teclado mecánico, switches verdes.',750.00,10,3,'img/kb-razer.jpg'),
(29,11,'Corsair K70 RGB Pro','Estructura de aluminio, Cherry MX.',680.00,8,2,'img/kb-corsair.jpg'),
(30,11,'Logitech G Pro X TKL','Diseño compacto para e-sports.',550.00,12,3,'img/kb-logi.jpg'),
(31,11,'HyperX Alloy Origins','Cuerpo de aluminio, switches rojos.',420.00,15,5,'img/kb-hyperx.jpg'),
(32,11,'VSG Quasar RGB','Mecánico calidad-precio local.',180.00,30,10,'img/kb-vsg.jpg'),
(33,12,'Logitech G Pro X Superlight','El mouse más usado por pros.',520.00,15,5,'img/m-logi.jpg'),
(34,12,'Razer DeathAdder V3','Ergonomía perfecta y 30K DPI.',480.00,12,3,'img/m-razer.jpg'),
(35,12,'Glorious Model O','Diseño panal ultra ligero.',320.00,20,5,'img/m-glorious.jpg'),
(36,12,'Zowie EC2-C','El estándar para Counter-Strike.',350.00,10,2,'img/m-zowie.jpg'),
(37,12,'Redragon Griffin','Económico y guerrero.',85.00,50,15,'img/m-redragon.jpg'),
(48,15,'Samsung 990 Pro 1TB','NVMe Gen4, 7450MB/s.',480.00,15,5,'img/ssd-s9.jpg'),
(49,15,'WD Black SN850X 2TB','NVMe Gen4, top para PS5/PC.',850.00,10,3,'img/ssd-wb.jpg'),
(50,11,'Crucial P3 1TB','NVMe Gen3 calidad-precio.',250.00,30,5,'img/ssd-cp.jpg'),
(51,11,'Kingston NV2 1TB','El más vendido Gen4 económico.',230.00,50,10,'img/ssd-kn.jpg'),
(52,11,'TeamGroup MP44 2TB','Gen4 con gran durabilidad.',720.00,12,3,'img/ssd-tm.jpg'),
(53,16,'Crucial MX500 1TB','SATA III, el más estable.',290.00,20,5,'img/sa-cm.jpg'),
(54,16,'Samsung 870 EVO 500GB','SATA III, calidad Samsung.',220.00,25,5,'img/sa-s8.jpg'),
(55,16,'Kingston A400 480GB','SATA III para revivir laptops.',135.00,60,15,'img/sa-ka.jpg'),
(56,12,'WD Blue SA510 1TB','SATA III confiable para trabajo.',275.00,18,5,'img/sa-wb.jpg'),
(57,12,'TeamGroup GX2 512GB','SATA III ultra económico.',120.00,40,10,'img/sa-tg.jpg'),
(58,13,'Corsair Vengeance 16GB','DDR4 3200MHz RGB.',280.00,20,5,'img/ram1.jpg'),
(59,14,'G.Skill Trident Z5','DDR5 6000MHz 32GB Kit.',750.00,10,2,'img/ram2.jpg'),
(60,15,'Samsung 990 Pro 1TB','NVMe Gen4, 7450MB/s.',480.00,15,5,'img/ssd-s9.jpg'),
(61,15,'WD Black SN850X 2TB','NVMe Gen4, top para PS5/PC.',850.00,10,3,'img/ssd-wb.jpg'),
(62,15,'Crucial P3 1TB','NVMe Gen3 calidad-precio.',250.00,30,5,'img/ssd-cp.jpg'),
(63,15,'Kingston NV2 1TB','El más vendido Gen4 económico.',230.00,50,10,'img/ssd-kn.jpg'),
(64,15,'TeamGroup MP44 2TB','Gen4 con gran durabilidad.',720.00,12,3,'img/ssd-tm.jpg'),
(65,16,'Crucial MX500 1TB','SATA III, el más estable.',290.00,20,5,'img/sa-cm.jpg'),
(66,16,'Samsung 870 EVO 500GB','SATA III, calidad Samsung.',220.00,25,5,'img/sa-s8.jpg'),
(67,16,'Kingston A400 480GB','SATA III para revivir laptops.',135.00,60,15,'img/sa-ka.jpg'),
(68,16,'WD Blue SA510 1TB','SATA III confiable para trabajo.',275.00,18,5,'img/sa-wb.jpg'),
(69,16,'TeamGroup GX2 512GB','SATA III ultra económico.',120.00,40,10,'img/sa-tg.jpg');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `rol` enum('cliente','administrador','vendedor') DEFAULT 'cliente',
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES
(1,'El Profe Admin','admin@tienda.com','123456','administrador','2026-05-12 17:00:53'),
(2,'Causita Cliente','cliente@tienda.com','123456','cliente','2026-05-12 17:00:53'),
(3,'Zoraide','chata123@gmail.com','123456','cliente','2026-05-12 19:35:07');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-05-13 14:37:20
