<?php
// 1. INICIALIZAÇÃO E SEGURANÇA
session_start();
require '../includes/db_connect.php';

// Redireciona se não for um usuário do tipo empresa logado
if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] != 2) {
    header("Location: ../login.php");
    exit;
}

$id_empresa_logada = $_SESSION['user_id'];
$perfil_completo = false;

// 2. LÓGICA DE VERIFICAÇÃO DE PERFIL COMPLETO
try {
    // Busca o status do perfil da empresa
    $stmt = $pdo->prepare("SELECT status FROM empresa WHERE id_empresa = ?");
    $stmt->execute([$id_empresa_logada]);
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

    // Consideramos o perfil completo se o status for 'Verificada' ou outro status positivo.
    // O status 'Incompleto' é o padrão após o cadastro inicial.
    if ($empresa && $empresa['status'] !== 'Incompleto') {
        $perfil_completo = true;
    }
} catch (PDOException $e) {
    // Em caso de erro, tratamos como perfil incompleto por segurança.
    // Idealmente, registrar o erro para depuração.
    error_log("Erro ao verificar status da empresa: " . $e->getMessage());
}


$pageTitle = "Dashboard | Empresa";
include '../includes/header.php';
?>

<main class="dashboard-empresa">
    <div class="container">
        <h1>Painel da Empresa</h1>
        <p>Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['user_nome']); ?>!</p>

        <?php if (!$perfil_completo): ?>
            <!-- AVISO DE PERFIL INCOMPLETO -->
            <div class="feedback-banner warning">
                <div>
                    <strong>Seu perfil está incompleto!</strong>
                    <p>Para publicar vagas e buscar talentos, você precisa primeiro completar o cadastro da sua empresa.</p>
                </div>
                <a href="perfil.php" class="btn">Completar Perfil Agora</a>
            </div>
        <?php endif; ?>
        
        <p>Gerencie suas vagas, encontre os melhores talentos do IFBA e edite as informações da sua empresa.</p>

        <!-- CARDS DE AÇÃO -->
        <div class="dashboard-actions">
            <a href="<?php echo $perfil_completo ? 'vagas.php' : '#'; ?>" class="action-card <?php echo !$perfil_completo ? 'disabled' : ''; ?>">
                <h3>Gerenciar Minhas Vagas</h3>
                <p>Crie, edite e acompanhe o status das suas oportunidades de estágio.</p>
            </a>
            <a href="perfil.php" class="action-card">
                <h3>Editar Perfil da Empresa</h3>
                <p>Mantenha as informações da sua empresa sempre atualizadas.</p>
            </a>
            <a href="<?php echo $perfil_completo ? 'buscar_talentos.php' : '#'; ?>" class="action-card <?php echo !$perfil_completo ? 'disabled' : ''; ?>">
                <h3>Buscar Talentos</h3>
                <p>Explore o perfil dos alunos e encontre o candidato ideal para sua equipe.</p>
            </a>
        </div>
    </div>
</main>

<script>
// Adiciona um alerta para os cards desabilitados para melhorar a UX
document.querySelectorAll('.action-card.disabled').forEach(card => {
    card.addEventListener('click', function(event) {
        event.preventDefault();
        alert('Você precisa completar o perfil da sua empresa antes de acessar esta funcionalidade.');
    });
});
</script>

<?php include '../includes/footer.php'; ?>