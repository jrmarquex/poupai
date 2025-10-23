// Sistema de Autenticação Híbrido - GitHub Pages + Hospedagem Real
// Este arquivo detecta automaticamente o ambiente e funciona em ambos

class PoupaAiAuth {
    constructor() {
        this.isProduction = false; // Será detectado automaticamente
        this.currentUser = null;
        this.isAuthenticated = false;
        
        // URLs das APIs (será detectado automaticamente)
        this.apiBaseUrl = this.detectApiUrl();
        
        // Dados de demonstração para GitHub Pages
        this.demoData = this.getDemoData();
    }

    // Detectar se está em produção (hospedagem real) ou GitHub Pages
    detectApiUrl() {
        const hostname = window.location.hostname;
        
        // Se estiver em GitHub Pages
        if (hostname.includes('github.io') || hostname.includes('localhost')) {
            this.isProduction = false;
            return null; // Não há APIs PHP no GitHub Pages
        }
        
        // Se estiver em hospedagem real
        this.isProduction = true;
        return 'https://seudominio.com/api'; // Substitua pela URL real da sua hospedagem
    }

    // Dados de demonstração para GitHub Pages
    getDemoData() {
        return {
            admin: {
                clientid: 'demo-admin-001',
                nome: 'Francisco Marques de Oliveira Junior',
                email: 'frmarques.oli@gmail.com',
                whatsapp: '5511997245501',
                primeiro_acesso: false
            },
            dashboard: {
                resumo: {
                    ganhos: {
                        valor: 3500.00,
                        variacao: 12,
                        formatted: 'R$ 3.500,00'
                    },
                    gastos: {
                        valor: 1290.00,
                        variacao: 8,
                        formatted: 'R$ 1.290,00'
                    },
                    saldo_liquido: {
                        valor: 2210.00,
                        formatted: 'R$ 2.210,00'
                    },
                    total_transacoes: 45
                },
                gastos_por_categoria: [
                    { categoria: 'alimentação', valor: 450.00, formatted: 'R$ 450,00' },
                    { categoria: 'transporte', valor: 320.00, formatted: 'R$ 320,00' },
                    { categoria: 'lazer', valor: 280.00, formatted: 'R$ 280,00' },
                    { categoria: 'outros', valor: 240.00, formatted: 'R$ 240,00' }
                ],
                evolucao_financeira: [
                    { data: '2024-11-23', receitas: 0, despesas: 89.18, saldo_dia: -89.18 },
                    { data: '2024-11-24', receitas: 0, despesas: 42.90, saldo_dia: -42.90 },
                    { data: '2024-11-25', receitas: 850.00, despesas: 25.00, saldo_dia: 825.00 },
                    { data: '2024-11-26', receitas: 0, despesas: 70.00, saldo_dia: -70.00 },
                    { data: '2024-11-27', receitas: 2800.00, despesas: 0, saldo_dia: 2800.00 },
                    { data: '2024-11-28', receitas: 0, despesas: 150.00, saldo_dia: -150.00 },
                    { data: '2024-11-29', receitas: 0, despesas: 200.00, saldo_dia: -200.00 }
                ],
                atividades_recentes: [
                    {
                        id: '001',
                        data: '2024-11-29',
                        valor: 200.00,
                        tipo: 'Despesa',
                        categoria: 'outros',
                        observacao: 'Compra de material de escritório',
                        formatted_valor: 'R$ 200,00',
                        formatted_data: '29/11/2024'
                    },
                    {
                        id: '002',
                        data: '2024-11-28',
                        valor: 150.00,
                        tipo: 'Despesa',
                        categoria: 'lazer',
                        observacao: 'Cinema e pipoca',
                        formatted_valor: 'R$ 150,00',
                        formatted_data: '28/11/2024'
                    },
                    {
                        id: '003',
                        data: '2024-11-27',
                        valor: 2800.00,
                        tipo: 'Receita',
                        categoria: 'outros',
                        observacao: 'Salário CLT',
                        formatted_valor: 'R$ 2.800,00',
                        formatted_data: '27/11/2024'
                    },
                    {
                        id: '004',
                        data: '2024-11-26',
                        valor: 70.00,
                        tipo: 'Despesa',
                        categoria: 'alimentação',
                        observacao: 'Pizza',
                        formatted_valor: 'R$ 70,00',
                        formatted_data: '26/11/2024'
                    },
                    {
                        id: '005',
                        data: '2024-11-25',
                        valor: 850.00,
                        tipo: 'Receita',
                        categoria: 'outros',
                        observacao: 'Freelance Design',
                        formatted_valor: 'R$ 850,00',
                        formatted_data: '25/11/2024'
                    }
                ]
            },
            transacoes: [
                {
                    id: '001',
                    data: '2024-11-29',
                    valor: 200.00,
                    tipo: 'Despesa',
                    categoria: 'outros',
                    observacao: 'Compra de material de escritório',
                    formatted_valor: 'R$ 200,00',
                    formatted_data: '29/11/2024'
                },
                {
                    id: '002',
                    data: '2024-11-28',
                    valor: 150.00,
                    tipo: 'Despesa',
                    categoria: 'lazer',
                    observacao: 'Cinema e pipoca',
                    formatted_valor: 'R$ 150,00',
                    formatted_data: '28/11/2024'
                },
                {
                    id: '003',
                    data: '2024-11-27',
                    valor: 2800.00,
                    tipo: 'Receita',
                    categoria: 'outros',
                    observacao: 'Salário CLT',
                    formatted_valor: 'R$ 2.800,00',
                    formatted_data: '27/11/2024'
                },
                {
                    id: '004',
                    data: '2024-11-26',
                    valor: 70.00,
                    tipo: 'Despesa',
                    categoria: 'alimentação',
                    observacao: 'Pizza',
                    formatted_valor: 'R$ 70,00',
                    formatted_data: '26/11/2024'
                },
                {
                    id: '005',
                    data: '2024-11-25',
                    valor: 850.00,
                    tipo: 'Receita',
                    categoria: 'outros',
                    observacao: 'Freelance Design',
                    formatted_valor: 'R$ 850,00',
                    formatted_data: '25/11/2024'
                },
                {
                    id: '006',
                    data: '2024-11-24',
                    valor: 42.90,
                    tipo: 'Despesa',
                    categoria: 'alimentação',
                    observacao: 'iFood - Jantar',
                    formatted_valor: 'R$ 42,90',
                    formatted_data: '24/11/2024'
                },
                {
                    id: '007',
                    data: '2024-11-23',
                    valor: 25.00,
                    tipo: 'Despesa',
                    categoria: 'transporte',
                    observacao: 'Uber - Centro',
                    formatted_valor: 'R$ 25,00',
                    formatted_data: '23/11/2024'
                },
                {
                    id: '008',
                    data: '2024-11-22',
                    valor: 89.18,
                    tipo: 'Despesa',
                    categoria: 'alimentação',
                    observacao: 'iFood',
                    formatted_valor: 'R$ 89,18',
                    formatted_data: '22/11/2024'
                }
            ]
        };
    }

