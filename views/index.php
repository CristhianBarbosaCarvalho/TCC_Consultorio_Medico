<!DOCTYPE html>
<html lang="pt-br">
<head> 
    <meta charset="UTF-8">
    <title>Bem-vindo ao Sistema</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Fontes e Ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/home.css">
</head>
<body>
    <div class="container">
        <h1>Seja Bem-vindo</h1>
        <h1>Clínica: Carvalho de Oliveira</h1>
        <p>Escolha seu perfil para continuar:</p>
        <div class="buttons">
            <a href="../views/login.php?perfil=administracao" class="admin">
                <i class="fas fa-user-shield"></i> Administração
            </a>
            <a href="../views/login.php?perfil=recepcao" class="recepcao">
                <i class="fas fa-concierge-bell"></i> Recepção
            </a>
            <a href="../views/login.php?perfil=medico" class="medico">
                <i class="fas fa-user-md"></i> Médico
            </a>
        </div>
    </div>
</body>
</html>
