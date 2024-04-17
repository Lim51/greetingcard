<?php

require_once('admin/db_connect.php');


if (isset($_SESSION['login_email'])) {
    $userEmail = $_SESSION['login_email'];
}
$query = "SELECT * FROM orders WHERE from_email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();

?>



<!-- Masthead-->
<header class="masthead">
    <div class="container h-100">
        <div class="row h-100 align-items-center justify-content-center text-center">
            <div class="col-lg-10 align-self-center mb-4 page-title">
                <h1 class="text-white">Sending History</h1>
                <hr class="divider my-4 bg-dark" />
            </div>

        </div>
    </div>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>

</header>

<body>
    <div class="container">
        <br>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Recipient Email</th>
                    <!-- <th>Image Path</th> -->
                    <th>Send At</th>
                    <th>View Details</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                while ($row = $result->fetch_assoc()) :
                ?>
                    <tr>
                        <td><?php echo $i++ ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['to_email']; ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td><a href="viewOrderDetails.php?id=<?php echo $row['id']; ?>">View</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
<script>
    $(document).ready(function() {
        $('.table').DataTable();
    });
</script>