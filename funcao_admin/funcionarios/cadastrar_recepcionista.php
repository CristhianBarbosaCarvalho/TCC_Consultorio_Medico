<?php
require_once '../../autenticacao/verificar_login.php';
require_once '../../config_BD/conexaoBD.php';
require_once '../../functions/insert.php';
require_once '../../functions/validacoes.php'; // Inclui funções de validação

$mensagem = '';
$mensagem_tipo = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $senha = $_POST['senha'];

    // Validações básicas
    $cpfLimpo = limparNumero($cpf);
    if (!validarCPF($cpfLimpo)) {
        $mensagem = "❌ CPF inválido!";
        $mensagem_tipo = "alert-danger";
    } elseif (!validarTelefone($telefone)) {
        $mensagem = "❌ Telefone inválido!";
        $mensagem_tipo = "alert-danger";
    } else {
        // Criptografa a senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // Verifica duplicidade usando função global
        $erroDuplicidade = verificarDuplicidadeFuncionario($conn, $cpf, $email);

        if ($erroDuplicidade !== false) {
            $mensagem = $erroDuplicidade;
            $mensagem_tipo = "alert-danger";
        } else {
            // Insere novo recepcionista
            $dados = [
                'nome' => $nome,
                'cpf' => $cpfLimpo,
                'email' => $email,
                'telefone' => $telefone,
                'senha' => $senha_hash
            ];

            $resultado = inserirDados('recepcionista', $dados);

            if ($resultado === true) {
                $mensagem = "✅ Recepcionista cadastrado com sucesso!";
                $mensagem_tipo = "alert-success";
            } else {
                $mensagem = "❌ Erro ao cadastrar recepcionista: " . $resultado;
                $mensagem_tipo = "alert-danger";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Cadastrar Recepcionista</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="../../assets/js/formatacao.js" defer></script>
</head>

<body>
    <header class="header">
        <div class="container header-content">
            <h1>Cadastrar Recepcionista</h1>
            <a href="../views/logout.php" class="logout-btn">Sair</a>
        </div>
    </header>

    <nav class="navbar">
        <div class="container">
            <ul class="nav-list">
                <a href="../../dashboard_users/administracao.php" class="nav-link">Voltar</a>
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
                    <label class="form-label">Nome:</label>
                    <input type="text" name="nome" class="form-control" placeholder="Digite o nome completo" required>
                </div>

                <div class="form-group">
                    <label class="form-label">CPF:</label>
                    <input type="text" name="cpf" class="form-control cpf-mask" placeholder="123.456.789-00" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email" class="form-control" placeholder="exemplo@email.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Telefone:</label>
                    <input type="text" name="telefone" class="form-control telefone-mask" placeholder="(11) 99999-9999"
                        required>
                </div>

                <div class="form-group">
                    <label class="form-label">Senha:</label>
                    <input type="password" name="senha" class="form-control" placeholder="Digite a senha" required>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Cadastrar</button>
                </div>
            </form>
        </div>
    </div>
    <script src="../../assets/Js/mascaras.js"></script>
</body>

</html>
