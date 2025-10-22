-- Sistema de Autenticação para Poupa.Ai
-- Script para adicionar campos de autenticação na tabela clientes

-- Adicionar campos de autenticação na tabela clientes
ALTER TABLE clientes 
ADD COLUMN IF NOT EXISTS nome VARCHAR(255),
ADD COLUMN IF NOT EXISTS email VARCHAR(255),
ADD COLUMN IF NOT EXISTS senha_hash VARCHAR(255),
ADD COLUMN IF NOT EXISTS primeiro_acesso BOOLEAN DEFAULT TRUE,
ADD COLUMN IF NOT EXISTS ultimo_login TIMESTAMP,
ADD COLUMN IF NOT EXISTS tentativas_login INTEGER DEFAULT 0,
ADD COLUMN IF NOT EXISTS bloqueado_ate TIMESTAMP,
ADD COLUMN IF NOT EXISTS token_recuperacao VARCHAR(255),
ADD COLUMN IF NOT EXISTS token_expiracao TIMESTAMP;

-- Adicionar comentários para documentação
COMMENT ON COLUMN clientes.nome IS 'Nome completo do cliente';
COMMENT ON COLUMN clientes.email IS 'Email de contato do cliente';
COMMENT ON COLUMN clientes.senha_hash IS 'Hash da senha do cliente (bcrypt)';
COMMENT ON COLUMN clientes.primeiro_acesso IS 'Indica se é o primeiro acesso do cliente';
COMMENT ON COLUMN clientes.ultimo_login IS 'Data e hora do último login';
COMMENT ON COLUMN clientes.tentativas_login IS 'Número de tentativas de login falhadas';
COMMENT ON COLUMN clientes.bloqueado_ate IS 'Data até quando o cliente está bloqueado';
COMMENT ON COLUMN clientes.token_recuperacao IS 'Token para recuperação de senha';
COMMENT ON COLUMN clientes.token_expiracao IS 'Data de expiração do token de recuperação';

-- Criar índices para melhorar performance nas consultas de autenticação
CREATE INDEX IF NOT EXISTS idx_clientes_whatsapp_auth ON clientes(whatsapp);
CREATE INDEX IF NOT EXISTS idx_clientes_email_auth ON clientes(email);
CREATE INDEX IF NOT EXISTS idx_clientes_primeiro_acesso ON clientes(primeiro_acesso);
CREATE INDEX IF NOT EXISTS idx_clientes_ultimo_login ON clientes(ultimo_login);
CREATE INDEX IF NOT EXISTS idx_clientes_token_recuperacao ON clientes(token_recuperacao);

