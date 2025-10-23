<?php
// Teste simples para verificar se PHP funciona
echo "PHP está funcionando!<br>";
echo "Data atual: " . date('d/m/Y H:i:s') . "<br>";
echo "Versão PHP: " . phpversion() . "<br>";

// Teste de conexão com banco (se configurado)
try {
    require_once 'config/database.php';
    $teste = testarConexao();
    
    if ($teste['sucesso']) {
        echo "✅ Banco de dados conectado!<br>";
        echo "Data do banco: " . $teste['data_atual'] . "<br>";
    } else {
        echo "❌ Erro no banco: " . $teste['mensagem'] . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}
?>
