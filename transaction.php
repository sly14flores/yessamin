<?php

session_start();
$uid = $_SESSION['user_id'];
$tbranch = $_SESSION['branch'];

require 'config.php';
require 'globalf.php';
require 'grants.php';

$req = "";
$START_T = "START TRANSACTION;";
$END_T = "COMMIT;";

$CONST_DD = 37; // due date

if (isset($_GET["p"])) $req = $_GET["p"];

$str_response = "";
$json = "";
$jpage = "";

switch ($req) {

case "dealer_tstat":
$str_response = 0;
$tdid = $_GET['tdid'];

$sql = "select tra_did, tra_no from transactions left join customers on transactions.tra_did = customers.customer_id where tra_cash = 0 and tra_did = $tdid and unli_terms = 0 and tra_due <= substr(now(),1,10) and ((round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2)) > 0)";

db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {

$str_response = $rc;
 
} 
db_close();

echo $str_response;
break;

case "last_trano":
$sql = "select tra_no from transactions order by tra_no desc limit 1";

$str_response = "0001";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$str_response = $rec['tra_no'] + 1;
}
db_close();

$str_response = str_pad($str_response, 4, '0', STR_PAD_LEFT);
echo $str_response;
break;

case "get_branch":

echo $tbranch;

break;

case "add":
$pdid = (isset($_POST['pdid'])) ? $_POST['pdid'] : 0;
$piscash = (isset($_POST['piscash'])) ? $_POST['piscash'] : 0;
$ptclimit = (isset($_POST['ptclimit'])) ? $_POST['ptclimit'] : 0;
$ptclimit = round($ptclimit,2);
$pcd = (isset($_POST['pcd'])) ? $_POST['pcd'] : 0;
$ptrai = (isset($_POST['ptrai'])) ? $_POST['ptrai'] : "";
$pnr = (isset($_POST['pnr'])) ? $_POST['pnr'] : 0;
$tvat = (isset($_POST['tvat'])) ? $_POST['tvat'] : 1;
$pad = (isset($_POST['pad'])) ? $_POST['pad'] : 0;
$ptamt = (isset($_POST['ptamt'])) ? $_POST['ptamt'] : 0;

$sql = "alter table transactions AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "insert into transactions (tra_date, tra_branch, tra_did, tra_cash, tra_cashd, tra_vat, tra_avond, tra_due, tra_uid, tra_amt) ";
$sql .= "values (CURRENT_TIMESTAMP, $tbranch, $pdid, $piscash, $pcd, $tvat, $pad, ADDDATE(CURRENT_TIMESTAMP, INTERVAL $CONST_DD DAY), $uid, $ptamt)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

if ($piscash == 0) {
$sql = "update customers set credit_limit_bal = $ptclimit where customer_id = $pdid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();
}

$ltra = 0;
$sql = "select tra_no from transactions where tra_uid = $uid order by tra_no desc limit 1";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$ltra = $rec['tra_no'];
db_close();

$sql = "alter table transaction_items AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sis = "0,";
$sql = "insert into transaction_items (tra_item_trano, tra_item_sup, tra_item_qty, tra_item_pcode, tra_item_pname, tra_item_psize, tra_item_gprice, tra_item_discount, tra_item_sino, is_borrowed) values ";
$eptrai = explode("|",$ptrai);
for ($i=0; $i<$pnr; ++$i) {
$ti = explode(",",$eptrai[$i]);
$sql .= "($ltra, " . $ti[0] . ", " . $ti[1] . ", '" . $ti[2] . "', '" . $ti[3] . "', '" . $ti[4] . "', " . $ti[5] . ", " . $ti[6] . ", " . $ti[7] . ", " . $ti[8] . "),";
$sis .= $ti[7] . ",";
}

$sql = substr($sql,0,strlen($sql)-1);
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$siids = $sis;
$sis = substr($sis,0,strlen($sis)-1);
updateStock($sis);
staticInventory($siids);

$str_response = "$ltra";

echo $str_response;
break;

case "update":
$ptrano = (isset($_POST['ptrano'])) ? $_POST['ptrano'] : 0;
$pdid = (isset($_POST['pdid'])) ? $_POST['pdid'] : 0;
$ptclimit = (isset($_POST['ptclimit'])) ? $_POST['ptclimit'] : 0;
$ptclimit = round($ptclimit,2);
$ptrai = (isset($_POST['ptrai'])) ? $_POST['ptrai'] : "";
$pnr = (isset($_POST['pnr'])) ? $_POST['pnr'] : 0;
$pltino = (isset($_POST['pltino'])) ? $_POST['pltino'] : 0;
$ptinos = (isset($_POST['ptinos'])) ? $_POST['ptinos'] : 0;
$ptamt = (isset($_POST['ptamt'])) ? $_POST['ptamt'] : 0;

$sql = "update customers set credit_limit_bal = $ptclimit where customer_id = $pdid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "update transactions set tra_amt = $ptamt where tra_no = $ptrano";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sis = "0,";
$eptrai = explode("|",$ptrai);
$ssql = "insert into transaction_items (tra_item_trano, tra_item_sup, tra_item_qty, tra_item_pcode, tra_item_pname, tra_item_psize, tra_item_gprice, tra_item_discount, tra_item_sino, is_borrowed, swapped_for) values ";
$sp_sql = "";
//$swap_amt = 0;
for ($i=0; $i<$pnr; ++$i) {
$ti = explode(",",$eptrai[$i]);
if ($ti[0] <= $pltino) { // update
	$sql = "update transaction_items set tra_item_qty = " . $ti[1] . " where tra_item_no = " . $ti[0];
} else {
	$ssql .= "($ptrano, " . $ti[1] . ", " . $ti[2] . ", '" . $ti[3] . "', '" . $ti[4] . "', '" . $ti[5] . "', " . $ti[6] . ", " . $ti[7] . ", " . $ti[8] . ", " . $ti[9] . ", " . $ti[10] . "),";
    $sis .= $ti[8] . ",";
	// item has been swapped
	if ($ti[10] != 0) {
	$sp_sql = "insert into swapped_products (sp_trano, sp_tino, sp_sino, sp_gp, sp_discount, sp_qty, sp_code, sp_name, sp_size) values ($ptrano, " . $ti[10] . ", " . $ti[8] . ", " . $ti[6] . ", " . $ti[7] . ", " . $ti[2] . ", '" . $ti[3] . "', '" . $ti[4] . "', '" . $ti[5] . "')";
		// insert payments
		//$receipt_sql = "insert into receipts (receipt_branch, receipt_did, receipt_iscash, receipt_date, receipt_uid) ";
		//$receipt_sql .= "values ($tbranch, $pdid, 0, CURRENT_TIMESTAMP, $uid)";
		//$swap_amt += ($ti[6] - ($ti[6]*($ti[7]/100))) * $ti[2];
		//
	}
	//
}

db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

if ($sp_sql != "") {
echo $sp_sql;
$sp_ssql = "alter table swapped_products AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sp_ssql);
$db_con->query($END_T);
db_close();

db_connect();
$db_con->query($START_T);
$db_con->query($sp_sql);
$db_con->query($END_T);
db_close();

}

}