-- Criar função para verificar se cliente existe pelo WhatsApp
CREATE OR REPLACE FUNCTION verificar_cliente_whatsapp(whatsapp_param TEXT)
RETURNS TABLE (
    clientid UUID,
    whatsapp TEXT,
    nome VARCHAR(255),
    email VARCHAR(255),
    primeiro_acesso BOOLEAN,
    status BOOLEAN,
    bloqueado_ate TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        c.clientid,
        c.whatsapp,
        c.nome,
        c.email,
        c.primeiro_acesso,
        c.status,
        c.bloqueado_ate
    FROM clientes c
    WHERE c.whatsapp = whatsapp_param
    AND c.status = TRUE;
END;
$$ LANGUAGE plpgsql;

-- Criar função para registrar primeiro acesso
CREATE OR REPLACE FUNCTION registrar_primeiro_acesso(
    whatsapp_param TEXT,
    nome_param VARCHAR(255),
    email_param VARCHAR(255),
    senha_hash_param VARCHAR(255)
)
RETURNS UUID AS $$
DECLARE
    cliente_id UUID;
BEGIN
    -- Verificar se cliente já existe
    SELECT clientid INTO cliente_id
    FROM clientes
    WHERE whatsapp = whatsapp_param;
    
    IF cliente_id IS NOT NULL THEN
        -- Atualizar dados do cliente existente
        UPDATE clientes SET
            nome = nome_param,
            email = email_param,
            senha_hash = senha_hash_param,
            primeiro_acesso = FALSE,
            ultimo_login = NOW(),
            tentativas_login = 0,
            updated_at = NOW()
        WHERE clientid = cliente_id;
    ELSE
        -- Criar novo cliente
        INSERT INTO clientes (whatsapp, nome, email, senha_hash, primeiro_acesso, ultimo_login)
        VALUES (whatsapp_param, nome_param, email_param, senha_hash_param, FALSE, NOW())
        RETURNING clientid INTO cliente_id;
    END IF;
    
    RETURN cliente_id;
END;
$$ LANGUAGE plpgsql;

-- Criar função para autenticar cliente
CREATE OR REPLACE FUNCTION autenticar_cliente(
    whatsapp_param TEXT,
    senha_hash_param VARCHAR(255)
)
RETURNS TABLE (
    clientid UUID,
    whatsapp TEXT,
    nome VARCHAR(255),
    email VARCHAR(255),
    primeiro_acesso BOOLEAN,
    status BOOLEAN
) AS $$
BEGIN
    -- Verificar se cliente está bloqueado
    IF EXISTS (
        SELECT 1 FROM clientes 
        WHERE whatsapp = whatsapp_param 
        AND bloqueado_ate > NOW()
    ) THEN
        RAISE EXCEPTION 'Cliente temporariamente bloqueado';
    END IF;
    
    -- Verificar credenciais
    RETURN QUERY
    SELECT 
        c.clientid,
        c.whatsapp,
        c.nome,
        c.email,
        c.primeiro_acesso,
        c.status
    FROM clientes c
    WHERE c.whatsapp = whatsapp_param
    AND c.senha_hash = senha_hash_param
    AND c.status = TRUE;
    
    -- Atualizar último login se encontrou
    IF FOUND THEN
        UPDATE clientes SET
            ultimo_login = NOW(),
            tentativas_login = 0,
            updated_at = NOW()
        WHERE whatsapp = whatsapp_param;
    ELSE
        -- Incrementar tentativas de login
        UPDATE clientes SET
            tentativas_login = tentativas_login + 1,
            bloqueado_ate = CASE 
                WHEN tentativas_login >= 4 THEN NOW() + INTERVAL '30 minutes'
                ELSE bloqueado_ate
            END,
            updated_at = NOW()
        WHERE whatsapp = whatsapp_param;
    END IF;
END;
$$ LANGUAGE plpgsql;

-- Criar função para buscar lançamentos do cliente
CREATE OR REPLACE FUNCTION buscar_lancamentos_cliente(
    clientid_param UUID,
    limite INTEGER DEFAULT 50,
    offset_param INTEGER DEFAULT 0
)
RETURNS TABLE (
    id TEXT,
    data_movimentacao DATE,
    valor_movimentacao NUMERIC(10,2),
    type TEXT,
    category TEXT,
    observation TEXT,
    created_at TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        m.id,
        m.data_movimentacao,
        m.valor_movimentacao,
        m.type,
        m.category,
        m.observation,
        m.created_at
    FROM movimentacoes m
    WHERE m.clientid = clientid_param
    ORDER BY m.created_at DESC
    LIMIT limite_param
    OFFSET offset_param;
END;
$$ LANGUAGE plpgsql;

-- Exemplo de dados de teste para o cliente específico
-- INSERT INTO clientes (whatsapp, nome, email, senha_hash, primeiro_acesso) VALUES
-- ('5511997245501', 'João Silva', 'joao.silva@email.com', '$2b$10$example_hash', FALSE);

-- Verificar se as colunas foram adicionadas corretamente
-- SELECT column_name, data_type, is_nullable 
-- FROM information_schema.columns 
-- WHERE table_name = 'clientes' 
-- AND column_name IN ('nome', 'email', 'senha_hash', 'primeiro_acesso', 'ultimo_login', 'tentativas_login', 'bloqueado_ate', 'token_recuperacao', 'token_expiracao');