    // Sistema de login híbrido
    async login(usuario, senha) {
        // Verificar credenciais de demonstração
        if (usuario === 'admin' && senha === '1234') {
            this.isAuthenticated = true;
            this.currentUser = this.demoData.admin;
            
            // Salvar no localStorage para persistir
            localStorage.setItem('poupai_demo_user', JSON.stringify(this.currentUser));
            localStorage.setItem('poupai_demo_auth', 'true');
            
            return {
                success: true,
                cliente: this.currentUser,
                message: 'Login realizado com sucesso (Modo Demonstração)',
                isDemo: true
            };
        }

        // Se estiver em produção, tentar login real
        if (this.isProduction && this.apiBaseUrl) {
            try {
                const response = await fetch(`${this.apiBaseUrl}/auth-real.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        usuario: usuario,
                        senha: senha
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.isAuthenticated = true;
                    this.currentUser = data.cliente;
                    return {
                        success: true,
                        cliente: data.cliente,
                        message: data.message,
                        isDemo: false
                    };
                } else {
                    return {
                        success: false,
                        message: data.message,
                        isDemo: false
                    };
                }
            } catch (error) {
                console.error('Erro no login real:', error);
                return {
                    success: false,
                    message: 'Erro de conexão com o servidor.',
                    isDemo: false
                };
            }
        }

        // Credenciais inválidas
        return {
            success: false,
            message: 'Usuário ou senha incorretos.',
            isDemo: false
        };
    }

    // Carregar dashboard híbrido
    async carregarDashboard() {
        // Se estiver logado em modo demonstração
        if (this.isAuthenticated && !this.isProduction) {
            return {
                success: true,
                cliente: this.currentUser,
                dashboard: this.demoData.dashboard,
                message: 'Dashboard carregado (Modo Demonstração)',
                isDemo: true
            };
        }

        // Se estiver em produção, carregar dados reais
        if (this.isProduction && this.apiBaseUrl) {
            try {
                const response = await fetch(`${this.apiBaseUrl}/dashboard-real.php`, {
                    method: 'GET',
                    credentials: 'include'
                });

                const data = await response.json();

                if (data.success) {
                    return {
                        success: true,
                        cliente: data.cliente,
                        dashboard: data.dashboard,
                        message: data.message,
                        isDemo: false
                    };
                } else {
                    return {
                        success: false,
                        message: data.message,
                        isDemo: false
                    };
                }
            } catch (error) {
                console.error('Erro ao carregar dashboard real:', error);
                return {
                    success: false,
                    message: 'Erro de conexão com o servidor.',
                    isDemo: false
                };
            }
        }

        return {
            success: false,
            message: 'Sistema não configurado.',
            isDemo: false
        };
    }

    // Carregar transações híbrido
    async carregarTransacoes(filtros = {}) {
        // Se estiver em modo demonstração
        if (!this.isProduction) {
            let transacoes = [...this.demoData.transacoes];

            // Aplicar filtros básicos
            if (filtros.tipo) {
                transacoes = transacoes.filter(t => t.tipo === filtros.tipo);
            }
            if (filtros.categoria) {
                transacoes = transacoes.filter(t => t.categoria === filtros.categoria);
            }
            if (filtros.busca) {
                transacoes = transacoes.filter(t => 
                    t.observacao.toLowerCase().includes(filtros.busca.toLowerCase())
                );
            }

            // Aplicar paginação
            const limite = filtros.limite || 20;
            const offset = filtros.offset || 0;
            const paginadas = transacoes.slice(offset, offset + limite);

            return {
                success: true,
                transacoes: paginadas,
                paginacao: {
                    total_registros: transacoes.length,
                    limite: limite,
                    offset: offset,
                    pagina_atual: Math.floor(offset / limite) + 1,
                    total_paginas: Math.ceil(transacoes.length / limite),
                    tem_proxima: (offset + limite) < transacoes.length,
                    tem_anterior: offset > 0
                },
                totais: {
                    receitas: 3650.00,
                    despesas: 1290.00,
                    saldo_liquido: 2360.00,
                    formatted_receitas: 'R$ 3.650,00',
                    formatted_despesas: 'R$ 1.290,00',
                    formatted_saldo: 'R$ 2.360,00'
                },
                message: 'Transações carregadas (Modo Demonstração)',
                isDemo: true
            };
        }

        // Se estiver em produção, usar API real
        if (this.isProduction && this.apiBaseUrl) {
            try {
                const params = new URLSearchParams();
                if (filtros.limite) params.append('limite', filtros.limite);
                if (filtros.offset) params.append('offset', filtros.offset);
                if (filtros.tipo) params.append('tipo', filtros.tipo);
                if (filtros.categoria) params.append('categoria', filtros.categoria);
                if (filtros.busca) params.append('busca', filtros.busca);

                const response = await fetch(`${this.apiBaseUrl}/transacoes-real.php?${params}`, {
                    method: 'GET',
                    credentials: 'include'
                });

                const data = await response.json();

                if (data.success) {
                    return {
                        success: true,
                        transacoes: data.transacoes,
                        paginacao: data.paginacao,
                        totais: data.totais,
                        message: data.message,
                        isDemo: false
                    };
                } else {
                    return {
                        success: false,
                        message: data.message,
                        isDemo: false
                    };
                }
            } catch (error) {
                console.error('Erro ao carregar transações reais:', error);
                return {
                    success: false,
                    message: 'Erro de conexão com o servidor.',
                    isDemo: false
                };
            }
        }

        return {
            success: false,
            message: 'Sistema não configurado.',
            isDemo: false
        };
    }

    // Verificar se está logado
    verificarLogin() {
        // Verificar localStorage para modo demonstração
        const demoAuth = localStorage.getItem('poupai_demo_auth');
        const demoUser = localStorage.getItem('poupai_demo_user');

        if (demoAuth === 'true' && demoUser) {
            this.isAuthenticated = true;
            this.currentUser = JSON.parse(demoUser);
            return true;
        }

        return false;
    }

    // Logout
    logout() {
        this.isAuthenticated = false;
        this.currentUser = null;
        
        // Limpar localStorage
        localStorage.removeItem('poupai_demo_auth');
        localStorage.removeItem('poupai_demo_user');
        
        return {
            success: true,
            message: 'Logout realizado com sucesso.'
        };
    }

    // Obter informações do ambiente
    getEnvironmentInfo() {
        return {
            isProduction: this.isProduction,
            apiBaseUrl: this.apiBaseUrl,
            hostname: window.location.hostname,
            isDemo: !this.isProduction
        };
    }
}

// Instância global
window.PoupaAiAuth = new PoupaAiAuth();

// Funções de conveniência para o frontend
window.fazerLogin = async function(usuario, senha) {
    return await window.PoupaAiAuth.login(usuario, senha);
};

window.carregarDashboard = async function() {
    return await window.PoupaAiAuth.carregarDashboard();
};

window.carregarTransacoes = async function(filtros) {
    return await window.PoupaAiAuth.carregarTransacoes(filtros);
};

window.verificarLogin = function() {
    return window.PoupaAiAuth.verificarLogin();
};

window.fazerLogout = function() {
    return window.PoupaAiAuth.logout();
};

// Inicialização automática
document.addEventListener('DOMContentLoaded', function() {
    const envInfo = window.PoupaAiAuth.getEnvironmentInfo();
    
    if (envInfo.isDemo) {
        console.log('🎭 Sistema em Modo Demonstração (GitHub Pages)');
        console.log('📝 Login: admin / 1234');
    } else {
        console.log('🚀 Sistema em Modo Produção (Hospedagem Real)');
    }
    
    // Verificar se já está logado
    if (window.PoupaAiAuth.verificarLogin()) {
        console.log('✅ Usuário já logado:', window.PoupaAiAuth.currentUser.nome);
    }
});