// update full payment
db_connect();
$nsql = "select round(( round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) ) + if( (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) > 29,( ( round((ifnull((select sum(tra_item_gprice*(tra_item_qty-ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0))) from transaction_items where tra_item_trano = tra_no),0)),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) ) - ( round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) )  ),( (round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2)) - ((round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2)) )*((case when (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) > 0 and (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) <=7 then 5 when (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) > 7 and (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) <= 14 then 10 when (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) > 14 and (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) <= 21 then 15 when (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) > 21 and (datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due)) <= 29 then 20 else 0 end)/100) ) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2),2) balance from transactions where tra_no =  $ptrano";
$rs = $db_con->query($nsql);
$rec = $rs->fetch_array();
$cbal = $rec['balance'];
if ($cbal <= 0) {
$nnsql = "update transactions set tra_fullpaid = CURRENT_TIMESTAMP where tra_no = $ptrano";
$db_con->query($START_T);
$db_con->query($nnsql);
$db_con->query($END_T);
}
db_close();
//

/*
if ($sp_sql != "") {
$sp_ssql = "alter table receipts AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sp_ssql);
$db_con->query($END_T);
db_close();

db_connect();
$db_con->query($START_T);
$db_con->query($receipt_sql);
$db_con->query($END_T);
db_close();

$prn = 0;
$prn_sql = "select receipt_no from receipts where receipt_uid = $uid order by receipt_no desc limit 1";
db_connect();
$rs = $db_con->query($prn_sql);
$rec = $rs->fetch_array();
$prn = $rec['receipt_no'];
db_close();

$sp_ssql = "alter table receipt_payments AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sp_ssql);
$db_con->query($END_T);
db_close();

$payments_sql = "insert into receipt_payments (payment_receipt_no, payment_transaction_no, payment_amount, payment_isfull, payment_date, payment_uid) values ";
$payments_sql .= "($prn, $ptrano, " . round($swap_amt,2) . ", 2, CURRENT_TIMESTAMP, $uid)";
db_connect();
$db_con->query($START_T);
$db_con->query($payments_sql);
$db_con->query($END_T);
db_close();

$swap_amt = round($swap_amt,2);
$cl_sql = "update customers set credit_limit_bal = credit_limit_bal + $swap_amt where customer_id = $pdid and credit_limit_type != 2";
db_connect();
$db_con->query($START_T);
$db_con->query($cl_sql);
$db_con->query($END_T);
db_close();
}
*/

$eptinos = explode(",",$ptinos);
$ctinos = count($eptinos);

if ($pnr > $ctinos) {
$ssql = substr($ssql,0,strlen($ssql)-1);
db_connect();
$db_con->query($START_T);
$db_con->query($ssql);
$db_con->query($END_T);
db_close();
}

$siids = $sis;
$sis = substr($sis,0,strlen($sis)-1);
updateStock($sis);
staticInventory($siids);

echo $ptrano;
break;

case "view":
$tid = $_GET['tid'];

$titra = 0;
$sql  = "select tra_no, tra_date, tra_due, unli_terms, waive_penalty, if(tra_fullpaid != '0000-00-00',tra_fullpaid,substr(now(),1,10)) now, tra_vat, tra_avond, customer_id, discount_type, credit_limit_type, tra_did, concat(customer_fname, ' ', substr(customer_mname,1,1), '. ', customer_lname) dealer, recruiter, credit_limit_bal, tra_cash, tra_cashd, ";
$sql .= "round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) balance, ";
$sql .= "round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2) swap_amt, ";
$sql .= "(ifnull((select sum(tra_item_gprice*(tra_item_qty-ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0))) from transaction_items where tra_item_trano = tra_no),0)) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) tra_gross_bal ";
$sql .= "from transactions left join customers on transactions.tra_did = customers.customer_id where tra_no = $tid";
$json = '{ "edittransaction": [';
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$tradue = $rec['tra_due'];
$tranow = $rec['now'];
$jdealer = $rec['dealer'];
if ($rec['customer_id'] == 0) $jdealer = "Walk-in Cash";
// computation with penalty
$vdayp = -1;
if (strtotime($tranow) > strtotime($tradue)) {
$vpd = (strtotime($tranow) - strtotime($tradue));
$vdayp = $vpd/86400;
}
if ($tranow == $tradue) $vdayp = 0;
//
$json .= '{';
$json .= '"jtn":"' . $rec['tra_no'] . '",';
$json .= '"jtdid":' . $rec['tra_did'] . ',';
$json .= '"jtd":"' . date("F j, Y",strtotime($rec['tra_date'])) . '",';
$json .= '"jtdd":"' . date("F j, Y",strtotime($rec['tra_due'])) . '",';
$json .= '"jtdn":"' . $jdealer . '",';
$json .= '"jtclt":' . $rec['credit_limit_type'] . ',';
$json .= '"jtdr":"' . $rec['recruiter'] . '",';
$json .= '"jdt":' . $rec['discount_type'] . ',';
$json .= '"jtcl":' . $rec['credit_limit_bal'] . ',';
$json .= '"jtpt":' . $rec['tra_cash'] . ',';
$json .= '"jtcd":' . $rec['tra_cashd'] . ',';
$json .= '"jtvat":' . $rec['tra_vat'] . ',';
$json .= '"jtadd":' . $rec['tra_avond'] . ',';
$json .= '"jtut":' . $rec['unli_terms'] . ',';
$json .= '"jtwa":' . $rec['waive_penalty'] . ',';
$json .= '"jtbal":' . $rec['balance'] . ',';
$json .= '"jswapa":' . $rec['swap_amt'] . ',';
$json .= '"jdp":' . $vdayp . ',';
$json .= '"jpbal":' . $rec['tra_gross_bal'] . '';
$json .= '}';
db_close();

