<?php

session_start();
$uid = $_SESSION['user_id'];

require 'config.php';

$req = "";
$START_T = "START TRANSACTION;";
$END_T = "COMMIT;";

if (isset($_GET["p"])) $req = $_GET["p"];

$str_response = "";
$json = "";
$jpage = "";

switch ($req) {

case "update":
$npassword = $_POST['pnpassword'];
$_SESSION['password'] = $npassword;

$sql = "update users set password = '$npassword' where user_id = $uid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Settings uccessfully updated.";

echo $str_response;
break;

}

?>