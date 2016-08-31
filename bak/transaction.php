<?php

session_start();
$uid = $_SESSION['user_id'];
$tbranch = $_SESSION['branch'];

require 'config.php';
require 'globalf.php';

$req = "";
$START_T = "START TRANSACTION;";
$END_T = "COMMIT;";

$CONST_DD = 37; // due date

if (isset($_GET["p"])) $req = $_GET["p"];

$str_response = "";
$json = "";
$jpage = "";

switch ($req) {

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

case "add":
$pdid = (isset($_POST['pdid'])) ? $_POST['pdid'] : 0;
$piscash = (isset($_POST['piscash'])) ? $_POST['piscash'] : 0;
$ptclimit = (isset($_POST['ptclimit'])) ? $_POST['ptclimit'] : 0;
$ptclimit = round($ptclimit,2);
$pcd = (isset($_POST['pcd'])) ? $_POST['pcd'] : 0;
$ptrai = (isset($_POST['ptrai'])) ? $_POST['ptrai'] : "";
$pnr = (isset($_POST['pnr'])) ? $_POST['pnr'] : 0;

$sql = "alter table transactions AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "insert into transactions (tra_date, tra_branch, tra_did, tra_cash, tra_cashd, tra_due, tra_uid) ";
$sql .= "values (CURRENT_TIMESTAMP, $tbranch, $pdid, $piscash, $pcd, ADDDATE(CURRENT_TIMESTAMP, INTERVAL $CONST_DD DAY), $uid)";
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

$sql = "insert into transaction_items (tra_item_trano, tra_item_sup, tra_item_qty, tra_item_pcode, tra_item_pname, tra_item_psize, tra_item_gprice, tra_item_discount, tra_item_vat, tra_item_add_discount, tra_item_sino) values ";
$eptrai = explode("|",$ptrai);
for ($i=0; $i<$pnr; ++$i) {
$ti = explode(",",$eptrai[$i]);
$sql .= "($ltra, " . $ti[0] . ", " . $ti[1] . ", '" . $ti[2] . "', '" . $ti[3] . "', '" . $ti[4] . "', " . $ti[5] . ", " . $ti[6] . ", " . $ti[7] . ", " . $ti[8] . ", " . $ti[9] . "),";
}

$sql = substr($sql,0,strlen($sql)-1);
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "$ltra";

echo $str_response;
break;

case "view":
$tid = $_GET['tid'];

$titra = 0;
$sql = "select tra_no, tra_date, tra_due, concat(customer_fname, ' ', substr(customer_mname,1,1), '. ', customer_lname) dealer, recruiter, credit_limit_bal, tra_cash, tra_cashd from transactions left join customers on transactions.tra_did = customers.customer_id where tra_no = $tid";
$json = '{ "edittransaction": [';
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"jtn":"' . $rec['tra_no'] . '",';
$json .= '"jtd":"' . date("F j, Y",strtotime($rec['tra_date'])) . '",';
$json .= '"jtdd":"' . date("F j, Y",strtotime($rec['tra_due'])) . '",';
$json .= '"jtdn":"' . $rec['dealer'] . '",';
$json .= '"jtdr":"' . $rec['recruiter'] . '",';
$json .= '"jtcl":' . $rec['credit_limit_bal'] . ',';
$json .= '"jtpt":' . $rec['tra_cash'] . ',';
$json .= '"jtcd":' . $rec['tra_cashd'] . '';
$json .= '}';
db_close();

$json .= '], "traitems": [';
$sql = "select tra_item_no, tra_item_sino, tra_item_trano, tra_item_sup, (select supplier_desc from suppliers where supplier_id = tra_item_sup) supplier, tra_item_qty, tra_item_pcode, tra_item_pname, tra_item_psize, tra_item_gprice, tra_item_discount, tra_item_vat, tra_item_add_discount, ifnull((select sum(dret_qty) from dealers_returns where dret_tino = tra_item_no group by dret_tino),0) returns from transaction_items where tra_item_trano = $tid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"jtin":' . $rec['tra_item_no'] . ',';
$json .= '"jtsino":"' . $rec['tra_item_sino'] . '",';
$json .= '"jtrano":"' . $rec['tra_item_trano'] . '",';
$json .= '"jtis":"' . $rec['supplier'] . '",';
$json .= '"jtisid":' . $rec['tra_item_sup'] . ',';
$json .= '"jtiq":' . $rec['tra_item_qty'] . ',';
$json .= '"jtpc":"' . $rec['tra_item_pcode'] . '",';
$json .= '"jtpn":"' . $rec['tra_item_pname'] . '",';
$json .= '"jtps":"' . $rec['tra_item_psize'] . '",';
$json .= '"jtii":"' . $rec['tra_item_pcode'] . ' ' . $rec['tra_item_pname'] . ' ' . $rec['tra_item_psize'] . '",';
$json .= '"jtip":' . $rec['tra_item_gprice'] . ',';
$json .= '"jtid":' . $rec['tra_item_discount'] . ',';
$json .= '"jtvat":' . $rec['tra_item_vat'] . ',';
$json .= '"jtadd":' . $rec['tra_item_add_discount'] . ',';
$json .= '"jtret":' . $rec['returns'] . '';
$json .= '},';
}
db_close();
$json = substr($json,0,strlen($json)-1);
$json .= '] }';