$tino = "";
$ltino = 0;
$json .= '], "traitems": [';
$sql = "select tra_item_no, tra_item_sino, tra_item_trano, tra_item_sup, (select supplier_desc from suppliers where supplier_id = tra_item_sup) supplier, tra_item_qty, tra_item_pcode, tra_item_pname, tra_item_psize, tra_item_gprice, tra_item_discount, is_borrowed, ifnull((select stock_in_quantity from stock_in_item where stock_in_id = tra_item_sino),0) - ifnull((select sum(retc_qty) from returns_companies where retc_sino = tra_item_sino),0) + ifnull((select sum(dret_qty) from dealers_returns where dret_sino = tra_item_sino),0) stocks, ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no group by dret_tino),0) returns, replaced, replacement, swapped_for from transaction_items where tra_item_trano = $tid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"jtin":' . $rec['tra_item_no'] . ',';
$json .= '"jtsino":"' . $rec['tra_item_sino'] . '",';
$json .= '"jtrano":"' . $rec['tra_item_trano'] . '",';
$json .= '"jtis":"' . $rec['supplier'] . '",';
$json .= '"jtisid":' . $rec['tra_item_sup'] . ',';
$json .= '"jtiq":' . $rec['tra_item_qty'] . ',';
$json .= '"jsoh":' . $rec['stocks'] . ',';
$json .= '"jtpc":"' . $rec['tra_item_pcode'] . '",';
$json .= '"jtpn":"' . $rec['tra_item_pname'] . '",';
$json .= '"jtps":"' . $rec['tra_item_psize'] . '",';
$json .= '"jtii":"' . $rec['tra_item_pcode'] . ' ' . $rec['tra_item_pname'] . ' ' . $rec['tra_item_psize'] . '",';
$json .= '"jtip":' . $rec['tra_item_gprice'] . ',';
$json .= '"jtid":' . $rec['tra_item_discount'] . ',';
$json .= '"jtret":' . $rec['returns'] . ',';
$json .= '"jtb":' . $rec['is_borrowed'] . ',';

$rep = "";
if ($rec['replacement'] != 0) {
$nsql = "select concat(' - replaced: ', tra_item_pcode, ' ', tra_item_pname, ' ', tra_item_psize) replacer from transaction_items where tra_item_no = " . $rec['replacement'];
$nrs = $db_con->query($nsql);
$nrc = $nrs->num_rows;
if ($nrc>0) {
	$nrec = $nrs->fetch_array();
	$rep = $nrec['replacer'];
}
}

$swap = "";
if ($rec['swapped_for'] != 0) {
$nsql = "select concat(' - payment for: ', tra_item_pcode, ' ', tra_item_pname, ' ', tra_item_psize) swap from transaction_items where tra_item_no = " . $rec['swapped_for'];
$nrs = $db_con->query($nsql);
$nrc = $nrs->num_rows;
if ($nrc>0) {
	$nrec = $nrs->fetch_array();
	$swap = $nrec['swap'];
}
}

$json .= '"jtr":' . $rec['replaced'] . ',';
$json .= '"jrep":' . $rec['replacement'] . ',';
$json .= '"jrepi":"' . $rep . '",';
$json .= '"jswp":' . $rec['swapped_for'] . ',';
$json .= '"jswpi":"' . $swap . '"';
$json .= '},';
$ltino = $rec['tra_item_no']; $tino .= $rec['tra_item_no'] . ",";
}
} else {
	$json .= '{},';
}
db_close();
$json = substr($json,0,strlen($json)-1);
$tino = substr($tino,0,(strlen($tino)-1));
$json .= '], "tino": [{"jltino":' . $ltino . ',"jtinos":"' . $tino . '"}] }';

echo $json;
break;

case "contents":

$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

$sql = "select count(*) mcount from transactions left join customers on transactions.tra_did = customers.customer_id";
$pql  = "select customer_id, tra_no, tra_cash, tra_branch, tra_date, unli_terms, waive_penalty, (select concat(firstname, ' ', lastname) from users where user_id = tra_uid) cashier, concat(customer_fname, ' ', substr(customer_mname,1,1), '. ', customer_lname) dealer, tra_due, tra_vat, tra_avond, if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid) now, tra_receipt_no, tra_fullpaid, if(tra_fullpaid != '0000-00-00',0,datediff(now(), tra_due)) day_past, ";
$pql .= "@amt := round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) amount, ";
$pql .= "tra_amt, ";
$pql .= "round(@amt,2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) balance, ";
$pql .= "(ifnull((select sum(tra_item_gprice*(tra_item_qty-ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0))) from transaction_items where tra_item_trano = tra_no),0)) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) tra_gross_bal, ";
$pql .= "ifnull((datediff(tra_fullpaid,tra_due)),0) paid_after_due ";
$pql .= "from transactions left join customers on transactions.tra_did = customers.customer_id";

// filters
$fptype = (isset($_GET['fptype'])) ? $_GET['fptype'] : -1;
$fbranch = (isset($_GET['fbranch'])) ? $_GET['fbranch'] : 0;
$fs = (isset($_GET['fs'])) ? $_GET['fs'] : "";
if ($fs != "") $fs = date("Y-m-d",strtotime($fs));
$fe = (isset($_GET['fe'])) ? $_GET['fe'] : "";
if ($fe != "") $fe = date("Y-m-d",strtotime($fe));
$ftrano = (isset($_GET['ftrano'])) ? $_GET['ftrano'] : 0;
$fcustomer = (isset($_GET['fcustomer'])) ? $_GET['fcustomer'] : "";
$fpcon = (isset($_GET['fpcon'])) ? $_GET['fpcon'] : "";
$und = (isset($_GET['und'])) ? $_GET['und'] : 0;

if ($fpcon != "") {
$sql .= " left join transaction_items on transactions.tra_no = transaction_items.tra_item_trano";
$pql .= " left join transaction_items on transactions.tra_no = transaction_items.tra_item_trano";
}

$sql .= " where tra_no != 0";
$pql .= " where tra_no != 0";

$c1 = " and tra_cash = $fptype";
$c2 = " and tra_branch = $fbranch";
$c3 = " and tra_date >= '$fs' and tra_date <= '$fe'";
$c4 = " and tra_no = $ftrano";
$c5 = " and concat(customer_fname, ' ', customer_lname) like '%$fcustomer%'";
$c6 = "";
$c6a = " and datediff(now(),tra_due) >= 0 and tra_fullpaid = '0000-00-00'";
$c6b = " and tra_fullpaid = '0000-00-00' and ((tra_amt - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2)) > 0)";
$c6c = " and tra_fullpaid != '0000-00-00'";
if ($und == 1) $c6 = $c6a . " and tra_cash = 0 and unli_terms = 0"; // due
if ($und == 2) $c6 = $c6b . " and tra_cash = 0 and unli_terms = 0"; // unpaid
if ($und == 3) $c6 = $c6c; // paid
$c7 = " and concat(tra_item_pcode, ' ', tra_item_pname, ' ', tra_item_psize) = '$fpcon'";

