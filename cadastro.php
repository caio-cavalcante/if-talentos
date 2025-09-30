<?php
// 1. INICIALIZAÇÃO
session_start();
require 'includes/db_connect.php';

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_tipo']) && $_SESSION['user_tipo'] == 2) {
        header("Location: /empresa/index.php");
        exit;
    } else if (isset($_SESSION['user_tipo']) && $_SESSION['user_tipo'] == 1) {
        header("Location: /aluno/index.php");
        exit;
    } else if (isset($_SESSION['user_tipo']) && $_SESSION['user_tipo'] == 3) {
        header("Location: /index.php");
        exit;
    }
}

$errors = []; // Array para armazenar mensagens de erro e feedback
$old_input = []; // Array para guardar os dados submetidos
$submitted_form_type = ''; // Variável para lembrar qual formulário foi enviado

// 2. LÓGICA DE PROCESSAMENTO DO FORMULÁRIO (BACK-END)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_input = $_POST; // Guarda todos os dados para repopular o formulário em caso de erro
    $tipo_cadastro = $_POST['tipo_cadastro'];
    $submitted_form_type = $tipo_cadastro;

    // --- Dados comuns a ambos os cadastros ---
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // --- Validação dos campos comuns ---
    if (empty($nome) || empty($email) || empty($senha)) {
        $errors['geral'] = "Todos os campos marcados com * são obrigatórios.";
    }
    if ($senha !== $confirmar_senha) {
        $errors['confirmar_senha'] = "As senhas não coincidem.";
    }

    // --- Verificação de duplicidade para campos da tabela 'usuario' ---
    $stmt_email = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
    $stmt_email->execute([$email]);
    if ($stmt_email->fetch()) {
        $errors['email'] = "Este e-mail já está em uso.";
    }

    // --- Validação específica por tipo de cadastro ---
    if ($tipo_cadastro == 'aluno') {
        $sobrenome = trim($_POST['sobrenome']);
        $cpf = trim($_POST['cpf']);

        $stmt_cpf = $pdo->prepare("SELECT id_aluno FROM aluno WHERE cpf = ?");
        $stmt_cpf->execute([$cpf]);
        if ($stmt_cpf->fetch()) {
            $errors['cpf'] = "Este CPF já está cadastrado.";
        }
    } elseif ($tipo_cadastro == 'empresa') {
        $cnpj = trim($_POST['cnpj']);

        $stmt_cnpj = $pdo->prepare("SELECT id_empresa FROM empresa WHERE cnpj = ?");
        $stmt_cnpj->execute([$cnpj]);
        if ($stmt_cnpj->fetch()) {
            $errors['cnpj'] = "Este CNPJ já está cadastrado.";
        }
    }

    // --- Processamento se não houver erros ---
    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $tipo_usuario_int = ($tipo_cadastro == 'aluno') ? 1 : 2;

            $sql_usuario = "INSERT INTO usuario (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
            $stmt_usuario = $pdo->prepare($sql_usuario);
            $stmt_usuario->execute([$nome, $email, $senha_hash, $tipo_usuario_int]);
            $id_usuario_novo = $pdo->lastInsertId();

            if ($tipo_cadastro == 'aluno') {
                $sql_aluno = "INSERT INTO aluno (id_aluno, sobrenome, cpf) VALUES (?, ?, ?)";
                $stmt_aluno = $pdo->prepare($sql_aluno);
                $stmt_aluno->execute([$id_usuario_novo, $sobrenome, $cpf]);
            } elseif ($tipo_cadastro == 'empresa') {
                $sql_empresa = "INSERT INTO empresa (id_empresa, cnpj) VALUES (?, ?)";
                $stmt_empresa = $pdo->prepare($sql_empresa);
                $stmt_empresa->execute([$id_usuario_novo, $cnpj]);
            }

            $pdo->commit();
            header("Location: login.php?status=cadastrado");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors['geral'] = "Erro ao realizar o cadastro. Por favor, tente novamente.";
            error_log($e->getMessage());
        }
    }
}

$pageTitle = "Cadastro | IF - Talentos";
include 'includes/header.php';
?>

