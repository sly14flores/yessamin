<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>USAPAN NG PAGTITIWALA - Print</title>
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

#in-main #tran-info {
	width: 100%;
	border-collapse: collapse;
}

#tran-info td {
	padding: 0;
}

#in-main #tran-items {
	width: 100%;
	border-collapse: collapse;
	margin-top: 3px;
}

#tran-items td {
	padding: 1px;
	/*border: 1px solid #000;*/
}

#tran-items thead td {
	border-top: 1px solid #000;
	border-bottom: 1px solid #000;
}

#next-tdues {
	border-collapse: collapse;
	width: 100%
}

#next-tdues thead tr:nth-child(2) td {
	border-top: 1px solid #000;
	border-bottom: 1px solid #000;
}

#footer {
	margin-top: 1px;
}

#in-footer {
	text-align: center;
	padding: 0 3px;
}

#in-footer p:first-child {
	text-align: left;
	text-indent: 50px;
}

#in-footer ol {
	margin-top: 1px;
	padding-left: 50px;
	text-align: left;
}

#in-footer ol li ol {
	margin-top: 0;
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
$branch_add = ""; // "Public Market, San Jose, Narvacan, Ilocos Sur";
// $branch_prop = "Frances Mae de Guzman - Prop.";
$branch_prop = "Frances Mae de Guzman";
$branch_tin = ""; //"TIN NO. 929-575-265-0000 NON VAT";
break;

case 2: // yessamin boutique
$branch_name = "Yessamin Boutique & Gen. MDSE";
$branch_add = ""; // "Public Market, San Jose, Narvacan, Ilocos Sur";
// $branch_prop = "Elsa Juanita B. de Guzman - Prop.";
$branch_prop = "Elsa Juanita B. de Guzman";
$branch_tin = ""; // "TIN NO. 179-706-265-0000 VAT";
break;

case 3: // sta maria branch
$branch_name = "Francey Boutique & Gen. MDSE Branch 1";
$branch_add = ""; // "Public Market, Sta. Maria, Ilocos Sur";
// $branch_prop = "Frances Mae de Guzman - Prop.";
$branch_prop = "Frances Mae de Guzman";
$branch_tin = ""; // "TIN NO. 929-575-265-0000 NON VAT";
break;

}

?>
<div id="wrapper">
<div id="in-wrapper">
<div id="header">
<div id="in-header">
<?php

$tid = $_GET['tid'];
$cashd = 0;
$swap_amt = 0;
$tra_branch = 0;
$sql = "select tra_no, tra_branch, tra_did, tra_date, tra_due, credit_limit_bal, unli_terms, waive_penalty, tra_vat, tra_avond, customer_id, concat(customer_fname, ' ', substr(customer_mname,1,1), '. ', customer_lname) dealer, address, tra_cash, tra_cashd, civil_status, ";
$sql .= "round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2) swap_amt, ";
$sql .= "(ifnull((select sum(tra_item_gprice*(tra_item_qty-ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0))) from transaction_items where tra_item_trano = tra_no),0)) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) tra_gross_bal ";
$sql .= "from transactions left join customers on transactions.tra_did = customers.customer_id where tra_no = $tid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$trano = $rec['tra_no'];
$pdid = $rec['tra_did'];
$dealer = $rec['dealer'];
if ($rec['customer_id'] == 0) $dealer = "Walk-in Cash";
$address = $rec['address'];
$tradate = $rec['tra_date'];
$tradue = $rec['tra_due'];
$clbal = $rec['credit_limit_bal'];
$cstat = $rec['civil_status'];
$tvat = $rec['tra_vat'];
$tavond = $rec['tra_avond'];
$unlit = $rec['unli_terms'];
$waive = $rec['waive_penalty'];
$swap_amt = $rec['swap_amt'];
$tra_branch = $rec['tra_branch'];
}
if ($rc>0) $cashd = $rec['tra_cashd'];
$tcash = $rec['tra_cash'];
if (($tcash == 0) && ($unlit == 0) && ($waive == 0)) $duedate = date("F j, Y",strtotime($rec['tra_due']));
db_close();

