// API Backend para Sistema de Autenticação Poupa.Ai
// Este é um exemplo de implementação usando Express.js e Supabase

const express = require('express');
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');
const { createClient } = require('@supabase/supabase-js');
require('dotenv').config();

const app = express();
app.use(express.json());

// Configuração do Supabase
const supabase = createClient(
    process.env.NEXT_PUBLIC_SUPABASE_URL,
    process.env.SUPABASE_SERVICE_ROLE_KEY
);

// Middleware de autenticação
const authenticateToken = (req, res, next) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];

    if (!token) {
        return res.status(401).json({ success: false, message: 'Token de acesso necessário' });
    }

    jwt.verify(token, process.env.JWT_SECRET, (err, user) => {
        if (err) {
            return res.status(403).json({ success: false, message: 'Token inválido' });
        }
        req.user = user;
        next();
    });
};

// 1. VERIFICAR PRIMEIRO ACESSO
app.post('/api/auth/check-first-access', async (req, res) => {
    try {
        const { whatsapp } = req.body;

        if (!whatsapp) {
            return res.status(400).json({ 
                success: false, 
                message: 'WhatsApp é obrigatório' 
            });
        }

        // Buscar cliente no banco
        const { data: cliente, error } = await supabase
            .from('clientes')
            .select('clientid, whatsapp, nome, email, primeiro_acesso, status, bloqueado_ate')
            .eq('whatsapp', whatsapp)
            .single();

        if (error && error.code !== 'PGRST116') {
            throw error;
        }

        if (!cliente) {
            return res.json({
                success: true,
                isFirstAccess: true,
                message: 'Cliente não encontrado - primeiro acesso necessário'
            });
        }

        // Verificar se está bloqueado
        if (cliente.bloqueado_ate && new Date(cliente.bloqueado_ate) > new Date()) {
            return res.status(423).json({
                success: false,
                message: 'Conta temporariamente bloqueada. Tente novamente mais tarde.'
            });
        }

        return res.json({
            success: true,
            isFirstAccess: cliente.primeiro_acesso || !cliente.nome,
            cliente: {
                clientid: cliente.clientid,
                whatsapp: cliente.whatsapp,
                nome: cliente.nome,
                email: cliente.email
            }
        });

    } catch (error) {
        console.error('Erro ao verificar primeiro acesso:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro interno do servidor' 
        });
    }
});

// 2. REGISTRAR PRIMEIRO ACESSO
app.post('/api/auth/register', async (req, res) => {
    try {
        const { whatsapp, nome, email, senha } = req.body;

        // Validações
        if (!whatsapp || !nome || !email || !senha) {
            return res.status(400).json({ 
                success: false, 
                message: 'Todos os campos são obrigatórios' 
            });
        }

        if (senha.length < 6) {
            return res.status(400).json({ 
                success: false, 
                message: 'A senha deve ter pelo menos 6 caracteres' 
            });
        }

        // Hash da senha
        const saltRounds = 10;
        const senhaHash = await bcrypt.hash(senha, saltRounds);

        // Verificar se cliente já existe
        const { data: clienteExistente } = await supabase
            .from('clientes')
            .select('clientid')
            .eq('whatsapp', whatsapp)
            .single();

        let clienteId;

        if (clienteExistente) {
            // Atualizar cliente existente
            const { data, error } = await supabase
                .from('clientes')
                .update({
                    nome,
                    email,
                    senha_hash: senhaHash,
                    primeiro_acesso: false,
                    ultimo_login: new Date().toISOString(),
                    tentativas_login: 0,
                    updated_at: new Date().toISOString()
                })
                .eq('whatsapp', whatsapp)
                .select()
                .single();

            if (error) throw error;
            clienteId = data.clientid;
        } else {
            // Criar novo cliente
            const { data, error } = await supabase
                .from('clientes')
                .insert({
                    whatsapp,
                    nome,
                    email,
                    senha_hash: senhaHash,
                    primeiro_acesso: false,
                    ultimo_login: new Date().toISOString(),
                    status: true
                })
                .select()
                .single();

            if (error) throw error;
            clienteId = data.clientid;
        }

        // Gerar token JWT
        const token = jwt.sign(
            { 
                clientid: clienteId, 
                whatsapp, 
                nome, 
                email 
            },
            process.env.JWT_SECRET,
            { expiresIn: '24h' }
        );

        res.json({
            success: true,
            message: 'Conta criada com sucesso',
            token,
            cliente: {
                clientid: clienteId,
                whatsapp,
                nome,
                email,
                primeiro_acesso: false
            }
        });

    } catch (error) {
        console.error('Erro ao registrar primeiro acesso:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro interno do servidor' 
        });
    }
});

