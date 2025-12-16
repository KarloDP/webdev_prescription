const pool = require('../config/db');

const Patient = {
  async getAll() {
    const [rows] = await pool.query('Select * FROM patient');
    return rows;
  },

  async getByPatientID(patientID) {
    const [rows] = await pool.query('SELECT * FROM patient WHERE patientID = ?', [patientID]);
    return rows;
  },

  async deletePatient (patientID) {
    await pool.query('DELETE FROM patient WHERE patientID = ?', [patientID]);
  },

  async addPatient ({ firstName, lastName, password, dateOfBirth, email, phoneNumber, address, doctorID }) {
    await pool.query(
      `INSERT INTO patient
       (firstName, lastName, password, dateOfBirth, email, phoneNumber, address, doctorID)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
      [firstName, lastName, password, dateOfBirth, email, phoneNumber, address, doctorID]
    );
  }

};

  module.exports = Patient;