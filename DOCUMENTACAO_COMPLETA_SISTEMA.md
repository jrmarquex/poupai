# 📋 DOCUMENTAÇÃO COMPLETA DO SISTEMA POUPA.AI

## 🎯 **VISÃO GERAL DO SISTEMA**

O **Poupa.Ai** é um sistema financeiro pessoal que permite aos usuários controlar suas receitas e despesas através de um assistente inteligente via WhatsApp. O sistema oferece um dashboard completo com gráficos, relatórios e análises financeiras.

### **Funcionalidades Principais:**
- 📱 **Integração WhatsApp** - Registro de gastos via mensagens, áudios e fotos
- 📊 **Dashboard Financeiro** - Visualização de dados em tempo real
- 📈 **Relatórios Detalhados** - Análises por período, categoria e tipo
- 🔐 **Sistema de Login** - Autenticação segura com diferentes níveis de acesso
- 📤 **Exportação de Dados** - PDF, Excel, XML, CSV
- 🎯 **Categorização Automática** - IA identifica tipos de gastos automaticamente

---

## 🏗️ **ARQUITETURA DO SISTEMA**

### **Frontend:**
- **HTML5 + CSS3 + JavaScript (Alpine.js)**
- **Tailwind CSS** para estilização
- **Chart.js** para gráficos
- **Design responsivo** para mobile e desktop

### **Backend:**
- **PHP** para APIs (hospedagem normal)
- **Node.js** para APIs avançadas (VPS)
- **PostgreSQL** via Supabase como banco de dados

### **Hospedagem:**
- **GitHub Pages** para frontend estático
- **Hospedagem compartilhada** para PHP (R$ 10-30/mês)
- **Supabase** para banco de dados PostgreSQL

---

## 📱 **PÁGINAS E FUNCIONALIDADES**

### **1. 🏠 Página de Vendas (`index.html`)**
**URL:** `https://jrmarquex.github.io/pouppi/index.html`

**Objetivo:** Converter visitantes em clientes pagantes

**Seções Principais:**
- **Header:** Logo Poupa.Ai + menu de navegação
- **Hero Section:** "PARE DE PERDER DINHEIRO" + CTA principal
- **Problema/Solução:** Dores financeiras + como o sistema resolve
- **Como Funciona:** 3 passos simples via WhatsApp
- **Preços:** R$ 29,90/mês (promocional de R$ 69,90)
- **Depoimentos:** Testimonials de diferentes profissões
- **FAQ:** Perguntas frequentes com garantia de 7 dias
- **Demonstração:** Link para dashboard de exemplo
- **CTA Final:** Botão de compra com urgência

**Elementos Visuais:**
- Fundo escuro com gradientes
- Animações de texto infinitas
- Cards de exemplo WhatsApp
- Gráficos de demonstração
- Design mobile-first

### **2. 🔐 Página de Login (`login_auth.html`)**
**URL:** `https://jrmarquex.github.io/pouppi/login_auth.html`

**Objetivo:** Autenticação de usuários no sistema

**Funcionalidades:**
- **Login:** WhatsApp/Email + Senha
- **Primeiro Acesso:** Cadastro de novos usuários
- **Modo Demonstração:** Funciona sem backend

**Credenciais de Teste:**
```
CLIENTE TESTE (Apenas Visualização):
- Usuário: cliente
- Senha: 1234
- Permissões: read (apenas leitura)

ADMINISTRADOR (Permissões Completas):
- Usuário: 5511997245501
- Senha: Hyundaimax@@9
- Permissões: read, write, delete, admin

ALTERNATIVA ADMIN (Email):
- Usuário: frmarques.oli@gmail.com
- Senha: Hyundaimax@@9
- Permissões: read, write, delete, admin
```

### **3. 📊 Dashboard Principal (`dashboard.html`)**
**URL:** `https://jrmarquex.github.io/pouppi/dashboard.html`

**Objetivo:** Visão geral das finanças do usuário

**Seções:**
- **Resumo Financeiro:** Ganhos, Gastos, Saldo Líquido
- **Gráfico Pizza:** Gastos por categoria
- **Gráfico Linha:** Evolução financeira (7 dias)
- **Atividades Recentes:** Últimas 10 transações
- **Exportar Dados:** Botões para PDF, Excel, XML, CSV

**Dados Demonstração (sem backend):**
- Ganhos: R$ 3.500,00 (+12% este mês)
- Gastos: R$ 1.290,00 (+8% este mês)
- Saldo: R$ 2.210,00
- Atividades: Pizza R$ 70, iFood R$ 89,18, etc.

### **4. 💰 Página de Transações (`transacoes.html`)**
**URL:** `https://jrmarquex.github.io/pouppi/transacoes.html`

