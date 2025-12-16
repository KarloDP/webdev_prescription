const express = require('express');
const router = express.Router();

const {
  showPrescriptionItems,
  showByPrescription
} = require('../controllers/prescriptionItemController');

router.get('/prescription-items', showPrescriptionItems);

router.get('/prescription-items/prescription/:prescriptionID', showByPrescription);

module.exports = router;
