// frontend/patient/prescription/prescription.js

// From the point of view of prescription.php:
//   /frontend/patient/prescription/prescription.php
// → backend API:
const RX_API = "../../../backend/sql_handler/prescription_table.php";

document.addEventListener("DOMContentLoaded", () => {
  loadPrescriptions();
});

async function loadPrescriptions() {
  // Container where all prescription tables will be injected
  const container = document.getElementById("prescription-groups");
  if (!container) return;

  container.innerHTML = `<p>Loading prescriptions...</p>`;

  try {
    const patientId = window.currentPatient?.id;
    if (!patientId) {
      throw new Error("Missing patient ID");
    }

    // MUST match PHP handler: patientID + grouped
    const params = new URLSearchParams({
      patientID: patientId,
      grouped: "1",
    });

    const res = await fetch(`${RX_API}?${params.toString()}`, {
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

    if (data.length === 0) {
      container.innerHTML = `<p>No original prescriptions found.</p>`;
      return;
    }

    // Group rows by prescriptionID
    const groups = new Map();
    data.forEach((row) => {
      const id = row.prescriptionID;
      if (!groups.has(id)) {
        groups.set(id, { header: row, items: [] });
      }
      groups.get(id).items.push(row);
    });

    // Render one table per prescription
    const html = Array.from(groups.values())
      .map(renderPrescriptionGroup)
      .join("");

    container.innerHTML = html;
  } catch (err) {
    console.error("Failed to load prescriptions:", err);
    container.innerHTML =
      "<p>Failed to load original prescriptions. Please try again later.</p>";
  }
}

function renderPrescriptionGroup(group) {
  const { header, items } = group;

  const rxId = header.prescriptionID;
  const rxLabel = formatRxId(rxId);
  const issued = formatDate(header.issueDate);
  const expires = formatDate(header.expirationDate);
  const doctor = escapeHtml(header.doctorName ?? "-");
  const status = header.status || "";

  const statusBadge =
    status === "Active"
      ? `<span style="color:#155724;background:#d4edda;padding:1px 4px;border-radius:4px;">Active</span>`
      : `<span style="color:#721c24;background:#f8d7da;padding:1px 4px;border-radius:4px;">${escapeHtml(
          status
        )}</span>`;

  const rowsHtml = items.map(renderMedicationRow).join("");

  return `
  <div class="prescription-group">
    <h3>${rxLabel} (${statusBadge})</h3>
    <p>
      Doctor: ${doctor} | Issued: ${issued} | Expires: ${expires}
    </p>

    <div class="prescription-button-container">
      <a 
        href="prescription_medication.php?prescriptionID=${rxId}" 
        class="prescription-btn-view"
      >
        View Full Medication Details
      </a>
    </div>

    <div class="table-frame">
      <table class="table-base">
        <thead>
          <tr>
            <th>Medicine</th>
            <th>Brand</th>
            <th>Form</th>
            <th>Strength</th>
            <th>Dosage</th>
            <th>Frequency</th>
            <th>Duration</th>
            <th>Amount</th>
            <th>Refills</th>
            <th>Instructions</th>
            <th>Refill Interval</th>
          </tr>
        </thead>
        <tbody>
          ${rowsHtml}
        </tbody>
      </table>
    </div>
  </div>
`;

}

function renderMedicationRow(row) {
  return `
    <tr>
      <td>${escapeHtml(row.medicine ?? "-")}</td>
      <td>${escapeHtml(row.brand ?? "-")}</td>
      <td>${escapeHtml(row.form ?? "-")}</td>
      <td>${escapeHtml(row.strength ?? "-")}</td>
      <td>${escapeHtml(row.dosage ?? "-")}</td>
      <td>${escapeHtml(row.frequency ?? "-")}</td>
      <td>${escapeHtml(row.duration ?? "-")}</td>
      <td>${escapeHtml(row.prescribed_amount ?? "-")}</td>
      <td>${escapeHtml(row.refill_count ?? "-")}</td>
      <td>${escapeHtml(row.instructions ?? "-")}</td>
      <td>${escapeHtml(row.refillInterval ?? "-")}</td>
      <td>
        <a 
          href="prescription_medication.php?prescriptionItemID=${encodeURIComponent(
            row.prescriptionItemID
          )}"
          class="btn-view"
          style="padding:4px 10px;background:#1e3d2f;color:#fff;border-radius:4px;text-decoration:none;"
        >
          View History
        </a>
      </td>
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
