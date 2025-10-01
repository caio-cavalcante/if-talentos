<?php
session_start();
require 'includes/db_connect.php';

$aluno_status = null;
$vagas_salvas_ids = [];

if (isset($_SESSION['user_id']) && $_SESSION['user_tipo'] == 1) {
    $id_aluno_logado = $_SESSION['user_id'];

    $stmt_aluno = $pdo->prepare("SELECT status FROM aluno WHERE id_aluno = ?");
    $stmt_aluno->execute([$_SESSION['user_id']]);
    $aluno = $stmt_aluno->fetch(PDO::FETCH_ASSOC);
    if ($aluno) {
        $aluno_status = $aluno['status'];
    }

    // Busca os IDs de todas as vagas que o aluno já salvou
    $stmt_salvas = $pdo->prepare("SELECT id_vaga FROM aluno_vagas_salvas WHERE id_aluno = ?");
    $stmt_salvas->execute([$id_aluno_logado]);
    $vagas_salvas_ids = $stmt_salvas->fetchAll(PDO::FETCH_COLUMN, 0);
}

try {
    // Seleciona todas as colunas da tabela vaga onde o status é 'Aprovada'
    // Ordena pelas mais recentes primeiro
    $stmt_vagas = $pdo->prepare("SELECT * FROM vaga WHERE status = 'Aprovada' or status = 'Aberta' ORDER BY data_publicacao DESC");
    $stmt_vagas->execute();
    $vagas = $stmt_vagas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
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
                <?php foreach ($vagas as $vaga):
                    // Verifica se a vaga atual está na lista de vagas salvas pelo aluno
                    $is_saved = in_array($vaga['id_vaga'], $vagas_salvas_ids);
                ?>
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
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_tipo'] == 1): ?>
                                    <!-- Verifica se o perfil do aluno está completo (status não está vazio) -->
                                    <?php if (!empty($aluno_status)): ?>
                                        <!-- Botão funcional -->
                                        <a href="<?php echo htmlspecialchars($vaga['link_candidatura']); ?>" target="_blank" class="btn btn-candidatar">Me candidatar</a>
                                    <?php else: ?>
                                        <a href="/aluno/perfil.php" class="btn btn-candidatar">Completar meu perfil</a>
                                    <?php endif; ?>

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
                                <?php else: ?>
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

<script src="/assets/js/bookmark.js"></script>
<?php include 'includes/footer.php'; ?>