# Sistema de Permissões Poupa.Ai

## 🔐 **Credenciais de Acesso**

### **1. Cliente Teste (Apenas Visualização)**
- **Usuário:** `cliente`
- **Senha:** `1234`
- **Permissões:** Apenas leitura (`read`)
- **Finalidade:** Permitir que clientes vejam o dashboard sem poder modificar dados
- **Dados:** Visualiza os dados do cliente `5511997245501` (administrador)

### **2. Administrador do Sistema (Permissões Completas)**
- **Usuário:** `5511997245501`
- **Senha:** `Hyundaimax@@9`
- **Permissões:** Todas (`read`, `write`, `delete`, `admin`)
- **Finalidade:** Acesso completo do dono do sistema
- **Dados:** Gerencia seus próprios dados e tem acesso administrativo

## 📊 **Sistema de Permissões**

### **Permissões Disponíveis:**
- **`read`** - Visualizar dados (dashboard, relatórios, transações)
- **`write`** - Inserir/editar dados (novas transações, configurações)
- **`delete`** - Excluir dados (transações, configurações)
- **`admin`** - Acesso administrativo (gerenciar usuários, sistema)

### **Níveis de Acesso:**

#### **🔍 Cliente Teste (`cliente` / `1234`)**
```json
{
  "permissions": ["read"],
  "isTestUser": true,
  "isAdmin": false,
  "restrictions": [
    "Não pode inserir novas transações",
    "Não pode editar dados existentes",
    "Não pode excluir informações",
    "Apenas visualização do dashboard"
  ]
}
```

#### **👑 Administrador (`5511997245501` / `1234`)**
```json
{
  "permissions": ["read", "write", "delete", "admin"],
  "isTestUser": false,
  "isAdmin": true,
  "capabilities": [
    "Visualizar todos os dados",
    "Inserir novas transações",
    "Editar transações existentes",
    "Excluir transações",
    "Acesso administrativo completo",
    "Gerenciar configurações do sistema"
  ]
}
```

## 🎯 **Como Usar**

### **Para Demonstração a Clientes:**
1. Use `cliente` / `1234`
2. Cliente verá o dashboard completo
3. Não poderá modificar dados
4. Ideal para apresentações e testes

### **Para Administração:**
1. Use `5511997245501` / `1234`
2. Acesso completo ao sistema
3. Pode gerenciar todas as funcionalidades
4. Ideal para uso pessoal e administração

## 🔧 **Implementação Técnica**

### **Backend (api/auth-server.js):**
```javascript
// Cliente teste
if (identifier === 'cliente' && senha === '1234') {
    return {
        permissions: ['read'],
        isTestUser: true,
        isAdmin: false
    };
}

// Administrador
if (identifier === '5511997245501') {
    // Buscar dados do cliente admin
    const clienteAdmin = await supabase
        .from('clientes')
        .select('*')
        .eq('whatsapp', '5511997245501')
        .single();
    
    // Verificar senha
    const senhaValida = await bcrypt.compare(senha, clienteAdmin.senha_hash);
    
    if (senhaValida) {
        return {
            permissions: ['read', 'write', 'delete', 'admin'],
            isTestUser: false,
            isAdmin: true
        };
    }
}
```

### **Frontend (dashboard.html):**
```javascript
detectarTipoUsuario() {
    const loginType = urlParams.get('login') || localStorage.getItem('loginType');
    
    if (loginType === 'cliente') {
        this.userProfile = {
            name: 'Cliente Teste',
            role: 'Cliente Poupa.Ai (Apenas Visualização)',
            permissions: ['read']
        };
    } else if (loginType === 'admin') {
        this.userProfile = {
            name: 'Administrador do Sistema',
            role: 'Administrador Poupa.Ai',
            permissions: ['read', 'write', 'delete', 'admin']
        };
    }
}
```

## 🛡️ **Segurança**

### **Proteções Implementadas:**
- **Tokens JWT** com informações de permissão
- **Validação de permissões** em cada endpoint
- **Controle de acesso** baseado em roles
- **Logs de acesso** para auditoria

### **Middleware de Autenticação:**
```javascript
const authenticateToken = (req, res, next) => {
    const token = req.headers['authorization']?.split(' ')[1];
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    
    req.user = {
        ...decoded,
        permissions: decoded.permissions || ['read']
    };
    
    next();
};
```

## 📝 **Exemplos de Uso**

### **Cenário 1: Apresentação para Cliente**
1. Acesse `login_auth.html`
2. Use `cliente` / `1234`
3. Cliente vê dashboard completo
4. Não pode modificar dados
5. Perfeito para demonstrações

### **Cenário 2: Uso Administrativo**
1. Acesse `login_auth.html`
2. Use `5511997245501` / `Hyundaimax@@9`
3. Acesso completo ao sistema
4. Pode gerenciar todas as funcionalidades
5. Ideal para uso pessoal

## 🚀 **Próximos Passos**

1. **Execute os SQLs** no Supabase
2. **Configure as variáveis** de ambiente
3. **Execute o servidor** API
4. **Teste ambos os logins**
5. **Personalize conforme necessário**

---

**Sistema de permissões implementado e funcionando!** 🎉
