<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>MANUAL REMITTANCE - Print</title>
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

$selco = (isset($_GET['selco'])) ? $_GET['selco'] : 0;
$fs = (isset($_GET['fs'])) ? $_GET['fs'] : "";
if ($fs != "") $fs = date("Y-m-d",strtotime($fs));
$fe = (isset($_GET['fe'])) ? $_GET['fe'] : "";
if ($fe != "") $fe = date("Y-m-d",strtotime($fe));
$fcustomer = (isset($_GET['fcustomer'])) ? $_GET['fcustomer'] : "";

$cover = date("F j, Y",strtotime($fs));
if ($fe != "") $cover = date("F j - ",strtotime($fs)) . date("F j, Y",strtotime($fe));

?>
<tr><td colspan="6" align="center">MANUAL REMITTANCE</tr>
<tr><td colspan="6">Date:&nbsp;<?php echo $cover; ?></td></tr>
<tr><td>Date</td><td>Cutoff</td><td>Dealer</td><td>Amount</td><td>Note</td><td>Remarks</td></tr>
</thead>
<tbody>
<?php

$actual_cash_fc_mr = 0;
$actual_cash_eod_mr = 0;
$sql = "SELECT manual_actual_cash_id, manual_actual_cash_amount_fc, manual_actual_cash_amount_eod, manual_actual_cash_date FROM manual_remittance_actual_cash WHERE manual_actual_cash_date = '$fs'";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$actual_cash_fc_mr = $rec['manual_actual_cash_amount_fc'];
$actual_cash_eod_mr = $rec['manual_actual_cash_amount_eod'];
}
db_close();

$pql = "select manual_remittance_id, manual_remittance_date, if(manual_remittance_cutoff = 0,'Undefined',if(manual_remittance_cutoff = 1,'First Cut-off','End of the day')) cutoff, concat(customer_fname, ' ', substr(customer_mname,1,1), '. ', customer_lname) dealer, manual_remittance_amount, manual_remittance_note, manual_remittance_cutoff from manual_remittances left join customers on manual_remittances.manual_remittance_did = customers.customer_id where manual_remittance_id != 0";

$c1 = " and manual_remittance_date = '$fs'";
if ($fe != "") $c1 = " and manual_remittance_date >= '$fs' and manual_remittance_date <= '$fe'";
$c2 = " and concat(customer_fname, ' ', customer_lname) like '%$fcustomer%'";
$c3 = " and manual_remittance_cutoff = $selco";

if (($fs == "") && ($fe == "")) $c1 = "";
if ($fcustomer == "") $c2 = "";
if ($selco == 0) $c3 = "";

$pql .= $c1 . $c2 . $c3;
//

$sql = $pql;
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
$rowstyle = "row-style-even";

$tcash_fc = 0;
$tcash_eod = 0;
if ($rc>0) {

for ($i=0; $i<$rc; ++$i) {
$rem = "";
$rec = $rs->fetch_array();
$str_response .= '<tr class="' . $rowstyle . '" onclick="chkRow(this);">';
$str_response .= '<td>' . date("F j, Y",strtotime($rec['manual_remittance_date'])) . '</td>';
$str_response .= '<td>' . $rec['cutoff'] . '</td>';
$str_response .= '<td>' . $rec['dealer'] . '</td>';
$str_response .= '<td>' . $rec['manual_remittance_amount'] . '</td>';
if ($rec['manual_remittance_cutoff'] == 1) $tcash_fc += $rec['manual_remittance_amount'];
if ($rec['manual_remittance_cutoff'] == 2) $tcash_eod += $rec['manual_remittance_amount'];
$str_response .= '<td>' . $rec['manual_remittance_note'] . '</td>';
$str_response .= '<td>' . $rem . '</td>';
$str_response .= '</tr>';
}

$str_response .= '</tbody>';

}
db_close();

echo $str_response;

?>
</tbody>
<tfoot></tfoot>
</table>
<!--<table id="tab-deductions">
<thead>
<tr><td colspan="5" align="center">DEDUCTIONS</tr>
<tr><td>Category</td><td>Description</td><td>Note</td><td>Cashier</td><td>Amount</td></tr>
</thead>
<tbody>
<?php

$tded = 0;
$sql = "select dedm_cat, dedm_id, dedm_date, (select concat(firstname, ' ', lastname) from users where user_id = dedm_uid) cashier, dedm_desc, dedm_amt, dedm_note from deductions_manual where dedm_id != 0";
$str_response = "";

// filter
$sc1 = " and dedm_date = '$fs'";
if ($fe != "") $sc1 = " and dedm_date >= '$fs' and dedm_date <= '$fe'";

if (($fs == "") && ($fe == "")) $sc1 = "";

$sql .= $sc1;
// $pql .= $sc1;
//

db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;

if ($rc>0) {

for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$str_response .= '<tr>';
$str_response .= '<td>' . $rec['dedm_cat'] . '</td>';
$str_response .= '<td>' . $rec['dedm_desc'] . '</td>';
$str_response .= '<td>' . $rec['dedm_note'] . '</td>';
$str_response .= '<td>' . $rec['cashier'] . '</td>';
$str_response .= '<td>' . $rec['dedm_amt'] . '</td>';
$str_response .= '</tr>';
$tded = $tded + $rec['dedm_amt'];
}

}
db_close();

// echo $str_response;

?>
</tbody>
<tfoot>
<?php

$str_response  = '<tr>';
$str_response .= '<td colspan="5">Total:&nbsp;Php. ' . number_format(round($tded,2),2)  . '</td>';
$str_response .= '</tr>';

// echo $str_response;

?>
</tfoot>
</table>-->
<table id="remittance-summary" class="clearfix">
<?php
$str_response = '<tr><td>Total First Cut-off:</td><td>Php.&nbsp;' . number_format($tcash_fc,2) . '</td></tr>';
$tacash_fc = 0;
if ($fcustomer == "") {
$str_response .= '<tr><td>Actual Cash on Hand:</td><td>Php.&nbsp;' . number_format($actual_cash_fc_mr,2) . '</td></tr>';
$tacash_fc = $tcash_fc - $actual_cash_fc_mr;
$str_response .= '<tr><td>Deficit:</td><td>Php.&nbsp;' . number_format($tacash_fc,0) . '</td></tr>';
}
$str_response .= '<tr><td>Total End of the Day:</td><td>Php.&nbsp;' . number_format($tcash_eod,2) . '</td></tr>';
$tacash_eod = 0;
if ($fcustomer == "") {
$str_response .= '<tr><td>Actual Cash on Hand:</td><td>Php.&nbsp;' . number_format($actual_cash_eod_mr,0) . '</td></tr>';
$tacash_eod = $tcash_eod - $actual_cash_eod_mr;
$str_response .= '<tr><td>Deficit:</td><td>Php.&nbsp;' . number_format($tacash_eod,2) . '</td></tr>';
}
$sub_total = 0;
$deficit_total = 0;
$grand_total = 0;
$sub_total = $tcash_fc + $tcash_eod;
$deficit_total = $tacash_fc + $tacash_eod;
$grand_total = $sub_total - $deficit_total;
$str_response .= '<tr><td>Sub Total:</td><td>Php.&nbsp;' . number_format($sub_total,0) . '</td></tr>';
$str_response .= '<tr><td>Total Deficit:</td><td>Php.&nbsp;' . number_format($deficit_total,0) . '</td></tr>';
$str_response .= '<tr><td>Grand Total:</td><td>Php.&nbsp;' . number_format($grand_total,0) . '</td></tr>';

echo $str_response;
?>
</table>
</div>
</div>
</body>
</html>