?>
<div id="trano">TA.No.&nbsp;<?php echo $trano; ?></div>
<!--<p style="font-weight: bold;"><?php // echo $branch_name; ?></p>
<p><?php // echo $branch_add; ?></p>
<p><?php // echo $branch_prop; ?></p>
<p><?php // echo $branch_tin; ?></p>-->
<h3>USAPAN NG PAGTITIWALA</h3>
<h3 style="margin-bottom: 10px;">(Trust Agreement)</h3>
</div>
</div>
<div id="main">
<div id="in-main">
<table id="tran-info">
<tr><td>Entrusted to:&nbsp;<?php echo $dealer; ?></td><td>Date Issued:&nbsp;<?php echo date("F j, Y",strtotime($tradate)); ?></td><td>CL Balance:&nbsp;<?php echo $clbal; ?></td></tr>
<tr><td>Address:&nbsp;<?php echo $address; ?></td><td><?php if (($tcash == 0) && ($unlit == 0) && ($waive == 0)) echo "Due Date:&nbsp;$duedate"; else echo "COD"; ?></td><td>&nbsp;</td></tr>
</table>
<table id="tran-items">
<thead>
<tr><td>Company</td><td>Product Code</td><td>Name</td><td>Size</td><td>Qty</td><td>Price</td><td>Gross Amt</td><td>Discount</td><td>Net Sale</td><td>Remark</td></tr>
</thead>
<tbody>
<?php

$sql = "select tra_item_no, tra_item_sup, if((select tra_fullpaid from transactions where tra_no = $tid) != '0000-00-00',(select tra_fullpaid from transactions where tra_no = $tid),substr(now(),1,10)) now, (select supplier_desc from suppliers where supplier_id = tra_item_sup) supplier, tra_item_qty, tra_item_pcode, tra_item_pname, tra_item_psize, tra_item_gprice, tra_item_discount, is_borrowed, ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no group by dret_tino),0) returns, replaced, replacement, swapped_for from transaction_items where tra_item_trano = $tid";
$tnet = 0;
$titem = 0;
$isavon = 0;
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
for ($i=0; $i<$rc; ++$i) {
$net = 0;
$nb = "";
$rem = "";
$rec = $rs->fetch_array();
if ($rec['tra_item_sup'] == 2) $isavon = 1;

if ($rec['is_borrowed'] == 1) $nb = " - Borrowed";
$net = ( ((float)$rec['tra_item_gprice']) - ((float)$rec['tra_item_gprice'] * (float)(($rec['tra_item_discount'])/100)) ) * ($rec['tra_item_qty'] - $rec['returns']);
$str_response .= '<tr>';
$str_response .= '<td>' . $rec['supplier'] . '</td>';
$str_response .= '<td>' . $rec['tra_item_pcode'] . '</td>';
$str_response .= '<td>' . $rec['tra_item_pname'] . '</td>';
$str_response .= '<td>' . $rec['tra_item_psize'] . $nb . '</td>';
$str_response .= '<td>' . $rec['tra_item_qty'] .'</td>';
$str_response .= '<td>' . $rec['tra_item_gprice'] . '</td>';
$str_response .= '<td>' . round((float)$rec['tra_item_gprice']*($rec['tra_item_qty'] - $rec['returns']),2) . '</td>';
$str_response .= '<td>' . number_format($rec['tra_item_discount']) . '%</td>';
$str_response .= '<td>' . round($net,2) . '</td>';
if ($rec['returns'] > 0) $rem = $rec['returns'] . " item(s) returned";
if ($rec['replacement'] != 0) {
$nsql = "select concat('Replaced: ', tra_item_pcode, ' ', tra_item_pname, ' ', tra_item_psize) replacer from transaction_items where tra_item_no = " . $rec['replacement'];
$nrs = $db_con->query($nsql);
$nrc = $nrs->num_rows;
if ($nrc>0) {
	$nrec = $nrs->fetch_array();
	if ($rem == "") $rem = $nrec['replacer']; else $rem .= "/" . $nrec['replacer'];
}
}
if ($rec['swapped_for'] != 0) {
$nsql = "select concat('Payment for: ', tra_item_pcode, ' ', tra_item_pname, ' ', tra_item_psize) swap from transaction_items where tra_item_no = " . $rec['swapped_for'];
$nrs = $db_con->query($nsql);
$nrc = $nrs->num_rows;
if ($nrc>0) {
	$nrec = $nrs->fetch_array();
	if ($rem == "") $rem = $nrec['swap']; else $rem .= "/" . $nrec['swap'];
}
}
$str_response .= '<td>' . $rem . '</td>';
$str_response .= '</tr>';
$tnet = $tnet + round($net,2);
$titem = $titem + $rec['tra_item_qty'];
}
db_close();

