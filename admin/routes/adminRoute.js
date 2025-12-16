const express = require('express');
const router = express.Router();

const {
  showAdmins,
  acceptAdmin,
  rejectAdmin,
  deleteAdmin
} = require('../controllers/adminController');

const { showDashboard } = require('../controllers/dashboardController');

// Admin management routes
router.get('/admins', showAdmins);
router.post('/admins/accept', acceptAdmin);
router.post('/admins/reject', rejectAdmin);
router.post('/admins/delete', deleteAdmin);

// ✅ Dashboard route
router.get('/dashboard', showDashboard);

module.exports = router;
