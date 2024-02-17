<?php
require_once 'includes/init.php'; // Adjust this path to your initialization script

if(isset($_POST['id'])) {
    $userId = $_POST['id'];

    $user = User::find_by_id($userId);
    if($user) {
        // Reactivate the user by setting 'deleted_at' to NULL or another appropriate value
        $user->deleted_at = '0000-00-00 00:00:00'; // Or NULL, adjust based on your database design

        if($user->save()) {
            echo "User reactivated successfully.";
        } else {
            echo "Failed to reactivate user.";
        }
    } else {
        echo "User not found.";
    }
}
?>
