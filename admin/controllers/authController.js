// controllers/authController.js
const Admin = require('../models/adminModel');
const Doctor = require('../models/doctorModel');
const Patient = require('../models/patientModel');
const Medication = require('../models/medicationModel');
const Pharmacy = require('../models/pharmacyModel');
const Prescription = require('../models/prescriptionModel');
const PrescriptionItem = require('../models/prescriptionItemModel');
const DispenseRecord = require('../models/dispenseRecordModel');

async function showLoginForm(req, res, next) {
  try {
    const admins = await Admin.getAll();
    res.render('login', { admins, error: null });
  } catch (err) {
    next(err);
  }
}

async function login(req, res, next) {
  try {
    const { adminId } = req.body;

    if (!adminId) {
      const admins = await Admin.getAll();
      return res.render('login', { admins, error: 'Please select an admin.' });
    }

    const admin = await Admin.getById(adminId);
    if (!admin) {
      const admins = await Admin.getAll();
      return res.render('login', { admins, error: 'Admin not found.' });
    }

    req.session.admin = admin;
    res.redirect('/dashboard');
  } catch (err) {
    next(err);
  }
}

async function showDashboard(req, res, next) {
  try {
    if (!req.session.admin) return res.redirect('/login');

    // Fetch all data in parallel
    const [
      admins,
      doctors,
      patients,
      medications,
      pharmacies,
      prescriptions,
      prescriptionItems,
      dispenseRecords
    ] = await Promise.all([
      Admin.getAll(),
      Doctor.getAll(),
      Patient.getAll(),
      Medication.getAll(),
      Pharmacy.getAll(),
      Prescription.getAll(),
      PrescriptionItem.getAll(),
      DispenseRecord.getAll()
    ]);

    // Build tables array with counts
    const tables = [
      { name: 'Admins',              count: admins.length,            link: '/admins' },
      { name: 'Doctors',             count: doctors.length,           link: '/doctors' },
      { name: 'Patients',            count: patients.length,          link: '/patients' },
      { name: 'Medications',         count: medications.length,       link: '/medications' },
      { name: 'Pharmacies',          count: pharmacies.length,        link: '/pharmacies' },
      { name: 'Prescriptions',       count: prescriptions.length,     link: '/prescriptions' },
      { name: 'Prescription Items',  count: prescriptionItems.length, link: '/prescription-items' },
      { name: 'Dispense Records',    count: dispenseRecords.length,   link: '/dispense-records' }
    ];

    // Render dashboard with admin info and tables
    res.render('pages/dashboard/dashboard', {
      admin: req.session.admin,
      tables
    });
  } catch (err) {
    next(err);
  }
}

function logout(req, res, next) {
  try {
    req.session.destroy(() => {
      res.redirect('/login');
    });
  } catch (err) {
    next(err);
  }
}

// ✨ SINGLE CLEAR EXPORT ✨
module.exports = {
  showLoginForm,
  login,
  showDashboard,
  logout
};
