// View -> JS -> Model (sql_handler). Assumes session cookie auth is in place.
const RX_LIST_API = "../../backend/sql_handler/prescription_table.php";
const RX_DETAILS_API = "../../backend/sql_handler/prescription_table.php";
const RX_ITEM_API = "../../backend/sql_handler/prescriptionitem_table.php";

// Prevent double-initialization if the script is loaded twice
if (window.__dispenseInit) {
  console.warn('dispense.js already initialized; skipping duplicate load');
} else {
  window.__dispenseInit = true;

  document.addEventListener("DOMContentLoaded", () => {
    const rxListEl = document.getElementById("prescription-list-scroll");
    const rxDetailsEl = document.getElementById("prescription-details-content");
    const searchInput = document.getElementById("list-search-input");

    let allRx = [];
    let selectedId = null;

    // Read the pre-selected ID from the URL query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const preselectedId = urlParams.get('prescription_id');

    loadList(preselectedId); // Pass the ID to the load function
    searchInput?.addEventListener("input", applySearch);

    async function loadList(preselectedId = null) {
      setListLoading();
      try {
        const res = await fetch(`${RX_LIST_API}?status=Active`, { credentials: "include" });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!Array.isArray(data)) throw new Error("Unexpected response");
        allRx = data;
        renderList(allRx);

        if (preselectedId) {
          selectRx(preselectedId);
        } else if (allRx.length > 0) {
          const firstId = allRx[0].prescriptionID;
          window.history.replaceState({ id: firstId }, '', `?prescription_id=${firstId}`);
          selectRx(firstId);
        } else {
          rxDetailsEl.innerHTML = `<p>No active prescriptions found.</p>`;
        }
      } catch (err) {
        if (rxListEl) rxListEl.innerHTML = `<p class="error">Failed to load prescriptions.</p>`;
        if (rxDetailsEl) rxDetailsEl.innerHTML = `<p class="error">Select a prescription.</p>`;
        console.error(err);
      }
    }

    function renderList(list) {
      if (!rxListEl) return;
      if (!list.length) {
        rxListEl.innerHTML = "<p>No prescriptions found.</p>";
        return;
      }
      rxListEl.innerHTML = list.map(rx => `
        <div class="prescription-item ${selectedId == rx.prescriptionID ? "active" : ""}" data-id="${rx.prescriptionID}">
          <a href="?prescription_id=${rx.prescriptionID}" data-id="${rx.prescriptionID}">
            <div class="rx-line">
              <span class="rx-id">RX-${rx.prescriptionID}</span>
              <span class="rx-name">${escapeHtml(rx.firstName ?? "")} ${escapeHtml(rx.lastName ?? "")}</span>
            </div>
          </a>
        </div>`
      ).join("");

      rxListEl.querySelectorAll("a[data-id]").forEach(a => a.addEventListener("click", (e) => {
        e.preventDefault();
        const id = e.currentTarget.getAttribute("data-id");
        window.history.pushState({ id: id }, '', `?prescription_id=${id}`);
        selectRx(id);
      }));
    }

    async function selectRx(id) {
      selectedId = id;
      highlightSelected();
      setDetailsLoading();
      try {
        const [rxRes, itemsRes] = await Promise.all([
          fetch(`${RX_DETAILS_API}?prescriptionID=${id}`, { credentials: "include" }),
          fetch(`${RX_ITEM_API}?prescriptionID=${id}`, { credentials: "include" })
        ]);

        if (!rxRes.ok || !itemsRes.ok) throw new Error(`Failed to fetch data.`);

        const rx = await rxRes.json();
        const items = await itemsRes.json();

        renderDetails(rx, items);
      } catch (err) {
        if (rxDetailsEl) rxDetailsEl.innerHTML = `<p class="error">Failed to load prescription details.</p>`;
        console.error(err);
      }
    }

    function renderDetails(rx, items) {
      if (!rxDetailsEl) return;

      console.log('RX details payload:', rx);

      const hasRemaining = Array.isArray(items) && items.some(item => (parseInt(item.prescribed_amount, 10) || 0) > 0);

      const first =
        rx.patientFirstName ?? rx.firstName ?? rx.patient_first_name ?? rx.fname ?? '';
      const last =
        rx.patientLastName ?? rx.lastName ?? rx.patient_last_name ?? rx.lname ?? '';
      const patientName = `${escapeHtml(first)} ${escapeHtml(last)}`.trim();

      rxDetailsEl.innerHTML = `
        <header class="rx-details-header">
          <h1>Prescription ${escapeHtml(rx.prescriptionID ?? '')}</h1>
          ${patientName ? `<div class="patient-name">Patient: ${patientName}</div>` : `<div class="patient-name muted">Patient: Not available</div>`}
        </header>
        <div class="rx-details-grid">
          <div><span class="label">Status:</span> ${escapeHtml(rx.status ?? '')}</div>
          <div><span class="label">Issue Date:</span> ${escapeHtml(rx.issueDate ?? '')}</div>
          <div><span class="label">Expiration:</span> ${escapeHtml(rx.expirationDate ?? '')}</div>
        </div>
        <section class="medication-rx-section">
          <h2>Rx Items</h2>
          <div id="rx-items-container">
            ${renderRxItems(items || [])}
          </div>
          ${hasRemaining ? `
          <div class="dispense-section">
            <button id="dispense-all-btn" class="btn-dispense-action">Dispense Medicine</button>
          </div>` : ''}
        </section>
        <div id="dispense-message" class="message" hidden></div>
      `;

      document.getElementById('dispense-all-btn')?.addEventListener('click', handleDispenseAll);
    }

    function renderRxItems(items) {
      if (!items || items.length === 0) return '<p>No medication items found.</p>';
      return items.map(item => {
        const remaining = parseInt(item.prescribed_amount, 10) || 0;
        const isDispensed = remaining <= 0;
        const medicationFullName = `${escapeHtml(item.medicationName)} ${escapeHtml(item.medicationStrength)}`;
        return `
        <div class="rx-item-card ${isDispensed ? 'item-dispensed' : ''}" data-item-id="${item.prescriptionItemID}">
          <h4>${medicationFullName}</h4>
          <div class="item-details-grid">
            <div><span class="label">Remaining:</span> ${remaining}</div>
            <div><span class="label">Dosage:</span> ${escapeHtml(item.dosage)}</div>
          </div>
          <div class="item-instructions"><p>${escapeHtml(item.instructions)}</p></div>
          ${!isDispensed ? `
          <div class="quantity-input-wrapper">
            <input type="number" class="item-quantity-input" data-item-id="${item.prescriptionItemID}" placeholder="Qty" min="1" max="${remaining}" value="">
          </div>` : `<div class="dispensed-badge">Fully Dispensed</div>`}
        </div>`;
      }).join('');
    }

    async function handleDispenseAll(e) {
      e.preventDefault();

      const itemInputs = document.querySelectorAll('.item-quantity-input');
      const dispensesToProcess = [];
      
      // 1. Gather all valid inputs
      itemInputs.forEach(input => {
        const qty = parseInt(input.value, 10);
        const max = parseInt(input.getAttribute('max'), 10) || Infinity;
        const itemId = input.getAttribute('data-item-id');

        if (Number.isFinite(qty) && qty > 0 && qty <= max && itemId) {
            dispensesToProcess.push({
                prescriptionItemID: itemId,
                dispensedQuantity: qty
            });
        }
      });

      if (dispensesToProcess.length === 0) {
        showMessage('Enter a valid quantity for at least one item.', 'error');
        return;
      }

      showMessage('Dispensing...', 'info');
      const btn = document.getElementById('dispense-all-btn');
      if(btn) btn.disabled = true;

      let successCount = 0;
      let errors = [];

      try {
        // 2. Process sequentially
        for (const payload of dispensesToProcess) {
            try {
                const res = await fetch('../../backend/sql_handler/dispenserecord_table.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                // Get text first to debug "Unexpected token" issues
                const text = await res.text();
                let data;
                
                try {
                    data = JSON.parse(text);
                } catch (parseErr) {
                    console.error("Server returned invalid JSON:", text);
                    throw new Error("Server Error: Invalid response format.");
                }

                if (!res.ok || data.error) {
                    throw new Error(data.details || data.message || 'Unknown error');
                }
                
                successCount++;
            } catch (innerErr) {
                console.error("Failed to dispense item:", payload, innerErr);
                errors.push(`Item #${payload.prescriptionItemID}: ${innerErr.message}`);
            }
        }

        // 3. Final Status
        if (errors.length > 0) {
            showMessage(`Dispensed ${successCount} items. Errors: ${errors.join('; ')}`, 'error');
        } else {
            showMessage(`Successfully dispensed ${successCount} item(s).`, 'success');
        }

        // Refresh details after a short delay
        setTimeout(() => {
            if (selectedId) selectRx(selectedId);
        }, 1000);

      } catch (err) {
        console.error('Dispense error:', err);
        showMessage(`Error: ${err.message}`, 'error');
      }
    }

    function applySearch() {
      const term = (searchInput?.value || "").toLowerCase().trim();
      const filtered = term ? allRx.filter(rx =>
        (`${rx.firstName ?? ""} ${rx.lastName ?? ""}`.toLowerCase().includes(term)) ||
        String(rx.prescriptionID).includes(term)
      ) : allRx;
      renderList(filtered);
    }

    function highlightSelected() {
      if (!rxListEl) return;
      rxListEl.querySelectorAll(".prescription-item").forEach(el => {
        el.classList.toggle("active", el.dataset.id == selectedId);
      });
    }

    function showMessage(text, type) {
      const messageEl = document.getElementById('dispense-message');
      if (!messageEl) return;
      messageEl.textContent = text;
      messageEl.className = `message ${type}`;
      messageEl.hidden = false;
    }

    function setListLoading() { if (rxListEl) rxListEl.innerHTML = "<p>Loading...</p>"; }
    function setDetailsLoading() { if (rxDetailsEl) rxDetailsEl.innerHTML = "<p>Loading details...</p>"; }

    function escapeHtml(str) {
      return String(str ?? '').replace(/[&<>"']/g, m => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' })[m]);
    }
  });
}