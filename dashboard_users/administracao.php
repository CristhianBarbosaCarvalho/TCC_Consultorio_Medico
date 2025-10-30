<?php
require_once '../config_BD/conexaoBD.php';
require_once '../autenticacao/verificar_login.php';
verificarAcesso(['admin']);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Painel do Administrador</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/recepcao.css"> <!-- Estilo reutilizado -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <header class="header">
        <div class="container header-content">
            <h1>Bem-vindo, <?php echo $_SESSION['usuario_nome']; ?>!</h1>
            <a href="../autenticacao/logout.php" class="logout-btn">Sair</a>
        </div>
    </header>

    <div class="container" style="margin-top: 20px; text-align: right;">
        <a href="../funcao_admin/alterar_dados_admin.php" class="small-btn">
            <i class="fas fa-user-edit"></i> Alterar Dados
        </a>
    </div>

    <div class="container">
        <h2 style="text-align:center;">Área Administrativa</h2>

        <div class="main-buttons">
            <a href="../funcao_admin/funcionarios/cadastrar_medico.php" class="button-box">
                <i class="fas fa-user-md"></i>
                Cadastrar Médico
            </a>
            <a href="../funcao_admin/funcionarios/cadastrar_recepcionista.php" class="button-box">
                <i class="fas fa-user-plus"></i>
                Cadastrar Recepcionista
            </a>
            <a href="../funcao_admin/funcionarios/gerenciar_funcionarios.php" class="button-box"> 
                <i class="fas fa-users-cog"></i>
                Gerenciar Funcionários
            </a>
        </div>
    </div>
</body>

</html>
