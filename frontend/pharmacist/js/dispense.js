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
    let activeRx = [];
    let selectedId = null;

    const urlParams = new URLSearchParams(window.location.search);
    const preselectedId = urlParams.get("prescription_id");

    loadList(preselectedId);
    searchInput?.addEventListener("input", applySearch);

    async function loadList(preselectedId = null) {
      setListLoading();
      try {
        const res = await fetch(`${RX_LIST_API}?includeExpired=1`, { credentials: "include" });
        const data = await res.json();
        const rawList = Array.isArray(data) ? data : [];

        allRx = rawList.map(rx => ({
          ...rx,
          isExpired: isRxExpired(rx)
        }));
        activeRx = allRx.filter(rx => !rx.isExpired);

        renderList(activeRx);
        if (preselectedId) {
          selectRx(preselectedId);
        } else if (activeRx.length) {
          selectRx(activeRx[0].prescriptionID);
        } else {
          setDetailsLoading();
        }
      } catch (err) {
        console.error("Failed to load prescriptions", err);
        rxListEl.innerHTML = "<p>Failed to load prescriptions.</p>";
        setDetailsLoading();
      }
    }

    function renderList(list, fromSearch = false) {
      if (!list.length) {
        rxListEl.innerHTML = fromSearch ? "<p>No matching prescriptions.</p>" : "<p>No active prescriptions.</p>";
        return;
      }

      rxListEl.innerHTML = list.map(rx => {
        const patientName = resolvePatientName(rx);
        const label = `RX-${rx.prescriptionID} - ${patientName || "Unknown patient"}`;
        const expiredBadge = rx.isExpired ? '<span class="rx-badge">Expired</span>' : "";

        return `
        <div class="prescription-item ${selectedId == rx.prescriptionID ? "active" : ""} ${rx.isExpired ? "is-expired" : ""}" data-id="${rx.prescriptionID}">
          <a href="?prescription_id=${rx.prescriptionID}" data-id="${rx.prescriptionID}">
            <div class="rx-line">
              <span class="rx-label">${escapeHtml(label)}</span>
              ${expiredBadge}
            </div>
          </a>
        </div>
      `;
      }).join("");

      rxListEl.querySelectorAll("a[data-id]").forEach(a =>
        a.addEventListener("click", e => {
          e.preventDefault();
          const id = a.dataset.id;
          history.pushState({ id }, "", `?prescription_id=${id}`);
          selectRx(id);
        })
      );
      highlightSelected();
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
      const patientName = escapeHtml(resolvePatientName(rx));

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
        }
      });

      if (!payloads.length) {
        showMessage("Enter valid quantities.", "error");
        return;
      }

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
      } catch (err) {
        showMessage(err.message, "error");
      }
    }

    function applySearch() {
      const term = (searchInput?.value || "").trim().toLowerCase();
      if (!term) {
        renderList(activeRx);
        return;
      }

      const matches = allRx.filter(rx => {
        const name = resolvePatientName(rx).toLowerCase();
        return name.includes(term) || String(rx.prescriptionID).includes(term);
      });

      renderList(matches, true);
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

    function resolvePatientName(rx = {}) {
      const firstCandidates = [
        rx.patientFirstName, rx.firstName, rx.patient_first_name,
        rx.patient_firstname, rx.patient_first, rx.patientGivenName,
        rx.givenName
      ];
      const lastCandidates = [
        rx.patientLastName, rx.lastName, rx.patient_last_name,
        rx.patient_lastname, rx.patient_last, rx.patientSurname,
        rx.surname, rx.familyName
      ];
      const miscCandidates = [
        rx.patientName, rx.patient_name, rx.patientFullName,
        rx.patient_fullname, rx.patientFullname, rx.patient_full_name,
        rx.fullName, rx.full_name, rx.patient, rx.name
      ];

      const first = firstCandidates.find(v => typeof v === "string" && v.trim().length) ?? "";
      const last = lastCandidates.find(v => typeof v === "string" && v.trim().length) ?? "";
      const combined = `${first} ${last}`.trim();

      if (combined.length) return combined;

      const fallback = miscCandidates.find(v => typeof v === "string" && v.trim().length);
      return fallback ? fallback.trim() : "";
    }

    function isRxExpired(rx = {}) {
      const status = String(rx.status || "").toLowerCase();
      if (status === "expired") return true;
      if (!rx.expirationDate) return false;
      const exp = new Date(rx.expirationDate);
      if (Number.isNaN(exp.getTime())) return false;
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      exp.setHours(0, 0, 0, 0);
      return exp < today;
    }
  });
}
