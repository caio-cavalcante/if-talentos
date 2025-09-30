<?php
session_start();

$feedback_message = '';
$feedback_type = ''; // 'success' ou 'error'

// Lógica de envio do formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nome = trim($_POST['nome']);
        $email_remetente = trim($_POST['email']);
        $mensagem = trim($_POST['mensagem']);

        // Validação simples
        if (empty($nome) || empty($email_remetente) || empty($mensagem)) {
            $feedback_message = "Por favor, preencha todos os campos.";
            $feedback_type = 'error';
        } elseif (!filter_var($email_remetente, FILTER_VALIDATE_EMAIL)) {
            $feedback_message = "Por favor, insira um endereço de e-mail válido.";
            $feedback_type = 'error';
        } else {
            // --- Configuração do E-mail ---
            $email_destinatario = "20231BSIFSA0005@ifba.edu.br";
            $assunto = "Nova Mensagem do Formulário de Contato - IF Talentos";

            // Monta o corpo do e-mail
            $corpo_email = "Você recebeu uma nova mensagem através do site IF Talentos.\n\n";
            $corpo_email .= "Nome: " . htmlspecialchars($nome) . "\n";
            $corpo_email .= "E-mail: " . htmlspecialchars($email_remetente) . "\n";
            $corpo_email .= "Mensagem:\n" . htmlspecialchars($mensagem) . "\n";

            // Monta os cabeçalhos do e-mail (essencial para evitar spam)
            $headers = "From: " . htmlspecialchars($nome) . " <" . htmlspecialchars($email_remetente) . ">\r\n";
            $headers .= "Reply-To: " . htmlspecialchars($email_remetente) . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            // Tenta enviar o e-mail
            if (mail($email_destinatario, $assunto, $corpo_email, $headers)) {
                $feedback_message = "Sua mensagem foi enviada com sucesso! Entraremos em contato em breve.";
                $feedback_type = 'success';
            } else {
                $feedback_message = "Ocorreu um erro ao tentar enviar sua mensagem. Por favor, tente novamente mais tarde ou contate-nos por outro meio.";
                $feedback_type = 'error';
            }
        }
    } catch (Exception $e) {
        $feedback_message = "Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.";
        $feedback_type = 'error';
        error_log("Erro no formulário de contato: " . $e->getMessage());
        echo ($e);
    }
}

$pageTitle = "Contato";
include 'includes/header.php';
?>

<main class="contact-page">
    <div class="container">
        <h1>Entre em Contato</h1>
        <p>Tem alguma dúvida, sugestão ou feedback? Adoraríamos ouvir você. Preencha o formulário abaixo.</p>

        <!-- Banner de Feedback -->
        <?php if (!empty($feedback_message)): ?>
            <div class="feedback-banner <?php echo $feedback_type; ?>">
                <?php echo htmlspecialchars($feedback_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="contact-form-container">
            <form action="contato.php" method="POST" class="crud-form">
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="mensagem">Mensagem</label>
                    <textarea id="mensagem" name="mensagem" rows="6" required></textarea>
                </div>
                <button type="submit" class="btn">Enviar Mensagem</button>
            </form>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>