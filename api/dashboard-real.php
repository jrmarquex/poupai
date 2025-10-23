<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';

$response = ['success' => false, 'message' => ''];

try {
    $pdo = getDbConnection();
    
    // Verificar se usuário está logado via sessão ou token
    session_start();
    $clientid = null;
    
    // Método 1: Via sessão (login tradicional)
    if (isset($_SESSION['clientid'])) {
        $clientid = $_SESSION['clientid'];
    }
    
    // Método 2: Via parâmetro GET (para testes)
    if (!$clientid && isset($_GET['clientid'])) {
        $clientid = $_GET['clientid'];
    }
    
    // Método 3: Via WhatsApp (para compatibilidade)
    if (!$clientid && isset($_GET['whatsapp'])) {
        $stmt = $pdo->prepare("SELECT clientid FROM clientes WHERE whatsapp = :whatsapp");
        $stmt->execute(['whatsapp' => $_GET['whatsapp']]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cliente) {
            $clientid = $cliente['clientid'];
        }
    }
    
    if (!$clientid) {
        http_response_code(401);
        $response['message'] = 'Usuário não autenticado.';
        echo json_encode($response);
        exit();
    }
    
    // Buscar dados do cliente
    $stmt = $pdo->prepare("SELECT clientid, nome, email, whatsapp FROM clientes WHERE clientid = :clientid");
    $stmt->execute(['clientid' => $clientid]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        http_response_code(404);
        $response['message'] = 'Cliente não encontrado.';
        echo json_encode($response);
        exit();
    }
    
    // 1. RESUMO FINANCEIRO (Ganhos, Gastos, Saldo Líquido)
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) as total_ganhos,
            COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as total_gastos,
            COUNT(*) as total_transacoes
        FROM movimentacoes
        WHERE clientid = :clientid
    ");
    $stmt->execute(['clientid' => $clientid]);
    $resumo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $saldo_liquido = $resumo['total_ganhos'] - $resumo['total_gastos'];
    
    // 2. VARIAÇÃO PERCENTUAL (comparando com mês anterior)
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) as ganhos_mes_atual,
            COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as gastos_mes_atual
        FROM movimentacoes
        WHERE clientid = :clientid
            AND EXTRACT(MONTH FROM data_movimentacao) = EXTRACT(MONTH FROM CURRENT_DATE)
            AND EXTRACT(YEAR FROM data_movimentacao) = EXTRACT(YEAR FROM CURRENT_DATE)
    ");
    $stmt->execute(['clientid' => $clientid]);
    $mes_atual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) as ganhos_mes_anterior,
            COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as gastos_mes_anterior
        FROM movimentacoes
        WHERE clientid = :clientid
            AND EXTRACT(MONTH FROM data_movimentacao) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month')
            AND EXTRACT(YEAR FROM data_movimentacao) = EXTRACT(YEAR FROM CURRENT_DATE - INTERVAL '1 month')
    ");
    $stmt->execute(['clientid' => $clientid]);
    $mes_anterior = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calcular variações percentuais
    $variacao_ganhos = 0;
    $variacao_gastos = 0;
    
    if ($mes_anterior['ganhos_mes_anterior'] > 0) {
        $variacao_ganhos = round((($mes_atual['ganhos_mes_atual'] - $mes_anterior['ganhos_mes_anterior']) / $mes_anterior['ganhos_mes_anterior']) * 100, 1);
    }
    
    if ($mes_anterior['gastos_mes_anterior'] > 0) {
        $variacao_gastos = round((($mes_atual['gastos_mes_atual'] - $mes_anterior['gastos_mes_anterior']) / $mes_anterior['gastos_mes_anterior']) * 100, 1);
    }
    
    // 3. GASTOS POR CATEGORIA (mês atual)
    $stmt = $pdo->prepare("
        SELECT 
            category,
            SUM(valor_movimentacao) as total_valor,
            COUNT(*) as quantidade
        FROM movimentacoes
        WHERE clientid = :clientid
            AND type = 'Despesa'
            AND EXTRACT(MONTH FROM data_movimentacao) = EXTRACT(MONTH FROM CURRENT_DATE)
            AND EXTRACT(YEAR FROM data_movimentacao) = EXTRACT(YEAR FROM CURRENT_DATE)
        GROUP BY category
        ORDER BY total_valor DESC
    ");
    $stmt->execute(['clientid' => $clientid]);
    $gastos_por_categoria = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. EVOLUÇÃO FINANCEIRA (últimos 7 dias)
    $stmt = $pdo->prepare("
        SELECT 
            DATE(data_movimentacao) as data,
            COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) as receitas_dia,
            COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as despesas_dia,
            COUNT(*) as total_transacoes_dia
        FROM movimentacoes
        WHERE clientid = :clientid
            AND data_movimentacao >= CURRENT_DATE - INTERVAL '7 days'
        GROUP BY DATE(data_movimentacao)
        ORDER BY data ASC
    ");
    $stmt->execute(['clientid' => $clientid]);
    $evolucao_financeira = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 5. ATIVIDADES RECENTES (últimas 10 transações)
    $stmt = $pdo->prepare("
        SELECT 
            id,
            data_movimentacao,
            valor_movimentacao,
            type,
            category,
            observation,
            updated_at
        FROM movimentacoes
        WHERE clientid = :clientid
        ORDER BY data_movimentacao DESC, updated_at DESC
        LIMIT 10
    ");
    $stmt->execute(['clientid' => $clientid]);
    $atividades_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 6. ESTATÍSTICAS ADICIONAIS
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN type = 'Receita' THEN 1 END) as total_receitas,
            COUNT(CASE WHEN type = 'Despesa' THEN 1 END) as total_despesas,
            AVG(CASE WHEN type = 'Receita' THEN valor_movimentacao END) as media_receitas,
            AVG(CASE WHEN type = 'Despesa' THEN valor_movimentacao END) as media_despesas,
            MAX(valor_movimentacao) as maior_valor,
            MIN(valor_movimentacao) as menor_valor
        FROM movimentacoes
        WHERE clientid = :clientid
    ");
    $stmt->execute(['clientid' => $clientid]);
    $estatisticas = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Preparar resposta completa
    $response['success'] = true;
    $response['message'] = 'Dados do dashboard carregados com sucesso.';
    $response['cliente'] = $cliente;
    $response['dashboard'] = [
        'resumo' => [
            'ganhos' => [
                'valor' => floatval($resumo['total_ganhos']),
                'variacao' => $variacao_ganhos,
                'formatted' => 'R$ ' . number_format($resumo['total_ganhos'], 2, ',', '.')
            ],
            'gastos' => [
                'valor' => floatval($resumo['total_gastos']),
                'variacao' => $variacao_gastos,
                'formatted' => 'R$ ' . number_format($resumo['total_gastos'], 2, ',', '.')
            ],
            'saldo_liquido' => [
                'valor' => floatval($saldo_liquido),
                'formatted' => 'R$ ' . number_format($saldo_liquido, 2, ',', '.')
            ],
            'total_transacoes' => intval($resumo['total_transacoes'])
        ],
        'gastos_por_categoria' => array_map(function($item) {
            return [
                'categoria' => $item['category'],
                'valor' => floatval($item['total_valor']),
                'quantidade' => intval($item['quantidade']),
                'formatted' => 'R$ ' . number_format($item['total_valor'], 2, ',', '.')
            ];
        }, $gastos_por_categoria),
        'evolucao_financeira' => array_map(function($item) {
            return [
                'data' => $item['data'],
                'receitas' => floatval($item['receitas_dia']),
                'despesas' => floatval($item['despesas_dia']),
                'saldo_dia' => floatval($item['receitas_dia']) - floatval($item['despesas_dia']),
                'total_transacoes' => intval($item['total_transacoes_dia'])
            ];
        }, $evolucao_financeira),
        'atividades_recentes' => array_map(function($item) {
            return [
                'id' => $item['id'],
                'data' => $item['data_movimentacao'],
                'valor' => floatval($item['valor_movimentacao']),
                'tipo' => $item['type'],
                'categoria' => $item['category'],
                'observacao' => $item['observation'],
                'formatted_valor' => 'R$ ' . number_format($item['valor_movimentacao'], 2, ',', '.'),
                'formatted_data' => date('d/m/Y', strtotime($item['data_movimentacao']))
            ];
        }, $atividades_recentes),
        'estatisticas' => [
            'total_receitas' => intval($estatisticas['total_receitas']),
            'total_despesas' => intval($estatisticas['total_despesas']),
            'media_receitas' => floatval($estatisticas['media_receitas'] ?? 0),
            'media_despesas' => floatval($estatisticas['media_despesas'] ?? 0),
            'maior_valor' => floatval($estatisticas['maior_valor'] ?? 0),
            'menor_valor' => floatval($estatisticas['menor_valor'] ?? 0)
        ]
    ];
    
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Erro no banco de dados: ' . $e->getMessage();
    error_log('Dashboard PHP Error: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Erro interno do servidor: ' . $e->getMessage();
    error_log('Dashboard PHP Error: ' . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
