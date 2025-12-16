const DispenseRecord = require('../models/dispenseRecordModel');

async function showByPrescription(req, res, next) {
  try {
    const { id } = req.params;
    const records = await DispenseRecord.getByPrescription(id);
    res.render('pages/dispenseRecordsByPrescription', { records, prescriptionID: id });
  } catch (err) {
    next(err);
  }
}

async function showAll(req, res, next) {
  try {
    const { prescriptionID, medication, pharmacy } = req.query;
    let records;
    if (prescriptionID || medication || pharmacy) {
      records = await DispenseRecord.search({ prescriptionID, medication, pharmacy });
    } else {
      records = await DispenseRecord.getAll();
    }
    res.render('pages/dispenseRecords', { records, prescriptionID, medication, pharmacy });
  } catch (err) {
    next(err);
  }
}

module.exports = { showByPrescription, showAll };
