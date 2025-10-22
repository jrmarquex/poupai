<?php
// api/transacoes.php - API PHP para transações
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Configurações do banco de dados
$host = 'db.xxxxxxxxxxxx.supabase.co';
$port = '5432';
$dbname = 'postgres';
$user = 'postgres';
$password = 'sua_senha_aqui';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com banco']);
    exit;
}

// Função para buscar transações
function buscarTransacoes($pdo, $whatsapp, $filtros = []) {
    try {
        // Buscar cliente
        $stmt = $pdo->prepare("SELECT clientid FROM clientes WHERE whatsapp = ?");
        $stmt->execute([$whatsapp]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            return ['success' => false, 'message' => 'Cliente não encontrado'];
        }
        
        // Query base
        $sql = "SELECT * FROM movimentacoes WHERE clientid = ?";
        $params = [$cliente['clientid']];
        
        // Aplicar filtros
        if (!empty($filtros['tipo']) && $filtros['tipo'] !== 'all') {
            $sql .= " AND type = ?";
            $params[] = $filtros['tipo'];
        }
        
        if (!empty($filtros['categoria']) && $filtros['categoria'] !== 'all') {
            $sql .= " AND category = ?";
            $params[] = $filtros['categoria'];
        }
        
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND data_movimentacao >= ?";
            $params[] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND data_movimentacao <= ?";
            $params[] = $filtros['data_fim'];
        }
        
        $sql .= " ORDER BY data_movimentacao DESC";
        
        if (!empty($filtros['limite'])) {
            $sql .= " LIMIT ?";
            $params[] = intval($filtros['limite']);
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'transacoes' => $transacoes,
            'total' => count($transacoes)
        ];
        
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Erro ao buscar transações: ' . $e->getMessage()];
    }
}

// Processar requisição
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $whatsapp = $_GET['whatsapp'] ?? '5511997245501';
    $filtros = [
        'tipo' => $_GET['tipo'] ?? 'all',
        'categoria' => $_GET['categoria'] ?? 'all',
        'data_inicio' => $_GET['data_inicio'] ?? '',
        'data_fim' => $_GET['data_fim'] ?? '',
        'limite' => $_GET['limite'] ?? 50
    ];
    
    $resultado = buscarTransacoes($pdo, $whatsapp, $filtros);
    echo json_encode($resultado);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?>
