// frontend/patient/dashboard/dashboard.js

// From the point of view of dashboard.php URL:
//   /frontend/patient/dashboard/dashboard.php
// → backend API is at:
//   ../../backend/sql_handler/prescription_table.php
const API_URL = "../../../backend/sql_handler/prescription_table.php";

const PER_PAGE = 8;
let allPrescriptions = [];
let currentPage = 1;

document.addEventListener("DOMContentLoaded", () => {
  loadDashboard();
});

async function loadDashboard(page = 1) {
  currentPage = page;

  try {
    const res = await fetch(API_URL, {
      method: "GET",
      credentials: "include", // send cookies/session
    });

    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`);
    }

    const data = await res.json();
    console.log("API data sample:", data[0]); // temp line for data testing

    if (!Array.isArray(data)) {
      throw new Error("API did not return an array");
    }

    allPrescriptions = data;
    renderDashboard();
  } catch (err) {
    console.error("Failed to load prescriptions:", err);
    const root = document.getElementById("patient-dashboard-root");
    if (root) {
      root.innerHTML =
        '<p class="error">Failed to load dashboard. Please try again later.</p>';
    }
  }
}

function renderDashboard() {
  const root = document.getElementById("patient-dashboard-root");
  if (!root) return;

  const patientName =
    (window.patientName && String(window.patientName).toUpperCase()) ||
    "PATIENT";

  const totalItems = allPrescriptions.length;
  const totalPages = Math.max(1, Math.ceil(totalItems / PER_PAGE));

  if (currentPage > totalPages) currentPage = totalPages;
  if (currentPage < 1) currentPage = 1;

  const offset = (currentPage - 1) * PER_PAGE;
  const pageItems = allPrescriptions.slice(offset, offset + PER_PAGE);

  const cardsHtml = pageItems.length
    ? pageItems.map(renderPrescriptionCard).join("")
    : `<div class="empty-state"><p>No active prescriptions found.</p></div>`;

  const paginationHtml =
    totalPages > 1
      ? `
      <nav class="pagination">
        ${
          currentPage > 1
            ? `<button class="page-link" data-page="${currentPage - 1}">&laquo; Prev</button>`
            : `<span class="page-link disabled">&laquo; Prev</span>`
        }

        <span class="page-info">Page ${currentPage} of ${totalPages}</span>

        ${
          currentPage < totalPages
            ? `<button class="page-link" data-page="${currentPage + 1}">Next &raquo;</button>`
            : `<span class="page-link disabled">Next &raquo;</span>`
        }
      </nav>
    `
      : "";

  root.innerHTML = `
    <div class="patient-dashboard">
      <div class="welcome-row">
        <div class="welcome-card">
          <h1>Welcome <span class="name">${escapeHtml(
            patientName
          )} !!</span></h1>
          <p class="subtitle">View Prescriptions. Manage medications and pharmacies.</p>
          <div class="welcome-actions">
            <a class="btn btn-primary" href="../medication.php">View Medications</a>
            <a class="btn btn-outline" href="../pharmacies.php">Find Pharmacies</a>
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
            <a class="link" href="../prescriptions.php">View Details</a>
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

  // pagination handlers
  root.querySelectorAll(".pagination .page-link[data-page]").forEach((btn) => {
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
  const medicine = p.medicine || p.medicineName || "-";
  const doctorName = p.doctor_name || p.doctorName || "-";
  const notes = p.notes || p.dosage || "-";

  let dateDisplay = "-";
  if (p.prescribed_at || p.issueDate) {
    const d = new Date(p.prescribed_at || p.issueDate);
    if (!isNaN(d.getTime())) {
      dateDisplay = d.toLocaleDateString(undefined, {
        year: "numeric",
        month: "long",
        day: "numeric",
      });
    }
  }

  const id = Number(p.prescriptionID || p.id || 0);

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
        <a class="details-link" href="../view_prescription.php?id=${id}">
          Medicine Details &gt;&gt;
        </a>
      </div>
    </article>
  `;
}

function escapeHtml(str) {
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}