**Objetivo:** Lista detalhada de todas as movimentações

**Funcionalidades:**
- **Filtros:** Por data, tipo (Receita/Despesa), categoria
- **Ordenação:** Por data, valor, categoria
- **Busca:** Campo de pesquisa
- **Exportação:** PDF, Excel, XML, CSV funcionais
- **Paginação:** Carregamento de mais transações

### **5. 📈 Página de Relatórios (`relatorios.html`)**
**URL:** `https://jrmarquex.github.io/pouppi/relatorios.html`

**Objetivo:** Análises financeiras detalhadas

**Funcionalidades:**
- **Períodos:** Semana, Mês, Trimestre, Ano
- **Gráficos:** Linha, Pizza, Barras
- **Resumos:** Totais por período
- **Exportação:** Relatórios completos
- **Filtros:** Por categoria, tipo, valor

### **6. ⚙️ Página de Configurações (`configuracoes.html`)**
**URL:** `https://jrmarquex.github.io/pouppi/configuracoes.html`

**Objetivo:** Gerenciar perfil e preferências do usuário

**Funcionalidades:**
- **Perfil:** Nome, email, foto
- **Segurança:** Alterar senha
- **Notificações:** Email, WhatsApp
- **Integrações:** APIs externas
- **Backup:** Exportar dados

### **7. 📚 Página de Tutorial (`tutorial.html`)**
**URL:** `https://jrmarquex.github.io/pouppi/tutorial.html`

**Objetivo:** Ensinar como usar o sistema

**Conteúdo:**
- **Como Funciona:** 3 passos simples
- **Exemplos:** Receitas e despesas via WhatsApp
- **Recursos:** Áudio, fotos, categorização automática
- **Suporte:** Link para WhatsApp de ajuda

### **8. 💬 Página de Feedback (`feedback.html`)**
**URL:** `https://jrmarquex.github.io/pouppi/feedback.html`

**Objetivo:** Coletar feedback dos usuários

**Funcionalidades:**
- **Formulário:** Avaliação, comentários
- **Histórico:** Feedbacks anteriores
- **Categorias:** Bug, sugestão, elogio

---

## 🗄️ **ESTRUTURA DO BANCO DE DADOS**

### **Tabela: `clientes`**
```sql
CREATE TABLE clientes (
    clientid UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    whatsapp VARCHAR(20) UNIQUE NOT NULL,
    nome VARCHAR(100),
    email VARCHAR(100),
    senha_hash VARCHAR(255),
    primeiro_acesso BOOLEAN DEFAULT true,
    status BOOLEAN DEFAULT true,
    ultimo_login TIMESTAMP,
    tentativas_login INTEGER DEFAULT 0,
    bloqueado_ate TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW()
);
```

**Campos:**
- `clientid`: ID único do cliente (UUID)
- `whatsapp`: Número do WhatsApp (único)
- `nome`: Nome completo do cliente
- `email`: Email do cliente
- `senha_hash`: Hash da senha (bcrypt)
- `primeiro_acesso`: Se é primeiro login
- `status`: Se conta está ativa
- `ultimo_login`: Data do último acesso
- `tentativas_login`: Contador de tentativas falhadas
- `bloqueado_ate`: Data de bloqueio por tentativas

### **Tabela: `movimentacoes`**
```sql
CREATE TABLE movimentacoes (
    id VARCHAR(10) PRIMARY KEY,
    data_movimentacao DATE NOT NULL,
    valor_movimentacao NUMERIC(10,2) NOT NULL,
    clientid UUID REFERENCES clientes(clientid),
    type VARCHAR(20) NOT NULL, -- 'Receita' ou 'Despesa'
    category VARCHAR(50), -- 'alimentação', 'transporte', etc.
    observation TEXT,
    updated_at TIMESTAMP DEFAULT NOW()
);
```

**Campos:**
- `id`: ID único da movimentação (texto curto)
- `data_movimentacao`: Data da transação
- `valor_movimentacao`: Valor monetário
- `clientid`: Referência ao cliente (FK)
- `type`: Tipo ('Receita' ou 'Despesa')
- `category`: Categoria da movimentação
- `observation`: Descrição/observação
- `updated_at`: Data de atualização

