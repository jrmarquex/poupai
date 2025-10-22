<?php
// config/database.php - Configurações do banco de dados

// Configurações do Supabase
define('DB_HOST', 'db.xxxxxxxxxxxx.supabase.co');
define('DB_PORT', '5432');
define('DB_NAME', 'postgres');
define('DB_USER', 'postgres');
define('DB_PASS', 'sua_senha_aqui');

// Função para conectar ao banco
function conectarBanco() {
    try {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        error_log("Erro de conexão: " . $e->getMessage());
        return false;
    }
}

// Função para retornar resposta JSON
function respostaJson($dados, $codigo = 200) {
    http_response_code($codigo);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    echo json_encode($dados);
    exit;
}
?>
