-- Exemplos de queries para a tabela de feedback
-- Este arquivo contém exemplos de como consultar e gerenciar os feedbacks

-- 1. BUSCAR TODOS OS FEEDBACKS DE UM USUÁRIO
SELECT 
    f.id,
    f.title,
    f.description,
    f.rating,
    f.category,
    f.priority,
    f.status,
    f.created_at,
    c.whatsapp as user_whatsapp
FROM feedback f
JOIN clientes c ON f.user_id = c.clientid
WHERE f.user_id = '550e8400-e29b-41d4-a716-446655440000'
ORDER BY f.created_at DESC;

-- 2. BUSCAR FEEDBACKS POR CATEGORIA
SELECT 
    f.id,
    f.title,
    f.description,
    f.rating,
    f.priority,
    f.status,
    f.created_at,
    c.nome as user_name
FROM feedback f
JOIN clientes c ON f.user_id = c.id
WHERE f.category = 'bug'
ORDER BY f.priority DESC, f.created_at DESC;

-- 3. BUSCAR FEEDBACKS POR STATUS
SELECT 
    f.id,
    f.title,
    f.description,
    f.rating,
    f.category,
    f.priority,
    f.created_at,
    c.nome as user_name
FROM feedback f
JOIN clientes c ON f.user_id = c.id
WHERE f.status = 'pending'
ORDER BY f.priority DESC, f.created_at ASC;

-- 4. ESTATÍSTICAS DE FEEDBACK
SELECT 
    COUNT(*) as total_feedbacks,
    AVG(rating) as average_rating,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
    COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_count,
    COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_count,
    COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed_count
FROM feedback;

-- 5. FEEDBACKS POR CATEGORIA (ESTATÍSTICAS)
SELECT 
    category,
    COUNT(*) as count,
    AVG(rating) as avg_rating,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending
FROM feedback
GROUP BY category
ORDER BY count DESC;

-- 6. FEEDBACKS POR PRIORIDADE
SELECT 
    priority,
    COUNT(*) as count,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
    COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress
FROM feedback
GROUP BY priority
ORDER BY 
    CASE priority 
        WHEN 'high' THEN 1 
        WHEN 'medium' THEN 2 
        WHEN 'low' THEN 3 
    END;

-- 7. FEEDBACKS RECENTES (ÚLTIMOS 30 DIAS)
SELECT 
    f.id,
    f.title,
    f.description,
    f.rating,
    f.category,
    f.priority,
    f.status,
    f.created_at,
    c.nome as user_name
FROM feedback f
JOIN clientes c ON f.user_id = c.id
WHERE f.created_at >= NOW() - INTERVAL '30 days'
ORDER BY f.created_at DESC;

-- 8. FEEDBACKS COM MAIOR RATING
SELECT 
    f.id,
    f.title,
    f.description,
    f.rating,
    f.category,
    f.created_at,
    c.nome as user_name
FROM feedback f
JOIN clientes c ON f.user_id = c.id
WHERE f.rating >= 4
ORDER BY f.rating DESC, f.created_at DESC;

-- 9. FEEDBACKS COM MENOR RATING (PROBLEMAS)
SELECT 
    f.id,
    f.title,
    f.description,
    f.rating,
    f.category,
    f.priority,
    f.status,
    f.created_at,
    c.nome as user_name
FROM feedback f
JOIN clientes c ON f.user_id = c.id
WHERE f.rating <= 2
ORDER BY f.rating ASC, f.priority DESC;

-- 10. INSERIR NOVO FEEDBACK
INSERT INTO feedback (
    user_id, 
    title, 
    description, 
    rating, 
    category, 
    priority, 
    contact_email, 
    contact_phone
) VALUES (
    1, 
    'Título do feedback', 
    'Descrição detalhada do feedback', 
    4, 
    'feature', 
    'medium', 
    'usuario@exemplo.com', 
    '+5511999999999'
);

-- 11. ATUALIZAR STATUS DO FEEDBACK
UPDATE feedback 
SET 
    status = 'in_progress',
    updated_at = NOW()
WHERE id = 1;

-- 12. ADICIONAR RESPOSTA DA EQUIPE
UPDATE feedback 
SET 
    admin_response = 'Obrigado pelo feedback! Estamos trabalhando nesta funcionalidade.',
    admin_response_at = NOW(),
    admin_user_id = 1,
    status = 'resolved',
    updated_at = NOW()
WHERE id = 1;

-- 13. BUSCAR FEEDBACKS SEM RESPOSTA
SELECT 
    f.id,
    f.title,
    f.description,
    f.rating,
    f.category,
    f.priority,
    f.created_at,
    c.nome as user_name
FROM feedback f
JOIN clientes c ON f.user_id = c.id
WHERE f.admin_response IS NULL
ORDER BY f.priority DESC, f.created_at ASC;

-- 14. FEEDBACKS POR USUÁRIO (ESTATÍSTICAS)
SELECT 
    c.nome,
    c.email,
    COUNT(f.id) as total_feedbacks,
    AVG(f.rating) as avg_rating,
    COUNT(CASE WHEN f.status = 'pending' THEN 1 END) as pending_count
FROM clientes c
LEFT JOIN feedback f ON c.id = f.user_id
GROUP BY c.id, c.nome, c.email
HAVING COUNT(f.id) > 0
ORDER BY total_feedbacks DESC;

-- 15. FEEDBACKS POR MÊS (ESTATÍSTICAS TEMPORAIS)
SELECT 
    DATE_TRUNC('month', created_at) as month,
    COUNT(*) as total_feedbacks,
    AVG(rating) as avg_rating,
    COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_count
FROM feedback
GROUP BY DATE_TRUNC('month', created_at)
ORDER BY month DESC;
