<?php
/* vincular login de alunos com o login do suap e poder ver quais usuários estão ativos, suas informações, e se eles já aplicaram em alguma vaga */

// 1. INICIALIZAÇÃO E SEGURANÇA
session_start();
require '../includes/db_connect.php';

// Redireciona se não estiver logado ou não for admin (tipo = 3)
if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] != 3) {
    header("Location: ../login.php");
    exit;
}

// 2. LÓGICA PARA DELETAR UM USUÁRIO (se a ação for enviada)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_usuario'])) {
    $id_usuario_para_deletar = $_POST['id_usuario'];
    
    // A deleção na tabela 'usuario' irá cascatear para 'aluno' ou 'empresa'
    // graças ao "ON DELETE CASCADE" que definimos no SQL.
    $stmt = $pdo->prepare("DELETE FROM usuario WHERE id_usuario = ?");
    $stmt->execute([$id_usuario_para_deletar]);
    
    // Redireciona para a mesma página para evitar reenvio do formulário
    header("Location: gerenciar_usuarios.php?tipo=" . urlencode($_POST['tipo_atual']) . "&status=deletado");
    exit;
}

// 3. LÓGICA PARA SELECIONAR QUAL TIPO DE USUÁRIO EXIBIR
$tipo_selecionado = $_GET['tipo'] ?? 'aluno'; // 'aluno' é o padrão
$usuarios = [];

switch ($tipo_selecionado) {
    case 'empresa':
        $sql = "SELECT u.id_usuario, u.nome, u.email, e.nome_fant, e.cnpj
                FROM usuario u
                JOIN empresa e ON u.id_usuario = e.id_empresa
                WHERE u.tipo = 2 ORDER BY u.nome";
        break;
    case 'admin':
        $sql = "SELECT id_usuario, nome, email
                FROM usuario
                WHERE tipo = 3 ORDER BY nome";
        break;
    case 'aluno':
    default:
        $sql = "SELECT u.id_usuario, u.nome, a.sobrenome, u.email, a.matricula, c.nome_curso
                FROM usuario u
                JOIN aluno a ON u.id_usuario = a.id_aluno
                LEFT JOIN curso c ON a.id_curso = c.id_curso
                WHERE u.tipo = 1 ORDER BY u.nome";
        break;
}

// 4. LÓGICA PARA TRATAR MENSAGENS DE FEEDBACK
$feedback_message = '';
$feedback_type = '';

if (isset($_GET['status'])) {
    $feedback_type = 'success';
    switch ($_GET['status']) {
        case 'editado':
            $feedback_message = 'Usuário atualizado com sucesso!';
            break;
        case 'deletado':
            $feedback_message = 'Usuário excluído com sucesso.';
            break;
        case 'criado':
            $feedback_message = 'Novo usuário criado com sucesso.';
            break;
    }
} elseif (isset($_GET['error'])) {
    $feedback_type = 'error';
    switch ($_GET['error']) {
        case 'invalid_id':
            $feedback_message = 'Erro: O ID do usuário fornecido é inválido.';
            break;
        case 'not_found':
            $feedback_message = 'Erro: O usuário que você tentou editar não foi encontrado.';
            break;
        case 'update_failed':
            $feedback_message = 'Erro: Falha ao atualizar o usuário. Tente novamente.';
            break;
    }
}

$stmt = $pdo->query($sql);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Gerenciar Usuários | Admin";
include '../includes/header.php';
?>

<main class="admin-page">
    <div class="container">
        <h2>Gerenciar Usuários</h2>

        <!-- BANNER DE FEEDBACK -->
        <?php if (!empty($feedback_message)): ?>
            <div class="feedback-banner <?php echo $feedback_type; ?>">
                <?php echo htmlspecialchars($feedback_message); ?>
            </div>
        <?php endif; ?>

        <div class="admin-actions">
            <a href="adicionar_usuario.php" class="btn-create">Adicionar Novo Usuário</a>
        </div>

        <!-- Abas de Navegação -->
        <div class="admin-tabs">
            <a href="?tipo=aluno" class="<?php echo $tipo_selecionado == 'aluno' ? 'active' : ''; ?>">Alunos</a>
            <a href="?tipo=empresa" class="<?php echo $tipo_selecionado == 'empresa' ? 'active' : ''; ?>">Empresas</a>
            <a href="?tipo=admin" class="<?php echo $tipo_selecionado == 'admin' ? 'active' : ''; ?>">Administradores</a>
        </div>

        <!-- Tabela de Resultados -->
        <div class="table-container">
            <table class="crud-table">
                <thead>
                    <?php if ($tipo_selecionado == 'aluno'): ?>
                        <tr><th>Nome Completo</th><th>Matrícula</th><th>E-mail</th><th>Curso</th><th>Ações</th></tr>
                    <?php elseif ($tipo_selecionado == 'empresa'): ?>
                        <tr><th>Nome Fantasia</th><th>Responsável</th><th>CNPJ</th><th>E-mail</th><th>Ações</th></tr>
                    <?php else: // admin ?>
                        <tr><th>Nome</th><th>E-mail</th><th>Ações</th></tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php if (count($usuarios) > 0): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <!-- Células da tabela mudam dinamicamente -->
                                <?php if ($tipo_selecionado == 'aluno'): ?>
                                    <td><?php echo htmlspecialchars($usuario['nome'] . ' ' . $usuario['sobrenome']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['matricula']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['nome_curso'] ?? 'Não informado'); ?></td>
                                <?php elseif ($tipo_selecionado == 'empresa'): ?>
                                    <td><?php echo htmlspecialchars($usuario['nome_fant']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['cnpj']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <?php else: // admin ?>
                                    <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <?php endif; ?>
                                
                                <td class="actions">
                                    <a href="editar_usuario.php?id=<?php echo $usuario['id_usuario']; ?>" class="btn-edit" title="Funcionalidade a ser implementada">Editar</a>
                                    <form action="gerenciar_usuarios.php" method="POST" onsubmit="return confirm('Atenção: Excluir um usuário é uma ação permanente. Deseja continuar?');" style="display:inline;">
                                        <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                                        <input type="hidden" name="tipo_atual" value="<?php echo $tipo_selecionado; ?>">
                                        <button type="submit" name="delete_usuario" class="btn-delete">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">Nenhum usuário deste tipo encontrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>