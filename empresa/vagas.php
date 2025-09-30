<?php
/* PRINCIPAL CRUD DO SISTEMA. Deve adicionar a vaga, com as informações necessárias e permissão para editar ou excluir. De alguma forma, permitir que alunos vejam as vagas postadas */
// 1. INICIALIZAÇÃO E SEGURANÇA
session_start();
require '../includes/db_connect.php';

// Redireciona se não for um usuário do tipo empresa logado
if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] != 2) {
    header("Location: ../login.php");
    exit;
}

$id_empresa_logada = $_SESSION['user_id'];

// Redireciona se o perfil da empresa estiver incompleto
$stmt_check = $pdo->prepare("SELECT status FROM empresa WHERE id_empresa = ?");
$stmt_check->execute([$id_empresa_logada]);
$empresa_status = $stmt_check->fetchColumn();
if ($empresa_status === 'Incompleto') {
    header("Location: index.php?error=perfil_incompleto");
    exit;
}

// 2. LÓGICA DE PROCESSAMENTO (CREATE, UPDATE, DELETE)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // AÇÃO: DELETAR VAGA
    if (isset($_POST['delete_vaga'])) {
        $id_vaga_para_deletar = $_POST['vaga_id'];
        // Segurança: Garante que a empresa só pode deletar suas próprias vagas
        $stmt = $pdo->prepare("DELETE FROM vaga WHERE id_vaga = ? AND id_usuario_criador = ?");
        $stmt->execute([$id_vaga_para_deletar, $id_empresa_logada]);
        header("Location: vagas.php?status=deletado");
        exit;
    }

    // AÇÃO: CRIAR OU ATUALIZAR VAGA
    $id_vaga = $_POST['vaga_id'] ?? null;
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $faixa_salarial = !empty($_POST['faixa_salarial']) ? $_POST['faixa_salarial'] : null;
    $pre_requi = trim($_POST['pre_requi']);
    $modalidade = trim($_POST['modalidade']);
    $link_candidatura = trim($_POST['link_candidatura']);
    $data_expirar = !empty($_POST['data_expirar']) ? $_POST['data_expirar'] : null;

    // Validação
    if (empty($titulo)) {
        $errors[] = "O título da vaga é obrigatório.";
    }
     if (empty($descricao)) {
        $errors[] = "A descrição da vaga é obrigatória.";
    }

    if (empty($errors)) {
        if ($id_vaga) { // UPDATE
            // Segurança: Garante que a empresa só pode editar suas próprias vagas
            $sql = "UPDATE vaga SET titulo = ?, descricao = ?, faixa_salarial = ?, pre_requi = ?, modalidade = ?, link_candidatura = ?, data_expirar = ?, status = 'Pendente' WHERE id_vaga = ? AND id_usuario_criador = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titulo, $descricao, $faixa_salarial, $pre_requi, $modalidade, $link_candidatura, $data_expirar, $id_vaga, $id_empresa_logada]);
        } else { // INSERT
            // Novas vagas sempre entram com status 'Pendente' para aprovação do admin
            $sql = "INSERT INTO vaga (titulo, descricao, faixa_salarial, pre_requi, modalidade, link_candidatura, data_expirar, id_usuario_criador, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendente')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titulo, $descricao, $faixa_salarial, $pre_requi, $modalidade, $link_candidatura, $data_expirar, $id_empresa_logada]);
        }
        header("Location: vagas.php?status=sucesso");
        exit;
    }
}

// 3. LÓGICA DE LEITURA
// Busca apenas as vagas criadas pela empresa logada
$vagas_empresa = $pdo->prepare("SELECT * FROM vaga WHERE id_usuario_criador = ? ORDER BY data_publicacao DESC");
$vagas_empresa->execute([$id_empresa_logada]);
$vagas = $vagas_empresa->fetchAll(PDO::FETCH_ASSOC);

