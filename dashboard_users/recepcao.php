<?php
require_once '../autenticacao/verificar_login.php';
require_once '../config_BD/conexaoBD.php';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Painel da Recepção</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/recepcao.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <header class="header">
        <div class="container header-content">
            <h1>Bem-vinda, <?php echo $_SESSION['usuario_nome']; ?>!</h1>
            <a href="../autenticacao/logout.php" class="logout-btn">Sair</a>
        </div>
    </header>

    <div class="container" style="margin-top: 20px; text-align: right;">
        <a href="../funcao_recepcao/alterar_dados_recepcao.php" class="small-btn">
            <i class="fas fa-user-edit"></i> Alterar Dados
        </a>
    </div>

    <div class="container">
        <h2 style="text-align:center;">Área da Recepção</h2>

        <div class="main-buttons">
            <a href="../funcao_recepcao/pacientes/pacientes_cadastrados.php" class="button-box">
                <i class="fas fa-users"></i>
                Ver Pacientes
            </a>
            <a href="../funcao_recepcao/pacientes/cadastrar_paciente.php" class="button-box">
                <i class="fas fa-user-plus"></i>
                Cadastrar Paciente
            </a>
            <a href="../funcao_recepcao/agenda/gerenciar_agenda.php" class="button-box">
                <i class="fas fa-calendar-alt"></i>
                Gerenciar Agendas
            </a>
        </div>
    </div>
</body>

</html>
