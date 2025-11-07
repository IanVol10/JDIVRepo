<?php
session_start();
include('conexion.php');

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
  $_SESSION['carrito'] = [];
}

$accion = $_GET['accion'] ?? $_POST['accion'] ?? null;

switch ($accion) {
  case 'agregar':
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
    $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;
    if ($id === null) {
      http_response_code(400);
      echo "ID inválido";
      exit;
    }
    $id = (string)$id;
    if (isset($_SESSION['carrito'][$id])) {
      $_SESSION['carrito'][$id]['cantidad']++;
    } else {
      $_SESSION['carrito'][$id] = [
        'nombre' => $nombre,
        'precio' => $precio,
        'cantidad' => 1
      ];
    }
    break;

  case 'eliminar':
    $id = isset($_POST['id']) ? (string)$_POST['id'] : null;
    if ($id !== null && isset($_SESSION['carrito'][$id])) {
      unset($_SESSION['carrito'][$id]);
    }
    break;

  case 'vaciar':
    $_SESSION['carrito'] = [];
    break;

  case 'mostrar':
    if (empty($_SESSION['carrito'])) {
      echo "<p>El carrito está vacío.</p>";
    } else {
      $total = 0;
      foreach ($_SESSION['carrito'] as $id => $item) {
        $cantidad = intval($item['cantidad']);
        $precio = floatval($item['precio']);
        $subtotal = $precio * $cantidad;
        $total += $subtotal;
        echo "<div class='carrito-item'>
                <span class='ci-nombre'>" . htmlspecialchars($item['nombre']) . " (x{$cantidad})</span>
                <span class='ci-precio'>$" . number_format($subtotal, 0, ',', '.') . "</span>
                <button class='eliminarBtn' data-id='" . htmlspecialchars($id) . "'>Eliminar</button>
              </div>";
      }
      echo "<div class='carrito-total'>
              <strong>Total:</strong>
              <span>$" . number_format($total, 0, ',', '.') . "</span>
            </div>";
    }
    break;

  case 'contador':
    $sum = 0;
    if (!empty($_SESSION['carrito'])) {
      $sum = array_sum(array_column($_SESSION['carrito'], 'cantidad'));
    }
    echo intval($sum);
    break;

  case 'checkout':
    if (empty($_SESSION['carrito'])) {
      echo "<p>No hay productos en el carrito para procesar la venta.</p>";
      break;
    }
    if (!isset($_SESSION['id_usuario'])) {
      echo "<p>Debes iniciar sesión para completar la compra.</p>";
      break;
    }
    $id_usuario = $_SESSION['id_usuario'];
    $total = 0;
    foreach ($_SESSION['carrito'] as $id => $item) {
      $cantidad = intval($item['cantidad']);
      $precio = floatval($item['precio']);
      $subtotal = $precio * $cantidad;
      $total += $subtotal;
    }
    $fecha = date("Y-m-d H:i:s");
    $conn->query("INSERT INTO pedido (id_usuario, fecha, total) VALUES ($id_usuario, '$fecha', $total)");
    $id_pedido = $conn->insert_id;
    foreach ($_SESSION['carrito'] as $id => $item) {
      $codigo = $conn->real_escape_string($id);
      $precio = floatval($item['precio']);
      $cantidad = intval($item['cantidad']);
      $conn->query("INSERT INTO pedido_detalle (id_pedido, codigo_producto, precio, cantidad) VALUES ($id_pedido, '$codigo', $precio, $cantidad)");
    }
    $_SESSION['carrito'] = [];
    echo "<h3>Compra realizada con éxito</h3>";
    echo "<p>Tu pedido ha sido registrado correctamente.</p>";
    echo "<p><strong>Total pagado: $" . number_format($total, 0, ',', '.') . "</strong></p>";
    break;

  default:
    http_response_code(400);
    echo "Acción inválida.";
    break;
}
?>
