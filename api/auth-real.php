<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';

$response = ['success' => false, 'message' => ''];

try {
    $pdo = getDbConnection();
    
    // Verificar método da requisição
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
    
    if ($method === 'POST') {
        // LOGIN
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        $usuario = $input['usuario'] ?? '';
        $senha = $input['senha'] ?? '';
        
        if (empty($usuario) || empty($senha)) {
            http_response_code(400);
            $response['message'] = 'Usuário e senha são obrigatórios.';
            echo json_encode($response);
            exit();
        }
        
        // Buscar usuário por WhatsApp ou email
        $stmt = $pdo->prepare("
            SELECT 
                clientid, 
                nome, 
                email, 
                whatsapp, 
                senha_hash, 
                primeiro_acesso,
                status,
                tentativas_login,
                bloqueado_ate
            FROM clientes 
            WHERE (whatsapp = :usuario OR email = :usuario) 
                AND status = true
        ");
        $stmt->execute(['usuario' => $usuario]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            http_response_code(401);
            $response['message'] = 'Usuário não encontrado ou inativo.';
            echo json_encode($response);
            exit();
        }
        
        // Verificar se conta está bloqueada
        if ($cliente['bloqueado_ate'] && strtotime($cliente['bloqueado_ate']) > time()) {
            http_response_code(423);
            $response['message'] = 'Conta temporariamente bloqueada. Tente novamente mais tarde.';
            echo json_encode($response);
            exit();
        }
        
        // Verificar senha
        if (!password_verify($senha, $cliente['senha_hash'])) {
            // Incrementar tentativas de login
            $tentativas = $cliente['tentativas_login'] + 1;
            $bloqueado_ate = null;
            
            // Bloquear após 5 tentativas por 30 minutos
            if ($tentativas >= 5) {
                $bloqueado_ate = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            }
            
            $stmt = $pdo->prepare("
                UPDATE clientes 
                SET tentativas_login = :tentativas, bloqueado_ate = :bloqueado_ate
                WHERE clientid = :clientid
            ");
            $stmt->execute([
                'tentativas' => $tentativas,
                'bloqueado_ate' => $bloqueado_ate,
                'clientid' => $cliente['clientid']
            ]);
            
            http_response_code(401);
            $response['message'] = 'Senha incorreta.';
            echo json_encode($response);
            exit();
        }
        
        // Login bem-sucedido - resetar tentativas e atualizar último login
        $stmt = $pdo->prepare("
            UPDATE clientes 
            SET tentativas_login = 0, 
                bloqueado_ate = NULL, 
                ultimo_login = NOW()
            WHERE clientid = :clientid
        ");
        $stmt->execute(['clientid' => $cliente['clientid']]);
        
        // Iniciar sessão
        session_start();
        $_SESSION['clientid'] = $cliente['clientid'];
        $_SESSION['nome'] = $cliente['nome'];
        $_SESSION['email'] = $cliente['email'];
        $_SESSION['whatsapp'] = $cliente['whatsapp'];
        $_SESSION['primeiro_acesso'] = $cliente['primeiro_acesso'];
        
        // Gerar token JWT simples (opcional)
        $token_data = [
            'clientid' => $cliente['clientid'],
            'nome' => $cliente['nome'],
            'email' => $cliente['email'],
            'whatsapp' => $cliente['whatsapp'],
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 horas
        ];
        
        $token = base64_encode(json_encode($token_data));
        
        $response['success'] = true;
        $response['message'] = 'Login realizado com sucesso.';
        $response['cliente'] = [
            'clientid' => $cliente['clientid'],
            'nome' => $cliente['nome'],
            'email' => $cliente['email'],
            'whatsapp' => $cliente['whatsapp'],
            'primeiro_acesso' => $cliente['primeiro_acesso']
        ];
        $response['token'] = $token;
        
    } elseif ($method === 'GET') {
        // VERIFICAR STATUS DA SESSÃO
        session_start();
        
        if (!isset($_SESSION['clientid'])) {
            http_response_code(401);
            $response['message'] = 'Usuário não autenticado.';
            echo json_encode($response);
            exit();
        }
        
        // Buscar dados atualizados do cliente
        $stmt = $pdo->prepare("
            SELECT 
                clientid, 
                nome, 
                email, 
                whatsapp, 
                primeiro_acesso,
                status,
                ultimo_login
            FROM clientes 
            WHERE clientid = :clientid
        ");
        $stmt->execute(['clientid' => $_SESSION['clientid']]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            session_destroy();
            http_response_code(404);
            $response['message'] = 'Cliente não encontrado.';
            echo json_encode($response);
            exit();
        }
        
        $response['success'] = true;
        $response['message'] = 'Sessão válida.';
        $response['cliente'] = [
            'clientid' => $cliente['clientid'],
            'nome' => $cliente['nome'],
            'email' => $cliente['email'],
            'whatsapp' => $cliente['whatsapp'],
            'primeiro_acesso' => $cliente['primeiro_acesso'],
            'ultimo_login' => $cliente['ultimo_login']
        ];
        
    } elseif ($method === 'DELETE') {
        // LOGOUT
        session_start();
        
        if (isset($_SESSION['clientid'])) {
            // Log do logout (opcional)
            error_log("Logout realizado para cliente: " . $_SESSION['clientid']);
        }
        
        session_destroy();
        
        $response['success'] = true;
        $response['message'] = 'Logout realizado com sucesso.';
        
    } else {
        http_response_code(405);
        $response['message'] = 'Método não permitido.';
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Erro no banco de dados: ' . $e->getMessage();
    error_log('Auth PHP Error: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Erro interno do servidor: ' . $e->getMessage();
    error_log('Auth PHP Error: ' . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
