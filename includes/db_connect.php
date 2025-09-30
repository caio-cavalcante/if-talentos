<?php

$host = getenv('DB_HOST');
if (empty($host)) { $host = 'seu_host_local'; }

$dbname = getenv('DB_NAME');
if (empty($dbname)) { $dbname = 'seu_db_local'; }

$user = getenv('DB_USER');
if (empty($user)) { $user = 'seu_user_local'; }

$pass = getenv('DB_PASSWORD');
if (empty($pass)) { $pass = 'sua_senha_local'; }

$port = getenv('DB_PORT');
if (empty($port)) { $port = '5432'; }

$endpoint_id = getenv('DB_ENDPOINT_ID');

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

$options = [
    // Se ainda der erro de constante, o Dockerfile está falhando em carregar o PDO.
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, 
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options); 
} catch (PDOException $e) {
    // Registra o erro no log do Render
    error_log("FALHA CRÍTICA NA CONEXÃO COM O BD: " . $e->getMessage());
    // Mostra a mensagem amigável ao usuário
    http_response_code(503);
    die("Serviço indisponível temporariamente. Tente novamente mais tarde.");
}
?>