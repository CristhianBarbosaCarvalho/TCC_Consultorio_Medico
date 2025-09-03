<?php
require_once '../autenticacao/verificar_login.php';
require_once '../config_BD/conexaoBD.php';
require_once '../functions/insert.php';

$mensagem = '';
$mensagem_tipo = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $crm = $_POST['crm']; 
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $especialidade = $_POST['especialidade'];

    // Criptografar a senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Criar o array de dados para inserção
    $dados = [
        'crm' => $crm,
        'nome' => $nome,
        'telefone' => $telefone,
        'email' => $email,
        'senha' => $senha_hash,
        'especialidade' => $especialidade
    ];

    // Chama a função genérica para inserir no banco
    $resultado = inserirDados('medicos', $dados);

    if ($resultado === true) {
        $mensagem = "Médico cadastrado com sucesso!";
        $mensagem_tipo = "alert-success";
    } else {
        $mensagem = "Erro ao cadastrar médico: " . $resultado;
        $mensagem_tipo = "alert-danger";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Médico</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/formatacao.js" defer></script>
</head>
<body>

<header class="header">
    <div class="container header-content">
        <h1>Cadastrar Médico</h1>
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
    <?php if (!empty($mensagem)) : ?>
        <p class="alert <?= $mensagem_tipo ?>"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <div class="card">
        <form method="POST" class="form-content">
            <div class="form-group">
                <label class="form-label">CRM:</label>
                <input type="text" name="crm" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Nome:</label>
                <input type="text" name="nome" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Telefone:</label>
                <input type="text" name="telefone" class="form-control telefone-mask" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email:</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Senha:</label>
                <input type="password" name="senha" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Especialidade:</label>
                <input type="text" name="especialidade" class="form-control" required>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Cadastrar</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