echo $json;
break;

case "contents":

$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

$sql = "select count(*) mcount from transactions left join customers on transactions.tra_did = customers.customer_id where tra_no != 0";
$pql = "select tra_no, tra_cash, tra_branch, tra_date, (select concat(firstname, ' ', lastname) from users where user_id = tra_uid) cashier, concat(customer_fname, ' ', substr(customer_mname,1,1), '. ', customer_lname) dealer, tra_due, tra_receipt_no, /* -> */round(((select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2)) from transaction_items where tra_item_trano = tra_no) - (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2))/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no)) /* <-(net price) - net price/vat*(discount) */ - /* -> */((select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2)) from transaction_items where tra_item_trano = tra_no) - (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2))/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no))*(tra_cashd/100),2) amount, /* -> */round(((select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2)) from transaction_items where tra_item_trano = tra_no) - (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2))/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no)) /* <-(net price) - net price/vat*(discount) */ - /* -> */((select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2)) from transaction_items where tra_item_trano = tra_no) - (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2))/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no))*(tra_cashd/100),2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) balance from transactions left join customers on transactions.tra_did = customers.customer_id where tra_no != 0";

// filters
$fptype = (isset($_GET['fptype'])) ? $_GET['fptype'] : -1;
$fbranch = (isset($_GET['fbranch'])) ? $_GET['fbranch'] : 0;
$fdate = (isset($_GET['fdate'])) ? $_GET['fdate'] : "";
if ($fdate != "") $fdate = date("Y-m-d",strtotime($fdate));
$ftrano = (isset($_GET['ftrano'])) ? $_GET['ftrano'] : 0;
$fcustomer = (isset($_GET['fcustomer'])) ? $_GET['fcustomer'] : "";
$und = (isset($_GET['und'])) ? $_GET['und'] : 0;

$c1 = " and tra_cash = $fptype";
$c2 = " and tra_branch = $fbranch";
$c3 = " and tra_date like '$fdate%'";
$c4 = " and tra_no = $ftrano";
$c5 = " and concat(customer_fname, ' ', customer_lname) like '%$fcustomer%'";
$c6 = " and ((round(((select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2)) from transaction_items where tra_item_trano = tra_no) - (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2))/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no)) /* <-(net price) - net price/vat*(discount) */ - /* -> */((select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2)) from transaction_items where tra_item_trano = tra_no) - (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2))/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no))*(tra_cashd/100),2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2)) > 0)";

if ($fptype == -1) $c1 = "";
if ($fbranch == 0) $c2 = "";
if ($fdate == "") $c3 = "";
if ($ftrano == 0) $c4 = "";
if ($fcustomer == "") $c5 = "";
if ($und == 0) $c6 = "";

$sql .= $c1 . $c2 . $c3 . $c4 . $c5 . $c6;
$pql .= $c1 . $c2 . $c3 . $c4 . $c5 . $c6;
//

$sql .= " order by tra_date";
$pql .= " order by tra_date";

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
$tbr = "Francey";
$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
if ($rec['tra_branch'] ==2) $tbr = "Yessamin";
$pt = ($rec['tra_cash'] == 1) ? "Cash" : "Terms";
$bal = "n/a";
$pen = "n/a";
$tstat = "";
$dd = "n/a";
if ($rec['tra_cash'] == 0) {
$bal = $rec['balance'];
if ($bal == 0) $tstat = "Paid";
$pen = "0";
$dd = date("F j, Y",strtotime($rec['tra_due']));
}
if ($rec['tra_receipt_no'] != 0) $tstat = "Paid";
$str_response .= '<tr class="' . $rowstyle . '" onclick="chkRow(this);">';
$str_response .= '<td><input type="checkbox" name="chk_' . $rec['tra_no'] . '" id="chk_' . $rec['tra_no'] . '" onClick="Uncheck_Parent(\'chk_checkall\',this);" /></td>';
$str_response .= '<td>' . $pt . '</td>';
$str_response .= '<td>' . $tbr . '</td>';
$str_response .= '<td>' . date("F j, Y",strtotime($rec['tra_date'])) . '</td>';
$str_response .= '<td>' . $rec['cashier'] . '</td>';
$str_response .= '<td>' . $rec['tra_no'] . '</td>';
$str_response .= '<td>' . $rec['dealer'] . '</td>';
$str_response .= '<td>' . $rec['amount'] . '</td>';
$str_response .= '<td>' . $bal . '</td>';
$str_response .= '<td>' . $pen . '</td>';
$str_response .= '<td>' . $tstat . '</td>';
$str_response .= '<td>' . $dd . '</td>';
$str_response .= '<td>';
$str_response .= '<a href="javascript: printTran(\'' . $rec['tra_no'] . '\')" class="tooltip-min"><img src="images/print-16.png" /><span>Print this TRA</span></a>';
$str_response .= '<a href="javascript: viewTran(\'' . $rec['tra_no'] . '\')" class="tooltip-min"><img src="images/view-16.png" /><span>View this TRA</span></a>';
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
$sql = "select customer_id, concat(customer_fname, ' ', customer_lname) fullname, recruiter, customer_credit_limit, credit_limit_bal, discount_type, credit_limit_type from customers";

