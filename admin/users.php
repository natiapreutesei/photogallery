<?php
include("includes/header.php");
if(!$session->is_signed_in()){
    header("location:login.php");
}
include("includes/sidebar.php");

?>

	<div id="main">
	<div class="row">
	<div class="col-12">
	<div class="d-flex align-items-center justify-content-between">
		<h1 class="page-header">All Users</h1>
		<a href="add_user.php" class="btn btn-primary">
			<i class="bi bi-person-add"> User</i>
		</a>
	</div>

	<div class="page-heading">
		<section class="section">
			<div class="card">
				<div class="card-body">
					<table class="table table-hover table-striped table-borderless mb-0" id="table1">
						<thead>
						<tr>
							<th>ID</th>
							<th>USERNAME</th>
							<th>FIRST_NAME</th>
							<th>LAST_NAME</th>
							<th>DELETED AT</th>
							<th>ACTION</th>
						</tr>
						</thead>
						<tbody>
                        <?php
                        $users = User::find_all_users();
                        ?>
                        <?php foreach ($users as $user): ?>
							<tr>
								<td class="text-bold-500"><?php echo $user->id; ?></td>
								<td>
									<div class="d-flex align-items-center">
										<div class="avatar">
											<img src="<?php echo $user->picture_path_and_placeholder(); ?>"
											     alt="avatar img holder">
										</div>
										<div class="mt-1 ms-2">
                                            <?php echo $user->username; ?>
										</div>
									</div>
								</td>

								<td><?php echo $user->first_name; ?></td>
								<td><?php echo $user->last_name; ?></td>
								<td><?php echo $user->deleted_at; ?></td>
								<td>
									<div class="d-flex">
										<a href="edit_user.php?id=<?php echo $user->id; ?>">
											<i class="bi bi-pencil-square text-warning"></i>
										</a>
										<a href="delete_user.php?id=<?php echo $user->id; ?>">
											<i class="bi bi-trash text-danger"></i>
										</a>
										<!-- Voeg de checkbox toe met een unieke ID -->

										<div class="form-check form-switch">
											<input class="form-check-input" type="checkbox"
											       id="deleteSwitch<?php echo $user->id; ?>"
                                                <?php echo ($user->deleted_at == '0000-00-00 00:00:00') ? 'checked' : ''; ?>>
										</div>
									</div>
								</td>
							</tr>
                        <?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>

	<!--This is the toast container that will show the confirmation message when the user is set to active again-->
	<div class="toast-container position-fixed bottom-0 end-0 p-3">
		<div id="customToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" style="background-color: #004085;">
			<div class="toast-header">
				<strong class="me-auto" style="color: #fff;">Active!</strong>
				<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
			</div>
			<div class="toast-body" style="color: #fff;">
				The user has been set to active again!
			</div>
		</div>
	</div>

<?php
include("includes/footer.php");
?>