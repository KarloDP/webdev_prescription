const RX_API = "../../backend/sql_handler/prescription_table.php";

const tbody = document.getElementById("rx-table-body");
const searchInput = document.getElementById("rx-search");
const searchBtn = document.getElementById("rx-search-btn");

let allRx = [];

document.addEventListener("DOMContentLoaded", () => {
  loadRx();
  searchBtn?.addEventListener("click", applySearch);
  searchInput?.addEventListener("keyup", (e) => e.key === "Enter" && applySearch());
});

async function loadRx() {
  setLoading();
  try {
    const res = await fetch(RX_API, { credentials: "include" });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    if (!Array.isArray(data)) throw new Error("Unexpected response");
    allRx = data;
    render(allRx);
  } catch (err) {
    console.error(err);
    tbody.innerHTML = `<tr><td colspan="7">Failed to load prescriptions.</td></tr>`;
  }
}

function render(list) {
  if (!list.length) {
    tbody.innerHTML = `<tr><td colspan="7">No prescriptions found.</td></tr>`;
    return;
  }
  tbody.innerHTML = list
    .map(
      (rx) => `
      <tr>
        <td>RX-${escapeHtml(rx.prescriptionID)}</td>
        <td>${escapeHtml(`${rx.firstName ?? ""} ${rx.lastName ?? ""}`.trim())}</td>
        <td>${escapeHtml(rx.medicationName ?? rx.medicine ?? "-")}</td>
        <td>${escapeHtml(rx.totalQuantity ?? rx.prescribed_amount ?? "-")}</td>
        <td>${escapeHtml(rx.status ?? "-")}</td>
        <td>${escapeHtml(rx.doctorLastName ?? rx.doctor_name ?? "-")}</td>
        <td><a href="edit_prescription.php?id=${encodeURIComponent(rx.prescriptionID)}" class="btn-edit">Edit</a></td>
      </tr>`
    )
    .join("");
}

function applySearch() {
  const term = (searchInput?.value || "").toLowerCase().trim();
  if (!term) return render(allRx);
  const filtered = allRx.filter((rx) =>
    `${rx.firstName ?? ""} ${rx.lastName ?? ""}`.toLowerCase().includes(term) ||
    String(rx.prescriptionID ?? "").includes(term) ||
    String(rx.medicationName ?? "").toLowerCase().includes(term)
  );
  render(filtered);
}

function setLoading() {
  tbody.innerHTML = `<tr><td colspan="7">Loading...</td></tr>`;
}

document.addEventListener('DOMContentLoaded', async () => {
  const target = document.getElementById('prescription-list');
  if (!target) return;
  
  try {
    const res = await fetch('/WebDev_Prescription/backend/sql_handler/get_prescriptions_data.php', { 
      credentials: 'include' 
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const rows = await res.json();
    
    if (!rows.length) {
      target.innerHTML = '<p>No prescriptions found.</p>';
      return;
    }
    
    target.innerHTML = `
      <table class="table-base">
        <thead><tr>
          <th>Prescription ID</th>
          <th>Patient</th>
          <th>Doctor</th>
          <th>Issue Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr></thead>
        <tbody>
          ${rows.map(r => `
            <tr>
              <td>${escapeHtml(r.prescriptionID)}</td>
              <td>${escapeHtml(r.patientName || '-')}</td>
              <td>${escapeHtml(r.doctorName || '-')}</td>
              <td>${escapeHtml(r.issueDate || '-')}</td>
              <td>${escapeHtml(r.status || '-')}</td>
              <td>
                <button onclick="viewDetails(${r.prescriptionID})">View</button>
              </td>
            </tr>`).join('')}
        </tbody>
      </table>`;
  } catch (e) {
    console.error(e);
    target.innerHTML = '<p class="error">Failed to load prescriptions.</p>';
  }
});

function viewDetails(id) {
  window.location.href = `/WebDev_Prescription/frontend/pharmacist/dispense.php?prescriptionID=${id}`;
}

function escapeHtml(str) {
  return String(str ?? "")
    .replace(/&/g, "&amp;").replace(/</g, "&lt;")
    .replace(/>/g, "&gt;").replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}