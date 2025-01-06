<?php

$server = "localhost";
$user = "root";
$password = "";
$database_name = "prestogrubs";

$conn = mysqli_connect($server, $user, $password, $database_name);

if (!$conn) {
    die("Connection Failed:" . mysqli_connect_error());
}

?>
