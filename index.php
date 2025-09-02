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
    <title>Banco de Talentos BSI - IFBA</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="landing-container">
        <h1 class="title">Banco de Talentos - BSI</h1>
        <p class="tagline">Conectando talentos e empresas.</p>

        <div class="code-animation-container">
            <pre><code id="code-snippet"></code></pre>
        </div>

        <div class="cta-buttons">
            <a href="login.php?perfil=aluno" class="btn btn-aluno">Sou Aluno</a>
            <a href="login.php?perfil=empresa" class="btn btn-empresa">Sou Empresa</a>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
    <script src="assets/js/animation.js"></script>
    <script src="assets/js/data-theme.js"></script>
</body>
</html>