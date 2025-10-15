# 🛡️ Backup do Banco Supabase - Instruções

## 1. Backup via Supabase Dashboard (RECOMENDADO)

### Passo a Passo:
1. **Acesse**: [https://supabase.com/dashboard](https://supabase.com/dashboard)
2. **Selecione seu projeto**: `beqpplqfamcpyuzgzhcs`
3. **Vá em**: Settings > Database
4. **Clique em**: "Backup" ou "Export"
5. **Escolha**: "Schema + Data" ou "Schema Only"
6. **Download**: Salve o arquivo `.sql`

### Vantagens:
- ✅ Backup completo (estrutura + dados)
- ✅ Interface gráfica simples
- ✅ Inclui todas as configurações
- ✅ Pode ser restaurado facilmente

---

## 2. Backup via SQL (Manual)

### Exportar Estrutura das Tabelas:
```sql
-- Execute no SQL Editor do Supabase
-- 1. Exportar estrutura da tabela clientes
SELECT 
    'CREATE TABLE clientes (' ||
    string_agg(
        column_name || ' ' || data_type || 
        CASE 
            WHEN character_maximum_length IS NOT NULL 
            THEN '(' || character_maximum_length || ')'
            ELSE ''
        END ||
        CASE 
            WHEN is_nullable = 'NO' THEN ' NOT NULL'
            ELSE ''
        END ||
        CASE 
            WHEN column_default IS NOT NULL 
            THEN ' DEFAULT ' || column_default
            ELSE ''
        END,
        ', '
    ) || ');' as create_statement
FROM information_schema.columns 
WHERE table_name = 'clientes' 
AND table_schema = 'public'
ORDER BY ordinal_position;
```

### Exportar Dados:
```sql
-- Exportar dados da tabela clientes
COPY clientes TO STDOUT WITH CSV HEADER;

-- Exportar dados da tabela movimentacoes  
COPY movimentacoes TO STDOUT WITH CSV HEADER;
```

---

## 3. Backup via pg_dump (Avançado)

### Se você tiver acesso direto ao PostgreSQL:
```bash
# Backup completo
pg_dump -h db.beqpplqfamcpyuzgzhcs.supabase.co \
        -U postgres \
        -d postgres \
        -f backup_completo.sql

# Backup apenas estrutura
pg_dump -h db.beqpplqfamcpyuzgzhcs.supabase.co \
        -U postgres \
        -d postgres \
        --schema-only \
        -f backup_estrutura.sql

# Backup apenas dados
pg_dump -h db.beqpplqfamcpyuzgzhcs.supabase.co \
        -U postgres \
        -d postgres \
        --data-only \
        -f backup_dados.sql
```

---

## 4. Script de Backup Automatizado

### Criar script para backup via Supabase API:
```javascript
// backup_script.js
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  'https://beqpplqfamcpyuzgzhcs.supabase.co',
  'SUA_SERVICE_KEY_AQUI'
);

async function backupDatabase() {
  try {
    // Backup tabela clientes
    const { data: clientes, error: clientesError } = await supabase
      .from('clientes')
      .select('*');
    
    if (clientesError) throw clientesError;
    
    // Backup tabela movimentacoes
    const { data: movimentacoes, error: movimentacoesError } = await supabase
      .from('movimentacoes')
      .select('*');
    
    if (movimentacoesError) throw movimentacoesError;
    
    // Salvar backup
    const backup = {
      timestamp: new Date().toISOString(),
      clientes,
      movimentacoes
    };
    
    console.log('Backup criado com sucesso!');
    console.log(`Clientes: ${clientes.length} registros`);
    console.log(`Movimentações: ${movimentacoes.length} registros`);
    
    return backup;
  } catch (error) {
    console.error('Erro no backup:', error);
  }
}

backupDatabase();
```

---

## 5. Restauração Rápida

### Se algo quebrar, para restaurar:
1. **Via Dashboard**: Import > Upload do arquivo `.sql`
2. **Via SQL**: Execute o arquivo de backup no SQL Editor
3. **Via API**: Use o script de backup para restaurar dados

---

## ⚠️ IMPORTANTE

### Antes de executar migrations:
1. ✅ **Faça backup completo**
2. ✅ **Teste em ambiente de desenvolvimento**
3. ✅ **Tenha plano de rollback**
4. ✅ **Monitore logs do n8n**

### Arquivos de Backup Recomendados:
- `backup_estrutura.sql` - Estrutura das tabelas
- `backup_dados.sql` - Dados atuais
- `backup_completo.sql` - Estrutura + Dados
- `backup_timestamp.json` - Backup via API (JSON)

---

## 🚀 Próximos Passos

1. **Execute o backup via Dashboard** (mais seguro)
2. **Salve os arquivos** em local seguro
3. **Teste a restauração** em ambiente de teste
4. **Execute as migrations** com segurança
5. **Monitore o n8n** após as mudanças
