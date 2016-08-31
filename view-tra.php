<?php

$tid = $_GET['tid'];

session_start();
require 'config.php';
require 'globalf.php';
require 'grants.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $tid; ?></title>
<style type="text/css">
* {
	margin: 0;
	padding: 0;
}

body {
	background-color: #fbf8fa;
	font: 12px sans-serif;
}

#content {
	width: 95%;
	margin: 20px auto 0 auto;
	border: 1px solid #c9c7c7;
	border-radius: 5px;	
	background-color: #fff;
}

#in-content {
	padding: 10px;
	color: #265fb9;
}

#tab-view-tra {
	border-collapse: collapse;
	width: 100%;
}

#tab-view-tra thead td {
	border: 1px solid #fa17ae;
	padding: 5px;
}

#tab-view-tra tbody td {
	border: 1px solid #fa17ae;
	padding: 5px;
}
</style>
<link rel="icon" type="image/ico" href="favicon.ico" />
<link rel="shortcut icon" href="favicon.ico" />
<link href="jquery/css/start/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="jquery/js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="js/JSONSuggestBox2/jquery.jsonSuggest-2.js"></script>
<script type="text/javascript" src="js/dialogbox.js"></script>
<script type="text/javascript" src="js/globalf.js"></script>
<script type="text/javascript">
$(function() {

$('#clear_payment').button();
$('#clear_payment').click(function() {
	confirmClearPayment('<?php echo $tid; ?>');
});

$('#clear_payment_return').button();
$('#clear_payment_return').click(function() {
	confirmClearPaymentReturn('<?php echo $tid; ?>');
});

$('#clear_payment_replace').button();
$('#clear_payment_replace').click(function() {
	confirmClearPaymentReplace('<?php echo $tid; ?>');
});

$('#unclear_payment').button();
$('#unclear_payment').click(function() {
	confirmUnclearPayment('<?php echo $tid; ?>');
});

$('#unclear_payment_return').button();
$('#unclear_payment_return').click(function() {
	confirmUnclearPayment('<?php echo $tid; ?>');
});

$('#unclear_payment_replace').button();
$('#unclear_payment_replace').click(function() {
	confirmUnclearPayment('<?php echo $tid; ?>');
});

$('#archive').button();
$('#archive').click(function() {
	confirmArchive();
});

});

// clear
function confirmClearPayment(id) {

var m = 'Clear this TN?';
var f = function() { clearPayment(id); };
confirmation(300,200,m,f);

}

function clearPayment(id) {

$.ajax({
	url: 'transaction.php?p=clear_payment',
	type: 'post',
	data: {tid: id},
	success: function(data, status) {
	notify(300,150,data);
	}
});

}
//

// clear return
function confirmClearPaymentReturn(id) {

var m = 'Clear this TN?';
var f = function() { clearPaymentReturn(id); };
confirmation(300,200,m,f);

}

function clearPaymentReturn(id) {

$.ajax({
	url: 'transaction.php?p=clear_payment_return',
	type: 'post',
	data: {tid: id},
	success: function(data, status) {
	notify(300,150,data);
	}
});

}
//

// clear replace
function confirmClearPaymentReplace(id) {

var m = 'Clear this TN?';
var f = function() { clearPaymentReplace(id); };
confirmation(300,200,m,f);

}

function clearPaymentReplace(id) {

$.ajax({
	url: 'transaction.php?p=clear_payment_replace',
	type: 'post',
	data: {tid: id},
	success: function(data, status) {
	notify(300,150,data);
	}
});

}
//

function confirmUnclearPayment(id) {

var m = 'Unclear this TN?';
var f = function() { unclearPayment(id); };
confirmation(300,200,m,f);

}

function unclearPayment(id) {

$.ajax({
	url: 'transaction.php?p=unclear_payment',
	type: 'post',
	data: {tid: id},
	success: function(data, status) {
	notify(300,150,data);
	}
});

}

function confirmArchive() {

var m = 'Are you sure you want to archive this TN?';
var f = function() { archive(); };
confirmation(350,180,m,f);

}

