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
    
    // Verificar se é POST com ação específica
    $action = $_GET['action'] ?? '';
    
    if ($method === 'POST' && $action === 'check-first-access') {
        // VERIFICAR PRIMEIRO ACESSO
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        $whatsapp = $input['whatsapp'] ?? '';
        
        if (empty($whatsapp)) {
            http_response_code(400);
            $response['message'] = 'WhatsApp é obrigatório.';
            echo json_encode($response);
            exit();
        }
        
        // Buscar cliente
        $stmt = $pdo->prepare("
            SELECT clientid, whatsapp, nome, email, primeiro_acesso, status, bloqueado_ate
            FROM clientes 
            WHERE whatsapp = :whatsapp
        ");
        $stmt->execute(['whatsapp' => $whatsapp]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            $response['success'] = true;
            $response['isFirstAccess'] = true;
            $response['message'] = 'Cliente não encontrado - primeiro acesso necessário';
            echo json_encode($response);
            exit();
        }
        
        // Verificar se está bloqueado
        if ($cliente['bloqueado_ate'] && strtotime($cliente['bloqueado_ate']) > time()) {
            http_response_code(423);
            $response['message'] = 'Conta temporariamente bloqueada. Tente novamente mais tarde.';
            echo json_encode($response);
            exit();
        }
        
        $response['success'] = true;
        $response['isFirstAccess'] = $cliente['primeiro_acesso'] || empty($cliente['nome']);
        $response['cliente'] = [
            'clientid' => $cliente['clientid'],
            'whatsapp' => $cliente['whatsapp'],
            'nome' => $cliente['nome'],
            'email' => $cliente['email']
        ];
        echo json_encode($response);
        exit();
        
    } elseif ($method === 'POST' && $action === 'register-first-access') {
        // REGISTRAR PRIMEIRO ACESSO
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        $whatsapp = $input['whatsapp'] ?? '';
        $nome = $input['nome'] ?? '';
        $email = $input['email'] ?? '';
        $senha = $input['senha'] ?? '';
        
        if (empty($whatsapp) || empty($nome) || empty($email) || empty($senha)) {
            http_response_code(400);
            $response['message'] = 'Todos os campos são obrigatórios.';
            echo json_encode($response);
            exit();
        }
        
        if (strlen($senha) < 6) {
            http_response_code(400);
            $response['message'] = 'A senha deve ter pelo menos 6 caracteres.';
            echo json_encode($response);
            exit();
        }
        
        // Hash da senha
        $senhaHash = password_hash($senha, PASSWORD_BCRYPT);

        // Verificar se cliente já existe e validar status
        $stmt = $pdo->prepare("SELECT clientid, status, bloqueado_ate FROM clientes WHERE whatsapp = :whatsapp");
        $stmt->execute(['whatsapp' => $whatsapp]);
        $clienteExistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($clienteExistente) {
            // VALIDAR STATUS DO CLIENTE
            if (!$clienteExistente['status'] || $clienteExistente['status'] === false) {
                http_response_code(403);
                $response['success'] = false;
                $response['message'] = 'Seu acesso não está ativo. Contate nosso suporte para mais informações.';
                echo json_encode($response);
                exit();
            }

            // Verificar se está bloqueado
            if ($clienteExistente['bloqueado_ate'] && strtotime($clienteExistente['bloqueado_ate']) > time()) {
                http_response_code(423);
                $response['success'] = false;
                $response['message'] = 'Conta temporariamente bloqueada. Tente novamente mais tarde.';
                echo json_encode($response);
                exit();
            }

            // Atualizar cliente existente (somente se status = TRUE)
            $stmt = $pdo->prepare("
                UPDATE clientes
                SET nome = :nome,
                    email = :email,
                    senha_hash = :senha_hash,
                    primeiro_acesso = FALSE,
                    ultimo_login = NOW(),
                    tentativas_login = 0,
                    updated_at = NOW()
                WHERE whatsapp = :whatsapp AND status = TRUE
                RETURNING clientid
            ");
            $stmt->execute([
                'nome' => $nome,
                'email' => $email,
                'senha_hash' => $senhaHash,
                'whatsapp' => $whatsapp
            ]);

            $result = $stmt->fetch();
            if (!$result) {
                http_response_code(403);
                $response['success'] = false;
                $response['message'] = 'Não foi possível completar o cadastro. Entre em contato com o suporte.';
                echo json_encode($response);
                exit();
            }
            $clienteId = $result['clientid'];
        } else {
            // NOVO CLIENTE: Não pode criar conta sem estar na base
            http_response_code(403);
            $response['success'] = false;
            $response['message'] = 'WhatsApp não autorizado. Entre em contato com o suporte para liberação de acesso.';
            echo json_encode($response);
            exit();
        }
        
        // Iniciar sessão
        session_start();
        $_SESSION['clientid'] = $clienteId;
        $_SESSION['nome'] = $nome;
        $_SESSION['email'] = $email;
        $_SESSION['whatsapp'] = $whatsapp;
        $_SESSION['primeiro_acesso'] = false;
        
        // Gerar token
        $token_data = [
            'clientid' => $clienteId,
            'whatsapp' => $whatsapp,
            'nome' => $nome,
            'email' => $email,
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60)
        ];
        $token = base64_encode(json_encode($token_data));
        
        $response['success'] = true;
        $response['message'] = 'Conta criada com sucesso.';
        $response['cliente'] = [
            'clientid' => $clienteId,
            'whatsapp' => $whatsapp,
            'nome' => $nome,
            'email' => $email,
            'primeiro_acesso' => false
        ];
        $response['token'] = $token;
        echo json_encode($response);
        exit();
        
    } elseif ($method === 'POST' && $action === 'login' || empty($action)) {
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
