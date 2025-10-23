// Sistema de APIs PHP Reais para Poupa.Ai
// Este arquivo conecta o frontend com as APIs PHP que consultam o banco de dados real

class PoupaAiAPI {
    constructor() {
        // URL base das APIs PHP (ajuste conforme sua hospedagem)
        this.apiBaseUrl = 'https://seudominio.com/api'; // Substitua pela URL real
        
        // Para desenvolvimento local, use:
        // this.apiBaseUrl = 'http://localhost/api';
        
        this.isAuthenticated = false;
        this.currentUser = null;
    }

    // ========================================
    // SISTEMA DE AUTENTICAÇÃO
    // ========================================
    
    async login(usuario, senha) {
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
                
                // Salvar token no localStorage (opcional)
                if (data.token) {
                    localStorage.setItem('poupai_token', data.token);
                }
                
                return {
                    success: true,
                    cliente: data.cliente,
                    message: data.message
                };
            } else {
                return {
                    success: false,
                    message: data.message
                };
            }
        } catch (error) {
            console.error('Erro no login:', error);
            return {
                success: false,
                message: 'Erro de conexão com o servidor.'
            };
        }
    }

    async logout() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/auth-real.php`, {
                method: 'DELETE'
            });

            const data = await response.json();

            this.isAuthenticated = false;
            this.currentUser = null;
            localStorage.removeItem('poupai_token');

            return {
                success: true,
                message: data.message
            };
        } catch (error) {
            console.error('Erro no logout:', error);
            return {
                success: false,
                message: 'Erro de conexão com o servidor.'
            };
        }
    }

    async verificarSessao() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/auth-real.php`, {
                method: 'GET'
            });

            const data = await response.json();

            if (data.success) {
                this.isAuthenticated = true;
                this.currentUser = data.cliente;
                return true;
            } else {
                this.isAuthenticated = false;
                this.currentUser = null;
                return false;
            }
        } catch (error) {
            console.error('Erro ao verificar sessão:', error);
            this.isAuthenticated = false;
            this.currentUser = null;
            return false;
        }
    }

    // ========================================
    // DASHBOARD - DADOS PRINCIPAIS
    // ========================================
    
    async carregarDashboard() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/dashboard-real.php`, {
                method: 'GET',
                credentials: 'include' // Para manter sessão
            });

            const data = await response.json();

            if (data.success) {
                return {
                    success: true,
                    cliente: data.cliente,
                    dashboard: data.dashboard,
                    message: data.message
                };
            } else {
                return {
                    success: false,
                    message: data.message
                };
            }
        } catch (error) {
            console.error('Erro ao carregar dashboard:', error);
            return {
                success: false,
                message: 'Erro de conexão com o servidor.'
            };
        }
    }

    // ========================================
    // TRANSAÇÕES - LISTA E FILTROS
    // ========================================
    
    async carregarTransacoes(filtros = {}) {
        try {
            const params = new URLSearchParams();
            
            // Adicionar filtros aos parâmetros
            if (filtros.limite) params.append('limite', filtros.limite);
            if (filtros.offset) params.append('offset', filtros.offset);
            if (filtros.tipo) params.append('tipo', filtros.tipo);
            if (filtros.categoria) params.append('categoria', filtros.categoria);
            if (filtros.data_inicio) params.append('data_inicio', filtros.data_inicio);
            if (filtros.data_fim) params.append('data_fim', filtros.data_fim);
            if (filtros.busca) params.append('busca', filtros.busca);
            if (filtros.ordenacao) params.append('ordenacao', filtros.ordenacao);

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
                    filtros: data.filtros,
                    estatisticas_periodo: data.estatisticas_periodo,
                    message: data.message
                };
            } else {
                return {
                    success: false,
                    message: data.message
                };
            }
        } catch (error) {
            console.error('Erro ao carregar transações:', error);
            return {
                success: false,
                message: 'Erro de conexão com o servidor.'
            };
        }
    }

    // ========================================
    // RELATÓRIOS - ANÁLISES DETALHADAS
    // ========================================
    
    async gerarRelatorio(filtros = {}) {
        try {
            const params = new URLSearchParams();
            
            // Adicionar filtros aos parâmetros
            if (filtros.periodo) params.append('periodo', filtros.periodo);
            if (filtros.data_inicio) params.append('data_inicio', filtros.data_inicio);
            if (filtros.data_fim) params.append('data_fim', filtros.data_fim);
            if (filtros.tipo_lancamento) params.append('tipo_lancamento', filtros.tipo_lancamento);
            if (filtros.categoria) params.append('categoria', filtros.categoria);
            if (filtros.formato_grafico) params.append('formato_grafico', filtros.formato_grafico);

            const response = await fetch(`${this.apiBaseUrl}/relatorios-real.php?${params}`, {
                method: 'GET',
                credentials: 'include'
            });

            const data = await response.json();

            if (data.success) {
                return {
                    success: true,
                    resumo: data.resumo,
                    despesas_por_categoria: data.despesas_por_categoria,
                    receitas_por_categoria: data.receitas_por_categoria,
                    tendencia_temporal: data.tendencia_temporal,
                    comparacao_anterior: data.comparacao_anterior,
                    maiores_transacoes: data.maiores_transacoes,
                    dias_mais_ativos: data.dias_mais_ativos,
                    message: data.message
                };
            } else {
                return {
                    success: false,
                    message: data.message
                };
            }
        } catch (error) {
            console.error('Erro ao gerar relatório:', error);
            return {
                success: false,
                message: 'Erro de conexão com o servidor.'
            };
        }
    }

    // ========================================
    // UTILITÁRIOS
    // ========================================
    
    formatarMoeda(valor) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(valor);
    }

    formatarData(data) {
        return new Date(data).toLocaleDateString('pt-BR');
    }

    formatarDataHora(data) {
        return new Date(data).toLocaleString('pt-BR');
    }

    // Verificar se está conectado ao backend real
    async testarConexao() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/auth-real.php`, {
                method: 'GET'
            });
            
            return response.ok;
        } catch (error) {
            console.error('Erro ao testar conexão:', error);
            return false;
        }
    }
}

