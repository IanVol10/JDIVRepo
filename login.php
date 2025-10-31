<?php

$conexion = mysqli_connect("localhost", "root", "", "motoboxrepuestos");

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}


$usuario = $_POST['usuario'];
$contraseña = $_POST['contraseña'];


$sql = "SELECT * FROM usuario WHERE nombre = '$usuario' AND contraseña = '$contraseña'";
$resultado = mysqli_query($conexion, $sql);


if (mysqli_num_rows($resultado) > 0) {
    $fila = mysqli_fetch_assoc($resultado);
    $rol = strtolower($fila['rol']); 

    
    switch ($rol) {
        case 'admin':
            header("Location: sistema.php"); 
            break;

        case 'vendedor':
            header("Location: vendedor.php"); 
            break;

        case 'cliente':
            header("Location: cliente.php"); 
            break;

        default:
            echo "<script>
                    alert('Rol desconocido. Contacte con el administrador.');
                    window.location = 'login.html';
                  </script>";
            break;
    }

    exit();
} else {
   
    echo "<script>
            alert('Usuario o contraseña incorrectos');
            window.location = 'login.html';
          </script>";
}



mysqli_close($conexion);
?>
