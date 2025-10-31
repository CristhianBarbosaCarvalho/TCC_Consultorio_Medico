<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['recepcao']);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Consulta inválida.");
}

$id_consulta = intval($_GET['id']);

// Buscar dados da consulta
$sql = "SELECT 
            c.*, 
            p.nome AS paciente, 
            m.id_medico, 
            m.nome AS medico,
            e.id_especialidade,
            e.nome AS especialidade,
            pg.valor,
            pg.forma_de_pagamento,
            pg.status AS status_pagamento
        FROM consulta c
        INNER JOIN paciente p ON c.fk_id_paciente = p.id_paciente
        INNER JOIN medicos m ON c.fk_id_medico = m.id_medico
        INNER JOIN medico_especialidade me ON me.id_medico = m.id_medico
        INNER JOIN especialidade e ON e.id_especialidade = me.id_especialidade
        LEFT JOIN pagamentos pg ON pg.fk_id_consulta = c.id_consulta
        WHERE c.id_consulta = $id_consulta";

$result = $conn->query($sql);
if (!$result || $result->num_rows === 0) {
    die("Consulta não encontrada.");
}

$consulta = $result->fetch_assoc();

if (strtolower($consulta['status']) === 'concluída') {
    die("❌ Esta consulta já foi concluída e não pode ser editada.");
}

// Atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_medico = $_POST['id_medico'] ?? '';
    $data = $_POST['data_consulta'] ?? '';
    $hora = $_POST['hora_consulta'] ?? '';
    $status = $_POST['status'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $forma = $_POST['forma_de_pagamento'] ?? '';
    $status_pg = $_POST['status_pagamento'] ?? '';

    if (empty($id_medico) || empty($data) || empty($hora) || empty($status)) {
        $erro = "Preencha todos os campos obrigatórios.";
    } else {
        $data_hora = "$data $hora:00";

        // Atualiza dados da consulta
        $stmt = $conn->prepare("UPDATE consulta 
            SET fk_id_medico=?, data_hora=?, status=?, observacoes=? 
            WHERE id_consulta=?");
        $stmt->bind_param("isssi", $id_medico, $data_hora, $status, $observacoes, $id_consulta);

        if ($stmt->execute()) {

            // Atualiza ou insere pagamento
            $check = $conn->query("SELECT id_pagamento FROM pagamentos WHERE fk_id_consulta = $id_consulta");
            if ($check->num_rows > 0) {
                $stmt2 = $conn->prepare("UPDATE pagamentos 
                    SET valor=?, forma_de_pagamento=?, status=? 
                    WHERE fk_id_consulta=?");
                $stmt2->bind_param("dssi", $valor, $forma, $status_pg, $id_consulta);
            } else {
                $stmt2 = $conn->prepare("INSERT INTO pagamentos 
                    (fk_id_consulta, valor, forma_de_pagamento, status) 
                    VALUES (?, ?, ?, ?)");
                $stmt2->bind_param("idss", $id_consulta, $valor, $forma, $status_pg);
            }
            $stmt2->execute();

            // ===============================
            // 🔹 Atualiza tabela AGENDAMENTO com a mesma regra do marcar_consulta.php
            // ===============================

            // Mapeia status da consulta → status do agendamento/pagamento
            $status_map = [
                'confirmado'  => 'pago',
                'confirmada'  => 'pago',
                'agendada'    => 'pendente',
                'pendente'    => 'pendente',
                'cancelado'   => 'cancelado',
                'cancelada'   => 'cancelado',
                'finalizado'  => 'finalizado',
                'concluido'   => 'finalizado',
                'concluída'   => 'finalizado'
            ];

            $status_normalizado = strtolower(trim($status));
            $status_agendamento = $status_map[$status_normalizado] ?? 'pendente';

            // Busca agendamento relacionado à consulta
            $resAgenda = $conn->query("SELECT a.id_agendamento, c.fk_id_agenda, c.fk_id_paciente
                                       FROM agendamento a
                                       JOIN consulta c ON c.fk_id_agenda = a.fk_id_agenda
                                       WHERE c.id_consulta = $id_consulta
                                       LIMIT 1");

            if ($resAgenda && $resAgenda->num_rows > 0) {
                // Atualiza o agendamento existente
                $rowAg = $resAgenda->fetch_assoc();
                $id_agendamento = $rowAg['id_agendamento'];
                $fk_id_paciente = $rowAg['fk_id_paciente'];

                $stmt3 = $conn->prepare("UPDATE agendamento 
                    SET fk_id_paciente=?, dia_consulta=?, hora_agendada=?, status=?, observacoes=? 
                    WHERE id_agendamento=?");
                $stmt3->bind_param("issssi", $fk_id_paciente, $data, $hora, $status_agendamento, $observacoes, $id_agendamento);
                $stmt3->execute();
            }

            // ✅ Tudo atualizado, redireciona normalmente
            header("Location: relatorio_consulta.php?busca=" . urlencode($consulta['paciente']) . "&status=sucesso");
            exit;

        } else {
            $erro = "Erro ao atualizar consulta: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Editar Consulta</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body {
            background-color: #f7f9fb;
            font-family: Arial, sans-serif;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            margin-top: 40px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }

        .form-control,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #2563eb;
        }

        h2,
        h3 {
            color: #333;
            text-align: center;
            margin-bottom: 25px;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }

        #pagamento-section {
            display: none;
        }
    </style>
</head>

<body>

<header class="header">
    <div class="container header-content">
        <h1>Editar Consulta</h1>
    </div>
</header>

<nav class="navbar">
    <div class="container">
        <ul class="nav-list">
            <a href="relatorio_consulta.php?busca=<?= urlencode($consulta['paciente']) ?>&status=sucesso" class="nav-link">Voltar</a>

        </ul>
    </div>
</nav>

<div class="container" style="max-width: 700px; margin-top: 100px;">
    <?php if (!empty($erro)): ?>
        <p class="alert alert-danger"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <div class="card">
        <form method="POST" id="formConsulta" class="form-content">

            <h2>Consulta de <?= htmlspecialchars($consulta['paciente']) ?></h2>

            <div class="form-group">
                <label class="form-label">Especialidade</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($consulta['especialidade']) ?>" readonly>
                <input type="hidden" name="id_especialidade" value="<?= $consulta['id_especialidade'] ?>">
            </div>

            <div class="form-group">
                <label for="id_medico" class="form-label">Médico</label>
                <select id="id_medico" name="id_medico" class="form-control" required onchange="carregarDatas(this.value)">
                    <option value="">Carregando médicos...</option>
                </select>
            </div>

            <div class="form-group">
                <label for="data_consulta" class="form-label">Data da Consulta</label>
                <select id="data_consulta" name="data_consulta" class="form-control" required onchange="carregarHorarios()">
                    <option value="">Selecione o médico primeiro</option>
                </select>
            </div>

            <div class="form-group">
                <label for="hora_consulta" class="form-label">Horário</label>
                <select id="hora_consulta" name="hora_consulta" class="form-control" required>
                    <option value="">Selecione a data</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status da Consulta</label>
                <select name="status" id="status" class="form-control" required onchange="togglePagamento()">
                    <?php
                    $status_opcoes = ['pendente', 'confirmada', 'cancelada'];
                    foreach ($status_opcoes as $opcao):
                        $sel = ($consulta['status'] === $opcao) ? 'selected' : '';
                        echo "<option value='$opcao' $sel>" . ucfirst($opcao) . "</option>";
                    endforeach;
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="observacoes" class="form-label">Observações</label>
                <textarea name="observacoes" id="observacoes" class="form-control"
                          rows="3"><?= htmlspecialchars($consulta['observacoes']) ?></textarea>
            </div>

            <div id="pagamento-section">
                <hr>
                <h3>Pagamento</h3>

                <div class="form-group">
                    <label for="valor" class="form-label">Valor</label>
                    <input type="number" name="valor" id="valor" class="form-control" step="0.01"
                           value="<?= htmlspecialchars($consulta['valor'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="forma_de_pagamento" class="form-label">Forma de Pagamento</label>
                    <select name="forma_de_pagamento" id="forma_de_pagamento" class="form-control">
                        <option value="">Selecione</option>
                        <option value="PIX" <?= ($consulta['forma_de_pagamento'] === 'PIX') ? 'selected' : '' ?>>PIX</option>
                        <option value="Crédito" <?= ($consulta['forma_de_pagamento'] === 'Crédito') ? 'selected' : '' ?>>Crédito</option>
                        <option value="Débito" <?= ($consulta['forma_de_pagamento'] === 'Débito') ? 'selected' : '' ?>>Débito</option>
                        <option value="Dinheiro" <?= ($consulta['forma_de_pagamento'] === 'Dinheiro') ? 'selected' : '' ?>>Dinheiro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status_pagamento" class="form-label">Status do Pagamento</label>
                    <select name="status_pagamento" id="status_pagamento" class="form-control">
                        <option value="pago" <?= ($consulta['status_pagamento'] === 'pago') ? 'selected' : '' ?>>Pago</option>
                        <option value="pendente" <?= ($consulta['status_pagamento'] === 'pendente') ? 'selected' : '' ?>>Pendente</option>
                        <option value="cancelado" <?= ($consulta['status_pagamento'] === 'cancelado') ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
            </div>

            <div style="text-align:center; margin-top:25px;">
                <button type="submit" class="btn">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePagamento() {
        const status = document.getElementById('status').value;
        const pagamento = document.getElementById('pagamento-section');
        pagamento.style.display = (status === 'confirmada') ? 'block' : 'none';
    }
    togglePagamento();

    window.addEventListener('DOMContentLoaded', () => {
        const idEsp = <?= json_encode($consulta['id_especialidade']) ?>;
        const idMedSel = <?= json_encode($consulta['id_medico']) ?>;
        const medicoSelect = document.getElementById('id_medico');

        fetch(`buscar_medicos.php?id_especialidade=${idEsp}`)
            .then(r => r.json())
            .then(data => {
                medicoSelect.innerHTML = '<option value="">Selecione</option>';
                data.forEach(m => {
                    const op = document.createElement('option');
                    op.value = m.id_medico;
                    op.textContent = m.nome;
                    if (m.id_medico == idMedSel) op.selected = true;
                    medicoSelect.appendChild(op);
                });
            });
    });

    function carregarDatas(id_medico) {
        const dataSelect = document.getElementById('data_consulta');
        dataSelect.innerHTML = '<option value="">Carregando...</option>';
        fetch(`buscar_datas.php?id_medico=${id_medico}`)
            .then(r => r.json())
            .then(dates => {
                dataSelect.innerHTML = '<option value="">Selecione</option>';
                dates.forEach(d => {
                    const partes = d.data_agenda.split('-');
                    const dataFormatada = `${partes[2]}/${partes[1]}/${partes[0]}`;
                    const op = document.createElement('option');
                    op.value = d.data_agenda;
                    op.textContent = dataFormatada;
                    dataSelect.appendChild(op);
                });
            });
    }

    function carregarHorarios() {
        const idMed = document.getElementById('id_medico').value;
        const data = document.getElementById('data_consulta').value;
        const sel = document.getElementById('hora_consulta');
        if (!idMed || !data) return;
        sel.innerHTML = '<option value="">Carregando...</option>';
        fetch(`buscar_horarios.php?id_medico=${idMed}&data=${data}`)
            .then(r => r.json())
            .then(horas => {
                sel.innerHTML = '<option value="">Selecione</option>';
                horas.forEach(h => {
                    const op = document.createElement('option');
                    op.value = h.hora;
                    op.textContent = h.hora;
                    sel.appendChild(op);
                });
            });
    }
</script>

</body>
</html>
