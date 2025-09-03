<?php
session_start();

// Remove todas as variáveis de sessão
session_unset();


// Destrói a sessão
session_destroy();

// Redireciona para a página index
header("Location: ../views/index.php");
exit();
?>