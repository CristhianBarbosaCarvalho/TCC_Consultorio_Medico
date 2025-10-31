<?php
require_once '../../autenticacao/verificar_login.php';
require_once '../../config_BD/conexaoBD.php';

// Verificar se o ID da consulta foi enviado
if (!isset($_GET['id_consulta'])) {
    header("Location: gerenciar_agenda.php");
    exit();
}

$id_consulta = intval($_GET['id_consulta']);

// Buscar dados da consulta
$consulta = $conn->query("
    SELECT c.data_hora, p.nome AS paciente_nome, m.nome AS medico_nome
    FROM consulta c
    JOIN paciente p ON c.fk_id_paciente = p.id_paciente
    JOIN medicos m ON c.fk_id_medico = m.id_medico
    WHERE c.id_consulta = $id_consulta
")->fetch_assoc();

if (!$consulta) {
    echo "<p>Consulta não encontrada.</p>";
    exit();
}

// Processar envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_pagamento = $_POST['data_pagamento'];
    $valor = $_POST['valor'];
    $forma = $_POST['forma_de_pagamento'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("
        INSERT INTO pagamentos (data_pagamento, valor, forma_de_pagamento, status, fk_id_consulta)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sdssi", $data_pagamento, $valor, $forma, $status, $id_consulta);
    $stmt->execute();
    $stmt->close();

    header("Location: listar_pagamentos.php?id_consulta=$id_consulta");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Registrar Pagamento</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/recepcao.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .pagamento-container {
            max-width: 600px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
        }

        form {
            display: grid;
            gap: 15px;
        }

        input,
        select {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }

        input:focus,
        select:focus {
            border-color: #3b82f6;
            outline: none;
        }

        button {
            background: #3b82f6;
            color: #fff;
            font-weight: 600;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #2563eb;
        }
    </style>
</head>

<body>
    <div class="pagamento-container">
        <h1>Registrar Pagamento</h1>

        <p><strong>Médico:</strong> <?= htmlspecialchars($consulta['medico_nome']) ?></p>
        <p><strong>Paciente:</strong> <?= htmlspecialchars($consulta['paciente_nome']) ?></p>
        <p><strong>Data da Consulta:</strong> <?= date('d/m/Y H:i', strtotime($consulta['data_hora'])) ?></p>

        <form method="POST">
            <label for="data_pagamento">Data do Pagamento:</label>
            <input type="date" name="data_pagamento" id="data_pagamento" required value="<?= date('Y-m-d') ?>">

            <label for="valor">Valor (R$):</label>
            <input type="number" name="valor" id="valor" step="0.01" required>

            <label for="forma_de_pagamento">Forma de Pagamento:</label>
            <select name="forma_de_pagamento" id="forma_de_pagamento" required>
                <option value="">Selecione</option>
                <option>Dinheiro</option>
                <option>Cartão de Crédito</option>
                <option>Cartão de Débito</option>
                <option>Pix</option>
                <option>Cheque</option>
            </select>

            <label for="status">Status:</label>
            <select name="status" id="status" required>
                <option value="">Selecione</option>
                <option>Pago</option>
                <option>Pendente</option>
            </select>

            <button type="submit"><i class="fas fa-dollar-sign"></i> Registrar Pagamento</button>
        </form>
    </div>
</body>

</html>