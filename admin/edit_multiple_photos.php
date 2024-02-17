<?php
include("includes/header.php");
include("includes/sidebar.php");

if (isset($_POST['edit_selected'])) {
    $selected_photos = $_POST['selected_photos'] ?? [];
    $photos_to_edit = [];
    foreach ($selected_photos as $photo_id) {
        $photo = Photo::find_by_id($photo_id);
        if ($photo) {
            $photos_to_edit[] = $photo;
        }
    }
    if(empty($photos_to_edit)) {
        header("Location: photos.php");
        exit;
    }
} else {
    header("Location: photos.php");
    exit;
}
?>

<div id="main">
	<div class="row">
		<div class="col-12">
			<h1 class="page-header">Edit Multiple Photos</h1>
			<form action="edit_multiple_photos_process.php" method="POST" enctype="multipart/form-data" class="form form-horizontal">
                <?php foreach ($photos_to_edit as $photo): ?>
                    <?php
                    // Fetch tags and categories for the photo
                    $photo_tags = Tag::find_tags_by_photo_id($photo->id);
                    $photo_categories = Category::find_categories_by_photo_id($photo->id);

                    // Convert arrays to comma-separated strings
                    $tags_string = implode(', ', $photo_tags);
                    $categories_string = implode(', ', $photo_categories);
                    ?>
					<div class="card mb-3">
						<div class="card-header">
							<h2 class="card-title">Editing Photo: <?php echo htmlspecialchars($photo->title); ?> (ID: <?php echo $photo->id; ?>)</h2>
						</div>
						<div class="card-body">
							<div class="mb-3 text-center">
								<img src="<?php echo $photo->picture_path(); ?>" alt="<?php echo htmlspecialchars($photo->title); ?>" class="img-fluid img-thumbnail" style="max-height: 300px;">
							</div>
							<input type="hidden" name="photo_ids[]" value="<?php echo $photo->id; ?>">
							<div class="mb-3">
								<label for="title-<?php echo $photo->id; ?>" class="form-label">Title</label>
								<input type="text" class="form-control" id="title-<?php echo $photo->id; ?>" name="titles[<?php echo $photo->id; ?>]" value="<?php echo htmlspecialchars($photo->title); ?>">
							</div>
							<div class="mb-3">
								<label for="description-<?php echo $photo->id; ?>" class="form-label">Description</label>
								<textarea class="form-control" id="description-<?php echo $photo->id; ?>" name="descriptions[<?php echo $photo->id; ?>]"><?php echo htmlspecialchars($photo->description); ?></textarea>
							</div>
							<div class="mb-3">
								<label for="alternate_text-<?php echo $photo->id; ?>" class="form-label">Alternate Text</label>
								<input type="text" class="form-control" id="alternate_text-<?php echo $photo->id; ?>" name="alternate_texts[<?php echo $photo->id; ?>]" value="<?php echo htmlspecialchars($photo->alternate_text); ?>">
							</div>
							<div class="mb-3">
								<label for="tags-<?php echo $photo->id; ?>" class="form-label">Tags</label>
								<input type="text" class="form-control" id="tags-<?php echo $photo->id; ?>" name="tags[<?php echo $photo->id; ?>]" value="<?php echo htmlspecialchars($tags_string); ?>">
							</div>
							<div class="mb-3">
								<label for="categories-<?php echo $photo->id; ?>" class="form-label">Categories</label>
								<input type="text" class="form-control" id="categories-<?php echo $photo->id; ?>" name="categories[<?php echo $photo->id; ?>]" value="<?php echo htmlspecialchars($categories_string); ?>">
							</div>
							<div class="mb-3">
								<label for="file-<?php echo $photo->id; ?>" class="form-label">Photo File</label>
								<input type="file" class="form-control" id="file-<?php echo $photo->id; ?>" name="files[<?php echo $photo->id; ?>]">
								<small>Current file: <?php echo htmlspecialchars($photo->filename); ?></small>
							</div>
						</div>
					</div>
                <?php endforeach; ?>
				<div class="text-center">
					<button type="submit" class="btn btn-primary" name="update_multiple_photos">Update Photos</button>
					<a href="photos.php" class="btn btn-secondary">Back to Photos</a>
				</div>
			</form>
		</div>
	</div>
</div>

<?php include("includes/footer.php"); ?>
