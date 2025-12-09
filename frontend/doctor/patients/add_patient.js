document.getElementById("addPatientForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let data = {
        action: "add_patient",
        doctorID: LOGGED_DOCTOR_ID,
        full_name: document.getElementById("full_name").value,
        age: document.getElementById("age").value,
        gender: document.getElementById("gender").value,
        address: document.getElementById("address").value,
        contact: document.getElementById("contact").value
    };

    fetch("../../../backend/sql_handler/patient_table.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
    })
        .then(res => res.json())
        .then(response => {
            let msg = document.getElementById("message");

            if (response.success) {
                msg.style.color = "green";
                msg.textContent = "Patient added successfully!";

                document.getElementById("addPatientForm").reset();
            } else {
                msg.style.color = "red";
                msg.textContent = response.message;
            }
        })
        .catch(err => console.log(err));
});
