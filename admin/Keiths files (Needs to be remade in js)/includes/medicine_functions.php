<?php
require_once "db_connect.php";

function deleteMedicine($id, $conn) {
    $id = intval($id);
    if ($id > 0) {
        $conn->query("DELETE FROM medication WHERE medicationID = $id");
    }
}

function saveMedicine($post, $conn) {
    $id      = !empty($post['id']) ? intval($post['id']) : null;
    $generic = $conn->real_escape_string($post['genericName'] ?? '');
    $brand   = $conn->real_escape_string($post['brandName'] ?? '');
    $form    = $conn->real_escape_string($post['form'] ?? '');
    $strength= $conn->real_escape_string($post['strength'] ?? '');
    $manuf   = $conn->real_escape_string($post['manufacturer'] ?? '');
    $stock   = intval($post['stock'] ?? 0);

    if ($id) {
        $conn->query("UPDATE medication 
                      SET genericName='$generic', brandName='$brand', form='$form', 
                          strength='$strength', manufacturer='$manuf', stock=$stock
                      WHERE medicationID=$id");
    } else {
        $conn->query("INSERT INTO medication (genericName, brandName, form, strength, manufacturer, stock)
                      VALUES ('$generic', '$brand', '$form', '$strength', '$manuf', $stock)");
    }
}
