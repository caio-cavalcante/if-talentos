<?php
// Configurações do Banco de Dados Local (Desenvolvimento)
$host = 'aws-1-sa-east-1.pooler.supabase.com';        
$port = '6543';             
$dbname = 'postgres'; 
$user = 'postgres.vhgzwtiwoycnbfbesvoc';         
$password = 'ifbaedubr20231';

// DSN (Data Source Name) para a conexão com PostgreSQL
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

// Opções do PDO para otimização e segurança
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Tenta criar uma nova instância do PDO para estabelecer a conexão
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    // Em caso de falha, encerra o script e exibe uma mensagem de erro genérica.
    error_log("Erro de conexão com o banco de dados: " . $e->getMessage());
    die("Erro: Não foi possível conectar ao banco de dados. Por favor, tente novamente mais tarde.");
}
?>