$json = '[';
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"id":' . $rec['customer_id'] . ',';
$json .= '"text":"' . addslashes($rec['fullname']) . '",';
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

case "list_products":
$sql = "select stock_in_id, concat(stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) item, concat(stock_in_pcode, ' ', stock_in_pname, ' ', supplier_desc, ' ', stock_in_psize, ' ', stock_in_price) product, concat(stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) item_desc, stock_in_pcode, stock_in_pname, supplier_id, supplier_desc, stock_in_psize, stock_in_price, sin_date_invoice, stock_in_quantity, ifnull(stock_in_quantity,0) - ifnull((select sum(tra_item_qty) from transaction_items where tra_item_sino = stock_in_id group by stock_in_id),0) + ifnull((select sum(dret_qty) from dealers_returns where dret_sino = stock_in_id group by dret_sino),0) - ifnull((select sum(retc_qty) from returns_companies where retc_sino = stock_in_id group by retc_sino),0) stocks from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id left join suppliers on stock_in.sin_supid = suppliers.supplier_id order by sin_date_invoice, stock_in_id";

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
$list_txt[$i] = $rec['product'] . " DATE: " . date("F j",strtotime($rec['sin_date_invoice'])) . " STOCKS: " . $rec['stocks'];
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
if ($list_stock[$i] == 0) continue;
$json .= '{';
$json .= '"id":"' . $list_id[$i] . '",';
$json .= '"text":"' . $list_txt[$i] . '",';
$json .= '"jsid":' . $list_supid[$i] . ',';
$json .= '"jsd":"' . $list_supd[$i] . '",';
$json .= '"jpd":"' . $list_item[$i] . '",';
$json .= '"jpc":"' . $list_code[$i] . '",';
$json .= '"jpn":"' . $list_name[$i] . '",';
$json .= '"jps":"' . $list_size[$i] . '",';
$json .= '"jpp":' . $list_price[$i] . '';
$json .= '},';
}
$json = substr($json,0,(strlen($json)-1));
}
$json .= ']';

db_close();

echo $json;
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
	
	}

db_close();

$json = '{"dtype":[';
$json .= '{"jds":' . $itemd  . '}';
$json .= ']}';

echo $json;
break;

case "avon_discount":
$avonc = $_GET['acat'];
$ddid = $_GET['ddid'];
$br = $_GET['br'];
$vat = 1;
$avond = 0;
$dap = 0;


