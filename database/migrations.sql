-- AssistenteFin Database Schema for Supabase
-- Execute these queries in your Supabase SQL editor

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    whatsapp VARCHAR(20),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(7), -- hex color
    icon VARCHAR(50),
    type VARCHAR(20) NOT NULL CHECK (type IN ('income', 'expense')),
    created_at TIMESTAMP DEFAULT NOW()
);

-- Create transactions table
CREATE TABLE IF NOT EXISTS transactions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    category_id INTEGER REFERENCES categories(id),
    amount DECIMAL(10,2) NOT NULL,
    description TEXT NOT NULL,
    establishment VARCHAR(100),
    type VARCHAR(20) NOT NULL CHECK (type IN ('income', 'expense')),
    source VARCHAR(20) CHECK (source IN ('text', 'audio', 'photo')),
    original_message TEXT,
    processed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Create monthly_summaries table
CREATE TABLE IF NOT EXISTS monthly_summaries (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    month INTEGER NOT NULL,
    year INTEGER NOT NULL,
    total_income DECIMAL(10,2) DEFAULT 0,
    total_expenses DECIMAL(10,2) DEFAULT 0,
    balance DECIMAL(10,2) DEFAULT 0,
    top_category VARCHAR(50),
    top_establishment VARCHAR(100),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Create whatsapp_sessions table
CREATE TABLE IF NOT EXISTS whatsapp_sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    whatsapp_id VARCHAR(100) NOT NULL,
    session_token VARCHAR(255),
    is_active INTEGER DEFAULT 1,
    last_activity TIMESTAMP DEFAULT NOW(),
    created_at TIMESTAMP DEFAULT NOW()
);

-- Insert default admin user (password is '1234' - you should hash this in production)
INSERT INTO users (username, password, name) 
VALUES ('admin', '1234', 'Administrador')
ON CONFLICT (username) DO NOTHING;

-- Insert default categories
INSERT INTO categories (name, color, icon, type) VALUES
('Transporte', '#20C997', 'car', 'expense'),
('Alimentação', '#A78BFA', 'utensils', 'expense'),
('Lazer', '#22D3EE', 'gamepad', 'expense'),
('Outros', '#6B7280', 'more', 'expense'),
('Salário CLT', '#10B981', 'briefcase', 'income'),
('Trabalho Freelancer', '#F59E0B', 'laptop', 'income'),
('Delivery', '#EF4444', 'truck', 'income')
ON CONFLICT DO NOTHING;

-- Insert sample transactions for demo
INSERT INTO transactions (user_id, category_id, amount, description, establishment, type, created_at) VALUES
(1, 1, 25.00, 'Uber - Centro', 'Uber', 'expense', '2024-11-30 14:30:00'),
(1, 6, 850.00, 'Freelance Design', 'Cliente XYZ', 'income', '2024-11-29 10:00:00'),
(1, 2, 42.90, 'iFood - Jantar', 'iFood', 'expense', '2024-11-28 19:30:00'),
(1, 1, 180.00, 'Corridas Uber - Novembro', 'Uber', 'expense', '2024-11-25 18:00:00'),
(1, 2, 145.00, 'Pedidos iFood - Novembro', 'iFood', 'expense', '2024-11-24 20:15:00'),
(1, 2, 120.00, 'Compras do mês', 'Supermercado', 'expense', '2024-11-23 16:00:00'),
(1, 5, 2800.00, 'Salário CLT', 'Empresa ABC', 'income', '2024-11-01 09:00:00'),
(1, 7, 75.00, 'Corridas iFood', 'iFood', 'income', '2024-11-20 21:30:00')
ON CONFLICT DO NOTHING;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_transactions_user_id ON transactions(user_id);
CREATE INDEX IF NOT EXISTS idx_transactions_category_id ON transactions(category_id);
CREATE INDEX IF NOT EXISTS idx_transactions_created_at ON transactions(created_at);
CREATE INDEX IF NOT EXISTS idx_transactions_type ON transactions(type);
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);

-- Create updated_at trigger for users table
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users 
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_transactions_updated_at BEFORE UPDATE ON transactions 
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_monthly_summaries_updated_at BEFORE UPDATE ON monthly_summaries 
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();