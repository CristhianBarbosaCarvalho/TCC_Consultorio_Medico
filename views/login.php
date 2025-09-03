<?php
require_once '../autenticacao/autenticar.php';

$erro = '';
$perfil = $_GET['perfil'] ?? ''; // Pega o perfil passado na URL

$perfilValido = in_array($perfil, ['administracao', 'medico', 'recepcao']);
if (!$perfilValido) {
    header("Location: ../erro-permissao.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura os dados do formulário
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Tenta autenticar o usuário com os dados fornecidos
    $resultado = autenticarUsuario($email, $senha, $perfil);

    // Se autenticado com sucesso
    if (isset($resultado['sucesso'])) {
        // Redireciona conforme o tipo de usuário
        switch ($resultado['tipo']) {
            case 'admin':
                header("Location: ../dashboard_users/administracao.php");
                break;
            case 'medico':
                header("Location: ../dashboard_users/medico.php");
                break;
            case 'recepcao':
                header("Location: ../dashboard_users/recepcao.php");
                break;
        }
        exit();
    } else {
        $erro = $resultado['erro'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <ul class="nav-list">
            <a href="index.php" class="nav-link">Voltar</a>
        </ul>
    </div>
</nav>

<div class="container" style="max-width: 500px; margin-top: 100px;">
    <div class="card">
        <h2 class="form-title">Login</h2>
        <?php if (!empty($erro)) echo '<div class="alert alert-danger">' . $erro . '</div>'; ?>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="text" name="email" class="form-control" placeholder="Seu email" required>
            </div>
            <div class="form-group">
                <label class="form-label">Senha</label>
                <input type="password" name="senha" class="form-control" placeholder="Sua senha" required>
            </div>
            <button type="submit" class="btn btn-block">Entrar</button>
        </form>
    </div>
</div>
</body>
</html>