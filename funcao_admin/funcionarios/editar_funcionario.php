<?php
require_once '../../autenticacao/verificar_login.php';
require_once '../../config_BD/conexaoBD.php';
require_once '../../functions/busca.php';
require_once '../../functions/update.php';
require_once '../../functions/validacoes.php';

$mensagem = '';
$mensagem_tipo = '';
$funcionario = null;
$especialidades_medico = [];
$todas_especialidades = [];

// ===============================
// Verifica parâmetros da URL
// ===============================
if (isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['cargo'])) {
    $id = intval($_GET['id']);
    $cargo = strtolower(trim($_GET['cargo']));

    // Remove acentos
    $cargo = str_replace(
        ['á','à','â','ã','é','è','ê','í','ì','î','ó','ò','ô','õ','ú','ù','û','ç'],
        ['a','a','a','a','e','e','e','i','i','i','o','o','o','o','u','u','u','c'],
        $cargo
    );

    $tabelas = [
        'administrador' => ['tabela' => 'administrador', 'campo_id' => 'id_admin'],
        'medico'        => ['tabela' => 'medicos', 'campo_id' => 'id_medico'],
        'recepcionista' => ['tabela' => 'recepcionista', 'campo_id' => 'id_recepcionista']
    ];

    if (array_key_exists($cargo, $tabelas)) {
        $tabela = $tabelas[$cargo]['tabela'];
        $campo_id = $tabelas[$cargo]['campo_id'];

        // ===============================
        // Busca os dados do funcionário
        // ===============================
        $filtros = [$campo_id => $id];
        $config_campos = [$campo_id => ['operador' => '=', 'tipo' => 'i']];
        $resultado = buscarDados($conn, $tabela, $filtros, $config_campos);

        if ($resultado->num_rows === 1) {
            $funcionario = $resultado->fetch_assoc();

            // Se for médico, buscar suas especialidades
            if ($cargo === 'medico') {
                $sqlEsp = "
                    SELECT e.id_especialidade, e.nome 
                    FROM especialidade e
                    INNER JOIN medico_especialidade me 
                    ON me.id_especialidade = e.id_especialidade
                    WHERE me.id_medico = ?";
                $stmt = $conn->prepare($sqlEsp);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $especialidades_medico = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                // Buscar todas as especialidades disponíveis
                $todas = $conn->query("SELECT id_especialidade, nome FROM especialidade ORDER BY nome");
                $todas_especialidades = $todas->fetch_all(MYSQLI_ASSOC);
            }
        } else {
            $mensagem = "Funcionário não encontrado.";
            $mensagem_tipo = "alert-danger";
        }

        // ===============================
        // Atualização via POST
        // ===============================
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $nome = $_POST['nome'];
            $cpf = limparNumero($_POST['cpf']);
            $email = $_POST['email'];
            $telefone = limparNumero($_POST['telefone']);
            $senha = $_POST['senha'] ?? '';

            $dados = [
                'nome' => $nome,
                'cpf' => $cpf,
                'email' => $email,
                'telefone' => $telefone
            ];

            if ($cargo === 'medico') {
                $crm = $_POST['crm'];
                $dados['crm'] = $crm;
            }

            if (!empty($senha)) {
                $dados['senha'] = password_hash($senha, PASSWORD_BCRYPT);
            }

            if (!validarCPF($cpf)) {
                $mensagem = "CPF inválido.";
                $mensagem_tipo = "alert-danger";
            } elseif (!validarTelefone($telefone)) {
                $mensagem = "Telefone inválido.";
                $mensagem_tipo = "alert-danger";
            } else {
                $resultado = atualizarDados($tabela, $dados, $campo_id, $id);

                if ($resultado === true) {
                    // Atualizar especialidades do médico (apenas inserindo/removendo diferenças)
                    if ($cargo === 'medico') {
                        $especialidadesSelecionadas = $_POST['especialidades'] ?? [];

                        // Buscar especialidades atuais
                        $resultadoAtual = $conn->query("SELECT id_especialidade FROM medico_especialidade WHERE id_medico = $id");
                        $atual = array_column($resultadoAtual->fetch_all(MYSQLI_ASSOC), 'id_especialidade');

                        // Diferenças
                        $aInserir = array_diff($especialidadesSelecionadas, $atual);
                        $aRemover = array_diff($atual, $especialidadesSelecionadas);

                        // Inserir novas
                        if (!empty($aInserir)) {
                            $stmt = $conn->prepare("INSERT INTO medico_especialidade (id_medico, id_especialidade) VALUES (?, ?)");
                            foreach ($aInserir as $idEsp) {
                                $stmt->bind_param("ii", $id, $idEsp);
                                $stmt->execute();
                            }
                            $stmt->close();
                        }

                        // Remover desmarcadas
                        if (!empty($aRemover)) {
                            $ids = implode(",", $aRemover);
                            $conn->query("DELETE FROM medico_especialidade WHERE id_medico = $id AND id_especialidade IN ($ids)");
                        }
                    }

                    $mensagem = "Funcionário atualizado com sucesso!";
                    $mensagem_tipo = "alert-success";
                } else {
                    $mensagem = "Erro ao atualizar: $resultado";
                    $mensagem_tipo = "alert-danger";
                }
            }
        }

    } else {
        $mensagem = "Cargo inválido.";
        $mensagem_tipo = "alert-danger";
    }
} else {
    $mensagem = "Dados inválidos para edição.";
    $mensagem_tipo = "alert-danger";
}

