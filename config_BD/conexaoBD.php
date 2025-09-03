<?php
$servername = "localhost"; // Endereço do servidor
$username = "root";        // Usuário do banco (por padrão no XAMPP é 'root')
$password = "";            // Senha do banco (por padrão no XAMPP é em branco)
$dbname = "consultorio";   // Nome do banco de dados

// Criando a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificando se houve erro na conexão
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>