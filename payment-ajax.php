<?php

session_start();
$uid = $_SESSION['user_id'];
$rbranch = $_SESSION['branch'];

require 'grants.php';
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

case "cashier_fullname":
$sql = "select concat(firstname, ' ', lastname) cashier from users where user_id = $uid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$str_response = $rec['cashier'];
}
db_close();

echo $str_response;
break;

case "new":
$pdid = (isset($_POST['pdid'])) ? $_POST['pdid'] : 0;
$pclimit = (isset($_POST['pclimit'])) ? $_POST['pclimit'] : 0;
$ppayi = (isset($_POST['ppayi'])) ? $_POST['ppayi'] : "";
$pclt = (isset($_POST['pclt'])) ? $_POST['pclt'] : 0;
$pnr = (isset($_POST['pnr'])) ? $_POST['pnr'] : 0;

$sql = "alter table receipts AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "insert into receipts (receipt_branch, receipt_did, receipt_iscash, receipt_date, receipt_uid) ";
$sql .= "values ($rbranch, $pdid, 0, CURRENT_TIMESTAMP, $uid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$lrno = 0;
$sql = "select receipt_no from receipts where receipt_uid = $uid order by receipt_no desc limit 1";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$lrno = $rec['receipt_no'];
db_close();

$sql = "alter table receipt_payments AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$cutoff = 0;
$sql = "select * from receipt_payments left join receipts on receipt_payments.payment_receipt_no = receipts.receipt_no where receipt_branch = $rbranch and payment_date = date_format(now(),'%Y-%m-%d') and cut_off = 1";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$cutoff = 2;
}
db_close();

$sql = "insert into receipt_payments (payment_receipt_no, payment_transaction_no, payment_amount, payment_isfull, payment_date, payment_uid, cut_off) values ";
$payarr = explode("|",$ppayi);
for ($i=0; $i<$pnr; ++$i) {
$pay = explode(",",$payarr[$i]);
$sql .= "(" . $lrno . "," . $pay[0] . "," . $pay[1] . "," . $pay[2] . ", CURRENT_TIMESTAMP, $uid, $cutoff),";
}

$sql = substr($sql,0,strlen($sql)-1);
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

db_connect();
for ($i=0; $i<$pnr; ++$i) {
$pay = explode(",",$payarr[$i]);
$nsql = "select round(( round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) ) + if( (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) > 29,( ( round((ifnull((select sum(tra_item_gprice*(tra_item_qty-ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0))) from transaction_items where tra_item_trano = tra_no),0)),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) ) - ( round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) )  ),( (round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2)) - ((round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2)) )*((case when (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) > 0 and (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) <=7 then 5 when (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) > 7 and (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) <= 14 then 10 when (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) > 14 and (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) <= 21 then 15 when (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) > 21 and (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) <= 29 then 20 else 0 end)/100) ) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2),2) balance from transactions where tra_no = " . $pay[0];
// $nsql = "select (round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2)) balance from transactions where tra_no = " . $pay[0];
$rs = $db_con->query($nsql);
$rec = $rs->fetch_array();
$cbal = $rec['balance'];
if ($cbal <= 0) {
$nnsql = "update transactions set tra_receipt_no = $lrno where tra_no = " . $pay[0];
$db_con->query($START_T);
$db_con->query($nnsql);
$db_con->query($END_T);
$nnsql = "update transactions set tra_fullpaid = CURRENT_TIMESTAMP where tra_no = " . $pay[0];
$db_con->query($START_T);
$db_con->query($nnsql);
$db_con->query($END_T);
}
}
db_close();

if ($pclt != 2) {
$sql = "update customers set credit_limit_bal = $pclimit where customer_id = $pdid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();
}

$str_response = "New Payment saved.";

echo $str_response;
break;

case "add":
$pdid = (isset($_POST['pdid'])) ? $_POST['pdid'] : 0;
$pclimit = (isset($_POST['pclimit'])) ? $_POST['pclimit'] : 0;
$ppayi = (isset($_POST['ppayi'])) ? $_POST['ppayi'] : "";
$pclt = (isset($_POST['pclt'])) ? $_POST['pclt'] : 0;
$pnr = (isset($_POST['pnr'])) ? $_POST['pnr'] : 0;
$rid = $_GET['rid'];

