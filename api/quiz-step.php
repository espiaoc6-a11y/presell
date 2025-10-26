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

// Receber dados da requisição
$input = json_decode(file_get_contents('php://input'), true);
$stepData = $input['stepData'] ?? [];
$sessionId = $stepData['sessionId'] ?? '';
$currentStep = $stepData['currentStep'] ?? 1;

// Verificar se a sessão existe
if (!isset($_SESSION['quiz_session']) || $_SESSION['quiz_session']['sessionId'] !== $sessionId) {
    echo json_encode(['success' => false, 'error' => 'Sessão inválida']);
    exit;
}

// Determinar próximo passo baseado no passo atual
$nextStep = intval($currentStep) + 1;

// Configurar resposta baseada no próximo passo
switch ($nextStep) {
    case 2:
        // Etapa do CAPTCHA
        $response = [
            'success' => true,
            'step' => [
                'id' => 2,
                'type' => 'captcha',
                'title' => '🤖 Confirme que você não é um robô',
                'description' => '',
                'question' => 'Digite o número <span class="highlight-number">47</span> abaixo:',
                'input' => [
                    'type' => 'number',
                    'placeholder' => 'Digite aqui',
                    'label' => '',
                    'required' => true,
                    'validation' => [
                        'min' => 1,
                        'max' => 999
                    ]
                ],
                'button' => [
                    'text' => 'VERIFICAR',
                    'action' => 'submit'
                ],
                'icon' => 'shield',
                'iconColor' => 'blue',
                'helpText' => ''
            ]
        ];
        
        // Salvar dados do CAPTCHA se fornecidos
        if (isset($stepData['captcha'])) {
            $_SESSION['quiz_session']['captcha'] = $stepData['captcha'];
        }
        break;
        
    case 3:
        // Verificar se o CAPTCHA está correto
        $captchaInput = $stepData['captcha'] ?? null;
        $expectedCaptcha = 47; // Número fixo do CAPTCHA
        
        if ($captchaInput !== $expectedCaptcha) {
            echo json_encode(['success' => false, 'error' => 'Código de verificação incorreto']);
            exit;
        }
        
        // Etapa de loading/redirecionamento
        $response = [
            'success' => true,
            'step' => [
                'id' => 3,
                'type' => 'loading',
                'title' => '✅ Verificação Aprovada!',
                'description' => 'Você será redirecionado para o ambiente seguro da consulta.',
                'loadingText' => 'Aguarde alguns segundos...',
                'progressSteps' => [
                    '🔒 Verificação de segurança concluída',
                    '📊 Acessando base de dados do Banco Central',
                    '🔄 Transferindo parâmetros de rastreamento',
                    '✅ Redirecionando para consulta...'
                ],
                'autoRedirect' => true,
                'redirectDelay' => 3000,
                'icon' => 'loader',
                'iconColor' => 'green'
            ],
            'userData' => [
                'verified' => true,
                'captchaValid' => true,
                'verificationTime' => time()
            ]
        ];
        
        // Atualizar dados da sessão
        $_SESSION['quiz_session']['currentStep'] = 3;
        $_SESSION['quiz_session']['verified'] = true;
        $_SESSION['quiz_session']['verificationTime'] = time();
        break;
        
    default:
        $response = ['success' => false, 'error' => 'Etapa inválida'];
        break;
}

echo json_encode($response);
?>