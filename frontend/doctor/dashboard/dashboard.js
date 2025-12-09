//default landing page. redirect to login page if user session is not started.
document.addEventListener('DOMContentLoaded', function() {
  fetch('/WebDev_Prescription/backend/sql_handler/get_dashboard_stats.php')
    .then(r => r.json())
    .then(data => {
      document.getElementById('patients-count').textContent = data.total_patients ?? '0';
      document.getElementById('active-prescriptions-count').textContent = data.active_prescriptions ?? '0';
      document.getElementById('meds-prescribed-count').textContent = data.medications_prescribed ?? '0';
    })
    .catch(() => {
      document.getElementById('patients-count').textContent = 'N/A';
      document.getElementById('active-prescriptions-count').textContent = 'N/A';
      document.getElementById('meds-prescribed-count').textContent = 'N/A';
    });

  fetch('/WebDev_Prescription/backend/sql_handler/get_patients_list.php')
    .then(r => r.json())
    .then(data => {
      const tbody = document.getElementById('patients-table-body');
      tbody.innerHTML = '';
      if (!data || !data.length) {
        tbody.innerHTML = '<tr><td colspan="4">No patients found.</td></tr>';
        return;
      }
      data.forEach(p => {
        const row = `
          <tr>
            <td>${escapeHTML(p.patientID ?? p.patientId ?? p.id ?? '')}</td>
            <td>${escapeHTML(p.firstName ?? p.firstname ?? p.FirstName ?? '')}</td>
            <td>${escapeHTML(p.lastName ?? p.lastname ?? p.LastName ?? '')}</td>
            <td>${escapeHTML(p.birthDate ?? p.birthdate ?? p.dateOfBirth ?? p.DateOfBirth ?? '')}</td>
          </tr>`;
        tbody.insertAdjacentHTML('beforeend', row);
      });
    })
    .catch(() => {
      const tbody = document.getElementById('patients-table-body');
      tbody.innerHTML = '<tr><td colspan="4">Error loading patients.</td></tr>';
    });

  function escapeHTML(str) {
    return String(str ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }
});