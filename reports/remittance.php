<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>REMITTANCE - Print</title>
<style type="text/css">
@import url(../css/clearfix.css) screen;
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

#tab-remittance, #tab-deductions {
	width: 100%;
	border-collapse: collapse;
}

#tab-remittance thead td, #tab-deductions thead td {
	border-bottom: 1px solid #000;
}

#tab-remittance tfoot td, #tab-deductions tfoot td {
	border-top: 1px solid #000;
}

#tab-remittance td, #tab-deductions td {
	padding: 2px;
}

#total-remittance {
	text-align: right;
	padding-right: 5px;
}

#remittance-summary {
	float: right;
	width: 30%;
	border-collapse: collapse;
	margin-top: 10px;
	margin-right: 25px;
}

#remittance-summary td {
	padding: 3px;
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
<table id="tab-remittance">
<thead>
<?php

require '../config.php';
$str_response = "";

$fbranch = (isset($_GET['fbranch'])) ? $_GET['fbranch'] : 1;
$fdate = (isset($_GET['fdate'])) ? $_GET['fdate'] : "";
$fdate = date("Y-m-d",strtotime($fdate));
$fcutoff = (isset($_GET['fcutoff'])) ? $_GET['fcutoff'] : 0;

$rbr = "Francey";
if ($fbranch == 2) $rbr = "Yessamin";
if ($fbranch == 3) $rbr = "Francey Branch 1";

?>
<tr><td colspan="6" align="center">REMITTANCE</tr>
<tr><td colspan="3">Branch:&nbsp;<?php echo $rbr;  ?></td><td colspan="3">Date:&nbsp;<?php echo date("F j, Y",strtotime($fdate)); ?></td></tr>
<tr><td>Cutoff</td><td>Name</td><td>Tra.No.</td><td>Receipt No.</td><td>Cashier</td><td>Amount</td></tr>
</thead>
<tbody>
<?php

$actual_cash_fc = 0;
$actual_cash_eod = 0;
$sql = "SELECT `actual_cash_fc_amount`, `actual_cash_eod_amount` FROM `remittance_actual_cash` WHERE `actual_cash_branch` = $fbranch AND `actual_cash_date` = '$fdate'";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$actual_cash_fc = $rec['actual_cash_fc_amount'];
$actual_cash_eod = $rec['actual_cash_eod_amount'];
}
db_close();

$sql  = "select receipt_did, payment_date, receipt_branch, (select concat(customer_fname, ' ', customer_lname) from customers where customer_id = receipt_did) dealer, payment_transaction_no, payment_receipt_no, sum(payment_amount) amount, (select concat(firstname, ' ', lastname) from users where user_id = payment_uid) cashier, cut_off from receipt_payments left join receipts on receipt_payments.payment_receipt_no = receipts.receipt_no where payment_no != 0";
$sql .= " and (select receipt_branch from receipts where receipt_no = payment_receipt_no) = $fbranch";
$sql .= " and payment_date = '$fdate'";
if ($fcutoff == 0) $sql .= ""; else $sql .= " and cut_off = $fcutoff";
$sql .= " group by payment_transaction_no, cut_off";

$_1cf = 0;
$_eod = 0;
$tamt = 0; // total remittance

db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;

if ($rc>0) {

for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$dn = $rec['dealer'];
$cutoff = "Undefine";
if ($rec['cut_off'] == 1) $cutoff = "First Cutoff";
if ($rec['cut_off'] == 2) $cutoff = "End of the day";
if ($rec['receipt_did'] == 0) $dn = "Walk-in Cash";
$str_response .= '<tr>';
$str_response .= '<td>' . $cutoff . '</td>';
$str_response .= '<td>' . $dn . '</td>';
$str_response .= '<td>' . $rec['payment_transaction_no'] . '</td>';
$str_response .= '<td>' . $rec['payment_receipt_no'] . '</td>';
$str_response .= '<td>' . $rec['cashier'] . '</td>';
$str_response .= '<td>' . $rec['amount'] . '</td>';
$str_response .= '</tr>';
if ($rec['cut_off'] == 1) $_1cf = $_1cf + $rec['amount'];
if ($rec['cut_off'] == 2) $_eod = $_eod + $rec['amount'];
$tamt = $tamt + $rec['amount'];
}

}
db_close();

