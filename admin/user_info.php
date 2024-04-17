<?php
include 'db_connect.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <button class="btn btn-primary float-right btn-sm" id="new_user_info"><i class="fa fa-plus"></i> New User Info</button>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">First Name</th>
                                <th class="text-center">Last Name</th>
                                <th class="text-center">Email</th>
                                <th class="text-center">Mobile</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $userInfos = $conn->query("SELECT * FROM user_info ORDER BY first_name ASC");
                            $i = 1;
                            while ($row = $userInfos->fetch_assoc()) :
                            ?>
                                <tr>
                                    <td><?php echo $i++ ?></td>
                                    <td><?php echo $row['first_name'] ?></td>
                                    <td><?php echo $row['last_name'] ?></td>
                                    <td><?php echo $row['email'] ?></td>
                                    <td><?php echo $row['mobile'] ?></td>
                                    <td>
                                        <center>
                                            <div class="btn-group">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <button class="btn btn-sm btn-primary edit_user_info" type="button" data-id='<?php echo $row['user_id'] ?>' data-first_name='<?php echo $row['first_name'] ?>' data-last_name='<?php echo $row['last_name'] ?>' data-email='<?php echo $row['email'] ?>' data-mobile='<?php echo $row['mobile'] ?>' data-password='<?php echo $row['password'] ?>'>Edit</button>
                                                        <button class="btn btn-sm btn-danger delete_user_info" type="button" data-id='<?php echo $row['user_id'] ?>'>Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </center>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('#new_user_info').click(function() {
        uni_modal('New User Info', 'manage_user_info.php')
    })

    $('.edit_user_info').click(function() {
        var id = $(this).attr('data-id');
        var first_name = $(this).attr('data-first_name');
        var last_name = $(this).attr('data-last_name');
        var email = $(this).attr('data-email');
        var mobile = $(this).attr('data-mobile');
        var password = $(this).attr('data-password');
        uni_modal('Edit User Info', 'manage_user_info.php?user_id=' + id + '&first_name=' + first_name + '&last_name=' + last_name + '&email=' + email + '&mobile=' + mobile +'&password=' + password);
    })

    $('.delete_user_info').click(function() {
        _conf("Are you sure you want to delete this user info?", "delete_user_info", [$(this).attr('data-id')])
    })

    function delete_user_info(user_id) {
        start_load()
        $.ajax({
            url: 'ajax.php?action=delete_user_info',
            method: 'POST',
            data: { user_id: user_id },
            success: function(resp) {
                if (resp == 1) {
                    alert_toast("Data successfully deleted", 'success')
                    setTimeout(function() {
                        location.reload()
                    }, 1500)
                }
            }
        })
    }

    $('table').dataTable();
</script>
