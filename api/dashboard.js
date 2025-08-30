// API endpoints for dashboard data
import { getUserByUsername, getDashboardData, getTransactionsByUser } from '../database/queries.js';

// Get dashboard data for current month
export const getDashboard = async (req, res) => {
  try {
    // For demo purposes, using hardcoded user
    // In production, this would come from authentication middleware
    const user = await getUserByUsername('admin');
    
    if (!user) {
      return res.status(404).json({ error: 'User not found' });
    }

    const currentDate = new Date();
    const month = req.query.month || currentDate.getMonth() + 1;
    const year = req.query.year || currentDate.getFullYear();

    const dashboardData = await getDashboardData(user.id, month, year);
    const recentTransactions = await getTransactionsByUser(user.id, 10);

    res.json({
      success: true,
      data: {
        ...dashboardData,
        recentTransactions,
        month,
        year
      }
    });
  } catch (error) {
    console.error('Dashboard API error:', error);
    res.status(500).json({ 
      success: false, 
      error: 'Internal server error' 
    });
  }
};

// Get user transactions with pagination
export const getTransactions = async (req, res) => {
  try {
    const user = await getUserByUsername('admin');
    
    if (!user) {
      return res.status(404).json({ error: 'User not found' });
    }

    const limit = parseInt(req.query.limit) || 50;
    const transactions = await getTransactionsByUser(user.id, limit);

    res.json({
      success: true,
      data: {
        transactions,
        total: transactions.length
      }
    });
  } catch (error) {
    console.error('Transactions API error:', error);
    res.status(500).json({ 
      success: false, 
      error: 'Internal server error' 
    });
  }
};

// Test database connection
export const testDatabase = async (req, res) => {
  try {
    const { testConnection } = await import('../database/connection.js');
    const isConnected = await testConnection();
    
    res.json({
      success: isConnected,
      message: isConnected ? 'Database connection successful' : 'Database connection failed'
    });
  } catch (error) {
    console.error('Database test error:', error);
    res.status(500).json({ 
      success: false, 
      error: 'Database test failed',
      details: error.message
    });
  }
};