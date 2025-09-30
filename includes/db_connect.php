<?php

$host = 'ep-sparkling-rice-acd11mw2-pooler.sa-east-1.aws.neon.tech';
// getenv('DB_HOST');
// if (empty($host)) { $host = 'seu_host_local'; }

$dbname = 'neondb';
// getenv('DB_NAME');
// if (empty($dbname)) { $dbname = 'seu_db_local'; }

$user = 'neondb_owner';
// getenv('DB_USER');
// if (empty($user)) { $user = 'seu_user_local'; }

$pass = 'npg_QAFNbn3OuL8p';
// getenv('DB_PASSWORD');
// if (empty($pass)) { $pass = 'sua_senha_local'; }

$port = '5432';
// getenv('DB_PORT');
// if (empty($port)) { $port = '5432'; }

$endpoint_id = 'ep-sparkling-rice-acd11mw2-pooler';
// getenv('DB_ENDPOINT_ID');

// $pass_com_endpoint = "endpoint=$endpoint_id;" . $pass;

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;";

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
    echo($e);
    die("Serviço indisponível temporariamente. Tente novamente mais tarde.");
}
?>