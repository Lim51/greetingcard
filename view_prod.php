<?php
include 'admin/db_connect.php';
$id = $_GET['id'];
$qry = $conn->query("SELECT * FROM card_list WHERE id = $id")->fetch_array();
?>
<div class="container-fluid">
	<div class="card">
		<img src="assets/img/<?php echo $qry['img_path'] ?>" class="card-img-top" alt="...">
		<div class="card-body">
			<h5 class="card-title"><?php echo $qry['name'] ?></h5>
			<p class="card-text truncate"><?php echo $qry['description'] ?></p>
		</div>

		<div class="text-center">
			<button class="btn btn-outline-dark btn-sm btn-block" id="add_to_cart_modal" data-img-path="<?php echo $qry['img_path']; ?>" data-img-id="<?php echo $qry['id']; ?>">
				<i class="fa fa-cart-plus"></i> Send This Card
			</button>
		</div>
	</div>
</div>

<style>
	#uni_modal_right .modal-footer {
		display: none;
	}
</style>


<script>
	$('#add_to_cart_modal').click(function() {
		// Extract the image path and image ID from the button attributes
		var imgPath = $(this).data('img-path');
		var imgId = $(this).data('img-id');

		$.post('cart_actions.php', {
			action: 'add',
			id: imgId
		}, function(resp) {
			// Redirect to the canvas editor page with the image path and ID as query parameters
			window.location.href = 'canvas_editor.php?img=' + encodeURIComponent(imgPath) + '&id=' + imgId;
		});
		
	});
</script>