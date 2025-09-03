<?php
require_once '../config_BD/conexaoServer.php';

// Verifica se o banco de dados 'consultorio' existe, e se não, chama o script de instalação
if (!checkDatabaseExists($conn, 'consultorio')) {
    include '../config_BD/setup_admin.php';  // Script que cria o banco e as tabelas
}

// Conecta ao banco de dados 'consultorio'
$conn->select_db("consultorio");

/**
 * Verifica se o banco de dados existe
 */
function checkDatabaseExists($conn, $dbname) {
    $result = $conn->query("SHOW DATABASES LIKE '$dbname'");
    return $result && $result->num_rows > 0;
}

/**
 * Autentica um usuário de acordo com perfil, email e senha
 * @param string $email
 * @param string $senha
 * @param string $perfil
 * @return array ['sucesso' => true, 'tipo' => 'admin'] ou ['erro' => 'mensagem']
 */

function autenticarUsuario($email, $senha, $perfil) {
    global $conn; // Usa a conexão global com o banco

    // Define tabela e tipo conforme perfil escolhido
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

    // Prepara e executa a consulta para buscar o usuário
    $stmt = $conn->prepare("SELECT * FROM $tabela WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // Se encontrar o usuário
    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Verifica se a senha está correta
        if (password_verify($senha, $usuario['senha'])) {
            session_start();

            // Armazena dados na sessão
            $_SESSION['usuario_id'] = $usuario[$idField];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $tipo;

            return ['sucesso' => true, 'tipo' => $tipo];
        }
    }

    return ['erro' => 'Email ou senha inválidos.'];
}
