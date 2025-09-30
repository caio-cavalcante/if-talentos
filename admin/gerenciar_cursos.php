<?php
// 1. INICIALIZAÇÃO E SEGURANÇA
session_start();
require '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] != 3) {
    header("Location: ../login.php");
    exit;
}

$errors = [];
$feedback_message = '';
$feedback_type = '';

// 2. LÓGICA DE PROCESSAMENTO (CREATE, UPDATE, DELETE)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // AÇÃO: DELETAR CURSO
    if (isset($_POST['delete_curso'])) {
        $id_curso_para_deletar = $_POST['id_curso'];

        // Verificação de integridade: Checa se algum aluno está vinculado a este curso
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM aluno WHERE id_curso = ?");
        $stmt_check->execute([$id_curso_para_deletar]);
        $aluno_count = $stmt_check->fetchColumn();

        if ($aluno_count > 0) {
            header("Location: gerenciar_cursos.php?error=vinculado");
        } else {
            $stmt = $pdo->prepare("DELETE FROM curso WHERE id_curso = ?");
            $stmt->execute([$id_curso_para_deletar]);
            header("Location: gerenciar_cursos.php?status=deletado");
        }
        exit;
    }

    // AÇÃO: CRIAR OU ATUALIZAR CURSO
    $id_curso = $_POST['id_curso'] ?? null;
    $nome_curso = trim($_POST['nome_curso']);
    $id_departam = filter_input(INPUT_POST, 'id_departam', FILTER_VALIDATE_INT);

    if (empty($nome_curso) || !$id_departam) {
        $errors[] = "Nome do curso e departamento são obrigatórios.";
    }

    if (empty($errors)) {
        if ($id_curso) { // UPDATE
            $sql = "UPDATE curso SET nome_curso = ?, id_departam = ? WHERE id_curso = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome_curso, $id_departam, $id_curso]);
        } else { // INSERT
            $sql = "INSERT INTO curso (nome_curso, id_departam) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome_curso, $id_departam]);
        }
        header("Location: gerenciar_cursos.php?status=sucesso");
        exit;
    }
}


// 3. LÓGICA DE LEITURA
// Busca o curso a ser editado (se aplicável)
$curso_para_editar = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $stmt_edit = $pdo->prepare("SELECT * FROM curso WHERE id_curso = ?");
    $stmt_edit->execute([$_GET['id']]);
    $curso_para_editar = $stmt_edit->fetch(PDO::FETCH_ASSOC);
}

// Busca todos os cursos para a listagem (com JOIN para nome do departamento)
$cursos = $pdo->query("SELECT c.id_curso, c.nome_curso, d.nome AS nome_departamento
                      FROM curso c
                      JOIN departamento d ON c.id_departam = d.id_departam
                      ORDER BY c.nome_curso ASC")->fetchAll(PDO::FETCH_ASSOC);

// Busca todos os departamentos para o <select> do formulário
$departamentos = $pdo->query("SELECT * FROM departamento ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

// Tratamento de mensagens de feedback
if (isset($_GET['status'])) { /* ... lógica de feedback de sucesso ... */ }
if (isset($_GET['error'])) {
    $feedback_type = 'error';
    if ($_GET['error'] == 'vinculado') {
        $feedback_message = 'Erro: Este curso não pode ser excluído pois há alunos matriculados nele.';
    }
}


$pageTitle = "Gerenciar Cursos";
include '../includes/header.php';
?>

<main class="admin-page">
    <div class="container">
        <h2><?php echo $curso_para_editar ? 'Editar Curso' : 'Adicionar Novo Curso'; ?></h2>

        <!-- Banners de feedback -->
        <?php if (!empty($feedback_message)): ?>
            <div class="feedback-banner <?php echo $feedback_type; ?>"><?php echo htmlspecialchars($feedback_message); ?></div>
        <?php endif; ?>

        <form action="gerenciar_cursos.php" method="POST" class="crud-form">
            <?php if ($curso_para_editar): ?>
                <input type="hidden" name="id_curso" value="<?php echo htmlspecialchars($curso_para_editar['id_curso']); ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="nome_curso">Nome do Curso</label>
                <input type="text" id="nome_curso" name="nome_curso" value="<?php echo htmlspecialchars($curso_para_editar['nome_curso'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="id_departam">Departamento</label>
                <select id="id_departam" name="id_departam" required>
                    <option value="">Selecione um departamento</option>
                    <?php foreach ($departamentos as $depto): ?>
                        <option value="<?php echo $depto['id_departam']; ?>" <?php echo (($curso_para_editar['id_departam'] ?? '') == $depto['id_departam']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($depto['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn"><?php echo $curso_para_editar ? 'Atualizar Curso' : 'Adicionar Curso'; ?></button>
                <?php if ($curso_para_editar): ?>
                    <a href="gerenciar_cursos.php" class="btn btn-secondary">Cancelar Edição</a>
                <?php endif; ?>
            </div>
        </form>

        <h2>Cursos Cadastrados</h2>
        <table class="crud-table">
            <thead>
                <tr>
                    <th>Nome do Curso</th>
                    <th>Departamento</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cursos as $curso): ?>
                <tr>
                    <td><?php echo htmlspecialchars($curso['nome_curso']); ?></td>
                    <td><?php echo htmlspecialchars($curso['nome_departamento']); ?></td>
                    <td class="actions">
                        <a href="gerenciar_cursos.php?action=edit&id=<?php echo $curso['id_curso']; ?>" class="btn-edit">Editar</a>
                        <form action="gerenciar_cursos.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este curso?');" style="display:inline;">
                            <input type="hidden" name="id_curso" value="<?php echo $curso['id_curso']; ?>">
                            <button type="submit" name="delete_curso" class="btn-delete">Excluir</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include '../includes/footer.php'; ?>