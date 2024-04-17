<?php
include 'db_connect.php';
?>

<div class="container-fluid">

	<div class="row">
		<div class="col-lg-12">
			<button class="btn btn-primary float-right btn-sm" id="new_user"><i class="fa fa-plus"></i> New user</button>
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
								<th class="text-center">Name</th>
								<th class="text-center">Username</th>
								<th class="text-center">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php

							$users = $conn->query("SELECT * FROM users order by name asc");
							$i = 1;
							while ($row = $users->fetch_assoc()) :
							?>
								<tr>
									<td>
										<?php echo $i++ ?>
									</td>
									<td>
										<?php echo $row['name'] ?>
									</td>
									<td>
										<?php echo $row['username'] ?>
									</td>
									<td>
										<center>
											<div class="btn-group">
												<div class="row">
													<div class="col-md-12">
														<button class="btn btn-sm btn-primary edit_user" type="button" data-id='<?php echo $row['id'] ?>' data-name='<?php echo $row['name'] ?>' data-username='<?php echo $row['username'] ?>' data-password='<?php echo $row['password'] ?>' data-type='<?php echo $row['type'] ?>'>Edit</button>
														<button class="btn btn-sm btn-danger delete_user" type="button" data-id='<?php echo $row['id'] ?>'>Delete</button>
													</div>
												</div>
											</div>
										</center>
									</td>

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
    $('#new_user').click(function() {
        uni_modal('New User', 'manage_user.php')
    })

    $('.edit_user').click(function() {
        var id = $(this).attr('data-id');
        var name = $(this).attr('data-name');
        var username = $(this).attr('data-username');
        var password = $(this).attr('data-password');
        var type = $(this).attr('data-type');
        uni_modal('Edit User', 'manage_user.php?id=' + id + '&name=' + name + '&username=' + username + '&password=' + password + '&type=' + type);
    })

    $('.delete_user').click(function() {
		_conf("Are you sure you want to delete this user?","delete_user",[$(this).attr('data-id')])
	})
	function delete_user($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_user',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully deleted",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	}

    $('table').dataTable();
</script>
