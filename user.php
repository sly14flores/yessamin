<?php

$uname	= (isset($_POST["username"])) ? $_POST["username"] : "";
$upass	= (isset($_POST["password"])) ? $_POST["password"] : "";

require 'config.php';

db_connect();
$sql	= "select user_id, username, password, concat(firstname, ' ', lastname) user_fullname, grants, user_branch, is_built_in from users where username='$uname' and password='$upass' and is_deleted = 0";
$rs = $db_con->query($sql);
$rc = $rs->num_rows;

if ($rc>0) {
	session_start();
	$row = $rs->fetch_array();
	$_SESSION['user_id'] = $row['user_id'];
	$_SESSION['grants'] = $row['grants'];
	$_SESSION['fullname'] = $row['user_fullname'];
	$_SESSION['password'] = $row['password'];
	$_SESSION['branch'] = $row['user_branch'];
	header("location: index.php");
} else {
	header("location: login.php?m=1");
}

db_close();

?>