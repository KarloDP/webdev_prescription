//default landing page. redirect to login page if user session is not started.
document.addEventListener('DOMContentLoaded', function() {
    // Fetch dashboard statistics
fetch('/WebDev_Prescription/backend/sql_handler/get_dashboard_stats.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('patients-count').textContent = data.total_patients || '0';
            document.getElementById('active-prescriptions-count').textContent = data.active_prescriptions || '0';
            document.getElementById('meds-prescribed-count').textContent = data.medications_prescribed || '0';
        })
        .catch(error => {
            console.error('Error fetching dashboard stats:', error);
            document.getElementById('patients-count').textContent = 'N/A';
            document.getElementById('active-prescriptions-count').textContent = 'N/A';
            document.getElementById('meds-prescribed-count').textContent = 'N/A';
        });
    // Fetch patients list
    fetch('/WebDev_Prescription/backend/sql_handler/get_patients_list.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const tableBody = document.getElementById('patients-table-body');
            tableBody.innerHTML = ''; // Clear loading message

            if (data && data.length > 0) {
                data.forEach(patient => {
                    const row = `
                        <tr>
                            <td>${patient.PatientID}</td>
                            <td>${patient.FirstName}</td>
                            <td>${patient.LastName}</td>
                            <td>${patient.DateOfBirth}</td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="4">No patients found.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error fetching patients list:', error);
            const tableBody = document.getElementById('patients-table-body');
            tableBody.innerHTML = '<tr><td colspan="4">Error loading patients.</td></tr>';
        });
});