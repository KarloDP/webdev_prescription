// View -> JS -> Model (sql_handler). Assumes session cookie auth is in place.
const RX_LIST_API = "../../backend/sql_handler/prescription_table.php";
const RX_DETAILS_API = "../../backend/sql_handler/prescription_table.php";
const RX_ITEM_API = "../../backend/sql_handler/prescriptionitem_table.php";

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
                // Automatically select the first item if none is selected
                const firstId = allRx[0].prescriptionID;
                window.history.replaceState({id: firstId}, '', `?prescription_id=${firstId}`);
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
            window.history.pushState({id: id}, '', `?prescription_id=${id}`);
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
        const hasRemaining = items.some(item => (parseInt(item.prescribed_amount, 10) || 0) > 0);

        rxDetailsEl.innerHTML = `
            <header class="rx-details-header">
                <h1>Prescription ${escapeHtml(rx.prescriptionID)}</h1>
            </header>
            <div class="rx-details-grid">
                <div><span class="label">Status:</span> ${escapeHtml(rx.status)}</div>
                <div><span class="label">Issue Date:</span> ${escapeHtml(rx.issueDate)}</div>
                <div><span class="label">Expiration:</span> ${escapeHtml(rx.expirationDate)}</div>
            </div>
            <section class="medication-rx-section">
                <h2>Rx Items</h2>
                <div id="rx-items-container">
                    ${renderRxItems(items)}
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
        const messageEl = document.getElementById('dispense-message');
        const itemInputs = document.querySelectorAll('.item-quantity-input');
        const dispensesToProcess = [];

        itemInputs.forEach(input => {
            const quantity = parseInt(input.value, 10);
            if (quantity > 0) {
                dispensesToProcess.push({
                    prescriptionItemID: input.dataset.itemId,
                    dispensedQuantity: quantity
                });
            }
        });

        if (dispensesToProcess.length === 0) {
            showMessage('Please enter a quantity for at least one medicine.', 'error');
            return;
        }

        showMessage('Dispensing...', 'info');

        try {
            for (const dispense of dispensesToProcess) {
                const res = await fetch('../../backend/sql_handler/dispenserecord_table.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dispense)
                });

                const raw = await res.text(); // read raw text first
                if (!res.ok) {
                    console.error('Dispense API error body:', raw);
                    throw new Error(`HTTP ${res.status}`);
                }

                let result;
                try { result = JSON.parse(raw); } catch (e) {
                    console.error('JSON parse failed, body:', raw);
                    throw new Error('Invalid JSON from server');
                }

                if (result.error) {
                    throw new Error(result.details || 'Server error');
                }
            }
            showMessage(`Successfully dispensed ${dispensesToProcess.length} item(s)! Reloading...`, 'success');
            setTimeout(() => selectRx(selectedId), 1500);
        } catch (err) {
            showMessage(`Error: ${err.message}`, 'error');
            console.error('Dispense error:', err);
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
        return String(str ?? "").replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[m]);
    }
});