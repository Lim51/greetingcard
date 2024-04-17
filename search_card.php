<?php
include 'admin/db_connect.php';

if (isset($_POST['search'])) {
    $searchVal = $_POST['search'];

    $stmt = $conn->prepare("SELECT * FROM card_list WHERE status = ? AND name LIKE ?");
    $status = 1;
    $searchParam = "%" . $searchVal . "%";
    $stmt->bind_param("is", $status, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Output the HTML code for each menu item
            echo '
                <div class="col-lg-3 mb-3">
                    <div class="card menu-item rounded-0">
                        <div class="position-relative overflow-hidden" id="item-img-holder">
                            <img src="assets/img/' . $row['img_path'] . '" class="card-img-top" alt="...">
                        </div>
                        <div class="card-body rounded-0">
                            <h5 class="card-title">' . $row['name'] . '</h5>
                            <div class="text-center">
                                <button class="btn btn-sm btn-outline-dark view_prod btn-block" data-id="' . $row['id'] . '"><i class="fa fa-eye"></i> View</button>
                            </div>
                        </div>
                    </div>
                </div>
            ';
        }
    } else {
        echo "No cards found!";
    }
}
?>
