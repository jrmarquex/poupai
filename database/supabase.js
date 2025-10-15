// Supabase client configuration for Poupa.AI
import { createClient } from '@supabase/supabase-js';
import dotenv from 'dotenv';

// Load environment variables
dotenv.config({ path: './config.env' });

// Supabase configuration
const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL;
const supabaseAnonKey = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY;
const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

if (!supabaseUrl || !supabaseAnonKey) {
  throw new Error('Missing Supabase environment variables. Please check your config.env file.');
}

// Create Supabase client for client-side operations
export const supabase = createClient(supabaseUrl, supabaseAnonKey);

// Create Supabase client for server-side operations (with service role)
export const supabaseAdmin = createClient(supabaseUrl, supabaseServiceKey);

// Helper functions for common operations
export const supabaseHelpers = {
  // Get current user
  async getCurrentUser() {
    const { data: { user }, error } = await supabase.auth.getUser();
    if (error) throw error;
    return user;
  },

  // Sign in with email and password
  async signIn(email, password) {
    const { data, error } = await supabase.auth.signInWithPassword({
      email,
      password
    });
    if (error) throw error;
    return data;
  },

  // Sign up with email and password
  async signUp(email, password, userData = {}) {
    const { data, error } = await supabase.auth.signUp({
      email,
      password,
      options: {
        data: userData
      }
    });
    if (error) throw error;
    return data;
  },

  // Sign out
  async signOut() {
    const { error } = await supabase.auth.signOut();
    if (error) throw error;
  },

  // Get all clientes
  async getClientes() {
    const { data, error } = await supabaseAdmin
      .from('clientes')
      .select('*')
      .order('created_at', { ascending: false });
    
    if (error) throw error;
    return data;
  },

  // Get cliente by clientid
  async getClienteById(clientid) {
    const { data, error } = await supabaseAdmin
      .from('clientes')
      .select('*')
      .eq('clientid', clientid)
      .single();
    
    if (error) throw error;
    return data;
  },

  // Get cliente by whatsapp
  async getClienteByWhatsapp(whatsapp) {
    const { data, error } = await supabaseAdmin
      .from('clientes')
      .select('*')
      .eq('whatsapp', whatsapp)
      .single();
    
    if (error) throw error;
    return data;
  },

  // Create new cliente
  async createCliente(clienteData) {
    const { data, error } = await supabaseAdmin
      .from('clientes')
      .insert([clienteData])
      .select()
      .single();
    
    if (error) throw error;
    return data;
  },

  // Update cliente
  async updateCliente(clientid, updates) {
    const { data, error } = await supabaseAdmin
      .from('clientes')
      .update(updates)
      .eq('clientid', clientid)
      .select()
      .single();
    
    if (error) throw error;
    return data;
  },

  // Delete cliente
  async deleteCliente(clientid) {
    const { error } = await supabaseAdmin
      .from('clientes')
      .delete()
      .eq('clientid', clientid);
    
    if (error) throw error;
    return true;
  },

  // Get movimentacoes by clientid
  async getMovimentacoesByCliente(clientid) {
    const { data, error } = await supabaseAdmin
      .from('movimentacoes')
      .select('*')
      .eq('clientid', clientid)
      .order('data_movimentacao', { ascending: false });
    
    if (error) throw error;
    return data;
  },

  // Get movimentacoes by date range
  async getMovimentacoesByDateRange(clientid, startDate, endDate) {
    const { data, error } = await supabaseAdmin
      .from('movimentacoes')
      .select('*')
      .eq('clientid', clientid)
      .gte('data_movimentacao', startDate)
      .lte('data_movimentacao', endDate)
      .order('data_movimentacao', { ascending: false });
    
    if (error) throw error;
    return data;
  },

  // Create new movimentacao
  async createMovimentacao(movimentacaoData) {
    const { data, error } = await supabaseAdmin
      .from('movimentacoes')
      .insert([movimentacaoData])
      .select()
      .single();
    
    if (error) throw error;
    return data;
  },

  // Update movimentacao
  async updateMovimentacao(id, updates) {
    const { data, error } = await supabaseAdmin
      .from('movimentacoes')
      .update(updates)
      .eq('id', id)
      .select()
      .single();
    
    if (error) throw error;
    return data;
  },

  // Delete movimentacao
  async deleteMovimentacao(id) {
    const { error } = await supabaseAdmin
      .from('movimentacoes')
      .delete()
      .eq('id', id);
    
    if (error) throw error;
    return true;
  },

  // Get summary by type (Despesa/Receita)
  async getSummaryByType(clientid, type) {
    const { data, error } = await supabaseAdmin
      .from('movimentacoes')
      .select('valor_movimentacao')
      .eq('clientid', clientid)
      .eq('type', type);
    
    if (error) throw error;
    
    const total = data.reduce((sum, item) => sum + parseFloat(item.valor_movimentacao || 0), 0);
    return {
      total,
      count: data.length
    };
  },

  // Get top categories
  async getTopCategories(clientid, type = null) {
    let query = supabaseAdmin
      .from('movimentacoes')
      .select('category, valor_movimentacao')
      .eq('clientid', clientid);
    
    if (type) {
      query = query.eq('type', type);
    }
    
    const { data, error } = await query;
    
    if (error) throw error;
    
    // Group by category and calculate totals
    const categoryTotals = {};
    data.forEach(item => {
      const category = item.category || 'Outros';
      if (!categoryTotals[category]) {
        categoryTotals[category] = { total: 0, count: 0 };
      }
      categoryTotals[category].total += parseFloat(item.valor_movimentacao || 0);
      categoryTotals[category].count += 1;
    });
    
    // Convert to array and sort by total
    return Object.entries(categoryTotals)
      .map(([category, stats]) => ({ category, ...stats }))
      .sort((a, b) => b.total - a.total)
      .slice(0, 10);
  },

  // Get monthly summary
  async getMonthlySummary(clientid, year, month) {
    const startDate = `${year}-${month.toString().padStart(2, '0')}-01`;
    const endDate = `${year}-${month.toString().padStart(2, '0')}-31`;
    
    const [despesas, receitas] = await Promise.all([
      this.getSummaryByType(clientid, 'Despesa'),
      this.getSummaryByType(clientid, 'Receita')
    ]);
    
    return {
      totalDespesas: despesas.total,
      totalReceitas: receitas.total,
      balance: receitas.total - despesas.total,
      countDespesas: despesas.count,
      countReceitas: receitas.count
    };
  }
};

export default supabase;