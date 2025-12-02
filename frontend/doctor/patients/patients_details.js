fetch("../../../backend/sql_handler/patient_table.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
        action: "get_patient_details",
        id: patientId
    })
})
    .then(res => res.json())
    .then(p => {
        document.getElementById("details").innerHTML = `
        <h3>${p.full_name}</h3>
        <p><strong>Age:</strong> ${p.age}</p>
        <p><strong>Gender:</strong> ${p.gender}</p>
        <p><strong>Address:</strong> ${p.address}</p>
        <p><strong>Contact:</strong> ${p.contact}</p>
    `;
    })
    .catch(err => console.log("Error:", err));
