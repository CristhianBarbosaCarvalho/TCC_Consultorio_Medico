<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['medico']);

// Verifica se o usuário logado é médico
if ($_SESSION['usuario_tipo'] !== 'medico') {
    header("Location: ../erro-permissao.php");
    exit();
}

$medico_id = $_SESSION['usuario_id']; // ID do médico logado
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Consultas Agendadas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .container {
            width: 90%;
            margin: 40px auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
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
            display: inline-block;
            margin: 5px;
        }

        .btn-view {
            background-color: #3498db;
        }

        .btn-edit {
            background-color: #f1c40f;
        }

        .btn-delete {
            background-color: #e74c3c;
        }

        .btn:hover {
            opacity: 0.9;
        }

        /* Centraliza o botão Voltar */
        .voltar-btn {
            text-align: center;
            margin: 30px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Consultas Agendadas</h2>

        <div class="voltar-btn">
            <a href="../dashboard_users/medico.php" class="btn btn-edit">
                <i class="fas fa-arrow-left"></i> Voltar ao Painel
            </a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Data/Hora</th>
                    <th>Status Consulta</th>
                    <th>Status Pagamento</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Consulta apenas as consultas do médico logado
                $sql = "
                    SELECT 
                        c.id_consulta,
                        p.nome AS paciente,
                        c.data_hora,
                        c.status AS status_consulta,
                        IFNULL(pay.status,'Pendente') AS status_pagamento
                    FROM consulta c
                    INNER JOIN paciente p ON c.fk_id_paciente = p.id_paciente
                    LEFT JOIN pagamentos pay ON c.id_consulta = pay.fk_id_consulta
                    WHERE c.fk_id_medico = ?
                    ORDER BY c.data_hora DESC
                ";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $medico_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                        <tr>
                            <td><?= htmlspecialchars($row['paciente']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['data_hora'])) ?></td>
                            <td><?= htmlspecialchars($row['status_consulta']) ?></td>
                            <td><?= htmlspecialchars($row['status_pagamento']) ?></td>
                            <td>
                                <a href="../funcao_medico/detalhes_consulta.php?id=<?= $row['id_consulta'] ?>" class="btn btn-view">
                                    <i class="fas fa-eye"></i> Ver Consulta
                                </a>
                                <?php if ($row['status_pagamento'] !== 'Pendente'): ?>
                                    <a href="../funcao_recepcao/pagamento/visualizar_pagamento.php?id_consulta=<?= $row['id_consulta'] ?>" class="btn btn-view">
                                        <i class="fas fa-eye"></i> Ver Pagamento
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                <?php
                    endwhile;
                else:
                    echo "<tr><td colspan='5' style='text-align:center;'>Nenhuma consulta agendada encontrada.</td></tr>";
                endif;
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>