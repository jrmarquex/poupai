# 🚀 GUIA COMPLETO DE DEPLOY PARA HOSPEDAGEM

## 📋 ARQUIVOS NECESSÁRIOS PARA SUA HOSPEDAGEM

### ✅ **ESTRUTURA DE PASTAS RECOMENDADA:**

```
seu-dominio.com/
├── public_html/          (ou htdocs/)
│   ├── index.html
│   ├── login_auth.html
│   ├── dashboard.html
│   ├── transacoes.html
│   ├── relatorios.html
│   ├── configuracoes.html
│   ├── tutorial.html
│   ├── feedback.html
│   ├── login.html
│   │
│   ├── api/              (PASTA API)
│   │   ├── auth-real.php
│   │   ├── dashboard-real.php
│   │   ├── transacoes-real.php
│   │   └── relatorios-real.php
│   │
│   ├── config/           (PASTA CONFIG)
│   │   ├── database.php
│   │   └── .htaccess     (PROTEÇÃO)
│   │
│   └── js/               (PASTA JS)
│       └── pouppi-auth-hybrid.js
│
└── (fora do public_html)
    └── logs/             (OPCIONAL - para logs)
```

---

## 📁 **ARQUIVOS ESSENCIAIS**

### **1. HTML PAGES (Frontend):**
✅ `index.html` - Página inicial  
✅ `login_auth.html` - Login e primeiro acesso  
✅ `dashboard.html` - Dashboard principal  
✅ `transacoes.html` - Página de transações  
✅ `relatorios.html` - Relatórios financeiros  
✅ `configuracoes.html` - Configurações do usuário  
✅ `tutorial.html` - Tutorial do sistema  
✅ `feedback.html` - Feedback dos usuários  
✅ `login.html` - (Opcional - página alternativa)

### **2. PHP APIs (Backend - Essenciais):**
✅ `api/auth-real.php` - Autenticação (login/registro)  
✅ `api/dashboard-real.php` - Dados do dashboard  
✅ `api/transacoes-real.php` - Dados das transações  
✅ `api/relatorios-real.php` - Dados dos relatórios

### **3. Configuração:**
✅ `config/database.php` - Conexão com Supabase

### **4. JavaScript:**
✅ `js/pouppi-auth-hybrid.js` - Sistema de autenticação híbrido

---

## 📦 **CHECKLIST DE UPLOAD**

### **✅ PASSOS PARA DEPLOY:**

#### **1. CRIAR PASTAS NA HOSPEDAGEM:**
```bash
public_html/
├── api/
├── config/
└── js/
```

#### **2. UPLOAD DOS ARQUIVOS:**

**📄 HTML PAGES (Raiz do public_html):**
```bash
✅ index.html
✅ login_auth.html
✅ dashboard.html
✅ transacoes.html
✅ relatorios.html
✅ configuracoes.html
✅ tutorial.html
✅ feedback.html
```

**🔧 PHP APIs (pasta api/):**
```bash
✅ api/auth-real.php
✅ api/dashboard-real.php
✅ api/transacoes-real.php
✅ api/relatorios-real.php
```

**⚙️ CONFIGURAÇÃO (pasta config/):**
```bash
✅ config/database.php
```

**💻 JAVASCRIPT (pasta js/):**
```bash
✅ js/pouppi-auth-hybrid.js
```

---

## 🔐 **CONFIGURAÇÃO DE SEGURANÇA**

### **1. Criar arquivo `.htaccess` na pasta `config/`:**

```apache
# Proteger pasta config contra acesso direto
<FilesMatch "\.php$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Permitir apenas acesso via include
<Files "database.php">
    Order allow,deny
    Allow from all
</Files>
```

### **2. Verificar `config/database.php` tem as credenciais corretas:**

```php
<?php
// config/database.php

// Credenciais Supabase
define('DB_HOST', 'db.beqpplqfamcpyuzgzhcs.supabase.co');
define('DB_PORT', '5432');
define('DB_NAME', 'postgres');
define('DB_USER', 'postgres');
define('DB_PASS', 'Hyundaimax@@9');

// ... resto do código
?>
```

