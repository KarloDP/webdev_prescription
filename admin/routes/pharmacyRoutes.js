const express = require('express');
const router = express.Router();
const {
  showPharmacies,
  addPharmacy,
  approvePharmacy,
  denyPharmacy,
  deactivatePharmacy,
  activatePharmacy,
  deletePharmacy
} = require('../controllers/pharmacyController');

router.get('/pharmacies', showPharmacies);
router.post('/pharmacies/add', addPharmacy);
router.post('/pharmacies/approve', approvePharmacy);
router.post('/pharmacies/deny', denyPharmacy);
router.post('/pharmacies/deactivate', deactivatePharmacy);
router.post('/pharmacies/activate', activatePharmacy);
router.post('/pharmacies/delete', deletePharmacy);

module.exports = router;
