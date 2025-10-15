-- Poupa.AI Database Schema for Supabase - VERSÃO SEGURA PARA N8N
-- Execute these queries in your Supabase SQL editor
-- Esta versão NÃO quebra o fluxo do n8n

-- Create clientes table (matches your existing structure)
CREATE TABLE IF NOT EXISTS clientes (
    clientid UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    whatsapp TEXT,
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Create movimentacoes table (matches your existing structure)
CREATE TABLE IF NOT EXISTS movimentacoes (
    id TEXT PRIMARY KEY,
    data_movimentacao DATE,
    valor_movimentacao NUMERIC(10,2),
    clientid UUID REFERENCES clientes(clientid),
    type TEXT,
    category TEXT,
    observation TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Create indexes for better performance (SEGURO)
CREATE INDEX IF NOT EXISTS idx_movimentacoes_clientid ON movimentacoes(clientid);
CREATE INDEX IF NOT EXISTS idx_movimentacoes_data ON movimentacoes(data_movimentacao);
CREATE INDEX IF NOT EXISTS idx_movimentacoes_type ON movimentacoes(type);
CREATE INDEX IF NOT EXISTS idx_movimentacoes_category ON movimentacoes(category);
CREATE INDEX IF NOT EXISTS idx_clientes_whatsapp ON clientes(whatsapp);
CREATE INDEX IF NOT EXISTS idx_clientes_status ON clientes(status);

-- Create updated_at trigger function (SEGURO)
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create triggers for updated_at (SEGURO)
CREATE TRIGGER update_clientes_updated_at BEFORE UPDATE ON clientes 
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_movimentacoes_updated_at BEFORE UPDATE ON movimentacoes 
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Grant necessary permissions (SEGURO)
GRANT USAGE ON SCHEMA public TO anon, authenticated;
GRANT ALL ON ALL TABLES IN SCHEMA public TO anon, authenticated;
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO anon, authenticated;

-- NOTA: RLS e políticas foram REMOVIDAS para não quebrar o n8n
-- Se quiser segurança adicional, configure as políticas manualmente depois
