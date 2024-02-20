<?php
/**
 * This script provides an interface for editing multiple photos simultaneously. It is part of a photo management system
 * within a content management system (CMS), allowing for batch updates to photos' metadata, such as titles, descriptions,
 * tags, and categories. The script handles form submission from a page where users select photos to edit and presents
 * a form for editing metadata for each selected photo.
 *
 * Process Flow:
 * 1. Checks if the 'edit_selected' POST request is not empty, indicating that the user has submitted a list of photo IDs to edit.
 * 2. Retrieves the selected photo IDs from the POST request and stores them in an array.
 * 3. For each photo ID, it attempts to find the corresponding photo object using the Photo::find_by_id method. If found,
 *    the photo object is added to an array of photos to edit.
 * 4. If no photos are found (e.g., the user did not select any photos or the selected IDs are invalid), the script redirects
 *    the user back to the photos management page.
 * 5. If there are photos to edit, the script generates a form for each photo, pre-filling the form fields with the current
 *    metadata (title, description, tags, categories) of the photo. It also allows for the upload of a new image file for each photo.
 * 6. Upon submission of the form, the data is sent to another script (edit_multiple_photos_process.php) for processing and updating
 *    the photo records in the database.
 *
 * Key Features:
 * - Batch editing capability: This script is a crucial part of the CMS's functionality, allowing users to efficiently manage
 *   multiple photos at once, saving time and effort.
 * - Dynamic form generation: Based on the selected photos, the script dynamically generates a form for each photo, streamlining
 *   the user interface and improving the user experience.
 * - Flexibility in editing: Users can update titles, descriptions, tags, categories, and even replace the photo file itself,
 *   providing comprehensive control over the photo metadata.
 *
 * Security Considerations:
 * - The script checks for a valid user session at the start. If the user is not signed in, it redirects to the login page,
 *   ensuring that only authenticated users can edit photos.
 * - Input validation and sanitization should be handled in the processing script (edit_multiple_photos_process.php) to protect
 *   against SQL injection and other malicious inputs.
 *
 * Usage:
 * This script is intended to be used within an administrative interface of a CMS where users have permissions to manage photo
 * content. It is accessed when a user selects multiple photos for editing from the photo management interface and submits the
 * selection for editing.
 */


include("includes/header.php");
include("includes/sidebar.php");

if (!empty($_POST['edit_selected'])) {
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
