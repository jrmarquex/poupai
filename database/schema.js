// Database Schema for Poupa.AI using Drizzle ORM
// This schema matches your existing Supabase tables: clientes and movimentacoes
import { pgTable, uuid, text, boolean, timestamp, numeric, date } from 'drizzle-orm/pg-core';

// Clientes table (matches your existing structure)
export const clientes = pgTable('clientes', {
  clientid: uuid('clientid').primaryKey().defaultRandom(),
  whatsapp: text('whatsapp'),
  status: boolean('status').default(true),
  createdAt: timestamp('created_at').defaultNow(),
  updatedAt: timestamp('updated_at').defaultNow()
});

// Movimentacoes table (matches your existing structure)
export const movimentacoes = pgTable('movimentacoes', {
  id: text('id').primaryKey(),
  dataMovimentacao: date('data_movimentacao'),
  valorMovimentacao: numeric('valor_movimentacao', { precision: 10, scale: 2 }),
  clientid: uuid('clientid').references(() => clientes.clientid),
  type: text('type'), // 'Despesa' or 'Receita'
  category: text('category'),
  observation: text('observation'),
  createdAt: timestamp('created_at').defaultNow(),
  updatedAt: timestamp('updated_at').defaultNow()
});

// Helper functions for common queries
export const schemaHelpers = {
  // Get all clientes
  async getAllClientes(db) {
    return await db.select().from(clientes);
  },

  // Get cliente by clientid
  async getClienteById(db, clientid) {
    const result = await db.select().from(clientes).where(eq(clientes.clientid, clientid));
    return result[0] || null;
  },

  // Get cliente by whatsapp
  async getClienteByWhatsapp(db, whatsapp) {
    const result = await db.select().from(clientes).where(eq(clientes.whatsapp, whatsapp));
    return result[0] || null;
  },

  // Get movimentacoes by clientid
  async getMovimentacoesByCliente(db, clientid) {
    return await db
      .select()
      .from(movimentacoes)
      .where(eq(movimentacoes.clientid, clientid))
      .orderBy(desc(movimentacoes.dataMovimentacao));
  },

  // Get movimentacoes by date range
  async getMovimentacoesByDateRange(db, clientid, startDate, endDate) {
    return await db
      .select()
      .from(movimentacoes)
      .where(
        and(
          eq(movimentacoes.clientid, clientid),
          gte(movimentacoes.dataMovimentacao, startDate),
          lte(movimentacoes.dataMovimentacao, endDate)
        )
      )
      .orderBy(desc(movimentacoes.dataMovimentacao));
  },

  // Get summary by type (Despesa/Receita)
  async getSummaryByType(db, clientid, type) {
    const result = await db
      .select({
        total: sum(movimentacoes.valorMovimentacao),
        count: count(movimentacoes.id)
      })
      .from(movimentacoes)
      .where(
        and(
          eq(movimentacoes.clientid, clientid),
          eq(movimentacoes.type, type)
        )
      );
    
    return result[0] || { total: 0, count: 0 };
  },

  // Get top categories
  async getTopCategories(db, clientid, type = null) {
    let whereCondition = eq(movimentacoes.clientid, clientid);
    
    if (type) {
      whereCondition = and(whereCondition, eq(movimentacoes.type, type));
    }

    return await db
      .select({
        category: movimentacoes.category,
        total: sum(movimentacoes.valorMovimentacao),
        count: count(movimentacoes.id)
      })
      .from(movimentacoes)
      .where(whereCondition)
      .groupBy(movimentacoes.category)
      .orderBy(desc(sum(movimentacoes.valorMovimentacao)))
      .limit(10);
  },

  // Create new movimentacao
  async createMovimentacao(db, movimentacaoData) {
    const result = await db
      .insert(movimentacoes)
      .values(movimentacaoData)
      .returning();
    
    return result[0];
  },

  // Update movimentacao
  async updateMovimentacao(db, id, updates) {
    const result = await db
      .update(movimentacoes)
      .set(updates)
      .where(eq(movimentacoes.id, id))
      .returning();
    
    return result[0];
  },

  // Delete movimentacao
  async deleteMovimentacao(db, id) {
    await db
      .delete(movimentacoes)
      .where(eq(movimentacoes.id, id));
    
    return true;
  }
};

// Import Drizzle operators
import { eq, and, gte, lte, desc, sum, count } from 'drizzle-orm';

export default { clientes, movimentacoes, schemaHelpers };