if ($fptype == -1) $c1 = "";
if ($fbranch == 0) $c2 = "";
if (($fs == "") || ($fe == "")) $c3 = "";
if ($ftrano == 0) $c4 = "";
if ($fcustomer == "") $c5 = "";
if ($und == 0) $c6 = "";
if ($fpcon == "") $c7 = "";

$sql .= $c1 . $c2 . $c3 . $c4 . $c5 . $c6 . $c7;
$pql .= $c1 . $c2 . $c3 . $c4 . $c5 . $c6 . $c7;
//

$sql .= " order by tra_date, tra_no";
$pql .= " order by tra_date, tra_no";

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
$str_response .= '<tr><td width="2%"><input type="checkbox" name="chk_checkall" id="chk_checkall" onclick="Check_all(this.form, this);" /></td><td>Payment Type</td><td>Branch</td><td>Date</td><td>Cashier</td><td>Tra.No.</td><td>Dealer\'s Name</td><td>Amount</td><td>Balance</td><td>Penalty</td><td>Status</td><td>Due Date</td><td>Tools</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {
$tstatc = "tstatok";
$tbr = "Francey";
$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
$tdue = $rec['tra_due'];
$tnow = $rec['now'];
$waive = $rec['waive_penalty'];

$tnet = $rec['amount'];
$dayp = 0;
// penalty
if (($rec['tra_cash'] == 0) && ($rec['unli_terms']) == 0 && ($waive == 0)) {
    $tnet = $rec['tra_amt'];
    $dayp = $rec['day_past'];
}
//

$dfn = $rec['dealer'];
if ($rec['customer_id'] == 0) $dfn = "Walk-in Cash";
if ($rec['tra_branch'] == 2) $tbr = "Yessamin";
if ($rec['tra_branch'] == 3) $tbr = "Sta. Maria";
$pt = ($rec['tra_cash'] == 1) ? "Cash" : "Terms";
$bal = "n/a";
$cbal = 1;
$pen = "n/a";
$i_pen = 0; // add penalty in balance
$tstat = "";
$dd = "n/a";
if (($rec['tra_cash'] == 0) && ($rec['unli_terms'] == 0) && ($waive == 0) ) {
$bal = $rec['balance'];
$pen = "";
$dd = date("F j, Y",strtotime($rec['tra_due']));
	if (strtotime($rec['now']) == strtotime($rec['tra_due'])) { $tstat = "Due today"; $tstatc = "tstatdue"; }
	if (strtotime($rec['now']) > strtotime($rec['tra_due'])) {
	
	$lessd = 0;
	if (($dayp>0) && ($dayp<=7)) $lessd = 5; // 1 - 7 days
	if (($dayp>7) && ($dayp<=14)) $lessd = 10; // 8 - 14 days
	if (($dayp>14) && ($dayp<=21)) $lessd = 15; // 15 - 21 days
	if ($dayp>21) $lessd = 100; // beyond 21 days
	if (($dayp>21) && ($dayp<=29)) $lessd = 20; // 22 - 29 days
	if ($dayp>29) $lessd = 0; // beyond 29 days	
	
	$tstat = $dayp . " day(s) due"; $tstatc = "tstatdue";
	$pen = round(($rec['balance']*($lessd/100)),2);	
	$i_pen = round(($rec['balance']*($lessd/100)),2); // add penalty in balance	
	}
// add penalty in balance
$bal = $bal + $i_pen;	
if ($bal <= 0) {
	$bal = 0;
	$cbal = 0; // penalty is paid
	$tstat = "Cleared";
	$tstatc = "tstatok";
	$pen = "";	
}
}
if ($waive == 1) { // if penalty is waived
$pen = "Waived";
$dd = date("F j, Y",strtotime($rec['tra_due']));
}
if ($rec['paid_after_due'] > 0) $tstatc = "tstatpp";
// if ($rec['tra_receipt_no'] != 0) $tstat = "Cleared";
if ($rec['tra_fullpaid'] != '0000-00-00') $tstat = "Cleared";
$str_response .= '<tr class="' . $rowstyle . '" onclick="chkRow(this);">';
$str_response .= '<td class="' . $tstatc . '"><input type="checkbox" name="chk_' . $rec['tra_no'] . '" id="chk_' . $rec['tra_no'] . '" onClick="Uncheck_Parent(\'chk_checkall\',this);" /></td>';
$str_response .= '<td class="' . $tstatc . ' payt-' . $rec['tra_no'] . '">' . $pt . '</td>';
$str_response .= '<td class="' . $tstatc . '">' . $tbr . '</td>';
$str_response .= '<td class="' . $tstatc . '">' . date("F j, Y",strtotime($rec['tra_date'])) . '</td>';
$str_response .= '<td class="' . $tstatc . '">' . $rec['cashier'] . '</td>';
$str_response .= '<td class="' . $tstatc . '">' . $rec['tra_no'] . '</td>';
$str_response .= '<td class="' . $tstatc . '">' . $dfn . '</td>';
$str_response .= '<td class="' . $tstatc . '">' . $rec['amount'] . '</td>';
//$str_response .= '<td class="' . $tstatc . '">' . $rec['tra_amt'] . '</td>';
if ($dayp>29) $bal = $rec['tra_gross_bal'];
$str_response .= '<td class="' . $tstatc . '">' . $bal . '</td>';
$str_response .= '<td class="' . $tstatc . ' penstat-' . $rec['tra_no'] . '">' . $pen . '</td>';
$str_response .= '<td class="' . $tstatc . ' tstatok-' . $rec['tra_no'] . '">' . $tstat . '</td>';
$str_response .= '<td class="' . $tstatc . '">' . $dd . '</td>';
$str_response .= '<td>';
$str_response .= '<a href="javascript: printTran(\'' . $rec['tra_no'] . '\')" class="tooltip-min"><img src="images/print-16.png" /><span>Print this TRA</span></a>';
$str_response .= '<a href="javascript: viewTran(\'' . $rec['tra_no'] . '\')" class="tooltip-min"><img src="images/view-16.png" /><span>View this TRA</span></a>';
if ((strtotime($tnow) > strtotime($tdue)) && ($cbal == 1)) {
if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1)) $str_response .= '<a href="javascript: penalty(\'' . $rec['tra_no'] . '\')" class="tooltip-min"><img src="images/waive.png" /><span>Impose/Waive Penalty</span></a>';
}
if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1)) $str_response .= '<a href="javascript: changeCashier(\'' . $rec['tra_no'] . '\')" class="tooltip-min"><img src="images/cashier.png" /><span>Change Cashier</span></a>';
$str_response .= '</td>';
$str_response .= '</tr>';

}

$str_response .= '</tbody>';

