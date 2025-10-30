<?php
require_once '../config_BD/conexaoServer.php';

// Caminho absoluto do script de instalação inicial
$setupPath = 'C:/xampp/setup_scripts/setup_admin.php';

// Função: Verifica se o banco já existe
function checkDatabaseExists($conn, $dbname) {
    $result = $conn->query("SHOW DATABASES LIKE '$dbname'");
    return $result && $result->num_rows > 0;
}

// Se o banco não existir, executa o setup inicial
if (!checkDatabaseExists($conn, 'consultorio')) {
    if (file_exists($setupPath)) {
        include $setupPath;
    } else {
        die("Erro: script de instalação não encontrado. Contate o administrador.");
    }
}

$conn->select_db("consultorio");

/**
 * Autentica um usuário com base no perfil, email e senha.
 * Retorna ['sucesso' => true, 'tipo' => 'admin'] ou ['erro' => 'mensagem'].
 */
function autenticarUsuario($email, $senha, $perfil) {
    global $conn;

    // Determina a tabela e o campo de ID conforme o tipo de perfil
    switch ($perfil) {
        case 'administracao':
            $tabela = 'administrador';
            $idField = 'id_admin';
            $tipo = 'admin';
            break;
        case 'medico':
            $tabela = 'medicos';
            $idField = 'id_medico';
            $tipo = 'medico';
            break;
        case 'recepcao':
            $tabela = 'recepcionista';
            $idField = 'id_recepcionista';
            $tipo = 'recepcao';
            break;
        default:
            return ['erro' => 'Perfil inválido.'];
    }

    // Busca o usuário
    $stmt = $conn->prepare("SELECT * FROM $tabela WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) { 
        $usuario = $resultado->fetch_assoc();

        // Verifica senha
        if (password_verify($senha, $usuario['senha'])) {
            session_start();
            $_SESSION['usuario_id'] = $usuario[$idField];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $tipo;

            return ['sucesso' => true, 'tipo' => $tipo];
        }
    }

    return ['erro' => 'Email ou senha inválidos.'];
}
?>
