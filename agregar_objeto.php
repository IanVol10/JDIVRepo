<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = $_POST["codigo"];
    $nombre = $_POST["nombre"];
    $descripcion = $_POST["descripcion"];
    $precio = $_POST["precio"];
    $cantidad = $_POST["cantidad"];


    $directorio = "objetoimg/";

  
    $nombreArchivo = basename($_FILES["imagen"]["name"]);
    $ruta = $directorio . $nombreArchivo;

 
    $tipoArchivo = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));

    
    $tiposPermitidos = array("jpg", "jpeg", "png", "gif");
    if (in_array($tipoArchivo, $tiposPermitidos)) {

        
        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta)) {
            
            $sql = "INSERT INTO objeto (codigo, nombre, descripcion, precio, cantidad, imagen)
                    VALUES ('$codigo', '$nombre', '$descripcion', '$precio', '$cantidad', '$ruta')";

            if ($conn->query($sql) === TRUE) {
                echo "<p> Producto agregado correctamente.</p>";
            } else {
                echo "<p> Error al guardar en la base: " . $conn->error . "</p>";
            }
        } else {
            echo "<p> Error al subir la imagen.</p>";
        }
    } else {
        echo "<p> Solo se permiten archivos JPG, JPEG, PNG o GIF.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Producto</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 30px; }
        form { background: white; padding: 20px; border-radius: 10px; width: 400px; margin: auto; box-shadow: 0 0 10px #ccc; }
        input, textarea { width: 100%; margin-bottom: 10px; padding: 8px; }
        button { background: #007bff; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
    <link rel="stylesheet" href="agregar.css">

</head>
<body>
    <h2 style="text-align:center;">Agregar nuevo producto</h2>

    <form method="POST" enctype="multipart/form-data">
        <label>Código:</label>
        <input type="text" name="codigo" required>

        <label>Nombre:</label>
        <input type="text" name="nombre" required>

        <label>Descripción:</label>
        <textarea name="descripcion"></textarea>

        <label>Precio:</label>
        <input type="number" name="precio" step="0.01" required>

        <label>Cantidad:</label>
        <input type="number" name="cantidad" required>

        <label>Imagen:</label>
        <input type="file" name="imagen" accept="image/*" required>

        <button type="submit">Guardar Producto</button>
    </form>
</body>
</html>
