-- Script para adicionar colunas de nome e email na tabela clientes
-- Execute este script no seu banco PostgreSQL/Supabase

-- Adicionar colunas de nome e email na tabela clientes
ALTER TABLE clientes 
ADD COLUMN IF NOT EXISTS nome VARCHAR(255),
ADD COLUMN IF NOT EXISTS email VARCHAR(255);

-- Adicionar comentários para documentação
COMMENT ON COLUMN clientes.nome IS 'Nome completo do cliente';
COMMENT ON COLUMN clientes.email IS 'Email de contato do cliente';

-- Criar índices para melhorar performance nas consultas
CREATE INDEX IF NOT EXISTS idx_clientes_nome ON clientes(nome);
CREATE INDEX IF NOT EXISTS idx_clientes_email ON clientes(email);

-- Atualizar a tabela de feedback para referenciar corretamente
-- (Se a tabela feedback já existir, você pode precisar recriar as foreign keys)

-- Exemplo de dados de teste (opcional)
-- UPDATE clientes SET 
--     nome = 'João Silva',
--     email = 'joao.silva@email.com'
-- WHERE whatsapp = '+5511999999999';

-- Verificar se as colunas foram adicionadas corretamente
-- SELECT column_name, data_type, is_nullable 
-- FROM information_schema.columns 
-- WHERE table_name = 'clientes' 
-- AND column_name IN ('nome', 'email');