// ===============================
// Funções de formatação
// ===============================
function formatarCPF($cpf) {
    $cpf = preg_replace('/\D/', '', $cpf);
    if (strlen($cpf) === 11) {
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }
    return $cpf;
}

function formatarTelefone($telefone) {
    $telefone = preg_replace('/\D/', '', $telefone);
    if (strlen($telefone) === 10) {
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6);
    } elseif (strlen($telefone) === 11) {
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7);
    }
    return $telefone;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Funcionário</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="../../assets/js/formatacao.js" defer></script>
</head>
<body>

<header class="header">
    <div class="container header-content">
        <h1>Editar Funcionário</h1>
        <a href="../../views/logout.php" class="logout-btn">Sair</a>
    </div>
</header>

<nav class="navbar">
    <div class="container">
        <ul class="nav-list">
            <li><a href="gerenciar_funcionarios.php" class="nav-link">Voltar</a></li>
        </ul>
    </div>
</nav>

<main class="container form-container">
    <?php if ($mensagem): ?>
        <p class="alert <?= $mensagem_tipo ?>"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <?php if ($funcionario): ?>
        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nome:</label>
                    <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($funcionario['nome']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">CPF:</label>
                    <input type="text" name="cpf" class="form-control cpf-mask" value="<?= formatarCPF($funcionario['cpf']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($funcionario['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Telefone:</label>
                    <input type="text" name="telefone" class="form-control telefone-mask" value="<?= formatarTelefone($funcionario['telefone']) ?>" required>
                </div>

                <?php if ($cargo === 'medico'): ?>
                    <div class="form-group">
                        <label class="form-label">CRM:</label>
                        <input type="text" name="crm" class="form-control" value="<?= htmlspecialchars($funcionario['crm']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Especialidades:</label><br>
                        <?php foreach ($todas_especialidades as $esp): ?>
                            <label style="display:block; margin-bottom:5px;">
                                <input type="checkbox" name="especialidades[]" value="<?= $esp['id_especialidade'] ?>"
                                    <?= in_array($esp['id_especialidade'], array_column($especialidades_medico, 'id_especialidade')) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($esp['nome']) ?>
                            </label>
                        <?php endforeach; ?>
                        <small>Selecione uma ou mais especialidades.</small>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Nova Senha (opcional):</label>
                    <input type="password" name="senha" class="form-control" placeholder="Deixe em branco para não alterar">
                </div>

                <button type="submit" class="btn btn-success btn-block">Salvar Alterações</button>
            </form>
        </div>
    <?php endif; ?>
</main>

</body>
</html>
