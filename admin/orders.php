<div class="container-fluid">
	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th class="text-center">#</th>
							<th class="text-center">Name</th>
							<th class="text-center">Recipient Email</th>

							<th class="text-center">Send At</th>
							<th class="text-center">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 1;
						include 'db_connect.php';
						$qry = $conn->query("SELECT * FROM orders ");
						while ($row = $qry->fetch_assoc()) :
						?>
							<tr>
								<td class="text-center"><?php echo $i++ ?></td>
								<td class="text-center"><?php echo $row['name'] ?></td>
								<td class="text-center"><?php echo $row['to_email'] ?></td>

								<!-- <td><img src="<?php echo isset($orderDetails['image_path']) ? $orderDetails['image_path'] : '' ?>" alt="Image"></td> -->

								<td><?php echo $row['created_at'] ?></td>
								<td>
									<button class="btn btn-sm btn-danger delete_order" data-id="<?php echo $row['id'] ?>">Delete Order</button>
								</td>

								<!-- <td>
									<button class="btn btn-sm btn-primary view_order" data-id="<?php echo $row['id'] ?>">View Order</button>
								</td> -->
							</tr>
						<?php endwhile; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

</div>
<style>
	img#cimg,
	.cimg {
		max-height: 50vh;
		max-width: 15vw;
	}
</style>
<script>
	$('.view_order').click(function() {
		uni_modal('Order', 'view_order.php?id=' + $(this).attr('data-id'))
	})
	$('table').dataTable();
	$('.delete_order').click(function() {
		var id = $(this).attr('data-id');
		if (confirm('Are you sure you want to delete this record?')) {
			$.ajax({
				url: 'delete_order.php',
				type: 'POST',
				data: {
					id: id
				},
				success: function(response) {
					if (response == 'success') {
						alert('Order deleted successfully');
						location.reload();
					} else {
						alert('Error deleting order');
					}
				}
			});
		}
	})
</script>