<!-- 3. INTERFACE DO USUÁRIO -->
<main class="register-page">
    <div class="container">
        <div class="register-form-container">
            <h2>Crie sua Conta</h2>

            <div id="user-type-selector" <?php if(!empty($submitted_form_type)) echo 'style="display:none;"'; ?>>
                <p>Selecione seu perfil para iniciar o cadastro.</p>
                <div class="selector-buttons">
                    <button class="selection-btn" data-form-target="form-aluno">Sou Aluno</button>
                    <button class="selection-btn" data-form-target="form-empresa">Sou Empresa</button>
                </div>
            </div>

            <?php if (isset($errors['geral'])): ?>
                <div class="error-banner">
                    <p><?php echo htmlspecialchars($errors['geral']); ?></p>
                </div>
            <?php endif; ?>

            <!-- FORMULÁRIO DE CADASTRO DE ALUNO -->
            <form id="form-aluno" action="cadastro.php" method="POST" class="register-form" <?php if($submitted_form_type !== 'aluno') echo 'style="display:none;"'; ?>>
                <h3>Cadastro de Aluno</h3>
                <input type="hidden" name="tipo_cadastro" value="aluno">

                <div class="form-group">
                    <input type="text" name="nome" placeholder="Nome" required value="<?php echo htmlspecialchars($old_input['nome'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <input type="text" name="sobrenome" placeholder="Sobrenome" required value="<?php echo htmlspecialchars($old_input['sobrenome'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <input type="email" name="email" placeholder="E-mail Institucional" required value="<?php echo htmlspecialchars($old_input['email'] ?? ''); ?>">
                    <?php if (isset($errors['email'])): ?><span class="error-message"><?php echo $errors['email']; ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <input type="text" name="cpf" placeholder="CPF" required value="<?php echo htmlspecialchars($old_input['cpf'] ?? ''); ?>">
                    <?php if (isset($errors['cpf'])): ?><span class="error-message"><?php echo $errors['cpf']; ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <input type="password" name="senha" placeholder="Senha" required>
                    <?php if (isset($errors['senha'])): ?><span class="error-message"><?php echo $errors['senha']; ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <input type="password" name="confirmar_senha" placeholder="Confirmar Senha" required>
                    <?php if (isset($errors['confirmar_senha'])): ?><span class="error-message"><?php echo $errors['confirmar_senha']; ?></span><?php endif; ?>
                </div>

                <button type="submit" class="btn-register">Criar Conta de Aluno</button>
                <p class="form-switcher">Não é um aluno? <a href="#" data-form-target="form-empresa">Cadastre-se como empresa.</a></p>
            </form>

            <!-- FORMULÁRIO DE CADASTRO DE EMPRESA -->
            <form id="form-empresa" action="cadastro.php" method="POST" class="register-form" <?php if($submitted_form_type !== 'empresa') echo 'style="display:none;"'; ?>>
                <h3>Cadastro de Empresa</h3>
                <input type="hidden" name="tipo_cadastro" value="empresa">

                <div class="form-group">
                    <input type="text" name="nome" placeholder="Nome do Responsável" required value="<?php echo htmlspecialchars($old_input['nome'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <input type="email" name="email" placeholder="E-mail Corporativo" required value="<?php echo htmlspecialchars($old_input['email'] ?? ''); ?>">
                    <?php if (isset($errors['email'])): ?><span class="error-message"><?php echo $errors['email']; ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <input type="text" name="cnpj" placeholder="CNPJ" required value="<?php echo htmlspecialchars($old_input['cnpj'] ?? ''); ?>">
                    <?php if (isset($errors['cnpj'])): ?><span class="error-message"><?php echo $errors['cnpj']; ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <input type="password" name="senha" placeholder="Senha" required>
                    <?php if (isset($errors['senha'])): ?><span class="error-message"><?php echo $errors['senha']; ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <input type="password" name="confirmar_senha" placeholder="Confirmar Senha" required>
                    <?php if (isset($errors['confirmar_senha'])): ?><span class="error-message"><?php echo $errors['confirmar_senha']; ?></span><?php endif; ?>
                </div>

                <button type="submit" class="btn-register">Criar Conta de Empresa</button>
                <p class="form-switcher">Não é uma empresa? <a href="#" data-form-target="form-aluno">Cadastre-se como aluno.</a></p>
            </form>

            <div class="login-link">
                <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectorContainer = document.getElementById('user-type-selector');
        const allForms = document.querySelectorAll('.register-form');
        const commonFields = ['nome', 'tel', 'email', 'senha', 'confirmar_senha'];

        function switchForm(targetId) {
            // Esconde o seletor inicial
            selectorContainer.style.display = 'none';

            // Encontra o formulário que está visível no momento (se houver)
            let currentForm = null;
            allForms.forEach(f => {
                if (f.style.display === 'block') {
                    currentForm = f;
                }
            });

            // Copia os dados dos campos comuns do formulário atual para o de destino
            if (currentForm) {
                const targetForm = document.getElementById(targetId);
                commonFields.forEach(fieldName => {
                    const sourceField = currentForm.querySelector(`[name="${fieldName}"]`);
                    const destField = targetForm.querySelector(`[name="${fieldName}"]`);
                    if (sourceField && destField) {
                        destField.value = sourceField.value;
                    }
                });
            }

            // Esconde todos os formulários e exibe apenas o de destino
            allForms.forEach(form => {
                form.style.display = form.id === targetId ? 'block' : 'none';
            });
        }

        // Adiciona o evento de clique aos botões de seleção inicial
        document.querySelectorAll('.selection-btn, .form-switcher a').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const targetFormId = this.getAttribute('data-form-target');
                switchForm(targetFormId);
            });
        });

        // Se a página foi recarregada após um erro de submissão, mostra o formulário correto
        const submittedForm = '<?php echo $submitted_form_type; ?>';
        if (submittedForm === 'aluno') {
            switchForm('form-aluno');
        } else if (submittedForm === 'empresa') {
            switchForm('form-empresa');
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        var submittedForm = '<?php echo $submitted_form_type; ?>';
        if (submittedForm === 'aluno') {
            // Esconde o seletor e mostra o formulário de aluno
            document.getElementById('user-type-selector').style.display = 'none';
            document.getElementById('form-aluno').style.display = 'block';
        } else if (submittedForm === 'empresa') {
            // Esconde o seletor e mostra o formulário de empresa
            document.getElementById('user-type-selector').style.display = 'none';
            document.getElementById('form-empresa').style.display = 'block';
        }
    });
</script>

<?php include 'includes/footer.php'; ?>