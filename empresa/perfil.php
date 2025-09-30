<?php
// 1. INICIALIZAÇÃO E SEGURANÇA
session_start();
require '../includes/db_connect.php';

// Redireciona se não for um usuário do tipo empresa logado
if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] != 2) {
    header("Location: ../login.php");
    exit;
}

$id_empresa_logada = $_SESSION['user_id'];
$errors = [];
$success_message = '';

// FUNÇÃO HELPER PARA REDIMENSIONAR IMAGEM
function resizeImage($sourcePath, $destinationPath, $maxWidth, $maxHeight) {
    list($width, $height, $type) = getimagesize($sourcePath);
    $aspectRatio = $width / $height;

    if ($width > $maxWidth || $height > $maxHeight) {
        if ($width / $maxWidth > $height / $maxHeight) {
            $newWidth = $maxWidth;
            $newHeight = $maxWidth / $aspectRatio;
        } else {
            $newHeight = $maxHeight;
            $newWidth = $maxHeight * $aspectRatio;
        }
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }

    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            break;
        default:
            return false;
    }

    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($thumb, $destinationPath, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumb, $destinationPath, 9);
            break;
    }
    
    imagedestroy($source);
    imagedestroy($thumb);
    return true;
}

// 2. LÓGICA DE ATUALIZAÇÃO DO PERFIL (QUANDO O FORMULÁRIO É ENVIADO)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coleta dos dados do formulário
    // Tabela 'usuario'
    $nome_responsavel = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $tel = trim($_POST['tel']);
    $link_perfil_externo = trim($_POST['link_perfil_externo']);
    $logo_path = $_POST['current_logo_path'];

    // Tabela 'empresa'
    $nome_fantasia = trim($_POST['nome_fant']);
    $descricao = trim($_POST['descricao']);
    $endereco = trim($_POST['endereco_completo']);
    $area_atuacao = trim($_POST['area_atuacao']);

    // Validação de campos obrigatórios
    if (empty($nome_responsavel) || empty($email) || empty($nome_fantasia) || empty($descricao) || empty($endereco) || empty($area_atuacao)) {
        $errors[] = "Todos os campos com * são obrigatórios para completar o perfil.";
    }

    // Validação de e-mail único (excluindo o próprio usuário)
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = ? AND id_usuario != ?");
    $stmt->execute([$email, $id_empresa_logada]);
    if ($stmt->fetch()) {
        $errors[] = "Este e-mail já está em uso por outra conta.";
    }

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5 MB

        if (in_array($_FILES['logo']['type'], $allowed_types) && $_FILES['logo']['size'] <= $max_size) {
            $upload_dir = '../uploads/logos/';
            $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $unique_filename = 'logo_' . $id_empresa_logada . '_' . time() . '.' . $file_extension;
            $target_path = $upload_dir . $unique_filename;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_path)) {
                // Redimensiona a imagem
                if (resizeImage($target_path, $target_path, 300, 300)) {
                    // Se um logo antigo existir, deleta-o para não acumular lixo no servidor
                    if (!empty($logo_path) && file_exists('..' . $logo_path)) {
                        unlink('..' . $logo_path);
                    }
                    $logo_path = '/uploads/logos/' . $unique_filename; // Salva o caminho relativo à raiz
                } else {
                    $errors[] = "Erro ao redimensionar a imagem.";
                    unlink($target_path); // Deleta o arquivo se o redimensionamento falhar
                }
            } else {
                $errors[] = "Erro ao mover o arquivo para o servidor.";
            }
        } else {
            $errors[] = "Arquivo inválido. Apenas imagens JPG ou PNG de até 5MB são permitidas.";
        }
    }

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            // Atualiza a tabela 'usuario'
            $sql_usuario = "UPDATE usuario SET nome = ?, email = ?, tel = ?, link_perfil_externo = ? WHERE id_usuario = ?";
            $stmt_usuario = $pdo->prepare($sql_usuario);
            $stmt_usuario->execute([$nome_responsavel, $email, $tel, $link_perfil_externo, $id_empresa_logada]);

            // Atualiza a tabela 'empresa' e define o status como 'Pendente de Verificação'
            $sql_empresa = "UPDATE empresa SET nome_fant = ?, descricao = ?, endereco_completo = ?, area_atuacao = ?, status = 'Pendente de Verificação', logo_path = ? WHERE id_empresa = ?";
            $stmt_empresa = $pdo->prepare($sql_empresa);
            $stmt_empresa->execute([$nome_fantasia, $descricao, $endereco, $area_atuacao, $logo_path, $id_empresa_logada]);

            $pdo->commit();
            $success_message = "Perfil da empresa atualizado com sucesso! Agora você tem acesso a todas as funcionalidades.";
            // Atualiza o nome na sessão, caso tenha sido alterado
            $_SESSION['user_nome'] = $nome_responsavel;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Ocorreu um erro ao atualizar o perfil. Tente novamente.";
            error_log($e->getMessage()); // Para depuração
        }
    }
}

