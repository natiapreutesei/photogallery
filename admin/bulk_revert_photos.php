<?php
require_once("includes/init.php");
global $session;
/**
 * This script is designed to handle the bulk action of reverting the soft deletion of selected photos.
 * It is typically accessed through a form submission from an administrative interface where multiple
 * photos can be selected for restoration. The script checks for user authentication, validates the form
 * submission, and performs the reverting action on each selected photo.
 *
 * Explanation:
 * - The script begins by ensuring that the user is signed in, redirecting to the login page if not.
 *   This is a security measure to prevent unauthorized access to the functionality.
 * - It checks if the form has been submitted with the specific action 'bulk_revert' and if any photos
 *   have been selected. If not, it redirects back to the photos management page.
 * - For each selected photo, identified by its ID, the script fetches the corresponding Photo object
 *   from the database. If the photo exists, it calls a static method to undo its soft deletion.
 * - Upon successful completion of the action for all selected photos, a session message is set for
 *   feedback, and the user is redirected back to the photos management page.
 *
 * Process:
 * 1. Check user authentication and redirect if not authenticated.
 * 2. Validate form submission, checking for the 'bulk_revert' action and the selection of photos.
 * 3. Iterate over each selected photo, perform a lookup by ID, and revert its soft deletion if found.
 * 4. Set a feedback message and redirect back to the photos management page.
 *
 * Importance:
 * - Enables bulk operations on photos, improving the efficiency of managing large photo libraries.
 * - Provides a mechanism for recovering photos that have been mistakenly soft-deleted, enhancing data management flexibility.
 * - Encapsulates the functionality in a single script, keeping the bulk reverting logic centralized and maintainable.
 *
 * Usage:
 * This script is intended to be triggered by submitting a form from the photos management interface in an administrative dashboard.
 *
 * Note:
 * - The script assumes that the user is interacting with a web interface that supports session management and form submission.
 * - Proper validation and sanitization of the submitted data are crucial to prevent security vulnerabilities, such as SQL injection.
 */
if(!$session->is_signed_in()){
    header("Location: login.php");
    exit;
}

if(isset($_POST['bulk_revert']) && isset($_POST['selected_photos'])) {
    $selected_photos = $_POST['selected_photos'];
    foreach($selected_photos as $photo_id) {
        $photo = Photo::find_by_id($photo_id);
        if($photo) {
            Photo::undo_soft_delete($photo_id);
        }
    }
    $session->message("Selected photos have been reverted successfully.");
    header("Location: photos.php");
    exit;
} else {
    header("Location: photos.php");
    exit;
}
