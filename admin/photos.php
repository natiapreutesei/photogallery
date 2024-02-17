<?php
global $session;
include("includes/header.php");
include("includes/sidebar.php");
?>

<?php
/*include("includes/content-top.php");*/

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
								<form action="edit_multiple_photos.php" method="post">
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
												<!-- Add Checkbox for each photo -->
												<div class="position-absolute" style="top: 5px; right: 65px;">
													<input class="form-check-input" type="checkbox" value="<?php echo $photo->id; ?>" id="photo-<?php echo $photo->id; ?>" name="selected_photos[]">
													<label class="form-check-label" for="photo-<?php echo $photo->id; ?>"></label>
												</div>
											</div>
                                        <?php endforeach; ?>
									</div>
									<div class="d-flex mt-5">
											<button type="submit" name="edit_selected" class="btn btn-primary">Edit Selected</button>
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