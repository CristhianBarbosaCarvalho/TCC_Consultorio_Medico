<?php
require_once '../config_BD/conexaoBD.php';
require_once '../autenticacao/verificar_login.php';
verificarAcesso(['medico']);

if ($_SESSION['usuario_tipo'] !== 'medico') {
    header("Location: ../erro-permissao.php");
    exit();
}

$medico_id = intval($_SESSION['usuario_id']);
$sql = "
    SELECT 
        c.id_consulta,
        p.nome AS paciente,
        c.data_hora,
        c.status AS status_consulta,
        COALESCE(pay.status,'pendente') AS status_pagamento
    FROM consulta c
    INNER JOIN paciente p ON c.fk_id_paciente = p.id_paciente
    LEFT JOIN pagamentos pay ON c.id_consulta = pay.fk_id_consulta
    WHERE c.fk_id_medico = ? 
        AND c.status = 'confirmada'
    ORDER BY c.data_hora DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $medico_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Consultas Agendadas</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* pequenos estilos inline (mantive o seu visual) */
.card{background:#fff;padding:25px;border-radius:10px;box-shadow:0 0 12px rgba(0,0,0,0.1);margin-top:30px;}
.table{width:100%;border-collapse:collapse}
th,td{padding:12px 10px;border-bottom:1px solid #ddd;text-align:left}
th{background:#585B66;color:#fff}
.action-buttons{display:flex;gap:6px}
.btn-sm{padding:6px 10px;border-radius:5px;color:#fff;text-decoration:none}
.btn-view{background:#4e5251}
</style>
</head>
<body>
<header class="header"><div class="container header-content"><h1>Consultas Agendadas</h1></div></header>
<nav class="navbar"><div class="container"><ul class="nav-list"><a href="../dashboard_users/medico.php" class="nav-link">Voltar ao Painel</a></ul></div></nav>

<div class="container" style="margin-top:20px;">
    <div class="card">
        <h2 class="form-title">Minhas Consultas</h2>
        <table class="table">
            <thead>
                <tr><th>Paciente</th><th>Data/Hora</th><th>Status Consulta</th><th>Status Pagamento</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['paciente']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['data_hora'])) ?></td>
                            <td><?= htmlspecialchars($row['status_consulta']) ?></td>
                            <td><?= htmlspecialchars($row['status_pagamento']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a class="btn-sm btn-view" href="../funcao_medico/detalhes_consulta.php?id=<?= intval($row['id_consulta']) ?>">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <a class="btn-sm btn-view" href="../funcao_medico/prontuario.php?id_consulta=<?= intval($row['id_consulta']) ?>">
                                        <i class="fas fa-notes-medical"></i> Prontuário
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">Nenhuma consulta agendada encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
