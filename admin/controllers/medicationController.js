const Medication = require('../models/medicationModel');

async function showMedications(req, res, next) {
  try {
    const medications = await Medication.getAll();
    res.render('pages/medications', { medications });
  } catch (err) {
    console.error('Medication Error:', err);
    next(err);
  }
}

async function filterByPharmacyName(req, res, next) {
  try {
    const { pharmacyName } = req.query;
    const medications = await Medication.getByPharmacyName(pharmacyName);

    if (!medications || medications.length === 0) {
      return res.status(404).send(`
        <h2>No Medications Found</h2>
        <p>Pharmacy "${pharmacyName}" does not exist or has no medications assigned.</p>
        <br><a href="/medications">Back to All Medications</a>
      `);
    }

    res.render('pages/medicationsByPharmacy', { medications, pharmacyName });
  } catch (err) {
    console.error('Medication Filter Error:', err);
    next(err);
  }
}

module.exports = {
  showMedications,
  filterByPharmacyName
};
