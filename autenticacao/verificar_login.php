<?php
session_start(); // Inicia a sessão para acessar os dados do usuário

/**
 * Função para verificar se o usuário está logado e tem permissão
 * @param array $tiposPermitidos - Tipos de usuário autorizados a acessar a página
 */
function verificarAcesso($tiposPermitidos = []) {
    // Se não estiver logado, redireciona para login
    if (!isset($_SESSION['usuario_tipo'])) {
        header("Location: ../autenticacao/login.php");
        exit();
    }

    // Se houver restrição de tipo e o tipo do usuário logado não estiver permitido
    if (!empty($tiposPermitidos) && !in_array($_SESSION['usuario_tipo'], $tiposPermitidos)) {
        header("Location: ../erro-permissao.php"); // Redireciona para página de acesso negado
        exit();
    }
}