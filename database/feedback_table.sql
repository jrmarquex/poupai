-- Estrutura da tabela de feedback para o Poupa.Ai
-- Esta tabela armazena todos os feedbacks dos usuários

CREATE TABLE IF NOT EXISTS feedback (
    id SERIAL PRIMARY KEY,
    user_id UUID REFERENCES clientes(clientid) ON DELETE CASCADE,
    
    -- Informações básicas do feedback
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    
    -- Avaliação e categorização
    rating INTEGER CHECK (rating >= 1 AND rating <= 5),
    category VARCHAR(50) NOT NULL CHECK (category IN ('bug', 'feature', 'improvement', 'ui', 'performance', 'general')),
    priority VARCHAR(20) NOT NULL DEFAULT 'medium' CHECK (priority IN ('low', 'medium', 'high')),
    
    -- Status do feedback
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'in_progress', 'resolved', 'closed')),
    
    -- Informações de contato (opcionais)
    contact_email VARCHAR(255),
    contact_phone VARCHAR(20),
    
    -- Metadados
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    
    -- Resposta da equipe (opcional)
    admin_response TEXT,
    admin_response_at TIMESTAMP WITH TIME ZONE,
    admin_user_id UUID REFERENCES clientes(clientid) ON DELETE SET NULL
);

-- Índices para melhorar performance
CREATE INDEX IF NOT EXISTS idx_feedback_user_id ON feedback(user_id);
CREATE INDEX IF NOT EXISTS idx_feedback_status ON feedback(status);
CREATE INDEX IF NOT EXISTS idx_feedback_category ON feedback(category);
CREATE INDEX IF NOT EXISTS idx_feedback_priority ON feedback(priority);
CREATE INDEX IF NOT EXISTS idx_feedback_created_at ON feedback(created_at);
CREATE INDEX IF NOT EXISTS idx_feedback_rating ON feedback(rating);

-- Trigger para atualizar updated_at automaticamente
CREATE OR REPLACE FUNCTION update_feedback_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_update_feedback_updated_at
    BEFORE UPDATE ON feedback
    FOR EACH ROW
    EXECUTE FUNCTION update_feedback_updated_at();

-- Comentários para documentação
COMMENT ON TABLE feedback IS 'Tabela para armazenar feedbacks dos usuários do sistema Poupa.Ai';
COMMENT ON COLUMN feedback.id IS 'ID único do feedback';
COMMENT ON COLUMN feedback.user_id IS 'UUID do usuário que enviou o feedback (referencia clientes.clientid)';
COMMENT ON COLUMN feedback.title IS 'Título do feedback';
COMMENT ON COLUMN feedback.description IS 'Descrição detalhada do feedback';
COMMENT ON COLUMN feedback.rating IS 'Avaliação de 1 a 5 estrelas';
COMMENT ON COLUMN feedback.category IS 'Categoria do feedback: bug, feature, improvement, ui, performance, general';
COMMENT ON COLUMN feedback.priority IS 'Prioridade: low, medium, high';
COMMENT ON COLUMN feedback.status IS 'Status: pending, in_progress, resolved, closed';
COMMENT ON COLUMN feedback.contact_email IS 'Email de contato (opcional)';
COMMENT ON COLUMN feedback.contact_phone IS 'Telefone de contato (opcional)';
COMMENT ON COLUMN feedback.created_at IS 'Data de criação do feedback';
COMMENT ON COLUMN feedback.updated_at IS 'Data da última atualização';
COMMENT ON COLUMN feedback.admin_response IS 'Resposta da equipe de suporte';
COMMENT ON COLUMN feedback.admin_response_at IS 'Data da resposta da equipe';
COMMENT ON COLUMN feedback.admin_user_id IS 'UUID do administrador que respondeu (referencia clientes.clientid)';

-- Exemplo de dados de teste (opcional)
-- INSERT INTO feedback (user_id, title, description, rating, category, priority, contact_email) VALUES
-- ('550e8400-e29b-41d4-a716-446655440000', 'Sugestão de melhoria na interface', 'Gostaria de ver mais opções de personalização no dashboard.', 4, 'ui', 'medium', 'usuario@exemplo.com'),
-- ('550e8400-e29b-41d4-a716-446655440000', 'Problema ao exportar relatórios', 'O botão de exportar PDF não está funcionando corretamente.', 2, 'bug', 'high', 'usuario@exemplo.com'),
-- ('550e8400-e29b-41d4-a716-446655440000', 'Nova funcionalidade solicitada', 'Seria interessante ter notificações por WhatsApp.', 5, 'feature', 'medium', 'usuario@exemplo.com');
