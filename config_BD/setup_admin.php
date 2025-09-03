<?php
require_once 'conexaoServer.php';

// Cria banco
$nomeBanco = "consultorio";
if ($conn->query("CREATE DATABASE IF NOT EXISTS $nomeBanco") !== TRUE) {
    echo "Erro ao criar o banco de dados.<br>";
}

// Seleciona o banco
if (!$conn->select_db($nomeBanco)) {
    echo "Erro ao selecionar o banco de dados.<br>";
    exit();
}

// Tabela administrador
$conn->query("CREATE TABLE IF NOT EXISTS administrador (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo VARCHAR(50) NOT NULL DEFAULT 'admin'
)");

// Tabela medicos
$conn->query("CREATE TABLE IF NOT EXISTS medicos (
    id_medico INT AUTO_INCREMENT PRIMARY KEY,
    crm VARCHAR(150) UNIQUE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    especialidade VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL DEFAULT 'medico'
)");

// Tabela recepcionista
$conn->query("CREATE TABLE IF NOT EXISTS recepcionista (
    id_recepcionista INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefone VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo VARCHAR(50) NOT NULL DEFAULT 'recepcao'
)");

// Tabela paciente
$conn->query("CREATE TABLE IF NOT EXISTS paciente (
    id_paciente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    data_nascimento DATE NOT NULL,
    cpf VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefone VARCHAR(100) NOT NULL,
    endereco VARCHAR(100) NOT NULL
)");

// Tabela agenda
$conn->query("CREATE TABLE IF NOT EXISTS agenda (
    id_agenda INT AUTO_INCREMENT PRIMARY KEY,
    dia_semana VARCHAR(20) NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    fk_id_medico INT NOT NULL,
    FOREIGN KEY (fk_id_medico) REFERENCES medicos(id_medico)
)");

// Tabela consulta
$conn->query("CREATE TABLE IF NOT EXISTS consulta (
    id_consulta INT AUTO_INCREMENT PRIMARY KEY,
    data_hora DATETIME NOT NULL,
    estatus VARCHAR(50) NOT NULL,
    observacoes TEXT,
    fk_id_paciente INT NOT NULL,
    fk_id_medico INT NOT NULL,
    fk_id_agenda INT NOT NULL,
    sintomas TEXT,
    diagnostico TEXT,
    prescricao TEXT,
    exames_solicitados TEXT,
    FOREIGN KEY (fk_id_paciente) REFERENCES paciente(id_paciente),
    FOREIGN KEY (fk_id_medico) REFERENCES medicos(id_medico),
    FOREIGN KEY (fk_id_agenda) REFERENCES agenda(id_agenda)
)");

// Tabela pagamentos
$conn->query("CREATE TABLE IF NOT EXISTS pagamentos (
    id_pagamento INT AUTO_INCREMENT PRIMARY KEY,
    data_pagamento DATE NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    forma_de_pagamento VARCHAR(50) NOT NULL,
    estatus VARCHAR(50) NOT NULL,
    fk_id_consulta INT NOT NULL,
    FOREIGN KEY (fk_id_consulta) REFERENCES consulta(id_consulta)
)");

// Verifica se já existe um administrador cadastrado
$result = $conn->query("SELECT COUNT(*) AS count FROM administrador");
$row = $result->fetch_assoc();

// Se não houver, cria o admin padrão
if ($row['count'] == 0) {
    $senhaAdmin = 'admin';
    $senhaHash = password_hash($senhaAdmin, PASSWORD_BCRYPT);

    $conn->query("INSERT INTO administrador (nome, telefone, email, senha, tipo)
                  VALUES ('Cristhian', '13981656220', 'admin', '$senhaHash', 'admin')");
}
?>