### **Tabela: `feedback`**
```sql
CREATE TABLE feedback (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES clientes(clientid),
    admin_user_id UUID REFERENCES clientes(clientid),
    tipo VARCHAR(20) NOT NULL, -- 'bug', 'sugestao', 'elogio'
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    avaliacao INTEGER CHECK (avaliacao >= 1 AND avaliacao <= 5),
    status VARCHAR(20) DEFAULT 'pendente',
    resposta TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

---

## 🔌 **APIS E INTEGRAÇÕES**

### **APIs PHP (Hospedagem Normal)**

#### **1. Dashboard API (`api/dashboard.php`)**
**Endpoint:** `GET /api/dashboard.php?whatsapp=5511997245501`

**Resposta:**
```json
{
    "success": true,
    "cliente": {
        "clientid": "uuid",
        "nome": "Francisco Marques de Oliveira Junior",
        "email": "frmarques.oli@gmail.com",
        "whatsapp": "5511997245501"
    },
    "dashboard": {
        "ganhos": {
            "valor": 3500.00,
            "variacao": 12
        },
        "gastos": {
            "valor": 1290.00,
            "variacao": 8
        },
        "saldoLiquido": {
            "valor": 2210.00
        }
    },
    "movimentacoes": [...],
    "gastosPorCategoria": [...]
}
```

#### **2. Transações API (`api/transacoes.php`)**
**Endpoint:** `GET /api/transacoes.php?whatsapp=5511997245501&limite=20`

**Parâmetros:**
- `whatsapp`: Número do cliente
- `limite`: Quantidade de registros
- `tipo`: 'Receita' ou 'Despesa'
- `categoria`: Categoria específica
- `data_inicio`: Data inicial
- `data_fim`: Data final

#### **3. Relatórios API (`api/relatorios.php`)**
**Endpoint:** `GET /api/relatorios.php?whatsapp=5511997245501&periodo=mes`

**Parâmetros:**
- `whatsapp`: Número do cliente
- `periodo`: 'semana', 'mes', 'trimestre', 'ano'

### **APIs Node.js (VPS)**

#### **1. Autenticação (`api/auth-server.js`)**
**Endpoints:**
- `POST /api/auth/login` - Login de usuário
- `POST /api/auth/register` - Cadastro de usuário
- `POST /api/auth/logout` - Logout

#### **2. Dashboard Real (`api/dashboard-real-data.js`)**
**Endpoint:** `GET /api/dashboard` - Dados reais do dashboard

---

## 🔐 **SISTEMA DE AUTENTICAÇÃO**

### **Níveis de Acesso:**

#### **1. Cliente Teste (Apenas Visualização)**
- **Credenciais:** `cliente` / `1234`
- **Permissões:** `['read']`
- **Dados:** Visualiza dados do admin (5511997245501)
- **Uso:** Demonstrações para clientes

#### **2. Administrador (Permissões Completas)**
- **Credenciais:** `5511997245501` / `Hyundaimax@@9`
- **Permissões:** `['read', 'write', 'delete', 'admin']`
- **Dados:** Gerencia seus próprios dados
- **Uso:** Dono do sistema

#### **3. Usuário Normal (Permissões Padrão)**
- **Credenciais:** WhatsApp/Email + Senha cadastrada
- **Permissões:** `['read', 'write']`
- **Dados:** Apenas seus próprios dados
- **Uso:** Clientes pagantes

### **Segurança:**
- **Senhas:** Hash bcrypt
- **Sessões:** JWT tokens
- **Rate Limiting:** Proteção contra brute force
- **CORS:** Configurado para frontend
- **SQL Injection:** Prepared statements

---

## 📊 **DADOS DE DEMONSTRAÇÃO**

### **Cliente Administrador:**
- **WhatsApp:** `5511997245501`
- **Nome:** `Francisco Marques de Oliveira Junior`
- **Email:** `frmarques.oli@gmail.com`
- **Senha:** `Hyundaimax@@9`

### **Movimentações de Exemplo:**
```sql
-- Receitas
INSERT INTO movimentacoes VALUES ('001', '2024-11-29', 850.00, 'clientid', 'Receita', 'outros', 'Freelance Design');
INSERT INTO movimentacoes VALUES ('002', '2024-11-28', 2800.00, 'clientid', 'Receita', 'outros', 'Salário CLT');

