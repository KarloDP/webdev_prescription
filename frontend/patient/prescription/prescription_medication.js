// frontend/patient/prescription/prescription_medication.js

// From the point of view of prescription_medication.php:
//   /frontend/patient/prescription/prescription_medication.php
// → backend API endpoint (adjust name if your file differs):
const DISPENSE_API = "../../../backend/sql_handler/dispenserecord_table.php";


document.addEventListener("DOMContentLoaded", () => {
  loadDispenseHistory();
});

async function loadDispenseHistory() {
  const container = document.getElementById("dispense-history-container");
  if (!container) return;

  container.innerHTML = "<p>Loading dispense history...</p>";

  const itemId = Number(window.currentPrescriptionItemID || 0);
  if (!itemId) {
    container.innerHTML =
      "<p>No prescription item selected. Missing prescriptionItemID in URL.</p>";
    return;
  }

  try {
    // For patients, the PHP handler ignores prescriptionItemID and returns
    // all records for the patient, so we filter client-side.
    const params = new URLSearchParams({
      prescriptionItemID: String(itemId),
    });

    const res = await fetch(`${DISPENSE_API}?${params.toString()}`, {
      method: "GET",
      credentials: "include",
    });

    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`);
    }

    const data = await res.json();
    if (!Array.isArray(data)) {
      throw new Error("API did not return an array");
    }

    // Filter to the specific prescriptionItemID
    const records = data.filter(
      (r) => Number(r.prescriptionItemID) === itemId
    );

    if (records.length === 0) {
      container.innerHTML =
        '<div class="empty-state"><p>No dispense history for this medication.</p></div>';
      return;
    }

    container.innerHTML = renderTable(records);
  } catch (err) {
    console.error("Failed to load dispense history:", err);
    container.innerHTML =
      "<p>Failed to load dispense history. Please try again later.</p>";
  }
}

function renderTable(records) {
  const rowsHtml = records.map(renderRow).join("");

  return `
    <div class="table-frame">
      <table class="table-base">
        <thead>
          <tr>
            <th>Dispense ID</th>
            <th>Pharmacy ID</th>
            <th>Quantity Dispensed</th>
            <th>Date Dispensed</th>
            <th>Pharmacist</th>
            <th>Status</th>
            <th>Next Available Date</th>
          </tr>
        </thead>
        <tbody>
          ${rowsHtml}
        </tbody>
      </table>
    </div>
  `;
}

function renderRow(row) {
  const dateDispensed = formatDate(row.dateDispensed);
  const nextDate = formatDate(row.nextAvailableDates);

  return `
    <tr>
      <td>${escapeHtml(row.dispenseID)}</td>
      <td>${escapeHtml(row.pharmacyID)}</td>
      <td>${escapeHtml(row.quantityDispensed)}</td>
      <td>${escapeHtml(dateDispensed)}</td>
      <td>${escapeHtml(row.pharmacistName ?? "-")}</td>
      <td>${escapeHtml(row.status ?? "-")}</td>
      <td>${escapeHtml(nextDate)}</td>
    </tr>
  `;
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