### **3. Criar arquivo `.htaccess` na raiz (OPCIONAL):**
```apache
# Habilitar reescrita de URL
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Redirecionar HTTP para HTTPS (recomendado)
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>

# Proteger arquivos sensíveis
<FilesMatch "^(config\.env|\.env)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

## 🗄️ **BANCO DE DADOS SUPABASE**

### **✅ Configuração já pronta:**
- **Host:** `db.beqpplqfamcpyuzgzhcs.supabase.co`
- **Porta:** `5432`
- **Database:** `postgres`
- **Usuário:** `postgres`
- **Senha:** `Hyundaimax@@9`

### **✅ Tabelas já criadas:**
- `clientes` - Usuários do sistema
- `movimentacoes` - Transações financeiras
- `feedback` - Feedback dos usuários

---

## 🧪 **TESTE APÓS UPLOAD**

### **1. Testar conexão com o banco:**
Acesse: `https://seudominio.com/api/teste-conexao.php`

Se não existir, crie o arquivo:
```php
<?php
// api/teste-conexao.php
require_once __DIR__ . '/../config/database.php';

try {
    $conn = getDatabaseConnection();
    echo "✅ Conexão com banco de dados bem-sucedida!";
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>
```

### **2. Testar login:**
1. Acesse: `https://seudominio.com/login_auth.html`
2. Teste com:
   - **Admin:** `5511997245501` / `Hyundaimax@@9`
   - **Cliente teste:** `cliente` / `1234`

### **3. Verificar dashboard:**
Após login, verifique se os dados estão carregando corretamente.

---

## 📋 **ARQUIVOS ADICIONAIS (OPCIONAL)**

### **🛠️ Utilitários:**
```bash
❌ api/teste-conexao.php (criar se necessário)
❌ teste-php.php (não necessário em produção)
❌ config.env (não necessário)
```

### **📚 Documentação:**
```bash
❌ DOCUMENTACAO_COMPLETA_SISTEMA.md (não enviar)
❌ README.md (opcional)
❌ *.md files (não enviar)
```

### **🔧 Desenvolvimento:**
```bash
❌ node_modules/ (não enviar)
❌ package.json (não necessário)
❌ backups/ (não enviar)
❌ attached_assets/ (não necessário)
```

---

## 🚀 **RESUMO EXECUTIVO**

### **✅ ARQUIVOS ESSENCIAIS (Total: 17 arquivos)**

**HTML (8 arquivos):**
1. index.html
2. login_auth.html
3. dashboard.html
4. transacoes.html
5. relatorios.html
6. configuracoes.html
7. tutorial.html
8. feedback.html

**PHP (4 arquivos):**
1. api/auth-real.php
2. api/dashboard-real.php
3. api/transacoes-real.php
4. api/relatorios-real.php

**Config (1 arquivo):**
1. config/database.php

**JavaScript (1 arquivo):**
1. js/pouppi-auth-hybrid.js

**Segurança (2 arquivos a criar):**
1. .htaccess (raiz)
2. config/.htaccess

---

## ✅ **CHECKLIST FINAL ANTES DO DEPLOY**

- [ ] Criar pastas `api/`, `config/`, `js/` na hospedagem
- [ ] Upload de todos os arquivos HTML na raiz
- [ ] Upload dos arquivos PHP na pasta `api/`
- [ ] Upload do `database.php` na pasta `config/`
- [ ] Upload do `pouppi-auth-hybrid.js` na pasta `js/`
- [ ] Verificar credenciais no `config/database.php`
- [ ] Criar `.htaccess` na pasta `config/`
- [ ] Criar `.htaccess` na raiz (opcional)
- [ ] Testar conexão com banco
- [ ] Testar login
- [ ] Verificar dashboard

---

## 🎯 **URLS FINAIS**

Após o deploy, suas URLs serão:

```
✅ https://seudominio.com/
✅ https://seudominio.com/login_auth.html
✅ https://seudominio.com/dashboard.html
✅ https://seudominio.com/transacoes.html
✅ https://seudominio.com/relatorios.html
```

---

## 📞 **SUPORTE**

Em caso de problemas:
1. Verifique os logs de erro do servidor
2. Teste a conexão com o banco de dados
3. Verifique permissões dos arquivos PHP
4. Confirme que PHP 7.4+ está instalado na hospedagem

---

**🎉 DEPLOY PREPARADO E DOCUMENTADO!**

