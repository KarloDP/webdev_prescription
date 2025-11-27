// frontend/patient/original_prescription.js

// From the point of view of original_prescription.php:
//   /frontend/patient/original_prescription.php
// → backend API endpoints:
const RX_API_ITEMS = "../../../backend/sql_handler/prescriptionitem_table.php";
// (optionally, if you ever need pure prescription data)
// const RX_API = "../../backend/sql_handler/prescription_table.php";

document.addEventListener("DOMContentLoaded", () => {
  loadOriginalPrescriptions();
});

async function loadOriginalPrescriptions() {
  const tbody = document.getElementById("original-prescriptions-body");
  if (!tbody) return;

  tbody.innerHTML = `
    <tr>
      <td colspan="13">Loading prescriptions...</td>
    </tr>
  `;

  try {
    const patientId = window.currentPatient?.id;
    if (!patientId) {
      throw new Error("Missing patient ID");
    }

    // Adjust query params to match how your prescriptionitem_table.php
    // exposes "original prescriptions for this patient"
    const params = new URLSearchParams({
      patientID: patientId,
      mode: "1" // <-- you can use this on the PHP side if you like
    });

    const res = await fetch(`${RX_API_ITEMS}?${params.toString()}`, {
      method: "GET",
      credentials: "include", // send session cookies
    });

    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`);
    }

    const data = await res.json();
    if (!Array.isArray(data)) {
      throw new Error("API did not return an array");
    }

    if (data.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="13">No original prescriptions found.</td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = data.map(renderRow).join("");
  } catch (err) {
    console.error("Failed to load original prescriptions:", err);
    tbody.innerHTML = `
      <tr>
        <td colspan="13">Failed to load original prescriptions. Please try again later.</td>
      </tr>
    `;
  }
}

function renderRow(row) {
  // Expecting fields similar to your old PHP query:
  //  prescriptionID, issueDate, expirationDate, refillInterval,
  //  genericName, brandName, form, strength,
  //  firstName, lastName, dosage, frequency, duration, instructions

  const rxID = formatRxId(row.prescriptionID);
  const doctorFull = row.doctorName ? escapeHtml(row.doctorName) : "-";
  const issued = formatDate(row.issueDate);

  return `
    <tr>
      <td>${rxID}</td>
      <td>${escapeHtml(row.medicine ?? "-")}</td>
      <td>${escapeHtml(row.brand ?? "-")}</td>
      <td>${escapeHtml(row.dosage ?? "-")}</td>
      <td>${escapeHtml(row.frequency ?? "-")}</td>
      <td>${escapeHtml(row.duration ?? "-")}</td>
      <td>${escapeHtml(row.prescribed_amount ?? "-")}</td>
      <td>${escapeHtml(row.refill_count ?? "-")}</td>
      <td>${escapeHtml(row.instructions ?? "-")}</td>
      <td>${issued}</td>
    </tr>
  `;
}

function formatRxId(id) {
  const n = Number(id);
  if (!Number.isFinite(n)) return "-";
  return "RX-" + String(n).padStart(2, "0");
}

function formatDate(value) {
  if (!value) return "-";
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return "-";
  return d.toLocaleDateString(undefined, {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
}

function escapeHtml(str) {
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}