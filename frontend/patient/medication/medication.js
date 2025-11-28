const API_URL = "../../../backend/sql_handler/medication_table.php";
const PER_PAGE = 8;
let allMedications = [];
let currentPage = 1;

document.addEventListener("DOMContentLoaded", () => {
  loadMedications();
});

async function loadMedications(page = 1) {
  currentPage = page;
  const root = document.getElementById("medications-root");

  try {
    const res = await fetch(`${API_URL}?patientID=${window.currentPatient?.id}`, {
      method: "GET",
      credentials: "include",
    });

    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    if (!Array.isArray(data)) throw new Error("API did not return an array");

    allMedications = data;
    renderMedications();
  } catch (err) {
    console.error("Failed to load medications:", err);
    root.innerHTML = '<p class="error">Failed to load medications. Please try again later.</p>';
  }
}

function renderMedicationRow(item) {
  return `
    <tr>
      <td>${item.prescriptionID}</td>
      <td>${item.medicine}</td>
      <td>${item.brandName}</td>
      <td>${item.dosage}</td>
      <td>${item.frequency}</td>
      <td>${item.duration}</td>
    </tr>
  `;
}

function renderMedications() {
  const root = document.getElementById("medications-root");
  if (!root) return;

  const totalItems = allMedications.length;
  const totalPages = Math.max(1, Math.ceil(totalItems / PER_PAGE));
  const offset = (currentPage - 1) * PER_PAGE;
  const pageItems = allMedications.slice(offset, offset + PER_PAGE);

  const tableHtml = `
    <table class="table-base">
      <thead>
        <tr>
          <th>ID</th>
          <th>Medicine</th>
          <th>Brand</th>
          <th>Dosage</th>
          <th>Frequency</th>
          <th>Duration</th>
        </tr>
      </thead>
      <tbody>
        ${pageItems.length ? pageItems.map(renderMedicationRow).join("") : '<tr><td colspan="6">No prescriptions found.</td></tr>'}
      </tbody>
    </table>
  `;

  const paginationHtml =
    totalPages > 1
      ? `<nav class="pagination">
          ${
            currentPage > 1
              ? `<button class="page-link" data-page="${currentPage - 1}">&laquo; Prev</button>`
              : `<span class="page-link disabled">&laquo; Prev</span>`
          }
          <span>Page ${currentPage} of ${totalPages}</span>
          ${
            currentPage < totalPages
              ? `<button class="page-link" data-page="${currentPage + 1}">Next &raquo;</button>`
              : `<span class="page-link disabled">Next &raquo;</span>`
          }
        </nav>`
      : "";

  root.innerHTML = tableHtml + paginationHtml;

  root.querySelectorAll(".page-link[data-page]").forEach(btn => {
    btn.addEventListener("click", () => {
      const page = parseInt(btn.dataset.page);
      loadMedications(page);
    });
  });
}
