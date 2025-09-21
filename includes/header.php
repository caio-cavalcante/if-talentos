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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="shortcut icon" href="/assets/images/favicon_io/android-chrome-512x512.png" type="image/x-icon">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>

    <header class="main-header">
        <div class="container">
            <a href="/index.php" class="logo-link">
                <img src="/assets/images/logo-pequena-colorida.png" alt="Logo do IFBA" class="logo pequena" id="pequena-light">
                <img src="/assets/images/logo-pequena-branca.png" alt="Logo do IFBA" class="logo pequena" id="pequena-dark">
            </a>
            <nav class="main-nav">
                <ul>
                    <?php if (isset($_SESSION['user_id'])) : ?>
                        <?php if ($_SESSION['user_tipo'] == 1) : // TIPO ALUNO 
                        ?>
                            <li><a href="/vagas.php">Ver Vagas</a></li>
                            <li><a href="/aluno/perfil.php">Meu Perfil</a></li>

                        <?php elseif ($_SESSION['user_tipo'] == 2) : // TIPO EMPRESA 
                        ?>
                            <li><a href="/empresa/buscar_talentos.php">Buscar Talentos</a></li>
                            <li><a href="/empresa/vagas.php">Minhas Vagas</a></li>

                        <?php elseif ($_SESSION['user_tipo'] == 3) : // TIPO ADMIN 
                        ?>
                            <li><a href="/admin/index.php">Dashboard</a></li>
                            <li><a href="/admin/gerenciar_vagas.php">Gerenciar Vagas</a></li>
                            <li><a href="/admin/gerenciar_usuarios.php">Gerenciar Usuários</a></li>

                        <?php endif; ?>

                        <li><a href="/logout.php">Sair</a></li>

                    <?php else : ?>
                        <li><a href="/vagas.php">Vagas</a></li>
                        <li><a href="/sobre.php">Sobre</a></li>
                        <li><a href="/contato.php">Contato</a></li>
                        <li><a href="/login.php" class="btn-login">Entrar</a></li>
                    <?php endif; ?>
                    <li>
                        <div class="theme-switch-wrapper">
                            <label class="theme-switch" for="checkbox">
                                <input type="checkbox" id="checkbox" />
                                <div class="slider round"></div>
                            </label>
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
</body>