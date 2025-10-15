// Script de Backup Automatizado para Supabase
// Execute: node backup_script.js

import { createClient } from '@supabase/supabase-js';
import fs from 'fs';
import path from 'path';

// Configuração do Supabase
const supabaseUrl = 'https://beqpplqfamcpyuzgzhcs.supabase.co';
const supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImJlcXBwbHFmYW1jcHl1emd6aGNzIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1MTE3MzUyMywiZXhwIjoyMDY2NzQ5NTIzfQ.JrvEhJ2wDnEQK8i85BQiCF-_dUdIZfKHQUbEwuWXu5A';

const supabase = createClient(supabaseUrl, supabaseKey);

async function createBackup() {
  const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
  const backupDir = `backups/backup_${timestamp}`;
  
  // Criar diretório de backup
  if (!fs.existsSync('backups')) {
    fs.mkdirSync('backups');
  }
  if (!fs.existsSync(backupDir)) {
    fs.mkdirSync(backupDir);
  }

  try {
    console.log('🔄 Iniciando backup...');
    
    // 1. Backup da tabela clientes
    console.log('📊 Fazendo backup da tabela clientes...');
    const { data: clientes, error: clientesError } = await supabase
      .from('clientes')
      .select('*');
    
    if (clientesError) {
      throw new Error(`Erro ao fazer backup de clientes: ${clientesError.message}`);
    }
    
    // 2. Backup da tabela movimentacoes
    console.log('📊 Fazendo backup da tabela movimentacoes...');
    const { data: movimentacoes, error: movimentacoesError } = await supabase
      .from('movimentacoes')
      .select('*');
    
    if (movimentacoesError) {
      throw new Error(`Erro ao fazer backup de movimentacoes: ${movimentacoesError.message}`);
    }
    
    // 3. Criar arquivo de backup JSON
    const backup = {
      timestamp: new Date().toISOString(),
      version: '1.0',
      tables: {
        clientes: {
          count: clientes.length,
          data: clientes
        },
        movimentacoes: {
          count: movimentacoes.length,
          data: movimentacoes
        }
      }
    };
    
    // 4. Salvar backup JSON
    const jsonFile = path.join(backupDir, 'backup_completo.json');
    fs.writeFileSync(jsonFile, JSON.stringify(backup, null, 2));
    
    // 5. Criar arquivo SQL para restauração
    const sqlFile = path.join(backupDir, 'restore_backup.sql');
    let sqlContent = `-- Backup criado em: ${backup.timestamp}\n`;
    sqlContent += `-- Tabela: clientes (${clientes.length} registros)\n`;
    sqlContent += `-- Tabela: movimentacoes (${movimentacoes.length} registros)\n\n`;
    
    // SQL para restaurar clientes
    if (clientes.length > 0) {
      sqlContent += `-- Restaurar tabela clientes\n`;
      sqlContent += `DELETE FROM clientes;\n`;
      sqlContent += `INSERT INTO clientes (clientid, whatsapp, status, created_at, updated_at) VALUES\n`;
      
      const clientesValues = clientes.map(c => 
        `('${c.clientid}', ${c.whatsapp ? `'${c.whatsapp}'` : 'NULL'}, ${c.status}, '${c.created_at}', '${c.updated_at}')`
      ).join(',\n');
      
      sqlContent += clientesValues + ';\n\n';
    }
    
    // SQL para restaurar movimentacoes
    if (movimentacoes.length > 0) {
      sqlContent += `-- Restaurar tabela movimentacoes\n`;
      sqlContent += `DELETE FROM movimentacoes;\n`;
      sqlContent += `INSERT INTO movimentacoes (id, data_movimentacao, valor_movimentacao, clientid, type, category, observation, created_at, updated_at) VALUES\n`;
      
      const movimentacoesValues = movimentacoes.map(m => 
        `('${m.id}', '${m.data_movimentacao}', ${m.valor_movimentacao}, '${m.clientid}', '${m.type}', '${m.category}', ${m.observation ? `'${m.observation.replace(/'/g, "''")}'` : 'NULL'}, '${m.created_at}', '${m.updated_at}')`
      ).join(',\n');
      
      sqlContent += movimentacoesValues + ';\n';
    }
    
    fs.writeFileSync(sqlFile, sqlContent);
    
    // 6. Criar arquivo de informações
    const infoFile = path.join(backupDir, 'backup_info.txt');
    const infoContent = `BACKUP CRIADO EM: ${backup.timestamp}
    
TABELAS BACKUPADAS:
- clientes: ${clientes.length} registros
- movimentacoes: ${movimentacoes.length} registros

ARQUIVOS CRIADOS:
- backup_completo.json: Dados completos em formato JSON
- restore_backup.sql: Script SQL para restauração
- backup_info.txt: Este arquivo

COMO RESTAURAR:
1. Acesse o Supabase Dashboard
2. Vá em SQL Editor
3. Execute o arquivo restore_backup.sql
4. Ou use o backup_completo.json via API

IMPORTANTE: Este backup foi criado ANTES de executar as migrations.
Se algo der errado, use este backup para restaurar o estado anterior.
`;
    
    fs.writeFileSync(infoFile, infoContent);
    
    console.log('✅ Backup criado com sucesso!');
    console.log(`📁 Diretório: ${backupDir}`);
    console.log(`📊 Clientes: ${clientes.length} registros`);
    console.log(`📊 Movimentações: ${movimentacoes.length} registros`);
    console.log(`📄 Arquivos criados:`);
    console.log(`   - backup_completo.json`);
    console.log(`   - restore_backup.sql`);
    console.log(`   - backup_info.txt`);
    
    return backupDir;
    
  } catch (error) {
    console.error('❌ Erro durante o backup:', error.message);
    process.exit(1);
  }
}

// Executar backup
createBackup();
