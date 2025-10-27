<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Deploy - Pouppi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 30px;
            text-align: center;
        }
        .test-item {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #ddd;
        }
        .test-item.success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .test-item.error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .test-item.warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        .test-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 18px;
        }
        .test-result {
            font-family: 'Courier New', monospace;
            background: white;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            overflow-x: auto;
        }
        .icon {
            display: inline-block;
            width: 24px;
            height: 24px;
            margin-right: 10px;
            vertical-align: middle;
        }
        .summary {
            background: #667eea;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        .summary h2 {
            margin-bottom: 10px;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Teste de Deploy - Pouppi.com</h1>

        <?php
        $testes_passou = 0;
        $testes_total = 0;
        $resultados = [];

        // TESTE 1: Verificar PHP
        $testes_total++;
        $php_version = phpversion();
        if (version_compare($php_version, '7.4', '>=')) {
            $testes_passou++;
            $resultados[] = [
                'titulo' => 'Versão do PHP',
                'status' => 'success',
                'mensagem' => "PHP $php_version instalado ✓"
            ];
        } else {
            $resultados[] = [
                'titulo' => 'Versão do PHP',
                'status' => 'error',
                'mensagem' => "PHP $php_version (requer >= 7.4) ✗"
            ];
        }

        // TESTE 2: Verificar extensões necessárias
        $testes_total++;
        $extensoes = ['pdo', 'pdo_pgsql', 'json', 'mbstring'];
        $extensoes_faltando = [];
        foreach ($extensoes as $ext) {
            if (!extension_loaded($ext)) {
                $extensoes_faltando[] = $ext;
            }
        }
        if (empty($extensoes_faltando)) {
            $testes_passou++;
            $resultados[] = [
                'titulo' => 'Extensões PHP',
                'status' => 'success',
                'mensagem' => 'Todas as extensões necessárias instaladas ✓'
            ];
        } else {
            $resultados[] = [
                'titulo' => 'Extensões PHP',
                'status' => 'error',
                'mensagem' => 'Faltam extensões: ' . implode(', ', $extensoes_faltando) . ' ✗'
            ];
        }

        // TESTE 3: Verificar arquivo de configuração
        $testes_total++;
        if (file_exists('config/database.php')) {
            $testes_passou++;
            $resultados[] = [
                'titulo' => 'Arquivo de Configuração',
                'status' => 'success',
                'mensagem' => 'config/database.php encontrado ✓'
            ];
        } else {
            $resultados[] = [
                'titulo' => 'Arquivo de Configuração',
                'status' => 'error',
                'mensagem' => 'config/database.php NÃO encontrado ✗'
            ];
        }

        // TESTE 4: Testar conexão com Supabase
        $testes_total++;
        if (file_exists('config/database.php')) {
            try {
                require_once 'config/database.php';
                $resultado = testarConexao();
                if ($resultado['sucesso']) {
                    $testes_passou++;
                    $resultados[] = [
                        'titulo' => 'Conexão Supabase',
                        'status' => 'success',
                        'mensagem' => 'Conexão estabelecida com sucesso ✓<br>Data/Hora: ' . $resultado['data_atual']
                    ];
                } else {
                    $resultados[] = [
                        'titulo' => 'Conexão Supabase',
                        'status' => 'error',
                        'mensagem' => 'Erro: ' . $resultado['mensagem'] . ' ✗'
                    ];
                }
            } catch (Exception $e) {
                $resultados[] = [
                    'titulo' => 'Conexão Supabase',
                    'status' => 'error',
                    'mensagem' => 'Erro: ' . $e->getMessage() . ' ✗'
                ];
            }
        } else {
            $resultados[] = [
                'titulo' => 'Conexão Supabase',
                'status' => 'warning',
                'mensagem' => 'Não testado (arquivo config ausente)'
            ];
        }

        // TESTE 5: Verificar arquivos API
        $testes_total++;
        $apis = [
            'api/auth-real.php',
            'api/dashboard-real.php',
            'api/transacoes-real.php',
            'api/relatorios-real.php'
        ];
        $apis_faltando = [];
        foreach ($apis as $api) {
            if (!file_exists($api)) {
                $apis_faltando[] = $api;
            }
        }
        if (empty($apis_faltando)) {
            $testes_passou++;
            $resultados[] = [
                'titulo' => 'Arquivos API',
                'status' => 'success',
                'mensagem' => 'Todos os 4 arquivos API encontrados ✓'
            ];
        } else {
            $resultados[] = [
                'titulo' => 'Arquivos API',
                'status' => 'error',
                'mensagem' => 'Faltam: ' . implode(', ', $apis_faltando) . ' ✗'
            ];
        }

        // TESTE 6: Verificar arquivos HTML
        $testes_total++;
        $htmls = [
            'index.html',
            'login_auth.html',
            'dashboard.html',
            'transacoes.html',
            'relatorios.html',
            'configuracoes.html'
        ];
        $htmls_faltando = [];
        foreach ($htmls as $html) {
            if (!file_exists($html)) {
                $htmls_faltando[] = $html;
            }
        }
        if (empty($htmls_faltando)) {
            $testes_passou++;
            $resultados[] = [
                'titulo' => 'Arquivos HTML',
                'status' => 'success',
                'mensagem' => 'Todos os arquivos HTML encontrados ✓'
            ];
        } else {
            $resultados[] = [
                'titulo' => 'Arquivos HTML',
                'status' => 'warning',
                'mensagem' => 'Faltam: ' . implode(', ', $htmls_faltando)
            ];
        }

        // TESTE 7: Verificar .htaccess
        $testes_total++;
        if (file_exists('.htaccess')) {
            $testes_passou++;
            $resultados[] = [
                'titulo' => 'Arquivo .htaccess',
                'status' => 'success',
                'mensagem' => '.htaccess encontrado ✓'
            ];
        } else {
            $resultados[] = [
                'titulo' => 'Arquivo .htaccess',
                'status' => 'warning',
                'mensagem' => '.htaccess NÃO encontrado (recomendado)'
            ];
        }

        // TESTE 8: Verificar permissões de escrita
        $testes_total++;
        if (is_writable('.')) {
            $testes_passou++;
            $resultados[] = [
                'titulo' => 'Permissões de Escrita',
                'status' => 'success',
                'mensagem' => 'Pasta raiz tem permissão de escrita ✓'
            ];
        } else {
            $resultados[] = [
                'titulo' => 'Permissões de Escrita',
                'status' => 'warning',
                'mensagem' => 'Sem permissão de escrita na raiz'
            ];
        }

        $porcentagem = round(($testes_passou / $testes_total) * 100);
        ?>

        <div class="summary">
            <h2>Resultado Geral</h2>
            <p style="font-size: 48px; margin: 20px 0;">
                <?php echo $testes_passou; ?>/<?php echo $testes_total; ?>
            </p>
            <p style="font-size: 24px;">
                <?php echo $porcentagem; ?>% dos testes passaram
            </p>
        </div>

        <?php foreach ($resultados as $resultado): ?>
            <div class="test-item <?php echo $resultado['status']; ?>">
                <div class="test-title">
                    <?php
                    if ($resultado['status'] === 'success') echo '✅';
                    elseif ($resultado['status'] === 'error') echo '❌';
                    else echo '⚠️';
                    ?>
                    <?php echo $resultado['titulo']; ?>
                </div>
                <div class="test-result">
                    <?php echo $resultado['mensagem']; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="test-item" style="background: #e7f3ff; border-left-color: #2196F3;">
            <div class="test-title">ℹ️ Informações do Servidor</div>
            <div class="test-result">
                <strong>Sistema Operacional:</strong> <?php echo PHP_OS; ?><br>
                <strong>Servidor Web:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido'; ?><br>
                <strong>Documento Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Desconhecido'; ?><br>
                <strong>Domínio:</strong> <?php echo $_SERVER['HTTP_HOST'] ?? 'Desconhecido'; ?><br>
                <strong>HTTPS:</strong> <?php echo (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'Ativo ✓' : 'Inativo ✗'; ?>
            </div>
        </div>

        <?php if ($porcentagem === 100): ?>
            <div class="test-item success" style="text-align: center; font-size: 20px;">
                <strong>🎉 PARABÉNS! Sistema pronto para uso!</strong><br>
                <a href="/" style="color: #667eea; text-decoration: none; margin-top: 10px; display: inline-block;">
                    → Ir para página inicial
                </a>
            </div>
        <?php else: ?>
            <div class="test-item warning" style="text-align: center;">
                <strong>⚠️ Alguns testes falharam</strong><br>
                <small>Corrija os erros acima antes de usar o sistema</small>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
