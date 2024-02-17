<?php
require_once("includes/header.php");
if(!$session->is_signed_in()){
    header("Location:login.php");
}
if(empty($_GET['id'])){
    header("Location:users.php");
}
$user = User::find_by_id($_GET['id']);
if($user){
    /*verwijderen uit de database*/
    $user->soft_delete();
    /*de image te verwijderen uit de users folder*/
    $user->delete_user_image();
    header("Location:users.php");
}else{
    header("Location:users.php");
}
require_once("includes/sidebar.php");
require_once("includes/content-top.php");

?>

<h1>delete page</h1>

<?php
require_once("includes/footer.php");
?>