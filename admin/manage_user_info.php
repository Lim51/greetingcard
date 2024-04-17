<?php
include('db_connect.php');

if (isset($_GET['user_id'])) {
    $stmt = $conn->prepare("SELECT * FROM user_info WHERE user_id = ?");
    $stmt->bind_param("i", $_GET['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $meta = $result->fetch_assoc();
    }
    $stmt->close();
}
?>

<div class="container-fluid">
    <form action="" id="manage-user-info">
        <input type="hidden" name="user_id" value="<?php echo isset($meta['user_id']) ? $meta['user_id'] : '' ?>">
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo isset($meta['first_name']) ? $meta['first_name'] : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo isset($meta['last_name']) ? $meta['last_name'] : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo isset($meta['email']) ? $meta['email'] : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="mobile">Mobile</label>
            <input type="text" name="mobile" id="mobile" class="form-control" value="<?php echo isset($meta['mobile']) ? $meta['mobile'] : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" value="<?php echo isset($meta['password']) ? $meta['password'] : '' ?>" required>
        </div>

    </form>
</div>

<script>
    $('#manage-user-info').submit(function(e) {
        e.preventDefault();
        start_load()
        $.ajax({
            url: 'ajax.php?action=save_user_info',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
                if (resp == 1) {
                    alert_toast("Data successfully saved", 'success')
                    setTimeout(function() {
                        location.reload()
                    }, 1500)
                }
            }
        })
    })
</script>