if ($max_count > $perPage) {

$sNPage = new pageNav(4,'rTranF()',$pageNum,$max_p);
$str_response .= '<tfoot><tr><td colspan="7">';
$str_response .= $sNPage->getNav();
$str_response .= '</td></tr></tfoot>';

}

$str_response .= '</table></form>' . $lpage;
}
db_close();

echo $str_response;
break;

case "list_dealers":
$ftb = (isset($_GET['ftb'])) ? $_GET['ftb'] : 0;
$sql = "select customer_id, customer_branch, concat(customer_fname, ' ', customer_lname) fullname, recruiter, customer_credit_limit, credit_limit_bal, discount_type, credit_limit_type from customers where customer_id != 0";
if ($ftb != 0) $sql .= " and customer_branch = $tbranch";

$json = '[';
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$dbr = " - Francey";
if ($rec['customer_branch'] == 2) $dbr = " - Yessamin";
$json .= '{';
$json .= '"id":' . $rec['customer_id'] . ',';
$json .= '"text":"' . addslashes($rec['fullname']) . $dbr . '",';
$json .= '"dealer":"' . addslashes($rec['fullname']) . '",';
$json .= '"dr":"' . addslashes($rec['recruiter']) . '",';
$json .= '"dicl":' . $rec['customer_credit_limit'] . ',';
$json .= '"dcl":' . $rec['credit_limit_bal'] . ',';
$json .= '"ddt":' . $rec['discount_type'] . ',';
$json .= '"dclt":'. $rec['credit_limit_type'] . '';
$json .= '},';
}
$json = substr($json,0,(strlen($json)-1));
}
$json .= ']';

db_close();

echo $json;
break;

case "list_suppliers":
$sql = "select supplier_id, supplier_desc from suppliers";

$json = '[';
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$str_sup = $rec['supplier_id'];
if ($rec['supplier_id'] < 10) $str_sup = str_pad($str_sup,2,"0",STR_PAD_LEFT);
$json .= '{';
$json .= '"id":' . $rec['supplier_id'] . ',';
$json .= '"text":"' . $rec['supplier_desc'] . '",';
$json .= '"psup":"' . $str_sup . '"';
$json .= '},';
}
$json = substr($json,0,(strlen($json)-1));
}
$json .= ']';

db_close();

echo $json;
break;

/*
case "list_products":
$bp = "!=";
$rel = "and";
if ($tbranch == 2) {
$bp = "=";
$rel = "or";
}
$sql = "select stock_in_id, stock_in_sid, concat(stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) item, concat(supplier_desc, ' ', stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize, ' ', stock_in_price) product, concat(stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) item_desc, stock_in_pcode, stock_in_pname, supplier_id, supplier_desc, stock_in_psize, stock_in_price, sin_date_invoice, stock_in_quantity, stock_in_ref, ifnull(stock_in_quantity,0) - ifnull((select sum(tra_item_qty) from transaction_items where tra_item_sino = stock_in_id),0) + ifnull((select sum(dret_qty) from dealers_returns where dret_sino = stock_in_id),0) - ifnull((select sum(retc_qty) from returns_companies where retc_sino = stock_in_id),0) stocks from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id left join suppliers on stock_in.sin_supid = suppliers.supplier_id where supplier_id $bp 2 $rel supplier_id $bp 17 $rel supplier_id $bp 18 $rel supplier_id $bp 23 and stock_soldout = 0 order by sin_date_invoice, stock_in_id";

$json = '[';
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
//$list_chk[$i] = $rec['item'];
$list_stock[$i] = $rec['stocks'];
$list_id[$i] = $rec['stock_in_id'];
$list_sid[$i] = $rec['stock_in_sid'];
$list_txt[$i] = $rec['product'] . " REF#: " . $rec['stock_in_ref']  . " DATE: " . date("F j",strtotime($rec['sin_date_invoice'])) . " STOCKS: " . $rec['stocks'];
$list_supid[$i] = $rec['supplier_id'];
$list_supd[$i] = $rec['supplier_desc'];
$list_item[$i] = $rec['item_desc'];
$list_code[$i] = $rec['stock_in_pcode'];
$list_name[$i] = $rec['stock_in_pname'];
$list_size[$i] = $rec['stock_in_psize'];
$list_price[$i] = $rec['stock_in_price'];
}

$tp = "";
for ($i=0; $i<$rc; ++$i) {
//$tp .= $list_chk[$i] . ",";
//if (substr_count($tp,$list_chk[$i]) > 1) continue;
if ($list_stock[$i] <= 0) continue;
$json .= '{';
$json .= '"id":"' . $list_id[$i] . '",';
$json .= '"jsiid":' . $list_sid[$i] . ',';
$json .= '"text":"' . $list_txt[$i] . '",';
$json .= '"jsid":' . $list_supid[$i] . ',';
$json .= '"jsd":"' . $list_supd[$i] . '",';
$json .= '"jpd":"' . $list_item[$i] . '",';
$json .= '"jpc":"' . $list_code[$i] . '",';
$json .= '"jpn":"' . $list_name[$i] . '",';
$json .= '"jps":"' . $list_size[$i] . '",';
$json .= '"jpp":' . $list_price[$i] . ',';
$json .= '"jpst":' . $list_stock[$i] . '';
$json .= '},';
}
$json = substr($json,0,(strlen($json)-1));
}
$json .= ']';

db_close();

echo $json;
break;
*/

case "list_products":
$sql = "select stock_in_id, stock_in_sid,  concat(stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) product, stock_in_pcode, stock_in_pname, stock_in_psize from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id left join suppliers on stock_in.sin_supid = suppliers.supplier_id where supplier_branch = $tbranch or supplier_branch = 0 group by stock_in_pcode, stock_in_pname, stock_in_psize";

$json = '[';
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$list_sid[$i] = $rec['stock_in_id'];
$list_txt[$i] = $rec['product'];
$list_code[$i] = $rec['stock_in_pcode'];
$list_name[$i] = $rec['stock_in_pname'];
$list_size[$i] = $rec['stock_in_psize'];
}

$tp = "";
for ($i=0; $i<$rc; ++$i) {
$json .= '{';
$json .= '"id":"' . $list_sid[$i] . '",';
$json .= '"text":"' . $list_txt[$i] . '",';
$json .= '"jpc":"' . $list_code[$i] . '",';
$json .= '"jpn":"' . $list_name[$i] . '",';
$json .= '"jps":"' . $list_size[$i] . '"';
$json .= '},';
}
$json = substr($json,0,(strlen($json)-1));
}
$json .= ']';

db_close();

echo $json;
break;

case "suggest_products":
$filp = (isset($_GET['filp'])) ? $_GET['filp'] : "";

