<?php
// Define global constants for database connection parameters.
// These constants are used throughout the application to establish connections to the MySQL database.
// DB_HOST: Specifies the hostname of the database server, typically 'localhost' for a server running on the same machine as the web server.
// DB_USER: The username used to connect to the database. 'root' is commonly used for local development environments.
// DB_PASS: The password associated with the database user. In local environments, it might be left empty for simplicity.
// DB_NAME: The name of the database that the application will interact with.
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dbblogoop');

// Define constants related to Google OAuth 2.0 authentication.
// These constants are part of the configuration needed to integrate Google Sign-In into the application.
// GOOGLE_CLIENT_ID: The client ID provided by Google when registering the application in the Google Developer Console.
// GOOGLE_CLIENT_SECRET: A secret key provided by Google to authenticate the application's credentials during the OAuth flow.
// GOOGLE_REDIRECT_URI: The URL to which Google will redirect users after they authenticate with Google and grant permissions to the application.
// This URI must match one of the authorized redirect URIs configured in the Google Developer Console.
define('GOOGLE_CLIENT_ID', '80319367448-272679625dqh8edntn0mkgncgcvbdni4.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/blogoopklas2024/admin/google_auth.php');
