<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Print</title>
<style type="text/css">
img {
	border: 0;
}

* {
	margin: 0;
	padding: 0;
}

body {
	font: .7em sans-serif;
}

#wrapper {
	width: 100%;
	margin-left: auto;
	margin-right: auto;
}

#in-wrapper {
	padding: 3px 5px;
}

#in-header {
	padding-left: 30px;
}

#in-header p, #in-header h3 {
	text-align: center;
}

#in-header p:first-child {
	font-weight: bold;
	margin-top: 0;
}

#in-header p {
	margin-top: 0;
}

#in-header h3 {
	margin-top: 5px;
}

#in-main {
	padding: 5px 5px 0 5px;
}

#in-main #receipt-info {
	width: 100%;
	border-collapse: collapse;
}

#receipt-info td {
	padding: 0;
}

#in-main #receipt-items {
	width: 100%;
	border-collapse: collapse;
	margin-top: 1px;
}

#receipt-items td {
	padding: 1px;
	/*border: 1px solid #000;*/
}

#footer {
	margin-top: 5px;
	float: right;
}

#in-footer {
	padding: 0 10px;
}

#in-footer p:last-child {
	width: 150px;
	border-bottom: 1px solid #000;
	padding-top: 30px;
}

#receipt-items thead td {
	border-top: 1px solid #000;
	border-bottom: 1px solid #000;
}
</style>
<link rel="icon" type="image/ico" href="transaction.ico" />
<link rel="shortcut icon" href="invoice.ico" />
<link href="../jquery/css/start/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="../jquery/js/jquery-ui-1.8.16.custom.min.js"></script>
</head>
<body>
<?php

session_start();
$branch = $_SESSION['branch'];
require '../config.php';
require '../globalf.php';
$str_response = "";

switch ($branch) {

case 1: // frances branch
$branch_name = "Francey Boutique & Gen. MDSE";
$branch_add = "Public Market, San Jose, Narvacan, Ilocos Sur";
$branch_prop = "Frances Mae de Guzman - Prop.";
$branch_tin = "TIN NO. 929-575-265-0000 NON VAT";
break;

case 2: // yessamin boutique
$branch_name = "Yessamin Boutique & Gen. MDSE";
$branch_add = "Public Market, San Jose, Narvacan, Ilocos Sur";
$branch_prop = "Elsa Juanita B. de Guzman - Prop.";
$branch_tin = "TIN NO. 179-706-265-0000 VAT";
break;

case 3: // sta maria branch
$branch_name = "Francey Boutique & Gen. MDSE Branch 1";
$branch_add = "Public Market, Sta. Maria, Ilocos Sur";
$branch_prop = "Frances Mae de Guzman - Prop.";
$branch_tin = "TIN NO. 929-575-265-0000 NON VAT";
break;

}

?>
<div id="wrapper">
<div id="in-wrapper">
<div id="header">
<div id="in-header">
<?php

$rid = $_GET['rid'];
$sql = "select receipt_no, concat(customer_fname, ' ', substr(customer_mname,1,1), '. ', customer_lname) dealer, (select concat(firstname, ' ', lastname) from users where user_id = receipt_uid) cashier, customer_id, address, receipt_date, receipt_iscash from receipts left join customers on receipts.receipt_did = customers.customer_id where receipt_no = $rid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
$rec = $rs->fetch_array();
$pdid = $rec['customer_id'];
$rno = $rec['receipt_no'];
$dealer = $rec['dealer'];
$rca = $rec['cashier'];
$address = $rec['address'];
$rdate = $rec['receipt_date'];
db_close();

?>
<div id="trano">No.&nbsp;<?php echo $rno; ?></div>
<p style="font-weight: bold;"><?php echo $branch_name; ?></p>
<!-- <p><?php // echo $branch_add; ?></p> -->
<!-- <p><?php // echo $branch_prop; ?></p> -->
<!-- <p><?php // echo $branch_tin; ?></p> -->
<!-- <h3>RECEIPT</h3> -->
</div>
</div>
<div id="main">
<div id="in-main">
<table id="receipt-info">
<tr><td>Sold to:&nbsp;<?php echo $dealer; ?></td><td>Date Issued:&nbsp;<?php echo date("F j, Y",strtotime($rdate)); ?></td></tr>
<tr><td>Address:&nbsp;<?php echo $address; ?></td><td>Cashier:&nbsp;<?php echo $rca ?></td></tr>
</table>
<table id="receipt-items">
<thead>
<tr><td>Cashier</td><td>Date</td><td>TRA No.</td><td>Mode of Payment</td><td>Amount</td></tr>
</thead>
<tbody>
<?php

$sql = "select (select firstname from users where user_id = payment_uid) cashier, payment_date, payment_transaction_no, payment_isfull, payment_amount from receipt_payments where payment_receipt_no = $rid";
$mop = "Advance";
$tamt = 0;
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
if ($rec['payment_isfull']) $mop = "Full";
if ($rec['payment_isfull'] == 2) $mop = "Swapped Product(s)";
$str_response .= '<tr>';
$str_response .= '<td>' . $rec['cashier']  . '</td>';
$str_response .= '<td>' . date("F j, Y",strtotime($rec['payment_date']))  . '</td>';
$str_response .= '<td>' . $rec['payment_transaction_no'] . '</td>';
$str_response .= '<td>' . $mop  . '</td>';
$str_response .= '<td>' . $rec['payment_amount']  . '</td>';
$str_response .= '</tr>';
$tamt = $tamt + $rec['payment_amount'];
}
db_close();

$str_response .= '<tr><td>&nbsp;</td><td colspan="3" align="right">Total:</td><td align="center">' . round($tamt,2) . '</td></tr>';

echo $str_response;

?>
</tbody>
</table>
</div>
</div>
<div id="footer">
<div id="in-footer">
<p>Received by:</p>
<p></p>
</div>
</div>
</div>
</div>
</body>
</html>