$bp = "!=";
$rel = "and";
if ($tbranch == 2) {
$bp = "=";
$rel = "or";
}
$sql = "select stock_in_id, stock_in_sid, concat(stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) item, concat(supplier_desc, ' ', stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize, ' ', stock_in_price) product, concat(stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) item_desc, stock_in_pcode, stock_in_pname, supplier_id, supplier_desc, stock_in_psize, supplier_branch, stock_in_price, sin_date_invoice, stock_in_quantity, stock_in_ref, ifnull(stock_in_quantity,0) - ifnull((select sum(tra_item_qty) from transaction_items where tra_item_sino = stock_in_id),0) + ifnull((select sum(dret_qty) from dealers_returns where dret_sino = stock_in_id),0) - ifnull((select sum(retc_qty) from returns_companies where retc_sino = stock_in_id),0) + ifnull((select sum(sp_qty) from swapped_products where sp_sino = stock_in_id),0) stocks from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id left join suppliers on stock_in.sin_supid = suppliers.supplier_id where concat(stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) like '%$filp%' order by sin_date_invoice, stock_in_id";

$json = '{ "suggestproducts": [';
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$alos = 0;
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
if ($rec['stocks'] <= 0) continue;
$alos = 1;
$json .= '{';
$json .= '"id":' . $rec['stock_in_id'] . ',';
$json .= '"jsiid":' . $rec['stock_in_sid'] . ',';
$supid = $rec['supplier_id'];
if ($rec['supplier_id'] == '') $supid = 0;
$json .= '"jsid":' . $supid . ',';
$json .= '"jsdate":"' . date("M j",strtotime($rec['sin_date_invoice'])) . '",';
$json .= '"jsref":"' . $rec['stock_in_ref'] . '",';
$json .= '"jsd":"' . $rec['supplier_desc'] . '",';
$json .= '"jpd":"' . $rec['item_desc'] . '",';
$json .= '"jpc":"' . $rec['stock_in_pcode'] . '",';
$json .= '"jpn":"' . $rec['stock_in_pname'] . '",';
$json .= '"jps":"' . $rec['stock_in_psize'] . '",';
$json .= '"jpp":' . $rec['stock_in_price'] . ',';
$json .= '"jpst":' . $rec['stocks'] . '';
$json .= '},';
}
if ($alos == 1) $json = substr($json,0,(strlen($json)-1));
}
$json .= '] }';

db_close();

echo $json;
break;

case "soldout_stock":
$bp = "!=";
if ($tbranch == 2) $bp = "=";

$sql = "select stock_in_id, stock_in_sid, ifnull(stock_in_quantity,0) - ifnull((select sum(tra_item_qty) from transaction_items where tra_item_sino = stock_in_id),0) + ifnull((select sum(dret_qty) from dealers_returns where dret_sino = stock_in_id),0) - ifnull((select sum(retc_qty) from returns_companies where retc_sino = stock_in_id),0) + ifnull((select sum(sp_qty) from swapped_products where sp_sino = stock_in_id),0) stocks from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id left join suppliers on stock_in.sin_supid = suppliers.supplier_id where supplier_id $bp 2 and sin_date_invoice <= date_sub(now(), interval 2 month) and stock_soldout = 0 order by sin_date_invoice, stock_in_id";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$sis = "0,";
    for ($i=0; $i<$rc; ++$i) {
    $rec = $rs->fetch_array();
    if ($rec['stocks'] <= 0) {
        $sis .= $rec['stock_in_id'] . ",";
    }
    }
$sis = substr($sis,0,strlen($sis)-1);
$nsql = "update stock_in_item set stock_soldout = 1 where stock_in_id in (" . $sis . ")";    
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
}   
db_close();

echo $str_response;
break;

case "discount_type":
$dt = $_GET['dt'];
$supid = $_GET['supid'];
$sql = "select ";

	switch ($dt) {

	case 1:
	$sql .= "basic_discount ";
	break;

	case 2:
	$sql .= "top_seller_discount ";
	break;

	case 3:
	$sql .= "outright_discount ";
	break;
	
	case 4:
	$sql .= "special_discount ";
	break;
	
	case 5:
	$sql .= "sl_discount ";
	break;		

	}

$sql .= "from suppliers where supplier_id = $supid";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$itemd = 0;

	switch ($dt) {

	case 1:
	$itemd = $rec['basic_discount'];
	break;

	case 2:
	$itemd = $rec['top_seller_discount'];
	break;

	case 3:
	$itemd = $rec['outright_discount'];
	break;

	case 4:
	$itemd = $rec['special_discount'];
	break;
	
	case 5:
	$itemd = $rec['sl_discount'];
	break;		
	
	}

db_close();

$json = '{"dtype":[';
$json .= '{"jds":' . $itemd  . '}';
$json .= ']}';

echo $json;
break;

case "avon_discount":
$ddid = $_GET['ddid'];
$br = $_GET['br'];
$vat = 1;
$avond = 0;
$dap = 0;

/*
*	1000 to 5199.99 = 5%
*	5200 to 7499.99 = 10%
*	7500 to 9899.99 = 15%
*	9900 to 25499.99 = 20%
*	25500 and above = 22.5%
*/
$sql = "select  round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no and tra_item_sup = 2) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no and tra_item_sup = 2)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no and tra_item_sup) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no and tra_item_sup)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) amount from transactions where tra_did = $ddid and tra_date >= date_sub(curdate(), interval day(curdate())-1 day)";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$dap = $dap + $rec['amount'];
}
db_close();

$dap = $dap + $br;
if (($dap >= 1000) && ($dap <= 5199.99)) { $vat = 1.12; $avond = 5; } // 5%
if (($dap >= 5200) && ($dap <= 7499.99)) { $vat = 1.12; $avond = 10; } // 10%
if (($dap >= 7500) && ($dap <= 9899.99)) { $vat = 1.12; $avond = 15; } // 15%
if (($dap >= 9900) && ($dap <= 25499.99)) { $vat = 1.12; $avond = 20; } // 20%
if ($dap >= 25500) { $vat = 1.12; $avond = 22; } // 22.5%

$sql = "select customer_category from customers where customer_id = $ddid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
	if ($rec['customer_category'] == "cbc") {
		$vat = 1;
		$avond = 0;
	}
}
db_close();

$json = '{"adis":[';
$json .= '{"jvat":' . $vat . ',"jads":' . $avond  . '}';
$json .= ']}';

echo $json;
break;

case "delete":
$tid = $_POST['tid'];

// for update sold out stocks
$sis = "0,";
$sql = "select tra_item_sino from transaction_items where tra_item_trano = $tid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
    for ($i=0; $i<$rc; ++$i) {
        $rec = $rs->fetch_array();
        $sis .= $rec['tra_item_sino'] . ",";
    }
}
db_close();
//