function archive() {

var id = $('#htid').val();

$.ajax({
	url: 'transaction.php?p=archive_tra',
	type: 'post',
	data: {ptid: id},
	success: function(data, status) {
		notify(300,200,data);
	}
});

}

</script>
</head>
<body>
<div id="content">
<div id="in-content">
<table id="tab-view-tra">
<thead>
<?php

$str_response = "";
$fullpaid = "";
$tstat = "";
$swap_amt = 0;
$sql = "select tra_no, tra_date, tra_due, tra_fullpaid, ";
$sql .= "round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) balance, unli_terms, waive_penalty, tra_vat, tra_avond, customer_id, if(tra_fullpaid = '0000-00-00',substr(now(),1,10),tra_fullpaid) now, concat(customer_fname, ' ', substr(customer_mname,1,1), '. ', customer_lname) dealer, recruiter, credit_limit_bal, tra_cash, tra_cashd, if(tra_fullpaid = '0000-00-00','',date_format(tra_fullpaid,'%M %e, %Y')) date_paid, round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2) swap_amt, ";
$sql .= "(ifnull((select sum(tra_item_gprice*(tra_item_qty-ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0))) from transaction_items where tra_item_trano = tra_no),0)) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) tra_gross_bal ";
$sql .= "from transactions left join customers on transactions.tra_did = customers.customer_id where tra_no = $tid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$dealer = $rec['dealer'];
if ($rec['customer_id'] == 0) $dealer = "Walk-in Cash";
$tradue = $rec['tra_due'];
$tvat = $rec['tra_vat'];
$tavond = $rec['tra_avond'];
$cashd = $rec['tra_cashd'];
$tcash = $rec['tra_cash'];
$unlit = $rec['unli_terms'];
$waive = $rec['waive_penalty'];
$bal = $rec['balance'];
$tra_gross_bal = $rec['tra_gross_bal'];
$swap_amt = $rec['swap_amt'];
$fullpaid = $rec['tra_fullpaid'];
$dayp = 0;
if (($unlit == 0) && ($waive == 0)) { // skip for unlimited terms & if penalty is waived
	if ($rec['now'] == $rec['tra_due']) $tstat = "Due today";
	if (strtotime($rec['now']) > strtotime($tradue)) {
	$pd = (strtotime($rec['now']) - strtotime($tradue));
	$dayp = $pd/86400;
	$tstat = "$dayp day(s) due";
	}
}
if ($fullpaid != '0000-00-00') $tstat = "Cleared";
$str_response = '<tr><td colspan="2">Tra.No:&nbsp;' . $rec['tra_no'] . '</td><td>Date:&nbsp;' . date("F j, Y",strtotime($rec['tra_date'])) . '</td><td>Status:&nbsp;' . $tstat . '</td><td colspan="2">Due Date:&nbsp;';
if ( ($tcash == 0) && ($unlit == 0) && ($waive == 0) ) $str_response .= date("F j, Y",strtotime($rec['tra_due'])); else $str_response .= 'na';
$str_response .= '</td><td colspan="2">Date Paid: ' . $rec['date_paid'] . '</td></tr>';
$str_response .= '<tr><td colspan="2">Dealer\'s Name:&nbsp;' . $dealer . '</td><td colspan="3">Recruiter:&nbsp;' . $rec['recruiter'] . '</td><td colspan="6">Credit Limit Balance:&nbsp;' . $rec['credit_limit_bal'] . '</td></tr>';
}
db_close();

?>
</thead>
<tbody>
<?php

