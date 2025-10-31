<?php
session_start();
include('conexion.php');


if (!isset($_SESSION['venta'])) $_SESSION['venta'] = [];


$seccion = $_GET['seccion'] ?? 'ventas';



if ($seccion === 'ventas') {

  
    $busqueda = $_GET['buscar'] ?? '';
    $sql = "SELECT codigo, nombre, precio, cantidad AS stock FROM objeto";
    if ($busqueda != '') {
        $busqueda = $conn->real_escape_string($busqueda);
        $sql .= " WHERE nombre LIKE '%$busqueda%' OR codigo LIKE '%$busqueda%'";
    }
    $resultado = $conn->query($sql);
    $productos = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];

    
    if (isset($_POST['codigo'])) {
        $codigo = $_POST['codigo'];
        $buscar = $conn->query("SELECT * FROM objeto WHERE codigo='$codigo' LIMIT 1");
        if ($buscar && $buscar->num_rows > 0) {
            $producto = $buscar->fetch_assoc();
            if ($producto['cantidad'] > 0) {
                
                $conn->query("UPDATE objeto SET cantidad = cantidad - 1 WHERE codigo='$codigo'");
                
                $_SESSION['venta'][] = [
                    "codigo" => $producto['codigo'],
                    "nombre" => $producto['nombre'],
                    "precio" => $producto['precio']
                ];
            }
        }
    }

    
    if (isset($_POST['confirmar'])) {
        $total = array_sum(array_column($_SESSION['venta'], 'precio'));
        $fecha = date("Y-m-d H:i:s");

        
        $conn->query("INSERT INTO pedido (fecha, total) VALUES ('$fecha', '$total')");
        $id_pedido = $conn->insert_id;

        
        foreach ($_SESSION['venta'] as $item) {
            $codigo = $item['codigo'];
            $precio = $item['precio'];
            $conn->query("INSERT INTO pedido_detalle (id_pedido, codigo_producto, precio) VALUES ($id_pedido, '$codigo', $precio)");
        }

        $_SESSION['venta'] = []; 
    }

    $total = array_sum(array_column($_SESSION['venta'], 'precio'));
}



if ($seccion === 'stock') {
    
    if (isset($_POST['nuevo'])) {
        $codigo = $_POST['codigo'];
        $nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
        $conn->query("INSERT INTO objeto (codigo, nombre, precio, cantidad) VALUES ('$codigo', '$nombre', $precio, $stock)");
    }

    
    if (isset($_POST['editar'])) {
        $codigo = $_POST['codigo'];
        $nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
        $conn->query("UPDATE objeto SET nombre='$nombre', precio=$precio, cantidad=$stock WHERE codigo='$codigo'");
    }

    
    if (isset($_POST['eliminar'])) {
        $codigo = $_POST['codigo'];
        $conn->query("DELETE FROM objeto WHERE codigo='$codigo'");
    }

    
    $productos_stock = $conn->query("SELECT * FROM objeto")->fetch_all(MYSQLI_ASSOC);
}


if ($seccion === 'pedidos') {
    $pedidos = $conn->query("
        SELECT p.id_pedido, p.fecha, p.total, COUNT(d.id_pedido_detalle) AS productos
        FROM pedido p
        LEFT JOIN pedido_detalle d ON p.id_pedido = d.id_pedido
        GROUP BY p.id_pedido
        ORDER BY p.fecha DESC
    ")->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Control - MotoBox</title>
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
nav a:hover, nav a.activo {
  text-decoration: underline;
}
</style>
</head>
<body>

<header>
  <h1>Control de Stock y Ventas</h1>
  <nav>
    <a href="?seccion=ventas" class="<?= $seccion=='ventas'?'activo':'' ?>">Ventas</a>
    <a href="?seccion=stock" class="<?= $seccion=='stock'?'activo':'' ?>">Stock</a>
    <a href="?seccion=pedidos" class="<?= $seccion=='pedidos'?'activo':'' ?>">Pedidos</a>
    <a href="Pagina Inicial.php" class="<?= $seccion=='logout'?'activo':'' ?>">logout</a>
  </nav>
</header>

<main class="contenedor">


  

<?php

 if ($seccion === 'logout'): 

include('logout.php');

endif

?>





 


<?php if ($seccion === 'ventas'): ?>
  
  <div class="panel-izquierda">
    <section class="busqueda">
      <form method="get">
        <input type="hidden" name="seccion" value="ventas">
        <input type="text" name="buscar" placeholder="Buscar producto..." value="<?= htmlspecialchars($busqueda) ?>">
        <button type="submit">Buscar</button>
      </form>
    </section>
    <h2>Productos disponibles</h2>
    <div class="tabla-contenedor">
      <table>
        <tr><th>Código</th><th>Producto</th><th>Precio</th><th>Stock</th><th>Acción</th></tr>
        <?php foreach ($productos as $p): ?>
          <tr>
            <td><?= $p['codigo'] ?></td>
            <td><?= $p['nombre'] ?></td>
            <td>$<?= number_format($p['precio'],0,',','.') ?></td>
            <td><?= $p['stock'] ?></td>
            <td>
              <?php if ($p['stock']>0): ?>
              <form method="post">
                <input type="hidden" name="codigo" value="<?= $p['codigo'] ?>">
                <button type="submit">Agregar</button>
              </form>
              <?php else: ?>
              <span class="sin-stock">Sin stock</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>

  <div class="panel-derecha">
    <h2>Venta en curso</h2>
    <?php if (empty($_SESSION['venta'])): ?>
      <p>No hay productos en la venta actual.</p>
    <?php else: ?>
      <table>
        <tr><th>Código</th><th>Producto</th><th>Precio</th></tr>
        <?php foreach ($_SESSION['venta'] as $item): ?>
        <tr><td><?= $item['codigo'] ?></td><td><?= $item['nombre'] ?></td><td>$<?= number_format($item['precio'],0,',','.') ?></td></tr>
        <?php endforeach; ?>
      </table>
      <h3>Total: $<?= number_format($total,0,',','.') ?></h3>
      <form method="post"><button type="submit" name="confirmar">Confirmar venta</button></form>
    <?php endif; ?>
  </div>

<?php elseif ($seccion === 'stock'): ?>
 
  <div class="panel-izquierda" style="flex:1;">
    <h2>Gestión de Stock</h2>
    <div style="text-align:right; margin-bottom:20px;">
        <a href="agregar_objeto.php" style="
        background-color:#ff5500;
        color:white;
        padding:10px 20px;
        border-radius:6px;
        text-decoration:none;
        font-weight:bold;">
        + Agregar nuevo objeto
        </a>
    </div>






    <table>
      <tr><th>Código</th><th>Nombre</th><th>Precio</th><th>Cantidad</th><th>Acciones</th></tr>
      <?php foreach ($productos_stock as $p): ?>
      <tr>
        <form method="post">
          <td><input type="text" name="codigo" value="<?= $p['codigo'] ?>" readonly></td>
          <td><input type="text" name="nombre" value="<?= $p['nombre'] ?>"></td>
          <td><input type="number" name="precio" value="<?= $p['precio'] ?>"></td>
          <td><input type="number" name="stock" value="<?= $p['cantidad'] ?>"></td>
          <td>
            <button name="editar">Modificar</button>
            <button name="eliminar" onclick="return confirm('¿Eliminar producto?')">Eliminar</button>
          </td>
        </form>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>

<?php elseif ($seccion === 'pedidos'): ?>
  
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
<?php endif; ?>

</main>

<footer>
  <p>© 2025 MotoBox Repuestos</p>
</footer>
</body>
</html>
