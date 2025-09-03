<?php
require_once '../../autenticacao/verificar_login.php';
require_once '../../config_BD/conexaoBD.php';
require_once '../../functions/delete.php';

$mensagem = '';
$paciente = null;

function formatarCPF($cpf) {
    $cpf = preg_replace('/\D/', '', $cpf);
    return preg_replace("/^(\d{3})(\d{3})(\d{3})(\d{2})$/", "$1.$2.$3-$4", $cpf);
}

function formatarTelefone($tel) {
    $tel = preg_replace('/\D/', '', $tel);
    return preg_replace("/^(\d{2})(\d{5})(\d{4})$/", "($1) $2-$3", $tel);
}

if (isset($_GET['id_paciente']) && is_numeric($_GET['id_paciente'])) {
    $id = intval($_GET['id_paciente']);

    // Buscar paciente
    $stmt = $conn->prepare("SELECT * FROM paciente WHERE id_paciente = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $paciente = $resultado->fetch_assoc();
    } else {
        $mensagem = "Paciente não encontrado.";
    }
    $stmt->close();

    // Excluir paciente se enviado por POST
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $resultado = excluirRegistro($conn, 'paciente', 'id_paciente', $id);
        if ($resultado === true) {
            $mensagem = "Paciente excluído com sucesso!";
            $paciente = null;
        } else {
            $mensagem = $resultado;
        }
    }
} else {
    $mensagem = "ID inválido.";
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Excluir Paciente</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <h1>Excluir Paciente</h1>
            <a href="../views/logout.php" class="logout-btn">Sair</a>
        </div>
    </header>

    <nav class="navbar">
        <div class="container">
            <ul class="nav-list">
                <a href="pacientes_cadastrados.php" class="nav-link">Voltar</a>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <?php if ($mensagem): ?>
            <p class="alert <?= str_contains($mensagem, 'sucesso') ? 'alert-success' : 'alert-danger' ?>">
                <?= $mensagem ?>
            </p>
        <?php endif; ?>

        <?php if ($paciente): ?>
            <div class="card" style="max-width: 500px; margin: 0 auto; text-align: left; display: flex; flex-direction: column; align-items: center;">
                <h2 class="form-title" style="text-align: center;">Confirmação de Exclusão</h2>
                <p style="width: 100%;"><strong>Nome:</strong> <?= htmlspecialchars($paciente['nome']) ?></p>
                <p style="width: 100%;"><strong>CPF:</strong> <?= formatarCPF($paciente['cpf']) ?></p>
                <p style="width: 100%;"><strong>Telefone:</strong> <?= formatarTelefone($paciente['telefone']) ?></p>
                <p style="width: 100%;"><strong>Email:</strong> <?= htmlspecialchars($paciente['email']) ?></p>
                <br>

                <form method="POST">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este paciente?');">
                        Confirmar Exclusão
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>