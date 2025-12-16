const pool = require('../config/db');

const DispenseRecord = {
  async getAll() {
    const [rows] = await pool.query(`
      SELECT dr.dispenseID, dr.dispenseDate, dr.dispensedQuantity,
             pi.prescriptionID,
             m.brandName, m.genericName,
             ph.name AS pharmacyName
      FROM dispenserecord dr
      JOIN prescriptionitem pi ON dr.prescriptionItemID = pi.prescriptionItemID
      JOIN medication m ON pi.medicationID = m.medicationID
      JOIN pharmacy ph ON dr.pharmacyID = ph.pharmacyID
      ORDER BY dr.dispenseDate DESC
    `);
    return rows;
  },

  async getByPrescription(prescriptionID) {
    const [rows] = await pool.query(`
      SELECT dr.dispenseID, dr.dispenseDate, dr.dispensedQuantity,
             m.brandName, m.genericName,
             ph.name AS pharmacyName
      FROM dispenserecord dr
      JOIN prescriptionitem pi ON dr.prescriptionItemID = pi.prescriptionItemID
      JOIN medication m ON pi.medicationID = m.medicationID
      JOIN pharmacy ph ON dr.pharmacyID = ph.pharmacyID
      WHERE pi.prescriptionID = ?
      ORDER BY dr.dispenseDate DESC
    `, [prescriptionID]);
    return rows;
  }
};

async search({ prescriptionID, medication, pharmacy }) {
    let query = `
      SELECT dr.dispenseID, dr.dispenseDate, dr.dispensedQuantity,
             pi.prescriptionID,
             m.brandName, m.genericName,
             ph.name AS pharmacyName
      FROM dispenserecord dr
      JOIN prescriptionitem pi ON dr.prescriptionItemID = pi.prescriptionItemID
      JOIN medication m ON pi.medicationID = m.medicationID
      JOIN pharmacy ph ON dr.pharmacyID = ph.pharmacyID
      WHERE 1=1`;
    const params = [];
    if (prescriptionID) {
      query += ' AND pi.prescriptionID = ?';
      params.push(prescriptionID);
    }
    if (medication) {
      query += ' AND (m.brandName LIKE ? OR m.genericName LIKE ?)';
      params.push(`%${medication}%`, `%${medication}%`);
    }
    if (pharmacy) {
      query += ' AND ph.name LIKE ?';
      params.push(`%${pharmacy}%`);
    }
    query += ' ORDER BY dr.dispenseDate DESC';
    const [rows] = await pool.query(query, params);
    return rows;
  },
  
module.exports = DispenseRecord;
