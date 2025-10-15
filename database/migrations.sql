-- Poupa.AI Database Schema for Supabase
-- Execute these queries in your Supabase SQL editor
-- This schema matches your existing tables: clientes and movimentacoes

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

-- Insert sample clientes data (matching your existing data structure)
INSERT INTO clientes (clientid, whatsapp, status) VALUES
('660a9484-dc41-4aa3-a01d-aec3d477fdfe', '5511997245501', TRUE),
('800fcbf0-95d5-4c1b-b79d-fdc3755cc5d', '5511913142143', TRUE),
('89864049-7d89-4f6e-a15b-3a38b810dd5', '555199528953', TRUE),
('a0b9ae28-2806-4e44-aec2-9f735c5b76c', '5521998934748', TRUE)
ON CONFLICT (clientid) DO NOTHING;

-- Insert sample movimentacoes data (matching your existing data structure)
INSERT INTO movimentacoes (id, data_movimentacao, valor_movimentacao, clientid, type, category, observation) VALUES
('011a0', '2025-08-03', 70.00, '660a9484-dc41-4aa3-a01d-aec3d477fdfe', 'Despesa', 'alimentação', 'pizza'),
('06ba0', '2025-07-13', 89.18, '660a9484-dc41-4aa3-a01d-aec3d477fdfe', 'Despesa', 'alimentação', 'Ifood'),
('086e0', '2023-10-01', 37.99, '660a9484-dc41-4aa3-a01d-aec3d477fdfe', 'Despesa', 'Produtos', 'Transferência de M'),
('09a09', '2025-07-01', 100.00, '89864049-7d89-4f6e-a15b-3a38b810dd5', 'Despesa', 'alimentação', 'barzinho'),
('0d72d', '2022-11-07', 85.00, '89864049-7d89-4f6e-a15b-3a38b810dd5', 'Despesa', 'Outros', 'Transferência de Jc'),
('0f48a', '2025-07-21', 130.00, '660a9484-dc41-4aa3-a01d-aec3d477fdfe', 'Despesa', 'Outros', 'valor corrigido'),
('1a25a', '2025-07-14', 16.00, '660a9484-dc41-4aa3-a01d-aec3d477fdfe', 'Despesa', 'alimentação', 'Almoço'),
('1bfdd', '2025-07-23', 25.99, '660a9484-dc41-4aa3-a01d-aec3d477fdfe', 'Despesa', 'alimentação', 'almoço'),
('1c1bd', '2025-07-21', 13.68, '660a9484-dc41-4aa3-a01d-aec3d477fdfe', 'Despesa', 'alimentação', 'mercadinho'),
('1e48b', '2025-07-18', 15.78, '660a9484-dc41-4aa3-a01d-aec3d477fdfe', 'Despesa', 'Outros', 'mercado'),
('1eb51', '2025-07-14', 26.00, '800fcbf0-95d5-4c1b-b79d-fdc3755cc5d', 'Despesa', 'alimentação', 'mercado'),
('20c46', '2025-09-03', 26.48, '660a9484-dc41-4aa3-a01d-aec3d477fdfe', 'Despesa', 'alimentação', 'almoço'),
('212b1', '2025-07-17', 10.00, 'a0b9ae28-2806-4e44-aec2-9f735c5b76c', 'Despesa', 'Outros', 'noite'),
('216b0', '2025-07-13', 2.05, '660a9484-dc41-4aa3-a01d-aec3d477fdfe', 'Receita', 'Outros', 'venda'),
('227b7', '2025-09-01', 10.00, '660a9484-dc41-4aa3-a01d-aec3d477fdfe', 'Despesa', 'Outros', 'rifa'),
('2415c', '2025-07-30', 9.99, '660a9484-dc41-4aa3-a01d-aec3d477fdfe', 'Despesa', 'Outros', 'compra'),
('258b6', '2025-07-31', 19.48, '660a9484-dc41-4aa3-a01d-aec3d477fdfe', 'Despesa', 'Outros', 'mercadinho'),
('28488', '2025-09-08', 22.98, '660a9484-dc41-4aa3-a01d-aec3d477fdfe', 'Despesa', 'alimentação', 'Almoço')
ON CONFLICT (id) DO NOTHING;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_movimentacoes_clientid ON movimentacoes(clientid);
CREATE INDEX IF NOT EXISTS idx_movimentacoes_data ON movimentacoes(data_movimentacao);
CREATE INDEX IF NOT EXISTS idx_movimentacoes_type ON movimentacoes(type);
CREATE INDEX IF NOT EXISTS idx_movimentacoes_category ON movimentacoes(category);
CREATE INDEX IF NOT EXISTS idx_clientes_whatsapp ON clientes(whatsapp);
CREATE INDEX IF NOT EXISTS idx_clientes_status ON clientes(status);

-- Create updated_at trigger function
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create triggers for updated_at
CREATE TRIGGER update_clientes_updated_at BEFORE UPDATE ON clientes 
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_movimentacoes_updated_at BEFORE UPDATE ON movimentacoes 
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Enable Row Level Security (RLS) for better security
ALTER TABLE clientes ENABLE ROW LEVEL SECURITY;
ALTER TABLE movimentacoes ENABLE ROW LEVEL SECURITY;

-- Create policies for clientes table
CREATE POLICY "Users can view their own data" ON clientes
    FOR SELECT USING (auth.uid()::text = clientid::text);

CREATE POLICY "Users can insert their own data" ON clientes
    FOR INSERT WITH CHECK (auth.uid()::text = clientid::text);

CREATE POLICY "Users can update their own data" ON clientes
    FOR UPDATE USING (auth.uid()::text = clientid::text);

-- Create policies for movimentacoes table
CREATE POLICY "Users can view their own movimentacoes" ON movimentacoes
    FOR SELECT USING (auth.uid()::text = clientid::text);

CREATE POLICY "Users can insert their own movimentacoes" ON movimentacoes
    FOR INSERT WITH CHECK (auth.uid()::text = clientid::text);

CREATE POLICY "Users can update their own movimentacoes" ON movimentacoes
    FOR UPDATE USING (auth.uid()::text = clientid::text);

-- Grant necessary permissions
GRANT USAGE ON SCHEMA public TO anon, authenticated;
GRANT ALL ON ALL TABLES IN SCHEMA public TO anon, authenticated;
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO anon, authenticated;