// ========================================
// INTEGRAÇÃO COM O FRONTEND EXISTENTE
// ========================================

// Substituir as funções existentes pelas novas APIs
window.PoupaAiAPI = PoupaAiAPI;

// Função para inicializar o sistema com dados reais
async function inicializarSistemaReal() {
    const api = new PoupaAiAPI();
    
    // Testar conexão
    const conectado = await api.testarConexao();
    
    if (!conectado) {
        console.warn('Backend PHP não disponível. Usando dados de demonstração.');
        return false;
    }
    
    // Verificar se usuário está logado
    const logado = await api.verificarSessao();
    
    if (!logado) {
        console.log('Usuário não logado. Redirecionando para login.');
        // Redirecionar para login se necessário
        return false;
    }
    
    console.log('Sistema conectado ao backend PHP real.');
    return true;
}

// Função para fazer login com dados reais
async function fazerLoginReal(usuario, senha) {
    const api = new PoupaAiAPI();
    const resultado = await api.login(usuario, senha);
    
    if (resultado.success) {
        console.log('Login realizado com sucesso:', resultado.cliente);
        
        // Atualizar interface com dados reais
        await atualizarDashboardReal();
        
        return true;
    } else {
        console.error('Erro no login:', resultado.message);
        return false;
    }
}

// Função para carregar dashboard com dados reais
async function atualizarDashboardReal() {
    const api = new PoupaAiAPI();
    const dados = await api.carregarDashboard();
    
    if (dados.success) {
        console.log('Dashboard carregado:', dados.dashboard);
        
        // Atualizar elementos da interface
        atualizarElementosDashboard(dados.dashboard);
        
        return true;
    } else {
        console.error('Erro ao carregar dashboard:', dados.message);
        return false;
    }
}

// Função para atualizar elementos do dashboard
function atualizarElementosDashboard(dashboard) {
    // Atualizar resumo financeiro
    if (dashboard.resumo) {
        const ganhosElement = document.querySelector('[data-ganhos]');
        const gastosElement = document.querySelector('[data-gastos]');
        const saldoElement = document.querySelector('[data-saldo]');
        
        if (ganhosElement) {
            ganhosElement.textContent = dashboard.resumo.ganhos.formatted;
        }
        
        if (gastosElement) {
            gastosElement.textContent = dashboard.resumo.gastos.formatted;
        }
        
        if (saldoElement) {
            saldoElement.textContent = dashboard.resumo.saldo_liquido.formatted;
        }
    }
    
    // Atualizar gráfico de gastos por categoria
    if (dashboard.gastos_por_categoria && window.Chart) {
        atualizarGraficoCategorias(dashboard.gastos_por_categoria);
    }
    
    // Atualizar gráfico de evolução financeira
    if (dashboard.evolucao_financeira && window.Chart) {
        atualizarGraficoEvolucao(dashboard.evolucao_financeira);
    }
    
    // Atualizar atividades recentes
    if (dashboard.atividades_recentes) {
        atualizarAtividadesRecentes(dashboard.atividades_recentes);
    }
}

// Função para atualizar gráfico de categorias
function atualizarGraficoCategorias(dados) {
    const ctx = document.getElementById('grafico-categorias');
    if (!ctx) return;
    
    const labels = dados.map(item => item.categoria);
    const valores = dados.map(item => item.valor);
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: valores,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Função para atualizar gráfico de evolução
function atualizarGraficoEvolucao(dados) {
    const ctx = document.getElementById('grafico-evolucao');
    if (!ctx) return;
    
    const labels = dados.map(item => item.data);
    const receitas = dados.map(item => item.receitas);
    const despesas = dados.map(item => item.despesas);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Receitas',
                data: receitas,
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                tension: 0.1
            }, {
                label: 'Despesas',
                data: despesas,
                borderColor: '#F44336',
                backgroundColor: 'rgba(244, 67, 54, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Função para atualizar atividades recentes
function atualizarAtividadesRecentes(atividades) {
    const container = document.getElementById('atividades-recentes');
    if (!container) return;
    
    container.innerHTML = '';
    
    atividades.forEach(atividade => {
        const item = document.createElement('div');
        item.className = 'atividade-item';
        item.innerHTML = `
            <div class="atividade-tipo ${atividade.tipo.toLowerCase()}">
                ${atividade.tipo}
            </div>
            <div class="atividade-detalhes">
                <div class="atividade-valor">${atividade.formatted_valor}</div>
                <div class="atividade-categoria">${atividade.categoria}</div>
                <div class="atividade-data">${atividade.formatted_data}</div>
            </div>
        `;
        container.appendChild(item);
    });
}

// ========================================
// INICIALIZAÇÃO AUTOMÁTICA
// ========================================

// Inicializar quando a página carregar
document.addEventListener('DOMContentLoaded', async function() {
    console.log('Inicializando sistema Poupa.Ai com APIs PHP reais...');
    
    // Tentar conectar ao backend real
    const conectado = await inicializarSistemaReal();
    
    if (conectado) {
        console.log('✅ Sistema conectado ao backend PHP real');
    } else {
        console.log('⚠️ Usando dados de demonstração');
    }
});

// Exportar para uso global
window.PoupaAiAPI = PoupaAiAPI;
window.fazerLoginReal = fazerLoginReal;
window.atualizarDashboardReal = atualizarDashboardReal;
