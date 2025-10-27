# 🚀 GUIA DE DEPLOY - HOSTINGER (pouppi.com)

## 📋 PRÉ-REQUISITOS

✅ **Credenciais do Supabase:**
- URL: `https://beqpplqfamcpyuzgzhcs.supabase.co`
- Anon Key: `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImJlcXBwbHFmYW1jcHl1emd6aGNzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTExNzM1MjMsImV4cCI6MjA2Njc0OTUyM30.AGH7vn44wQdLX8qG5YnIHQp4dj_ZGHfxGV6GqpvZKwA`
- Service Role Key: `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImJlcXBwbHFmYW1jcHl1emd6aGNzIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1MTE3MzUyMywiZXhwIjoyMDY2NzQ5NTIzfQ.JrvEhJ2wDnEQK8i85BQiCF-_dUdIZfKHQUbEwuWXu5A`

✅ **Domínio:** https://pouppi.com

✅ **Hospedagem:** Hostinger

---

## 📦 PASSO 1: PREPARAR ARQUIVOS PARA UPLOAD

### Arquivos na RAIZ (public_html/):
```
✅ index.html
✅ login_auth.html
✅ dashboard.html
✅ transacoes.html
✅ relatorios.html
✅ configuracoes.html
✅ tutorial.html
✅ feedback.html
✅ .htaccess
```

### Pasta: api/
```
✅ auth-real.php
✅ dashboard-real.php
✅ transacoes-real.php
✅ relatorios-real.php
```

### Pasta: config/
```
✅ database.php
✅ .htaccess
```

### Pasta: js/
```
✅ pouppi-auth-hybrid.js
```

---

## 🔧 PASSO 2: ACESSAR PAINEL DA HOSTINGER

1. Acesse: https://hpanel.hostinger.com
2. Faça login com suas credenciais
3. Selecione o site **pouppi.com**
4. Clique em **File Manager** (Gerenciador de Arquivos)

---

## 📤 PASSO 3: FAZER UPLOAD DOS ARQUIVOS

### Método 1: Via File Manager (Recomendado)

1. **Acesse o File Manager da Hostinger**
2. **Navegue até `public_html/`**
3. **Crie as pastas necessárias:**
   - Clique em "New Folder"
   - Crie: `api`, `config`, `js`

4. **Upload dos arquivos HTML:**
   - Selecione: Upload
   - Escolha todos os arquivos `.html` e `.htaccess`
   - Faça upload para `public_html/`

5. **Upload pasta api/:**
   - Entre na pasta `api/`
   - Upload: `auth-real.php`, `dashboard-real.php`, `transacoes-real.php`, `relatorios-real.php`

6. **Upload pasta config/:**
   - Entre na pasta `config/`
   - Upload: `database.php`, `.htaccess`

7. **Upload pasta js/:**
   - Entre na pasta `js/`
   - Upload: `pouppi-auth-hybrid.js`

### Método 2: Via FTP (Alternativo)

```
Host: ftp.pouppi.com (ou IP fornecido pela Hostinger)
Usuário: [seu_usuario_ftp]
Senha: [sua_senha_ftp]
Porta: 21
```

**Ferramentas FTP recomendadas:**
- FileZilla: https://filezilla-project.org/
- WinSCP: https://winscp.net/

---

## ✅ PASSO 4: VERIFICAR PERMISSÕES DOS ARQUIVOS

**No File Manager da Hostinger:**

1. Clique com botão direito nos arquivos
2. Selecione "Permissions" (Permissões)
3. Configure:
   - **Arquivos `.php`:** 644 (rw-r--r--)
   - **Pastas:** 755 (rwxr-xr-x)
   - **Arquivo `.htaccess`:** 644

---

## 🧪 PASSO 5: TESTAR O SISTEMA

### Teste 1: Verificar página inicial
```
https://pouppi.com/
```
✅ Deve carregar a landing page

### Teste 2: Testar login
```
https://pouppi.com/login_auth.html
```
**Credenciais Admin:**
- WhatsApp: `5511997245501`
- Senha: `Hyundaimax@@9`

