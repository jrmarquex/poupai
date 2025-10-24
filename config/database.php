<?php
// config/database.php - Configurações do banco de dados

// Configurações do Supabase PostgreSQL - CREDENCIAIS REAIS CONFIGURADAS
define('DB_HOST', 'db.beqpplqfamcpyuzgzhcs.supabase.co'); // Host real do Supabase
define('DB_PORT', '5432');
define('DB_NAME', 'postgres');
define('DB_USER', 'postgres');
define('DB_PASS', 'Hyundaimax@@9'); // Senha real do Supabase

// Para desenvolvimento local, você pode usar estas configurações:
// define('DB_HOST', 'localhost');
// define('DB_PORT', '5432');
// define('DB_NAME', 'pouppi');
// define('DB_USER', 'postgres');
// define('DB_PASS', 'sua_senha_local');

// Função principal para conectar ao banco (compatível com APIs existentes)
function getDbConnection() {
    try {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
        // Configurar timezone
        $pdo->exec("SET timezone = 'America/Sao_Paulo'");
        
        return $pdo;
    } catch(PDOException $e) {
        error_log("Erro de conexão com o banco de dados: " . $e->getMessage());
        throw new Exception("Não foi possível conectar ao banco de dados: " . $e->getMessage());
    }
}

// Função alternativa para compatibilidade
function conectarBanco() {
    return getDbConnection();
}

// Função para testar conexão
function testarConexao() {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->query("SELECT NOW() as data_atual");
        $resultado = $stmt->fetch();
        return [
            'sucesso' => true,
            'mensagem' => 'Conexão estabelecida com sucesso',
            'data_atual' => $resultado['data_atual']
        ];
    } catch (Exception $e) {
        return [
            'sucesso' => false,
            'mensagem' => $e->getMessage()
        ];
    }
}

// Função para obter informações do banco
function obterInfoBanco() {
    try {
        $pdo = getDbConnection();
        
        // Verificar tabelas existentes
        $stmt = $pdo->query("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public' 
            ORDER BY table_name
        ");
        $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Contar registros em cada tabela
        $contadores = [];
        foreach ($tabelas as $tabela) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabela");
            $contadores[$tabela] = $stmt->fetch()['total'];
        }
        
        return [
            'sucesso' => true,
            'tabelas' => $tabelas,
            'contadores' => $contadores
        ];
    } catch (Exception $e) {
        return [
            'sucesso' => false,
            'mensagem' => $e->getMessage()
        ];
    }
}

// Função para retornar resposta JSON
function respostaJson($dados, $codigo = 200) {
    http_response_code($codigo);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}
?>