// 3. BUSCAR DADOS ATUAIS DA EMPRESA PARA PREENCHER O FORMULÁRIO
$sql = "SELECT 
            u.nome, u.email, u.tel, u.link_perfil_externo,
            e.nome_fant, e.cnpj, e.status, e.descricao, e.endereco_completo, e.area_atuacao
        FROM usuario u
        JOIN empresa e ON u.id_usuario = e.id_empresa
        WHERE u.id_usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_empresa_logada]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = "Meu Perfil | Empresa";
include '../includes/header.php';
?>

<main class="profile-page">
    <div class="container">
        <h2>Perfil da Empresa</h2>
        <p>Complete e mantenha as informações da sua empresa atualizadas para atrair os melhores talentos.</p>

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

        <form action="perfil.php" method="POST" class="crud-form" enctype="multipart/form-data">
            <h3>Informações da Empresa</h3>
            <div class="form-group">
                <label for="logo">Logo da Empresa</label>
                <?php if (!empty($empresa['logo_path']) && file_exists('..' . $empresa['logo_path'])): ?>
                    <div class="current-logo">
                        <img src="<?php echo '..' . htmlspecialchars($empresa['logo_path']); ?>" alt="Logo atual da empresa">
                    </div>
                <?php endif; ?>
                <input type="file" id="logo" name="logo">
                <input type="hidden" name="current_logo_path" value="<?php echo htmlspecialchars($empresa['logo_path'] ?? ''); ?>">
            </div>
            <small>Envie uma imagem JPG ou PNG de até 5MB. A imagem será redimensionada para 300x300 pixels.</small>
            <div class="form-group">
                <label for="nome_fant">Nome Fantasia*</label>
                <input type="text" id="nome_fant" name="nome_fant" value="<?php echo htmlspecialchars($empresa['nome_fant'] ?? ''); ?>" required>
            </div>
            <div class="form-group-row">
                <div class="form-group">
                    <label for="cnpj">CNPJ (não editável)</label>
                    <input type="text" id="cnpj" name="cnpj" value="<?php echo htmlspecialchars($empresa['cnpj'] ?? ''); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="area_atuacao">Área de Atuação*</label>
                    <input type="text" id="area_atuacao" name="area_atuacao" placeholder="Ex: Tecnologia da Informação" value="<?php echo htmlspecialchars($empresa['area_atuacao'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição da Empresa*</label>
                <textarea id="descricao" name="descricao" rows="5" placeholder="Fale sobre a cultura, missão e o que sua empresa faz." required><?php echo htmlspecialchars($empresa['descricao'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="endereco_completo">Endereço Completo*</label>
                <input type="text" id="endereco_completo" name="endereco_completo" value="<?php echo htmlspecialchars($empresa['endereco_completo'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="link_perfil_externo">Site da Empresa</label>
                <input type="url" id="link_perfil_externo" name="link_perfil_externo" placeholder="https://suaempresa.com" value="<?php echo htmlspecialchars($empresa['link_perfil_externo'] ?? ''); ?>">
            </div>

            <h3>Dados do Responsável</h3>
            <div class="form-group">
                <label for="nome">Nome do Responsável*</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($empresa['nome'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail de Contato*</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($empresa['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group-row">
                <div class="form-group">
                    <label for="tel">Telefone</label>
                    <input type="tel" id="tel" name="tel" value="<?php echo htmlspecialchars($empresa['tel'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="status">Status do Cadastro</label>
                    <input type="text" id="status" name="status" value="<?php echo htmlspecialchars($empresa['status'] ?? 'Incompleto'); ?>" disabled>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Salvar Perfil</button>
            </div>
        </form>
    </div>
</main>

<?php include '../includes/footer.php'; ?>