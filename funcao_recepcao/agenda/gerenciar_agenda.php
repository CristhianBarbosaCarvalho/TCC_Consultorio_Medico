<?php
require_once '../../autenticacao/verificar_login.php';
require_once '../../config_BD/conexaoBD.php';

// Buscar todos os médicos cadastrados
$medicos = $conn->query("SELECT id_medico, nome, especialidade FROM medicos ORDER BY nome");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gerenciar Agendas</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/recepcao.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <h1>Gerenciar Agendas</h1>
            <a href="../../autenticacao/logout.php" class="logout-btn">Sair</a>
        </div>
    </header>

    <div class="container">
        <a href="../../dashboard_users/recepcao.php" class="small-btn">
            <i class="fas fa-arrow-left"></i> Voltar ao Painel
        </a>

        <h2 style="text-align:center;">Médicos Cadastrados</h2>

        <?php if ($medicos->num_rows > 0): ?>
            <div class="main-buttons">
                <?php while ($medico = $medicos->fetch_assoc()): ?>
                    <a href="editar_agenda.php?id_medico=<?= $medico['id_medico'] ?>" class="button-box">
                        <i class="fas fa-calendar-alt"></i>
                        <?= $medico['nome'] ?><br>
                        <small><?= $medico['especialidade'] ?></small>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p style="text-align:center;">Nenhum médico cadastrado.</p>
        <?php endif; ?>
    </div>
</body>
</html>
