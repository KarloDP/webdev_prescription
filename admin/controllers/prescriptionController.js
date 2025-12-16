const Prescription = require('../models/prescriptionModel');

async function showPrescriptions(req, res, next) {
  try {
    const { name } = req.query;
    const prescriptions = name
      ? await Prescription.searchByName(name)
      : await Prescription.getAll();
    res.render('pages/prescriptions', { prescriptions, name });
  } catch (err) {
    next(err);
  }
}

async function showHistory(req, res, next) {
  try {
    const { id } = req.params;
    const history = await Prescription.getDispenseHistory(id);
    res.render('pages/prescriptionHistory', { history, prescriptionID: id });
  } catch (err) {
    next(err);
  }
}

module.exports = { showPrescriptions, showHistory };
