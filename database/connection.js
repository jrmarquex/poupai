// Database connection setup for Supabase with Drizzle ORM
import { drizzle } from 'drizzle-orm/postgres-js';
import postgres from 'postgres';
import dotenv from 'dotenv';

// Load environment variables
dotenv.config({ path: './config.env' });

// Database connection configuration
const getDatabaseUrl = () => {
  // Try to get DATABASE_URL from environment variables
  let dbUrl = process.env.DATABASE_URL;
  
  // If not found, try to construct it from Supabase URL
  if (!dbUrl && process.env.NEXT_PUBLIC_SUPABASE_URL) {
    const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL;
    const host = supabaseUrl.replace('https://', '').replace('.supabase.co', '');
    dbUrl = `postgresql://postgres:[YOUR_PASSWORD]@db.${host}.supabase.co:5432/postgres?sslmode=require`;
  }
  
  if (!dbUrl) {
    throw new Error('DATABASE_URL environment variable is not set. Please check your config.env file.');
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