echo $str_response;

?>
</tbody>
<tfoot>
<?php

$str_response  = '<tr>';
$str_response .= '<td colspan="6">Total:&nbsp;' . round($tamt,2)  . '</td>';
$str_response .= '</tr>';

echo $str_response;

?>
</tfoot>
</table>
<table id="tab-deductions">
<thead>
<tr><td colspan="5" align="center">DEDUCTIONS</tr>
<tr><td>Category</td><td>Description</td><td>Note</td><td>Cashier</td><td>Amount</td></tr>
</thead>
<tbody>
<?php

$sql  = "select ded_cat, ded_branch, ded_id, ded_date, (select concat(firstname, ' ', lastname) from users where user_id = ded_uid) cashier, ded_desc, ded_amt, ded_note from deductions where ded_id != 0";
$sql .= " and ded_branch = $fbranch";
$sql .= " and ded_date = '$fdate'";
$str_response = "";

$tded = 0;

db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;

if ($rc>0) {

for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$str_response .= '<tr>';
$str_response .= '<td>' . $rec['ded_cat'] . '</td>';
$str_response .= '<td>' . $rec['ded_desc'] . '</td>';
$str_response .= '<td>' . $rec['ded_note'] . '</td>';
$str_response .= '<td>' . $rec['cashier'] . '</td>';
$str_response .= '<td>' . $rec['ded_amt'] . '</td>';
$str_response .= '</tr>';
$tded = $tded + $rec['ded_amt'];
}

}
db_close();

echo $str_response;

?>
</tbody>
<tfoot>
<?php

$str_response  = '<tr>';
$str_response .= '<td colspan="5">Total:&nbsp;' . round($tded,2)  . '</td>';
$str_response .= '</tr>';

echo $str_response;

?>
</tfoot>
</table>
<?php

$fc_deficit = 0;
$eod_deficit = 0;
$totaleod = 0;
$totalr = 0;

?>
<table id="remittance-summary" class="clearfix">
<?php

if ( ($fcutoff == 0) || ($fcutoff == 1) ) echo "<tr><td style=\"width: 250px;\">First Cutoff:</td><td>" . round($_1cf,2) . "</td></tr>";
if ( ($fcutoff == 0) || ($fcutoff == 1) ) echo "<tr><td>Actual Cash:</td><td>" . round($actual_cash_fc,2) . "</td></tr>";
if ($fcutoff == 2) $actual_cash_fc = 0;
if ( ($fcutoff == 0) || ($fcutoff == 1) ) $fc_deficit = $_1cf - $actual_cash_fc;
if ( ($fcutoff == 0) || ($fcutoff == 1) ) echo "<tr><td>Deficit:</td><td>" . round($fc_deficit,2) . "</td></tr>";

if ( ($fcutoff == 0) || ($fcutoff == 2) ) echo "<tr><td>End of the day:</td><td>" . round($_eod,2) . "</td></tr>";
if ( ($fcutoff == 0) || ($fcutoff == 2) ) echo "<tr><td>Actual Cash:</td><td>" . round($actual_cash_eod,2) . "</td></tr>";
if ($fcutoff == 1) $actual_cash_eod = 0;

if ( ($fcutoff == 0) || ($fcutoff == 2) ) echo "<tr><td style=\"padding-left: 25px;\">Deductions:</td><td>" . round($tded,2) . "</td></tr>";

if ( ($fcutoff == 0) || ($fcutoff == 2) ) $totaleod = $_eod - $tded;
if ( ($fcutoff == 0) || ($fcutoff == 2) ) echo "<tr><td>Total End of the day:</td><td>" . round($totaleod,2) . "</td></tr>";
if ( ($fcutoff == 0) || ($fcutoff == 2) ) $eod_deficit = $_eod - ($actual_cash_eod + $tded);
if ( ($fcutoff == 0) || ($fcutoff == 2) ) echo "<tr><td>Deficit:</td><td>" . round($eod_deficit,2) . "</td></tr>";

if ($fcutoff == 0)$totalr = $actual_cash_fc + $totaleod;
if ($fcutoff == 0) echo "<tr><td>Total Remittance:</td><td>" . round($totalr,2) . "</td></tr>";
?>
</table>
</div>
</div>
</body>
</html>