<?php
// api/teste-conexao.php - Arquivo para testar conexão com banco de dados
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$response = ['success' => false, 'message' => ''];

try {
    // Testar conexão com banco
    $teste = testarConexao();
    
    if ($teste['sucesso']) {
        $response['success'] = true;
        $response['message'] = 'Conexão com banco de dados OK';
        $response['data_atual'] = $teste['data_atual'];
        
        // Obter informações do banco
        $info = obterInfoBanco();
        if ($info['sucesso']) {
            $response['banco_info'] = $info;
        }
        
        // Testar consulta básica nas tabelas
        $pdo = getDbConnection();
        
        // Verificar se tabelas existem
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes");
        $total_clientes = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM movimentacoes");
        $total_movimentacoes = $stmt->fetch()['total'];
        
        $response['tabelas'] = [
            'clientes' => $total_clientes,
            'movimentacoes' => $total_movimentacoes
        ];
        
        // Testar consulta de exemplo
        $stmt = $pdo->query("
            SELECT 
                c.nome, 
                c.whatsapp,
                COUNT(m.id) as total_movimentacoes,
                COALESCE(SUM(m.valor_movimentacao), 0) as valor_total
            FROM clientes c
            LEFT JOIN movimentacoes m ON c.clientid = m.clientid
            GROUP BY c.clientid, c.nome, c.whatsapp
            ORDER BY valor_total DESC
            LIMIT 5
        ");
        $exemplo_dados = $stmt->fetchAll();
        
        $response['exemplo_consulta'] = $exemplo_dados;
        
    } else {
        $response['message'] = 'Erro na conexão: ' . $teste['mensagem'];
    }
    
} catch (Exception $e) {
    $response['message'] = 'Erro: ' . $e->getMessage();
    error_log('Teste conexão error: ' . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
