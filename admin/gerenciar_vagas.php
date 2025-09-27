<?php
/* gerenciar todas as vagas postadas, podendo ver quem aplicou
MUDANÇA DE IDEIA: NÓS POSTAREMOS AS VAGAS, EMPRESAS SÓ VEEM OS ALUNOS
NO BANCO DE DADOS, EMPRESA VAI SERVIR SÓ PRA AGRUPAR, NADA COM LOGIN */

// 1. INICIALIZAÇÃO E SEGURANÇA
session_start();
require '../includes/db_connect.php'; // Nosso arquivo de conexão

// Redireciona se não estiver logado ou não for admin (tipo = 3)
if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] != 3) {
    header("Location: ../login.php");
    exit;
}

// 2. LÓGICA DE PROCESSAMENTO (CREATE, UPDATE, DELETE)
$errors = [];

// --- Lógica para processar o envio de formulários (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- AÇÃO: DELETAR VAGA ---
    if (isset($_POST['delete_vaga'])) {
        $vaga_id_para_deletar = $_POST['vaga_id'];
        $stmt = $pdo->prepare("DELETE FROM vaga WHERE id_vaga = ?");
        $stmt->execute([$vaga_id_para_deletar]);
        header("Location: gerenciar_vagas.php?status=deletado");
        exit;
    }

    // --- AÇÃO: CRIAR OU ATUALIZAR VAGA ---
    $vaga_id = $_POST['vaga_id'] ?? null;
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $faixa_salarial = $_POST['faixa_salarial'];
    $pre_requi = trim($_POST['pre_requi']);
    $modalidade = trim($_POST['modalidade']);
    $link_candidatura = trim($_POST['link_candidatura']);
    
    // Validação simples
    if (empty($titulo)) {
        $errors[] = "O título é obrigatório.";
    }

    if (empty($errors)) {
        if ($vaga_id) { 
            // É um UPDATE (Atualização)
            $sql = "UPDATE vaga SET titulo = ?, descricao = ?, faixa_salarial = ?, pre_requi = ?, modalidade = ?, link_candidatura = ? WHERE id_vaga = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titulo, $descricao, $faixa_salarial, $pre_requi, $modalidade, $link_candidatura, $vaga_id]);
        } else { 
            // É um INSERT (Criação)
            $sql = "INSERT INTO vaga (titulo, descricao, faixa_salarial, pre_requi, modalidade, id_usuario_admin, link_candidatura) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titulo, $descricao, $faixa_salarial, $pre_requi, $modalidade, $_SESSION['user_id'], $link_candidatura]);
        }
        header("Location: gerenciar_vagas.php?status=sucesso");
        exit;
    }
}

// 3. LÓGICA DE PREPARAÇÃO (PARA PREENCHER O FORMULÁRIO DE EDIÇÃO)
$vaga_para_editar = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM vaga WHERE id_vaga = ?");
    $stmt->execute([$_GET['id']]);
    $vaga_para_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 4. LÓGICA DE LEITURA (PARA EXIBIR A LISTA DE VAGAS)
$vagas = $pdo->query("SELECT * FROM vw_gerenciamento_vagas")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Gerenciar Vagas | Admin";
include '../includes/header.php';
?>

<main class="admin-page">
    <div class="container">
        <h2><?php echo $vaga_para_editar ? 'Editar Vaga' : 'Criar Nova Vaga'; ?></h2>
        
        <form action="gerenciar_vagas.php" method="POST" class="crud-form">
            <?php if ($vaga_para_editar): ?>
                <input type="hidden" name="vaga_id" value="<?php echo htmlspecialchars($vaga_para_editar['id_vaga']); ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="titulo">Título da Vaga</label>
                <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($vaga_para_editar['titulo'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="descricao">Descrição Completa</label>
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

            <button type="submit" class="btn"><?php echo $vaga_para_editar ? 'Atualizar Vaga' : 'Criar Vaga'; ?></button>
            <?php if ($vaga_para_editar): ?>
                <a href="gerenciar_vagas.php" class="btn btn-secondary">Cancelar Edição</a>
            <?php endif; ?>
        </form>

        <h2>Vagas Atuais</h2>
        <table class="crud-table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Status</th>
                    <th>Publicada em</th>
                    <th>Expira em</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vagas as $vaga): ?>
                <tr>
                    <td><?php echo htmlspecialchars($vaga['titulo']); ?></td>
                    <td><?php echo htmlspecialchars($vaga['status']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($vaga['data_publicacao'])); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($vaga['data_expirar'])); ?></td>
                    <td class="actions">
                        <div class="action-buttons">
                            <a href="gerenciar_vagas.php?action=edit&id=<?php echo $vaga['id_vaga']; ?>" class="btn-edit">Editar</a>
                        
                            <form action="gerenciar_vagas.php" method="POST" onsubmit="return confirm('Tem certeza?');" style="display:inline;">
                                <input type="hidden" name="vaga_id" value="<?php echo $vaga['id_vaga']; ?>">
                                <button type="submit" name="delete_vaga" class="btn-delete">Excluir</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include '../includes/footer.php'; ?>