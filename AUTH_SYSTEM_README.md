# Sistema de Autenticação Poupa.Ai

Sistema completo de autenticação para primeiro acesso com telefone e senha, incluindo busca de lançamentos do cliente específico.

## 📋 Arquivos Criados

### 1. **Banco de Dados**
- `database/auth_system.sql` - Script SQL para adicionar campos de autenticação
- `database/queries_cliente_5511997245501.sql` - Queries específicas para o cliente

### 2. **Frontend**
- `login_auth.html` - Página de login/primeiro acesso responsiva
- `js/auth-system.js` - JavaScript para integração com API

### 3. **Backend**
- `api/auth-server.js` - Servidor Express.js com todas as rotas de autenticação
- `env.example` - Exemplo de variáveis de ambiente

## 🚀 Implementação

### Passo 1: Configurar Banco de Dados

Execute o script SQL no seu Supabase:

```sql
-- Execute database/auth_system.sql no SQL Editor do Supabase
```

### Passo 2: Configurar Variáveis de Ambiente

Copie `env.example` para `.env` e configure:

```bash
cp env.example .env
```

Edite o `.env` com suas credenciais do Supabase.

### Passo 3: Instalar Dependências

```bash
npm install express bcrypt jsonwebtoken @supabase/supabase-js dotenv
```

### Passo 4: Executar Servidor

```bash
node api/auth-server.js
```

### Passo 5: Testar Sistema

Acesse `login_auth.html` no navegador.

## 🔐 Fluxo de Autenticação

### Primeiro Acesso
1. Usuário informa WhatsApp: `5511997245501`
2. Sistema verifica se é primeiro acesso
3. Se sim, exibe formulário de cadastro
4. Usuário preenche: nome, email, senha
5. Sistema cria/atualiza conta no banco
6. Redireciona para dashboard

### Login Normal
1. Usuário informa WhatsApp ou email
2. Sistema busca cliente no banco
3. Verifica senha com bcrypt
4. Gera token JWT
5. Redireciona para dashboard

## 📊 Busca de Lançamentos

### Para o Cliente Específico (5511997245501)

```javascript
// Buscar lançamentos
const lancamentos = await buscarLancamentosCliente(clientid);

// Resumo financeiro
const resumo = {
    receitas: lancamentos.filter(l => l.type === 'receita').reduce((sum, l) => sum + l.valor_movimentacao, 0),
    despesas: lancamentos.filter(l => l.type === 'despesa').reduce((sum, l) => sum + l.valor_movimentacao, 0),
    saldoLiquido: receitas - despesas
};
```

## 🛡️ Segurança Implementada

- **Hash de senhas** com bcrypt (10 rounds)
- **Tokens JWT** com expiração de 24h
- **Proteção contra força bruta** (5 tentativas, bloqueio de 30min)
- **Validação de entrada** em todas as rotas
- **Middleware de autenticação** para rotas protegidas

## 📱 Funcionalidades

### ✅ Implementadas
- [x] Verificação de primeiro acesso
- [x] Cadastro com telefone e senha
- [x] Login com telefone ou email
- [x] Busca de lançamentos por cliente
- [x] Resumo financeiro automático
- [x] Interface responsiva
- [x] Proteção contra ataques

### 🔄 Próximas Implementações
- [ ] Recuperação de senha por email
- [ ] Notificações por WhatsApp
- [ ] Dashboard com gráficos em tempo real
- [ ] Exportação de relatórios
- [ ] Integração com APIs de pagamento

## 🧪 Testando com Cliente Específico

### WhatsApp: `5511997245501`

1. **Primeiro acesso**: Sistema detecta e exibe formulário
2. **Login**: Use o WhatsApp ou email cadastrado
3. **Lançamentos**: Sistema busca automaticamente os dados

### Queries SQL de Teste

```sql
-- Verificar cliente
SELECT * FROM clientes WHERE whatsapp = '5511997245501';

-- Buscar lançamentos
SELECT * FROM movimentacoes m 
JOIN clientes c ON m.clientid = c.clientid 
WHERE c.whatsapp = '5511997245501';
```

## 🔧 Configuração de Produção

### 1. Variáveis de Ambiente
```bash
NODE_ENV=production
JWT_SECRET=your-super-secret-key
SUPABASE_SERVICE_ROLE_KEY=your-service-key
```

### 2. HTTPS
Configure SSL/TLS para produção.

### 3. Rate Limiting
Implemente rate limiting para prevenir abuso.

### 4. Logs
Configure sistema de logs para monitoramento.

## 📞 Suporte

Para dúvidas ou problemas:
- Verifique os logs do servidor
- Confirme as variáveis de ambiente
- Teste as queries SQL diretamente no Supabase
- Verifique a conectividade com o banco

## 🎯 Próximos Passos

1. **Implementar em produção** com suas credenciais
2. **Configurar domínio** e HTTPS
3. **Integrar com dashboard** existente
4. **Adicionar mais funcionalidades** conforme necessário

---

**Sistema pronto para uso em produção!** 🚀