$trid = 0;
$tdid = 0;
$tclt = 0;
$sql = "select tra_did, tra_cash, credit_limit_type from transactions left join customers on transactions.tra_did = customers.customer_id where tra_no = $tid";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$tdid = $rec['tra_did'];
$tcash = $rec['tra_cash'];
$tclt = $rec['credit_limit_type'];
db_close();

$rcl = 0; // increment credit limit

// $sql = "select round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) net from transactions where tra_no = $tid";
$sql = "select round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) amount from transactions where tra_no = $tid";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$rcl = $rec['amount'];
db_close();

if (($tcash == 0) && ($tclt != 2)) {
$sql = "update customers set credit_limit_bal = credit_limit_bal + $rcl where customer_id = $tdid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();
}

$sql = "delete from transactions where tra_no in ($tid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$drno = "";
$sql = "select tra_item_no from transaction_items where tra_item_trano = $tid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$drno .= $rec['tra_item_no'] . ",";
}
db_close();
$drno = substr($drno,0,strlen($drno)-1);

$sql = "delete from transaction_items where tra_item_trano in ($tid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

// delete returns
$sql = "delete from dealers_returns where dret_tino in ($drno)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();
//

$trid = 0;
$sql = "select payment_receipt_no from receipt_payments where payment_transaction_no = $tid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$trid = $rec['payment_receipt_no'];
}
db_close();
/*
$sql = "delete from receipts where receipt_no in($trid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();
*/
$sql = "delete from receipt_payments where payment_transaction_no in($tid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "delete from swapped_products where sp_trano in ($tid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$siids = $sis;
$sis = substr($sis,0,strlen($sis)-1);
updateStock($sis);
staticInventory($siids);

$str_response = "Transaction deleted.";
echo $str_response;
break;

case "cash_payment":
$ptid = $_POST['ptid'];

$sql = "select tra_did from transactions where tra_no = $ptid";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$rdid = $rec['tra_did'];
db_close();

$sql = "alter table receipts AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

// receipts
$sql = "insert into receipts (receipt_branch, receipt_did, receipt_iscash, receipt_date, receipt_uid) values ($tbranch, $rdid, 1, CURRENT_TIMESTAMP, $uid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$lrno = 0;
$sql = "select receipt_no from receipts where receipt_iscash = 1 and receipt_uid = $uid order by receipt_no desc limit 1";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$lrno = $rec['receipt_no'];
db_close();

// receipt payments
$namt = 0;
$sql = "select round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) net from transactions where tra_no = $ptid";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$namt = $rec['net'];
db_close();

$sql = "alter table receipt_payments AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$cutoff = 0;
$sql = "select * from receipt_payments left join receipts on receipt_payments.payment_receipt_no = receipts.receipt_no where receipt_branch = $tbranch and payment_date = date_format(now(),'%Y-%m-%d') and cut_off = 1";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$cutoff = 2;
}
db_close();

$sql = "insert into receipt_payments (payment_receipt_no, payment_transaction_no, payment_amount, payment_isfull, payment_date, payment_uid, cut_off) ";
$sql .= "values($lrno, $ptid, $namt, 1, CURRENT_TIMESTAMP, $uid, $cutoff)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "update transactions set tra_fullpaid = CURRENT_TIMESTAMP, tra_receipt_no = $lrno where tra_no = $ptid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

echo $str_response;
break;

case "penalty":
$ptra = (isset($_POST['ptra'])) ? $_POST['ptra'] : 0;
$pwa = (isset($_POST['pwa'])) ? $_POST['pwa'] : 0;
$pre = (isset($_POST['pre'])) ? $_POST['pre'] : "";

$sql = "update transactions set waive_penalty = $pwa, penalty_waive_reason = '$pre' where tra_no = $ptra";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

echo $str_response;
break;

case "penalty_status":
$ptid = (isset($_GET['ptid'])) ? $_GET['ptid'] : 0;

$json = '{ "penalty":[';
$sql = "select waive_penalty, penalty_waive_reason from transactions where tra_no = $ptid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"jtp":' . $rec['waive_penalty'] . ',';
$json .= '"jtre":"' . $rec['penalty_waive_reason'] . '"';
$json .= '}';
}
db_close();
$json .= ']}';

echo $json;
break;

case "replace_item":
$ptino = (isset($_POST['ptino'])) ? $_POST['ptino'] : 0;
$psup  = (isset($_POST['psup'])) ? $_POST['psup'] : 0;
$ppt = (isset($_POST['ppt'])) ? $_POST['ppt'] : 0;
$prepicode = (isset($_POST['prepicode'])) ? $_POST['prepicode'] : "";
$prepiname = (isset($_POST['prepiname'])) ? $_POST['prepiname'] : "";
$prepisize = (isset($_POST['prepisize'])) ? $_POST['prepisize'] : "";
$prepiqty = (isset($_POST['prepiqty'])) ? $_POST['prepiqty'] : 0;
$ppgp = (isset($_POST['ppgp'])) ? $_POST['ppgp'] : 0;
$ppd = (isset($_POST['ppd'])) ? $_POST['ppd'] : 0;
$phrsino = (isset($_POST['phrsino'])) ? $_POST['phrsino'] : 0;

$sql = "update transaction_items set replaced = 1 where tra_item_no = $ptino";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "insert into transaction_items (tra_item_trano, tra_item_sup, tra_item_qty, tra_item_pcode, tra_item_pname, tra_item_psize, tra_item_gprice, tra_item_discount, tra_item_sino, replacement) values ";
$sql .= "($ppt, $psup, $prepiqty, '$prepicode', '$prepiname', '$prepisize', $ppgp, $ppd, $phrsino, $ptino)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

updateStock($ptino);
$si = "$ptino,";
staticInventory($si);

$str_response = "Item successfully replaced.";

echo $str_response;
break;

case "get_cashier":
$ccid = $_GET['ccid'];

$sql = "select tra_uid from transactions where tra_no = $ccid";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$str_response = $rec['tra_uid'];
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

$sql = "update transactions set tra_branch = $tcb, tra_uid = $cid where tra_no = $tcc";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

echo $str_response;
break;

case "get_uid":

echo $uid;

break;

case "clear_payment":
$tid = isset($_POST['tid']) ? $_POST['tid'] : 0;

// get recent date of payment
$recent_date_payment = "";
$sql = "select payment_date from receipt_payments where payment_transaction_no = '$tid' order by payment_no desc limit 1";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc) {
	$rec = $rs->fetch_array();
	$recent_date_payment = $rec['payment_date'];
}
db_close();
//