$sql = "select tra_item_no, tra_item_sino, tra_item_trano, tra_item_sup, if((select tra_fullpaid from transactions where tra_no = $tid) != '0000-00-00',(select tra_fullpaid from transactions where tra_no = $tid),substr(now(),1,10)) now, (select supplier_desc from suppliers where supplier_id = tra_item_sup) supplier, tra_item_qty, tra_item_pcode, tra_item_pname, tra_item_psize, tra_item_gprice, tra_item_discount, is_borrowed, ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no group by dret_tino),0) returns, replaced, replacement, swapped_for from transaction_items where tra_item_trano = $tid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$tgross = 0;
$tqty = 0;
$tnet = 0;
$isavon = 0;
$str_response .= '<tr style="background-color: #fadab8;"><td>Company</td><td>Quantity</td><td>Product Description</td><td>Unit Price</td><td>Gross Amount</td><td>Discount</td><td>Net Price</td><td>Returns</td></tr>';
for ($i=0; $i<$rc; ++$i) {
$net = 0;
$nb = "";
$rec = $rs->fetch_array();
if ($rec['tra_item_sup'] == 2) $isavon = 1;
// penalty computation
$lessd = 0;

if ( ($tcash == 0) && ($unlit == 0) && ($waive == 0)) {
	if (strtotime($rec['now']) > strtotime($tradue)) {

		if (($dayp>0) && ($dayp<=7)) $lessd = 5; // 1 - 7 days

		if (($dayp>7) && ($dayp<=14)) $lessd = 10; // 8 - 14 days

		if (($dayp>14) && ($dayp<=21)) $lessd = 15; // 15 - 21 days

		if (($dayp>21) && ($dayp<=29)) $lessd = 20; // 22 - 29 days
		
		if ($dayp>29) $lessd = 0; // beyond 29 days

	}
}
//
$tqty = $tqty + $rec['tra_item_qty']; // total items
if ($rec['is_borrowed'] == 1) $nb = " - Borrowed";
$idesc = $rec['tra_item_pcode'] . ' ' . $rec['tra_item_pname'] . ' ' . $rec['tra_item_psize'];

if ($rec['replacement'] != 0) {
$nsql = "select concat(' - replaced: ', tra_item_pcode, ' ', tra_item_pname, ' ', tra_item_psize) replacer from transaction_items where tra_item_no = " . $rec['replacement'];
$nrs = $db_con->query($nsql);
$nrc = $nrs->num_rows;
if ($nrc>0) {
	$nrec = $nrs->fetch_array();
	$idesc .= $nrec['replacer'];
}
}

if ($rec['swapped_for'] != 0) {
$nsql = "select concat(' - payment for: ', tra_item_pcode, ' ', tra_item_pname, ' ', tra_item_psize) swap from transaction_items where tra_item_no = " . $rec['swapped_for'];
$nrs = $db_con->query($nsql);
$nrc = $nrs->num_rows;
if ($nrc>0) {
	$nrec = $nrs->fetch_array();
	$idesc .= $nrec['swap'];
}
}

$net = ( ((float)$rec['tra_item_gprice']) - ((float)$rec['tra_item_gprice'] * (float)(($rec['tra_item_discount'])/100)) ) * ($rec['tra_item_qty'] - $rec['returns']);
$str_response .= '<tr>';
$str_response .= '<td>' . $rec['supplier'] . '</td>';
$str_response .= '<td>' . $rec['tra_item_qty'] . '</td>';
$str_response .= '<td>' . $idesc . $nb . '</td>';
$str_response .= '<td>' . $rec['tra_item_gprice'] . '</td>';
$tgross += $rec['tra_item_gprice'] * ($rec['tra_item_qty']-$rec['returns']);
$str_response .= '<td>' . round((float)$rec['tra_item_gprice']*($rec['tra_item_qty'] - $rec['returns']),2) . '</td>';
$str_response .= '<td>' . number_format($rec['tra_item_discount']) . '%</td>';
$str_response .= '<td>' . round($net,2) . '</td>';
$str_response .= '<td>' . $rec['returns'] . '</td>';
$str_response .= '</tr>';
$tnet = $tnet + round($net,2);
}
$str_response .= '<tr>';
$str_response .= '<td colspan="4">&nbsp;</td><td>Total: ' . number_format($tgross,2)  . '</td><td colspan="3"></td>';
$str_response .= '</tr>';
$tnet = $tnet - ($swap_amt*2);
$str_response .= '<tr>';
$str_response .= '<td style="border: 0;" colspan="5">&nbsp;</td>';
$str_response .= '<td style="border: 0;" colspan="2">Total Item(s):</td>';
$str_response .= '<td style="border: 0;">' . $tqty . '</td>';
$str_response .= '</tr>';
$str_response .= '<tr>';
$str_response .= '<td style="border: 0;" colspan="5">&nbsp;</td>';
$str_response .= '<td style="border: 0;" colspan="2">Net Amt Due:</td>';
$str_response .= '<td style="border: 0;">' . round($tnet,2) . '</td>';
$str_response .= '</tr>';
$str_response .= '<tr>';
$str_response .= '<td style="border: 0;" colspan="5">&nbsp;</td>';
$str_response .= '<td style="border: 0;" colspan="2">Cash Discount:</td>';
$str_response .= '<td style="border: 0;">' . number_format($cashd) . '%</td>';
$str_response .= '</tr>';
if ( ($tcash == 1) && ($isavon == 0) ) $tnet = $tnet - ( (float)($tnet * ($cashd/100)) );
$str_response .= '<tr>';
$str_response .= '<td style="border: 0;" colspan="5">&nbsp;</td>';
$str_response .= '<td style="border: 0;" colspan="2">Vat:</td>';
$str_response .= '<td style="border: 0;">' . $tvat . '</td>';
$str_response .= '</tr>';
$str_response .= '<tr>';
$str_response .= '<td style="border: 0;" colspan="5">&nbsp;</td>';
$str_response .= '<td style="border: 0;" colspan="2">Add.Dis:</td>';
$str_response .= '<td style="border: 0;">' . number_format($tavond) . '%</td>';
$str_response .= '</tr>';
$pen = 0;
$pen = $bal*($lessd/100);
if ($bal<0) $pen = 0;
if ($dayp>29) $pen = $tra_gross_bal;
$str_response .= '<tr>';
$str_response .= '<td style="border: 0;" colspan="5">&nbsp;</td>';
$str_response .= '<td style="border: 0;" colspan="2">Penalty:</td>';
$str_response .= '<td style="border: 0;">' . round($pen,2) . '</td>';
$str_response .= '</tr>';
$str_response .= '<tr>';
$str_response .= '<td style="border: 0;" colspan="5">&nbsp;</td>';
$str_response .= '<td style="border: 0;" colspan="2">Total:</td>';
$str_response .= '<td style="border: 0;">' . round(($tnet - ($tnet/$tvat*($tavond/100))),2) . '</td>';
$str_response .= '</tr>';
}
db_close();

