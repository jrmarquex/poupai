<?php
// api/dashboard.php - API PHP para dashboard
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

// Função para buscar dados do dashboard
function buscarDadosDashboard($pdo, $whatsapp) {
    try {
        // 1. Buscar dados do cliente
        $stmt = $pdo->prepare("
            SELECT clientid, nome, email, whatsapp 
            FROM clientes 
            WHERE whatsapp = ?
        ");
        $stmt->execute([$whatsapp]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            return ['success' => false, 'message' => 'Cliente não encontrado'];
        }
        
        // 2. Resumo financeiro
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) as total_receitas,
                COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as total_despesas,
                COUNT(*) as total_movimentacoes
            FROM movimentacoes 
            WHERE clientid = ?
        ");
        $stmt->execute([$cliente['clientid']]);
        $resumo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $saldo_liquido = $resumo['total_receitas'] - $resumo['total_despesas'];
        
        // 3. Movimentações recentes
        $stmt = $pdo->prepare("
            SELECT 
                id, type, valor_movimentacao, category, observation, data_movimentacao
            FROM movimentacoes 
            WHERE clientid = ?
            ORDER BY data_movimentacao DESC 
            LIMIT 10
        ");
        $stmt->execute([$cliente['clientid']]);
        $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 4. Gastos por categoria
        $stmt = $pdo->prepare("
            SELECT 
                category,
                COUNT(*) as quantidade,
                SUM(valor_movimentacao) as total_valor
            FROM movimentacoes 
            WHERE clientid = ? AND type = 'Despesa'
            GROUP BY category
            ORDER BY total_valor DESC
        ");
        $stmt->execute([$cliente['clientid']]);
        $gastos_categoria = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'cliente' => $cliente,
            'dashboard' => [
                'ganhos' => [
                    'valor' => floatval($resumo['total_receitas']),
                    'variacao' => 12 // Pode calcular baseado no mês anterior
                ],
                'gastos' => [
                    'valor' => floatval($resumo['total_despesas']),
                    'variacao' => 8
                ],
                'saldoLiquido' => [
                    'valor' => floatval($saldo_liquido)
                ]
            ],
            'movimentacoes' => $movimentacoes,
            'gastosPorCategoria' => $gastos_categoria
        ];
        
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Erro ao buscar dados: ' . $e->getMessage()];
    }
}

// Processar requisição
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $whatsapp = $_GET['whatsapp'] ?? '5511997245501';
    $resultado = buscarDadosDashboard($pdo, $whatsapp);
    echo json_encode($resultado);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?>
