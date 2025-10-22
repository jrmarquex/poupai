// Script para buscar lançamentos do cliente 5511997245501
// Este script demonstra como integrar com o banco de dados

// Configuração da API (ajuste conforme sua implementação)
const API_BASE_URL = 'https://your-api-domain.com/api';

// Função para buscar lançamentos do cliente
async function buscarLancamentosCliente(clientid, limite = 50, offset = 0) {
    try {
        const response = await fetch(`${API_BASE_URL}/lancamentos/${clientid}?limite=${limite}&offset=${offset}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erro ao buscar lançamentos:', error);
        throw error;
    }
}

// Função para autenticar cliente
async function autenticarCliente(whatsapp, senha) {
    try {
        const response = await fetch(`${API_BASE_URL}/auth/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                whatsapp: whatsapp,
                senha: senha
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erro ao autenticar:', error);
        throw error;
    }
}

// Função para verificar se é primeiro acesso
async function verificarPrimeiroAcesso(whatsapp) {
    try {
        const response = await fetch(`${API_BASE_URL}/auth/check-first-access`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                whatsapp: whatsapp
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erro ao verificar primeiro acesso:', error);
        throw error;
    }
}

// Função para registrar primeiro acesso
async function registrarPrimeiroAcesso(dadosCliente) {
    try {
        const response = await fetch(`${API_BASE_URL}/auth/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dadosCliente)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erro ao registrar primeiro acesso:', error);
        throw error;
    }
}

// Exemplo de uso para o cliente específico
async function exemploUsoCliente() {
    const whatsappCliente = '5511997245501';
    
    try {
        // 1. Verificar se é primeiro acesso
        console.log('Verificando primeiro acesso...');
        const primeiroAcesso = await verificarPrimeiroAcesso(whatsappCliente);
        
        if (primeiroAcesso.isFirstAccess) {
            console.log('Cliente precisa fazer primeiro acesso');
            // Redirecionar para página de primeiro acesso
            return;
        }
        
        // 2. Autenticar cliente (simulação)
        console.log('Autenticando cliente...');
        const authResult = await autenticarCliente(whatsappCliente, 'senha_exemplo');
        
        if (!authResult.success) {
            console.log('Falha na autenticação');
            return;
        }
        
        // 3. Buscar lançamentos
        console.log('Buscando lançamentos...');
        const lancamentos = await buscarLancamentosCliente(authResult.data.clientid);
        
        console.log('Lançamentos encontrados:', lancamentos);
        
        // 4. Processar dados
        if (lancamentos && lancamentos.length > 0) {
            const totalReceitas = lancamentos
                .filter(l => l.type === 'receita')
                .reduce((sum, l) => sum + parseFloat(l.valor_movimentacao), 0);
            
            const totalDespesas = lancamentos
                .filter(l => l.type === 'despesa')
                .reduce((sum, l) => sum + parseFloat(l.valor_movimentacao), 0);
            
            const saldoLiquido = totalReceitas - totalDespesas;
            
            console.log('Resumo financeiro:');
            console.log(`Total de Receitas: R$ ${totalReceitas.toFixed(2)}`);
            console.log(`Total de Despesas: R$ ${totalDespesas.toFixed(2)}`);
            console.log(`Saldo Líquido: R$ ${saldoLiquido.toFixed(2)}`);
            
            // Exibir no dashboard
            atualizarDashboard({
                receitas: totalReceitas,
                despesas: totalDespesas,
                saldoLiquido: saldoLiquido,
                lancamentos: lancamentos
            });
        }
        
    } catch (error) {
        console.error('Erro no exemplo de uso:', error);
    }
}

// Função para atualizar o dashboard com os dados
function atualizarDashboard(dados) {
    // Atualizar cards de resumo
    document.querySelector('[data-card="receitas"] .valor').textContent = 
        `R$ ${dados.receitas.toFixed(2)}`;
    
    document.querySelector('[data-card="despesas"] .valor').textContent = 
        `R$ ${dados.despesas.toFixed(2)}`;
    
    document.querySelector('[data-card="saldo"] .valor').textContent = 
        `R$ ${dados.saldoLiquido.toFixed(2)}`;
    
    // Atualizar lista de lançamentos
    const lista = document.querySelector('[data-list="lancamentos"]');
    if (lista) {
        lista.innerHTML = dados.lancamentos.map(lancamento => `
            <div class="lancamento-item">
                <div class="lancamento-info">
                    <span class="categoria">${lancamento.category}</span>
                    <span class="observacao">${lancamento.observation || 'Sem observação'}</span>
                    <span class="data">${new Date(lancamento.data_movimentacao).toLocaleDateString('pt-BR')}</span>
                </div>
                <div class="lancamento-valor ${lancamento.type === 'receita' ? 'receita' : 'despesa'}">
                    ${lancamento.type === 'receita' ? '+' : '-'} R$ ${parseFloat(lancamento.valor_movimentacao).toFixed(2)}
                </div>
            </div>
        `).join('');
    }
}

// Função para gerar hash da senha (usar bcrypt em produção)
function hashSenha(senha) {
    // Em produção, use bcrypt ou similar
    // Aqui é apenas um exemplo simples
    return btoa(senha + 'salt_secreto');
}

// Função para validar formato do WhatsApp
function validarWhatsApp(whatsapp) {
    const regex = /^55\d{10,11}$/;
    return regex.test(whatsapp.replace(/\D/g, ''));
}

// Função para formatar WhatsApp
function formatarWhatsApp(whatsapp) {
    const numeros = whatsapp.replace(/\D/g, '');
    if (numeros.length === 13) {
        return `(${numeros.slice(2, 4)}) ${numeros.slice(4, 9)}-${numeros.slice(9)}`;
    }
    return whatsapp;
}

// Exportar funções para uso em outros arquivos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        buscarLancamentosCliente,
        autenticarCliente,
        verificarPrimeiroAcesso,
        registrarPrimeiroAcesso,
        exemploUsoCliente,
        atualizarDashboard,
        hashSenha,
        validarWhatsApp,
        formatarWhatsApp
    };
}

// Executar exemplo se estiver no contexto correto
if (typeof window !== 'undefined') {
    // Aguardar carregamento da página
    document.addEventListener('DOMContentLoaded', () => {
        console.log('Sistema de autenticação carregado');
        // exemploUsoCliente(); // Descomente para testar
    });
}
