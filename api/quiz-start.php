<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar se é uma requisição POST (vinda do JavaScript)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Se acessado diretamente pelo navegador, retornar erro
    if (isset($_SERVER['HTTP_SEC_FETCH_MODE']) && $_SERVER['HTTP_SEC_FETCH_MODE'] === 'navigate') {
        // Requisição direta do navegador - mostrar mensagem de erro
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Método não permitido'
        ]);
    } else {
        // Para outros acessos diretos, retornar erro simples
        http_response_code(405);
        die('Acesso não permitido');
    }
    exit;
}

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Gerar ID único para a sessão
$sessionId = 'quiz_' . time() . '_' . bin2hex(random_bytes(5));

// Dados da primeira etapa (welcome)
$response = [
    'success' => true,
    'sessionId' => $sessionId,
    'step' => [
        'id' => 1,
        'type' => 'welcome',
        'title' => 'Bem-vindo(a) ao Portal de Atendimento!',
        'description' => 'Clique no botão abaixo para verificar se possui Valores Disponíveis.',
        'button' => [
            'text' => 'VERIFICAR VALORES A RECEBER',
            'action' => 'next'
        ],
        'icon' => 'check-circle',
        'iconColor' => 'green'
    ]
];

// Salvar dados iniciais na sessão
$_SESSION['quiz_session'] = [
    'sessionId' => $sessionId,
    'currentStep' => 1,
    'startTime' => time(),
    'urlParams' => json_decode(file_get_contents('php://input'), true)['urlParams'] ?? []
];

echo json_encode($response);
?>