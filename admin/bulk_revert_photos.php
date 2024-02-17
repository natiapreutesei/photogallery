<?php
global $session;
require_once("includes/init.php");

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
