<?php
// 1. INICIALIZAÇÃO E SEGURANÇA
session_start();
require '../includes/db_connect.php';

// Redireciona se não estiver logado ou não for aluno
if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] != 1) {
    header("Location: ../login.php");
    exit;
}

$id_aluno_logado = $_SESSION['user_id'];
$errors = [];
$success_message = '';

// 2. LÓGICA DE ATUALIZAÇÃO DO PERFIL (QUANDO O FORMULÁRIO É ENVIADO)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coleta dos dados do formulário
    $nome = trim($_POST['nome']);
    $sobrenome = trim($_POST['sobrenome']);
    $email = trim($_POST['email']);
    $tel = trim($_POST['tel']);
    $matricula = trim($_POST['matricula']);
    $data_nasc = $_POST['data_nasc'];
    $link_perfil_externo = trim($_POST['link_perfil_externo']);
    // Habilidades: Transforma string separada por vírgulas em um array e depois em JSON
    $habilidades_string = trim($_POST['habilidades']);
    $habilidades_array = array_map('trim', explode(',', $habilidades_string));
    $habilidades_json = json_encode($habilidades_array);

    // Validação de campos obrigatórios
    if (empty($nome) || empty($sobrenome) || empty($email) || empty($matricula) || empty($data_nasc)) {
        $errors[] = "Todos os campos com * são obrigatórios para completar o perfil.";
    }

    // Validação de e-mail e matrícula únicos (excluindo o próprio usuário)
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = ? AND id_usuario != ?");
    $stmt->execute([$email, $id_aluno_logado]);
    if ($stmt->fetch()) {
        $errors[] = "Este e-mail já está em uso por outra conta.";
    }

    $stmt = $pdo->prepare("SELECT id_aluno FROM aluno WHERE matricula = ? AND id_aluno != ?");
    $stmt->execute([$matricula, $id_aluno_logado]);
    if ($stmt->fetch()) {
        $errors[] = "Esta matrícula já está em uso por outra conta.";
    }

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            // Atualiza a tabela 'usuario'
            $sql_usuario = "UPDATE usuario SET nome = ?, email = ?, tel = ?, link_perfil_externo = ? WHERE id_usuario = ?";
            $stmt_usuario = $pdo->prepare($sql_usuario);
            $stmt_usuario->execute([$nome, $email, $tel, $link_perfil_externo, $id_aluno_logado]);

            // Atualiza a tabela 'aluno' e define o status como 'Regular'
            $sql_aluno = "UPDATE aluno SET sobrenome = ?, matricula = ?, data_nasc = ?, habilidades = ?, status = 'Regular' WHERE id_aluno = ?";
            $stmt_aluno = $pdo->prepare($sql_aluno);
            $stmt_aluno->execute([$sobrenome, $matricula, $data_nasc, $habilidades_json, $id_aluno_logado]);

            $pdo->commit();
            $success_message = "Perfil atualizado com sucesso!";
            // Atualiza o nome na sessão, caso tenha sido alterado
            $_SESSION['user_nome'] = $nome;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Ocorreu um erro ao atualizar o perfil. Tente novamente.";
            // error_log($e->getMessage()); // Para depuração
        }
    }
}

// 3. BUSCAR DADOS ATUAIS DO ALUNO PARA PREENCHER O FORMULÁRIO
$sql = "SELECT u.nome, u.email, u.link_perfil_externo, u.tel, a.sobrenome, a.matricula, a.cpf, a.data_nasc, a.status, a.habilidades
        FROM usuario u
        JOIN aluno a ON u.id_usuario = a.id_aluno
        WHERE u.id_usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_aluno_logado]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

// Converte as habilidades de JSON para uma string separada por vírgulas para o input
$habilidades_para_exibir = '';
if (!empty($aluno['habilidades'])) {
    $habilidades_array = json_decode($aluno['habilidades'], true);
    if (is_array($habilidades_array)) {
        $habilidades_para_exibir = implode(', ', $habilidades_array);
    }
}


$pageTitle = "Meu Perfil | Aluno";
include '../includes/header.php';
?>

<main class="profile-page">
    <div class="container">
        <h2>Meu Perfil</h2>
        <p>Mantenha suas informações atualizadas para aumentar suas chances de encontrar o estágio ideal.</p>

        <!-- Banners de feedback -->
        <?php if (!empty($success_message)): ?>
            <div class="feedback-banner success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="feedback-banner error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="perfil.php" method="POST" class="crud-form">
            <h3>Dados Pessoais</h3>
            <div class="form-group-row">
                <div class="form-group">
                    <label for="nome">Nome*</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($aluno['nome'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="sobrenome">Sobrenome*</label>
                    <input type="text" id="sobrenome" name="sobrenome" value="<?php echo htmlspecialchars($aluno['sobrenome'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="form-group-row">
                <div class="form-group">
                    <label for="data_nasc">Data de Nascimento*</label>
                    <input type="date" id="data_nasc" name="data_nasc" value="<?php echo htmlspecialchars($aluno['data_nasc'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="cpf">CPF (não editável)</label>
                    <input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars($aluno['cpf'] ?? ''); ?>" disabled>
                </div>
            </div>

            <h3>Dados de Contato e Acesso</h3>
            <div class="form-group">
                <label for="email">Email*</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($aluno['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="link_perfil_externo">Seu site</label>
                <input type="url" id="link_perfil_externo" name="link_perfil_externo" placeholder="https://seusite.com" value="<?php echo htmlspecialchars($aluno['link_perfil_externo'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="tel">Telefone</label>
                <input type="tel" id="tel" name="tel" value="<?php echo htmlspecialchars($aluno['tel'] ?? ''); ?>">
            </div>

            <h3>Dados Acadêmicos e Profissionais</h3>
            <div class="form-group-row">
                <div class="form-group">
                    <label for="matricula">Matrícula*</label>
                    <input type="text" id="matricula" name="matricula" value="<?php echo htmlspecialchars($aluno['matricula'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <input type="text" id="status" name="status_display" value="<?php echo htmlspecialchars($aluno['status'] ?? 'Incompleto'); ?>" disabled>
                </div>
            </div>
            <div class="form-group">
                <label for="habilidades">Habilidades (separe por vírgula)</label>
                <textarea id="habilidades" name="habilidades" rows="3" placeholder="Ex: PHP, JavaScript, SQL, Liderança"><?php echo htmlspecialchars($habilidades_para_exibir); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Salvar Alterações</button>
            </div>
        </form>
    </div>
</main>

<?php include '../includes/footer.php'; ?>