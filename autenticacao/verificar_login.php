<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
/**
 * Verifica se o usuário está logado e tem permissão para acessar a página.
 * 
 * @param array $tiposPermitidos - Ex: ['admin', 'recepcao']
 */
function verificarAcesso($tiposPermitidos = []) {
    // Se não estiver logado
    if (!isset($_SESSION['usuario_tipo'])) {
        header("Location: ../autenticacao/login.php?erro=acesso_negado");
        exit();
    }

    // Se tiver restrição de tipo e o usuário não for autorizado
    if (!empty($tiposPermitidos) && !in_array($_SESSION['usuario_tipo'], $tiposPermitidos)) {
        header("Location: ../erro-permissao.php");
        exit();
    }
}
?>
