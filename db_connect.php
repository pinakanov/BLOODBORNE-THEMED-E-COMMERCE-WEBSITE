<?php
 
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "bloodborne";
 
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
 
if(mysqli_connect_errno()) {
    die("The Workshop is currently unavailable: " . mysqli_connect_error());
}
 