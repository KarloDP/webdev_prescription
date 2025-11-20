//default landing page. redirect to login page if user session is not started.
//code below is chatGPT generated, will likely need to redo. ******

// patient_dashboard.js
// Frontend-only version of the PHP dashboard.
// Assumes:
//   - <div id="patient-dashboard-root"></div> exists in index.html
//   - API_URL returns a JSON array of prescriptions for the logged-in patient
//     with fields: prescriptionID, medicine, doctor_name, notes, prescribed_at
//   - window.patientName may be defined elsewhere; falls back to "Patient"

const API_URL = "../../backend/sql_handler/prescriptions_table.php"; // <-- change to your endpoint

const PER_PAGE = 8;
let allPrescriptions = [];
let currentPage = 1;

document.addEventListener("DOMContentLoaded", () => {
  loadDashboard();
});

async function loadDashboard(page = 1) {
  currentPage = page;

  try {
    const res = await fetch(API_URL, { method: "GET", credentials: "include" });
    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`);
    }

    // Expecting an array of prescriptions
    const data = await res.json();
    if (!Array.isArray(data)) {
      throw new Error("Invalid data format from API (expected array)");
    }

    allPrescriptions = data;
    renderDashboard();
  } catch (err) {
    console.error("Failed to load prescriptions:", err);
    const root = document.getElementById("patient-dashboard-root");
    if (root) {
      root.innerHTML = `<p class="error">Failed to load dashboard. Please try again later.</p>`;
    }
  }
}

function renderDashboard() {
  const root = document.getElementById("patient-dashboard-root");
  if (!root) return;

  const patientName = (window.patientName || "Patient").toUpperCase();
  const totalItems = allPrescriptions.length;
  const totalPages = Math.max(1, Math.ceil(totalItems / PER_PAGE));

  // Clamp current page
  if (currentPage > totalPages) currentPage = totalPages;
  if (currentPage < 1) currentPage = 1;

  const offset = (currentPage - 1) * PER_PAGE;
  const pageItems = allPrescriptions.slice(offset, offset + PER_PAGE);

  // Build cards HTML
  const cardsHtml = pageItems.length
    ? pageItems.map(p => renderPrescriptionCard(p)).join("")
    : `<div class="empty-state"><p>No active prescriptions found.</p></div>`;

  // Pagination HTML
  const paginationHtml = totalPages > 1
    ? `
      <nav class="pagination">
        ${currentPage > 1
          ? `<button class="page-link" data-page="${currentPage - 1}">&laquo; Prev</button>`
          : `<span class="page-link disabled">&laquo; Prev</span>`}

        <span class="page-info">Page ${currentPage} of ${totalPages}</span>

        ${currentPage < totalPages
          ? `<button class="page-link" data-page="${currentPage + 1}">Next &raquo;</button>`
          : `<span class="page-link disabled">Next &raquo;</span>`}
      </nav>
    `
    : "";

  root.innerHTML = `
    <div class="patient-dashboard">
      <div class="welcome-row">
        <div class="welcome-card">
          <h1>Welcome <span class="name">${escapeHtml(patientName)} !!</span></h1>
          <p class="subtitle">View Prescriptions. Manage medications and pharmacies.</p>
          <div class="welcome-actions">
            <a class="btn btn-primary" href="medication.html">View Medications</a>
            <a class="btn btn-outline" href="pharmacies.html">Find Pharmacies</a>
          </div>
        </div>

        <div class="stats-card">
          <div class="stats-row">
            <div class="stat">
              <div class="stat-number" id="activeCount">${totalItems}</div>
              <div class="stat-label">Active Prescriptions</div>
            </div>
            <div class="stat">
              <div class="stat-number" id="refillCount">0</div>
              <div class="stat-label">Upcoming Refills</div>
            </div>
            <div class="stat">
              <div class="stat-number" id="nearbyPharmacies">0</div>
              <div class="stat-label">Nearby Pharmacies</div>
            </div>
          </div>
          <div class="stats-cta">
            <a class="link" href="prescriptions.html">View Details</a>
          </div>
        </div>
      </div>

      <section class="prescriptions-section">
        <h2 class="section-title">Active Prescriptions</h2>

        <div class="cards-grid">
          ${cardsHtml}
        </div>

        ${paginationHtml}
      </section>
    </div>
  `;

  // Attach pagination handlers (if any)
  root.querySelectorAll(".pagination .page-link[data-page]").forEach(btn => {
    btn.addEventListener("click", () => {
      const page = parseInt(btn.getAttribute("data-page"), 10);
      if (!Number.isNaN(page)) {
        currentPage = page;
        renderDashboard();
      }
    });
  });
}

function renderPrescriptionCard(p) {
  const medicine = p.medicine || "-";
  const doctorName = p.doctor_name || "-";
  const notes = p.notes || "-";

  let dateDisplay = "-";
  if (p.prescribed_at) {
    const d = new Date(p.prescribed_at);
    if (!isNaN(d.getTime())) {
      // e.g. "March 5, 2025"
      dateDisplay = d.toLocaleDateString(undefined, {
        year: "numeric",
        month: "long",
        day: "numeric"
      });
    }
  }

  const id = Number(p.prescriptionID) || 0;

  return `
    <article class="prescription-card">
      <div class="card-left">
        <h3 class="medicine">${escapeHtml(medicine)}</h3>
        <p class="small muted">
          Prescribed by <strong>${escapeHtml(doctorName)}</strong>
        </p>
        <p class="small muted">
          First taken at <strong>${escapeHtml(dateDisplay)}</strong>
        </p>
      </div>
      <div class="card-right">
        <p class="note">${escapeHtml(notes)}</p>
        <a class="details-link" href="view_prescription.html?id=${id}">Medicine Details &gt;&gt;</a>
      </div>
    </article>
  `;
}

// Simple HTML escape helper (similar to htmlspecialchars in PHP)
function escapeHtml(str) {
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
