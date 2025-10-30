<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['admin']);
require_once '../functions/update.php';
require_once '../functions/busca.php';

$id = $_SESSION['usuario_id'];
$mensagem = "";
$mensagem_tipo = "";

// Processamento do formulário
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $senha = $_POST['senha'];

    $dados = [
        'email' => $email,
        'telefone' => $telefone
    ];

    if (!empty($senha)) {
        $dados['senha'] = password_hash($senha, PASSWORD_DEFAULT);
    }

    $resultado = atualizarDados('administrador', $dados, 'id_admin', $id);

    if ($resultado === true) {
        $mensagem = "Dados atualizados com sucesso!";
        $mensagem_tipo = "alert-success";
    } else {
        $mensagem = "Erro ao atualizar dados: $resultado";
        $mensagem_tipo = "alert-danger";
    }
}

// Buscar dados atuais
$filtros = ['id_admin' => $id];
$config_campos = ['id_admin' => ['operador' => '=', 'tipo' => 'i']];
$resultado = buscarDados($conn, 'administrador', $filtros, $config_campos);

if ($resultado->num_rows === 1) {
    $dados = $resultado->fetch_assoc();
    $email_atual = $dados['email'];
    $telefone_atual = $dados['telefone'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Administrador</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<script src="../assets/Js/script_formatar_editar.js"></script>
<body>

<header class="header">
    <div class="container header-content">
        <h1>Editar Perfil - Administrador</h1>
        <a href="../views/logout.php" class="logout-btn">Sair</a>
    </div>
</header>

<nav class="navbar">
    <div class="container">
        <ul class="nav-list">
            <a href="../dashboard_users/administracao.php" class="nav-link">Voltar</a>
        </ul>
    </div>
</nav>

<div class="container" style="margin-top: 100px; max-width: 600px;">
    <?php if (!empty($mensagem)): ?>
        <p class="alert <?= $mensagem_tipo ?>"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <div class="card">
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Email:</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email_atual) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Telefone:</label>
                <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($telefone_atual) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Nova Senha (deixe em branco se não quiser alterar):</label>
                <input type="password" name="senha" class="form-control">
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
