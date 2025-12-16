const PrescriptionItem = require('../models/prescriptionItemModel');

async function showPrescriptionItems(req, res, next) {
  try {
    const items = await PrescriptionItem.getAll();
    res.render('pages/prescriptionItems', { items });
  } catch (err) {
    next(err);
  }
}

async function showByPrescription(req, res, next) {
  try {
    const { prescriptionID } = req.params;
    const items = await PrescriptionItem.getByPrescription(prescriptionID);
    res.render('pages/prescriptionItemsByPrescription', { items, prescriptionID });
  } catch (err) {
    next(err);
  }
}

module.exports = {
  showPrescriptionItems,
  showByPrescription
};
