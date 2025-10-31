<?php
session_start();

$_SESSION = [];

session_destroy();

header("Location: Pagina Inicial.php");
exit();
?>