// 3. LOGIN
app.post('/api/auth/login', async (req, res) => {
    try {
        const { identifier, senha } = req.body;

        if (!identifier || !senha) {
            return res.status(400).json({ 
                success: false, 
                message: 'WhatsApp/Email e senha são obrigatórios' 
            });
        }

        // Determinar se é WhatsApp ou email
        const isEmail = identifier.includes('@');
        const query = isEmail 
            ? supabase.from('clientes').select('*').eq('email', identifier)
            : supabase.from('clientes').select('*').eq('whatsapp', identifier);

        const { data: cliente, error } = await query.single();

        if (error && error.code !== 'PGRST116') {
            throw error;
        }

        if (!cliente) {
            return res.status(401).json({ 
                success: false, 
                message: 'Credenciais inválidas' 
            });
        }

        // Verificar se está bloqueado
        if (cliente.bloqueado_ate && new Date(cliente.bloqueado_ate) > new Date()) {
            return res.status(423).json({
                success: false,
                message: 'Conta temporariamente bloqueada. Tente novamente mais tarde.'
            });
        }

        // Verificar senha
        const senhaValida = await bcrypt.compare(senha, cliente.senha_hash);
        
        if (!senhaValida) {
            // Incrementar tentativas de login
            await supabase
                .from('clientes')
                .update({
                    tentativas_login: cliente.tentativas_login + 1,
                    bloqueado_ate: cliente.tentativas_login >= 4 
                        ? new Date(Date.now() + 30 * 60 * 1000).toISOString() // 30 minutos
                        : cliente.bloqueado_ate,
                    updated_at: new Date().toISOString()
                })
                .eq('clientid', cliente.clientid);

            return res.status(401).json({ 
                success: false, 
                message: 'Credenciais inválidas' 
            });
        }

        // Login bem-sucedido - atualizar dados
        await supabase
            .from('clientes')
            .update({
                ultimo_login: new Date().toISOString(),
                tentativas_login: 0,
                updated_at: new Date().toISOString()
            })
            .eq('clientid', cliente.clientid);

        // Gerar token JWT
        const token = jwt.sign(
            { 
                clientid: cliente.clientid, 
                whatsapp: cliente.whatsapp, 
                nome: cliente.nome, 
                email: cliente.email 
            },
            process.env.JWT_SECRET,
            { expiresIn: '24h' }
        );

        res.json({
            success: true,
            message: 'Login realizado com sucesso',
            token,
            cliente: {
                clientid: cliente.clientid,
                whatsapp: cliente.whatsapp,
                nome: cliente.nome,
                email: cliente.email,
                primeiro_acesso: cliente.primeiro_acesso
            }
        });

    } catch (error) {
        console.error('Erro ao fazer login:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro interno do servidor' 
        });
    }
});

