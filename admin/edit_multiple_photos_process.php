<?php
global $session;
require_once("includes/init.php");

// Check if the form was submitted
if (isset($_POST['update_multiple_photos'])) {
    // Arrays to keep track of updates
    $update_successes = [];
    $update_failures = [];

    // Loop through the submitted photo IDs
    foreach ($_POST['photo_ids'] as $index => $photo_id) {
        // Fetch the photo by ID
        $photo = Photo::find_by_id($photo_id);
        if ($photo) {
            // Update the photo's properties with checks
            $photo->title = $_POST['titles'][$photo_id] ?? $photo->title;
            $photo->description = $_POST['descriptions'][$photo_id] ?? $photo->description;
            $photo->alternate_text = $_POST['alternate_texts'][$photo_id] ?? $photo->alternate_text;

            // Handle tags and categories if provided
            if (isset($_POST['tags'][$photo_id])) {
                $photo->syncTags($_POST['tags'][$photo_id]);
            }
            if (isset($_POST['categories'][$photo_id])) {
                $photo->syncCategories($_POST['categories'][$photo_id]);
            }

            // Handle file upload if a new file was provided
            if (!empty($_FILES['files']['name'][$photo_id])) {
                $fileArray = [
                    'name' => $_FILES['files']['name'][$photo_id],
                    'type' => $_FILES['files']['type'][$photo_id],
                    'tmp_name' => $_FILES['files']['tmp_name'][$photo_id],
                    'error' => $_FILES['files']['error'][$photo_id],
                    'size' => $_FILES['files']['size'][$photo_id]
                ];
                $photo->set_file($fileArray);
            }

            // Attempt to save the updated photo
            if ($photo->save_all()) {
                $update_successes[] = $photo_id;
            } else {
                $update_failures[] = $photo_id;
            }
        } else {
            $update_failures[] = $photo_id;
        }
    }

    // Redirect or inform the user based on the update results
    if (count($update_failures) > 0) {
        // Redirect back with error message if any updates failed
        $session->message("Some photos failed to update.");
    } else {
        // Redirect back with success message if all updates succeeded
        $session->message("All selected photos were updated successfully.");
    }
    header("Location: photos.php");
    exit;
} else {
    // Redirect back if the script is accessed without submitting the form
    header("Location: photos.php");
    exit;
}

