const express = require('express');
const router = express.Router();

const {
  showPatients,
  addPatient,
  deletePatient
} = require('../controllers/patientController');


router.get('/patients', showPatients);
router.post('/patients/add', addPatient);
router.post('/patients/delete', deletePatient);

module.exports = router;