<?php
session_start();

$feedback_message = '';
$feedback_type = ''; // 'success' ou 'error'

$apiUrl = "https://notification-api-ztqe.onrender.com/api/contact";

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
            // 1. Preparar os dados para o formato que o Zod espera (JSON)
            $dados = [
                "name" => $nome,
                "email" => $email_remetente,
                "message" => $mensagem
            ];

            $payload = json_encode($dados);

            // 2. Inicializar o cURL
            $ch = curl_init($apiUrl);

            // 3. Configurar as opções da requisição
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retornar a resposta em vez de imprimir
            curl_setopt($ch, CURLOPT_POST, true);           // Método POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload); // O JSON
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',           // Avisar que é JSON
                'Content-Length: ' . strlen($payload)
            ]);

            // Timeout de 20s (importante para o Render Free Tier)
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);

            // 4. Executar
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            
            curl_close($ch);

            // 5. Verificar o resultado
            if ($httpCode === 200) {
                // Sucesso! A API retornou 200 OK
                $feedback_message = "Sua mensagem foi enviada com sucesso! Entraremos em contato em breve.";
                $feedback_type = 'success';
            } else {
                // Erro na API (pode ser validação do Zod ou erro de servidor)
                // Logar para debug (não mostre o erro técnico para o usuário final)
                error_log("Erro na API de Notificação. Código: $httpCode. Erro cURL: $curlError. Resposta: $response");
                
                $feedback_message = "Ocorreu um erro ao processar sua mensagem. Tente novamente mais tarde.";
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