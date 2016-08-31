<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Deposits - Print</title>
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

#tab-deposits {
	width: 100%;
	border-collapse: collapse;
}

#tab-deposits thead td {
	border-bottom: 1px solid #000;
}

#tab-deposits tfoot td {
	border-top: 1px solid #000;
}

#tab-deposits td {
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
<table id="tab-deposits">
<thead>
<?php

$fbranch = (isset($_GET['fbranch'])) ? $_GET['fbranch'] : 0;
$fan = (isset($_GET['fan'])) ? $_GET['fan'] : 0;
$fs = (isset($_GET['fs'])) ? $_GET['fs'] : "";
$fe = (isset($_GET['fe'])) ? $_GET['fe'] : "";
if ($fs != "") $fs = date("Y-m-d",strtotime($fs));
if ($fe != "") $fe = date("Y-m-d",strtotime($fe));

$cover = date("M j, Y",strtotime($fs));
if ($fe != "") $cover .= " to " . date("M j, Y",strtotime($fe));

require '../config.php';
$str_response = "";

?>
<tr><td colspan="9" align="center">Deposits</td></tr>
<tr><td colspan="9">Date:&nbsp;<?php echo $cover; ?></td></tr>
<tr><td>Branch</td><td>Date</td><td>Account Name</td><td>From</td><td>Cutoff</td><td>To</td><td>Cutoff</td><td>Amount</td><td>Note</td></tr>
</thead>
<tbody>
<?php

$sql = "select deposit_id, if(deposit_branch = 1,'Francey',if(deposit_branch = 2,'Yessamin','Francey')) branch, deposit_from, if(deposit_from_cutoff = 1,'First Cutoff','End of the day') from_co, deposit_to, if(deposit_to_cutoff = 1,'First Cutoff','End of the day') to_co, (select bank_account_name from bank_accounts where bank_account_id = deposit_account_name) account_name, deposit_amount, deposit_note, deposit_encoded_date, deposit_uid FROM deposits where deposit_id != 0";

$c1 = " and deposit_branch = $fbranch";
$c2 = " and deposit_account_name = $fan";
$c3a = " and deposit_encoded_date = '$fs'";
$c3ab = " and deposit_encoded_date >= '$fs' and deposit_encoded_date <= '$fe'";
$c3 = $c3a;
if ($fe != "") $c3 = $c3ab;

if ($fbranch == 0) $c1 = "";
if ($fan == 0) $c2 = "";
if (($fs == "") && ($fe == "")) $c3 = "";

$sql .= $c1 . $c2 . $c3;

db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;

$total_amount_deposited = 0;
if ($rc>0) {

for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$str_response .= '<tr>';
$str_response .= '<td>' . $rec['branch'] . '</td>';
$str_response .= '<td>' . date("M j, Y",strtotime($rec['deposit_encoded_date'])) . '</td>';
$str_response .= '<td>' . $rec['account_name'] . '</td>';
$str_response .= '<td>' . date("M j, Y",strtotime($rec['deposit_from'])) . '</td>';
$str_response .= '<td>' . $rec['from_co'] . '</td>';
$str_response .= '<td>' . date("M j, Y",strtotime($rec['deposit_to'])) . '</td>';
$str_response .= '<td>' . $rec['to_co'] . '</td>';
$str_response .= '<td>' . number_format($rec['deposit_amount'],2) . '</td>';
$total_amount_deposited += $rec['deposit_amount'];
$str_response .= '<td>' . $rec['deposit_note'] . '</td>';
$str_response .= '</tr>';
}

}
db_close();

echo $str_response;

?>
</tbody>
<tfoot>
<?php

$str_response = '<tr><td colspan="7">&nbsp;</td><td colspan="2">Total: Php ' . number_format($total_amount_deposited,2) . '</td></tr>';

echo $str_response;

?>
</tfoot>
</table>
</div>
</div>
</body>
</html>