<?php
global $session;
require_once("includes/init.php");

if($session->is_signed_in()) {
    if(isset($_GET['id'])) {
        $photo_id = $_GET['id'];
        if(Photo::undo_soft_delete($photo_id)) {
            // If the revert operation was successful, redirect back to the photos page
            header("Location: photos.php");
        } else {
            // If the operation failed, redirect with an error message (or handle as needed)
            header("Location: photos.php?error=could_not_revert");
        }
    } else {
        // Redirect if no ID is provided in the URL
        header("Location: photos.php");
    }
} else {
    // Redirect to login page if not signed in
    header("Location: login.php");
}