switch ($avonc) {

case "CFT":
/*
*	1000 to 5199.99 = 5%
*	5200 to 7499.99 = 10%
*	7500 to 9899.99 = 15%
*	9900 to 25499.99 = 20%
*	25500 and above = 22.5%
*/
$sql = "select ifnull(round(((select sum((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty) from transaction_items where tra_item_trano = tra_no and tra_item_pcode like '$avonc-%') - (select sum((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty)/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no and tra_item_pcode like '$avonc-%')) /* <-(net price) - net price/vat*(discount) */ - /* -> */((select sum((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty) from transaction_items where tra_item_trano = tra_no and tra_item_pcode like '$avonc-%') - (select sum((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty)/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no and tra_item_pcode like '$avonc-%'))*(tra_cashd/100),2),0) amount from transactions where tra_did = $ddid and tra_date >= date_sub(curdate(), interval day(curdate())-1 day)";
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
break;

case "NCFT":
/*
*	1000 to 5199.99 = 5%
*	5200 to 7799.99 = 10%
*	7800 and above = 15%
*/
$sql = "select ifnull(round(((select sum((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty) from transaction_items where tra_item_trano = tra_no and tra_item_pcode like '$avonc-%') - (select sum((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty)/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no and tra_item_pcode like '$avonc-%')) /* <-(net price) - net price/vat*(discount) */ - /* -> */((select sum((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty) from transaction_items where tra_item_trano = tra_no and tra_item_pcode like '$avonc-%') - (select sum((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty)/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no and tra_item_pcode like '$avonc-%'))*(tra_cashd/100),2),0) amount from transactions where tra_did = $ddid and tra_date >= date_sub(curdate(), interval day(curdate())-1 day)";
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
if (($dap >= 5200) && ($dap <= 7799.99)) { $vat = 1.12; $avond = 10; } // 10%
if ($dap >= 7800) { $vat = 1.12; $avond = 15; } // 15%
break;

case "HCMS":
/*
*	1000 to 5199.99 = 5%
*	5200 to 7799.99 = 10%
*	7800 and above = 15%
*/
$sql = "select ifnull(round(((select sum((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty) from transaction_items where tra_item_trano = tra_no and tra_item_pcode like '$avonc-%') - (select sum((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty)/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no and tra_item_pcode like '$avonc-%')) /* <-(net price) - net price/vat*(discount) */ - /* -> */((select sum((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty) from transaction_items where tra_item_trano = tra_no and tra_item_pcode like '$avonc-%') - (select sum((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty)/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no and tra_item_pcode like '$avonc-%'))*(tra_cashd/100),2),0) amount from transactions where tra_did = $ddid and tra_date >= date_sub(curdate(), interval day(curdate())-1 day)";
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
if (($dap >= 5200) && ($dap <= 7799.99)) { $vat = 1.12; $avond = 10; } // 10%
if ($dap >= 7800) { $vat = 1.12; $avond = 15; } // 15%
break;

case "HC":
/*
*	1 to 999.99 = 5%
*	1000 to 2499.99 = 10%
*	2500 and above = 15%
*/
$sql = "select ifnull(round(((select sum((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty) from transaction_items where tra_item_trano = tra_no and tra_item_pcode like '$avonc-%') - (select sum((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty)/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no and tra_item_pcode like '$avonc-%')) /* <-(net price) - net price/vat*(discount) */ - /* -> */((select sum((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty) from transaction_items where tra_item_trano = tra_no and tra_item_pcode like '$avonc-%') - (select sum((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty)/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no and tra_item_pcode like '$avonc-%'))*(tra_cashd/100),2),0) amount from transactions where tra_did = $ddid and tra_date >= date_sub(curdate(), interval day(curdate())-1 day)";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$dap = $dap + $rec['amount'];
}
db_close();
$dap = $dap + $br;
if (($dap >= 1) && ($dap <= 999.99)) { $vat = 1.12; $avond = 5; } // 5%
if (($dap >= 1000) && ($dap <= 2499.99)) { $vat = 1.12; $avond = 10; } // 10%
if ($dap >= 2500) { $vat = 1.12; $avond = 15; } // 15%
break;

}

$json = '{"adis":[';
$json .= '{"jvat":' . $vat . ',"jads":' . $avond  . '}';
$json .= ']}';

echo $json;
break;

case "delete":
$tid = $_POST['tid'];

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
$sql = "select /* -> */round(((select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2)) from transaction_items where tra_item_trano = tra_no) - (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2))/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no)) /* <-(net price) - net price/vat*(discount) */ - /* -> */((select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2)) from transaction_items where tra_item_trano = tra_no) - (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2))/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no))*(tra_cashd/100),2) - round(ifnull((select sum(payment_amount) from receipt_payments where payment_transaction_no = tra_no),0),2) net from transactions where tra_no = $tid";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$rcl = $rec['net'];
db_close();

// check if transaction has been paid advance/full
$chkr = 0;
$sql = "select payment_transaction_no from receipt_payments where payment_transaction_no = $tid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$chkr = $rec['payment_transaction_no'];
}
db_close();
//

if (($tcash == 0) && ($tclt != 2) && ($chkr == 0)) {
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

$sql = "delete from receipts where receipt_no in($trid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "delete from receipt_payments where payment_transaction_no in($tid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

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
$sql = "select /* -> */round(((select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2)) from transaction_items where tra_item_trano = tra_no) - (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2))/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no)) /* <-(net price) - net price/vat*(discount) */ - /* -> */((select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2)) from transaction_items where tra_item_trano = tra_no) - (select sum(round((tra_item_gprice - (tra_item_gprice*(tra_item_discount/100))) * tra_item_qty,2))/tra_item_vat*(tra_item_add_discount/100) from transaction_items where tra_item_trano = tra_no))*(tra_cashd/100),2) net from transactions where tra_no = $ptid";
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

$sql = "insert into receipt_payments (payment_receipt_no, payment_transaction_no, payment_amount, payment_isfull, payment_date, payment_uid) ";
$sql .= "values($lrno, $ptid, $namt, 1, CURRENT_TIMESTAMP, $uid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "update transactions set tra_receipt_no = $lrno where tra_no = $ptid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

echo $str_response;
break;

}

?>