$sql = "alter table receipt_payments AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$cutoff = 0;
$sql = "select * from receipt_payments left join receipts on receipt_payments.payment_receipt_no = receipts.receipt_no where receipt_branch = $rbranch and payment_date = date_format(now(),'%Y-%m-%d') and cut_off = 1";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$cutoff = 2;
}
db_close();

$sql = "insert into receipt_payments (payment_receipt_no, payment_transaction_no, payment_amount, payment_isfull, payment_date, payment_uid, cut_off) values ";
$payarr = explode("|",$ppayi);
for ($i=0; $i<$pnr; ++$i) {
$pay = explode(",",$payarr[$i]);
$sql .= "(" . $rid . "," . $pay[0] . "," . $pay[1] . "," . $pay[2] . ", CURRENT_TIMESTAMP, $uid, $cutoff),";
}

$sql = substr($sql,0,strlen($sql)-1);
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

db_connect();
for ($i=0; $i<$pnr; ++$i) {
$pay = explode(",",$payarr[$i]);
$nsql = "select round(( round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) ) + if( (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) > 29,( ( round((ifnull((select sum(tra_item_gprice*(tra_item_qty-ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0))) from transaction_items where tra_item_trano = tra_no),0)),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) ) - ( round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) )  ),( (round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2)) - ((round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2)) )*((case when (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) > 0 and (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) <=7 then 5 when (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) > 7 and (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) <= 14 then 10 when (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) > 14 and (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) <= 21 then 15 when (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) > 21 and (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) <= 29 then 20 else 0 end)/100) ) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2),2) balance from transactions where tra_no = " . $pay[0];
// $nsql = "select (round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2)) balance from transactions where tra_no = " . $pay[0];
$rs = $db_con->query($nsql);
$rec = $rs->fetch_array();
$cbal = $rec['balance'];
if ($cbal <= 0) {
$nnsql = "update transactions set tra_receipt_no = $rid where tra_no = " . $pay[0];
$db_con->query($START_T);
$db_con->query($nnsql);
$db_con->query($END_T);
$nnsql = "update transactions set tra_fullpaid = CURRENT_TIMESTAMP where tra_no = " . $pay[0];
$db_con->query($START_T);
$db_con->query($nnsql);
$db_con->query($END_T);
}
}
db_close();

if ($pclt != 2) {
$sql = "update customers set credit_limit_bal = $pclimit where customer_id = $pdid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();
}

$str_response = "Payment added.";

echo $str_response;
break;

case "show_terms_transactions":
$pdid = $_POST['pdid'];

$sql = "select tra_date, tra_no, if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid) now, tra_due, unli_terms, waive_penalty, tra_vat, tra_avond, ";
$sql .= "round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) amount, ";
$sql .= "round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) balance, ";
$sql .= "(ifnull((select sum(tra_item_gprice*(tra_item_qty-ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0))) from transaction_items where tra_item_trano = tra_no),0)) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) tra_gross_bal, ";
$sql .= "round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2) swap_amt, tra_due, ";
$sql .= "round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) total_payment ";
$sql .= "from transactions left join customers on transactions.tra_did = customers.customer_id ";
$sql .= "where customer_id = $pdid and tra_cash = 0 ";
// $sql .= "and tra_fullpaid = '0000-00-00' ";
$sql .= "and ((round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2)) > 0)";

