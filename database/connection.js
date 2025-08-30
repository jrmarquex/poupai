// Database connection setup for Supabase with Drizzle ORM
import { drizzle } from 'drizzle-orm/postgres-js';
import postgres from 'postgres';

// Database connection configuration
const getDatabaseUrl = () => {
  // In production, this will use the DATABASE_URL environment variable
  const dbUrl = process.env.DATABASE_URL;
  
  if (!dbUrl) {
    throw new Error('DATABASE_URL environment variable is not set');
  }
  
  return dbUrl;
};

// Create postgres client
let client;
let db;

export const initializeDatabase = () => {
  try {
    const connectionString = getDatabaseUrl();
    
    // Create postgres client with connection pooling
    client = postgres(connectionString, {
      ssl: 'prefer',
      max: 10, // connection pool size
      idle_timeout: 20,
      connect_timeout: 10
    });
    
    // Create drizzle instance
    db = drizzle(client);
    
    console.log('Database connection initialized successfully');
    return db;
  } catch (error) {
    console.error('Failed to initialize database connection:', error);
    throw error;
  }
};

// Get database instance
export const getDatabase = () => {
  if (!db) {
    return initializeDatabase();
  }
  return db;
};

// Close database connection
export const closeDatabase = async () => {
  if (client) {
    await client.end();
    console.log('Database connection closed');
  }
};

// Test database connection
export const testConnection = async () => {
  try {
    const database = getDatabase();
    await database.execute('SELECT 1');
    console.log('Database connection test successful');
    return true;
  } catch (error) {
    console.error('Database connection test failed:', error);
    return false;
  }
};

export default getDatabase;