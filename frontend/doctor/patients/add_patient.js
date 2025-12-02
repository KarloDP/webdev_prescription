document.getElementById("addPatientForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let data = {
        full_name: document.getElementById("full_name").value,
        age: document.getElementById("age").value,
        gender: document.getElementById("gender").value,
        address: document.getElementById("address").value,
        contact: document.getElementById("contact").value
    };

    fetch("../../../backend/doctor/add_patient.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify(data)
    })
        .then(res => res.json())
        .then(response => {
            const msg = document.getElementById("message");
            msg.style.color = response.success ? "green" : "red";
            msg.textContent = response.success ? "Patient added successfully" : response.error;
            if (response.success) {
                document.getElementById("addPatientForm").reset();
            }
        })
        .catch(err => console.log(err));
});