$tnet = $tnet - ($swap_amt*2);
$str_response .= '<tr><td colspan="6">&nbsp;</td><td colspan="2" align="right">Total Item(s):</td><td align="center">' . $titem . '</td><td>&nbsp;</td></tr>';
$str_response .= '<tr><td colspan="6">&nbsp;</td><td colspan="2" align="right">Net Amount Due:</td><td align="center">' . round($tnet,2) . '</td><td>&nbsp;</td></tr>';
if ( ($tcash == 1) && ($isavon == 0) ) {
$tnet = $tnet - ( (float)($tnet * ($cashd/100)) );
$str_response .= '<tr><td colspan="6">&nbsp;</td><td colspan="2" align="right">Cash Discount:</td><td align="center">' . number_format($cashd) . '%</td><td>&nbsp;</td></tr>';
$str_response .= '<tr><td colspan="6">&nbsp;</td><td colspan="2" align="right">Total:</td><td align="center">' . round($tnet,2) . '</td><td>&nbsp;</td></tr>';
}
if (($tvat == 1.12) && ($tavond > 0)) {
$str_response .= '<tr><td colspan="6">&nbsp;</td><td colspan="2" align="right">Vat:</td><td align="center">' . $tvat . '</td><td>&nbsp;</td></tr>';
$str_response .= '<tr><td colspan="6">&nbsp;</td><td colspan="2" align="right">Add.Dis:</td><td align="center">' . number_format($tavond) . '%</td><td>&nbsp;</td></tr>';
$str_response .= '<tr><td colspan="6">&nbsp;</td><td colspan="2" align="right">Total:</td><td align="center">' . round(($tnet - ($tnet/$tvat*($tavond/100))),2) . '</td><td>&nbsp;</td></tr>';
}
echo $str_response;

?>
</tbody>
</table>
</div>
</div>
<table style="page-break-before: auto; page-break-inside: avoid;">
<tr><td>
<div id="footer">
<div id="in-footer">
<?php

$str_duedate = "";
$str_duedate_dot = "";
if (($tcash == 0) && ($unlit == 0) && ($waive == 0)) {
	$str_duedate = "($duedate) ";
	$str_duedate_dot = "($duedate)";
}	

if ($tra_branch == 2) $branch_prop = "Elsa Juanita B. de Guzman";
else $branch_prop = "Frances Mae de Guzman";

?>
<p>Ako <span style="border-bottom: 1px solid #000;"><?php echo $dealer; ?></span> PINAGKATIWALAAN, na kakatira sa <span style="border-bottom: 1px solid #000;"><?php echo $address; ?></span> ay tinanggap ang mga ipinakatiwalang produktong nakalista dito na nasa maayos na lagay galing kay <?php echo $branch_prop; ?>, NAGTIWALA, ayon sa sumusonod na termino at kondisyon</p>
<ol>
<li>Ako ay sumasangayon  na tinanggap ko ang mga ipinagkatiwalang produkto para ibenta. Ang pinagbentahan ay dapat kong ibayad kay <?php echo $branch_prop; ?> na nakabawas na ang napagusapang komisyon bago sa araw o sa itinakdang araw ng pagbayad <?php echo $str_duedate ?>na nakasaad sa dokumentong ito.</li>
<li>Kung hindi ko maibigay ang napagbentahan sa araw na napagkasunduan <?php echo $str_duedate ?>obligasyon kong bayaran ang kabuuang halaga ng mga kinuhang kong produkto at kanselado na ang dapat sanang komisyon ko.</li>
<li>Naintindihan ko na maaring gamitin ni <?php echo $branch_prop; ?> ang dokumentong ito sa korte kung hindi ko maibigay ang napagkasunduang kabayaran sa takdang araw ng pagbayad <?php echo $str_duedate_dot ?>.</li>
</ol>
<p style="width=100%; margin-top: 10px;"><span style="display: inline-block; width: 130px; margin-right: 180px; border-bottom: 1px solid #000; padding-bottom: 20px;">Checked by:</span><span style="display: inline-block; width: 130px; border-bottom: 1px solid #000; padding-bottom: 20px;">Printed Name:</span></p>
</div>
</div>
</td></tr>
</table>
<?php

