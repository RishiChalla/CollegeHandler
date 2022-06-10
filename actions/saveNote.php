<?php
// This file is a REST API

// Include Config files
$root = "../";
require $root."config/config.php";
require $root."config/REST_UserInfo.php";

// Store the JSON
$output = ["success" => true];

$notesId = mysqli_real_escape_string($db, $_POST["notesId"]);
$notes = mysqli_real_escape_string($db, $_POST["notes"]);
$preview = mysqli_real_escape_string($db, $_POST["preview"] == "true" ? "1" : "0");

setData("UPDATE `notes` SET notes='$notes', preview='$preview' WHERE id=$notesId AND userId='".$user['id']."'");

// Output the JSON
echo json_encode($output);

?>