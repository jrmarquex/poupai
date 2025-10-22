-- Queries para buscar lançamentos do cliente 5511997245501
-- Execute estas queries no seu banco PostgreSQL/Supabase

-- 1. VERIFICAR SE O CLIENTE EXISTE E SE É PRIMEIRO ACESSO
SELECT 
    clientid,
    whatsapp,
    nome,
    email,
    primeiro_acesso,
    status,
    ultimo_login,
    created_at
FROM clientes 
WHERE whatsapp = '5511997245501';

-- 2. BUSCAR TODOS OS LANÇAMENTOS DO CLIENTE (últimos 50)
SELECT 
    m.id,
    m.data_movimentacao,
    m.valor_movimentacao,
    m.type,
    m.category,
    m.observation,
    m.created_at,
    c.nome as cliente_nome,
    c.whatsapp as cliente_whatsapp
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501'
ORDER BY m.created_at DESC
LIMIT 50;

-- 3. RESUMO FINANCEIRO DO CLIENTE
SELECT 
    c.nome,
    c.whatsapp,
    COUNT(m.id) as total_transacoes,
    SUM(CASE WHEN m.type = 'receita' THEN m.valor_movimentacao ELSE 0 END) as total_receitas,
    SUM(CASE WHEN m.type = 'despesa' THEN m.valor_movimentacao ELSE 0 END) as total_despesas,
    SUM(CASE WHEN m.type = 'receita' THEN m.valor_movimentacao ELSE -m.valor_movimentacao END) as saldo_liquido,
    MIN(m.data_movimentacao) as primeira_transacao,
    MAX(m.data_movimentacao) as ultima_transacao
FROM clientes c
LEFT JOIN movimentacoes m ON c.clientid = m.clientid
WHERE c.whatsapp = '5511997245501'
GROUP BY c.clientid, c.nome, c.whatsapp;

-- 4. LANÇAMENTOS POR CATEGORIA (últimos 30 dias)
SELECT 
    m.category,
    m.type,
    COUNT(*) as quantidade,
    SUM(m.valor_movimentacao) as valor_total,
    AVG(m.valor_movimentacao) as valor_medio
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501'
AND m.data_movimentacao >= CURRENT_DATE - INTERVAL '30 days'
GROUP BY m.category, m.type
ORDER BY valor_total DESC;

-- 5. EVOLUÇÃO MENSAL DOS GASTOS
SELECT 
    DATE_TRUNC('month', m.data_movimentacao) as mes,
    SUM(CASE WHEN m.type = 'receita' THEN m.valor_movimentacao ELSE 0 END) as receitas_mes,
    SUM(CASE WHEN m.type = 'despesa' THEN m.valor_movimentacao ELSE 0 END) as despesas_mes,
    SUM(CASE WHEN m.type = 'receita' THEN m.valor_movimentacao ELSE -m.valor_movimentacao END) as saldo_mes
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501'
GROUP BY DATE_TRUNC('month', m.data_movimentacao)
ORDER BY mes DESC;

-- 6. TOP 5 MAIORES GASTOS
SELECT 
    m.data_movimentacao,
    m.category,
    m.observation,
    m.valor_movimentacao,
    m.type
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501'
AND m.type = 'despesa'
ORDER BY m.valor_movimentacao DESC
LIMIT 5;

-- 7. TOP 5 MAIORES RECEITAS
SELECT 
    m.data_movimentacao,
    m.category,
    m.observation,
    m.valor_movimentacao,
    m.type
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501'
AND m.type = 'receita'
ORDER BY m.valor_movimentacao DESC
LIMIT 5;

-- 8. LANÇAMENTOS RECENTES (últimos 7 dias)
SELECT 
    m.data_movimentacao,
    m.type,
    m.category,
    m.observation,
    m.valor_movimentacao,
    m.created_at
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501'
AND m.data_movimentacao >= CURRENT_DATE - INTERVAL '7 days'
ORDER BY m.data_movimentacao DESC, m.created_at DESC;

-- 9. VERIFICAR SE CLIENTE ESTÁ BLOQUEADO
SELECT 
    whatsapp,
    tentativas_login,
    bloqueado_ate,
    CASE 
        WHEN bloqueado_ate > NOW() THEN 'BLOQUEADO'
        ELSE 'LIBERADO'
    END as status_bloqueio
FROM clientes 
WHERE whatsapp = '5511997245501';

-- 10. ATUALIZAR ÚLTIMO LOGIN (usar após login bem-sucedido)
UPDATE clientes 
SET 
    ultimo_login = NOW(),
    tentativas_login = 0,
    updated_at = NOW()
WHERE whatsapp = '5511997245501';

-- 11. INSERIR NOVO LANÇAMENTO (exemplo)
INSERT INTO movimentacoes (
    id,
    data_movimentacao,
    valor_movimentacao,
    clientid,
    type,
    category,
    observation
) VALUES (
    'mov_' || EXTRACT(EPOCH FROM NOW())::text,
    CURRENT_DATE,
    150.00,
    (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'),
    'despesa',
    'alimentação',
    'Almoço no restaurante'
);

-- 12. BUSCAR CLIENTE POR EMAIL (para login alternativo)
SELECT 
    clientid,
    whatsapp,
    nome,
    email,
    primeiro_acesso,
    status
FROM clientes 
WHERE email = 'joao.silva@email.com'
AND status = TRUE;

-- 13. ESTATÍSTICAS GERAIS DO CLIENTE
SELECT 
    'Total de Transações' as metrica,
    COUNT(*) as valor
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501'

UNION ALL

SELECT 
    'Receitas',
    COUNT(*)
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501'
AND m.type = 'receita'

UNION ALL

SELECT 
    'Despesas',
    COUNT(*)
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501'
AND m.type = 'despesa'

UNION ALL

SELECT 
    'Categorias Únicas',
    COUNT(DISTINCT m.category)
FROM movimentacoes m
JOIN clientes c ON m.clientid = c.clientid
WHERE c.whatsapp = '5511997245501';

-- 14. VERIFICAR PRIMEIRO ACESSO E REDIRECIONAR
SELECT 
    CASE 
        WHEN primeiro_acesso = TRUE THEN 'REDIRECIONAR_PARA_CADASTRO'
        WHEN senha_hash IS NULL THEN 'REDIRECIONAR_PARA_CADASTRO'
        ELSE 'REDIRECIONAR_PARA_LOGIN'
    END as acao_necessaria,
    whatsapp,
    nome,
    email
FROM clientes 
WHERE whatsapp = '5511997245501';