$str_response  = '<table id="next-tdues">';
$str_response .= '<thead>';
$str_response .= '<tr><td colspan="6">You next due dates:</td></tr>';
$str_response .= '<tr><td width="15%">Date</td><td>Tra.No.</td><td>Amount</td><td>Penalty</td><td>Balance</td><td>Due Date</td></tr>';
$str_response .= '</thead>';
$sql = "select tra_date, tra_no, round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) amount, round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) balance, round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) total_payment, tra_cash, unli_terms, waive_penalty, tra_due, ";
$sql .= "if(tra_fullpaid != '0000-00-00',tra_fullpaid,substr(now(),1,10)) now, ";
$sql .= "(ifnull((select sum(tra_item_gprice*(tra_item_qty-ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0))) from transaction_items where tra_item_trano = tra_no),0)) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) tra_gross_bal ";
$sql .= "from transactions left join customers on transactions.tra_did = customers.customer_id where customer_id = $pdid and tra_cash = 0 and tra_no != $tid ";
$sql .= "and ((round(((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */) - /* net/vat*avon discount*cash discount  */ ((/* net */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no) /* <- */ - /* net/vat*avon discount */ (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * (tra_item_qty - ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no),0)),2)) from transaction_items where tra_item_trano = tra_no)/tra_vat*(tra_avond/100) /* <- */)*(tra_cashd/100))),2) - (round(ifnull((select sum(((sp_gp - (sp_gp*(sp_discount/100)))*sp_qty)) from swapped_products where sp_trano = tra_no),0),2)*2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2)) > 0)";
// $sql .= "and tra_fullpaid = '0000-00-00'";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();

// penalty computation
$pen = 0;
$bal = 0;
$lessd = 0;

$bal = $rec['balance'];

if (($rec['tra_cash'] == 0) && ($rec['unli_terms'] == 0) && ($rec['waive_penalty'] == 0)) {
	if (strtotime($rec['now']) > strtotime($rec['tra_due'])) {
	$pd = (strtotime($rec['now']) - strtotime($rec['tra_due']));
	$dayp = $pd/86400;

	if (($dayp>0) && ($dayp<=7)) $lessd = 5; // 1 - 7 days

	if (($dayp>7) && ($dayp<=14)) $lessd = 10; // 8 - 14 days

	if (($dayp>14) && ($dayp<=21)) $lessd = 15; // 15 - 21 days

	if (($dayp>21) && ($dayp<=29)) $lessd = 20; // 22 - 29 days
	
	if ($dayp>29) $lessd = 0; // beyond 29 days	

	$pen = round(($bal*($lessd/100)),2);
	if ($bal<0) $pen = 0;
	
	$bal = $bal + $pen;
	if ($dayp>29) $bal = $rec['tra_gross_bal'];
	
	}
}	
//

$str_response .= '<tr>';
$str_response .= '<td>' . date("F j, Y",strtotime($rec['tra_date'])) . '</td>';
$str_response .= '<td>' . $rec['tra_no'] . '</td>';
$str_response .= '<td>' . $rec['amount'] . '</td>';
$str_response .= '<td>' . $pen . '</td>';
if ($bal < 0) $bal = 0;
$str_response .= '<td>' . $bal . '</td>';
$str_response .= '<td>' . date("F j, Y",strtotime($rec['tra_due'])) . '</td>';
$str_response .= '</tr>';
if ($i == 4) break;
}
db_close();
$str_response .= '<tbody>';
$str_response .= '</tbody>';
$str_response .= '</table>';
if (($rc>0) && ($unlit == 0) && ($waive == 0)) echo $str_response;

?>
</div>
</div>
</body>
</html>