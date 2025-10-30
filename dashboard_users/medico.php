<?php
require_once '../autenticacao/verificar_login.php';
require_once '../config_BD/conexaoBD.php';
?>

<!DOCTYPE html>
<html>

<head>
    <title>Painel do Médico</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/recepcao.css"> <!-- mesmo estilo do admin -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <header class="header">
        <div class="container header-content">
            <h1>Bem-vindo, Dr. <?php echo $_SESSION['usuario_nome']; ?>!</h1>
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
            <a href="../funcao_medico/consulta_agendadas.php" class="button-box">
                <i class="fas fa-calendar-check"></i>
                Consultas Agendadas
            </a>
            <a href="../funcao_medico/prontuario.php" class="button-box">
                <i class="fas fa-notes-medical"></i>
                Prontuários
            </a>
            <a href="../funcao_medico/historico_paciente.php" class="button-box">
                <i class="fas fa-history"></i>
                Histórico de Pacientes
            </a>
        </div>
    </div>
    </div>
</body>

</html>
