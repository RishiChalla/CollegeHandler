<?php

session_start();

require "vars.php";
require "db.php";
require "helpers.php";

if (!isset($root)) $root = "";

require $root."classes/header.php";
require $root."classes/formInput.php";
require $root."classes/form.php";

?>