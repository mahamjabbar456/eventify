<?php

$con = new mysqli('localhost','root','','eventplannersystem');

if(!$con){
    die(mysqli_error($con));
}

?>