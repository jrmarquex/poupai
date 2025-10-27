<?php
/**
 * Autenticação Admin - Validação segura de acesso administrativo
 * Apenas para: wxdmarques@gmail.com
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Credenciais admin (seguras no backend)
define('ADMIN_EMAIL', 'wxdmarques@gmail.com');
define('ADMIN_PASSWORD_HASH', password_hash('Hyundaimax@@9', PASSWORD_BCRYPT));

// WhatsApp do admin principal
define('ADMIN_WHATSAPP', '5511997245501');
define('ADMIN_EMAIL_PRINCIPAL', 'frmarques.oli@gmail.com');

$response = ['success' => false, 'message' => ''];

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    if ($method !== 'POST') {
        http_response_code(405);
        $response['message'] = 'Método não permitido';
        echo json_encode($response);
        exit();
    }

    // Receber credenciais
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        $input = $_POST;
    }

    $email = $input['email'] ?? '';
    $senha = $input['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        http_response_code(400);
        $response['message'] = 'Email e senha são obrigatórios';
        echo json_encode($response);
        exit();
    }

    // Verificar credenciais
    if ($email === ADMIN_EMAIL && password_verify($senha, ADMIN_PASSWORD_HASH)) {
        // Gerar token admin
        $token = base64_encode(json_encode([
            'email' => ADMIN_EMAIL,
            'role' => 'super_admin',
            'iat' => time(),
            'exp' => time() + (4 * 60 * 60) // 4 horas
        ]));

        $response['success'] = true;
        $response['message'] = 'Autenticação bem-sucedida';
        $response['admin'] = [
            'email' => ADMIN_EMAIL,
            'role' => 'super_admin',
            'permissions' => ['webhook', 'logs', 'users', 'all']
        ];
        $response['token'] = $token;

        http_response_code(200);
        echo json_encode($response);
        exit();
    }

    // Credenciais inválidas
    http_response_code(401);
    $response['message'] = 'Credenciais inválidas';
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Erro no servidor';
    echo json_encode($response);
}
?>
