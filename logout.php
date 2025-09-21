<?php
// 1. Inicia ou resume a sessão existente
session_start();

// 2. Limpa todas as variáveis da sessão
// Remove imediatamente os dados como 'user_id', 'user_tipo', etc.
session_unset();

// 3. Destrói a sessão no servidor
// Invalida o ID da sessão
session_destroy();

// 4. Redireciona o usuário para a página de login
// Fornece um feedback visual claro de que a sessão foi encerrada
header("Location: index.php");

// 5. Garante que nenhum código adicional seja executado após o redirecionamento
exit;
?>