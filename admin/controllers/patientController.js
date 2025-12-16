const Patient = require('../models/patientModel');

async function deletePatient(req, res, next) {
  try {
    const { patientID } = req.body;
    await Patient.deletePatient(patientID);
    res.redirect('/patients');
  } catch (err) {
    next(err);
  }
}

async function addPatient(req, res, next) {
  try {
    const { firstName, lastName, password, dateOfBirth, email, phoneNumber, address, doctorID } = req.body;
    await Patient.addPatient({ firstName, lastName, password, dateOfBirth, email, phoneNumber, address, doctorID });
    res.redirect('/patients');
  } catch (err) {
    next(err);
  }
}

async function showPatients(req, res, next) {
  try {
    const [ 
      patients 
    ] = await Promise.all([
      Patient.getAll()
    ]);

    const table = [
      {name : 'Patients',              count: patients.length,            link: '/patients'} 
    ];

    res.render('pages/patients', { patients, table });
  } catch (err) {
    next(err);
  }
}

module.exports = {
  showPatients,
  addPatient,
  deletePatient
};
   