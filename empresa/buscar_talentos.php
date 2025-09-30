<?php
// 1. INICIALIZAÇÃO E SEGURANÇA
session_start();
require '../includes/db_connect.php';

// Redireciona se não for um usuário do tipo empresa logado
if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] != 2) {
    header("Location: ../login.php");
    exit;
}

// Redireciona se o perfil da empresa estiver incompleto
$stmt_check = $pdo->prepare("SELECT status FROM empresa WHERE id_empresa = ?");
$stmt_check->execute([$_SESSION['user_id']]);
$empresa_status = $stmt_check->fetchColumn();
if ($empresa_status === 'Incompleto') {
    header("Location: index.php?error=perfil_incompleto");
    exit;
}


// 2. BUSCAR DADOS DOS ALUNOS PARA EXIBIÇÃO
$alunos = [];
try {
    // Query com JOIN para buscar todos os dados relevantes dos alunos com perfil regular
    $sql = "SELECT 
                u.nome, u.email, u.tel, u.link_perfil_externo,
                a.sobrenome, a.status as status_aluno,
                c.nome_curso,
                a.habilidades
            FROM usuario u
            JOIN aluno a ON u.id_usuario = a.id_aluno
            LEFT JOIN curso c ON a.id_curso = c.id_curso
            WHERE u.tipo = 1 AND a.status = 'Regular'
            ORDER BY u.nome, a.sobrenome";

    $stmt = $pdo->query($sql);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro ao buscar talentos: " . $e->getMessage());
    // Tratar o erro de forma amigável, se necessário
}


$pageTitle = "Buscar Talentos";
include '../includes/header.php';
?>

<main class="talentos-page">
    <div class="container">
        <h1>Banco de Talentos</h1>
        <p>Explore os perfis dos alunos de Sistemas de Informação do IFBA e encontre o candidato ideal.</p>

        <!-- ÁREA DE FILTROS (Funcionalidade futura) 
        <div class="filters-container">
            <h3>Filtrar por:</h3>
            <form action="buscar_talentos.php" method="GET">
                <div class="filter-group">
                    <input type="text" name="habilidade" placeholder="Habilidade (ex: PHP, React)">
                </div>
                <div class="filter-group">
                     <select name="status">
                        <option value="">Qualquer Status/Semestre</option>
                        <option value="Concluinte">Concluinte</option>
                        <option value="8º Semestre">8º Semestre</option>
                    </select>
                </div>
                <button type="submit" class="btn">Filtrar</button>
            </form>
        </div> -->

        <!-- LISTA DE CARDS DE ALUNOS -->
        <div class="talentos-list">
            <?php if (count($alunos) > 0): ?>
                <?php foreach ($alunos as $aluno): ?>
                    <details class="aluno-card">
                        <summary class="aluno-summary">
                            <div class="summary-content">
                                <h3><?php echo htmlspecialchars($aluno['nome'] . ' ' . $aluno['sobrenome']); ?></h3>
                                <span class="aluno-meta"><?php echo htmlspecialchars($aluno['nome_curso'] ?? 'Curso não informado'); ?></span>
                                <span class="aluno-meta"><?php echo htmlspecialchars($aluno['status_aluno'] ?? 'Status não informado'); ?></span>
                            </div>
                            <div class="summary-arrow">&#9660;</div>
                        </summary>
                        <div class="aluno-details">
                            <h4>Habilidades</h4>
                            <?php
                            $habilidades = json_decode($aluno['habilidades'], true);
                            if (!empty($habilidades) && is_array($habilidades) && !empty($habilidades[0])):
                            ?>
                                <div class="skills-container">
                                    <?php foreach ($habilidades as $habilidade): ?>
                                        <span class="skill-tag"><?php echo htmlspecialchars($habilidade); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p>Nenhuma habilidade informada.</p>
                            <?php endif; ?>

                            <h4>Contato</h4>
                            <p><strong>E-mail:</strong> <?php echo htmlspecialchars($aluno['email']); ?></p>
                            <?php if (!empty($aluno['tel'])): ?>
                                <p><strong>Telefone:</strong> <?php echo htmlspecialchars($aluno['tel']); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($aluno['link_perfil_externo'])): ?>
                                <div class="aluno-actions">
                                    <a href="<?php echo htmlspecialchars($aluno['link_perfil_externo']); ?>" target="_blank" class="btn-link">Ver Perfil Externo</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </details>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum perfil de aluno disponível no momento.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>