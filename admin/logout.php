<?php
    require_once("includes/header.php");
    $session->logout();
    header("Location:login.php");
?>