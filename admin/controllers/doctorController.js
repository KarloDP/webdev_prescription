const Doctor = require('../models/doctorModel');

async function showDoctors(req, res, next) {
  try {
    const { name, specialization, status } = req.query;
    let doctors;
    if (name || specialization || status) {
      doctors = await Doctor.search({ name, specialization, status });
    } else {
      doctors = await Doctor.getAll();
    }
    res.render('pages/doctors', { doctors, name, specialization, status });
  } catch (err) {
    console.error('Doctor Error:', err);
    next(err);
  }
}

async function acceptDoctor(req, res, next) {
  try {
    const { doctorID } = req.body;
    await Doctor.activateDoctor(doctorID);
    res.redirect('/doctors');
  } catch (err) {
    console.error('Doctor Accept Error:', err);
    next(err);
  }
}

async function addDoctor(req, res, next) {
  try {
    const { firstName, lastName, password, specialization, licenseNumber, email, clinicAddress } = req.body;
    await Doctor.addDoctor({ firstName, lastName, password, specialization, licenseNumber, email, clinicAddress });
    res.redirect('/doctors');
  } catch (err) {
    console.error('Doctor Add Error:', err);
    next(err);
  }
}

async function deactivateDoctor(req, res, next) {
  try {
    const { doctorID } = req.body;
    await Doctor.deactivateDoctor(doctorID);
    res.redirect('/doctors');
  } catch (err) {
    console.error('Doctor Deactivate Error:', err);
    next(err);
  }
}

async function deleteDoctor(req, res, next) {
  try {
    const { doctorID } = req.body;
    await Doctor.deleteDoctor(doctorID);
    res.redirect('/doctors');
  } catch (err) {
    console.error('Doctor Delete Error:', err);
    next(err);
  }
}

module.exports = {
  showDoctors,
  acceptDoctor,
  addDoctor,
  deactivateDoctor,
  deleteDoctor
};
