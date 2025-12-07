const API_URL = "../../backend/sql_handler/get_pharmacist_dashboard_stats.php";

document.addEventListener("DOMContentLoaded", () => {
    loadDashboard();
});

async function loadDashboard() {
    try {
        const res = await fetch(API_URL, { credentials: "include" });
        
        // Get the response text first to see what we're getting
        const text = await res.text();
        
        if (!res.ok) {
            console.error("Response status:", res.status);
            console.error("Response body:", text);
            throw new Error(`HTTP Error: ${res.status}`);
        }

        // Try to parse as JSON
        let data;
        try {
            data = JSON.parse(text);
        } catch (parseError) {
            console.error("Failed to parse JSON:", text);
            throw new Error("Invalid JSON response from server");
        }

        if (data.error) throw new Error(data.details || data.error);

        // Update stats
        document.getElementById("pending-count").textContent = data.pending_count ?? 0;
        document.getElementById("dispensed-count").textContent = data.dispensed_count ?? 0;
        document.getElementById("active-count").textContent = data.active_count ?? 0;
        document.getElementById("expiring-count").textContent = data.expiring_count ?? 0;

        // Load recent prescriptions
        await loadRecentPrescriptions();

    } catch (err) {
        console.error("Failed to load dashboard data:", err);
        const listContainer = document.getElementById("recent-rx-list");
        if(listContainer) {
            listContainer.innerHTML = `<p class="error">Could not load dashboard data. Error: ${err.message}</p>`;
        }
    }
}

async function loadRecentPrescriptions() {
    try {
        const res = await fetch("../../backend/sql_handler/prescription_table.php", { credentials: "include" });
        const text = await res.text();
        
        if (!res.ok) {
            console.error("Prescriptions response:", text);
            throw new Error(`HTTP Error: ${res.status}`);
        }

        let prescriptions;
        try {
            prescriptions = JSON.parse(text);
        } catch (parseError) {
            console.error("Failed to parse prescriptions JSON:", text);
            throw new Error("Invalid JSON response");
        }

        const container = document.getElementById("recent-rx-list");
        if (!container) return;

        if (!prescriptions || prescriptions.length === 0) {
            container.innerHTML = "<p>No active prescriptions found.</p>";
            return;
        }

        // Show only the 5 most recent
        const recent = prescriptions.slice(0, 5);

        container.innerHTML = `
            <h3>Recent Prescriptions</h3>
            <div class="prescriptions-grid">
                ${recent.map(rx => `
                    <div class="prescription-card">
                        <div class="card-header">
                            <span>Patient ID: <strong>${escapeHtml(rx.patientID)}</strong></span>
                            <span>Rx ID: <strong>${escapeHtml(rx.prescriptionID)}</strong></span>
                        </div>
                        <div class="card-body">
                            <p>Issue Date: <strong>${escapeHtml(rx.issueDate)}</strong></p>
                            <p>Expiration: <strong>${escapeHtml(rx.expirationDate)}</strong></p>
                            <p>Status: <span class="status-badge status-${rx.status.toLowerCase()}">${escapeHtml(rx.status)}</span></p>
                        </div>
                        <div class="card-footer">
                            <a href="prescription.php?id=${rx.prescriptionID}" class="btn-view">View Details</a>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

    } catch (err) {
        console.error("Failed to load recent prescriptions:", err);
        const container = document.getElementById("recent-rx-list");
        if(container) {
            container.innerHTML = `<p class="error">Could not load recent prescriptions. Error: ${err.message}</p>`;
        }
    }
}

function escapeHtml(str) {
    return String(str ?? "")
        .replace(/&/g, "&amp;").replace(/</g, "&lt;")
        .replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}