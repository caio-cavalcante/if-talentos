<?php
// 1. INICIALIZAÇÃO E SEGURANÇA
session_start();
require '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] != 3) {
    header("Location: ../login.php");
    exit;
}

$errors = [];
$old_input = [];
$submitted_form_type = '';

// 2. LÓGICA DE PROCESSAMENTO DO FORMULÁRIO (BACK-END)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_input = $_POST;
    $tipo_cadastro = $_POST['tipo_cadastro'];
    $submitted_form_type = $tipo_cadastro;

    // --- Validação dos dados (adaptado de cadastro.php) ---
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $login = trim($_POST['login']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if (empty($senha)) {
        $errors['senha'] = "O campo senha é obrigatório.";
    } elseif ($senha !== $confirmar_senha) {
        $errors['confirmar_senha'] = "As senhas não coincidem.";
    }

    $stmt_email = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
    $stmt_email->execute([$email]);
    if ($stmt_email->fetch()) {
        $errors['email'] = "Este e-mail já está em uso.";
    }

    $stmt_login = $pdo->prepare("SELECT id_usuario FROM usuario WHERE login = ?");
    $stmt_login->execute([$login]);
    if ($stmt_login->fetch()) {
        $errors['login'] = "Este login já está em uso.";
    }
    
    if ($tipo_cadastro == 'aluno') {
        $matricula = trim($_POST['matricula']);
        $cpf = trim($_POST['cpf']);
        if(empty($matricula)) $errors['matricula'] = "Matrícula é obrigatória.";
        if(empty($cpf)) $errors['cpf'] = "CPF é obrigatório.";
        
    } elseif ($tipo_cadastro == 'empresa') {
        $cnpj = trim($_POST['cnpj']);
        if(empty($cnpj)) $errors['cnpj'] = "CNPJ é obrigatório.";
    }

    // --- Processamento se não houver erros ---
    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            $tel = trim($_POST['tel']);
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $tipo_usuario_int = 0;
            if($tipo_cadastro == 'aluno') $tipo_usuario_int = 1;
            if($tipo_cadastro == 'empresa') $tipo_usuario_int = 2;
            if($tipo_cadastro == 'admin') $tipo_usuario_int = 3;

            $sql_usuario = "INSERT INTO usuario (nome, tel, email, login, senha, tipo) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_usuario = $pdo->prepare($sql_usuario);
            $stmt_usuario->execute([$nome, $tel, $email, $login, $senha_hash, $tipo_usuario_int]);
            $id_usuario_novo = $pdo->lastInsertId();

            if ($tipo_cadastro == 'aluno') {
                $sobrenome = trim($_POST['sobrenome']);
                $data_nasc = $_POST['data_nasc'];
                $sql_aluno = "INSERT INTO aluno (id_aluno, matricula, sobrenome, cpf, data_nasc) VALUES (?, ?, ?, ?, ?)";
                $stmt_aluno = $pdo->prepare($sql_aluno);
                $stmt_aluno->execute([$id_usuario_novo, $matricula, $sobrenome, $cpf, $data_nasc]);
            } elseif ($tipo_cadastro == 'empresa') {
                $nome_fant = trim($_POST['nome_fant']);
                $sql_empresa = "INSERT INTO empresa (id_empresa, nome_fant, cnpj) VALUES (?, ?, ?)";
                $stmt_empresa = $pdo->prepare($sql_empresa);
                $stmt_empresa->execute([$id_usuario_novo, $nome_fant, $cnpj]);
            }

            $pdo->commit();
            header("Location: gerenciar_usuarios.php?tipo=" . $tipo_cadastro . "&status=criado");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors['geral'] = "Erro ao criar usuário: " . $e->getMessage();
        }
    }
}


$pageTitle = "Adicionar Novo Usuário";
include '../includes/header.php';
?>

