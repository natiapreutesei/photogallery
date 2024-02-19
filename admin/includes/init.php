<?php
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
define('SITE_ROOT', DS . 'wamp64'.DS. 'www'.DS.'blogoopklas2024' );
defined('INCLUDES_PATH') ? null : define('INCLUDES_PATH', SITE_ROOT.DS.'admin'.DS.'includes');
defined('IMAGES_PATH')? null : define('IMAGES_PATH', SITE_ROOT.DS.'admin'.DS.'assets'.DS.'images'.DS.'photos');

require_once("config.php");
require_once("Database.php");
require_once("Db_object.php");
require_once("User.php");
require_once("Photo.php");
require_once("Comment.php");
require_once("Session.php");
require_once("Tags.php");
require_once("Category.php");
?>