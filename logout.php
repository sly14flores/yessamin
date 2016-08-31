<?php
session_start();

unset($_SESSION['user_id']);
unset($_SESSION['grants']);
unset($_SESSION['fullname']);
unset($_SESSION['password']);
unset($_SESSION['branch']);

header("location: index.php");
?>