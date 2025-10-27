# 📋 INSTRUÇÕES - Pasta logs/

## ✅ O QUE VOCÊ PRECISA FAZER:

### Passo 1: Criar a pasta `logs/` no servidor

**Via File Manager da Hostinger:**
1. Acesse o File Manager
2. Entre na pasta `public_html/`
3. Clique em **"New Folder"** (Nova Pasta)
4. Nome: `logs`
5. Clique em **"Create"**

---

### Passo 2: Fazer upload dos arquivos de proteção

**Faça upload destes 3 arquivos para `public_html/logs/`:**

```
logs/
├── .htaccess        ← Proteção de segurança
├── index.php        ← Bloqueia acesso direto
└── README.md        ← Documentação
```

---

### Passo 3: Configurar permissões

**No File Manager:**
1. Clique com botão direito na pasta `logs/`
2. Selecione **"Permissions"** (Permissões)
3. Configure: **755** (rwxr-xr-x)
   - Owner: Read, Write, Execute
   - Group: Read, Execute
   - Others: Read, Execute

**Para os arquivos dentro:**
- `.htaccess` → **644** (rw-r--r--)
- `index.php` → **644** (rw-r--r--)
- `README.md` → **644** (rw-r--r--)

---

## ❓ O arquivo `webhook-lastlink.log` não existe?

**ISSO É NORMAL!** ✅

O arquivo `webhook-lastlink.log` será **criado automaticamente** pelo sistema quando:
1. O primeiro webhook for recebido da Lastlink
2. Uma venda for processada
3. O endpoint `api/webhook-lastlink.php` for chamado

---

## 🔍 Como verificar se está funcionando?

### Opção 1: Após configurar o webhook na Lastlink

Quando a primeira venda acontecer:
1. O arquivo `logs/webhook-lastlink.log` será criado automaticamente
2. Você pode visualizá-lo via FTP ou File Manager

### Opção 2: Testar manualmente (via Postman ou cURL)

**Teste via cURL:**
```bash
curl -X POST https://pouppi.com/api/webhook-lastlink.php \
  -H "Content-Type: application/json" \
  -d '{
    "event": "payment.approved",
    "customer": {
      "name": "Cliente Teste",
      "email": "teste@email.com",
      "phone": "11987654321"
    },
    "status": "approved",
    "amount": 97.00
  }'
```

**Resultado esperado:**
- ✅ Arquivo `logs/webhook-lastlink.log` será criado
- ✅ Cliente será cadastrado no Supabase
- ✅ Log registrará a operação

---

## 📂 Estrutura Final no Servidor:

```
public_html/
├── api/
│   ├── webhook-lastlink.php
│   ├── admin-auth.php
│   └── ...
│
├── logs/                        ← CRIAR ESTA PASTA
│   ├── .htaccess                ← Upload
│   ├── index.php                ← Upload
│   ├── README.md                ← Upload
│   └── webhook-lastlink.log     ← Será criado automaticamente
│
├── admin-webhook.html
├── dashboard.html
└── ...
```

---

## 🔒 Segurança da Pasta logs/

Os arquivos `.htaccess` e `index.php` garantem que:
- ✅ Ninguém pode acessar os logs via navegador
- ✅ Apenas scripts PHP do sistema podem escrever
- ✅ Tentativas de acesso direto retornam "403 Forbidden"

**Exemplo:**
- ❌ `https://pouppi.com/logs/` → **403 Forbidden**
- ❌ `https://pouppi.com/logs/webhook-lastlink.log` → **403 Forbidden**
- ✅ Sistema interno → Pode ler e escrever normalmente

---

## 📊 Como visualizar os logs?

### Via FTP:
1. Conecte via FileZilla ou WinSCP
2. Navegue até: `public_html/logs/`
3. Baixe o arquivo `webhook-lastlink.log`
4. Abra com editor de texto

### Via File Manager:
1. Acesse File Manager da Hostinger
2. Vá em: `public_html/logs/`
3. Clique no arquivo `webhook-lastlink.log`
4. Selecione "View" ou "Edit"

---

## 🎯 Formato do Log

Cada entrada será registrada assim:

```
[2024-10-27 15:30:45] [received] {"method":"POST","headers":{...},"data":{...}}
[2024-10-27 15:30:45] [success] {"action":"created","clientid":123,"whatsapp":"11987654321"}
```

**Campos:**
- `[timestamp]` - Data e hora
- `[status]` - info, success, error, warning
- `{dados}` - JSON com informações da operação

---

## ✅ CHECKLIST FINAL:

- [ ] Pasta `logs/` criada em `public_html/`
- [ ] Arquivo `.htaccess` enviado para `logs/`
- [ ] Arquivo `index.php` enviado para `logs/`
- [ ] Arquivo `README.md` enviado para `logs/`
- [ ] Permissões configuradas (755 para pasta, 644 para arquivos)
- [ ] Webhook configurado na Lastlink
- [ ] Teste realizado (opcional)

---

## ❓ Problemas Comuns:

### "Permission denied" ao criar log
**Solução:** Verifique permissões da pasta `logs/` (deve ser 755)

### Arquivo .log não é criado
**Possíveis causas:**
1. Pasta `logs/` não existe
2. Permissões incorretas
3. Webhook ainda não recebeu eventos
4. Path incorreto no código PHP

### Não consigo acessar via navegador
**Isso é CORRETO!** ✅ Os logs são protegidos por segurança.

---

**📞 Em caso de dúvidas:** Verifique o arquivo no servidor ou teste manualmente com cURL.

**✨ Sistema pronto após seguir estes passos!**
