<?php
// Gain access to the session and initialize the application environment.
global $session;
require_once("includes/init.php");

// Check if the form submission contains selected photos.
if (!empty($_POST['selected_photos'])) {
    // Loop through each photo ID received from the form submission.
    foreach ($_POST['selected_photos'] as $photo_id) {
        // Attempt to find the photo by its ID in the database.
        $photo = Photo::find_by_id($photo_id);
        if ($photo) {
            // If the photo exists, perform a soft delete operation on it.
            // The soft_delete method marks the photo as deleted in the database,
            // typically by setting a 'deleted_at' timestamp, without actually removing the record.
            $photo->soft_delete();
        }
    }
    // After processing all selected photos, set a session message indicating success.
    // This message can be displayed to the user as feedback.
    $session->message("Selected photos have been successfully deleted.");
}

// Redirect the user back to the photos management page.
// This is a common post-operation action to prevent form resubmission on page refresh
// and to show the updated state of the photos list to the user.
header("Location: photos.php");
exit;
?>
