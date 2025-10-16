-- Adicionar campo para foto de perfil na tabela clientes
-- Execute este SQL no Supabase Dashboard

-- Adicionar coluna para URL da foto de perfil
ALTER TABLE clientes 
ADD COLUMN IF NOT EXISTS profile_photo_url TEXT;

-- Adicionar coluna para dados da foto (base64 ou blob)
ALTER TABLE clientes 
ADD COLUMN IF NOT EXISTS profile_photo_data TEXT;

-- Adicionar coluna para tipo de foto (url, base64, etc)
ALTER TABLE clientes 
ADD COLUMN IF NOT EXISTS profile_photo_type VARCHAR(20) DEFAULT 'url';

-- Adicionar coluna para data de atualização da foto
ALTER TABLE clientes 
ADD COLUMN IF NOT EXISTS profile_photo_updated_at TIMESTAMP DEFAULT NOW();

-- Criar trigger para atualizar timestamp da fotore
CREATE OR REPLACE FUNCTION update_profile_photo_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.profile_photo_updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Aplicar trigger na tabela clientes
CREATE TRIGGER update_clientes_profile_photo_timestamp 
BEFORE UPDATE ON clientes 
FOR EACH ROW 
EXECUTE FUNCTION update_profile_photo_timestamp();

-- Adicionar comentários para documentação
COMMENT ON COLUMN clientes.profile_photo_url IS 'URL da foto de perfil do usuário';
COMMENT ON COLUMN clientes.profile_photo_data IS 'Dados da foto em base64 ou blob';
COMMENT ON COLUMN clientes.profile_photo_type IS 'Tipo de armazenamento: url, base64, blob';
COMMENT ON COLUMN clientes.profile_photo_updated_at IS 'Data da última atualização da foto';
