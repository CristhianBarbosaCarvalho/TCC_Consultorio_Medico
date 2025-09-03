<?php
require_once '../../autenticacao/verificar_login.php';
require_once '../../config_BD/conexaoBD.php';

if (!isset($_GET['id_medico'])) {
    header("Location: gerenciar_agenda.php");
    exit();
}

$id_medico = intval($_GET['id_medico']);

// Buscar dados do médico
$medico = $conn->query("SELECT nome, especialidade FROM medicos WHERE id_medico = $id_medico")->fetch_assoc();

// Inserção de novo horário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dia'], $_POST['inicio'], $_POST['fim'])) {
    $dia = $_POST['dia'];
    $inicio = $_POST['inicio'];
    $fim = $_POST['fim'];

    $stmt = $conn->prepare("INSERT INTO agenda (dia_semana, hora_inicio, hora_fim, fk_id_medico) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $dia, $inicio, $fim, $id_medico);
    $stmt->execute();
    $stmt->close();

    header("Location: editar_agenda.php?id_medico=$id_medico");
    exit();
}

// Exclusão de horário
if (isset($_GET['excluir'])) {
    $id_agenda = intval($_GET['excluir']);
    $conn->query("DELETE FROM agenda WHERE id_agenda = $id_agenda AND fk_id_medico = $id_medico");
    header("Location: editar_agenda.php?id_medico=$id_medico");
    exit();
}

// Buscar horários existentes
$horarios = $conn->query("SELECT * FROM agenda WHERE fk_id_medico = $id_medico ORDER BY FIELD(dia_semana, 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'), hora_inicio");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Editar Agenda - Dr.(a) <?= htmlspecialchars($medico['nome']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="../../assets/css/style.css" />
    <link rel="stylesheet" href="../../assets/css/recepcao.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .agenda-container {
            max-width: 800px;
            margin: 20px auto 60px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }

        h1, h2 {
            color: #333;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .btn-voltar {
            display: inline-block;
            margin-bottom: 25px;
            background: #3b82f6;
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .btn-voltar:hover {
            background: #2563eb;
        }

        .styled-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 35px;
        }
        .styled-table thead tr {
            background-color: #3b82f6;
            color: #ffffff;
            text-align: left;
            font-weight: 600;
        }
        .styled-table th,
        .styled-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
        }
        .styled-table tbody tr:hover {
            background-color: #f3f4f6;
        }

        .delete-btn {
            background: #ef4444;
            color: white;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background-color 0.3s ease;
        }
        .delete-btn:hover {
            background: #b91c1c;
        }

        form.form-agenda {
            display: grid;
            gap: 18px;
            max-width: 450px;
            margin: 0 auto;
            padding-bottom: 20px;
        }
        form.form-agenda label {
            font-weight: 600;
            color: #374151;
        }
        form.form-agenda select,
        form.form-agenda input[type="time"] {
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        form.form-agenda select:focus,
        form.form-agenda input[type="time"]:focus {
            border-color: #3b82f6;
            outline: none;
        }
        form.form-agenda button {
            background: #3b82f6;
            color: white;
            font-weight: 600;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.3s ease;
        }
        form.form-agenda button:hover {
            background: #2563eb;
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

        <h2>Horários Cadastrados</h2>

        <?php if ($horarios->num_rows > 0): ?>
            <table class="styled-table" aria-label="Tabela de horários cadastrados">
                <thead>
                    <tr>
                        <th>Dia</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $horarios->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['dia_semana']) ?></td>
                            <td><?= substr($row['hora_inicio'], 0, 5) ?></td>
                            <td><?= substr($row['hora_fim'], 0, 5) ?></td>
                            <td>
                                <a href="?id_medico=<?= $id_medico ?>&excluir=<?= $row['id_agenda'] ?>" 
                                   class="delete-btn" 
                                   onclick="return confirm('Deseja realmente excluir este horário?')"
                                   aria-label="Excluir horário <?= htmlspecialchars($row['dia_semana']) ?> das <?= substr($row['hora_inicio'], 0, 5) ?> às <?= substr($row['hora_fim'], 0, 5) ?>">
                                    <i class="fas fa-trash-alt"></i> Excluir
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center; color: #6b7280; font-style: italic;">Nenhum horário cadastrado ainda.</p>
        <?php endif; ?>

        <h2>Adicionar Horário</h2>

        <form method="POST" class="form-agenda" aria-label="Formulário para adicionar novo horário à agenda">
            <label for="dia">Dia da Semana:</label>
            <select name="dia" id="dia" required>
                <option value="">Selecione</option>
                <option>Segunda</option>
                <option>Terça</option>
                <option>Quarta</option>
                <option>Quinta</option>
                <option>Sexta</option>
                <option>Sábado</option>
                <option>Domingo</option>
            </select>

            <label for="inicio">Hora Início:</label>
            <input type="time" name="inicio" id="inicio" required>

            <label for="fim">Hora Fim:</label>
            <input type="time" name="fim" id="fim" required>

            <button type="submit" aria-label="Adicionar novo horário">
                <i class="fas fa-plus-circle"></i> Adicionar Horário
            </button>
        </form>
    </main>
</body>
</html>
