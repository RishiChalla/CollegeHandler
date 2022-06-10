<?php
// This file is a REST API

// Include Config files
$root = "../";
require $root."config/config.php";
require $root."config/REST_UserInfo.php";

// Store the JSON
$output = ["success" => true];

setData("UPDATE `users` SET darkMode = !darkMode WHERE id=".$user["id"]);

// Output the JSON
echo json_encode($output);

?>