-- Despesas
INSERT INTO movimentacoes VALUES ('003', '2024-11-29', 25.00, 'clientid', 'Despesa', 'transporte', 'Uber - Centro');
INSERT INTO movimentacoes VALUES ('004', '2024-11-28', 42.90, 'clientid', 'Despesa', 'alimentação', 'iFood - Jantar');
INSERT INTO movimentacoes VALUES ('005', '2024-11-29', 70.00, 'clientid', 'Despesa', 'alimentação', 'Pizza');
INSERT INTO movimentacoes VALUES ('006', '2024-11-29', 89.18, 'clientid', 'Despesa', 'alimentação', 'iFood');
```

---

## 🚀 **COMO IMPLEMENTAR**

### **Opção 1: Hospedagem Normal (PHP)**
1. **Escolher hospedagem:** Hostinger (R$ 12/mês)
2. **Configurar banco:** Supabase PostgreSQL
3. **Upload arquivos:** Pasta `api/` e `config/`
4. **Configurar credenciais:** Editar `config/database.php`
5. **Testar APIs:** URLs das APIs
6. **Atualizar frontend:** Mudar `apiBaseUrl`

### **Opção 2: VPS (Node.js)**
1. **Escolher VPS:** DigitalOcean (R$ 20/mês)
2. **Instalar Node.js:** Versão 18+
3. **Configurar banco:** Supabase PostgreSQL
4. **Instalar dependências:** `npm install`
5. **Configurar variáveis:** Arquivo `.env`
6. **Executar servidor:** `node api/auth-server.js`

### **Opção 3: Demonstração (Sem Backend)**
1. **Usar GitHub Pages:** Já configurado
2. **Dados estáticos:** Funciona sem banco
3. **Login simulado:** Credenciais hardcoded
4. **Ideal para:** Apresentações e testes

---

## 📁 **ESTRUTURA DE ARQUIVOS**

```
BlogLiterario/
├── 📄 index.html                 # Página de vendas
├── 🔐 login_auth.html            # Sistema de login
├── 📊 dashboard.html             # Dashboard principal
├── 💰 transacoes.html            # Lista de transações
├── 📈 relatorios.html            # Relatórios financeiros
├── ⚙️ configuracoes.html         # Configurações do usuário
├── 📚 tutorial.html              # Guia de uso
├── 💬 feedback.html              # Sistema de feedback
├── 📁 api/                       # APIs PHP
│   ├── dashboard.php
│   ├── transacoes.php
│   └── relatorios.php
├── 📁 config/                    # Configurações
│   └── database.php
├── 📁 database/                  # Scripts SQL
│   ├── auth_system.sql
│   ├── dados_exemplo_admin.sql
│   └── buscar_dados_usuario_5511997245501.sql
├── 📁 js/                        # JavaScript
│   └── auth-system.js
└── 📄 package.json              # Dependências Node.js
```

---

## 🎯 **CASOS DE USO**

### **1. Demonstração para Cliente**
- **Acesso:** `https://jrmarquex.github.io/pouppi/login_auth.html`
- **Login:** `cliente` / `1234`
- **Resultado:** Visualiza dashboard com dados de exemplo
- **Uso:** Mostrar funcionalidades sem comprometer dados reais

### **2. Uso Administrativo**
- **Acesso:** `https://jrmarquex.github.io/pouppi/login_auth.html`
- **Login:** `5511997245501` / `Hyundaimax@@9`
- **Resultado:** Acesso completo ao sistema
- **Uso:** Gerenciar dados pessoais e do sistema

### **3. Cliente Pagante**
- **Acesso:** Sistema via WhatsApp
- **Registro:** Primeiro acesso com cadastro
- **Resultado:** Dashboard personalizado com seus dados
- **Uso:** Controle financeiro pessoal

---

## 🔧 **MANUTENÇÃO E SUPORTE**

### **Logs Importantes:**
- **Console do navegador:** Erros JavaScript
- **Logs do servidor:** Erros PHP/Node.js
- **Supabase:** Logs de banco de dados

### **Backup:**
- **Banco de dados:** Export via Supabase
- **Código:** Repositório GitHub
- **Arquivos:** Backup da hospedagem

### **Monitoramento:**
- **Uptime:** Status das APIs
- **Performance:** Tempo de resposta
- **Erros:** Taxa de erro das requisições

---

## 📞 **CONTATOS E SUPORTE**

### **Desenvolvedor:**
- **Nome:** Francisco Marques de Oliveira Junior
- **WhatsApp:** 5511997245501
- **Email:** frmarques.oli@gmail.com

### **Sistema:**
- **Repositório:** https://github.com/jrmarquex/pouppi
- **Demo:** https://jrmarquex.github.io/pouppi/
- **Documentação:** Este arquivo

---

## ⚠️ **NOTAS IMPORTANTES**

### **Segurança:**
- **Nunca commite** senhas no Git
- **Use HTTPS** em produção
- **Valide entrada** de dados
- **Monitore logs** de erro

### **Performance:**
- **Cache** dados frequentes
- **Otimize** queries SQL
- **Compress** imagens
- **Minimize** CSS/JS

### **Escalabilidade:**
- **CDN** para assets estáticos
- **Load balancer** para APIs
- **Database** connection pooling
- **Monitoring** de recursos

---

**📋 Esta documentação contém todas as informações necessárias para entender, manter e expandir o sistema Poupa.Ai. Qualquer IA futura pode usar este arquivo como referência completa do projeto.**
