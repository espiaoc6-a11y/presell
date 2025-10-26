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

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Receber dados do usuário
$input = json_decode(file_get_contents('php://input'), true);
$userData = $input['userData'] ?? [];

// URL de redirecionamento final
$redirectUrl = "https://v9.consultaoficialbr.com/?utm_content=034v9";

// Parâmetros de tracking (opcional)
$trackingParams = [];

// Adicionar parâmetros da URL original se existirem
if (isset($_SESSION['quiz_session']['urlParams'])) {
    $trackingParams = array_merge($trackingParams, $_SESSION['quiz_session']['urlParams']);
}

// Adicionar parâmetros de analytics
$trackingParams['utm_source'] = 'consultar_facil';
$trackingParams['utm_medium'] = 'quiz_funnel';
$trackingParams['utm_campaign'] = 'valores_receber';
$trackingParams['session_id'] = $_SESSION['quiz_session']['sessionId'] ?? '';

// Construir URL final com parâmetros
if (!empty($trackingParams)) {
    $redirectUrl .= (strpos($redirectUrl, '?') === false ? '?' : '&') . http_build_query($trackingParams);
}

$response = [
    'success' => true,
    'redirectUrl' => $redirectUrl,
    'sessionData' => [
        'sessionId' => $_SESSION['quiz_session']['sessionId'] ?? '',
        'totalTime' => time() - ($_SESSION['quiz_session']['startTime'] ?? time()),
        'stepsCompleted' => 3
    ]
];

// Limpar sessão após redirecionamento
unset($_SESSION['quiz_session']);

echo json_encode($response);
?>