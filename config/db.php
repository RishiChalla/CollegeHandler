<?php

global $db;
$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

if (mysqli_connect_errno()) {
    foreach ($ADMIN_EMAILS as $email) {
        mail(
            $email,
            "Error In Your Website (College Handler)",
            "There was an error in your website (".$WEBSITE_URL.") while connecting to mysql. Here are the details:
            Username: ".$DB_USER."
            Database: ".$DB_NAME."
            Connecting IP (REMOTE_ADDR): ".$_SERVER['REMOTE_ADDR']."
            Connecting IP (HTTP_X_FORWARDED_FOR): ".$_SERVER['HTTP_X_FORWARDED_FOR']."
            
            Mysqli Connect Error report:
            
            ".mysqli_connect_error()
        );
    }
    die ("We were unable to connect to our servers! Please try again later.");
}

?>