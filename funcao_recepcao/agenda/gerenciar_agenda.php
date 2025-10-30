<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['recepcao']);
require_once '../../functions/delete.php';

if (!isset($_GET['id_medico'])) {
    header("Location: agenda.php");
    exit();
}

$id_medico = intval($_GET['id_medico']);
$medico = $conn->query("SELECT nome FROM medicos WHERE id_medico = $id_medico")->fetch_assoc();

$data_hoje = date('Y-m-d');
$data_max = date('Y-m-d', strtotime('+6 months'));

$mensagem = isset($_GET['mensagem']) ? $_GET['mensagem'] : '';
$mensagem_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

$dias_pt = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];

// ===========================
// PROCESSAMENTO DO FORMULÁRIO
// ===========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dias_semana'], $_POST['inicio'], $_POST['fim'])) {
    $dias_semana = array_map('intval', $_POST['dias_semana']);
    $inicio = $_POST['inicio'];
    $fim = $_POST['fim'];

    $data_inicio = new DateTime();
    $data_limite = (new DateTime())->modify('+2 months');

    $datas_inseridas = 0;

    while ($data_inicio <= $data_limite) {
        $numero_dia = (int)$data_inicio->format('N'); // 1=Segunda ... 7=Domingo

        if (in_array($numero_dia, $dias_semana)) {
            $data_str = $data_inicio->format('Y-m-d');

            $stmtCheck = $conn->prepare("SELECT COUNT(*) as total FROM agenda 
                                         WHERE fk_id_medico = ? AND data_agenda = ? 
                                         AND hora_inicio = ? AND hora_fim = ?");
            $stmtCheck->bind_param("isss", $id_medico, $data_str, $inicio, $fim);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result()->fetch_assoc();
            $stmtCheck->close();

            if ($resultCheck['total'] == 0) {
                $dia_semana_nome = $dias_pt[$numero_dia - 1];
                $stmt = $conn->prepare("INSERT INTO agenda (dia_semana, data_agenda, hora_inicio, hora_fim, fk_id_medico)
                                        VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssi", $dia_semana_nome, $data_str, $inicio, $fim, $id_medico);
                $stmt->execute();
                $stmt->close();
                $datas_inseridas++;
            }
        }

        $data_inicio->modify('+1 day');
    }

    if ($datas_inseridas > 0) {
        $mensagem = "$datas_inseridas horário(s) cadastrados com sucesso!";
        $mensagem_tipo = "alert-success";
    } else {
        $mensagem = "Nenhum novo horário foi adicionado (todos já existiam).";
        $mensagem_tipo = "alert-info";
    }
}

// ===========================
// EXCLUSÃO DE HORÁRIO
// ===========================
if (isset($_GET['excluir'])) {
    $id_agenda = intval($_GET['excluir']);
    
    
    $resultadoExclusao = excluirRegistro($conn, 'agenda', 'id_agenda', $id_agenda);

    if ($resultadoExclusao === true) {
        $mensagem = "Horário excluído com sucesso!";
        $mensagem_tipo = "alert-success";
    } else {
        $mensagem = $resultadoExclusao;
        $mensagem_tipo = "alert-danger";
    }

    header("Location: gerenciar_agenda.php?id_medico=$id_medico&mensagem=" . urlencode($mensagem) . "&tipo=" . urlencode($mensagem_tipo));
    exit();
}

// ===========================
// LISTAGEM COMPLETA (sem filtro)
// ===========================
$horarios = $conn->query("SELECT * FROM agenda WHERE fk_id_medico = $id_medico ORDER BY data_agenda, hora_inicio");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <title>Editar Agenda - Dr.(a) <?= htmlspecialchars($medico['nome']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="../../assets/css/style.css" />
    <link rel="stylesheet" href="../../assets/css/recepcao.css" />
    <link rel="stylesheet" href="../../assets/css/agenda.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
    .mostrar-mais {
        display: block;
        margin: 15px auto;
        background-color: #2563eb;
        color: #fff;
        border: none;
        padding: 8px 15px;
        border-radius: 8px;
        cursor: pointer;
    }

    .mostrar-mais:hover {
        background-color: #1d4ed8;
    }

    .hidden-row {
        display: none;
    }
    </style>
</head>

<body>
    <header class="header">
        <div class="container header-content">
            <h1>Agenda do Dr.(a) <?= htmlspecialchars($medico['nome']) ?></h1>
            <a href="../../autenticacao/logout.php" class="logout-btn">Sair</a>
        </div>
    </header>

    <main class="agenda-container">
        <a href="gerenciar_agenda.php" class="btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>

        <?php if (!empty($mensagem)): ?>
        <p class="alert <?= $mensagem_tipo ?>"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>

        <h2>Horários Cadastrados</h2>

        <?php if ($horarios->num_rows > 0): ?>
        <table class="styled-table" id="tabelaHorarios">
            <thead>
                <tr>
                    <th style="text-align:center;">Dia</th>
                    <th style="text-align:center;">Data</th>
                    <th style="text-align:center;">Início</th>
                    <th style="text-align:center;">Fim</th>
                    <th style="text-align:center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php $contador = 0; ?>
                <?php while ($row = $horarios->fetch_assoc()): ?>
                <tr class="<?= $contador >= 4 ? 'hidden-row' : '' ?>">
                    <td style="text-align:center;"><?= htmlspecialchars($row['dia_semana']) ?></td>
                    <td style="text-align:center;"><?= date('d/m/Y', strtotime($row['data_agenda'])) ?></td>
                    <td style="text-align:center;"><?= substr($row['hora_inicio'], 0, 5) ?></td>
                    <td style="text-align:center;"><?= substr($row['hora_fim'], 0, 5) ?></td>
                    <td style="text-align:center;">
                        <a href="?id_medico=<?= $id_medico ?>&excluir=<?= $row['id_agenda'] ?>" class="delete-btn"
                            onclick="return confirm('Deseja realmente excluir este horário?')">
                            <i class="fas fa-trash-alt"></i> Excluir
                        </a>
                        <a href="editar_agenda.php?id_agenda=<?= $row['id_agenda'] ?>" class="edit-btn">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    </td>
                </tr>
                <?php $contador++; ?>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($contador > 10): ?>
        <button id="toggleMostrar" class="mostrar-mais">Mostrar mais</button>
        <?php endif; ?>

        <?php else: ?>
        <p style="text-align:center; color: #6b7280; font-style: italic;">Nenhum horário cadastrado ainda.</p>
        <?php endif; ?>

        <h2>Gerar Novos Horários</h2>
        <form method="POST" class="form-agenda">
            <label>Dias da Semana:</label>
            <div class="dias-semana-container">
                <?php for($i = 1; $i <= 7; $i++): ?>
                <label class="dia-btn">
                    <input type="checkbox" name="dias_semana[]" value="<?= $i ?>" class="dia-semana">
                    <?= $dias_pt[$i - 1] ?>
                </label>
                <?php endfor; ?>
            </div>

            <label for="inicio">Hora Início:</label>
            <input type="time" name="inicio" id="inicio" required>

            <label for="fim">Hora Fim:</label>
            <input type="time" name="fim" id="fim" required>

            <button type="submit"><i class="fas fa-plus-circle"></i> Gerar Horários</button>
        </form>
    </main>

    <script>
    // Exibir mais/menos horários
    const toggleBtn = document.getElementById('toggleMostrar');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const hiddenRows = document.querySelectorAll('.hidden-row');
            const isHidden = hiddenRows[0].style.display === '' || hiddenRows[0].style.display === 'none';
            hiddenRows.forEach(row => row.style.display = isHidden ? 'table-row' : 'none');
            toggleBtn.textContent = isHidden ? 'Mostrar menos' : 'Mostrar mais';
        });
    }
    </script>
</body>

</html>