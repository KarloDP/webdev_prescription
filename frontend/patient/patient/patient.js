const API_URL = "../../../backend/sql_handler/patient_history.php";

document.addEventListener("DOMContentLoaded", () => {
  loadHistory();
});

async function loadHistory() {
  const root = document.getElementById("history-root");
  try {
    const res = await fetch(API_URL, {
      method: "GET",
      credentials: "include"
    });

    const text = await res.text();
    try {
      const data = JSON.parse(text);
      if (!Array.isArray(data)) throw new Error("Invalid data format");
      renderHistory(data);
    } catch (e) {
      console.error("Response was not JSON:", text);
      root.innerHTML = '<p class="error">Backend error: ' + text + '</p>';
    }
  } catch (err) {
    console.error("Failed to load history:", err);
    root.innerHTML = '<p class="error">Unable to load prescription history.</p>';
  }
}

function renderHistory(items) {
  const root = document.getElementById("history-root");
  if (!items.length) {
    root.innerHTML = '<p>No prescription history found.</p>';
    return;
  }

  const rows = items.map(item => `
    <tr>
      <td>${item.medicine}</td>
      <td>${item.prescriptionID}</td>
      <td>${item.doctorName}</td>
      <td>${item.status}</td>
      <td>${item.prescribed_amount} pcs</td>   <!-- 👈 now shows pcs -->
      <td>${formatDate(item.issueDate)}</td>
    </tr>
  `).join("");

  root.innerHTML = `
    <div class="table-frame">
      <table class="table-base">
        <thead>
          <tr>
            <th>Medicine</th>
            <th>Prescription ID</th>
            <th>Doctor Name</th>
            <th>Status</th>
            <th>QTY</th>
            <th>Date Issued</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </div>
  `;
}

function formatDate(dateStr) {
  if (!dateStr) return "N/A";
  const d = new Date(dateStr);
  return d.toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" });
}
