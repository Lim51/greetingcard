<?php
include 'admin/db_connect.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['editedImg'])) {
    $imageData = $_POST['editedImg'];
    $canvasState = $_POST['canvasState'] ?? null; // Get the canvasState
    $id = $_POST['id'] ?? null;

    $filteredData = substr($imageData, strpos($imageData, ",") + 1);
    $decodedData = base64_decode($filteredData);
    $imageName = "assets/img/edited_images/" . uniqid() . ".png";

    if (file_put_contents($imageName, $decodedData)) {
        if ($id === null) {
            $stmt = $conn->prepare("INSERT INTO edit_card (edit_image, canvas_state) VALUES (?, ?)");
            $stmt->bind_param("ss", $imageName, $canvasState);
        } else {
            $stmt = $conn->prepare("UPDATE edit_card SET edit_image = ?, canvas_state = ? WHERE id = ?");
            $stmt->bind_param("ssi", $imageName, $canvasState, $id);
        }
        if ($stmt->execute()) {
            if ($id === null) {
                // Fetch the last inserted id
                $id = $conn->insert_id;
            }
            error_log("Debugging ID in PHP: " . $id); // <-- Debugging line
            echo json_encode(["success" => true, "id" => $id, "edit_image" => $imageName]); // <-- Include 'id' in the response
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update database"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Failed to save image"]);
    }

    // Debugging: Check if canvas state is being received
    if (isset($_POST['canvasState'])) {
        error_log("Received canvas state: " . $_POST['canvasState']);
    } else {
        error_log("Canvas state not received in POST data.");
    }
} else {
    echo json_encode(["success" => false, "message" => "No image data received"]);
}
?>