<main class="admin-page">
    <div class="container">
        <h2>Adicionar Novo Usuário</h2>

        <div class="crud-form">
            <!-- Seletor inicial de tipo de usuário -->
            <div id="user-type-selector-admin">
                <p>Qual tipo de usuário você deseja criar?</p>
                <div class="selector-buttons">
                    <button class="selection-btn" data-form-target="form-aluno">Aluno</button>
                    <button class="selection-btn" data-form-target="form-empresa">Empresa</button>
                    <button class="selection-btn" data-form-target="form-admin">Administrador</button>
                </div>
            </div>

            <?php if (isset($errors['geral'])): ?>
                <div class="feedback-banner error"><p><?php echo htmlspecialchars($errors['geral']); ?></p></div>
            <?php endif; ?>

            <!-- Formulários (escondidos por padrão) -->

            <!-- FORMULÁRIO ALUNO -->
            <form id="form-aluno" action="adicionar_usuario.php" method="POST" class="register-form" style="display:none;">
                <h3>Novo Aluno</h3>
                <input type="hidden" name="tipo_cadastro" value="aluno">
                
                <div class="form-group"><input type="text" name="nome" placeholder="Nome" required value="<?php echo htmlspecialchars($old_input['nome'] ?? ''); ?>"></div>
                <div class="form-group"><input type="text" name="sobrenome" placeholder="Sobrenome" required value="<?php echo htmlspecialchars($old_input['sobrenome'] ?? ''); ?>"></div>
                <div class="form-group"><input type="email" name="email" placeholder="E-mail" required value="<?php echo htmlspecialchars($old_input['email'] ?? ''); ?>"></div>
                <div class="form-group"><input type="tel" name="tel" placeholder="Telefone" value="<?php echo htmlspecialchars($old_input['tel'] ?? ''); ?>"></div>
                <div class="form-group"><input type="text" name="login" placeholder="Login" required value="<?php echo htmlspecialchars($old_input['login'] ?? ''); ?>"></div>
                <div class="form-group"><input type="password" name="senha" placeholder="Senha" required></div>
                <div class="form-group"><input type="password" name="confirmar_senha" placeholder="Confirmar Senha" required></div>
                <div class="form-group"><input type="text" name="matricula" placeholder="Matrícula" required value="<?php echo htmlspecialchars($old_input['matricula'] ?? ''); ?>"></div>
                <div class="form-group"><input type="text" name="cpf" placeholder="CPF" required value="<?php echo htmlspecialchars($old_input['cpf'] ?? ''); ?>"></div>
                <div class="form-group"><label>Data de Nascimento</label><input type="date" name="data_nasc" required value="<?php echo htmlspecialchars($old_input['data_nasc'] ?? ''); ?>"></div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Criar Aluno</button>
                    <a href="gerenciar_usuarios.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>

            <!-- FORMULÁRIO EMPRESA -->
            <form id="form-empresa" action="adicionar_usuario.php" method="POST" class="register-form" style="display:none;">
                <h3>Nova Empresa</h3>
                <input type="hidden" name="tipo_cadastro" value="empresa">
                
                <div class="form-group"><input type="text" name="nome_fant" placeholder="Nome Fantasia" required value="<?php echo htmlspecialchars($old_input['nome_fant'] ?? ''); ?>"></div>
                <div class="form-group"><input type="text" name="cnpj" placeholder="CNPJ" required value="<?php echo htmlspecialchars($old_input['cnpj'] ?? ''); ?>"></div>
                <div class="form-group"><input type="text" name="nome" placeholder="Nome do Responsável" required value="<?php echo htmlspecialchars($old_input['nome'] ?? ''); ?>"></div>
                <div class="form-group"><input type="email" name="email" placeholder="E-mail" required value="<?php echo htmlspecialchars($old_input['email'] ?? ''); ?>"></div>
                <div class="form-group"><input type="tel" name="tel" placeholder="Telefone" value="<?php echo htmlspecialchars($old_input['tel'] ?? ''); ?>"></div>
                <div class="form-group"><input type="text" name="login" placeholder="Login" required value="<?php echo htmlspecialchars($old_input['login'] ?? ''); ?>"></div>
                <div class="form-group"><input type="password" name="senha" placeholder="Senha" required></div>
                <div class="form-group"><input type="password" name="confirmar_senha" placeholder="Confirmar Senha" required></div>

                <div class="form-actions">
                    <button type="submit" class="btn">Criar Empresa</button>
                    <a href="gerenciar_usuarios.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>

            <!-- FORMULÁRIO ADMIN -->
            <form id="form-admin" action="adicionar_usuario.php" method="POST" class="register-form" style="display:none;">
                <h3>Novo Administrador</h3>
                <input type="hidden" name="tipo_cadastro" value="admin">
                
                <div class="form-group"><input type="text" name="nome" placeholder="Nome Completo" required value="<?php echo htmlspecialchars($old_input['nome'] ?? ''); ?>"></div>
                <div class="form-group"><input type="email" name="email" placeholder="E-mail" required value="<?php echo htmlspecialchars($old_input['email'] ?? ''); ?>"></div>
                <div class="form-group"><input type="tel" name="tel" placeholder="Telefone" value="<?php echo htmlspecialchars($old_input['tel'] ?? ''); ?>"></div>
                <div class="form-group"><input type="text" name="login" placeholder="Login" required value="<?php echo htmlspecialchars($old_input['login'] ?? ''); ?>"></div>
                <div class="form-group"><input type="password" name="senha" placeholder="Senha" required></div>
                <div class="form-group"><input type="password" name="confirmar_senha" placeholder="Confirmar Senha" required></div>

                <div class="form-actions">
                    <button type="submit" class="btn">Criar Admin</button>
                    <a href="gerenciar_usuarios.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectorContainer = document.getElementById('user-type-selector-admin');
        const allForms = document.querySelectorAll('.register-form');

        function showForm(targetId) {
            if (selectorContainer) {
                selectorContainer.style.display = 'none';
            }
            allForms.forEach(form => {
                form.style.display = form.id === targetId ? 'block' : 'none';
            });
        }

        document.querySelectorAll('.selection-btn').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const targetFormId = this.getAttribute('data-form-target');
                showForm(targetFormId);
            });
        });

        const submittedFormType = '<?php echo $submitted_form_type; ?>';
        if (submittedFormType) {
            showForm('form-' + submittedFormType);
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
