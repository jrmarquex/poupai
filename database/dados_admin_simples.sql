-- Script simplificado para inserir dados do administrador (5511997245501)
-- Execute este script no SQL Editor do Supabase

-- 1. INSERIR/ATUALIZAR CLIENTE ADMIN COM SENHA REAL
INSERT INTO clientes (
    whatsapp,
    nome,
    email,
    senha_hash,
    primeiro_acesso,
    status,
    ultimo_login
) VALUES (
    '5511997245501',
    'Francisco Marques de Oliveira Junior',
    'frmarques.oli@gmail.com',
    '$2b$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Hash da senha "Hyundaimax@@9"
    false,
    true,
    NOW()
) ON CONFLICT (whatsapp) DO UPDATE SET
    nome = EXCLUDED.nome,
    email = EXCLUDED.email,
    senha_hash = EXCLUDED.senha_hash,
    primeiro_acesso = EXCLUDED.primeiro_acesso,
    status = EXCLUDED.status,
    ultimo_login = EXCLUDED.ultimo_login;

-- 2. VERIFICAR SE O CLIENTE FOI INSERIDO/ATUALIZADO
SELECT 
    clientid,
    whatsapp,
    nome,
    email,
    primeiro_acesso,
    status,
    ultimo_login
FROM clientes 
WHERE whatsapp = '5511997245501';

-- 3. INSERIR MOVIMENTAÇÕES DE EXEMPLO PARA O ADMIN
-- Receitas (Ganhos)
INSERT INTO movimentacoes (id, data_movimentacao, valor_movimentacao, clientid, type, category, observation) VALUES
('mov_001', '2024-01-15', 2800.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'receita', 'salario', 'Salário CLT - Janeiro'),
('mov_002', '2024-01-20', 850.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'receita', 'freelance', 'Freelance Design - Projeto Web'),
('mov_003', '2024-01-25', 450.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'receita', 'freelance', 'Freelance Design - Logo'),
('mov_004', '2024-02-01', 2800.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'receita', 'salario', 'Salário CLT - Fevereiro'),
('mov_005', '2024-02-10', 1200.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'receita', 'freelance', 'Freelance Design - App Mobile'),
('mov_006', '2024-02-15', 300.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'receita', 'vendas', 'Venda de produto digital'),
('mov_007', '2024-02-20', 600.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'receita', 'freelance', 'Freelance Design - Site'),
('mov_008', '2024-03-01', 2800.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'receita', 'salario', 'Salário CLT - Março'),
('mov_009', '2024-03-05', 750.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'receita', 'freelance', 'Freelance Design - Branding'),
('mov_010', '2024-03-10', 400.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'receita', 'consultoria', 'Consultoria em Marketing');

-- Despesas (Gastos)
INSERT INTO movimentacoes (id, data_movimentacao, valor_movimentacao, clientid, type, category, observation) VALUES
('mov_011', '2024-01-16', 25.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'transporte', 'Uber - Centro'),
('mov_012', '2024-01-17', 42.90, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'alimentacao', 'iFood - Jantar'),
('mov_013', '2024-01-18', 15.50, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'alimentacao', 'Café da manhã'),
('mov_014', '2024-01-20', 120.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'transporte', 'Combustível'),
('mov_015', '2024-01-22', 89.90, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'lazer', 'Cinema'),
('mov_016', '2024-01-25', 35.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'alimentacao', 'Almoço'),
('mov_017', '2024-01-28', 200.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'contas', 'Conta de luz'),
('mov_018', '2024-02-02', 30.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'transporte', 'Uber - Shopping'),
('mov_019', '2024-02-05', 65.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'alimentacao', 'Supermercado'),
('mov_020', '2024-02-08', 150.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'contas', 'Internet'),
('mov_021', '2024-02-12', 45.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'lazer', 'Livro'),
('mov_022', '2024-02-15', 28.50, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'alimentacao', 'Lanche'),
('mov_023', '2024-02-18', 80.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'transporte', 'Taxi'),
('mov_024', '2024-02-22', 95.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'lazer', 'Restaurante'),
('mov_025', '2024-02-25', 180.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'contas', 'Água'),
('mov_026', '2024-03-02', 22.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'transporte', 'Uber - Trabalho'),
('mov_027', '2024-03-05', 38.90, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'alimentacao', 'iFood - Almoço'),
('mov_028', '2024-03-08', 120.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'lazer', 'Show'),
('mov_029', '2024-03-10', 55.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'alimentacao', 'Jantar'),
('mov_030', '2024-03-12', 300.00, (SELECT clientid FROM clientes WHERE whatsapp = '5511997245501'), 'despesa', 'contas', 'Aluguel');

-- 4. VERIFICAR DADOS INSERIDOS
-- Resumo financeiro do admin
SELECT 
    c.nome,
    c.whatsapp,
    COUNT(m.id) as total_transacoes,
    SUM(CASE WHEN m.type = 'receita' THEN m.valor_movimentacao ELSE 0 END) as total_receitas,
    SUM(CASE WHEN m.type = 'despesa' THEN m.valor_movimentacao ELSE 0 END) as total_despesas,
    SUM(CASE WHEN m.type = 'receita' THEN m.valor_movimentacao ELSE -m.valor_movimentacao END) as saldo_liquido
FROM clientes c
LEFT JOIN movimentacoes m ON c.clientid = m.clientid
WHERE c.whatsapp = '5511997245501'
GROUP BY c.clientid, c.nome, c.whatsapp;

-- 5. VERIFICAR MOVIMENTAÇÕES RECENTES
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
ORDER BY m.created_at DESC
LIMIT 10;
