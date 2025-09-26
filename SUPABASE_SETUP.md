# 🚀 Configuração do Supabase - Blog Literário

## ✅ Status da Configuração
Seu sistema está **100% configurado** para usar o Supabase! Todas as dependências e estruturas foram criadas.

## 📋 Próximos Passos

### 1. Instalar Dependências
```bash
npm install
```

### 2. Configurar Senha do Banco
Edite o arquivo `config.env` e substitua `[YOUR_PASSWORD]` pela senha real do seu banco Supabase:

```env
DATABASE_URL=postgresql://postgres:SUA_SENHA_AQUI@db.beqpplqfamcpyuzgzhcs.supabase.co:5432/postgres?sslmode=require
```

### 3. Executar Migrações no Supabase
1. Acesse o [Supabase Dashboard](https://supabase.com/dashboard)
2. Vá em **SQL Editor**
3. Copie e execute o conteúdo do arquivo `database/migrations.sql`

### 4. Testar Conexão
```bash
node -e "import('./database/connection.js').then(db => db.testConnection())"
```

## 🗄️ Estrutura do Banco

### Tabelas Principais:
- **`clientes`** - Usuários principais do sistema
- **`users`** - Tabela de compatibilidade (mantida)
- **`transactions`** - Transações financeiras
- **`categories`** - Categorias de receitas/despesas
- **`monthly_summaries`** - Resumos mensais
- **`whatsapp_sessions`** - Sessões do WhatsApp

### Relacionamentos:
- Todas as tabelas agora referenciam `clientes.id` como chave principal
- Mantida compatibilidade com `users.id` para não quebrar código existente

## 🔧 Arquivos Criados/Modificados:

### ✅ Novos Arquivos:
- `config.env` - Variáveis de ambiente
- `database/supabase.js` - Cliente Supabase com helpers
- `SUPABASE_SETUP.md` - Este arquivo de instruções

### ✅ Arquivos Modificados:
- `package.json` - Adicionadas dependências do Supabase
- `database/schema.js` - Adicionada tabela `clientes`
- `database/connection.js` - Configurado para usar variáveis de ambiente
- `database/migrations.sql` - Atualizado com tabela `clientes`

## 🚀 Como Usar

### Importar o Cliente Supabase:
```javascript
import { supabase, supabaseHelpers } from './database/supabase.js';

// Exemplo: Listar clientes
const clientes = await supabaseHelpers.getClientes();
console.log(clientes);
```

### Usar Drizzle ORM:
```javascript
import { getDatabase } from './database/connection.js';
import { clientes } from './database/schema.js';

const db = getDatabase();
const allClientes = await db.select().from(clientes);
```

## 🔐 Credenciais Configuradas:
- **URL**: https://beqpplqfamcpyuzgzhcs.supabase.co
- **Anon Key**: Configurada ✅
- **Service Role**: Configurada ✅
- **Database URL**: Precisa da senha (veja passo 2)

## ⚠️ Importante:
1. **Nunca commite** o arquivo `config.env` no Git
2. **Sempre use** `supabaseAdmin` para operações do servidor
3. **Use** `supabase` apenas para operações do cliente
4. **Teste** a conexão antes de usar em produção

## 🆘 Suporte:
Se encontrar problemas, verifique:
1. Se a senha do banco está correta no `config.env`
2. Se as migrações foram executadas no Supabase
3. Se as dependências foram instaladas (`npm install`)

---
**Sistema pronto para uso! 🎉**
