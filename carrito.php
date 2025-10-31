<?php
session_start();

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
    $total = 0;
    $detalle = "<ul>";
    foreach ($_SESSION['carrito'] as $id => $item) {
      $cantidad = intval($item['cantidad']);
      $precio = floatval($item['precio']);
      $subtotal = $precio * $cantidad;
      $total += $subtotal;
      $detalle .= "<li>" . htmlspecialchars($item['nombre']) . " x{$cantidad} - $" . number_format($subtotal, 0, ',', '.') . "</li>";
    }
    $detalle .= "</ul>";
 

    echo "<h3>Venta realizada</h3>";
    echo "<p>Detalle:</p>";
    echo $detalle;
    echo "<p><strong>Total a pagar: $" . number_format($total, 0, ',', '.') . "</strong></p>";
    echo "<p>El carrito se ha vaciado tras completar la venta.</p>";


    $_SESSION['carrito'] = [];
    break;

  default:
   
    http_response_code(400);
    echo "Acción inválida.";
    break;
}
