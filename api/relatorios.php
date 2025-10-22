<?php
// api/relatorios.php - API PHP para relatórios
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

// Função para buscar relatórios
function buscarRelatorios($pdo, $whatsapp, $periodo = 'mes') {
    try {
        // Buscar cliente
        $stmt = $pdo->prepare("SELECT clientid, nome FROM clientes WHERE whatsapp = ?");
        $stmt->execute([$whatsapp]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            return ['success' => false, 'message' => 'Cliente não encontrado'];
        }
        
        // Definir período
        $data_inicio = '';
        switch($periodo) {
            case 'semana':
                $data_inicio = date('Y-m-d', strtotime('-7 days'));
                break;
            case 'mes':
                $data_inicio = date('Y-m-01');
                break;
            case 'trimestre':
                $data_inicio = date('Y-m-01', strtotime('-3 months'));
                break;
            case 'ano':
                $data_inicio = date('Y-01-01');
                break;
        }
        
        // Resumo do período
        $sql_resumo = "SELECT 
            COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) as total_receitas,
            COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as total_despesas,
            COUNT(*) as total_transacoes
            FROM movimentacoes 
            WHERE clientid = ?";
        
        $params = [$cliente['clientid']];
        
        if ($data_inicio) {
            $sql_resumo .= " AND data_movimentacao >= ?";
            $params[] = $data_inicio;
        }
        
        $stmt = $pdo->prepare($sql_resumo);
        $stmt->execute($params);
        $resumo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Transações do período
        $sql_transacoes = "SELECT * FROM movimentacoes WHERE clientid = ?";
        $params_transacoes = [$cliente['clientid']];
        
        if ($data_inicio) {
            $sql_transacoes .= " AND data_movimentacao >= ?";
            $params_transacoes[] = $data_inicio;
        }
        
        $sql_transacoes .= " ORDER BY data_movimentacao DESC";
        
        $stmt = $pdo->prepare($sql_transacoes);
        $stmt->execute($params_transacoes);
        $transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Evolução por dia (últimos 7 dias)
        $sql_evolucao = "SELECT 
            DATE(data_movimentacao) as data,
            COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) as receitas,
            COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as despesas
            FROM movimentacoes 
            WHERE clientid = ? AND data_movimentacao >= ?
            GROUP BY DATE(data_movimentacao)
            ORDER BY data DESC";
        
        $stmt = $pdo->prepare($sql_evolucao);
        $stmt->execute([$cliente['clientid'], date('Y-m-d', strtotime('-7 days'))]);
        $evolucao = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'cliente' => $cliente,
            'resumo' => [
                'totalReceitas' => floatval($resumo['total_receitas']),
                'totalDespesas' => floatval($resumo['total_despesas']),
                'saldoLiquido' => floatval($resumo['total_receitas'] - $resumo['total_despesas']),
                'totalTransacoes' => intval($resumo['total_transacoes'])
            ],
            'transacoes' => $transacoes,
            'evolucao' => $evolucao
        ];
        
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Erro ao buscar relatórios: ' . $e->getMessage()];
    }
}

// Processar requisição
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $whatsapp = $_GET['whatsapp'] ?? '5511997245501';
    $periodo = $_GET['periodo'] ?? 'mes';
    
    $resultado = buscarRelatorios($pdo, $whatsapp, $periodo);
    echo json_encode($resultado);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?>
