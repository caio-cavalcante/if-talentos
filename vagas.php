<?php
session_start();
require 'includes/db_connect.php';

$aluno_status = null;
if (isset($_SESSION['user_id']) && $_SESSION['user_tipo'] == 1) {
    $stmt_aluno = $pdo->prepare("SELECT status FROM aluno WHERE id_aluno = ?");
    $stmt_aluno->execute([$_SESSION['user_id']]);
    $aluno = $stmt_aluno->fetch(PDO::FETCH_ASSOC);
    if ($aluno) {
        $aluno_status = $aluno['status'];
    }
}

try {
    // Seleciona todas as colunas da tabela vaga onde o status é 'Aberta'
    // Ordena pelas mais recentes primeiro
    $stmt = $pdo->prepare("SELECT * FROM vaga WHERE status = 'Aberta' ORDER BY data_publicacao DESC");
    $stmt->execute();
    $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Em caso de erro, você pode exibir uma mensagem amigável
    // e registrar o erro real para depuração.
    error_log("Erro ao buscar vagas: " . $e->getMessage());
    $vagas = []; // Garante que a variável exista, mesmo que vazia
    echo "<p>Ocorreu um erro ao carregar as vagas. Tente novamente mais tarde.</p>";
}

$pageTitle = "Vagas | IF - Talentos";
include 'includes/header.php';
?>

<main class="vagas-page">
    <div class="container">
        <h1>Oportunidades de Estágio</h1>
        <p>Explore as vagas abertas e encontre a oportunidade certa para você.</p>

        <!-- EXIBIÇÃO DOS CARDS -->
        <div class="vagas-list">
            <?php if (count($vagas) > 0): ?>
                <?php foreach ($vagas as $vaga): ?>
                    <details class="vaga-card">
                        <summary class="vaga-summary">
                            <div class="summary-content">
                                <h3><?php echo htmlspecialchars($vaga['titulo']); ?></h3>
                                <span class="vaga-meta"><?php echo htmlspecialchars($vaga['modalidade']); ?></span>
                                <span class="vaga-meta">Publicada em: <?php echo date('d/m/Y', strtotime($vaga['data_publicacao'])); ?></span>
                            </div>
                            <div class="summary-arrow">&#9660;</div>
                        </summary>
                        <div class="vaga-details">
                            <h4>Descrição da Vaga</h4>
                            <p><?php echo nl2br(htmlspecialchars($vaga['descricao'])); ?></p>

                            <h4>Pré-requisitos</h4>
                            <p><?php echo nl2br(htmlspecialchars($vaga['pre_requi'])); ?></p>

                            <h4>Faixa Salarial</h4>
                            <p>R$ <?php echo htmlspecialchars(number_format($vaga['faixa_salarial'], 2, ',', '.')); ?></p>

                            <div class="vaga-actions">
                                <?php
                                // Verifica se é um aluno logado
                                if (isset($_SESSION['user_id']) && $_SESSION['user_tipo'] == 1):
                                    // Verifica se o perfil do aluno está completo (status não está vazio)
                                    if (!empty($aluno_status)):
                                ?>
                                        <!-- Botão funcional -->
                                        <a href="<?php echo htmlspecialchars($vaga['link_candidatura']); ?>" target="_blank" class="btn btn-candidatar">Me candidatar</a>
                                    <?php
                                    else:
                                    ?>
                                        <a href="/aluno/perfil.php" class="btn btn-candidatar">Completar meu perfil</a>
                                    <?php
                                    endif;
                                else:
                                    ?>
                                    <!-- Para visitantes não logados ou outros tipos de usuário -->
                                    <a href="../login.php" class="btn btn-candidatar">Fazer login como aluno</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </details>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhuma vaga aberta no momento. Volte em breve!</p>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php include 'includes/footer.php'; ?>