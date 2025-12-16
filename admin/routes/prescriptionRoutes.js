const express = require('express');
const router = express.Router();
const { showPrescriptions, showHistory } = require('../controllers/prescriptionController');

router.get('/prescriptions', showPrescriptions);
router.get('/prescriptions/:id/history', showHistory);

module.exports = router;
