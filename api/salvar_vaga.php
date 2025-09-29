<?php
session_start();
header('Content-Type: application/json'); // Define que a resposta será em JSON

// Segurança: Verifica se o usuário é um aluno logado
if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.']);
    exit;
}

require '../includes/db_connect.php';

// Pega o ID da vaga enviado pelo JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$id_vaga = $data['id_vaga'] ?? null;

if (!$id_vaga) {
    echo json_encode(['status' => 'error', 'message' => 'ID da vaga não fornecido.']);
    exit;
}

$id_aluno = $_SESSION['user_id'];

try {
    // Verifica se o aluno já salvou esta vaga
    $stmt_check = $pdo->prepare("SELECT * FROM aluno_vagas_salvas WHERE id_aluno = ? AND id_vaga = ?");
    $stmt_check->execute([$id_aluno, $id_vaga]);

    if ($stmt_check->fetch()) {
        // Se já salvou, remove (DELETE)
        $stmt_delete = $pdo->prepare("DELETE FROM aluno_vagas_salvas WHERE id_aluno = ? AND id_vaga = ?");
        $stmt_delete->execute([$id_aluno, $id_vaga]);
        echo json_encode(['status' => 'success', 'action' => 'removed']);
    } else {
        // Se não salvou, adiciona (INSERT)
        $stmt_insert = $pdo->prepare("INSERT INTO aluno_vagas_salvas (id_aluno, id_vaga) VALUES (?, ?)");
        $stmt_insert->execute([$id_aluno, $id_vaga]);
        echo json_encode(['status' => 'success', 'action' => 'saved']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro no banco de dados.']);
    // error_log($e->getMessage()); // Para depuração
}