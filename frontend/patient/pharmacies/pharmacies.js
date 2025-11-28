// Backend API endpoint relative to pharmacies.php
const API_URL = "../../../backend/sql_handler/pharmacy_table.php";

const PER_PAGE = 8;
let allPharmacies = [];
let currentPage = 1;

document.addEventListener("DOMContentLoaded", () => {
  loadPharmacies();
});

async function loadPharmacies(page = 1) {
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
    console.log("API pharmacy data sample:", data[0]); // debug

    if (!Array.isArray(data)) {
      throw new Error("API did not return an array");
    }

    allPharmacies = data;
    renderPharmacies();
  } catch (err) {
    console.error("Failed to load pharmacies:", err);
    const root = document.getElementById("pharmacies-root");
    if (root) {
      root.innerHTML =
        '<p class="error">Failed to load pharmacies. Please try again later.</p>';
    }
  }
}

function renderPharmacies() {
  const root = document.getElementById("pharmacies-root");
  if (!root) return;

  const patientName =
    (window.patientName && String(window.patientName).toUpperCase()) || "PATIENT";

  const totalItems = allPharmacies.length;
  const totalPages = Math.max(1, Math.ceil(totalItems / PER_PAGE));

  if (currentPage > totalPages) currentPage = totalPages;
  if (currentPage < 1) currentPage = 1;

  const offset = (currentPage - 1) * PER_PAGE;
  const pageItems = allPharmacies.slice(offset, offset + PER_PAGE);

  const cardsHtml = pageItems.length
    ? pageItems.map(renderPharmacyCard).join("")
    : `<div class="empty-state"><p>No pharmacies found.</p></div>`;

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
    <div class="welcome-row">
      <div class="welcome-card">
        <h1>Welcome <span class="name">${escapeHtml(patientName)} !!</span></h1>
        <p class="subtitle">Browse nearby pharmacies and their contact details.</p>
      </div>

      <div class="stats-card">
        <div class="stats-row">
          <div class="stat">
            <div class="stat-number" id="pharmacyCount">${totalItems}</div>
            <div class="stat-label">Pharmacies Listed</div>
          </div>
        </div>
      </div>
    </div>

    <section class="pharmacies-section">
      <h2 class="section-title">Available Pharmacies</h2>
      <div class="cards-grid">
        ${cardsHtml}
      </div>
      ${paginationHtml}
    </section>
  `;

  // pagination handlers
  root.querySelectorAll(".pagination .page-link[data-page]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const page = parseInt(btn.getAttribute("data-page"), 10);
      if (!Number.isNaN(page)) {
        currentPage = page;
        renderPharmacies();
      }
    });
  });
}

function renderPharmacyCard(p) {
  const name = p.name || "-";
  const contact = p.contactNumber || "-";
  const email = p.email || "-";
  const address = p.address || "-";
  const clinic = p.clinicAddress || "-";
  const status = p.status || "pending";
  const hours = p.operatingHours || "Mon–Sat, 8:00–18:00";

  return `
    <article class="prescription-card">
      <div class="card-left">
        <h3 class="medicine">${escapeHtml(name)}</h3>
        <p class="small muted">Contact: <strong>${escapeHtml(contact)}</strong></p>
        <p class="small muted">Email: <strong>${escapeHtml(email)}</strong></p>
        <p class="small muted">Address: <strong>${escapeHtml(address)}</strong></p>
        <p class="small muted">Clinic: <strong>${escapeHtml(clinic)}</strong></p>
      </div>
      <div class="card-right">
        <p class="note">Hours: ${escapeHtml(hours)}</p>
        <p class="note">Status: <strong>${escapeHtml(status)}</strong></p>
       <a class="details-link" href="pharmacy_details.php?pharmacyID=${escapeHtml(p.pharmacyID || 0)}">

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
