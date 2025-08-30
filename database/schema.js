// Database Schema for AssistenteFin using Drizzle ORM
import { pgTable, serial, varchar, decimal, timestamp, text, integer } from 'drizzle-orm/pg-core';

// Users table
export const users = pgTable('users', {
  id: serial('id').primaryKey(),
  username: varchar('username', { length: 50 }).notNull().unique(),
  password: varchar('password', { length: 255 }).notNull(),
  name: varchar('name', { length: 100 }),
  whatsapp: varchar('whatsapp', { length: 20 }),
  createdAt: timestamp('created_at').defaultNow(),
  updatedAt: timestamp('updated_at').defaultNow()
});

// Categories table
export const categories = pgTable('categories', {
  id: serial('id').primaryKey(),
  name: varchar('name', { length: 50 }).notNull(),
  color: varchar('color', { length: 7 }), // hex color
  icon: varchar('icon', { length: 50 }),
  type: varchar('type', { length: 20 }).notNull(), // 'income' or 'expense'
  createdAt: timestamp('created_at').defaultNow()
});

// Transactions table
export const transactions = pgTable('transactions', {
  id: serial('id').primaryKey(),
  userId: integer('user_id').references(() => users.id),
  categoryId: integer('category_id').references(() => categories.id),
  amount: decimal('amount', { precision: 10, scale: 2 }).notNull(),
  description: text('description').notNull(),
  establishment: varchar('establishment', { length: 100 }),
  type: varchar('type', { length: 20 }).notNull(), // 'income' or 'expense'
  source: varchar('source', { length: 20 }), // 'text', 'audio', 'photo'
  originalMessage: text('original_message'), // original WhatsApp message
  processedAt: timestamp('processed_at'),
  createdAt: timestamp('created_at').defaultNow(),
  updatedAt: timestamp('updated_at').defaultNow()
});

// Monthly summaries table
export const monthlySummaries = pgTable('monthly_summaries', {
  id: serial('id').primaryKey(),
  userId: integer('user_id').references(() => users.id),
  month: integer('month').notNull(),
  year: integer('year').notNull(),
  totalIncome: decimal('total_income', { precision: 10, scale: 2 }).default('0'),
  totalExpenses: decimal('total_expenses', { precision: 10, scale: 2 }).default('0'),
  balance: decimal('balance', { precision: 10, scale: 2 }).default('0'),
  topCategory: varchar('top_category', { length: 50 }),
  topEstablishment: varchar('top_establishment', { length: 100 }),
  createdAt: timestamp('created_at').defaultNow(),
  updatedAt: timestamp('updated_at').defaultNow()
});

// WhatsApp sessions table  
export const whatsappSessions = pgTable('whatsapp_sessions', {
  id: serial('id').primaryKey(),
  userId: integer('user_id').references(() => users.id),
  whatsappId: varchar('whatsapp_id', { length: 100 }).notNull(),
  sessionToken: varchar('session_token', { length: 255 }),
  isActive: integer('is_active').default(1), // 1 for active, 0 for inactive
  lastActivity: timestamp('last_activity').defaultNow(),
  createdAt: timestamp('created_at').defaultNow()
});