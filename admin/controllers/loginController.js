// api/login_handler.js

// TEMP USER DATA (replace with DB later)
const VALID_EMAIL = "admin@example.com";
const VALID_PASSWORD = "12345";

function loginHandler(req, res) {
  const { email, password } = req.body;

  if (email === VALID_EMAIL && password === VALID_PASSWORD) {
    return res.json({ success: true });
  }

  return res.json({ success: false, message: "Invalid credentials" });
}

module.exports = loginHandler;
