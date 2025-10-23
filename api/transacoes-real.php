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
    
    // Parâmetros de filtro
    $limite = intval($_GET['limite'] ?? 20);
    $offset = intval($_GET['offset'] ?? 0);
    $tipo = $_GET['tipo'] ?? null;
    $categoria = $_GET['categoria'] ?? null;
    $data_inicio = $_GET['data_inicio'] ?? null;
    $data_fim = $_GET['data_fim'] ?? null;
    $busca = $_GET['busca'] ?? null;
    $ordenacao = $_GET['ordenacao'] ?? 'data_desc'; // data_desc, data_asc, valor_desc, valor_asc, categoria
    
    // Construir query base
    $where_conditions = ["clientid = :clientid"];
    $params = ['clientid' => $clientid];
    
    // Aplicar filtros
    if ($tipo && in_array($tipo, ['Receita', 'Despesa'])) {
        $where_conditions[] = "type = :tipo";
        $params['tipo'] = $tipo;
    }
    
    if ($categoria) {
        $where_conditions[] = "category = :categoria";
        $params['categoria'] = $categoria;
    }
    
    if ($data_inicio) {
        $where_conditions[] = "data_movimentacao >= :data_inicio";
        $params['data_inicio'] = $data_inicio;
    }
    
    if ($data_fim) {
        $where_conditions[] = "data_movimentacao <= :data_fim";
        $params['data_fim'] = $data_fim;
    }
    
    if ($busca) {
        $where_conditions[] = "(observation ILIKE :busca OR category ILIKE :busca)";
        $params['busca'] = '%' . $busca . '%';
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Definir ordenação
    $order_by = "data_movimentacao DESC, updated_at DESC";
    switch ($ordenacao) {
        case 'data_asc':
            $order_by = "data_movimentacao ASC, updated_at ASC";
            break;
        case 'valor_desc':
            $order_by = "valor_movimentacao DESC";
            break;
        case 'valor_asc':
            $order_by = "valor_movimentacao ASC";
            break;
        case 'categoria':
            $order_by = "category ASC, data_movimentacao DESC";
            break;
    }
    
    // 1. Buscar transações com filtros
    $sql = "
        SELECT 
            id,
            data_movimentacao,
            valor_movimentacao,
            type,
            category,
            observation,
            updated_at
        FROM movimentacoes
        WHERE $where_clause
        ORDER BY $order_by
        LIMIT :limite OFFSET :offset
    ";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Contar total de transações (para paginação)
    $count_sql = "SELECT COUNT(*) as total FROM movimentacoes WHERE $where_clause";
    $stmt = $pdo->prepare($count_sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $total_transacoes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 3. Calcular totais por filtros aplicados
    $totais_sql = "
        SELECT 
            COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) as total_receitas,
            COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as total_despesas,
            COUNT(*) as total_registros
        FROM movimentacoes
        WHERE $where_clause
    ";
    
    $stmt = $pdo->prepare($totais_sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $totais = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 4. Buscar categorias disponíveis para filtros
    $stmt = $pdo->prepare("
        SELECT DISTINCT category, COUNT(*) as quantidade
        FROM movimentacoes
        WHERE clientid = :clientid AND category IS NOT NULL
        GROUP BY category
        ORDER BY category ASC
    ");
    $stmt->execute(['clientid' => $clientid]);
    $categorias_disponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 5. Estatísticas por período (se filtros de data aplicados)
    $estatisticas_periodo = null;
    if ($data_inicio && $data_fim) {
        $stmt = $pdo->prepare("
            SELECT 
                DATE_TRUNC('day', data_movimentacao) as dia,
                COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) as receitas_dia,
                COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as despesas_dia,
                COUNT(*) as transacoes_dia
            FROM movimentacoes
            WHERE clientid = :clientid
                AND data_movimentacao >= :data_inicio
                AND data_movimentacao <= :data_fim
            GROUP BY DATE_TRUNC('day', data_movimentacao)
            ORDER BY dia ASC
        ");
        $stmt->execute([
            'clientid' => $clientid,
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim
        ]);
        $estatisticas_periodo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Preparar resposta
    $response['success'] = true;
    $response['message'] = 'Transações carregadas com sucesso.';
    $response['transacoes'] = array_map(function($item) {
        return [
            'id' => $item['id'],
            'data' => $item['data_movimentacao'],
            'valor' => floatval($item['valor_movimentacao']),
            'tipo' => $item['type'],
            'categoria' => $item['category'],
            'observacao' => $item['observation'],
            'formatted_valor' => 'R$ ' . number_format($item['valor_movimentacao'], 2, ',', '.'),
            'formatted_data' => date('d/m/Y', strtotime($item['data_movimentacao'])),
            'updated_at' => $item['updated_at']
        ];
    }, $transacoes);
    
    $response['paginacao'] = [
        'total_registros' => intval($total_transacoes),
        'limite' => $limite,
        'offset' => $offset,
        'pagina_atual' => floor($offset / $limite) + 1,
        'total_paginas' => ceil($total_transacoes / $limite),
        'tem_proxima' => ($offset + $limite) < $total_transacoes,
        'tem_anterior' => $offset > 0
    ];
    
    $response['totais'] = [
        'receitas' => floatval($totais['total_receitas']),
        'despesas' => floatval($totais['total_despesas']),
        'saldo_liquido' => floatval($totais['total_receitas']) - floatval($totais['total_despesas']),
        'total_registros' => intval($totais['total_registros']),
        'formatted_receitas' => 'R$ ' . number_format($totais['total_receitas'], 2, ',', '.'),
        'formatted_despesas' => 'R$ ' . number_format($totais['total_despesas'], 2, ',', '.'),
        'formatted_saldo' => 'R$ ' . number_format($totais['total_receitas'] - $totais['total_despesas'], 2, ',', '.')
    ];
    
    $response['filtros'] = [
        'categorias_disponiveis' => array_map(function($item) {
            return [
                'categoria' => $item['category'],
                'quantidade' => intval($item['quantidade'])
            ];
        }, $categorias_disponiveis),
        'tipos_disponiveis' => ['Receita', 'Despesa'],
        'ordenacoes_disponiveis' => [
            'data_desc' => 'Data (mais recente)',
            'data_asc' => 'Data (mais antiga)',
            'valor_desc' => 'Valor (maior)',
            'valor_asc' => 'Valor (menor)',
            'categoria' => 'Categoria'
        ]
    ];
    
    if ($estatisticas_periodo) {
        $response['estatisticas_periodo'] = array_map(function($item) {
            return [
                'dia' => $item['dia'],
                'receitas' => floatval($item['receitas_dia']),
                'despesas' => floatval($item['despesas_dia']),
                'saldo_dia' => floatval($item['receitas_dia']) - floatval($item['despesas_dia']),
                'transacoes' => intval($item['transacoes_dia'])
            ];
        }, $estatisticas_periodo);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Erro no banco de dados: ' . $e->getMessage();
    error_log('Transacoes PHP Error: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Erro interno do servidor: ' . $e->getMessage();
    error_log('Transacoes PHP Error: ' . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
