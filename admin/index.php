<?php
// 1. INICIALIZAÇÃO E SEGURANÇA
session_start();
require '../includes/db_connect.php';

// Redireciona se não estiver logado ou não for admin (tipo = 3)
if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] != 3) {
    header("Location: ../login.php");
    exit;
}

// 2. LÓGICA DE BACK-END: BUSCAR DADOS PARA O DASHBOARD
try {
    // --- KPIs: Contagens Totais ---
    $total_alunos = $pdo->query("SELECT COUNT(*) FROM aluno")->fetchColumn();
    $total_empresas = $pdo->query("SELECT COUNT(*) FROM empresa")->fetchColumn();
    $total_vagas_abertas = $pdo->query("SELECT COUNT(*) FROM vaga WHERE status = 'Aprovada' OR status = 'Aberta'")->fetchColumn();

    // --- Análise de Habilidades dos Alunos (Usando funções JSON do PostgreSQL) ---
    $sql_habilidades = "
        SELECT 
            habilidade, 
            COUNT(*) as total
        FROM 
            aluno, jsonb_array_elements_text(habilidades) as habilidade
        GROUP BY 
            habilidade
        ORDER BY 
            total DESC
        LIMIT 5;
    ";
    $stmt_habilidades = $pdo->query($sql_habilidades);
    $habilidades_populares = $stmt_habilidades->fetchAll(PDO::FETCH_ASSOC);

    // Preparar dados para o gráfico de habilidades
    $habilidades_labels = array_column($habilidades_populares, 'habilidade');
    $habilidades_data = array_column($habilidades_populares, 'total');

    // --- Lista de Vagas Recentes (Usando a View) ---
    $vagas_recentes = $pdo->query("SELECT * FROM vw_gerenciamento_vagas LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    // Preparar dados para a quantidade de bookmarks por vaga
    foreach ($vagas_recentes as &$vaga) {
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM aluno_vagas_salvas WHERE id_vaga = ?");
        $stmt_count->execute([$vaga['id_vaga']]);
        $vaga['bookmarks_count'] = $stmt_count->fetchColumn();
    }

} catch (PDOException $e) {
    error_log("Erro no dashboard: " . $e->getMessage());
    die("Ocorreu um erro ao carregar os dados do dashboard.");
}

// Verifica se há vagas pendentes de aprovação dos admins
$stmt_pendentes = $pdo->query("SELECT COUNT(*) FROM vaga WHERE status = 'Pendente'");
$vagas_pendentes = $stmt_pendentes->fetchColumn();

$pageTitle = "Dashboard | Admin";
include '../includes/header.php';
?>

<main class="admin-dashboard">
    <div class="container">
        <h1>Dashboard Administrativo</h1>
        <p>Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_nome']); ?>!</p>

        <?php if ($vagas_pendentes > 0): ?>
            <div class="feedback-banner warning">
                <strong>Há <?php echo $vagas_pendentes; ?> vagas pendentes de aprovação.</strong>
                <a href="aprovar_vagas.php" class="btn">Ver Vagas Pendentes</a>
            </div>
        <?php endif; ?><br>

        <!-- Seção de KPIs -->
        <section class="dashboard-kpis">
            <div class="kpi-card">
                <h2><?php echo $total_alunos; ?></h2>
                <p>Alunos Cadastrados</p>
            </div>
            <div class="kpi-card">
                <h2><?php echo $total_empresas; ?></h2>
                <p>Empresas Parceiras</p>
            </div>
            <div class="kpi-card">
                <h2><?php echo $total_vagas_abertas; ?></h2>
                <p>Vagas Abertas</p>
            </div>
        </section>

        <!-- Seção de Vagas Recentes -->
        <section class="dashboard-list">
            <h3>Vagas Publicadas Recentemente</h3>
            <table class="crud-table">
                <thead>
                    <tr>
                        <th>Título da Vaga</th>
                        <th>Usuário Responsável</th>
                        <th>Vezes Salva</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vagas_recentes as $vaga): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vaga['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($vaga['nome_admin']); ?></td>
                        <td><?php echo $vaga['bookmarks_count']; ?></td>
                        <td><span class="status-<?php echo strtolower($vaga['status']); ?>"><?php echo htmlspecialchars($vaga['status']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
</main>

<?php include '../includes/footer.php'; ?>