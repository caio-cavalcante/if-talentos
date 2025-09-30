<?php
// 1. INICIALIZAÇÃO E SEGURANÇA
session_start();
require '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] != 3) {
    header("Location: ../login.php");
    exit;
}
$id_admin_logado = $_SESSION['user_id'];

// 2. LÓGICA DE PROCESSAMENTO (APROVAR / REJEITAR)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_vaga'])) {
    $id_vaga = $_POST['id_vaga'];
    $novo_status = '';

    if (isset($_POST['aprovar'])) {
        $novo_status = 'Aprovada';
    } elseif (isset($_POST['rejeitar'])) {
        $novo_status = 'Rejeitada';
    }

    if ($novo_status) {
        $sql = "UPDATE vaga SET status = ?, id_usuario_aprovador = ? WHERE id_vaga = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$novo_status, $id_admin_logado, $id_vaga]);
        header("Location: aprovar_vagas.php?status=atualizado");
        exit;
    }
}

// 3. LÓGICA DE LEITURA
// Busca todas as vagas pendentes e os dados da empresa que a criou
$sql_pendentes = "SELECT v.*, e.nome_fant FROM vaga v
                  JOIN empresa e ON v.id_usuario_criador = e.id_empresa
                  WHERE v.status = 'Pendente' ORDER BY v.data_publicacao ASC";
$vagas_pendentes = $pdo->query($sql_pendentes)->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Aprovar Vagas Pendentes";
include '../includes/header.php';
?>
<main class="admin-page">
    <div class="container">
        <h2>Vagas Pendentes de Aprovação</h2>
        <p>Revise as vagas enviadas pelas empresas antes de publicá-las na plataforma.</p>

        <div class="approval-list">
            <?php if (count($vagas_pendentes) > 0): ?>
                <?php foreach ($vagas_pendentes as $vaga): ?>
                    <div class="approval-card">
                        <div class="card-header">
                            <h3><?php echo htmlspecialchars($vaga['titulo']); ?></h3>
                            <span><strong>Empresa:</strong> <?php echo htmlspecialchars($vaga['nome_fant']); ?></span>
                        </div>
                        <div class="card-body">
                            <p><strong>Descrição:</strong> <?php echo nl2br(htmlspecialchars($vaga['descricao'])); ?></p>
                            <p><strong>Pré-requisitos:</strong> <?php echo nl2br(htmlspecialchars($vaga['pre_requi'])); ?></p>
                            <p><strong>Faixa Salarial:</strong> <?php echo nl2br(htmlspecialchars($vaga['faixa_salarial'])); ?></p>
                            <p><strong>Modalidade:</strong> <?php echo nl2br(htmlspecialchars($vaga['modalidade'])); ?></p>
                            <p><strong>Data para Expirar:</strong> <?php echo nl2br(htmlspecialchars($vaga['data_expirar'])); ?></p>
                            <a href="<?php echo nl2br(htmlspecialchars($vaga['link_candidatura'])); ?>" target="_blank" class="btn btn-candidatar"><strong>Link para Candidatura</strong></a>
                        </div>
                        <div class="card-actions">
                            <form method="POST" action="aprovar_vagas.php">
                                <input type="hidden" name="id_vaga" value="<?php echo $vaga['id_vaga']; ?>">
                                <button type="submit" name="aprovar" class="btn btn-success">Aprovar</button>
                                <button type="submit" name="rejeitar" class="btn btn-danger">Rejeitar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhuma vaga pendente de aprovação no momento.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>