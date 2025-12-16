const express = require('express');
const router = express.Router();
const {
  showMedications,
  filterByPharmacyName
} = require('../controllers/medicationController');

router.get('/medications', showMedications);
router.get('/medications/filter', filterByPharmacyName);

module.exports = router;
