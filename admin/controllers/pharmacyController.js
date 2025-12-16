const Pharmacy = require('../models/pharmacyModel');

async function showPharmacies(req, res, next) {
  try {
    const pharmacies = await Pharmacy.getAll();
    res.render('pages/pharmacies', { pharmacies });
  } catch (err) {
    console.error('Pharmacy Error:', err);
    next(err);
  }
}

async function addPharmacy(req, res, next) {
  try {
    const { name, password, address, contactNumber, email, clinicAddress } = req.body;
    await Pharmacy.addPharmacy({ name, password, address, contactNumber, email, clinicAddress });
    res.redirect('/pharmacies');
  } catch (err) {
    console.error('Pharmacy Add Error:', err);
    next(err);
  }
}

async function approvePharmacy(req, res, next) {
  try {
    const { pharmacyID } = req.body;
    await Pharmacy.approve(pharmacyID);
    res.redirect('/pharmacies');
  } catch (err) {
    console.error('Pharmacy Approve Error:', err);
    next(err);
  }
}

async function denyPharmacy(req, res, next) {
  try {
    const { pharmacyID } = req.body;
    await Pharmacy.deny(pharmacyID);
    res.redirect('/pharmacies');
  } catch (err) {
    console.error('Pharmacy Deny Error:', err);
    next(err);
  }
}

async function deactivatePharmacy(req, res, next) {
  try {
    const { pharmacyID } = req.body;
    await Pharmacy.deactivate(pharmacyID);
    res.redirect('/pharmacies');
  } catch (err) {
    console.error('Pharmacy Deactivate Error:', err);
    next(err);
  }
}

async function activatePharmacy(req, res, next) {
  try {
    const { pharmacyID } = req.body;
    await Pharmacy.activate(pharmacyID);
    res.redirect('/pharmacies');
  } catch (err) {
    console.error('Pharmacy Activate Error:', err);
    next(err);
  }
}

async function deletePharmacy(req, res, next) {
  try {
    const { pharmacyID } = req.body;
    await Pharmacy.delete(pharmacyID);
    res.redirect('/pharmacies');
  } catch (err) {
    console.error('Pharmacy Delete Error:', err);
    next(err);
  }
}

module.exports = {
  showPharmacies,
  addPharmacy,
  approvePharmacy,
  denyPharmacy,
  deactivatePharmacy,
  activatePharmacy,
  deletePharmacy
};
