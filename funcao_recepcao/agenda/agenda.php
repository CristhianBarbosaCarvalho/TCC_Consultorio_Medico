<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['recepcao']);
require_once '../../functions/validacoes.php';

// Buscar todos os médicos cadastrados com suas especialidades concatenadas
$sql = "
    SELECT m.id_medico, m.nome, GROUP_CONCAT(e.nome SEPARATOR ', ') AS especialidades
    FROM medicos m
    LEFT JOIN medico_especialidade me ON m.id_medico = me.id_medico
    LEFT JOIN especialidade e ON me.id_especialidade = e.id_especialidade
    GROUP BY m.id_medico, m.nome
    ORDER BY m.nome
";
$medicos = $conn->query($sql);
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
                    <a href="gerenciar_agenda.php?id_medico=<?= $medico['id_medico'] ?>" class="button-box">
                        <i class="fas fa-calendar-alt"></i>
                        <?= htmlspecialchars($medico['nome']) ?><br>
                        <small><?= htmlspecialchars($medico['especialidades'] ?? 'Sem especialidade') ?></small>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p style="text-align:center;">Nenhum médico cadastrado.</p>
        <?php endif; ?>
    </div>
</body>
</html>