### Teste 3: Verificar dashboard
```
https://pouppi.com/dashboard.html
```
✅ Deve carregar dados do Supabase

### Teste 4: Testar API
```
https://pouppi.com/api/auth-real.php
```
✅ Deve retornar resposta JSON (não erro 404)

### Teste 5: Verificar conexão com banco
Crie arquivo temporário: `teste-db.php` na raiz:

```php
<?php
require_once 'config/database.php';
$resultado = testarConexao();
echo json_encode($resultado, JSON_PRETTY_PRINT);
?>
```

Acesse: `https://pouppi.com/teste-db.php`

✅ Deve mostrar: `"sucesso": true`

---

## 🔒 PASSO 6: CONFIGURAR SSL/HTTPS

1. **No painel da Hostinger:**
   - Vá em: **SSL**
   - Ative: **Free SSL Certificate** (Let's Encrypt)
   - Aguarde 5-10 minutos para ativação

2. **Forçar HTTPS (já configurado no .htaccess):**
   - O arquivo `.htaccess` já redireciona HTTP → HTTPS automaticamente

---

## 🎯 URLS FINAIS DO SISTEMA

| Página | URL |
|--------|-----|
| **Landing Page** | https://pouppi.com/ |
| **Login** | https://pouppi.com/login_auth.html |
| **Dashboard** | https://pouppi.com/dashboard.html |
| **Transações** | https://pouppi.com/transacoes.html |
| **Relatórios** | https://pouppi.com/relatorios.html |
| **Configurações** | https://pouppi.com/configuracoes.html |
| **Tutorial** | https://pouppi.com/tutorial.html |
| **Feedback** | https://pouppi.com/feedback.html |

---

## 🐛 TROUBLESHOOTING (Solução de Problemas)

### Problema: Erro 500 (Internal Server Error)

**Solução:**
1. Verifique permissões dos arquivos PHP (devem ser 644)
2. Verifique se o `.htaccess` está correto
3. Veja logs de erro: Painel Hostinger → Error Logs

### Problema: API retorna erro de conexão

**Solução:**
1. Verifique se `config/database.php` tem as credenciais corretas do Supabase
2. Teste conexão acessando `teste-db.php`
3. Verifique se Supabase está permitindo conexões externas

### Problema: Dashboard não carrega dados

**Solução:**
1. Abra o console do navegador (F12)
2. Veja se há erros de JavaScript
3. Verifique se as chamadas à API estão falhando
4. Confirme que está logado corretamente

### Problema: CSS/JavaScript não carrega

**Solução:**
1. Verifique se o cache do navegador está limpo (Ctrl+Shift+R)
2. Confirme que os arquivos foram enviados corretamente
3. Veja o console do navegador para erros 404

---

## 📊 CHECKLIST FINAL

Antes de anunciar o sistema como pronto:

- [ ] ✅ Todos os arquivos HTML funcionando
- [ ] ✅ Login funcionando com admin (5511997245501)
- [ ] ✅ Dashboard carregando dados do Supabase
- [ ] ✅ Transações listando corretamente
- [ ] ✅ Relatórios gerando dados
- [ ] ✅ SSL/HTTPS ativo
- [ ] ✅ Menu mobile funcionando em dispositivos mobile
- [ ] ✅ Todas as páginas responsivas
- [ ] ✅ API retornando dados corretos
- [ ] ✅ Navegação entre páginas funcionando

---

## 🎉 SISTEMA PRONTO!

Após completar todos os passos, seu sistema estará rodando em:

**🌐 https://pouppi.com**

**Login Admin:**
- WhatsApp: `5511997245501`
- Senha: `Hyundaimax@@9`

---

## 📞 SUPORTE

Em caso de dúvidas:
1. Verifique os logs de erro no painel da Hostinger
2. Consulte documentação do Supabase: https://supabase.com/docs
3. Entre em contato com suporte da Hostinger se necessário

---

**✨ Boa sorte com o deploy!**
