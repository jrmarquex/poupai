<?php
// teste-supabase-conexao.php - Teste de conexão com Supabase
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config/database.php';

$response = ['success' => false, 'message' => ''];

try {
    echo "🔍 Testando conexão com Supabase...\n\n";
    
    // Testar conexão básica
    $teste = testarConexao();
    
    if ($teste['sucesso']) {
        echo "✅ Conexão estabelecida com sucesso!\n";
        echo "📅 Data atual do banco: " . $teste['data_atual'] . "\n\n";
        
        // Obter informações do banco
        $info = obterInfoBanco();
        if ($info['sucesso']) {
            echo "📊 Informações do banco:\n";
            echo "📋 Tabelas encontradas: " . implode(', ', $info['tabelas']) . "\n\n";
            
            echo "📈 Contadores de registros:\n";
            foreach ($info['contadores'] as $tabela => $total) {
                echo "   - $tabela: $total registros\n";
            }
            echo "\n";
            
            // Testar consulta específica para cliente admin
            $pdo = getDbConnection();
            
            echo "👤 Testando consulta do cliente admin (5511997245501):\n";
            $stmt = $pdo->prepare("SELECT clientid, nome, email, whatsapp FROM clientes WHERE whatsapp = :whatsapp");
            $stmt->execute(['whatsapp' => '5511997245501']);
            $cliente = $stmt->fetch();
            
            if ($cliente) {
                echo "✅ Cliente encontrado:\n";
                echo "   - ID: " . $cliente['clientid'] . "\n";
                echo "   - Nome: " . $cliente['nome'] . "\n";
                echo "   - Email: " . $cliente['email'] . "\n";
                echo "   - WhatsApp: " . $cliente['whatsapp'] . "\n\n";
                
                // Testar consulta de movimentações
                echo "💰 Testando consulta de movimentações:\n";
                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM movimentacoes WHERE clientid = :clientid");
                $stmt->execute(['clientid' => $cliente['clientid']]);
                $total_movimentacoes = $stmt->fetch()['total'];
                
                echo "✅ Total de movimentações: $total_movimentacoes\n\n";
                
                // Testar resumo financeiro
                echo "📊 Testando resumo financeiro:\n";
                $stmt = $pdo->prepare("
                    SELECT 
                        COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) as total_receitas,
                        COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as total_despesas
                    FROM movimentacoes
                    WHERE clientid = :clientid
                ");
                $stmt->execute(['clientid' => $cliente['clientid']]);
                $resumo = $stmt->fetch();
                
                echo "✅ Resumo financeiro:\n";
                echo "   - Receitas: R$ " . number_format($resumo['total_receitas'], 2, ',', '.') . "\n";
                echo "   - Despesas: R$ " . number_format($resumo['total_despesas'], 2, ',', '.') . "\n";
                echo "   - Saldo: R$ " . number_format($resumo['total_receitas'] - $resumo['total_despesas'], 2, ',', '.') . "\n\n";
                
                $response['success'] = true;
                $response['message'] = 'Conexão com Supabase funcionando perfeitamente!';
                $response['cliente'] = $cliente;
                $response['total_movimentacoes'] = $total_movimentacoes;
                $response['resumo'] = $resumo;
                
            } else {
                echo "❌ Cliente admin não encontrado!\n";
                $response['message'] = 'Cliente admin não encontrado no banco';
            }
            
        } else {
            echo "❌ Erro ao obter informações do banco: " . $info['mensagem'] . "\n";
            $response['message'] = $info['mensagem'];
        }
        
    } else {
        echo "❌ Erro na conexão: " . $teste['mensagem'] . "\n";
        $response['message'] = $teste['mensagem'];
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    $response['message'] = 'Erro: ' . $e->getMessage();
    error_log('Teste Supabase error: ' . $e->getMessage());
}

echo "\n" . json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
