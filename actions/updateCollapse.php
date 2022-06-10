<?php
// This file is a REST API

// Include Config files
$root = "../";
require $root."config/config.php";
require $root."config/REST_UserInfo.php";

// Store the JSON
$output = ["success" => true];

$classId = mysqli_real_escape_string($db, $_POST["classId"]);
$unitNumber = mysqli_real_escape_string($db, $_POST["unit"]);

setData("UPDATE `units` SET collapsed=".($_POST["collapse"] == "true" ? "TRUE" : "FALSE")." WHERE classId=$classId AND unitNumber=$unitNumber AND userId='".$user['id']."'");

// Output the JSON
echo json_encode($output);

?>