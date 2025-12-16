const pool = require('../config/db');

const Pharmacy = {
  async getAll() {
    const [rows] = await pool.query('SELECT * FROM pharmacy');
    return rows;
  },

  async addPharmacy({ name, password, address, contactNumber, email, clinicAddress }) {
    await pool.query(
      `INSERT INTO pharmacy (name, password, address, contactNumber, email, clinicAddress, status)
       VALUES (?, ?, ?, ?, ?, ?, "pending")`,
      [name, password, address, contactNumber, email, clinicAddress]
    );
  },

  async approve(pharmacyID) {
    await pool.query('UPDATE pharmacy SET status = "active" WHERE pharmacyID = ?', [pharmacyID]);
  },

  async deny(pharmacyID) {
    await pool.query('DELETE FROM pharmacy WHERE pharmacyID = ?', [pharmacyID]);
  },

  async deactivate(pharmacyID) {
    await pool.query('UPDATE pharmacy SET status = "inactive" WHERE pharmacyID = ?', [pharmacyID]);
  },

  async activate(pharmacyID) {
    await pool.query('UPDATE pharmacy SET status = "active" WHERE pharmacyID = ?', [pharmacyID]);
  },

  async delete(pharmacyID) {
    await pool.query('DELETE FROM pharmacy WHERE pharmacyID = ?', [pharmacyID]);
  }
};

module.exports = Pharmacy;