// Lógica para preencher o formulário de edição (se aplicável)
$vaga_para_editar = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $stmt_edit = $pdo->prepare("SELECT * FROM vaga WHERE id_vaga = ? AND id_usuario_criador = ?");
    $stmt_edit->execute([$_GET['id'], $id_empresa_logada]);
    $vaga_para_editar = $stmt_edit->fetch(PDO::FETCH_ASSOC);
}

$pageTitle = "Minhas Vagas | Empresa";
include '../includes/header.php';
?>

<main class="admin-page">
    <div class="container">
        <h2><?php echo $vaga_para_editar ? 'Editar Vaga' : 'Criar Nova Vaga'; ?></h2>
        
        <form action="vagas.php" method="POST" class="crud-form">
            <?php if ($vaga_para_editar): ?>
                <input type="hidden" name="vaga_id" value="<?php echo htmlspecialchars($vaga_para_editar['id_vaga']); ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="titulo">Título da Vaga *</label>
                <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($vaga_para_editar['titulo'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="descricao">Descrição Completa *</label>
                <textarea id="descricao" name="descricao" rows="5" required><?php echo htmlspecialchars($vaga_para_editar['descricao'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="pre_requi">Pré-requisitos</label>
                <textarea id="pre_requi" name="pre_requi" rows="3"><?php echo htmlspecialchars($vaga_para_editar['pre_requi'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group-row">
                <div class="form-group">
                    <label for="faixa_salarial">Faixa Salarial (R$)</label>
                    <input type="number" step="0.01" id="faixa_salarial" name="faixa_salarial" value="<?php echo htmlspecialchars($vaga_para_editar['faixa_salarial'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="modalidade">Modalidade</label>
                    <select id="modalidade" name="modalidade">
                        <option value="Presencial" <?php echo (($vaga_para_editar['modalidade'] ?? '') == 'Presencial') ? 'selected' : ''; ?>>Presencial</option>
                        <option value="Híbrido" <?php echo (($vaga_para_editar['modalidade'] ?? '') == 'Híbrido') ? 'selected' : ''; ?>>Híbrido</option>
                        <option value="Remoto" <?php echo (($vaga_para_editar['modalidade'] ?? '') == 'Remoto') ? 'selected' : ''; ?>>Remoto</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="link_candidatura">Link Externo para Candidatura</label>
                <input type="url" id="link_candidatura" name="link_candidatura" placeholder="https://exemplo.com/vaga/123" value="<?php echo htmlspecialchars($vaga_para_editar['link_candidatura'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="data_expirar">Data de Expiração da Vaga</label>
                <input type="date" id="data_expirar" name="data_expirar" value="<?php echo htmlspecialchars($vaga_para_editar['data_expirar'] ?? ''); ?>">
            </div>

            <button type="submit" class="btn"><?php echo $vaga_para_editar ? 'Atualizar Vaga' : 'Publicar Vaga'; ?></button>
            <?php if ($vaga_para_editar): ?>
                <a href="vagas.php" class="btn btn-secondary">Cancelar Edição</a>
            <?php endif; ?>
        </form>

        <h2>Vagas Publicadas</h2>
        <table class="crud-table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Status</th>
                    <th>Publicada em</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vagas as $vaga): ?>
                <tr>
                    <td><?php echo htmlspecialchars($vaga['titulo']); ?></td>
                    <td>
                        <span class="status-tag status-<?php echo strtolower(htmlspecialchars($vaga['status'])); ?>">
                            <?php echo htmlspecialchars($vaga['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($vaga['data_publicacao'])); ?></td>
                    <td class="actions">
                        <a href="vagas.php?action=edit&id=<?php echo $vaga['id_vaga']; ?>" class="btn-edit">Editar</a>
                        <form action="vagas.php" method="POST" onsubmit="return confirm('Tem certeza?');" style="display:inline;">
                            <input type="hidden" name="vaga_id" value="<?php echo $vaga['id_vaga']; ?>">
                            <button type="submit" name="delete_vaga" class="btn-delete">Excluir</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include '../includes/footer.php'; ?>