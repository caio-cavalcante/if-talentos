<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "IF - Talentos";

include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

    <main class="landing-container">
        <h1 class="title">
            <span style="color: var(--ift-green);">IF</span> - Talentos
        </h1>
        <p class="tagline">Conectando talentos e empresas. Aqui nós vamos 
            <strong><span style="color: var(--ift-green);">I</span>dentificar</strong> e 
            <strong><span style="color: var(--ift-green);">F</span>ortalecer</strong> 
            os talentos com as oportunidades certas.
        </p>
        <div class="code-animation-container">
            <pre><code id="code-snippet"></code></pre>
        </div>

        <div class="cta-buttons">
            <a href="login.php?perfil=aluno" class="btn aluno">Sou Aluno</a>
            <a href="login.php?perfil=empresa" class="btn empresa">Sou Empresa</a>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>