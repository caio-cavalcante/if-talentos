<?php
// Inicia a sessão no topo de tudo. É obrigatório para usar $_SESSION.
session_start();
require 'includes/db_connect.php';

$error_message = '';
$success_message = '';

if (isset($_GET['status']) && $_GET['status'] === 'cadastrado') {
    $success_message = "Cadastro realizado com sucesso! Agora você pode fazer login.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (empty($email) || empty($senha)) {
        $error_message = "Por favor, preencha todos os campos.";
    } else {
        // 1. Buscar o usuário no banco de dados pelo e-mail
        $stmt = $pdo->prepare("SELECT id_usuario, nome, email, senha, tipo FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Verificar se o usuário existe E se a senha está correta
        if ($user && password_verify($senha, $user['senha'])) {
            echo "<script>console.log('log');</script>";
            // 3. Autenticação bem-sucedida: Iniciar a sessão
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_nome'] = $user['nome'];
            $_SESSION['user_tipo'] = $user['tipo']; // 1: aluno, 2: empresa, 3: admin

            // 4. Redirecionar o usuário para sua respectiva dashboard
            switch ($user['tipo']) {
                case 3: // Admin
                    header("Location: /admin/index.php");
                    break;
                case 2: // Empresa
                    header("Location: /empresa/index.php");
                    break;
                case 1: // Aluno
                    header("Location: /aluno/index.php");
                    break;
                default:
                    header("Location: /index.php");
            }
            exit;

        } else {
            echo "<script>console.log('not log');</script>";
            $error_message = "E-mail ou senha inválidos.";
        }
    }
}

$pageTitle = "Login | IF - Talentos";
include 'includes/header.php';
?>

<main class="login-page">
    <div class="container">

        <h1 class="title">Banco de Talentos - BSI</h1>
        
        <div class="login-form-container">
            <h2>Acessar Plataforma</h2>
            
            <?php if (!empty($success_message)): ?>
                <div class="success-banner"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="error-banner"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <span class="input-icon"><i class="fas fa-user"></i></span>
                    <input type="email" id="email" name="email" placeholder="seu@email.com">
                </div>
                <div class="form-group">
                    <span class="input-icon"><i class="fas fa-key"></i></span>
                    <input type="password" id="senha" name="senha" placeholder="senha" required>
                </div>
                <button type="submit" class="btn-login">Entrar</button>
            </form>
            <div class="register">
                <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>