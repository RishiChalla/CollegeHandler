<?php

// Ensure the user is logged in
$loginOutput = json_encode(["error" => "Invalid API Key!"]);

if (!isset($_POST["apiKey"]) || empty($_POST["apiKey"])) {
	die($loginOutput);
}

$user = getData("SELECT * FROM `users` WHERE apiKey='".mysqli_real_escape_string($db, $_POST['apiKey'])."'");

if (count($user) != 1) {
	die($loginOutput);
}

$user = $user[0];

?>