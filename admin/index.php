<?php
//include: error op de pagina,php genereert een waarschuwing,
//maar: de pagina zal wel verder uitgevoerd worden.
//require: hetzelfde als include: php genereert een fatale fout
//en stop de pagina van uitvoering
//include_once
//require_once
global $session;
include("includes/header.php");
    if(!$session->is_signed_in()){
        header("location:login.php");
    }
    include("includes/sidebar.php");
    include("includes/content-top.php");
    include("includes/content.php");
    include("includes/footer.php");

//Facebook Login implementation
require_once 'includes/config.php';
require_once 'includes/FacebookAuth.php';

//Create FacebookAuth object
$facebookAuth = new FacebookAuth();

//Get access token from Facebook
if (isset($_GET['code'])) {
    try {
        $accessToken = $facebookAuth->getAccessToken($_GET['code']);
        $userDetails = $facebookAuth->getUserDetails($accessToken);

        // Process the user details, e.g., find/create user in your DB and log them in
        // Redirect to a logged-in page or dashboard
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        // Handle errors, e.g., log them and show an error message
        error_log("Facebook login error: " . $e->getMessage());
        // Redirect to login page with error
        header('Location: login.php?error=login_failed');
        exit;
    }
}


?>





