<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once('header.php');
require_once('admin/db_connect.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Invalid order ID";
    exit;
}

$orderID = $_GET['id'];

$query = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $orderID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $orderDetails = $result->fetch_assoc();
} else {
    echo "No order found with this ID";
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
</head>
<style>
    /* Reset some default styles */
    body {
        font-family: Arial, sans-serif;
        background-color: #f0f0f0;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 800px;
        margin: 0 auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
        color: #333;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    table,
    th,
    td {
        border: 1px solid #ddd;
    }

    th,
    td {
        padding: 10px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    a {
        color: #007bff;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    /* Style the "Back to Order History" link */
    a.back-link {
        display: inline-block;
        padding: 10px 20px;
        background-color: #007bff;
        /* Button color */
        color: #fff;
        /* Text color */
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    a.back-link:hover {
        background-color: #0056b3;
        /* Hover color */
    }
</style>

<body>
    <div class="container">
        <a class="back-link" href="javascript:history.back();">Back to Order History</a>
        <br><br><br>
        <h2>Order Details</h2>
        <table class="table">
            <tr>
                <th>ID</th>
                <td><?php echo $orderDetails['id']; ?></td>
            </tr>
            <tr>
                <th>Created At</th>
                <td><?php echo $orderDetails['created_at']; ?></td>
            </tr>
            <tr>
                <th>Name</th>
                <td><?php echo $orderDetails['name']; ?></td>
            </tr>
            <tr>
                <th>Recipient Email</th>
                <td><?php echo $orderDetails['to_email']; ?></td>
            </tr>
            <tr>
                <th>Message</th>
                <td><?php echo $orderDetails['message']; ?></td>
            </tr>
            <tr>
                <th>Image</th>
                <td><img src="<?php echo isset($orderDetails['image_path']) ? $orderDetails['image_path'] : '' ?>" alt="Image"></td>
            </tr>

            <!-- Add more fields as necessary -->
        </table>

    </div>
</body>

</html>