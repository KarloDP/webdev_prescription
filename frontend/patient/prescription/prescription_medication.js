// frontend/patient/prescription_medication.js

// API endpoints
const RX_API = "../../backend/sql_handler/prescription_table.php";
const RX_ITEM_API = "../../backend/sql_handler/prescriptionitem_table.php";

document.addEventListener("DOMContentLoaded", () => {
    loadGroupedPrescriptions();
});

async function loadGroupedPrescriptions() {
    const container = document.getElementById("prescription-groups");
    const patientID = window.currentPatient?.id;

    if (!patientID) {
        container.innerHTML = "<p>Error: No patient ID found.</p>";
        return;
    }

    try {
        // fetch via prescriptionitem_table.php?patientID=xx&group=true
        const res = await fetch(`${RX_ITEM_API}?patientID=${patientID}&grouped=true`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        const data = await res.json();
        if (!Array.isArray(data) || data.length === 0) {
            container.innerHTML = "<p>No prescriptions found.</p>";
            return;
        }

        // Group rows by prescriptionID
        const groups = {};
        data.forEach(item => {
            const rxID = item.prescriptionID;
            if (!groups[rxID]) groups[rxID] = [];
            groups[rxID].push(item);
        });

        container.innerHTML = Object.keys(groups)
            .map(renderPrescriptionGroup)
            .join("");

    } catch (err) {
        console.error("Failed loading prescriptions:", err);
        container.innerHTML = "<p>Error loading prescriptions.</p>";
    }
}

function renderPrescriptionGroup(rxID) {
    const items = window.groupedData ? window.groupedData[rxID] : [];
    const data = items[0];

    const doctorFull = data.doctorName || "-";
    const issueDate  = formatDate(data.issueDate);
    const statusBadge =
        data.status === "Active"
            ? "<span style='color:#155724;background:#d4edda;padding:1px 4px;border-radius:4px;'>Active</span>"
            : "<span style='color:#721c24;background:#f8d7da;padding:1px 4px;border-radius:4px;'>Expired</span>";

    return `
    <div class="prescription-group">
        <h3>Prescription RX-${String(rxID).padStart(2, '0')} (${statusBadge})</h3>
        <p>Doctor: ${doctorFull} | Issued: ${issueDate}</p>
        <table class="table-base">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Brand</th>
                    <th>Dosage</th>
                    <th>Frequency</th>
                    <th>Duration</th>
                    <th>Amount</th>
                    <th>Refills</th>
                    <th>Instructions</th>
                </tr>
            </thead>
            <tbody>
                ${items.map(renderItemRow).join("")}
            </tbody>
        </table>
    </div>
    `;
}

function renderItemRow(row) {
    return `
    <tr>
        <td>${escapeHtml(row.medicine ?? "-")}</td>
        <td>${escapeHtml(row.brand ?? "-")}</td>
        <td>${escapeHtml(row.dosage ?? "-")}</td>
        <td>${escapeHtml(row.frequency ?? "-")}</td>
        <td>${escapeHtml(row.duration ?? "-")}</td>
        <td>${escapeHtml(row.prescribed_amount ?? "-")}</td>
        <td>${escapeHtml(row.refill_count ?? "-")}</td>
        <td>${escapeHtml(row.instructions ?? "-")}</td>
    </tr>
    `;
}

function formatDate(val) {
    if (!val) return "-";
    const d = new Date(val);
    return d.toLocaleDateString(undefined, { year: "numeric", month: "long", day: "numeric" });
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}