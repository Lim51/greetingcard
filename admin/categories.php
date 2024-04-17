<?php include('db_connect.php');?>

<div class="container-fluid">

	<div class="col-lg-12">
		<div class="row">
			<!-- FORM Panel -->
			<div class="col-md-4">
				<form action="" id="manage-category">
					<div class="card">
						<div class="card-header">
							Category Form
						</div>
						<div class="card-body">
							<input type="hidden" name="id">
							<div class="form-group">
								<label class="control-label">Category</label>
								<input type="text" class="form-control" name="name">
							</div>
							<div class="form-group">
								<div class="custom-control custom-switch">
									<input type="checkbox" name="status" class="custom-control-input" id="availability" onchange="toggleMenuForm()">
									<label class="custom-control-label" for="availability">Available</label>
								</div>
							</div>

							<div class="form-group">
								<label for="" class="control-label">Image</label>
								<input type="file" class="form-control" name="img" onchange="displayImg(this,$(this))">
							</div>
							<div class="form-group">
								<img src="<?php echo isset($image_path) ? '../assets/img/category/' . $cover_img : '' ?>" alt="" id="cimg">
							</div>

						</div>

						<div class="card-footer">
							<div class="row">
								<div class="col-md-12">
									<button class="btn btn-sm btn-primary col-sm-3 offset-md-3"> Save</button>
									<button class="btn btn-sm btn-default col-sm-3" type="button" onclick="$('#manage-category').get(0).reset()"> Cancel</button>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
			<!-- FORM Panel -->

			<!-- Table Panel -->
			<div class="col-md-8">
				<div class="card">
					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-bordered table-hover">
								<thead>
									<tr>
										<th class="text-center">#</th>
										<th class="text-center">Name</th>
										<th class="text-center">Image</th>
										<th class="text-center">Action</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$i = 1;
									$cats = $conn->query("SELECT * FROM category_list order by id asc");
									while ($row = $cats->fetch_assoc()) :
									?>
										<tr>
											<td class="text-center"><?php echo $i++ ?></td>
											<td class="text-center">
												<?php echo $row['name'] ?>
											</td>
											<td class="text-center">
												<img src="<?php echo isset($row['img_path']) ? '../assets/img/category/' . $row['img_path'] : '' ?>" alt="" id="cimg">
											</td>
											
											<td class="text-center">
												<button class="btn btn-sm btn-primary edit_cat" type="button" data-id="<?php echo $row['id'] ?>" data-name="<?php echo $row['name'] ?>" data-status="<?php echo $row['status'] ?>" data-img_path="<?php echo $row['img_path'] ?>">Edit</button>
												<button class="btn btn-sm btn-danger delete_cat" type="button" data-id="<?php echo $row['id'] ?>">Delete</button>
											</td>
										</tr>
									<?php endwhile; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<!-- Table Panel -->
		</div>
	</div>

</div>
<style>
	img#cimg,
	.cimg {
		max-height: 10vh;
		max-width: 6vw;
	}

	td {
		vertical-align: middle !important;
	}

	td p {
		margin: unset !important;
	}

	.custom-switch,
	.custom-control-input,
	.custom-control-label {
		cursor: pointer;
	}

	b.truncate {
		overflow: hidden;
		text-overflow: ellipsis;
		display: -webkit-box;
		-webkit-line-clamp: 3;
		-webkit-box-orient: vertical;
		font-size: small;
		color: #000000cf;
		font-style: italic;
	}
</style>
<script>
	function displayImg(input, _this) {
		if (input.files && input.files[0]) {
			var reader = new FileReader();
			reader.onload = function(e) {
				$('#cimg').attr('src', e.target.result);
			}

			reader.readAsDataURL(input.files[0]);
		}
	}
	$('#manage-category').submit(function(e) {
		e.preventDefault()
		start_load()
		$.ajax({
			url: 'ajax.php?action=save_category',
			data: new FormData($(this)[0]),
			cache: false,
			contentType: false,
			processData: false,
			method: 'POST',
			type: 'POST',
			success: function(resp) {
				if (resp == 1) {
					alert_toast("Data successfully added", 'success')
					setTimeout(function() {
						location.reload()
					}, 1500)

				} else if (resp == 2) {
					alert_toast("Data successfully updated", 'success')
					setTimeout(function() {
						location.reload()
					}, 1500)

				}
			}
		})
	})
	$('.edit_cat').click(function() {
		start_load()
		var cat = $('#manage-category')
		cat.get(0).reset()
		cat.find("[name='id']").val($(this).attr('data-id'))
		cat.find("[name='name']").val($(this).attr('data-name'))
		if ($(this).attr('data-status') == 1)
			$('#availability').prop('checked', true)
		else
			$('#availability').prop('checked', false)

		cat.find("#cimg").attr('src', '../assets/img/category' + $(this).attr('data-img_path'))
		end_load()
	})
	$('.delete_cat').click(function() {
		_conf("Are you sure to delete this category?", "delete_cat", [$(this).attr('data-id')])
	})

	function delete_cat($id) {
		start_load()
		$.ajax({
			url: 'ajax.php?action=delete_category',
			method: 'POST',
			data: {
				id: $id
			},
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


	function toggleMenuForm() {
		const menuForm = document.getElementById('menu-form');
		const availabilityCheckbox = document.getElementById('availability');

		if (availabilityCheckbox.checked) {
			// If 'Available' checkbox is checked, show the menu form
			menuForm.style.display = 'block';
		} else {
			// If 'Available' checkbox is not checked, hide the menu form
			menuForm.style.display = 'none';
		}

	}


	$('table').dataTable()
</script>