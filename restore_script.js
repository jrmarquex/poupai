// Script de Restauração para Supabase
// Execute: node restore_script.js [caminho_do_backup]

import { createClient } from '@supabase/supabase-js';
import fs from 'fs';
import path from 'path';

// Configuração do Supabase
const supabaseUrl = 'https://beqpplqfamcpyuzgzhcs.supabase.co';
const supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImJlcXBwbHFmYW1jcHl1emd6aGNzIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1MTE3MzUyMywiZXhwIjoyMDY2NzQ5NTIzfQ.JrvEhJ2wDnEQK8i85BQiCF-_dUdIZfKHQUbEwuWXu5A';

const supabase = createClient(supabaseUrl, supabaseKey);

async function restoreBackup(backupPath) {
  try {
    console.log('🔄 Iniciando restauração...');
    
    // Verificar se o arquivo de backup existe
    const jsonFile = path.join(backupPath, 'backup_completo.json');
    if (!fs.existsSync(jsonFile)) {
      throw new Error(`Arquivo de backup não encontrado: ${jsonFile}`);
    }
    
    // Carregar dados do backup
    const backupData = JSON.parse(fs.readFileSync(jsonFile, 'utf8'));
    console.log(`📅 Restaurando backup de: ${backupData.timestamp}`);
    
    // 1. Limpar tabelas existentes
    console.log('🗑️ Limpando tabelas existentes...');
    
    const { error: deleteMovimentacoesError } = await supabase
      .from('movimentacoes')
      .delete()
      .neq('id', '');
    
    if (deleteMovimentacoesError) {
      console.warn('⚠️ Aviso ao limpar movimentacoes:', deleteMovimentacoesError.message);
    }
    
    const { error: deleteClientesError } = await supabase
      .from('clientes')
      .delete()
      .neq('clientid', '');
    
    if (deleteClientesError) {
      console.warn('⚠️ Aviso ao limpar clientes:', deleteClientesError.message);
    }
    
    // 2. Restaurar tabela clientes
    if (backupData.tables.clientes.data.length > 0) {
      console.log(`📊 Restaurando ${backupData.tables.clientes.count} registros de clientes...`);
      
      const { error: clientesError } = await supabase
        .from('clientes')
        .insert(backupData.tables.clientes.data);
      
      if (clientesError) {
        throw new Error(`Erro ao restaurar clientes: ${clientesError.message}`);
      }
      
      console.log('✅ Clientes restaurados com sucesso!');
    }
    
    // 3. Restaurar tabela movimentacoes
    if (backupData.tables.movimentacoes.data.length > 0) {
      console.log(`📊 Restaurando ${backupData.tables.movimentacoes.count} registros de movimentacoes...`);
      
      const { error: movimentacoesError } = await supabase
        .from('movimentacoes')
        .insert(backupData.tables.movimentacoes.data);
      
      if (movimentacoesError) {
        throw new Error(`Erro ao restaurar movimentacoes: ${movimentacoesError.message}`);
      }
      
      console.log('✅ Movimentações restauradas com sucesso!');
    }
    
    // 4. Verificar restauração
    console.log('🔍 Verificando restauração...');
    
    const { data: clientesVerificacao } = await supabase
      .from('clientes')
      .select('clientid')
      .limit(1);
    
    const { data: movimentacoesVerificacao } = await supabase
      .from('movimentacoes')
      .select('id')
      .limit(1);
    
    console.log('✅ Restauração concluída com sucesso!');
    console.log(`📊 Clientes restaurados: ${clientesVerificacao ? 'Sim' : 'Não'}`);
    console.log(`📊 Movimentações restauradas: ${movimentacoesVerificacao ? 'Sim' : 'Não'}`);
    
  } catch (error) {
    console.error('❌ Erro durante a restauração:', error.message);
    process.exit(1);
  }
}

// Verificar argumentos
const backupPath = process.argv[2];

if (!backupPath) {
  console.error('❌ Uso: node restore_script.js [caminho_do_backup]');
  console.error('Exemplo: node restore_script.js backups/backup_2024-01-15T10-30-00-000Z');
  process.exit(1);
}

// Executar restauração
restoreBackup(backupPath);
