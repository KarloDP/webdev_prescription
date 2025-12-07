const RX_API = "../../backend/sql_handler/prescription_table.php";
const RX_ITEM_API = "../../backend/sql_handler/prescriptionitem_table.php";

const root = document.querySelector(".edit-prescription-container");
const rxId = root?.dataset.rxId;
const msgEl = document.getElementById("rx-message");
const form = document.getElementById("rx-form");

document.addEventListener("DOMContentLoaded", () => {
  if (!rxId) {
    setMessage("No Prescription ID found.", "error");
    return;
  }
  loadPrescription(rxId);
});

async function loadPrescription(id) {
  setMessage("Loading...", "info");
  try {
    const [rxRes, itemsRes] = await Promise.all([
      fetch(`${RX_API}?prescriptionID=${encodeURIComponent(id)}`, { credentials: "include" }),
      fetch(`${RX_ITEM_API}?prescriptionID=${encodeURIComponent(id)}`, { credentials: "include" }),
    ]);

    if (!rxRes.ok) throw new Error(`Prescription fetch failed: ${rxRes.status}`);
    if (!itemsRes.ok) throw new Error(`Items fetch failed: ${itemsRes.status}`);

    const rx = await rxRes.json();
    const items = await itemsRes.json();

    if (rx.error) throw new Error(rx.error);

    renderFullForm(rx, items);
    clearMessage();
  } catch (err) {
    console.error(err);
    form.innerHTML = ""; // Clear the loading message
    setMessage(`Failed to load prescription. ${err.message}`, "error");
  }
}

function renderFullForm(rx, items) {
  document.getElementById("rx-title").textContent = `Edit Prescription RX-${rx.prescriptionID}`;

  // Build the form HTML dynamically
  form.innerHTML = `
    <section class="prescription-details-card">
        <h2>Prescription Details</h2>
        <div class="form-group-grid">
            <div class="form-group">
                <label>Patient Name:</label>
                <p>${escapeHtml(rx.patientFirstName)} ${escapeHtml(rx.patientLastName)}</p>
            </div>
            <div class="form-group">
                <label>Doctor Name:</label>
                <p>Dr. ${escapeHtml(rx.doctorLastName)}</p>
            </div>
            <div class="form-group">
                <label>Issue Date:</label>
                <p>${escapeHtml(rx.issueDate)}</p>
            </div>
            <div class="form-group">
                <label for="expirationDate">Expiration Date:</label>
                <input type="date" id="expirationDate" name="expirationDate" value="${(rx.expirationDate || '').split('T')[0]}" required>
            </div>
            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="Active" ${rx.status === 'Active' ? 'selected' : ''}>Active</option>
                    <option value="Dispensed" ${rx.status === 'Dispensed' ? 'selected' : ''}>Dispensed</option>
                    <option value="Expired" ${rx.status === 'Expired' ? 'selected' : ''}>Expired</option>
                    <option value="Cancelled" ${rx.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                </select>
            </div>
        </div>
    </section>

    <section class="medication-items-card">
        <h2>Medication Items</h2>
        <div id="rx-items">
            ${items.length > 0 ? items.map(it => `
              <div class="medication-item-form" data-item-id="${it.prescriptionItemID}">
                <h3>${escapeHtml(it.medicationName)} ${escapeHtml(it.medicationStrength)}mg</h3>
                <div class="form-group-inline">
                  <div class="form-group">
                    <label>Quantity:</label>
                    <input type="number" value="${it.prescribed_amount || 0}" disabled>
                  </div>
                  <div class="form-group">
                    <label>Dosage:</label>
                    <input type="text" value="${escapeHtml(it.dosage)}" disabled>
                  </div>
                </div>
                <div class="form-group">
                  <label>Instructions:</label>
                  <textarea disabled>${escapeHtml(it.instructions)}</textarea>
                </div>
              </div>
            `).join('') : '<p>No medication items found.</p>'}
        </div>
    </section>

    <div class="form-actions">
        <button type="submit" class="btn-save-changes">Save Changes</button>
    </div>
  `;
}

form.addEventListener("submit", async (e) => {
  e.preventDefault();
  setMessage("Saving...", "info");
  try {
    const formData = new FormData(form);
    const data = {
        prescriptionID: rxId,
        expirationDate: formData.get('expirationDate'),
        status: formData.get('status')
    };

    const res = await fetch(RX_API, {
      method: "PUT",
      credentials: "include",
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });

    if (!res.ok) {
        const errData = await res.json();
        throw new Error(errData.details || `HTTP Error ${res.status}`);
    }

    const result = await res.json();
    if (result.error) throw new Error(result.error);
    setMessage("Saved successfully.", "success");

  } catch (err) {
    console.error(err);
    setMessage(`Save failed: ${err.message}`, "error");
  }
});

function setMessage(text, type = "info") {
  msgEl.textContent = text;
  msgEl.className = `message ${type}`;
  msgEl.hidden = false;
}
function clearMessage() {
  msgEl.hidden = true;
}
function escapeHtml(str) {
  return String(str ?? "")
    .replace(/&/g, "&amp;").replace(/</g, "&lt;")
    .replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}