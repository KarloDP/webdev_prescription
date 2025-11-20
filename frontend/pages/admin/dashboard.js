//default landing page. redirect to login page if user session is not started.

//below is test script. do not use in final
// ======= CONFIG =======
const API_URL = "backend/sql_handler/admins_table.php"; // Change if needed

// ========= DOM SELECTORS ==========
const outputBox = document.getElementById("output");

function showOutput(data) {
    outputBox.innerText = JSON.stringify(data, null, 2);
}

// ======== GET ALL ADMINS =========
export function testGetAllAdmins() {
    fetch(API_URL)
        .then(response => response.json())
        .then(data => showOutput(data))
        .catch(error => showOutput(error));
}

// ======== GET ONE ADMIN BY ID ========
export function testGetAdminByID() {
    const id = document.getElementById("adminID").value;
    if (!id) return alert("Please enter an adminID!");

    fetch(`${API_URL}?adminID=${id}`)
        .then(response => response.json())
        .then(data => showOutput(data))
        .catch(error => showOutput(error));
}

// ======== ADD NEW ADMIN (POST) ========
export function testAddAdmin() {
    const firstName = document.getElementById("firstName").value;
    const lastName  = document.getElementById("lastName").value;

    if (!firstName || !lastName) {
        return alert("Please enter both firstName and lastName!");
    }

    fetch(API_URL, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ firstName, lastName })
    })
    .then(response => response.json())
    .then(data => showOutput(data))
    .catch(error => showOutput(error));
}
