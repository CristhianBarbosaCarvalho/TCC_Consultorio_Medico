<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['admin']);
require_once '../../functions/busca.php';
require_once '../../functions/delete.php'; // <-- nova função genérica para deletar

$mensagem = '';
$mensagem_tipo = '';
$funcionario = null;

// Verifica se veio o ID e o tipo (ex: medico, recepcionista, administrador)
if (isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['cargo'])) {
    $id = intval($_GET['id']);
    $cargo = strtolower($_GET['cargo']);

    // Define a tabela de acordo com o cargo
    $tabelas = [
        'administrador' => ['tabela' => 'administrador', 'campo_id' => 'id_admin'],
        'médico'        => ['tabela' => 'medicos', 'campo_id' => 'id_medico'],
        'recepcionista' => ['tabela' => 'recepcionista', 'campo_id' => 'id_recepcionista']
    ];

    if (array_key_exists($cargo, $tabelas)) {
        $tabela = $tabelas[$cargo]['tabela'];
        $campo_id = $tabelas[$cargo]['campo_id'];

        // Busca dados para exibir confirmação
        $filtros = [$campo_id => $id];
        $config_campos = [
            $campo_id => ['operador' => '=', 'tipo' => 'i']
        ];
        $resultado = buscarDados($conn, $tabela, $filtros, $config_campos);

        if ($resultado->num_rows === 1) {
            $funcionario = $resultado->fetch_assoc();
        } else {
            $mensagem = "Funcionário não encontrado.";
            $mensagem_tipo = "alert-danger";
        }

        // Se o formulário de confirmação foi enviado
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirmar'])) {
            $resultado = excluirRegistro($conn, $tabela, $campo_id, $id);

            if ($resultado === true) {
                $mensagem = "Funcionário excluído com sucesso!";
                $mensagem_tipo = "alert-success";
                $funcionario = null;
            } else {
                $mensagem = "Erro ao excluir: $resultado";
                $mensagem_tipo = "alert-danger";
            }
        }
    } else {
        $mensagem = "Cargo inválido.";
        $mensagem_tipo = "alert-danger";
    }
} else {
    $mensagem = "Dados inválidos para exclusão.";
    $mensagem_tipo = "alert-danger";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Excluir Funcionário</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<header class="header">
    <div class="container header-content">
        <h1>Excluir Funcionário</h1>
        <a href="../../views/logout.php" class="logout-btn">Sair</a>
    </div>
</header>

<nav class="navbar">
    <div class="container">
        <ul class="nav-list">
            <a href="gerenciar_funcionarios.php" class="nav-link">Voltar</a>
        </ul>
    </div>
</nav>

<div class="container" style="margin-top: 100px; max-width: 600px;">
    <?php if ($mensagem): ?>
        <p class="alert <?= $mensagem_tipo ?>"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <?php if ($funcionario): ?>
        <div class="card">
            <h3>Tem certeza que deseja excluir este funcionário?</h3>
            <p><strong>Nome:</strong> <?= htmlspecialchars($funcionario['nome']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($funcionario['email']) ?></p>
            <p><strong>Cargo:</strong> <?= ucfirst($cargo) ?></p>

            <form method="POST" style="text-align: center; margin-top: 20px;">
                <button type="submit" name="confirmar" class="btn btn-danger">Excluir</button>
                <a href="gerenciar_funcionarios.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