$swapa = 0;
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
	for ($i=0; $i<$rc; ++$i) {		
		$rec = $rs->fetch_array();
		$tnow = $rec['now'];
		$tdue = $rec['tra_due'];
		$unlit = $rec['unli_terms'];
		$waive = $rec['waive_penalty'];
		$swapa = $rec['swap_amt'];
		$str_response .= '<tr id="trano-' . $rec['tra_no'] . '" class="rowno-' . $i . '">';
		$str_response .= '<td>' . date("F j, Y",strtotime($rec['tra_date'])) . '</td>';
		$str_response .= '<td>' . $rec['tra_no'] . '</td>';
		
		$dayp = 0;
		if (($unlit == 0) && ($waive == 0)) {
		if (strtotime($tnow) > strtotime($tdue)) {
		$pd = (strtotime($tnow) - strtotime($tdue));
		$dayp = $pd/86400;

			$lessd = 0;
			if (($dayp>0) && ($dayp<=7)) $lessd = 5; // 1 - 7 days
			if (($dayp>7) && ($dayp<=14)) $lessd = 10; // 8 - 14 days
			if (($dayp>14) && ($dayp<=21)) $lessd = 15; // 15 - 21 days
			if (($dayp>21) && ($dayp<=29)) $lessd = 20; // 22 - 29 days
			if ($dayp>29) $lessd = 0; // beyond 29 days
		
		}
		}		
		
		$pen = "";
		$nsql = "select tra_item_no, tra_item_sup, (select supplier_desc from suppliers where supplier_id = tra_item_sup) supplier, tra_item_qty, tra_item_pcode, tra_item_pname, tra_item_psize, tra_item_gprice, tra_item_discount, is_borrowed, ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no group by dret_tino),0) returns, ifnull((select sum((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty) from swapped_products where sp_tino = tra_item_no),0) swap_amt from transaction_items where tra_item_trano = " . $rec['tra_no'];
		$nrs = $db_con->query($nsql);
		$nrc = $nrs->num_rows;
		$net = 0;
		$tnet = 0;
		if ($nrc>0) {
		for ($n=0; $n<$nrc; ++$n) {
		$nrec = $nrs->fetch_array();
		
		$net = ( ((float)$nrec['tra_item_gprice']) - ((float)$nrec['tra_item_gprice'] * (float)(($nrec['tra_item_discount'])/100)) ) * ($nrec['tra_item_qty'] - $nrec['returns']);
		$tnet = $tnet + round($net,2);
		}

		$tnet = $tnet - ($swapa*2);
		$btnet = $tnet;
		$tnet = round(($tnet - ($tnet/$rec['tra_vat']*($rec['tra_avond']/100))),2);
		if ( ($nrec['tra_item_sup'] == 2) && ($dayp > 0) && ($unlit == 0) && ($waive == 0) ) $tnet = $btnet;
		
		}		
		//
				
		if ($dayp>0) {
		$pen = round(($rec['balance']*($lessd/100)),2);
		if ($rec['balance'] < 0) $pen = 0;
		}
		$str_amt = $rec['amount'];
		$str_response .= '<td>' . $str_amt . '</td>';		
		$str_response .= '<td>' . $pen . '</td>';
		$str_bal = $rec['balance'];
		if ($dayp>0) {
			$str_bal = ($rec['amount'] + $pen) - $rec['total_payment'];
			if ($str_bal < 0) $str_bal = 0;
		}
		if ($dayp>29) $str_bal = $rec['tra_gross_bal'];
		$str_response .= '<td>' . $str_bal . '</td>';
		$str_response .= '<td>' . date("F j, Y",strtotime($rec['tra_due'])) . '</td>';
		$damt = $rec['balance'];
		if ($damt < 0) $damt = 0;
		if ($dayp>29) $damt = $rec['tra_gross_bal'];
		if ($pen == "") $pen = 0;
		$str_response .= '<td><input type="hidden" class="spamt-' . $rec['tra_no'] . '" value="' . $damt . '" /><input type="hidden" class="sppen-' . $rec['tra_no'] . '" value="' . $pen . '" /><a href="javascript: addPayment(\'' . date("F j, Y",strtotime($rec['tra_date'])) . '\',\'' . $rec['tra_no'] . '\',' . round($damt,2) . ',' . round($pen,2) . ')" class="tooltip-min trano-' . $rec['tra_no'] . '"><img src="images/add-payment.png" /><span>Add to payment</span></a></td>';
		$str_response .= '</tr>';
	}
} else {
$str_response = "No unpaid transactions.";
}
db_close();

echo $str_response;
break;

case "contents":
$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

$sql = "select count(*) mcount from receipts left join customers on receipts.receipt_did = customers.customer_id where receipt_no != 0";
$pql = "select receipt_no, receipt_branch, receipt_iscash, receipt_date, customer_id, concat(customer_fname, ' ', substr(customer_mname,1,1), '. ', customer_lname) dealer, (select concat(firstname, ' ', lastname) from users where user_id = receipt_uid) cashier, ifnull((select sum(payment_amount) from receipt_payments where payment_receipt_no = receipt_no),0) amount from receipts left join customers on receipts.receipt_did = customers.customer_id where receipt_no != 0";

// filter
$fptype = (isset($_GET['fptype'])) ? $_GET['fptype'] : -1;
$fbranch = (isset($_GET['fbranch'])) ? $_GET['fbranch'] : 0;
$fs = (isset($_GET['fs'])) ? $_GET['fs'] : "";
if ($fs != "") $fs = date("Y-m-d",strtotime($fs));
$fe = (isset($_GET['fe'])) ? $_GET['fe'] : "";
if ($fe != "") $fe = date("Y-m-d",strtotime($fe));
$frno = (isset($_GET['frno'])) ? $_GET['frno'] : 0;
$ftnno = (isset($_GET['ftnno'])) ? $_GET['ftnno'] : 0;
$fcustomer = (isset($_GET['fcustomer'])) ? $_GET['fcustomer'] : "";

