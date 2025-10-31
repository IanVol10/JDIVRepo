<?php
include("Conexion.php"); 

$codigo = $_POST['codigo'];
$nombre = $_POST['nombre'];
$descripcion = $_POST['descripcion'];
$precio = $_POST['precio'];
$cantidad = $_POST['cantidad'];

$imagen_nombre = $_FILES['imagen']['name'];
$imagen_tmp = $_FILES['imagen']['tmp_name'];


$carpeta = "imagenes/";
$ruta = $carpeta . basename($imagen_nombre);


move_uploaded_file($imagen_tmp, $ruta);


$sql = "INSERT INTO Objeto (codigo, nombre, descripcion, precio, cantidad, imagen)
        VALUES ('$codigo', '$nombre', '$descripcion', '$precio', '$cantidad', '$ruta')";

if (mysqli_query($conn, $sql)) {
    echo "Objeto guardado correctamente.";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
