<!-- handles add data retrieval and insertion to the database -->
<?php
include __DIR__.("../backend/included/db_connect.php");
function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    //returns a specific entry if an ID is given
    if (isset($_GET['adminID'])) {
        $adminID = intval($_GET['adminID']);
        $stmt = $conn->prepare('SELECT * FROM admins WHERE adminID = ?');
        $stmt->bind_param('i', $adminID);
        $stmt->execute();
        $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        respond(['error' => 'Admin not found'], 404);
    }
    respond($result->fetch_assoc());
    }

    //returns all entries from admin table
    $sql = "SELECT adminID, firstName, lastName FROM admins ORDER BY adminID";
    $result = $conn->query($sql);

    if (!$result) {
        respond(['error' => 'Database connection error: '.$conn->error], 500);
    }

    $data=[];
    while ($row = $result->fetch_assoc()) {
        $data[]=$row;
    }
    respond($data);

} else if ($method === 'POST') {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    //note that adminID is auto increment in the database so no ID needs to be taken from a user input
    $firstName = trim($data['firstName'] ??'');
    $lastName = trim($data['lastName'] ??'');

    if ($firstName === '' || $lastName === '') {
        respond(['error' => 'Firstname and lastname are required'], 400);
    }

    $stmt = $conn->prepare('INSERT INTO admins (firstName, lastName) VALUES (?, ?)');
    $stmt->bind_param('ss', $firstName, $lastName);

    if ($stmt->execute()) {
        respond([
            'staus'=> 'success',
            'message' => 'New Admin Added',
            'insert_ID' => $stmt->insert_id
        ], 201);
    } else {
        respond(['error'=> 'Insert Failed: ' . $stmt->error], 500);
    }
}
?>