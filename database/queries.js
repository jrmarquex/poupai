// Database queries for AssistenteFin
import { eq, desc, sum, count, sql } from 'drizzle-orm';
import { getDatabase } from './connection.js';
import { users, transactions, categories, monthlySummaries } from './schema.js';

// User queries
export const getUserByUsername = async (username) => {
  const db = getDatabase();
  const result = await db.select().from(users).where(eq(users.username, username)).limit(1);
  return result[0] || null;
};

export const createUser = async (userData) => {
  const db = getDatabase();
  const result = await db.insert(users).values(userData).returning();
  return result[0];
};

// Transaction queries
export const getTransactionsByUser = async (userId, limit = 50) => {
  const db = getDatabase();
  return await db
    .select({
      id: transactions.id,
      amount: transactions.amount,
      description: transactions.description,
      establishment: transactions.establishment,
      type: transactions.type,
      categoryName: categories.name,
      categoryColor: categories.color,
      createdAt: transactions.createdAt
    })
    .from(transactions)
    .leftJoin(categories, eq(transactions.categoryId, categories.id))
    .where(eq(transactions.userId, userId))
    .orderBy(desc(transactions.createdAt))
    .limit(limit);
};

export const createTransaction = async (transactionData) => {
  const db = getDatabase();
  const result = await db.insert(transactions).values(transactionData).returning();
  return result[0];
};

// Category queries
export const getAllCategories = async () => {
  const db = getDatabase();
  return await db.select().from(categories).orderBy(categories.name);
};

export const getCategoryByName = async (name) => {
  const db = getDatabase();
  const result = await db.select().from(categories).where(eq(categories.name, name)).limit(1);
  return result[0] || null;
};

// Dashboard queries
export const getDashboardData = async (userId, month, year) => {
  const db = getDatabase();
  
  // Get monthly transactions
  const monthlyTransactions = await db
    .select({
      categoryName: categories.name,
      categoryColor: categories.color,
      type: transactions.type,
      totalAmount: sum(transactions.amount),
      transactionCount: count(transactions.id)
    })
    .from(transactions)
    .leftJoin(categories, eq(transactions.categoryId, categories.id))
    .where(
      sql`${transactions.userId} = ${userId} 
          AND EXTRACT(MONTH FROM ${transactions.createdAt}) = ${month} 
          AND EXTRACT(YEAR FROM ${transactions.createdAt}) = ${year}`
    )
    .groupBy(categories.name, categories.color, transactions.type);

  // Get top establishments
  const topEstablishments = await db
    .select({
      establishment: transactions.establishment,
      totalAmount: sum(transactions.amount),
      categoryColor: categories.color
    })
    .from(transactions)
    .leftJoin(categories, eq(transactions.categoryId, categories.id))
    .where(
      sql`${transactions.userId} = ${userId} 
          AND ${transactions.type} = 'expense'
          AND ${transactions.establishment} IS NOT NULL
          AND EXTRACT(MONTH FROM ${transactions.createdAt}) = ${month} 
          AND EXTRACT(YEAR FROM ${transactions.createdAt}) = ${year}`
    )
    .groupBy(transactions.establishment, categories.color)
    .orderBy(desc(sum(transactions.amount)))
    .limit(5);

  // Get financial summary
  const income = await db
    .select({ total: sum(transactions.amount) })
    .from(transactions)
    .where(
      sql`${transactions.userId} = ${userId} 
          AND ${transactions.type} = 'income'
          AND EXTRACT(MONTH FROM ${transactions.createdAt}) = ${month} 
          AND EXTRACT(YEAR FROM ${transactions.createdAt}) = ${year}`
    );

  const expenses = await db
    .select({ total: sum(transactions.amount) })
    .from(transactions)
    .where(
      sql`${transactions.userId} = ${userId} 
          AND ${transactions.type} = 'expense'
          AND EXTRACT(MONTH FROM ${transactions.createdAt}) = ${month} 
          AND EXTRACT(YEAR FROM ${transactions.createdAt}) = ${year}`
    );

  return {
    categories: monthlyTransactions,
    topEstablishments,
    summary: {
      income: income[0]?.total || 0,
      expenses: expenses[0]?.total || 0,
      balance: (income[0]?.total || 0) - (expenses[0]?.total || 0)
    }
  };
};

// Initialize default categories
export const initializeDefaultCategories = async () => {
  const db = getDatabase();
  
  const defaultCategories = [
    { name: 'Transporte', color: '#20C997', icon: 'car', type: 'expense' },
    { name: 'Alimentação', color: '#A78BFA', icon: 'utensils', type: 'expense' },
    { name: 'Lazer', color: '#22D3EE', icon: 'gamepad', type: 'expense' },
    { name: 'Outros', color: '#6B7280', icon: 'more', type: 'expense' },
    { name: 'Salário CLT', color: '#10B981', icon: 'briefcase', type: 'income' },
    { name: 'Trabalho Freelancer', color: '#F59E0B', icon: 'laptop', type: 'income' },
    { name: 'Delivery', color: '#EF4444', icon: 'truck', type: 'income' }
  ];

  for (const category of defaultCategories) {
    const existing = await getCategoryByName(category.name);
    if (!existing) {
      await db.insert(categories).values(category);
    }
  }
};

export default {
  getUserByUsername,
  createUser,
  getTransactionsByUser,
  createTransaction,
  getAllCategories,
  getCategoryByName,
  getDashboardData,
  initializeDefaultCategories
};