echo $str_response;

?>
</tbody>
<tfoot>
</tfoot>
</table>
<?php

if ((substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) || (substr_count($USER_GRANTS,$ADMIN_ASSISTANT_GRANT) == 1)) {

$b_id = "clear_payment";
$b_value = "Clear";
$b_id1 = "clear_payment_return";
$b_value1 = "Clear (Return)";
$b_id2 = "clear_payment_replace";
$b_value2 = "Clear (Replace)";

if ($fullpaid != "0000-00-00") {
	$b_id = "unclear_payment";
	$b_value = "Unclear";
	$b_id1 = "unclear_payment_return";
	$b_value1 = "Unclear (Return)";
	$b_id2 = "unclear_payment_replace";
	$b_value2 = "Unclear (Replace)";	
}

$str_response = '<div>';
$str_response .= '<input type="button" id="' . $b_id . '" value="' . $b_value . '" />';
$str_response .= '&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" id="' . $b_id1 . '" value="' . $b_value1 . '" />';
$str_response .= '&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" id="' . $b_id2 . '" value="' . $b_value2 . '" />';
if (substr_count($USER_GRANTS,$ADMIN_GRANT) == 1) $str_response .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" id="archive" value="ARCHIVE" />';
$str_response .= '<input type="hidden" id="htid" value="' . $tid . '" />';
$str_response .= '</div>';

echo $str_response;

}

?>
</div>
</div>
<!--dialog boxes-->
<div id="main_dialog"></div>
<div id="sub_dialog"></div>
<div id="confirm_dialog"></div>
<div id="notify_dialog"></div>
<!--end dialog boxes-->
</body>
</html>