-- Script para buscar dados reais do usuário 5511997245501
-- Execute este script para verificar os dados disponíveis

-- 1. DADOS DO CLIENTE
SELECT 
    clientid,
    whatsapp,
    nome,
    email,
    status,
    ultimo_login,
    primeiro_acesso
FROM clientes 
WHERE whatsapp = '5511997245501';

-- 2. TOTAL DE MOVIMENTAÇÕES
SELECT 
    COUNT(*) as total_movimentacoes,
    COUNT(CASE WHEN type = 'Receita' THEN 1 END) as total_receitas,
    COUNT(CASE WHEN type = 'Despesa' THEN 1 END) as total_despesas
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501';

-- 3. RESUMO FINANCEIRO
SELECT 
    COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) as total_receitas,
    COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as total_despesas,
    COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) - 
    COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as saldo_liquido
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501';

-- 4. MOVIMENTAÇÕES RECENTES (últimas 10)
SELECT 
    m.id,
    m.type,
    m.valor_movimentacao,
    m.category,
    m.observation,
    m.data_movimentacao,
    m.updated_at
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501'
ORDER BY m.data_movimentacao DESC
LIMIT 10;

-- 5. GASTOS POR CATEGORIA
SELECT 
    category,
    COUNT(*) as quantidade,
    SUM(valor_movimentacao) as total_valor
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501' 
    AND type = 'Despesa'
GROUP BY category
ORDER BY total_valor DESC;

-- 6. RECEITAS POR CATEGORIA
SELECT 
    category,
    COUNT(*) as quantidade,
    SUM(valor_movimentacao) as total_valor
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501' 
    AND type = 'Receita'
GROUP BY category
ORDER BY total_valor DESC;

-- 7. EVOLUÇÃO FINANCEIRA (últimos 7 dias)
SELECT 
    DATE(data_movimentacao) as data,
    COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) as receitas_dia,
    COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as despesas_dia
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501'
    AND data_movimentacao >= CURRENT_DATE - INTERVAL '7 days'
GROUP BY DATE(data_movimentacao)
ORDER BY data DESC;

-- 8. MOVIMENTAÇÕES POR MÊS (últimos 6 meses)
SELECT 
    TO_CHAR(data_movimentacao, 'YYYY-MM') as mes,
    COUNT(*) as total_movimentacoes,
    COALESCE(SUM(CASE WHEN type = 'Receita' THEN valor_movimentacao ELSE 0 END), 0) as receitas_mes,
    COALESCE(SUM(CASE WHEN type = 'Despesa' THEN valor_movimentacao ELSE 0 END), 0) as despesas_mes
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501'
    AND data_movimentacao >= CURRENT_DATE - INTERVAL '6 months'
GROUP BY TO_CHAR(data_movimentacao, 'YYYY-MM')
ORDER BY mes DESC;
