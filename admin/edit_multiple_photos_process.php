<?php
/**
 * This script processes the batch update of multiple photos within a content management system (CMS). It updates each selected
 * photo's metadata, including title, description, alternate text, tags, categories, and optionally, the photo file itself. This
 * script is triggered after the user submits the form for editing multiple photos.
 *
 * Workflow:
 * 1. Checks if the 'update_multiple_photos' form was submitted.
 * 2. Initializes arrays to track which photo updates succeeded and which failed.
 * 3. Loops through each submitted photo ID, performing the following actions for each:
 *    a. Fetches the corresponding photo object from the database.
 *    b. Updates the photo's metadata with the submitted values, falling back to existing values if no update is provided.
 *    c. Synchronizes tags and categories based on the submitted information.
 *    d. Handles file upload if a new photo file is submitted, updating the photo's file property.
 *    e. Attempts to save all changes to the photo, including metadata and file changes.
 *    f. Tracks the success or failure of each update attempt.
 * 4. Provides feedback to the user by redirecting back to the photo management page with a message indicating the outcome of the update operations.
 *
 * Key Features:
 * - Batch processing: Allows for the efficient management of multiple photos at once, reducing repetitive tasks.
 * - Comprehensive updates: Enables updates to a wide range of photo metadata and the photo file itself.
 * - User feedback: Informs the user of the outcome of the batch update process through session messages.
 *
 * Security and Validation:
 * - Access control: Assumes a check for user authentication and authorization prior to executing the script.
 * - Data validation and sanitization: Should be performed on all inputs to prevent SQL injection, XSS, and other security vulnerabilities.
 * - Error handling: Properly handles the scenario where a photo ID does not correspond to an existing photo.
 *
 * Usage Scenario:
 * This script is intended for use in the administrative backend of a CMS, where users with appropriate permissions can manage photo content.
 * It is part of a larger workflow that includes selecting photos for batch editing, editing metadata in a form, and submitting changes.
 *
 * Note:
 * - It is crucial that the accompanying form includes appropriate fields for titles, descriptions, alternate texts, tags, categories, and files.
 * - The script assumes the existence of methods like `Photo::find_by_id`, `syncTags`, `syncCategories`, and `set_file` for handling photo objects.
 */


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

