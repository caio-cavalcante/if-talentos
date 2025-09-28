<?php
session_start();
require '../includes/db_connect.php';

// Segurança: Garante que apenas alunos logados acessem
if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] != 1) {
    header("Location: ../login.php");
    exit;
}

// Busca o status do aluno para a lógica de "completar perfil"
$stmt = $pdo->prepare("SELECT status FROM aluno WHERE id_aluno = ?");
$stmt->execute([$_SESSION['user_id']]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);
$perfil_completo = ($aluno && !empty($aluno['status']));

$pageTitle = "Dashboard | Aluno";
include '../includes/header.php';
?>

<main class="dashboard-aluno">
    <div class="container">
        <h1>Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['user_nome']); ?>!</h1>

        <?php if (!$perfil_completo): ?>
            <div class="feedback-banner warning">
                <strong>Seu perfil está incompleto!</strong> Para se candidatar às vagas, você precisa primeiro completar seu cadastro.
                <a href="perfil.php" class="btn">Completar Perfil Agora</a>
            </div>
        <?php endif; ?>

        <p>Este é o seu painel. A partir daqui, você pode gerenciar seu perfil e encontrar as melhores oportunidades de estágio para sua carreira.</p>

        <div class="dashboard-actions">
            <a href="../vagas.php" class="action-card">
                <h3>Ver Vagas</h3>
                <p>Explore as últimas oportunidades publicadas.</p>
            </a>
            <a href="perfil.php" class="action-card">
                <h3>Meu Perfil</h3>
                <p>Mantenha suas informações sempre atualizadas.</p>
            </a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>