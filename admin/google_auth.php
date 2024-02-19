<?php
require_once 'includes/init.php';
require_once 'includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/blogoopklas2024/vendor/autoload.php';

session_start(); // Ensure session start is called to use $_SESSION

$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope("email");
$client->addScope("profile");

// Disable SSL Verification for local development
$client->setHttpClient(new GuzzleHttp\Client(['verify' => false]));

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    // Get user profile information from Google
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    $email = $google_account_info->email;
    $name = $google_account_info->name;
    $picture = $google_account_info->picture; // URL of the user's profile picture.

    // Split the name into first name and last name
    $nameParts = explode(' ', $name, 2);
    $firstName = $nameParts[0];
    $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

    // Check if a user exists with the given email
    $existing_user = User::find_by_email($email);

    if ($existing_user) {
        // User exists, update information
        $existing_user->user_image = $picture; // Update the user's image.
        $existing_user->first_name = $firstName; // Update first name
        $existing_user->last_name = $lastName; // Update last name
        $existing_user->save();
        // Log the user in
        $_SESSION['user_id'] = $existing_user->id;
    } else {
        // User does not exist, create a new one
        $new_user = new User();
        $new_user->username = $email; // Or generate a username based on the user's name or email.
        $new_user->email = $email;
        $new_user->first_name = $firstName; // Set first name
        $new_user->last_name = $lastName; // Set last name
        $new_user->user_image = $picture; // Handle image as needed.
        $new_user->password = ''; // Consider how you want to handle passwords. Maybe users logging in with Google don't need one.
        if ($new_user->save()) {
            // Log the new user in
            $_SESSION['user_id'] = $new_user->id;
        }
    }

    // Redirect to index page or dashboard
    header('Location: index.php');
    exit();
} else {
    // If we don't have an authorization code then get one
    $authUrl = $client->createAuthUrl();
    header('Location: ' . $authUrl);
    exit();
}
