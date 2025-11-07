<?php 
session_start();
include("conexion.php");

$busqueda = isset($_GET['q']) ? $_GET['q'] : "";
$sql = $busqueda
  ? "SELECT * FROM Objeto WHERE nombre LIKE '%" . mysqli_real_escape_string($conn, $busqueda) . "%' OR descripcion LIKE '%" . mysqli_real_escape_string($conn, $busqueda) . "%'"
  : "SELECT * FROM Objeto";
$resultado = mysqli_query($conn, $sql);

$total_items = isset($_SESSION['carrito']) && is_array($_SESSION['carrito']) && !empty($_SESSION['carrito'])
  ? array_sum(array_column($_SESSION['carrito'], 'cantidad'))
  : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>MotoBox Repuestos</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="inicio.css">
</head>
<body>

<div id="modalCarrito" class="cart-dropdown" aria-hidden="true" data-debug="panel-arriba">
  <div class="carrito-content" role="dialog" aria-label="Carrito de compras">
    <button class="cerrarCarrito" aria-label="Cerrar carrito">&times;</button>
    <h2>Tu Carrito</h2>
    <div id="listaCarrito">
      <p>Cargando carrito...</p>
    </div>
    <div class="carrito-actions">
      <button id="vaciarCarritoBtn" class="secondary">Vaciar carrito</button>
      <button id="pagarBtn" class="primary">Pagar / Realizar venta</button>
    </div>
  </div>
</div>

<header>
  <h1>MotoBox Repuestos</h1>
  <nav>
    <ul>
      <li><a href="Pagina_Inicial.php">Inicio</a></li>
      <li>
        <button id="carritoBtn" class="carrito-boton" aria-expanded="false" aria-controls="modalCarrito">
          Carrito <span id="carritoContador"><?= $total_items ?></span>
        </button>
      </li>
      <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente'): ?>
        <li><a href="cliente.php">Mi cuenta</a></li>
        <li><a href="logout.php">Cerrar sesión</a></li>
      <?php else: ?>
        <li><a href="login.html" class="login-btn">Iniciar sesión</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>

<section class="buscador">
  <form method="GET" action="Pagina_Inicial.php">
    <input type="text" name="q" placeholder="Buscar repuesto..." value="<?php echo htmlspecialchars($busqueda); ?>">
    <button type="submit">Buscar</button>
  </form>
</section>

<section class="productos" id="productosGrid">
  <?php while($fila = mysqli_fetch_assoc($resultado)) { ?>
    <div class="card" data-codigo="<?php echo $fila['codigo']; ?>">
      <img src="<?php echo htmlspecialchars($fila['imagen']); ?>" alt="<?php echo htmlspecialchars($fila['nombre']); ?>">
      <div class="info">
        <h3><?php echo htmlspecialchars($fila['nombre']); ?></h3>
        <p>$<?php echo number_format($fila['precio'], 0, ',', '.'); ?></p>
        <button class="agregarBtn" 
                data-id="<?php echo $fila['codigo']; ?>" 
                data-nombre="<?php echo htmlspecialchars($fila['nombre']); ?>" 
                data-precio="<?php echo $fila['precio']; ?>">
          Agregar al carrito
        </button>
      </div>
    </div>
  <?php } ?>
</section>

<footer>
  © 2025 MotoBox Repuestos - Todos los derechos reservados.
</footer>

<script>
const carritoBtn = document.getElementById("carritoBtn");
const modalCarrito = document.getElementById("modalCarrito");
const cerrarBtn = document.querySelector(".cerrarCarrito");
const listaCarritoDiv = document.getElementById("listaCarrito");
const vaciarCarritoBtn = document.getElementById("vaciarCarritoBtn");
const pagarBtn = document.getElementById("pagarBtn");

carritoBtn.addEventListener("click", async (e) => {
  e.stopPropagation();
  const isOpen = modalCarrito.classList.toggle("open");
  carritoBtn.setAttribute("aria-expanded", isOpen ? "true" : "false");
  if (isOpen) await actualizarCarrito();
});

if (cerrarBtn) cerrarBtn.addEventListener("click", (e) => {
  e.stopPropagation();
  modalCarrito.classList.remove("open");
  carritoBtn.setAttribute("aria-expanded", "false");
});

document.addEventListener("click", (e) => {
  if (!modalCarrito.contains(e.target) && !carritoBtn.contains(e.target)) {
    modalCarrito.classList.remove("open");
    carritoBtn.setAttribute("aria-expanded", "false");
  }
});

document.querySelectorAll(".agregarBtn").forEach(btn => {
  btn.addEventListener("click", async (ev) => {
    ev.stopPropagation();
    const data = new FormData();
    data.append("accion", "agregar");
    data.append("id", btn.dataset.id);
    data.append("nombre", btn.dataset.nombre);
    data.append("precio", btn.dataset.precio);
    await fetch("carrito.php", { method: "POST", body: data });
    await actualizarContador();
    if (modalCarrito.classList.contains("open")) await actualizarCarrito();
  });
});

async function actualizarCarrito() {
  try {
    const resp = await fetch("carrito.php?accion=mostrar");
    const html = await resp.text();
    listaCarritoDiv.innerHTML = html;
    document.querySelectorAll(".eliminarBtn").forEach(b => b.addEventListener("click", eliminarItem));
  } catch (err) {
    listaCarritoDiv.innerHTML = "<p>Error cargando carrito.</p>";
  }
}

async function eliminarItem(e) {
  e.stopPropagation();
  const id = e.currentTarget.dataset.id;
  const data = new FormData();
  data.append("accion", "eliminar");
  data.append("id", id);
  await fetch("carrito.php", { method: "POST", body: data });
  await actualizarCarrito();
  actualizarContador();
}

vaciarCarritoBtn.addEventListener("click", async (e) => {
  e.stopPropagation();
  if (!confirm("¿Vaciar todo el carrito?")) return;
  const data = new FormData();
  data.append("accion", "vaciar");
  await fetch("carrito.php", { method: "POST", body: data });
  await actualizarCarrito();
  actualizarContador();
});

pagarBtn.addEventListener("click", async (e) => {
  e.stopPropagation();
  if (!confirm("¿Deseas generar la venta ahora?")) return;
  const data = new FormData();
  data.append("accion", "checkout");
  const resp = await fetch("carrito.php", { method: "POST", body: data });
  const html = await resp.text();
  listaCarritoDiv.innerHTML = html;
  actualizarContador();
});

async function actualizarContador() {
  try {
    const resp = await fetch("carrito.php?accion=contador");
    const num = await resp.text();
    const cont = document.getElementById("carritoContador");
    if (cont) cont.innerText = num;
  } catch (err) {}
}

document.addEventListener("DOMContentLoaded", () => actualizarContador());
</script>

</body>
</html>
