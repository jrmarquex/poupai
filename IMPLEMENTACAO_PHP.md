# 🚀 Sistema Poupa.Ai com PHP

## 📋 **Configuração para Hospedagem Normal**

### **1. ✅ Hospedagem Compartilhada (Recomendada)**
- **Funciona perfeitamente** com PHP
- **Exemplos:** Hostinger, HostGator, UOLHost, etc.
- **Custo:** R$ 10-30/mês
- **Vantagem:** Simples de configurar

### **2. ✅ Hospedagem VPS (Avançada)**
- **Funciona com PHP, Node.js, Python, etc.**
- **Exemplos:** DigitalOcean, AWS, Google Cloud
- **Custo:** R$ 20-100/mês
- **Vantagem:** Controle total

## 🔧 **Passos para Implementar:**

### **Passo 1: Configurar Banco de Dados**
1. Acesse seu painel do Supabase
2. Vá em Settings > Database
3. Copie as credenciais de conexão
4. Edite `config/database.php` com suas credenciais

### **Passo 2: Upload dos Arquivos**
1. Faça upload da pasta `api/` para seu servidor
2. Faça upload da pasta `config/` para seu servidor
3. Mantenha a estrutura de pastas

### **Passo 3: Testar APIs**
- **Dashboard:** `https://seudominio.com/api/dashboard.php?whatsapp=5511997245501`
- **Transações:** `https://seudominio.com/api/transacoes.php?whatsapp=5511997245501`
- **Relatórios:** `https://seudominio.com/api/relatorios.php?whatsapp=5511997245501`

### **Passo 4: Atualizar Frontend**
Edite as páginas HTML para usar suas APIs:

```javascript
// Em dashboard.html, transacoes.html, relatorios.html
apiBaseUrl: 'https://seudominio.com/api'
```

## 📁 **Estrutura de Arquivos:**

```
seu-site/
├── api/
│   ├── dashboard.php
│   ├── transacoes.php
│   └── relatorios.php
├── config/
│   └── database.php
├── dashboard.html
├── transacoes.html
├── relatorios.html
└── index.html
```

## 🔒 **Segurança:**

### **1. Proteger Credenciais**
- Nunca commite senhas no Git
- Use variáveis de ambiente se possível
- Mantenha `config/database.php` fora do público

### **2. Validação de Entrada**
- Sempre valide dados de entrada
- Use prepared statements
- Implemente rate limiting

### **3. CORS**
- Configure CORS adequadamente
- Use HTTPS em produção
- Valide origem das requisições

## 🚀 **Vantagens do PHP:**

### **✅ Hospedagem Barata**
- Funciona em qualquer hospedagem compartilhada
- Sem necessidade de VPS
- Custo baixo

### **✅ Simplicidade**
- Fácil de implementar
- Não precisa de Node.js
- Suporte universal

### **✅ Performance**
- Conexão direta com PostgreSQL
- Sem overhead de Node.js
- Resposta rápida

## 📊 **Exemplo de Uso:**

### **Dashboard:**
```javascript
fetch('https://seudominio.com/api/dashboard.php?whatsapp=5511997245501')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Dados carregados:', data.dashboard);
    }
  });
```

### **Transações:**
```javascript
fetch('https://seudominio.com/api/transacoes.php?whatsapp=5511997245501&limite=20')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Transações:', data.transacoes);
    }
  });
```

## 🎯 **Próximos Passos:**

1. **Escolha uma hospedagem** (Hostinger recomendada)
2. **Configure o banco** no Supabase
3. **Faça upload dos arquivos** PHP
4. **Teste as APIs** individualmente
5. **Atualize o frontend** para usar suas APIs
6. **Teste o sistema completo**

## 💡 **Dicas:**

- **Teste localmente** primeiro com XAMPP/WAMP
- **Use HTTPS** em produção
- **Monitore logs** de erro
- **Faça backup** regular do banco
- **Implemente cache** se necessário

**Sistema pronto para hospedagem normal!** 🚀
