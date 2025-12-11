  // frontend/patient/prescription/prescription_medication.js

// From the point of view of prescription_medication.php:
//   /frontend/patient/prescription/prescription_medication.php
// → backend API endpoint (adjust name if your file differs):
const DISPENSE_API = "../../../backend/sql_handler/dispenserecord_table.php";

document.addEventListener('DOMContentLoaded', function() {
  const prescriptionItemID = new URLSearchParams(window.location.search).get('prescriptionItemID');
  
  if (prescriptionItemID) {
    loadDispenseHistory(prescriptionItemID);
  }
});

async function loadDispenseHistory(itemID) {
  const container = document.getElementById('dispense-history-container');
  if (!container) return;

  try {
    const res = await fetch(DISPENSE_API, {
      method: "GET",
      credentials: 'include'
    });
    
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    
    const allRecords = await res.json();
    const filtered = allRecords.filter(r => Number(r.prescriptionItemID) === Number(itemID));
    
    if (filtered.length === 0) {
      container.innerHTML = '<p>No dispense history for this medication.</p>';
      return;
    }

    const html = `
      <table class="table-base">
        <thead>
          <tr>
            <th>Dispense ID</th>
            <th>Pharmacy ID</th>
            <th>Quantity Dispensed</th>
            <th>Date Dispensed</th>
          </tr>
        </thead>
        <tbody>
          ${filtered.map(r => `
            <tr>
              <td>${escapeHtml(r.dispenseID ?? '-')}</td>
              <td>${escapeHtml(r.pharmacyID ?? '-')}</td>
              <td>${escapeHtml(r.dispensedQuantity ?? '-')}</td>
              <td>${formatDate(r.dispenseDate ?? '-')}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    `;
    container.innerHTML = html;
  } catch (err) {
    console.error('Failed to load dispense history:', err);
    container.innerHTML = '<p class="error">Failed to load dispense history. Please try again later.</p>';
  }
}

function formatDate(value) {
  if (!value || value === '-') return '-';
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return '-';
  return d.toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

function escapeHtml(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}