<?php
require_once '../config_BD/conexaoBD.php';
require_once '../autenticacao/verificar_login.php';
verificarAcesso(['medico']); 
require_once '../functions/update.php';
require_once '../functions/busca.php';

$mensagem = '';
$mensagem_tipo = '';
$medico = null;

$id = $_SESSION['usuario_id'];


$filtros = ['id_medico' => $id];
$config_campos = ['id_medico' => ['operador' => '=', 'tipo' => 'i']];

$resultado = buscarDados($conn, 'medicos', $filtros, $config_campos);

if ($resultado->num_rows === 1) {
    $medico = $resultado->fetch_assoc();
} else {
    $mensagem = "Médico não encontrado.";
    $mensagem_tipo = "alert-danger";
}


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

    $resultado = atualizarDados('medicos', $dados, 'id_medico', $id);

    if ($resultado === true) {
        $mensagem = "Dados atualizados com sucesso!";
        $mensagem_tipo = "alert-success";

        $medico['email'] = $email;
        $medico['telefone'] = $telefone;
    } else {
        $mensagem = "Erro ao atualizar dados: $resultado";
        $mensagem_tipo = "alert-danger";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Editar Perfil Médico</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <header class="header">
        <div class="container header-content">
            <h1>Editar Perfil do Médico</h1>
            <a href="../autenticacao/logout.php" class="logout-btn">Sair</a>
        </div>
    </header>

    <nav class="navbar">
        <div class="container">
            <ul class="nav-list">
                <a href="../dashboard_users/medico.php" class="nav-link">Voltar</a>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px; max-width: 600px;">
        <?php if ($mensagem): ?>
            <p class="alert <?= $mensagem_tipo ?>"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>

        <?php if ($medico): ?>
            <div class="card">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Email:</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($medico['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Telefone:</label>
                        <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($medico['telefone']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nova Senha (deixe em branco para manter a atual):</label>
                        <input type="password" name="senha" class="form-control">
                    </div>

                    <div style="text-align: center; margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>