$sql = "update transactions set tra_fullpaid = '$recent_date_payment' where tra_no = '$tid'";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "TN no. $tid Cleared.";

echo $str_response;
break;

case "clear_payment_return":
$tid = isset($_POST['tid']) ? $_POST['tid'] : 0;

// all items have been returned
$sql = "select (select tra_due from transactions where tra_no = '$tid') transaction_due, sum(tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no group by dret_tino),0)) returns from transaction_items where tra_item_trano = '$tid'";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc) {
	$rec = $rs->fetch_array();
	// if ($rec['returns'] == 0) $recent_date_payment = $rec['transaction_due']; 
	$recent_date_payment = $rec['transaction_due']; 
}
db_close();
//

$sql = "update transactions set tra_fullpaid = '$recent_date_payment' where tra_no = '$tid'";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "TN no. $tid Cleared.";

echo $str_response;
break;

case "clear_payment_replace":
$tid = isset($_POST['tid']) ? $_POST['tid'] : 0;

// all items have been replaced
$sql = "select sum(replacement) check_replace, (select tra_due from transactions where tra_no = tra_item_trano) transaction_due from transaction_items where tra_item_trano = '$tid'";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc) {
	$rec = $rs->fetch_array();
	// if ($rec['check_replace'] > 0) $recent_date_payment = $rec['transaction_due']; 
	$recent_date_payment = $rec['transaction_due']; 
}
db_close();
//

$sql = "update transactions set tra_fullpaid = '$recent_date_payment' where tra_no = '$tid'";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "TN no. $tid Cleared.";

echo $str_response;
break;

case "unclear_payment":
$tid = isset($_POST['tid']) ? $_POST['tid'] : 0;

$sql = "update transactions set tra_fullpaid = '0000-00-00' where tra_no = '$tid'";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "TN no. $tid Uncleared.";

echo $str_response;
break;

case "archive_tra":
$ptid = (isset($_POST['ptid'])) ? $_POST['ptid'] : 0;
$archive_db = "boutique_archive";
$boutique_db = "boutique";

$tids = "";

//
$sql = "select * from transactions where tra_no = $ptid";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
db_close();

$dd = "";
foreach ($rec as $f => $v) {
	if (is_numeric($f)) {
		if (is_string($v)) $dd .= "'$v',";
		else $dd .= "$v,";
	}
}
$dd = substr($dd,0,strlen($dd)-1);

$DB_FILE = $archive_db;
$sql = "insert into transactions values ($dd)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();
//

//
$DB_FILE = $boutique_db;
$sql = "select * from transaction_items where tra_item_trano = $ptid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
$dd = "";
if ($rc) {
	for ($i=0; $i<$rc; ++$i) {
	$dd .= "(";
		$rec = $rs->fetch_array();
		foreach ($rec as $f => $v) {
			if (is_numeric($f)) {
				if (is_string($v)) $dd .= "'$v',";
				else $dd .= "$v,";
			} else {
				if ($f == 'tra_item_sino') $tids .= "$v,";
			}
		}
		$dd = substr($dd,0,strlen($dd)-1);
	$dd .= "),";		
	}
}
db_close();
$dd = substr($dd,0,strlen($dd)-1);
$tids = substr($tids,0,strlen($tids)-1);

$DB_FILE = $archive_db;
$sql = "insert into transaction_items values $dd";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();
//

/* //
$DB_FILE = $boutique_db;
$sql = "select * from dealers_returns where dret_sino in ($tids)";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
$dd = "";
if ($rc) {
	for ($i=0; $i<$rc; ++$i) {
	$dd .= "(";
		$rec = $rs->fetch_array();
		foreach ($rec as $f => $v) {
			if (is_numeric($f)) {
				if (is_string($v)) $dd .= "'$v',";
				else $dd .= "$v,";
			}
		}
		$dd = substr($dd,0,strlen($dd)-1);
	$dd .= "),";		
	}
}
db_close();
$dd = substr($dd,0,strlen($dd)-1);

$DB_FILE = $archive_db;
$sql = "insert into dealers_returns values $dd";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();
// */

$DB_FILE = $boutique_db;
$sql = "delete from transactions where tra_no = $ptid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "delete from transaction_items where tra_item_trano = $ptid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

/* $sql = "delete from dealers_returns where dret_sino in ($tids)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close(); */

$str_response = "TN successfully archived.";

echo $str_response;
break;

case "restore_db":

$sql = "DROP TABLE `avon_categories`, `bank_accounts`, `customers`, `dealers_returns`, `deductions`, `deductions_manual`, `deposits`, `discount_types`, `loans`, `manual_remittances`, `manual_remittance_actual_cash`, `offsets`, `offset_items`, `receipts`, `receipt_payments`, `remittance_actual_cash`, `returns_companies`, `stock_in`, `stock_in_item`, `suppliers`, `swapped_products`, `transactions`, `transaction_items`, `users`;";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$restore = "cmd /C echo Syching Database please wait... & mysql -u root -psly boutique < " . $_SERVER['DOCUMENT_ROOT'] . "yessamin/db/boutique.sql";
$WshShell = new COM("WScript.Shell");
$oExec = $WshShell->Run($restore, 3, true);
// $oExec = $WshShell->Run($restore);

echo $str_response;

break;

}

function updateStock($sis) {

global $db_con, $tbranch, $START_T, $END_T;

$sql = "select stock_in_id, stock_in_sid, ifnull(stock_in_quantity,0) - ifnull((select sum(tra_item_qty) from transaction_items where tra_item_sino = stock_in_id),0) + ifnull((select sum(dret_qty) from dealers_returns where dret_sino = stock_in_id),0) - ifnull((select sum(retc_qty) from returns_companies where retc_sino = stock_in_id),0) + ifnull((select sum(sp_qty) from swapped_products where sp_sino = stock_in_id),0) stocks from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id left join suppliers on stock_in.sin_supid = suppliers.supplier_id where stock_in_id in($sis) order by sin_date_invoice, stock_in_id";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$nsis = "0,";
$nnsis = "0,";
    for ($i=0; $i<$rc; ++$i) {
    $rec = $rs->fetch_array();
    if ($rec['stocks'] <= 0) {
        $nsis .= $rec['stock_in_id'] . ",";
    } else {
        $nnsis .= $rec['stock_in_id'] . ",";    
    }
    }
$nsis = substr($nsis,0,strlen($nsis)-1);
$nnsis = substr($nnsis,0,strlen($nnsis)-1);
$nsql = "update stock_in_item set stock_soldout = 1 where stock_in_id in (" . $nsis . ")";
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
$nsql = "update stock_in_item set stock_soldout = 0 where stock_in_id in (" . $nnsis . ")";
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
}   
db_close();

}

?>