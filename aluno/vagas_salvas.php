<?php
session_start();
require '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] != 1) {
    header("Location: ../login.php");
    exit;
}

$id_aluno_logado = $_SESSION['user_id'];
$vagas_salvas = [];

try {
    // Query com JOIN para buscar os detalhes das vagas que o aluno salvou
    $sql = "SELECT v.* FROM vaga v
            JOIN aluno_vagas_salvas avs ON v.id_vaga = avs.id_vaga
            WHERE avs.id_aluno = ?
            ORDER BY avs.data_salvo DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_aluno_logado]);
    $vagas_salvas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro ao buscar vagas salvas: " . $e->getMessage());
}

$pageTitle = "Vagas Salvas | Aluno";
include '../includes/header.php';
?>

<main class="vagas-page">
    <div class="container">
        <h1>Minhas Vagas Salvas</h1>
        <p>Aqui estão as oportunidades que você marcou como interessantes.</p>

        <div class="vagas-list">
            <?php if (count($vagas_salvas) > 0): ?>
                <?php foreach ($vagas_salvas as $vaga): 
                    $is_saved = true;
                ?>
                    <details class="vaga-card">
                        <summary class="vaga-summary">
                            <div class="summary-content">
                                <h3><?php echo htmlspecialchars($vaga['titulo']); ?></h3>
                                <span class="vaga-meta"><?php echo htmlspecialchars($vaga['modalidade']); ?></span>
                                <span class="vaga-meta">Status: <?php echo htmlspecialchars($vaga['status']); ?></span>
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
                                <a href="<?php echo htmlspecialchars($vaga['link_candidatura']); ?>" target="_blank" class="btn btn-candidatar">Me candidatar</a>

                                <?php 
                                $bookmark_class = $is_saved ? 'saved' : '';
                                $bookmark_title = $is_saved ? 'Remover dos salvos' : 'Salvar vaga';
                                $icon_style = $is_saved ? 'fa-solid' : 'fa-regular';
                                ?>
                                <button class="btn-bookmark <?php echo $bookmark_class; ?>" 
                                        data-vaga-id="<?php echo $vaga['id_vaga']; ?>" 
                                        title="<?php echo $bookmark_title; ?>">
                                    <i class="<?php echo $icon_style; ?> fa-bookmark"></i>
                                </button>
                            </div>
                        </div>
                    </details>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Você ainda não salvou nenhuma vaga. Explore as <a href="../vagas.php" class="btn-vagas">oportunidades disponíveis</a>!</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="/assets/js/bookmark.js"></script>
<?php include '../includes/footer.php'; ?>