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

$baid = (isset($_GET['eid'])) ? $_GET['eid'] : 0;
$ba_name = (isset($_GET['iba-name'])) ? $_GET['iba-name'] : "";
$ba_desc = (isset($_GET['iba-desc'])) ? $_GET['iba-desc'] : "";
$ba_amt = (isset($_GET['iba-amount'])) ? $_GET['iba-amount'] : 0;

$sql = "UPDATE `bank_accounts` SET `bank_account_name`='$ba_name',`bank_account_desc`='$ba_desc',`bank_account_amount`=$ba_amt WHERE `bank_account_id` = $baid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

echo "test";

?>