<?php

// Ensure the user is logged in
if (!isset($_SESSION["userId"]) || empty($_SESSION["userId"])) {
	header("Location: {$WEBSITE_URL}login.php");
	exit();
}

$user = getData("SELECT * FROM `users` WHERE id=".mysqli_real_escape_string($db, $_SESSION['userId']));

if (count($user) != 1) {
	session_destroy();
	header("Location: {$WEBSITE_URL}login.php");
	exit();
}

$user = $user[0];

?>