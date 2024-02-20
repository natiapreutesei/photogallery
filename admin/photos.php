<?php
/**
 * This script serves as the main interface for managing photos within a content management system. It allows users
 * to view, edit, delete, and recover (revert soft deletes) photos in bulk or individually. The interface categorizes
 * photos into active and deleted (soft deleted) groups, providing a user-friendly way to manage all photo content.
 *
 * Key Features:
 * - Displays all active photos with options to edit or delete each photo.
 * - Offers a bulk action feature to edit or delete multiple photos simultaneously.
 * - Lists soft-deleted photos with options to revert the deletion or permanently delete.
 * - Uses tabs to separate active and deleted photos for better organization and accessibility.
 *
 * Security and Validation:
 * - Assumes that access control checks are performed to ensure only authorized users can manage photos.
 * - Employs session messages to provide feedback on the outcomes of photo management actions.
 * - Protects against Cross-Site Request Forgery (CSRF) by submitting actions through POST requests.
 *
 * Workflow:
 * 1. Fetches all active and soft-deleted photos from the database using `Photo::find_all` and `Photo::find_all_soft_deletes`.
 * 2. Displays active photos with checkboxes for bulk actions, and individual edit and delete buttons.
 * 3. Provides a tab for soft-deleted photos, allowing users to select multiple photos for recovery or permanent deletion.
 * 4. Utilizes JavaScript to handle the submission of bulk actions based on the selected photos and the desired action (edit, delete, revert).
 *
 * Implementation Details:
 * - The interface dynamically generates the content for each photo, including a thumbnail, title, and action buttons.
 * - Forms are used to encapsulate photo selections and actions, enabling bulk processing through a single submission.
 * - JavaScript enhances the user experience by allowing immediate action selection without additional navigation.
 *
 * Usage Considerations:
 * - This script is part of the administrative backend of the CMS, where photo management is a critical function.
 * - It is designed to facilitate efficient management of a potentially large number of photos.
 *
 */


global $session;
include("includes/header.php");
include("includes/sidebar.php");
?>

<?php
$photos = Photo::find_all();
$photosoftdeletes = Photo::find_all_soft_deletes();

?>
	<div id="main">
		<div class="row">
			<div class="col-12">
				<div class="d-flex justify-content-between">
					<h1 class="page-header">All Photos</h1>
				</div>

				<hr>
				<?php
                if($message = $session->message()) {
                    echo "<div class='alert alert-success' role='alert'>{$message}</div>";
                }

                ?>
				<div class="tab-content" id="myTabContent">
					<div class="tab-pane fade show active" id="weergave1-tab-pane" role="tabpanel"
					     aria-labelledby="weergave1-tab" tabindex="0">
						<ul class="nav nav-tabs" id="myTab" role="tablist">
							<li class="nav-item" role="presentation">
								<button class="nav-link active" id="photos-tab" data-bs-toggle="tab"
								        data-bs-target="#photos-tab-pane" type="button" role="tab"
								        aria-controls="photos-tab-pane" aria-selected="true">Photos
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link" id="deleted-tab" data-bs-toggle="tab"
								        data-bs-target="#deleted-tab-pane" type="button" role="tab"
								        aria-controls="deleted-tab-pane" aria-selected="false">Deleted
								</button>
							</li>
						</ul>
						<div class="tab-content" id="myTabContent">
							<div class="tab-pane fade show active" id="photos-tab-pane" role="tabpanel"
							     aria-labelledby="photos-tab" tabindex="0">
								<form id="photosForm" action="edit_multiple_photos.php" method="post">
									<input type="hidden" name="action" id="formAction" value="edit">
									<input type="hidden" name="edit_selected" id="editAction" value="">

									<div class="mt-5 d-flex flex-wrap">
                                        <?php foreach ($photos as $photo): ?>
											<div class="m-1 position-relative">
												<img class="img-fluid img-thumbnail" width="300" src="<?php echo $photo->picture_path() ?>" alt="<?php echo $photo->title ?>">
												<a href="delete_photo.php?id=<?php echo $photo->id; ?>">
													<i class="bi bi-trash-fill position-absolute" style="top: 5px; right: 10px; cursor: pointer; color:red;"></i>
												</a>
												<a href="edit_photo.php?id=<?php echo $photo->id; ?>">
													<i class="bi bi-pencil-square position-absolute" style="top: 5px; right: 40px; cursor: pointer; color:yellow;"></i>
												</a>
												<div class="position-absolute" style="top: 5px; right: 65px;">
													<input class="form-check-input" type="checkbox" value="<?php echo $photo->id; ?>" id="photo-<?php echo $photo->id; ?>" name="selected_photos[]">
													<label class="form-check-label" for="photo-<?php echo $photo->id; ?>"></label>
												</div>
											</div>
                                        <?php endforeach; ?>
									</div>
									<div class="d-flex mt-5">
										<button type="button" id="editSelectedButton" class="btn btn-primary me-3">Edit Selected</button>
										<button type="button" id="deleteSelectedButton" class="btn btn-danger">Delete Selected</button>
									</div>
								</form>
							</div>
							<div class="tab-pane fade" id="deleted-tab-pane" role="tabpanel"
							     aria-labelledby="deleted-tab" tabindex="0">
								<form action="bulk_revert_photos.php" method="post">
									<div class="mt-5 d-flex flex-wrap">
                                        <?php foreach ($photosoftdeletes as $photosoftdelete): ?>
											<div class="m-1 position-relative">
												<img class="img-fluid img-thumbnail" width="300" src="<?php echo $photosoftdelete->picture_path() ?>" alt="<?php echo $photosoftdelete->title ?>">
												<!-- Add Checkbox for each photo -->
												<div class="position-absolute" style="top: 5px; right: 35px;">
													<input class="form-check-input" type="checkbox" name="selected_photos[]" value="<?php echo $photosoftdelete->id; ?>" id="delete-<?php echo $photosoftdelete->id; ?>">
													<label class="form-check-label" for="delete-<?php echo $photosoftdelete->id; ?>"></label>
												</div>
												<a href="revert_photo.php?id=<?php echo $photosoftdelete->id; ?>">
													<i class="bi bi-arrow-counterclockwise position-absolute" style="top: 5px; right: 10px; cursor: pointer; color:green;"></i>
												</a>
											</div>
                                        <?php endforeach; ?>
									</div>
									<div class="mt-3">
										<button type="submit" name="bulk_revert" class="btn btn-warning">Revert Selected</button>
									</div>
								</form>

							</div>

						</div>
					</div>
				</div>
			</div>
		</div>
<?php
include("includes/footer.php");
?>

		<script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('editSelectedButton').addEventListener('click', function() {
                    // Set the hidden input value to indicate the edit action
                    document.getElementById('editAction').value = 'true';
                    document.getElementById('photosForm').action = 'edit_multiple_photos.php';
                    document.getElementById('photosForm').submit(); // Navigate to edit_multiple_photos.php
                });

                document.getElementById('deleteSelectedButton').addEventListener('click', function() {
                    // No need to set editAction value for deletion
                    document.getElementById('formAction').value = 'delete';
                    document.getElementById('photosForm').action = 'bulk_delete_photos.php';
                    document.getElementById('photosForm').submit(); // Proceed with deletion
                });
            });
		</script>




