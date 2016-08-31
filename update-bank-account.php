<?php

session_start();
$uid = $_SESSION['user_id'];
$cashier_branch = $_SESSION['branch'];
$cashier_fullname = $_SESSION['fullname'];

require 'config.php';
require 'globalf.php';

$req = "";
$START_T = "START TRANSACTION;";
$END_T = "COMMIT;";

$ba_name = (isset($_POST['ba-name'])) ? $_POST['ba-name'] : "";
$ba_desc = (isset($_POST['ba-desc'])) ? $_POST['ba-desc'] : "";
$ba_amt = (isset($_POST['ba-amount'])) ? $_POST['ba-amount'] : 0;

$sql = "ALTER TABLE bank_accounts AUTO_INCREMENT = 1;";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "INSERT INTO `bank_accounts`(`bank_account_name`, `bank_account_desc`, `bank_account_amount`) VALUES ('$ba_name','$ba_desc',$ba_amt)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

?>