$c1 = " and receipt_iscash = $fptype";
$c2 = " and receipt_branch = $fbranch";
$c3 = " and receipt_date >= '$fs' and receipt_date <= '$fe'";
$c4 = " and receipt_no = $frno";
$c5 = " and concat(customer_fname, ' ', customer_lname) like '%$fcustomer%'";
$c6 = " and find_in_set(receipt_no, (select group_concat(payment_receipt_no) from receipt_payments where payment_transaction_no = $ftnno group by payment_transaction_no)) != 0";

if ($fptype == -1) $c1 = "";
if ($fbranch == 0) $c2 = "";
if (($fs == "") || ($fe == "")) $c3 = "";
if ($frno == 0) $c4 = "";
if ($fcustomer == "") $c5 = "";
if ($ftnno == 0) $c6 = "";

$sql .= $c1 . $c2 . $c3 . $c4 . $c5 . $c6;
$pql .= $c1 . $c2 . $c3 . $c4 . $c5 . $c6;
//

db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$max_count = $rec[0];
db_close();

$perPage = 20;
$max_p = ceil($max_count / $perPage);

if ($dir == 3) $pageNum = $max_p;

$offset = ($pageNum - 1) * $perPage;
$pageParam = " LIMIT $offset, $perPage";

$lpage = "|$max_p";	

$sql = $pql . $pageParam;
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
$rowstyle = "row-style-even";

if ($rc>0) {

$str_response  = '<form name="frmContent" id="frmContent">';
$str_response .= '<table id="content-page">';
$str_response .= '<thead>';
$str_response .= '<tr><td width="2%"><input type="checkbox" name="chk_checkall" id="chk_checkall" onclick="Check_all(this.form, this);" /></td><td>Payment Type</td><td>Branch</td><td>Cashier</td><td>Date</td><td>No.</td><td>Dealer\'s Name</td><td>Amount</td><td>Tools</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {
$rbr = "Francey";
$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
$dn = $rec['dealer'];
if ($rec['customer_id'] == 0) $dn = "Walk-in Cash";
if ($rec['receipt_branch'] == 2) $rbr = "Yessamin";
if ($rec['receipt_branch'] == 3) $rbr = "Sta. Maria";
$pt = ($rec['receipt_iscash'] == 1) ? "Cash" : "Terms";
$str_response .= '<tr class="' . $rowstyle . '" onclick="chkRow(this);">';
$str_response .= '<td><input type="checkbox" name="chk_' . $rec['receipt_no'] . '" id="chk_' . $rec['receipt_no'] . '" onClick="Uncheck_Parent(\'chk_checkall\',this);" /></td>';
$str_response .= '<td>' . $pt . '</td>';
$str_response .= '<td>' . $rbr . '</td>';
$str_response .= '<td>' . $rec['cashier'] . '</td>';
$str_response .= '<td>' . date("F j, Y",strtotime($rec['receipt_date'])) . '</td>';
$str_response .= '<td>' . $rec['receipt_no'] . '</td>';
$str_response .= '<td>' . $dn . '</td>';
$str_response .= '<td>' . $rec['amount'] . '</td>';
$str_response .= '<td>';
if ($rec['receipt_iscash'] == 0) $str_response .= '<a href="javascript: printReceipt(\'' . $rec['receipt_no'] . '\');" class="tooltip-min"><img src="images/print-24.png" /><span>Print this Receipt</span></a>';
if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1)) $str_response .= '<a href="javascript: changeCashier(\'' . $rec['receipt_no'] . '\')" class="tooltip-min"><img src="images/cashier.png" /><span>Change Cashier</span></a>';
$str_response .= '</td>';
$str_response .= '</tr>';

}

$str_response .= '</tbody>';

if ($max_count > $perPage) {

$sNPage = new pageNav(5,'rReceiptF()',$pageNum,$max_p);
$str_response .= '<tfoot><tr><td colspan="7">';
$str_response .= $sNPage->getNav();
$str_response .= '</td></tr></tfoot>';

}

