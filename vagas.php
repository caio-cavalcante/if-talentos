<?php
session_start();
require 'includes/db_connect.php';

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
                                <!-- Futuramente, o botão de salvar vaga virá aqui -->
                                <!-- <button class="btn-salvar">Salvar Vaga</button> -->
                                <a href="#" class="btn btn-candidatar">Candidatar-se</a>
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