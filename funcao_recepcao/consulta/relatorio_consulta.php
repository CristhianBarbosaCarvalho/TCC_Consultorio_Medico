<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso([ 'recepcao']);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Consultas por Paciente</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/relatorio_consulta.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        form {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        input[type="text"] {
            padding: 8px;
            width: 280px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .btn {
            padding: 6px 10px;
            border-radius: 6px;
            text-decoration: none;
            color: #fff;
            font-size: 0.9rem;
        }

        .btn-view { background-color: #3498db; }
        .btn-edit { background-color: #f1c40f; }
        .btn-delete { background-color: #e74c3c; }
        .btn-add { background-color: #007bff; }

        .btn:hover { opacity: 0.9; }
    </style>
</head>

<body>

    <div class="container">
        <h2>Relatório de Consultas por Paciente</h2>

        <!-- 🔍 Campo de busca -->
        <form method="GET">
            <input type="text" name="busca" placeholder="Digite o nome ou CPF do paciente" value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
            <button type="submit"><i class="fas fa-search"></i> Buscar</button>
        </form>

        <?php
        if (isset($_GET['busca']) && $_GET['busca'] !== ''):
            $busca = $conn->real_escape_string($_GET['busca']);

            $sql = "
                SELECT 
                    c.id_consulta,
                    p.nome AS paciente,
                    p.cpf AS cpf_paciente,
                    m.nome AS medico,
                    GROUP_CONCAT(DISTINCT e.nome SEPARATOR ', ') AS especialidades,
                    c.data_hora,
                    c.status AS status_consulta,
                    IFNULL(pay.status,'Pendente') AS status_pagamento
                FROM consulta c
                INNER JOIN paciente p ON c.fk_id_paciente = p.id_paciente
                INNER JOIN medicos m ON c.fk_id_medico = m.id_medico
                LEFT JOIN medico_especialidade me ON m.id_medico = me.id_medico
                LEFT JOIN especialidade e ON me.id_especialidade = e.id_especialidade
                LEFT JOIN pagamentos pay ON c.id_consulta = pay.fk_id_consulta
                WHERE p.nome LIKE '%$busca%' OR p.cpf LIKE '%$busca%'
                GROUP BY c.id_consulta, p.nome, p.cpf, m.nome, c.data_hora, c.status, pay.status
                ORDER BY c.data_hora DESC
            ";

            $result = $conn->query($sql);
        ?>

        <table>
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>CPF</th>
                    <th>Médico</th>
                    <th>Especialidades</th>
                    <th>Data/Hora</th>
                    <th>Status Consulta</th>
                    <th>Status Pagamento</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['paciente']) ?></td>
                            <td><?= htmlspecialchars($row['cpf_paciente']) ?></td>
                            <td><?= htmlspecialchars($row['medico']) ?></td>
                            <td><?= htmlspecialchars($row['especialidades'] ?? '—') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['data_hora'])) ?></td>
                            <td><?= htmlspecialchars($row['status_consulta']) ?></td>
                            <td><?= htmlspecialchars($row['status_pagamento']) ?></td>
                            <td>
                                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                                    <?php if ($row['status_pagamento'] === 'Pendente'): ?>
                                        <a href="../pagamento/registrar_pagamento.php?id_consulta=<?= $row['id_consulta'] ?>" class="btn btn-add">
                                            <i class="fas fa-credit-card"></i> Registrar Pagamento
                                        </a>
                                    <?php else: ?>
                                        <a href="../pagamento/visualizar_pagamento.php?id_consulta=<?= $row['id_consulta'] ?>" class="btn btn-view">
                                            <i class="fas fa-eye"></i> Ver Pagamento
                                        </a>
                                    <?php endif; ?>

                                    <a href="detalhes_consulta.php?id=<?= $row['id_consulta'] ?>" class="btn btn-view">
                                        <i class="fas fa-eye"></i> Ver Consulta
                                    </a>

                                    <a href="editar_consulta.php?id=<?= $row['id_consulta'] ?>" class="btn btn-edit">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>

                                    <a href="excluir_consulta.php?id=<?= $row['id_consulta'] ?>" class="btn btn-delete" onclick="return confirm('Tem certeza que deseja excluir esta consulta?')">
                                        <i class="fas fa-trash"></i> Excluir
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center;">Nenhuma consulta encontrada para este paciente.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php else: ?>
            <p style="text-align:center; margin-top:20px;">Digite o nome ou CPF do paciente para visualizar as consultas.</p>
        <?php endif; ?>
    </div>

</body>
</html>
