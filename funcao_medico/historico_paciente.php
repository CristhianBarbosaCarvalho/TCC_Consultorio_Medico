<?php
require_once '../config_BD/conexaoBD.php';
require_once '../autenticacao/verificar_login.php';
verificarAcesso(['medico']);

$paciente_id = isset($_GET['id_paciente']) ? intval($_GET['id_paciente']) : 0;
$historicos = [];

if ($paciente_id > 0) {
    $sql = "SELECT h.*, m.nome AS medico_nome 
            FROM historico_clinico h 
            INNER JOIN medicos m ON h.fk_id_medico = m.id_medico 
            WHERE h.fk_id_paciente = ? 
            ORDER BY h.data_registro DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $paciente_id);
    $stmt->execute();
    $historicos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $term = trim($_POST['term']);
    $sql = "SELECT id_paciente, nome 
            FROM paciente 
            WHERE nome LIKE ? OR cpf LIKE ? 
            LIMIT 20";
    $like = "%$term%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $patients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Histórico do Paciente</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.card{background:#fff;padding:25px;border-radius:10px;box-shadow:0 0 12px rgba(0,0,0,0.1);margin-top:30px;}
.table{width:100%;border-collapse:collapse}
th,td{padding:12px 10px;border-bottom:1px solid #ddd;text-align:left;vertical-align:top;}
th{background:#585B66;color:#fff}
.btn-sm{padding:6px 10px;border-radius:5px;color:#fff;text-decoration:none;background:#4e5251}
.btn-sm:hover{background:#3f4443}
.search-form{display:flex;gap:10px;margin-bottom:20px;}
.search-form input{flex:1;padding:10px;border-radius:6px;border:1px solid #ccc;}
.search-form button{padding:10px 15px;background:#4e5251;color:#fff;border:none;border-radius:6px;cursor:pointer;}
.search-form button:hover{background:#3f4443;}
ul{list-style:none;padding:0;margin:0;}
li{background:#f1f3f5;padding:10px 12px;border-radius:6px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;}
li a{text-decoration:none;background:#4e5251;color:#fff;padding:6px 10px;border-radius:5px;font-size:14px;}
li a:hover{background:#3f4443;}
.registro{background:#f9f9f9;border-left:5px solid #4e5251;padding:15px;border-radius:6px;margin-bottom:15px;box-shadow:0 2px 6px rgba(0,0,0,0.05);}
.btn-voltar{display:inline-block;margin-top:15px;padding:10px 15px;border-radius:6px;background:#6c757d;color:#fff;text-decoration:none;}
.btn-voltar:hover{background:#5a6268;}
</style>
</head>
<body>
<header class="header">
  <div class="container header-content"><h1>Histórico do Paciente</h1></div>
</header>

<nav class="navbar">
  <div class="container">
    <ul class="nav-list">
      <a href="../dashboard_users/medico.php" class="nav-link">Voltar ao Painel</a>
    </ul>
  </div>
</nav>

<div class="container">
  <div class="card">
    <?php if ($paciente_id <= 0): ?>
      <h2 class="form-title">Buscar Paciente</h2>
      <form method="POST" class="search-form">
        <input type="text" name="term" placeholder="Digite o nome ou CPF..." required>
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
      </form>

      <?php if (!empty($patients)): ?>
        <ul>
          <?php foreach($patients as $p): ?>
            <li>
              <span><?= htmlspecialchars($p['nome']) ?></span>
              <a href="historico_paciente.php?id_paciente=<?= intval($p['id_paciente']) ?>">
                <i class="fa-solid fa-eye"></i> Ver histórico
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

    <?php else: ?>
      <a href="historico_paciente.php" class="btn-sm" style="background:#6c757d;margin-bottom:15px;">
        <i class="fa-solid fa-arrow-left"></i> Nova busca
      </a>
      <h2 class="form-title">Registros do Paciente</h2>

      <?php if (empty($historicos)): ?>
        <p style="color:#555;">Nenhum registro de histórico clínico encontrado para este paciente.</p>
      <?php else: ?>
        <?php foreach($historicos as $h): ?>
          <div class="registro">
            <strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($h['data_registro'])) ?><br>
            <strong>Médico:</strong> <?= htmlspecialchars($h['medico_nome']) ?><br><br>
            <strong>Sintomas:</strong><br><?= nl2br(htmlspecialchars($h['sintomas'])) ?><br><br>
            <strong>Diagnóstico:</strong><br><?= nl2br(htmlspecialchars($h['diagnostico'])) ?><br><br>
            <strong>Prescrição:</strong><br><?= nl2br(htmlspecialchars($h['prescricao'])) ?><br><br>
            <strong>Observações:</strong><br><?= nl2br(htmlspecialchars($h['observacoes'])) ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
