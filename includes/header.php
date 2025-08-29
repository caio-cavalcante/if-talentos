<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Banco de Talentos BSI - IFBA'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css"> </head>
<body>

<header class="main-header">
    <div class="container">
        <a href="https://portal.ifba.edu.br/feira-de-santana" class="logo-link">
            <img src="assets/images/logo-colorida.png" alt="Logo do IFBA" class="logo">
        </a>
        <nav class="main-nav">
            <ul>
                <?php if (isset($_SESSION['user_id'])) : ?>
                    <?php if ($_SESSION['user_tipo'] == 'aluno') : ?>
                        <li><a href="/vagas.php">Ver Vagas</a></li>
                        <li><a href="/aluno/perfil.php">Meu Perfil</a></li>
                    <?php elseif ($_SESSION['user_tipo'] == 'empresa') : ?>
                        <li><a href="/empresa/buscar_talentos.php">Buscar Talentos</a></li>
                        <li><a href="/empresa/vagas.php">Minhas Vagas</a></li>
                    <?php endif; ?>
                    <li><a href="/logout.php">Sair</a></li>
                <?php else : ?>
                    <li><a href="/vagas.php">Vagas</a></li>
                    <li><a href="/sobre.php">Sobre</a></li>
                    <li><a href="/contato.php">Contato</a></li>
                    <li><a href="/login.php" class="btn-login">Entrar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>