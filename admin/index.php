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
?>





