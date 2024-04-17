<?php
session_start();

// Check if cart exists, if not, create it
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if ($_POST['action'] == 'add') {
    $id = $_POST['id'];
    $_SESSION['cart'][$id] = isset($_SESSION['cart'][$id]) ? $_SESSION['cart'][$id] + 1 : 1;
    echo "Added to cart";
}

// TODO: Handle other actions like delete, move, modify
?>
