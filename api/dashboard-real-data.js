// Dashboard API - Busca dados reais das movimentações do cliente admin (5511997245501)
const { createClient } = require('@supabase/supabase-js');
require('dotenv').config();

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

    // Para demo, aceitar qualquer token ou admin
    req.user = { clientid: 'admin', whatsapp: '5511997245501', isAdmin: true };
    next();
};

// 1. DADOS DO DASHBOARD PRINCIPAL
app.get('/api/dashboard', authenticateToken, async (req, res) => {
    try {
        const whatsappAdmin = '5511997245501';
        
        // Buscar cliente admin
        const { data: cliente, error: clienteError } = await supabase
            .from('clientes')
            .select('clientid, whatsapp, nome')
            .eq('whatsapp', whatsappAdmin)
            .single();

        if (clienteError && clienteError.code !== 'PGRST116') {
            throw clienteError;
        }

        if (!cliente) {
            return res.status(404).json({ 
                success: false, 
                message: 'Cliente admin não encontrado' 
            });
        }

        // Buscar todas as movimentações do cliente admin
        const { data: movimentacoes, error: movError } = await supabase
            .from('movimentacoes')
            .select('*')
            .eq('clientid', cliente.clientid)
            .order('created_at', { ascending: false });

        if (movError) throw movError;

        // Calcular dados do dashboard
        const dadosDashboard = calcularDadosDashboard(movimentacoes || []);

        res.json({
            success: true,
            cliente: {
                clientid: cliente.clientid,
                whatsapp: cliente.whatsapp,
                nome: cliente.nome
            },
            dashboard: dadosDashboard
        });

    } catch (error) {
        console.error('Erro ao buscar dados do dashboard:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro interno do servidor' 
        });
    }
});

// 2. ATIVIDADES RECENTES
app.get('/api/dashboard/atividades-recentes', authenticateToken, async (req, res) => {
    try {
        const whatsappAdmin = '5511997245501';
        const limite = parseInt(req.query.limite) || 10;

        // Buscar cliente admin
        const { data: cliente, error: clienteError } = await supabase
            .from('clientes')
            .select('clientid')
            .eq('whatsapp', whatsappAdmin)
            .single();

        if (clienteError || !cliente) {
            return res.status(404).json({ 
                success: false, 
                message: 'Cliente admin não encontrado' 
            });
        }

        // Buscar movimentações recentes
        const { data: movimentacoes, error: movError } = await supabase
            .from('movimentacoes')
            .select('*')
            .eq('clientid', cliente.clientid)
            .order('created_at', { ascending: false })
            .limit(limite);

        if (movError) throw movError;

        // Formatar atividades recentes
        const atividades = (movimentacoes || []).map(mov => ({
            id: mov.id,
            tipo: mov.type,
            categoria: mov.category,
            observacao: mov.observation,
            valor: parseFloat(mov.valor_movimentacao),
            data: mov.data_movimentacao,
            hora: new Date(mov.created_at).toLocaleTimeString('pt-BR', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            }),
            status: 'Concluído' // Por enquanto todos como concluído
        }));

        res.json({
            success: true,
            atividades,
            total: atividades.length
        });

    } catch (error) {
        console.error('Erro ao buscar atividades recentes:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro interno do servidor' 
        });
    }
});

// 3. GASTOS POR CATEGORIA
app.get('/api/dashboard/gastos-categoria', authenticateToken, async (req, res) => {
    try {
        const whatsappAdmin = '5511997245501';
        const periodo = req.query.periodo || 'mes'; // mes, trimestre, ano

        // Buscar cliente admin
        const { data: cliente, error: clienteError } = await supabase
            .from('clientes')
            .select('clientid')
            .eq('whatsapp', whatsappAdmin)
            .single();

        if (clienteError || !cliente) {
            return res.status(404).json({ 
                success: false, 
                message: 'Cliente admin não encontrado' 
            });
        }

        // Calcular data de início baseada no período
        const dataInicio = calcularDataInicio(periodo);

        // Buscar movimentações do período
        const { data: movimentacoes, error: movError } = await supabase
            .from('movimentacoes')
            .select('*')
            .eq('clientid', cliente.clientid)
            .eq('type', 'despesa')
            .gte('data_movimentacao', dataInicio);

        if (movError) throw movError;

        // Agrupar por categoria
        const gastosPorCategoria = agruparPorCategoria(movimentacoes || []);

        res.json({
            success: true,
            periodo,
            gastosPorCategoria,
            totalGastos: gastosPorCategoria.reduce((sum, cat) => sum + cat.valor, 0)
        });

    } catch (error) {
        console.error('Erro ao buscar gastos por categoria:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro interno do servidor' 
        });
    }
});

