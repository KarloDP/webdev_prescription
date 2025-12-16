const Admin = require('../models/adminModel');



async function showAdmins(req, res, next) {
  try {
    const showPending = req.query.pending === '1';
    let admins, isPendingMode;
    if (showPending) {
      admins = await Admin.getPending();
      isPendingMode = true;
    } else {
      admins = await Admin.getAll();
      isPendingMode = false;
    }
    res.render('pages/admins', {
      admins,
      isPendingMode,
      currentAdminID: req.session.adminID
    });
  } catch (err) {
    console.error('Admin Error:', err);
    next(err);
  }
}

async function acceptAdmin(req, res, next) {
  try {
    const { adminID } = req.body;
    await Admin.acceptAdmin(adminID);
    res.redirect('/admins/pending');
  } catch (err) {
    console.error('Admin Accept Error:', err);
    next(err);
  }
}

async function rejectAdmin(req, res, next) {
  try {
    const { adminID } = req.body;
    await Admin.rejectAdmin(adminID);
    res.redirect('/admins?pending=1');
  } catch (err) {
    console.error('Admin Reject Error:', err);
    next(err);
  }
}

async function deleteAdmin(req, res, next) {
  try {
    const { adminID } = req.body;
    if (parseInt(adminID) !== req.session.adminID) {
      return res.status(403).send('You can only delete your own account.');
    }
    await Admin.deleteAdmin(adminID);
    req.session.destroy(() => {
      res.redirect('/login');
    });
  } catch (err) {
    console.error('Admin Delete Error:', err);
    next(err);
  }
}

module.exports = {
  showAdmins,
  acceptAdmin,
  rejectAdmin,
  deleteAdmin
};