// 4. BUSCAR LANÇAMENTOS DO CLIENTE
app.get('/api/lancamentos/:clientid', authenticateToken, async (req, res) => {
    try {
        const { clientid } = req.params;
        const { limite = 50, offset = 0 } = req.query;

        // Verificar se o cliente tem acesso aos dados
        if (req.user.clientid !== clientid) {
            return res.status(403).json({ 
                success: false, 
                message: 'Acesso negado' 
            });
        }

        // Buscar lançamentos
        const { data: lancamentos, error } = await supabase
            .from('movimentacoes')
            .select('*')
            .eq('clientid', clientid)
            .order('created_at', { ascending: false })
            .range(offset, offset + limite - 1);

        if (error) throw error;

        // Calcular resumo financeiro
        const totalReceitas = lancamentos
            .filter(l => l.type === 'receita')
            .reduce((sum, l) => sum + parseFloat(l.valor_movimentacao), 0);

        const totalDespesas = lancamentos
            .filter(l => l.type === 'despesa')
            .reduce((sum, l) => sum + parseFloat(l.valor_movimentacao), 0);

        const saldoLiquido = totalReceitas - totalDespesas;

        res.json({
            success: true,
            lancamentos,
            resumo: {
                totalReceitas,
                totalDespesas,
                saldoLiquido,
                totalTransacoes: lancamentos.length
            }
        });

    } catch (error) {
        console.error('Erro ao buscar lançamentos:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro interno do servidor' 
        });
    }
});

// 5. BUSCAR LANÇAMENTOS POR WHATSAPP (para o cliente específico)
app.get('/api/lancamentos-whatsapp/:whatsapp', async (req, res) => {
    try {
        const { whatsapp } = req.params;
        const { limite = 50, offset = 0 } = req.query;

        // Buscar cliente pelo WhatsApp
        const { data: cliente, error: clienteError } = await supabase
            .from('clientes')
            .select('clientid')
            .eq('whatsapp', whatsapp)
            .single();

        if (clienteError || !cliente) {
            return res.status(404).json({ 
                success: false, 
                message: 'Cliente não encontrado' 
            });
        }

        // Buscar lançamentos
        const { data: lancamentos, error } = await supabase
            .from('movimentacoes')
            .select('*')
            .eq('clientid', cliente.clientid)
            .order('created_at', { ascending: false })
            .range(offset, offset + limite - 1);

        if (error) throw error;

        res.json({
            success: true,
            cliente: {
                clientid: cliente.clientid,
                whatsapp
            },
            lancamentos,
            total: lancamentos.length
        });

    } catch (error) {
        console.error('Erro ao buscar lançamentos por WhatsApp:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro interno do servidor' 
        });
    }
});

// 6. INSERIR NOVO LANÇAMENTO
app.post('/api/lancamentos', authenticateToken, async (req, res) => {
    try {
        const { data_movimentacao, valor_movimentacao, type, category, observation } = req.body;

        if (!data_movimentacao || !valor_movimentacao || !type) {
            return res.status(400).json({ 
                success: false, 
                message: 'Campos obrigatórios: data, valor e tipo' 
            });
        }

        const novoLancamento = {
            id: `mov_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
            data_movimentacao,
            valor_movimentacao: parseFloat(valor_movimentacao),
            clientid: req.user.clientid,
            type,
            category: category || 'outros',
            observation: observation || '',
            created_at: new Date().toISOString()
        };

        const { data, error } = await supabase
            .from('movimentacoes')
            .insert(novoLancamento)
            .select()
            .single();

        if (error) throw error;

        res.json({
            success: true,
            message: 'Lançamento criado com sucesso',
            lancamento: data
        });

    } catch (error) {
        console.error('Erro ao inserir lançamento:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro interno do servidor' 
        });
    }
});

// 7. MIDDLEWARE DE LOGS
app.use((req, res, next) => {
    console.log(`${new Date().toISOString()} - ${req.method} ${req.path}`);
    next();
});

// 8. ROTA DE TESTE
app.get('/api/test', (req, res) => {
    res.json({ 
        success: true, 
        message: 'API funcionando',
        timestamp: new Date().toISOString()
    });
});

// 9. CONFIGURAÇÃO DO SERVIDOR
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Servidor rodando na porta ${PORT}`);
    console.log(`API disponível em: http://localhost:${PORT}/api`);
});

module.exports = app;