// 4. EVOLUÇÃO FINANCEIRA
app.get('/api/dashboard/evolucao-financeira', authenticateToken, async (req, res) => {
    try {
        const whatsappAdmin = '5511997245501';
        const periodo = req.query.periodo || '7dias'; // 7dias, 30dias, 90dias

        // Buscar cliente admin
        const { data: cliente, error: clienteError } = await supabase
            .from('clientes')
            .select('clientid')
            .eq('whatsapp', whatsappAdmin)
            .single();

        if (clienteError || !cliente) {
            return res.status(404).json({ 
                success: false, 
                message: 'Cliente admin não encontrado' 
            });
        }

        // Calcular data de início baseada no período
        const dataInicio = calcularDataInicioEvolucao(periodo);

        // Buscar movimentações do período
        const { data: movimentacoes, error: movError } = await supabase
            .from('movimentacoes')
            .select('*')
            .eq('clientid', cliente.clientid)
            .gte('data_movimentacao', dataInicio)
            .order('data_movimentacao', { ascending: true });

        if (movError) throw movError;

        // Agrupar por período (dia, semana, mês)
        const evolucao = agruparPorPeriodo(movimentacoes || [], periodo);

        res.json({
            success: true,
            periodo,
            evolucao,
            labels: evolucao.map(item => item.label),
            receitas: evolucao.map(item => item.receitas),
            despesas: evolucao.map(item => item.despesas)
        });

    } catch (error) {
        console.error('Erro ao buscar evolução financeira:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro interno do servidor' 
        });
    }
});

// FUNÇÕES AUXILIARES

function calcularDadosDashboard(movimentacoes) {
    const receitas = movimentacoes.filter(m => m.type === 'receita');
    const despesas = movimentacoes.filter(m => m.type === 'despesa');

    const totalReceitas = receitas.reduce((sum, m) => sum + parseFloat(m.valor_movimentacao), 0);
    const totalDespesas = despesas.reduce((sum, m) => sum + parseFloat(m.valor_movimentacao), 0);
    const saldoLiquido = totalReceitas - totalDespesas;

    // Calcular variação do mês anterior (simulação)
    const variacaoReceitas = 12; // +12%
    const variacaoDespesas = 8;  // +8%

    return {
        ganhos: {
            valor: totalReceitas,
            variacao: variacaoReceitas,
            variacaoTexto: `+${variacaoReceitas}% este mês`
        },
        gastos: {
            valor: totalDespesas,
            variacao: variacaoDespesas,
            variacaoTexto: `+${variacaoDespesas}% este mês`
        },
        saldoLiquido: {
            valor: saldoLiquido,
            descricao: 'Ganhos - Gastos'
        },
        totalTransacoes: movimentacoes.length,
        ultimaAtualizacao: new Date().toLocaleString('pt-BR')
    };
}

function calcularDataInicio(periodo) {
    const hoje = new Date();
    switch (periodo) {
        case 'mes':
            return new Date(hoje.getFullYear(), hoje.getMonth(), 1).toISOString().split('T')[0];
        case 'trimestre':
            const trimestreAtual = Math.floor(hoje.getMonth() / 3);
            return new Date(hoje.getFullYear(), trimestreAtual * 3, 1).toISOString().split('T')[0];
        case 'ano':
            return new Date(hoje.getFullYear(), 0, 1).toISOString().split('T')[0];
        default:
            return new Date(hoje.getFullYear(), hoje.getMonth(), 1).toISOString().split('T')[0];
    }
}

function calcularDataInicioEvolucao(periodo) {
    const hoje = new Date();
    switch (periodo) {
        case '7dias':
            return new Date(hoje.getTime() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        case '30dias':
            return new Date(hoje.getTime() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        case '90dias':
            return new Date(hoje.getTime() - 90 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        default:
            return new Date(hoje.getTime() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    }
}

function agruparPorCategoria(movimentacoes) {
    const categorias = {};
    
    movimentacoes.forEach(mov => {
        const categoria = mov.category || 'outros';
        if (!categorias[categoria]) {
            categorias[categoria] = 0;
        }
        categorias[categoria] += parseFloat(mov.valor_movimentacao);
    });

    return Object.entries(categorias).map(([categoria, valor]) => ({
        categoria,
        valor: parseFloat(valor.toFixed(2))
    })).sort((a, b) => b.valor - a.valor);
}

function agruparPorPeriodo(movimentacoes, periodo) {
    const grupos = {};
    
    movimentacoes.forEach(mov => {
        const data = new Date(mov.data_movimentacao);
        let chave;
        
        switch (periodo) {
            case '7dias':
            case '30dias':
            case '90dias':
                chave = data.toLocaleDateString('pt-BR', { weekday: 'short' });
                break;
            default:
                chave = data.toLocaleDateString('pt-BR');
        }
        
        if (!grupos[chave]) {
            grupos[chave] = { receitas: 0, despesas: 0 };
        }
        
        if (mov.type === 'receita') {
            grupos[chave].receitas += parseFloat(mov.valor_movimentacao);
        } else {
            grupos[chave].despesas += parseFloat(mov.valor_movimentacao);
        }
    });

    return Object.entries(grupos).map(([label, dados]) => ({
        label,
        receitas: parseFloat(dados.receitas.toFixed(2)),
        despesas: parseFloat(dados.despesas.toFixed(2))
    }));
}

// Exportar para uso em outros arquivos
module.exports = {
    calcularDadosDashboard,
    calcularDataInicio,
    calcularDataInicioEvolucao,
    agruparPorCategoria,
    agruparPorPeriodo
};
