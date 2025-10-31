<?php
session_start();
include('conexion.php');



$pedidos = $conn->query("
    SELECT p.id_pedido, p.fecha, p.total, COUNT(d.id_pedido_detalle) AS productos
    FROM pedido p
    LEFT JOIN pedido_detalle d ON p.id_pedido = d.id_pedido
    GROUP BY p.id_pedido
    ORDER BY p.fecha DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Cliente - MotoBox</title>
<link rel="stylesheet" href="ventasc.css">
<style>
nav {
  background: #111;
  display: flex;
  justify-content: center;
  gap: 30px;
  padding: 15px;
}
nav a {
  color: #ff5500;
  text-decoration: none;
  font-weight: bold;
  font-size: 1.1rem;
}
</style>
</head>
<body>

<header>
  <h1>Pedidos del Cliente</h1>
</header>

<main class="contenedor">
  <div class="panel-izquierda" style="flex:1;">
    <h2>Pedidos Realizados</h2>
    <table>
      <tr><th>ID Pedido</th><th>Fecha</th><th>Total</th><th>Productos</th></tr>
      <?php foreach ($pedidos as $p): ?>
      <tr>
        <td><?= $p['id_pedido'] ?></td>
        <td><?= $p['fecha'] ?></td>
        <td>$<?= number_format($p['total'],0,',','.') ?></td>
        <td><?= $p['productos'] ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</main>

<footer>
  <p>© 2025 MotoBox Repuestos</p>
</footer>
</body>
</html>
