const RX_LIST_API = "/backend/sql_handler/prescription_table.php";
const RX_DETAILS_API = "/backend/sql_handler/prescription_table.php";
const RX_ITEM_API = "/backend/sql_handler/prescriptionitem_table.php";
const DISPENSE_API = "/backend/sql_handler/dispenserecord_table.php";

if (window.__dispenseInit) {
  console.warn("dispense.js already initialized");
} else {
  window.__dispenseInit = true;

  document.addEventListener("DOMContentLoaded", () => {
    const rxListEl = document.getElementById("prescription-list-scroll");
    const rxDetailsEl = document.getElementById("prescription-details-content");
    const searchInput = document.getElementById("list-search-input");

    let allRx = [];
    let selectedId = null;

    const urlParams = new URLSearchParams(window.location.search);
    const preselectedId = urlParams.get("prescription_id");

    loadList(preselectedId);
    searchInput?.addEventListener("input", applySearch);

    async function loadList(preselectedId = null) {
      setListLoading();
      try {
        const res = await fetch(`${RX_LIST_API}?status=Active`, { credentials: "include" });
        const data = await res.json();
        allRx = Array.isArray(data) ? data : [];
        renderList(allRx);

        if (preselectedId) {
          selectRx(preselectedId);
        } else if (allRx.length > 0) {
          const firstId = allRx[0].prescriptionID;
          history.replaceState({ id: firstId }, "", `?prescription_id=${firstId}`);
          selectRx(firstId);
        } else {
          rxDetailsEl.innerHTML = "<p>No active prescriptions found.</p>";
        }
      } catch {
        rxListEl.innerHTML = "<p class='error'>Failed to load prescriptions.</p>";
        rxDetailsEl.innerHTML = "<p class='error'>Select a prescription.</p>";
      }
    }

    function renderList(list) {
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
        </div>
      `).join("");

      rxListEl.querySelectorAll("a[data-id]").forEach(a =>
        a.addEventListener("click", e => {
          e.preventDefault();
          const id = a.dataset.id;
          history.pushState({ id }, "", `?prescription_id=${id}`);
          selectRx(id);
        })
      );
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

        const rx = await rxRes.json();
        const items = await itemsRes.json();

        renderDetails(rx, items);
      } catch {
        rxDetailsEl.innerHTML = "<p class='error'>Failed to load prescription details.</p>";
      }
    }

    function renderDetails(rx, items) {
      const hasRemaining = Array.isArray(items) && items.some(i => (parseInt(i.prescribed_amount, 10) || 0) > 0);

      const first = rx.patientFirstName ?? rx.firstName ?? "";
      const last = rx.patientLastName ?? rx.lastName ?? "";
      const patientName = `${escapeHtml(first)} ${escapeHtml(last)}`.trim();

      rxDetailsEl.innerHTML = `
        <header class="rx-details-header">
          <h1>Prescription ${escapeHtml(rx.prescriptionID)}</h1>
          <div class="patient-name">Patient: ${patientName || "Not available"}</div>
        </header>
        <div class="rx-details-grid">
          <div><span class="label">Status:</span> ${escapeHtml(rx.status)}</div>
          <div><span class="label">Issue Date:</span> ${escapeHtml(rx.issueDate)}</div>
          <div><span class="label">Expiration:</span> ${escapeHtml(rx.expirationDate)}</div>
        </div>
        <section class="medication-rx-section">
          <h2>Rx Items</h2>
          <div id="rx-items-container">${renderRxItems(items)}</div>
          ${hasRemaining ? `<button id="dispense-all-btn" class="btn-dispense-action">Dispense Medicine</button>` : ""}
        </section>
        <div id="dispense-message" class="message" hidden></div>
      `;

      document.getElementById("dispense-all-btn")?.addEventListener("click", handleDispenseAll);
    }

    function renderRxItems(items = []) {
      if (!items.length) return "<p>No medication items found.</p>";

      return items.map(item => {
        const remaining = parseInt(item.prescribed_amount, 10) || 0;
        const disabled = remaining <= 0;
        return `
          <div class="rx-item-card ${disabled ? "item-dispensed" : ""}">
            <h4>${escapeHtml(item.medicationName)} ${escapeHtml(item.medicationStrength)}</h4>
            <div><strong>Remaining:</strong> ${remaining}</div>
            <div><strong>Dosage:</strong> ${escapeHtml(item.dosage)}</div>
            <p>${escapeHtml(item.instructions)}</p>
            ${!disabled ? `<input type="number" class="item-quantity-input" data-item-id="${item.prescriptionItemID}" min="1" max="${remaining}">` : ""}
          </div>
        `;
      }).join("");
    }

    async function handleDispenseAll(e) {
      e.preventDefault();

<<<<<<< HEAD
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
=======
      const inputs = document.querySelectorAll(".item-quantity-input");
      const payloads = [];

      inputs.forEach(input => {
        const qty = parseInt(input.value, 10);
        const max = parseInt(input.max, 10);
        if (qty > 0 && qty <= max) {
          payloads.push({
            prescriptionItemID: parseInt(input.dataset.itemId, 10),
            dispensedQuantity: qty
          });
>>>>>>> mainAdminMerge
        }
      });

      if (!payloads.length) {
        showMessage("Enter valid quantities.", "error");
        return;
      }

<<<<<<< HEAD
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

=======
      showMessage("Dispensing...", "info");

      try {
        for (const payload of payloads) {
          const res = await fetch(DISPENSE_API, {
            method: "POST",
            credentials: "include",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
          });
          const data = await res.json();
          if (data.error) throw new Error(data.error);
        }

        showMessage("Dispense successful.", "success");
        setTimeout(() => selectRx(selectedId), 800);
>>>>>>> mainAdminMerge
      } catch (err) {
        showMessage(err.message, "error");
      }
    }

    function applySearch() {
      const term = searchInput.value.toLowerCase();
      renderList(allRx.filter(rx =>
        `${rx.firstName ?? ""} ${rx.lastName ?? ""}`.toLowerCase().includes(term) ||
        String(rx.prescriptionID).includes(term)
      ));
    }

    function highlightSelected() {
      rxListEl.querySelectorAll(".prescription-item").forEach(el =>
        el.classList.toggle("active", el.dataset.id == selectedId)
      );
    }

    function showMessage(text, type) {
      const el = document.getElementById("dispense-message");
      el.textContent = text;
      el.className = `message ${type}`;
      el.hidden = false;
    }

    function setListLoading() {
      rxListEl.innerHTML = "<p>Loading...</p>";
    }

    function setDetailsLoading() {
      rxDetailsEl.innerHTML = "<p>Loading details...</p>";
    }

    function escapeHtml(str) {
      return String(str ?? "").replace(/[&<>"']/g, m => ({
        "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;"
      }[m]));
    }
  });
}
