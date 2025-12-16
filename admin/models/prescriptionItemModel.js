const pool = require('../config/db');

const PrescriptionItem = {
  async getAll() {
    const [rows] = await pool.query('SELECT * FROM prescriptionitem');
    return rows;
  },

  async getByPrescription(prescriptionID) {
    const [rows] = await pool.query('SELECT * FROM prescriptionitem WHERE prescriptionID = ?', [prescriptionID]);
    return rows;
  }
};
module.exports = PrescriptionItem;