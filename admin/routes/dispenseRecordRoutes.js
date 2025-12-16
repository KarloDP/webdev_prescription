const express = require('express');
const router = express.Router();
const DispenseRecord = require('../models/dispenseRecordModel');

// Show specific dispense record
async function showHistory(req, res, next) {
  try {
    const { id } = req.params;
    const history = await DispenseRecord.getByPrescription(id);

    let html = `<h1>Dispense Records for Prescription #${id}</h1>
      <table border="1" cellpadding="8" cellspacing="0">
        <thead><tr>
          <th>ID</th><th>Date Dispensed</th><th>Medication</th><th>Quantity</th><th>Pharmacy</th>
        </tr></thead><tbody>`;

    history.forEach(r => {
      html += `<tr>
        <td>${r.dispenseID}</td>
        <td>${r.dispenseDate}</td>
        <td>${r.brandName} / ${r.genericName}</td>
        <td>${r.dispensedQuantity}</td>
        <td>${r.pharmacyName}</td>
      </tr>`;
    });

    html += `</tbody></table><br><a href="/prescriptions">Back to Prescriptions</a>`;
    res.send(html);
  } catch (err) {
    next(err);
  }
}

// Show all dispense records
async function showAll(req, res, next) {
  try {
    const rows = await DispenseRecord.getAll();

    let html = `<h1>All Dispense Records</h1>
      <table border="1" cellpadding="8" cellspacing="0">
        <thead><tr>
          <th>ID</th><th>Date Dispensed</th><th>Prescription ID</th>
          <th>Medication</th><th>Quantity</th><th>Pharmacy</th>
        </tr></thead><tbody>`;

    rows.forEach(r => {
      html += `<tr>
        <td>${r.dispenseID}</td>
        <td>${r.dispenseDate}</td>
        <td>${r.prescriptionID}</td>
        <td>${r.brandName} / ${r.genericName}</td>
        <td>${r.dispensedQuantity}</td>
        <td>${r.pharmacyName}</td>
      </tr>`;
    });

    html += `</tbody></table><br><a href="/dashboard">Back to Dashboard</a>`;
    res.send(html);
  } catch (err) {
    next(err);
  }
}


router.get('/prescriptions/:id/dispense-records', showHistory);
router.get('/dispense-records', showAll);

module.exports = router;
