<?php
// 1. INICIALIZAÇÃO E SEGURANÇA
session_start();
require '../includes/db_connect.php';

// Redireciona se não estiver logado ou não for admin (tipo = 3)
if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] != 3) {
    header("Location: ../login.php");
    exit;
}

// Valida o ID do usuário da URL
$id_usuario_para_editar = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id_usuario_para_editar === null) {
    header("Location: gerenciar_usuarios.php?error=invalid_id");
    exit;
}

// 2. LÓGICA PARA ATUALIZAR UM USUÁRIO (se o formulário for enviado)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pdo->beginTransaction();
    try {
        // Dados da tabela 'usuario' (comuns a todos)
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $tel = trim($_POST['tel']);
        
        $sql_usuario = "UPDATE usuario SET nome = ?, email = ?, tel = ? WHERE id_usuario = ?";
        $stmt_usuario = $pdo->prepare($sql_usuario);
        $stmt_usuario->execute([$nome, $email, $tel, $id_usuario_para_editar]);

        // Dados específicos por tipo de usuário
        $tipo_usuario = $_POST['tipo'];
        if ($tipo_usuario == 1) { // Aluno
            $sobrenome = trim($_POST['sobrenome']);
            $matricula = trim($_POST['matricula']);
            $status = trim($_POST['status']);
            $sql_aluno = "UPDATE aluno SET sobrenome = ?, matricula = ?, status = ? WHERE id_aluno = ?";
            $stmt_aluno = $pdo->prepare($sql_aluno);
            $stmt_aluno->execute([$sobrenome, $matricula, $status, $id_usuario_para_editar]);

        } elseif ($tipo_usuario == 2) { // Empresa
            $nome_fant = trim($_POST['nome_fant']);
            $cnpj = trim($_POST['cnpj']);
            $sql_empresa = "UPDATE empresa SET nome_fant = ?, cnpj = ? WHERE id_empresa = ?";
            $stmt_empresa = $pdo->prepare($sql_empresa);
            $stmt_empresa->execute([$nome_fant, $cnpj, $id_usuario_para_editar]);
        }

        $pdo->commit();
        header("Location: gerenciar_usuarios.php?tipo=" . urlencode($_POST['tipo_nome']) . "&status=editado");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        header("Location: editar_usuario.php?id=" . $id_usuario_para_editar . "&error=update_failed");
        exit;
    }
}

// 3. BUSCAR DADOS DO USUÁRIO PARA PREENCHER O FORMULÁRIO
$sql = "SELECT 
            u.*, 
            a.sobrenome, a.matricula, a.cpf, a.data_nasc, a.status,
            e.nome_fant, e.cnpj
        FROM usuario u
        LEFT JOIN aluno a ON u.id_usuario = a.id_aluno
        LEFT JOIN empresa e ON u.id_usuario = e.id_empresa
        WHERE u.id_usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_usuario_para_editar]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario === false) {
    header("Location: gerenciar_usuarios.php?error=not_found");
    exit;
}

$pageTitle = "Editar Usuário | Admin";
include '../includes/header.php';
?>

<!-- 4. HTML DO FORMULÁRIO DE EDIÇÃO -->
<main class="admin-page">
    <div class="container">
        <h2>Editando Usuário: <?php echo htmlspecialchars($usuario['nome']); ?></h2>
        
        <form action="editar_usuario.php?id=<?php echo $id_usuario_para_editar; ?>" method="POST" class="crud-form">
            <input type="hidden" name="tipo" value="<?php echo $usuario['tipo']; ?>">
            
            <?php // Determina o nome do tipo para o redirecionamento
                $tipo_nome = '';
                if($usuario['tipo'] == 1) $tipo_nome = 'aluno';
                if($usuario['tipo'] == 2) $tipo_nome = 'empresa';
                if($usuario['tipo'] == 3) $tipo_nome = 'admin';
            ?>
            <input type="hidden" name="tipo_nome" value="<?php echo $tipo_nome; ?>">


            <h3>Informações Gerais</h3>
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
            </div>
            <div class="form-group-row">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="tel">Telefone</label>
                    <input type="text" id="tel" name="tel" value="<?php echo htmlspecialchars($usuario['tel']); ?>">
                </div>
            </div>

            <!-- CAMPOS ESPECÍFICOS POR TIPO DE USUÁRIO -->
            <?php switch ($usuario['tipo']):
                case 1: // Aluno ?>
                    <h3>Informações do Aluno</h3>
                    <div class="form-group">
                        <label for="sobrenome">Sobrenome</label>
                        <input type="text" id="sobrenome" name="sobrenome" value="<?php echo htmlspecialchars($usuario['sobrenome']); ?>" required>
                    </div>
                    <div class="form-group-row">
                        <div class="form-group">
                            <label for="matricula">Matrícula</label>
                            <input type="text" id="matricula" name="matricula" value="<?php echo htmlspecialchars($usuario['matricula']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <input type="text" id="status" name="status" value="<?php echo htmlspecialchars($usuario['status']); ?>">
                        </div>
                    </div>
                    <?php break; ?>

                <?php case 2: // Empresa ?>
                    <h3>Informações da Empresa</h3>
                    <div class="form-group">
                        <label for="nome_fant">Nome Fantasia</label>
                        <input type="text" id="nome_fant" name="nome_fant" value="<?php echo htmlspecialchars($usuario['nome_fant']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="cnpj">CNPJ</label>
                        <input type="text" id="cnpj" name="cnpj" value="<?php echo htmlspecialchars($usuario['cnpj']); ?>" required>
                    </div>
                    <?php break; ?>

                <?php case 3: // Admin ?>
                    <h3>Informações do Administrador</h3>
                    <p>Os administradores possuem apenas informações gerais.</p>
                    <?php break; ?>
            <?php endswitch; ?>

            <div class="form-actions">
                <button type="submit" class="btn">Salvar Alterações</button>
                <a href="gerenciar_usuarios.php?tipo=<?php echo $tipo_nome; ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</main>

<?php include '../includes/footer.php'; ?>