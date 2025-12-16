const express = require('express');
const session = require('express-session');
const path = require('path');

const authRoutes = require('./routes/authRoutes');
const doctorRoutes = require('./routes/doctorRoutes');
const adminRoutes = require('./routes/adminRoute');
const patientRoutes = require('./routes/patientRoutes');
const pharmacyRoutes = require('./routes/pharmacyRoutes');
const medicationRoutes = require('./routes/medicationRoutes');
const prescriptionRoutes = require('./routes/prescriptionRoutes');
const dispenseRecordRoutes = require('./routes/dispenseRecordRoutes');
const prescriptionItemRoutes = require('./routes/prescriptionItemRoutes');

const app = express();

app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));

app.use(session({
  secret: 'supersecretkey',
  resave: false,
  saveUninitialized: false
}));

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

app.use('/', authRoutes);
app.use('/', doctorRoutes);
app.use('/', adminRoutes);
app.use('/', patientRoutes);
app.use('/', pharmacyRoutes);
app.use('/', medicationRoutes);
app.use('/', prescriptionRoutes);
app.use('/', dispenseRecordRoutes);
app.use('/', prescriptionItemRoutes);


app.get('/', (req, res) => res.redirect('/login'));

const PORT = process.env.PORT || 8080;


app.use((err, req, res, next) => {
  console.error('\n🔥 SERVER ERROR:', err.stack);
  res.status(500).send(`
    <h2>Server Error</h2>
    <pre>${err.stack}</pre>
  `);
});


app.use((req, res) => {
  res.status(404).send(`
    <h2>404 - Page Not Found</h2>
    <p>URL: ${req.originalUrl}</p>
  `);
});

app.listen(PORT, () => {
  console.log(`Server running at http://localhost:${PORT}`);
});
