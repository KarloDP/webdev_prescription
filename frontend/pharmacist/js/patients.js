const PATIENTS_API = "../../backend/sql_handler/get_patients_list.php";

const tbody = document.getElementById("patients-table-body");
const searchInput = document.getElementById("patient-search");
const searchBtn = document.getElementById("patient-search-btn");

let allPatients = [];

document.addEventListener("DOMContentLoaded", () => {
  loadPatients();
  searchBtn?.addEventListener("click", applySearch);
  searchInput?.addEventListener("keyup", (e) => e.key === "Enter" && applySearch());
});

async function loadPatients() {
  setLoading();
  try {
    const res = await fetch(PATIENTS_API, { credentials: "include" });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    if (!Array.isArray(data)) throw new Error("Unexpected response");
    allPatients = data;
    render(allPatients);
  } catch (err) {
    console.error(err);
    tbody.innerHTML = `<tr><td colspan="5">Failed to load patients.</td></tr>`;
  }
}

function render(list) {
  if (!list.length) {
    tbody.innerHTML = `<tr><td colspan="5">No patients found.</td></tr>`;
    return;
  }
  tbody.innerHTML = list
    .map(
      (p) => `
      <tr>
        <td>${escapeHtml(`${p.firstName ?? ""} ${p.lastName ?? ""}`.trim())}</td>
        <td>${escapeHtml(p.contactNumber ?? "-")}</td>
        <td>${p.prescriptionID ? `RX-${escapeHtml(p.prescriptionID)}` : "-"}</td>
        <td>${escapeHtml(p.address ?? "-")}</td>
        <td>${p.doctorLastName ? `Dr. ${escapeHtml(p.doctorLastName)}` : "-"}</td>
      </tr>`
    )
    .join("");
}

function applySearch() {
  const term = (searchInput?.value || "").toLowerCase().trim();
  if (!term) return render(allPatients);
  const filtered = allPatients.filter((p) =>
    `${p.firstName ?? ""} ${p.lastName ?? ""}`.toLowerCase().includes(term) ||
    String(p.contactNumber ?? "").toLowerCase().includes(term) ||
    String(p.address ?? "").toLowerCase().includes(term) ||
    String(p.prescriptionID ?? "").includes(term)
  );
  render(filtered);
}

function setLoading() {
  tbody.innerHTML = `<tr><td colspan="5">Loading...</td></tr>`;
}

function escapeHtml(str) {
  return String(str ?? "")
    .replace(/&/g, "&amp;").replace(/</g, "&lt;")
    .replace(/>/g, "&gt;").replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}