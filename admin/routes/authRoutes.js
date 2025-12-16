// routes/authRoutes.js
const express = require('express');
const router = express.Router();

const {
  showLoginForm,
  login,
  showDashboard,
  logout
} = require('../controllers/authController');

console.log('authController keys (in routes):', {
  showLoginForm: typeof showLoginForm,
  login: typeof login,
  showDashboard: typeof showDashboard,
  logout: typeof logout
});

// Routes
router.get('/login', showLoginForm);
router.post('/login', login);
router.get('/dashboard', showDashboard);
router.get('/logout', logout);

module.exports = router;
