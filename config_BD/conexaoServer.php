<?php
// conexao.php
$host = 'localhost';
$usuario = 'root';
$senha = ''; 

$conn = new mysqli($host, $usuario, $senha);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>
