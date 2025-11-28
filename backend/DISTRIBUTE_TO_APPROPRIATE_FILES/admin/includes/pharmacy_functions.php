<?php
require_once "db_connect.php";

function deletePharmacy($id, $conn) {
    $id = intval($id);
    if ($id > 0) {
        $conn->query("DELETE FROM pharmacy WHERE pharmacyID = $id");
    }
}

function savePharmacy($post, $conn) {
    $id      = !empty($post['id']) ? intval($post['id']) : null;
    $name    = $conn->real_escape_string($post['name'] ?? '');
    $address = $conn->real_escape_string($post['address'] ?? '');
    $contact = $conn->real_escape_string($post['contactNumber'] ?? '');
    $email   = $conn->real_escape_string($post['email'] ?? '');
    $clinic  = $conn->real_escape_string($post['clinicAddress'] ?? '');

    if ($id) {
        $conn->query("UPDATE pharmacy 
                      SET name='$name', address='$address', contactNumber='$contact', 
                          email='$email', clinicAddress='$clinic'
                      WHERE pharmacyID=$id");
    } else {
        $conn->query("INSERT INTO pharmacy (name, address, contactNumber, email, clinicAddress)
                      VALUES ('$name', '$address', '$contact', '$email', '$clinic')");
    }
}