$str_response .= '</table></form>' . $lpage;
}
db_close();

echo $str_response;
break;

case "edit":
$rid = $_GET['rid'];

$json = '{ "editpayment": [';
$sql = "select receipt_no, concat(customer_fname, ' ', substr(customer_mname,1,1), '. ', customer_lname) dealer, (select concat(firstname, ' ', lastname) from users where user_id = receipt_uid) cashier, recruiter, receipt_date, customer_credit_limit, credit_limit_bal, receipt_iscash, receipt_did, credit_limit_type from receipts left join customers on receipts.receipt_did = customers.customer_id where receipt_no = $rid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"jrno":"' . $rec['receipt_no'] . '",';
$json .= '"jrdn":"' . $rec['dealer'] . '",';
$json .= '"jrdid":' . $rec['receipt_did'] . ',';
$json .= '"jrca":"' . $rec['cashier'] . '",';
$json .= '"jrr":"' . $rec['recruiter'] . '",';
$json .= '"jrd":"' . date("F j, Y",strtotime($rec['receipt_date'])) . '",';
$json .= '"jrcl":' . $rec['customer_credit_limit'] . ',';	
$json .= '"jrcla":' . $rec['credit_limit_bal'] . ',';
$json .= '"jrc":' . $rec['receipt_iscash'] . ',';
$json .= '"jrclt":' . $rec['credit_limit_type'] . '';
$json .= '}';
}
db_close();

$json .= '], "payments": [';
$sql = "select payment_no, payment_transaction_no, payment_amount, payment_isfull, payment_date, (select concat(firstname, ' ', lastname) from users where user_id = payment_uid) cashier from receipt_payments where payment_receipt_no = $rid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"jrpno":' . $rec['payment_no'] . ','; // NOTE: in adding new payment transaction no is use
$json .= '"jrptra":"' . $rec['payment_transaction_no'] . '",';
$json .= '"jrpa":' . $rec['payment_amount'] . ',';
$json .= '"jrpmop":' . $rec['payment_isfull'] . ',';
$json .= '"jrpd":"' . date("F j , Y",strtotime($rec['payment_date'])) . '",';
$json .= '"jrpca":"' . $rec['cashier'] . '"';
$json .= '},';
}
}
db_close();

$json = substr($json,0,strlen($json)-1);
$json .= '] }';

echo $json;
break;

case "return_receipt_did":
$prid = $_POST['prid'];
$str_response = 0;

$sql = "select receipt_did from receipts where receipt_no = $prid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
	$rec = $rs->fetch_array();
	$str_response = $rec['receipt_did'];
}
db_close();

echo $str_response;
break;

case "delete":
$rid = $_POST['rid'];

$rdid = 0;
$riscash = 0;
$sql = "select receipt_did, receipt_iscash from receipts where receipt_no = $rid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
$rec = $rs->fetch_array();
$rdid = $rec['receipt_did'];
$riscash = $rec['receipt_iscash'];
db_close();

if ($riscash == 1) {
	echo "This is a cash transaction.";
	break;
}

// $sql = "select payment_transaction_no, sum(payment_amount) amount from receipt_payments where payment_receipt_no = $rid group by payment_transaction_no";
$sql = "select sum(payment_amount) amount from receipt_payments where payment_receipt_no = $rid group by payment_receipt_no";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
//for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();

/* $nsql = "update transactions set tra_receipt_no = 0 where tra_no = " . $rec['payment_transaction_no'];
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T); */

$nsql = "update customers set credit_limit_bal = credit_limit_bal - " . $rec['amount'] . " where customer_id = $rdid and credit_limit_type != 2"; 
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);

//}
}
db_close();

$sql = "delete from receipts where receipt_no in($rid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "delete from receipt_payments where payment_receipt_no in($rid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Receipt deleted.";

echo $str_response;
break;

case "get_cashier":
$ccid = $_GET['ccid'];

$sql = "select receipt_uid from receipts where receipt_no = $ccid";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$str_response = $rec['receipt_uid'];
db_close();

echo $str_response;
break;

case "change_cashier":
$cid = $_GET['cid'];
$tcc = $_POST['tcc'];
$tcb = 0;

$sql = "select user_branch from users where user_id = $cid";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$tcb = $rec['user_branch'];
db_close();

$sql = "update receipts set receipt_branch = $tcb, receipt_uid = $cid where receipt_no = $tcc";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

echo $str_response;
break;

}

?>