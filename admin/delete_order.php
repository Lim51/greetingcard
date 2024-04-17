<?php
include 'db_connect.php';

if(isset($_POST['id'])) {
    $id = $_POST['id'];
    
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo 'error';
}
?>
