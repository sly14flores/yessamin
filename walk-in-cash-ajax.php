<?php

session_start();
$uid = $_SESSION['user_id'];

require 'config.php';
require 'globalf.php';

$req = "";
$START_T = "START TRANSACTION;";
$END_T = "COMMIT;";

if (isset($_GET["p"])) $req = $_GET["p"];

$str_response = "";
$json = "";
$jpage = "";

switch ($req) {

case "discount":
$str_response = 0;
$ppass = (isset($_POST['ppass'])) ? $_POST['ppass'] : "";
$sql = "select employee_id from users where grants = 100 and is_deleted = 0 and password = '$ppass'";

db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
	$str_response = 1;
}
db_close();

$sql = "select employee_id from users where grants = 50 and is_deleted = 0 and password = '$ppass'";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
	$str_response = 1;
}
db_close();

echo $str_response;
break;

}

?>