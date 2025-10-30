<?php
require_once '../../autenticacao/verificar_login.php';
require_once '../../config_BD/conexaoBD.php';
require_once '../../functions/insert.php';
require_once '../../functions/validacoes.php'; // ← novo require

$mensagem = '';
$mensagem_tipo = '';

// Buscar especialidades do BD
$especialidades = [];
$result = $conn->query("SELECT id_especialidade, nome FROM especialidade ORDER BY nome ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $especialidades[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $crm = $_POST['crm']; 
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $especialidadesSelecionadas = $_POST['especialidades'] ?? [];

    if (empty($especialidadesSelecionadas)) {
        $mensagem = "Selecione ao menos uma especialidade.";
        $mensagem_tipo = "alert-warning";
    } else {
        // 🔹 Limpa o CPF (mantém apenas números)
        $cpfLimpo = preg_replace('/\D/', '', $cpf);

        // 🔹 Verifica duplicidade de CPF ou e-mail na tabela 'medicos'
        $erroDuplicidade = verificarDuplicidadeFuncionario($conn, $cpf, $email);

        if ($erroDuplicidade) {
            $mensagem = $erroDuplicidade;
            $mensagem_tipo = "alert-danger";
        } else {
            // 🔹 Criptografa senha e insere o médico
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $dados_medico = [
                'crm' => $crm,
                'nome' => $nome,
                'cpf' => $cpfLimpo,
                'telefone' => $telefone,
                'email' => $email,
                'senha' => $senha_hash
            ];

            $resultado = inserirDados('medicos', $dados_medico);

            if ($resultado === true) {
                $id_medico = $conn->insert_id;

                $stmt = $conn->prepare("INSERT INTO medico_especialidade (id_medico, id_especialidade) VALUES (?, ?)");
                $sucesso_total = true;

                foreach ($especialidadesSelecionadas as $id_especialidade) {
                    $stmt->bind_param("ii", $id_medico, $id_especialidade);
                    if (!$stmt->execute()) {
                        $sucesso_total = false;
                        $erro = $stmt->error;
                        break;
                    }
                }

                $stmt->close();

                if ($sucesso_total) {
                    $mensagem = "✅ Médico cadastrado com sucesso e especialidades vinculadas!";
                    $mensagem_tipo = "alert-success";
                } else {
                    $mensagem = "⚠️ Médico cadastrado, mas erro ao vincular especialidades: " . $erro;
                    $mensagem_tipo = "alert-warning";
                }
            } else {
                $mensagem = "❌ Erro ao cadastrar médico: " . $resultado;
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
    <title>Cadastrar Médico</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="../../assets/js/formatacao.js" defer></script>
</head>
<body>

<header class="header">
    <div class="container header-content">
        <h1>Cadastrar Médico</h1>
        <a href="../../views/logout.php" class="logout-btn">Sair</a>
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
                <label class="form-label">CRM:</label>
                <input type="text" name="crm" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Nome:</label>
                <input type="text" name="nome" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">CPF:</label>
                <input type="text" name="cpf" class="form-control cpf-mask" placeholder="123.456.789-00" required>
            </div>

            <div class="form-group">
                <label class="form-label">Telefone:</label>
                <input type="text" name="telefone" class="form-control telefone-mask" placeholder="(11) 11111-1111" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email:</label>
                <input type="email" name="email" class="form-control" placeholder="exemplo@email.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">Senha:</label>
                <input type="password" name="senha" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Especialidades:</label><br>
                <?php foreach ($especialidades as $esp): ?>
                    <label style="display:block; margin-bottom:5px;">
                        <input type="checkbox" name="especialidades[]" value="<?= $esp['id_especialidade'] ?>">
                        <?= htmlspecialchars($esp['nome']) ?>
                    </label>
                <?php endforeach; ?>
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
