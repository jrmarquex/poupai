<?php
/**
 * Webhook Lastlink - Recebe eventos de vendas e cadastra clientes automaticamente
 * URL: https://pouppi.com/api/webhook-lastlink.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';

// Log de requisições (para debug)
function logWebhook($data, $status = 'info') {
    $logFile = __DIR__ . '/../logs/webhook-lastlink.log';
    $logDir = dirname($logFile);

    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$status}] " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

$response = ['success' => false, 'message' => ''];

try {
    // Verificar método
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    // Receber dados do webhook
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        $data = $_POST;
    }

    // Log da requisição recebida
    logWebhook([
        'method' => $method,
        'headers' => getallheaders(),
        'data' => $data
    ], 'received');

    // Validar dados mínimos necessários
    if (empty($data)) {
        http_response_code(400);
        $response['message'] = 'Nenhum dado recebido';
        echo json_encode($response);
        logWebhook(['error' => 'Nenhum dado recebido'], 'error');
        exit();
    }

    // Conectar ao banco
    $pdo = getDbConnection();

    // Extrair informações do cliente da Lastlink
    // Formato esperado: pode variar conforme a Lastlink envia
    $evento = $data['event'] ?? $data['type'] ?? '';
    $clienteData = $data['customer'] ?? $data['cliente'] ?? $data;

    // Campos esperados
    $nome = $clienteData['name'] ?? $clienteData['nome'] ?? '';
    $email = $clienteData['email'] ?? '';
    $telefone = $clienteData['phone'] ?? $clienteData['telefone'] ?? $clienteData['whatsapp'] ?? '';
    $produtoComprado = $data['product'] ?? $data['produto'] ?? 'Assinatura Pouppi';
    $valorPago = $data['amount'] ?? $data['valor'] ?? 0;
    $statusPagamento = $data['status'] ?? 'approved';
    $transactionId = $data['transaction_id'] ?? $data['id'] ?? uniqid('lastlink_');

    // Limpar telefone (remover caracteres não numéricos)
    $whatsapp = preg_replace('/[^0-9]/', '', $telefone);

    // Validar dados mínimos
    if (empty($whatsapp) || strlen($whatsapp) < 10) {
        http_response_code(400);
        $response['message'] = 'WhatsApp inválido ou ausente';
        echo json_encode($response);
        logWebhook(['error' => 'WhatsApp inválido', 'whatsapp' => $whatsapp], 'error');
        exit();
    }

    // Garantir que WhatsApp tenha 11 dígitos (adicionar DDD se necessário)
    if (strlen($whatsapp) === 10) {
        $whatsapp = '11' . $whatsapp; // Adiciona DDD padrão 11 (SP)
    }

    // Verificar se é evento de pagamento aprovado
    $isPagamentoAprovado = in_array(strtolower($statusPagamento), ['approved', 'paid', 'success', 'completed', 'aprovado', 'pago']);

    if (!$isPagamentoAprovado) {
        // Log mas não cria cliente se pagamento não aprovado
        logWebhook([
            'info' => 'Pagamento não aprovado ainda',
            'status' => $statusPagamento,
            'whatsapp' => $whatsapp
        ], 'info');

        $response['success'] = true;
        $response['message'] = 'Webhook recebido - aguardando aprovação de pagamento';
        echo json_encode($response);
        exit();
    }

    // Verificar se cliente já existe
    $stmt = $pdo->prepare("
        SELECT clientid, nome, email, status, primeiro_acesso
        FROM clientes
        WHERE whatsapp = :whatsapp
    ");
    $stmt->execute(['whatsapp' => $whatsapp]);
    $clienteExistente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($clienteExistente) {
        // Cliente já existe - atualizar status e informações
        $stmt = $pdo->prepare("
            UPDATE clientes
            SET status = TRUE,
                nome = COALESCE(NULLIF(:nome, ''), nome),
                email = COALESCE(NULLIF(:email, ''), email),
                updated_at = NOW()
            WHERE whatsapp = :whatsapp
            RETURNING clientid
        ");

        $stmt->execute([
            'whatsapp' => $whatsapp,
            'nome' => $nome,
            'email' => $email
        ]);

        $clienteId = $stmt->fetch()['clientid'];

        logWebhook([
            'action' => 'updated',
            'clientid' => $clienteId,
            'whatsapp' => $whatsapp,
            'transaction_id' => $transactionId
        ], 'success');

        $response['success'] = true;
        $response['message'] = 'Cliente atualizado com sucesso';
        $response['cliente'] = [
            'clientid' => $clienteId,
            'whatsapp' => $whatsapp,
            'action' => 'updated'
        ];

    } else {
        // Criar novo cliente
        $stmt = $pdo->prepare("
            INSERT INTO clientes (
                whatsapp,
                nome,
                email,
                status,
                primeiro_acesso,
                origem,
                created_at,
                updated_at
            ) VALUES (
                :whatsapp,
                :nome,
                :email,
                TRUE,
                TRUE,
                'lastlink',
                NOW(),
                NOW()
            )
            RETURNING clientid
        ");

        $stmt->execute([
            'whatsapp' => $whatsapp,
            'nome' => $nome ?: 'Cliente Pouppi',
            'email' => $email ?: null
        ]);

        $clienteId = $stmt->fetch()['clientid'];

        logWebhook([
            'action' => 'created',
            'clientid' => $clienteId,
            'whatsapp' => $whatsapp,
            'nome' => $nome,
            'email' => $email,
            'transaction_id' => $transactionId
        ], 'success');

        $response['success'] = true;
        $response['message'] = 'Cliente cadastrado com sucesso';
        $response['cliente'] = [
            'clientid' => $clienteId,
            'whatsapp' => $whatsapp,
            'action' => 'created'
        ];
    }

    // Registrar transação (opcional - se você tiver uma tabela de transações)
    try {
        $stmt = $pdo->prepare("
            INSERT INTO webhook_logs (
                cliente_id,
                webhook_source,
                event_type,
                transaction_id,
                data_received,
                processed_at
            ) VALUES (
                :cliente_id,
                'lastlink',
                :event_type,
                :transaction_id,
                :data_received,
                NOW()
            )
        ");

        $stmt->execute([
            'cliente_id' => $clienteId,
            'event_type' => $evento ?: 'payment',
            'transaction_id' => $transactionId,
            'data_received' => json_encode($data)
        ]);
    } catch (Exception $e) {
        // Tabela webhook_logs pode não existir ainda - não é crítico
        logWebhook(['warning' => 'Não foi possível registrar em webhook_logs', 'error' => $e->getMessage()], 'warning');
    }

    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Erro ao processar webhook: ' . $e->getMessage();
    echo json_encode($response);

    logWebhook([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], 'error');
}
?>
