<?php
include("conexion.php");
session_start();

$alerta = ""; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST["nombre"];
    $celular = $_POST["celular"];
    $correo = $_POST["correo"];
    $direccion = $_POST["direccion"];
    $contraseña = $_POST["contraseña"];
    $rol = "cliente"; 

   
    $check = $conn->prepare("SELECT * FROM usuario WHERE correo = ?");
    $check->bind_param("s", $correo);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $alerta = "⚠️ Ya existe un usuario registrado con ese correo.";
    } else {
       
        $hash = password_hash($contraseña, PASSWORD_DEFAULT);

       
        $sql = $conn->prepare("INSERT INTO usuario (nombre, correo, contraseña, rol) VALUES (?, ?, ?, ?)");
        $sql->bind_param("ssss", $nombre, $correo, $hash, $rol);

        if ($sql->execute()) {
           
            echo "<script>
                alert(' Registro exitoso. Ahora puedes iniciar sesión.');
                window.location.href='Pagina Inicial.php';
            </script>";
            exit();
        } else {
            $alerta = " Error al registrar el usuario.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro de Usuario - MotoBox</title>
<style>
body {
  font-family: Arial, sans-serif;
  background: #f4f4f4;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
form {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  width: 360px;
}
h2 {
  text-align: center;
  color: #ff5500;
}
label {
  display: block;
  margin-top: 10px;
  font-weight: bold;
}
input {
  width: 100%;
  padding: 8px;
  margin-top: 5px;
  border-radius: 6px;
  border: 1px solid #ccc;
}
button {
  background: #ff5500;
  color: white;
  border: none;
  padding: 10px;
  width: 100%;
  border-radius: 8px;
  margin-top: 15px;
  font-weight: bold;
  cursor: pointer;
}
button:hover {
  background: #e04300;
}
.regresar {
  display: block;
  text-align: center;
  margin-top: 10px;
  text-decoration: none;
  color: #555;
  font-weight: bold;
}
.regresar:hover {
  color: #ff5500;
}
.mensaje {
  text-align: center;
  margin-top: 10px;
  color: #333;
  font-weight: bold;
}
</style>
</head>
<body>

<form method="POST" action="">
  <h2>Registro de Usuario</h2>

  <label>Nombre y Apellido:</label>
  <input type="text" name="nombre" required>

  <label>Celular:</label>
  <input type="text" name="celular" required>

  <label>Correo Electrónico:</label>
  <input type="email" name="correo" required>

  <label>Dirección:</label>
  <input type="text" name="direccion" required>

  <label>Contraseña:</label>
  <input type="password" name="contraseña" required>


  <input type="hidden" name="rol" value="cliente">

  <button type="submit">Registrar</button>

  <a href="javascript:history.back()" class="regresar">← Volver atrás</a>

  <?php if (!empty($alerta)) echo "<p class='mensaje'>$alerta</p>"; ?>
</form>

</body>
</html>
