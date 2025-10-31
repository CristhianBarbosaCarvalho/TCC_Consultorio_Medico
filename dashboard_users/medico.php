<?php
require_once '../config_BD/conexaoBD.php';
require_once '../autenticacao/verificar_login.php';
verificarAcesso(['medico']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Painel do Médico</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/recepcao.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <h1>Bem-vindo, Dr. <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h1>
            <a href="../autenticacao/logout.php" class="logout-btn">Sair</a>
        </div>
    </header>

    <div class="container" style="margin-top: 20px; text-align: right;">
        <a href="../func_medico/alterar_dados_medico.php" class="small-btn">
            <i class="fas fa-user-edit"></i> Alterar Dados
        </a>
    </div>

    <div class="container">
        <h2 style="text-align:center;">Área do Médico</h2>

        <div class="main-buttons">
            <a href="../funcao_medico/consultas_agendadas.php" class="button-box">
                <i class="fas fa-calendar-check"></i>
                Consultas Agendadas
            </a>
            <a href="../funcao_medico/historico_paciente.php" class="button-box">
                <i class="fas fa-history"></i>
                Histórico de Pacientes
            </a>
        </div>
    </div>
</body>
</html>
