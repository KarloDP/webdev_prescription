const express = require('express');
const router = express.Router();

// Imports ALL controller functions
const {
  showDoctors,
  acceptDoctor,
  addDoctor,
  deactivateDoctor,
  deleteDoctor
} = require('../controllers/doctorController');

// Show all doctors
router.get('/doctors', showDoctors);

// Accept doctor application
router.post('/doctors/accept', acceptDoctor);

// Add new doctor
router.post('/doctors/add', addDoctor);

// Deactivate doctor
router.post('/doctors/deactivate', deactivateDoctor);

// Delete doctor
router.post('/doctors/delete', deleteDoctor);

module.exports = router;
