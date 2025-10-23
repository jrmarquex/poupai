<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';

$response = ['success' => false, 'message' => ''];

try {
    $pdo = getDbConnection();
    
    // Verificar autenticação do usuário
    session_start();
    $clientid = null;
    
    if (isset($_SESSION['clientid'])) {
        $clientid = $_SESSION['clientid'];
    } elseif (isset($_GET['clientid'])) {
        $clientid = $_GET['clientid'];
    } elseif (isset($_GET['whatsapp'])) {
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
    
    // Parâmetros do relatório
    $periodo = $_GET['periodo'] ?? 'mes'; // semana, mes, trimestre, ano, personalizado
    $data_inicio = $_GET['data_inicio'] ?? null;
    $data_fim = $_GET['data_fim'] ?? null;
    $tipo_lancamento = $_GET['tipo_lancamento'] ?? 'todos'; // todos, receitas, despesas
    $categoria = $_GET['categoria'] ?? null;
    $formato_grafico = $_GET['formato_grafico'] ?? 'linha'; // linha, pizza, barras
    
    // Definir período baseado no parâmetro
    $where_periodo = "";
    $params_periodo = ['clientid' => $clientid];
    
    if ($periodo === 'personalizado' && $data_inicio && $data_fim) {
        $where_periodo = "AND data_movimentacao >= :data_inicio AND data_movimentacao <= :data_fim";
        $params_periodo['data_inicio'] = $data_inicio;
        $params_periodo['data_fim'] = $data_fim;
    } else {
        switch ($periodo) {
            case 'hoje':
                $where_periodo = "AND data_movimentacao = CURRENT_DATE";
                break;
            case 'semana':
                $where_periodo = "AND data_movimentacao >= CURRENT_DATE - INTERVAL '7 days'";
                break;
            case 'mes':
                $where_periodo = "AND EXTRACT(MONTH FROM data_movimentacao) = EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM data_movimentacao) = EXTRACT(YEAR FROM CURRENT_DATE)";
                break;
            case 'trimestre':
                $where_periodo = "AND data_movimentacao >= DATE_TRUNC('quarter', CURRENT_DATE) AND data_movimentacao < DATE_TRUNC('quarter', CURRENT_DATE) + INTERVAL '3 months'";
                break;
            case 'ano':
                $where_periodo = "AND EXTRACT(YEAR FROM data_movimentacao) = EXTRACT(YEAR FROM CURRENT_DATE)";
                break;
        }
    }
    
    // Filtros adicionais
    $where_filtros = "";
    if ($tipo_lancamento !== 'todos') {
        if ($tipo_lancamento === 'receitas') {
            $where_filtros .= " AND type = 'Receita'";
        } elseif ($tipo_lancamento === 'despesas') {
            $where_filtros .= " AND type = 'Despesa'";
        }
    }
    
    if ($categoria) {
        $where_filtros .= " AND category = :categoria";
        $params_periodo['categoria'] = $categoria;
    }
    
    $where_completo = "clientid = :clientid " . $where_periodo . $where_filtros;
    
    // 1. RESUMO GERAL DO PERÍODO
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) as total_receitas,
            COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as total_despesas,
            COUNT(*) as total_transacoes,
            COUNT(CASE WHEN type = 'Receita' THEN 1 END) as quantidade_receitas,
            COUNT(CASE WHEN type = 'Despesa' THEN 1 END) as quantidade_despesas,
            AVG(CASE WHEN type = 'Receita' THEN valor_movimentacao END) as media_receitas,
            AVG(CASE WHEN type = 'Despesa' THEN valor_movimentacao END) as media_despesas,
            MAX(valor_movimentacao) as maior_valor,
            MIN(valor_movimentacao) as menor_valor
        FROM movimentacoes
        WHERE $where_completo
    ");
    $stmt->execute($params_periodo);
    $resumo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $saldo_liquido = $resumo['total_receitas'] - $resumo['total_despesas'];
    
    // 2. DESPESAS POR CATEGORIA
    $stmt = $pdo->prepare("
        SELECT 
            category,
            SUM(valor_movimentacao) as total_valor,
            COUNT(*) as quantidade,
            AVG(valor_movimentacao) as media_valor,
            MAX(valor_movimentacao) as maior_valor,
            MIN(valor_movimentacao) as menor_valor
        FROM movimentacoes
        WHERE $where_completo AND type = 'Despesa'
        GROUP BY category
        ORDER BY total_valor DESC
    ");
    $stmt->execute($params_periodo);
    $despesas_por_categoria = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. RECEITAS POR CATEGORIA
    $stmt = $pdo->prepare("
        SELECT 
            category,
            SUM(valor_movimentacao) as total_valor,
            COUNT(*) as quantidade,
            AVG(valor_movimentacao) as media_valor,
            MAX(valor_movimentacao) as maior_valor,
            MIN(valor_movimentacao) as menor_valor
        FROM movimentacoes
        WHERE $where_completo AND type = 'Receita'
        GROUP BY category
        ORDER BY total_valor DESC
    ");
    $stmt->execute($params_periodo);
    $receitas_por_categoria = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. TENDÊNCIA TEMPORAL (para gráfico de linha)
    $agrupamento_temporal = "DATE_TRUNC('day', data_movimentacao)";
    $intervalo_temporal = "dia";
    
    if ($periodo === 'ano') {
        $agrupamento_temporal = "DATE_TRUNC('month', data_movimentacao)";
        $intervalo_temporal = "mes";
    } elseif ($periodo === 'trimestre') {
        $agrupamento_temporal = "DATE_TRUNC('week', data_movimentacao)";
        $intervalo_temporal = "semana";
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            $agrupamento_temporal as periodo,
            COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) as receitas_periodo,
            COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as despesas_periodo,
            COUNT(*) as total_transacoes_periodo
        FROM movimentacoes
        WHERE $where_completo
        GROUP BY $agrupamento_temporal
        ORDER BY periodo ASC
    ");
    $stmt->execute($params_periodo);
    $tendencia_temporal = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 5. COMPARAÇÃO COM PERÍODO ANTERIOR
    $comparacao_anterior = null;
    if ($periodo === 'mes') {
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) as receitas_anterior,
                COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as despesas_anterior,
                COUNT(*) as total_transacoes_anterior
            FROM movimentacoes
            WHERE clientid = :clientid
                AND EXTRACT(MONTH FROM data_movimentacao) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month')
                AND EXTRACT(YEAR FROM data_movimentacao) = EXTRACT(YEAR FROM CURRENT_DATE - INTERVAL '1 month')
                $where_filtros
        ");
        $stmt->execute($params_periodo);
        $comparacao_anterior = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // 6. TOP 10 MAIORES TRANSAÇÕES DO PERÍODO
    $stmt = $pdo->prepare("
        SELECT 
            id,
            data_movimentacao,
            valor_movimentacao,
            type,
            category,
            observation
        FROM movimentacoes
        WHERE $where_completo
        ORDER BY valor_movimentacao DESC
        LIMIT 10
    ");
    $stmt->execute($params_periodo);
    $maiores_transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 7. ANÁLISE DE FREQUÊNCIA (dias com mais transações)
    $stmt = $pdo->prepare("
        SELECT 
            DATE_TRUNC('day', data_movimentacao) as dia,
            COUNT(*) as quantidade_transacoes,
            SUM(valor_movimentacao) as valor_total_dia,
            SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END) as receitas_dia,
            SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END) as despesas_dia
        FROM movimentacoes
        WHERE $where_completo
        GROUP BY DATE_TRUNC('day', data_movimentacao)
        ORDER BY quantidade_transacoes DESC
        LIMIT 10
    ");
    $stmt->execute($params_periodo);
    $dias_mais_ativos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Preparar resposta completa
    $response['success'] = true;
    $response['message'] = 'Relatório gerado com sucesso.';
    
    $response['resumo'] = [
        'periodo' => $periodo,
        'data_inicio' => $data_inicio,
        'data_fim' => $data_fim,
        'total_receitas' => floatval($resumo['total_receitas']),
        'total_despesas' => floatval($resumo['total_despesas']),
        'saldo_liquido' => floatval($saldo_liquido),
        'total_transacoes' => intval($resumo['total_transacoes']),
        'quantidade_receitas' => intval($resumo['quantidade_receitas']),
        'quantidade_despesas' => intval($resumo['quantidade_despesas']),
        'media_receitas' => floatval($resumo['media_receitas'] ?? 0),
        'media_despesas' => floatval($resumo['media_despesas'] ?? 0),
        'maior_valor' => floatval($resumo['maior_valor'] ?? 0),
        'menor_valor' => floatval($resumo['menor_valor'] ?? 0),
        'formatted_receitas' => 'R$ ' . number_format($resumo['total_receitas'], 2, ',', '.'),
        'formatted_despesas' => 'R$ ' . number_format($resumo['total_despesas'], 2, ',', '.'),
        'formatted_saldo' => 'R$ ' . number_format($saldo_liquido, 2, ',', '.')
    ];
    
    $response['despesas_por_categoria'] = array_map(function($item) {
        return [
            'categoria' => $item['category'],
            'total_valor' => floatval($item['total_valor']),
            'quantidade' => intval($item['quantidade']),
            'media_valor' => floatval($item['media_valor']),
            'maior_valor' => floatval($item['maior_valor']),
            'menor_valor' => floatval($item['menor_valor']),
            'formatted_total' => 'R$ ' . number_format($item['total_valor'], 2, ',', '.'),
            'formatted_media' => 'R$ ' . number_format($item['media_valor'], 2, ',', '.')
        ];
    }, $despesas_por_categoria);
    
    $response['receitas_por_categoria'] = array_map(function($item) {
        return [
            'categoria' => $item['category'],
            'total_valor' => floatval($item['total_valor']),
            'quantidade' => intval($item['quantidade']),
            'media_valor' => floatval($item['media_valor']),
            'maior_valor' => floatval($item['maior_valor']),
            'menor_valor' => floatval($item['menor_valor']),
            'formatted_total' => 'R$ ' . number_format($item['total_valor'], 2, ',', '.'),
            'formatted_media' => 'R$ ' . number_format($item['media_valor'], 2, ',', '.')
        ];
    }, $receitas_por_categoria);
    
    $response['tendencia_temporal'] = [
        'intervalo' => $intervalo_temporal,
        'dados' => array_map(function($item) {
            return [
                'periodo' => $item['periodo'],
                'receitas' => floatval($item['receitas_periodo']),
                'despesas' => floatval($item['despesas_periodo']),
                'saldo_periodo' => floatval($item['receitas_periodo']) - floatval($item['despesas_periodo']),
                'total_transacoes' => intval($item['total_transacoes_periodo'])
            ];
        }, $tendencia_temporal)
    ];
    
    if ($comparacao_anterior) {
        $variacao_receitas = 0;
        $variacao_despesas = 0;
        
        if ($comparacao_anterior['receitas_anterior'] > 0) {
            $variacao_receitas = round((($resumo['total_receitas'] - $comparacao_anterior['receitas_anterior']) / $comparacao_anterior['receitas_anterior']) * 100, 1);
        }
        
        if ($comparacao_anterior['despesas_anterior'] > 0) {
            $variacao_despesas = round((($resumo['total_despesas'] - $comparacao_anterior['despesas_anterior']) / $comparacao_anterior['despesas_anterior']) * 100, 1);
        }
        
        $response['comparacao_anterior'] = [
            'receitas_anterior' => floatval($comparacao_anterior['receitas_anterior']),
            'despesas_anterior' => floatval($comparacao_anterior['despesas_anterior']),
            'saldo_anterior' => floatval($comparacao_anterior['receitas_anterior']) - floatval($comparacao_anterior['despesas_anterior']),
            'variacao_receitas' => $variacao_receitas,
            'variacao_despesas' => $variacao_despesas,
            'formatted_receitas_anterior' => 'R$ ' . number_format($comparacao_anterior['receitas_anterior'], 2, ',', '.'),
            'formatted_despesas_anterior' => 'R$ ' . number_format($comparacao_anterior['despesas_anterior'], 2, ',', '.')
        ];
    }
    
    $response['maiores_transacoes'] = array_map(function($item) {
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
    }, $maiores_transacoes);
    
    $response['dias_mais_ativos'] = array_map(function($item) {
        return [
            'dia' => $item['dia'],
            'quantidade_transacoes' => intval($item['quantidade_transacoes']),
            'valor_total_dia' => floatval($item['valor_total_dia']),
            'receitas_dia' => floatval($item['receitas_dia']),
            'despesas_dia' => floatval($item['despesas_dia']),
            'saldo_dia' => floatval($item['receitas_dia']) - floatval($item['despesas_dia']),
            'formatted_data' => date('d/m/Y', strtotime($item['dia'])),
            'formatted_valor_total' => 'R$ ' . number_format($item['valor_total_dia'], 2, ',', '.')
        ];
    }, $dias_mais_ativos);
    
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Erro no banco de dados: ' . $e->getMessage();
    error_log('Relatorios PHP Error: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Erro interno do servidor: ' . $e->getMessage();
    error_log('Relatorios PHP Error: ' . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
