<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>TRANSACTION REPORT - Print</title>
<style type="text/css">
img {
	border: 0;
}

* {
	margin: 0;
	padding: 0;
}

body {
	font: .8em sans-serif;
}

#wrapper {
	width: 100%;
	margin-left: auto;
	margin-right: auto;
}

#in-wrapper {
	padding: 0 10px;
}

#tab-transaction {
	width: 100%;
	border-collapse: collapse;
}

#tab-transaction thead td {
	border-bottom: 1px solid #000;
}

#tab-transaction tfoot td {
	border-top: 1px solid #000;
}

#tab-transaction td {
	padding: 2px;
}
</style>
<link rel="icon" type="image/ico" href="transaction.ico" />
<link rel="shortcut icon" href="invoice.ico" />
<link href="../jquery/css/start/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="../jquery/js/jquery-ui-1.8.16.custom.min.js"></script>
</head>
<body>
<div id="wrapper">
<div id="in-wrapper">
<table id="tab-transaction">
<thead>
<?php

require '../config.php';
$str_response = "";

$sql  = "select customer_id, tra_no, tra_cash, tra_branch, tra_date, unli_terms, waive_penalty, (select concat(firstname, ' ', lastname) from users where user_id = tra_uid) cashier, concat(customer_fname, ' ', substr(customer_mname,1,1), '. ', customer_lname) dealer, tra_due, tra_vat, tra_avond, substr(now(),1,10) now, tra_receipt_no, datediff(if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid), tra_due) day_past, ";
$sql .= "@amt := round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) amount, ";
$sql .= "tra_amt - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) amount_penalty, ";
$sql .= "round(@amt,2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) balance, ";
$sql .= "(ifnull((select sum(tra_item_gprice*(tra_item_qty-ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0))) from transaction_items where tra_item_trano = tra_no),0)) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) tra_gross_bal ";
$sql .= "from transactions left join customers on transactions.tra_did = customers.customer_id";

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
}

$sql .= " where tra_no != 0";

$c1 = " and tra_cash = $fptype";
$c2 = " and tra_branch = $fbranch";
$c3 = " and tra_date >= '$fs' and tra_date <= '$fe'";
$c4 = " and tra_no = $ftrano";
$c5 = " and concat(customer_fname, ' ', customer_lname) like '%$fcustomer%'";
$c6 = "";
$c6a = " and datediff(now(),tra_due) >= 0 and tra_fullpaid = '0000-00-00'";
$c6b = " and tra_fullpaid = '0000-00-00' and ((tra_amt - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2)) > 0)";
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
//

$sql .= " order by tra_date";

$tbranch = "All";
if ($fbranch == 1) $tbranch = "Francey";
if ($fbranch == 2) $tbranch = "Yessamin";
if ($fbranch == 3) $tbranch = "Sta. Maria";

switch ($und) {

case 1:
$ts = "Due ";
break;

case 2:
$ts = "Unpaid ";
break;

case 3:
$ts = "Paid ";
break;

}

?>
<tr><td colspan="10" align="center">TRANSACTIONS</tr>
<tr><td colspan="2">Branch:&nbsp;<?php echo $tbranch;  ?></td><td colspan="3"><?php echo $ts; ?>Transactions</td><td colspan="5">Date:&nbsp;<?php if ($c3 == "") echo "All Transactions"; else echo date("M j, Y",strtotime($fs)); if ($fe != "") echo date(" - M j, Y",strtotime($fe)); ?></td></tr>
<tr><td>Date</td><td>Cashier</td><td>Tra.No.</td><td>Dealer</td><td>Amount</td><td>Balance</td><td>Penalty</td><td>Due Date</td><td>Status</td><td>Remark</td></tr>
</thead>
<tbody>
<?php

db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;

if ($rc>0) {
	for ($i=0; $i<$rc; ++$i) {
	$str_response = "";
	$rec = $rs->fetch_array();
		$str_response .= '<tr>';
		$str_response .= '<td>' . date("M j, Y",strtotime($rec['tra_date'])) . '</td>';
		$str_response .= '<td>' . $rec['cashier'] . '</td>';
		$str_response .= '<td>' . $rec['tra_no'] . '</td>';
		$str_response .= '<td>' . $rec['dealer'] . '</td>';
		$str_response .= '<td>' . $rec['amount'] . '</td>';

		$tdue = $rec['tra_due'];
		$waive = $rec['waive_penalty'];
		
		$pen = 0;
		$tnet = 0;
		$dayp = 0;
        $dayp = $rec['day_past'];        
		if (($rec['unli_terms']) == 0 && ($waive == 0)) { // skip if unlimited terms & penalty is waived
            $tnet = $rec['amount_penalty'];
            $dayp = $rec['day_past'];
			
			if (($dayp>0) && ($dayp<=7)) $lessd = 5; // 1 - 7 days
			if (($dayp>7) && ($dayp<=14)) $lessd = 10; // 8 - 14 days
			if (($dayp>14) && ($dayp<=21)) $lessd = 15; // 15 - 21 days
			if (($dayp>21) && ($dayp<=29)) $lessd = 20; // 22 - 29 days
			if ($dayp>29) $lessd = 0; // beyond 29 days
			
            $pen = $rec['balance']*($lessd/100);
		}       
        if ($dayp < 0) $dayp = 0;
		$bal = $rec['tra_gross_bal'];
		$str_response .= '<td>' . $bal . '</td>';		
		$str_response .= '<td>' . round($pen,2) . '</td>';
		$str_response .= '<td>' . date("M j, Y",strtotime($rec['tra_due'])) . '</td>';
        $str_response .= '<td>' . $dayp . ' day(s) due</td>';
        $rem = "";
        if ($waive == 1) $rem = "Waived";
		if ($dayp>29) $rem = "Back to gross";
        $str_response .= '<td>' . $rem . '</td>';        
		$str_response .= '</tr>';
		if ($rec['balance'] > 0) echo $str_response;
	}
}

db_close();

?>
</tbody>
</tfoot>
</table>
</div>
</div>
</body>
</html>