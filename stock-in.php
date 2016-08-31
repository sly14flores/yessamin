<?php

session_start();
$uid = $_SESSION['user_id'];
$cashier_branch = $_SESSION['branch'];
$cashier_fullname = $_SESSION['fullname'];
$tbranch = $_SESSION['branch'];

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

case "add":
$psindate = (isset($_POST['psindate'])) ? $_POST['psindate'] : "";
$psindate = date("Y-m-d",strtotime($psindate));
$psmno = (isset($_POST['psmno'])) ? $_POST['psmno'] : "";
$psmn = (isset($_POST['psmn'])) ? $_POST['psmn'] : "";
$psrefno = (isset($_POST['psrefno'])) ? $_POST['psrefno'] : 0;
$pssup = (isset($_POST['pssup'])) ? $_POST['pssup'] : 0;
$psupplier = (isset($_POST['psupplier'])) ? $_POST['psupplier'] : "";
$parsitem = (isset($_POST['parsitem'])) ? $_POST['parsitem'] : "";
$psioff = (isset($_POST['psioff'])) ? $_POST['psioff'] : 0;
$psioffid = (isset($_POST['psioffid'])) ? $_POST['psioffid'] : 0;
$pspm = (isset($_POST['pspm'])) ? $_POST['pspm'] : 0;
$psdd = (isset($_POST['psdd'])) ? $_POST['psdd'] : "0000-00-00";
if ($psdd != "0000-00-00") $psdd = date("Y-m-d",strtotime($psdd));
$pavr = (isset($_POST['pavr'])) ? $_POST['pavr'] : 0;
$pcftd = (isset($_POST['pcftd'])) ? $_POST['pcftd'] : 0;
$pncftd = (isset($_POST['pncftd'])) ? $_POST['pncftd'] : 0;
$phsd = (isset($_POST['phsd'])) ? $_POST['phsd'] : 0;
$phcd = (isset($_POST['phcd'])) ? $_POST['phcd'] : 0;

if ($pssup == 0) {

$sql = "select * from suppliers where supplier_desc = '$psupplier'";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
db_close();

if ($rc == 0) {
$sql = "alter table suppliers AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "insert into suppliers (supplier_branch, supplier_desc, supplier_date, supplier_return, supplier_uid) ";
$sql .= "values ($tbranch, '$psupplier', CURRENT_TIMESTAMP, 1, $uid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "select supplier_id from suppliers where supplier_uid = $uid order by supplier_id desc limit 1";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$pssup = $rec['supplier_id'];
db_close();

}

}

$sql = "alter table stock_in AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();	

/*
$sql = "alter table stock_in_item AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();
*/

$sql = "insert into stock_in (sin_date_invoice, sin_time, sin_member_no, member_name, sin_ref, sin_supid, sin_userid, sin_offid, sin_off, sin_payment_mode, sin_due_date, avon_cft_discount, avon_ncft_discount, avon_home_discount, avon_health_discount, avon_rebate) ";
$sql .= "values ('$psindate', CURRENT_TIMESTAMP, '$psmno', '$psmn', '$psrefno', $pssup, $uid, '$psioffid', $psioff, $pspm, '$psdd', $pcftd, $pncftd, $phsd, $phcd, $pavr)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$lsid = 0;
$sql = "select sin_id from stock_in where sin_userid = $uid order by sin_id desc limit 1";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$lsid = $rec['sin_id'];
db_close();

$pitem = explode("|",$parsitem);
$pco = $_POST['pco'];

$sql = "insert into stock_in_item (stock_in_sid, stock_in_ref, stock_in_pcode, stock_in_pname, stock_in_psize, stock_in_price, stock_in_quantity, stock_in_amt, stock_in_acat, stock_in_inventory) values ";

for ($i=0; $i<$pco; ++$i) {

$dat = explode(",",$pitem[$i]);
$sql .= "($lsid, '" . $psrefno . "', '" . $dat[0] . "', '" . $dat[1] . "', '" . $dat[2] . "', " . $dat[3] . ", " . $dat[4] . ", " . (float)$dat[3] * $dat[4] . ", " . $dat[5] . ", " . $dat[4] . "),";

}

$sql = substr($sql,0,(strlen($sql)-1));
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Stock successfully added.";

echo $str_response;
break;

case "update":
$psindate = (isset($_POST['psindate'])) ? $_POST['psindate'] : "";
$psindate = date("Y-m-d",strtotime($psindate));
$psmno = (isset($_POST['psmno'])) ? $_POST['psmno'] : "";
$psmn = (isset($_POST['psmn'])) ? $_POST['psmn'] : "";
$psrefno = (isset($_POST['psrefno'])) ? $_POST['psrefno'] : 0;
$pssup = (isset($_POST['pssup'])) ? $_POST['pssup'] : 0;
$psupplier = (isset($_POST['psupplier'])) ? $_POST['psupplier'] : "";
$parsitem = (isset($_POST['parsitem'])) ? $_POST['parsitem'] : "";
$psinos = (isset($_POST['psinos'])) ? $_POST['psinos'] : "0";
$psioff = (isset($_POST['psioff'])) ? $_POST['psioff'] : 0;
$psioffid = (isset($_POST['psioffid'])) ? $_POST['psioffid'] : "";
$pspm = (isset($_POST['pspm'])) ? $_POST['pspm'] : 0;
$psdd = (isset($_POST['psdd'])) ? $_POST['psdd'] : "0000-00-00";
if ($psdd != "0000-00-00") $psdd = date("Y-m-d",strtotime($psdd));
$pavr = (isset($_POST['pavr'])) ? $_POST['pavr'] : 0;
$pcftd = (isset($_POST['pcftd'])) ? $_POST['pcftd'] : 0;
$pncftd = (isset($_POST['pncftd'])) ? $_POST['pncftd'] : 0;
$phsd = (isset($_POST['phsd'])) ? $_POST['phsd'] : 0;
$phcd = (isset($_POST['phcd'])) ? $_POST['phcd'] : 0;


if ($pssup == 0) {
$sql = "alter table suppliers AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "insert into suppliers (supplier_desc, supplier_date, supplier_uid)";
$sql .= "values ('$psupplier', CURRENT_TIMESTAMP, $uid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "select supplier_id from suppliers where supplier_uid = $uid order by supplier_id desc limit 1";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$pssup = $rec['supplier_id'];
db_close();
}

$sid = $_GET['sid'];

$sql = "update stock_in set ";
$sql .= "sin_date_invoice = '$psindate', ";
$sql .= "sin_member_no = '$psmno', ";
$sql .= "member_name = '$psmn', ";
$sql .= "sin_ref = '$psrefno', ";
$sql .= "sin_supid = $pssup, ";
$sql .= "sin_offid = '$psioffid', ";
$sql .= "sin_off = $psioff, ";
$sql .= "sin_payment_mode = $pspm,";
$sql .= "sin_due_date = '$psdd', ";
$sql .= "avon_cft_discount = $pcftd, ";
$sql .= "avon_ncft_discount = $pncftd, ";
$sql .= "avon_home_discount = $phsd, ";
$sql .= "avon_health_discount = $phcd, ";
$sql .= "avon_rebate = $pavr ";
$sql .= "where sin_id = $sid";

db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$pitem = explode("|",$parsitem);
$pco = $_POST['pco'];

$rnos = $psinos;
$siids = "";
for ($i=0; $i<$pco; ++$i) {

$dat = explode(",",$pitem[$i]);
$siid = $dat[5];

if (substr_count($psinos,$siid) == 1) { // update
	$sql = "update stock_in_item set stock_in_psize = '" . $dat[2] . "', stock_in_price = " . $dat[3] . ", stock_in_quantity = " . $dat[4] . ", stock_in_amt = " . (float)$dat[3] * $dat[4] . ", stock_in_acat = " . $dat[6] . " where stock_in_id = $siid";
	$rnos = extStr($rnos,$siid);
	$siids .= $siid . ",";
} else {
	$sql = "insert into stock_in_item (stock_in_sid, stock_in_ref, stock_in_pcode, stock_in_pname, stock_in_psize, stock_in_price, stock_in_quantity, stock_in_amt, stock_in_acat, stock_in_inventory)";
	$sql .= " values ($sid, '" . $psrefno . "', '" . $dat[0] . "', '" . $dat[1] . "', '" . $dat[2] . "', " . $dat[3] . ", " . $dat[4] . ", " . (float)$dat[3] * $dat[4] . ", " . $dat[6] . ", " . $dat[4] . ")";
}

db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

}

$sql = "delete from stock_in_item where stock_in_id in ($rnos)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

staticInventory($siids);

$str_response = "Stock successfully updated.";

echo $str_response;
break;

case "edit":
$sid = $_GET['sid'];

$sql = "select avon_cat_id, avon_cat_name from avon_categories order by avon_cat_name";

$cats = '<option value=\"0\">None</option>';
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$cats .= '<option value=\"' . $rec['avon_cat_id'] . '\">';
$cats .= $rec['avon_cat_name'];
$cats .= '</option>';
}
}
db_close();

$json = '{ "editstock": [';
$sql = "select sin_date_invoice, sin_member_no, member_name, sin_ref, (select supplier_desc from suppliers where supplier_id = sin_supid) supplier, (select supplier_return from suppliers where supplier_id = sin_supid) return_ok, sin_supid, sin_off, sin_offid, (select supplier_branch from suppliers where supplier_id = sin_supid) sup_branch, (select concat(firstname, ' ', lastname) user_fullname from users where user_id = sin_userid) cashier, sin_payment_mode, sin_due_date, avon_rebate, avon_cft_discount, avon_ncft_discount, avon_home_discount, avon_health_discount ";
$sql .= "from stock_in where sin_id = $sid";

db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$json .= '{';
$rsupd = $rec['supplier'];
$rsup = $rec['sin_supid'];
$json .= '"jsid":"' . date("m/d/Y",strtotime($rec['sin_date_invoice'])) . '",';
$json .= '"jmno":"' . $rec['sin_member_no'] . '",';
$json .= '"jmn":"' . $rec['member_name'] . '",';
$json .= '"jsr":"' . $rec['sin_ref'] . '",';
$json .= '"jsu":"' . $rec['supplier'] . '",';
$json .= '"jsupid":' . $rec['sin_supid'] . ',';
$json .= '"jsoff":' . $rec['sin_off'] . ',';
$json .= '"jsoffid":"' . $rec['sin_offid'] . '",';
$json .= '"jsib":' . $rec['sup_branch'] . ',';
$json .= '"jrok":' . $rec['return_ok'] . ',';
$json .= '"jcashier":"' . $rec['cashier'] . '",';
$json .= '"jspm":' . $rec['sin_payment_mode'] . ',';
$json .= '"jsdd":"' . date("m/d/Y",strtotime($rec['sin_due_date'])) . '",';
$json .= '"jgrant":' . $_SESSION['grants'] . ',';
$json .= '"javr":' . $rec['avon_rebate'] . ',';
$json .= '"jcftd":' . $rec['avon_cft_discount'] . ',';
$json .= '"jncftd":' . $rec['avon_ncft_discount'] . ',';
$json .= '"jhsd":' . $rec['avon_home_discount'] . ',';
$json .= '"jhcd":' . $rec['avon_health_discount'] . '';
$json .= '}';
db_close();

$siino = "";
$lsiino = 0;
$json .= '], "stockitems": [';
//$sql = "select @rep := sii_replacement, stock_in_id, stock_in_ref, stock_in_pcode, stock_in_pname, stock_in_psize, stock_in_price, (stock_in_quantity - sii_replaced) siq, stock_in_amt, ifnull((select sum(retc_qty) from returns_companies where retc_sino = stock_in_id group by retc_sino),0) returns, stock_in_acat, if(sii_replacement != 0,(select concat('Replaced: ', stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) from stock_in_item where stock_in_id = @rep),'') remarks, ifnull((select outright_discount from suppliers where supplier_id = (select sin_supid from stock_in where sin_id = stock_in_sid)),0) outright from stock_in_item where stock_in_sid = $sid order by stock_in_id";
$sql = "select @rep := sii_replacement, stock_in_id, stock_in_ref, stock_in_pcode, stock_in_pname, stock_in_psize, stock_in_price, (stock_in_quantity - sii_replaced) siq, stock_in_amt, ifnull((select sum(retc_qty) from returns_companies where retc_sino = stock_in_id group by retc_sino),0) returns, stock_in_acat, if(sii_replacement != 0,(select concat('Replaced: ', stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) from stock_in_item where stock_in_id = @rep),'') remarks, ifnull((select case (select sin_supid from stock_in where sin_id = stock_in_sid) when 2 then basic_discount when 17 then basic_discount else outright_discount end from suppliers where supplier_id = (select sin_supid from stock_in where sin_id = stock_in_sid)),0) net_discount from stock_in_item where stock_in_sid = $sid order by stock_in_id";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
	for ($i=0; $i<$rc; ++$i) {
	$rec = $rs->fetch_array();
	$json .= '{';
	$json .= '"jsino":' . $rec['stock_in_id'] . ',';
	$json .= '"jpr":"' . $rec['stock_in_ref'] . '",';	
	$json .= '"jpc":"' . $rec['stock_in_pcode'] . '",';
	$json .= '"jpn":"' . $rec['stock_in_pname'] . '",';
	$json .= '"jps":"' . $rec['stock_in_psize'] . '",';	
	$json .= '"jsp":' . $rec['stock_in_price'] . ',';
	$json .= '"jsq":' . $rec['siq'] . ',';
	$json .= '"jsa":' . $rec['stock_in_amt'] . ',';
	$json .= '"jrsupd":"' . $rsupd . '",';
	$json .= '"jrsup":' . $rsup . ',';
	$json .= '"jret":' . $rec['returns'] . ',';	
	$json .= '"japcid":' . $rec['stock_in_acat'] . ',';
	$json .= '"japcs":"' . $cats . '",';
	$json .= '"jrem":"' . $rec['remarks'] . '",';
	$json .= '"jod":' . $rec['net_discount'] . '';	
	$json .= '},';
	$lsiino = $rec['stock_in_id']; $siino .= $rec['stock_in_id'] . ",";
	}
} else {
	$json .= '{},';
}
db_close();

$json = substr($json,0,(strlen($json)-1));
$siino = substr($siino,0,(strlen($siino)-1));
$json .= '], "siino": [{"jsiino":' . $lsiino . ',"jsiinos":"' . $siino .'"}';
$json .= ' ]}';
echo $json;
break;

case "contents":
$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

$sql = "select count(*) mcount from stock_in";
$pql = "select sin_id, sin_supid, sin_date_invoice, substr(now(),1,10) now, sin_member_no, member_name, (select supplier_desc from suppliers where supplier_id = sin_supid) supplier, sin_ref, @vamount := ifnull((select sum(stock_in_price*stock_in_quantity) from stock_in_item where stock_in_sid = sin_id),0) amount, @vnet_amt := round((ifnull((select sum((stock_in_price - (stock_in_price*(ifnull((select case sin_supid when 2 then basic_discount when 17 then basic_discount else outright_discount end from suppliers where supplier_id = sin_supid),0)/100)))*stock_in_quantity) from stock_in_item where stock_in_sid = sin_id),0)),2) net_amt, @cftnet := round((ifnull((select sum((stock_in_price - (stock_in_price*(ifnull((select case sin_supid when 2 then basic_discount when 17 then basic_discount else outright_discount end from suppliers where supplier_id = sin_supid),0)/100)))*stock_in_quantity) from stock_in_item where stock_in_sid = sin_id and (select main_cat from avon_categories where avon_cat_id = stock_in_acat) = 1),0)),2) cft_net, if(avon_cft_discount != 0,@cftnet-((@cftnet/1.12)*(avon_cft_discount/100)),0) avon_cftd, @ncftnet := round((ifnull((select sum((stock_in_price - (stock_in_price*(ifnull((select case sin_supid when 2 then basic_discount when 17 then basic_discount else outright_discount end from suppliers where supplier_id = sin_supid),0)/100)))*stock_in_quantity) from stock_in_item where stock_in_sid = sin_id and (select main_cat from avon_categories where avon_cat_id = stock_in_acat) = 2),0)),2) ncft_net, if(avon_ncft_discount != 0,@ncftnet-((@ncftnet/1.12)*(avon_ncft_discount/100)),0) avon_ncftd, @hsnet := round((ifnull((select sum((stock_in_price - (stock_in_price*(ifnull((select case sin_supid when 2 then basic_discount when 17 then basic_discount else outright_discount end from suppliers where supplier_id = sin_supid),0)/100)))*stock_in_quantity) from stock_in_item where stock_in_sid = sin_id and (select main_cat from avon_categories where avon_cat_id = stock_in_acat) = 3),0)),2) hs_net, if(avon_home_discount != 0,@hsnet-((@hsnet/1.12)*(avon_home_discount/100)),0) avon_hsd, @hcnet := round((ifnull((select sum((stock_in_price - (stock_in_price*(ifnull((select case sin_supid when 2 then basic_discount when 17 then basic_discount else outright_discount end from suppliers where supplier_id = sin_supid),0)/100)))*stock_in_quantity) from stock_in_item where stock_in_sid = sin_id and (select main_cat from avon_categories where avon_cat_id = stock_in_acat) = 4),0)),2) hc_net, if(avon_health_discount != 0,@hcnet-((@hcnet/1.12)*(avon_health_discount/100)),0) avon_hcd, sin_off, sin_payment_mode, sin_due_date, sin_pullout, sin_date_pullout, sin_gross_amt from stock_in";

// filters
$fsup = (isset($_GET['fsup'])) ? $_GET['fsup'] : 0;
$frefno = (isset($_GET['frefno'])) ? $_GET['frefno'] : "";
$fmemn = (isset($_GET['fmemn'])) ? $_GET['fmemn'] : "";
$fs = (isset($_GET['fs'])) ? $_GET['fs'] : "";
if ($fs != "") $fs = date("Y-m-d",strtotime($fs));
$fe = (isset($_GET['fe'])) ? $_GET['fe'] : "";
if ($fe != "") $fe = date("Y-m-d",strtotime($fe));
$fpcon = (isset($_GET['fpcon'])) ? $_GET['fpcon'] : "";
$fd = (isset($_GET['fd'])) ? $_GET['fd'] : "";
if ($fd != "") $fd = date("Y-m-d",strtotime($fd));

if ($fpcon != "") {
$sql .= " left join stock_in_item on stock_in.sin_id = stock_in_item.stock_in_sid";
$pql .= " left join stock_in_item on stock_in.sin_id = stock_in_item.stock_in_sid";
}

$sql .= " where sin_id != 0";
$pql .= " where sin_id != 0";

$c1 = " and sin_supid = $fsup";
$c2 = " and sin_ref like '%$frefno%'";
$c3 = " and sin_date_invoice >= '$fs' and sin_date_invoice <= '$fe'";
$c4 = " and concat(stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) = '$fpcon'";
$c5 = " and member_name = '$fmemn'";
$c6 = " and sin_due_date = '$fd'";

if ($fsup == 0) $c1 = "";
if ($frefno == "") $c2 = "";
if (($fs == "") || ($fe == "")) $c3 = "";
if ($fpcon == "") $c4 = "";
if ($fmemn == "") $c5 = "";
if ($fd == "") $c6 = "";

$sql .= $c1 . $c2 . $c3 . $c4 . $c5 . $c6;
$pql .= $c1 . $c2 . $c3 . $c4 . $c5 . $c6;
//

$sql .= " order by sin_date_invoice, sin_time";
$pql .= " order by sin_date_invoice, sin_time";

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
$str_response .= '<tr><td width="2%"><input type="checkbox" name="chk_checkall" id="chk_checkall" onclick="Check_all(this.form, this);" /></td><td>Date Invoice</td><td>Day(s) Elapsed</td><td>Supplier</td><td>Ref.No.</td><td>Mem.No.</td><td>Member Name</td><td>Gross Amt</td><td>Net Amt</td><td>Due Date</td><td>Remarks</td><td align="center">Tool</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {

$netamt = 0;
$duedate = "COD";
$rem = "";
$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
if ($rec['sin_payment_mode'] == 0) $duedate = ($rec['sin_due_date'] == '0000-00-00') ? '' : date("M j, Y",strtotime($rec['sin_due_date']));
if ($rec['sin_payment_mode'] == 1) $duedate = "COD";
if ($rec['sin_payment_mode'] == 2) $duedate = "Offset";
if ($rec['sin_payment_mode'] == 3) $duedate = "Swap";
$str_response .= '<tr class="' . $rowstyle . '" onclick="chkRow(this);">';
$str_response .= '<td><input type="checkbox" name="chk_' . $rec['sin_id'] . '" id="chk_' . $rec['sin_id'] . '" onClick="Uncheck_Parent(\'chk_checkall\',this);" /></td>';
$str_response .= '<td>' . date("F j, Y",strtotime($rec['sin_date_invoice'])) . '</td>';
$delapsed = (strtotime($rec['now']) - strtotime($rec['sin_date_invoice']))/86400;
$str_response .= '<td>' . $delapsed . ' day(s)</td>';
$str_response .= '<td>' . $rec['supplier'] . '</td>';
$str_response .= '<td>' . $rec['sin_ref'] . '</td>';
$str_response .= '<td>' . $rec['sin_member_no'] . '</td>';
$str_response .= '<td>' . $rec['member_name'] . '</td>';
$str_response .= '<td>' . $rec['amount'] . '</td>';
$avon_discounts = ($rec['avon_cftd'] + $rec['avon_ncftd'] + $rec['avon_hsd'] + $rec['avon_hcd']) - $rec['sin_off'];
$netamt = $rec['net_amt'] - $rec['sin_off'];
$tmp_netamt = $netamt;
if ($rec['sin_supid'] == 2) {
	$netamt = $avon_discounts - $rec['sin_off'];
	if ($netamt == 0) $netamt = $tmp_netamt;
}
$str_response .= '<td>' . round($netamt,2) . '</td>';
$str_response .= '<td>' . $duedate . '</td>';
$str_response .= '<td>' . $rem . '</td>';
$str_response .= '<td align="center">';
$str_response .= '<a href="javascript: viewStock(' . $rec['sin_id'] . ',\'' . $rec['sin_ref'] . '\');" class="tooltip-min" style="padding-right: 5px;"><img src="images/view-16.png" /><span>View Ref.no.</span></a>';
if ($rec['sin_payment_mode'] == 0) $str_response .= '<a href="javascript: pullOutCheck(' . $rec['sin_id'] . ');" class="tooltip-min"><img src="images/cheque.png" /><span>Pull-out Cheque</span></a>';
$str_response .= '</td>';
$str_response .= '</tr>';

}

$str_response .= '</tbody>';

if ($max_count > $perPage) {

$cNPage = new pageNav(1,'rStockF()',$pageNum,$max_p);
$str_response .= '<tfoot><tr><td colspan="7">';
$str_response .= $cNPage->getNav();
$str_response .= '</td></tr></tfoot>';

}

$str_response .= '</table></form>' . $lpage;

}
db_close();

echo $str_response;
break;

case "delete":
$sid = $_POST['sid'];

$sql = "delete from stock_in where sin_id in ($sid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "select stock_in_id from stock_in_item where stock_in_sid = $sid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
$rds = "0,";
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$rds .= $rec['stock_in_id'] . ",";
}
db_close();
$rds = substr($rds,0,strlen($rds)-1);

$sql = "delete from stock_in_item where stock_in_sid in ($sid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "delete from returns_companies where retc_sino in ($rds)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Stock(s) deleted.";
echo $str_response;
break;

case "check_supplier":
$pcsup = (isset($_POST['pcsup'])) ? $_POST['pcsup'] : "";

$str_response = 0;
$sql = "select * from suppliers where supplier_desc like '%$pcsup%'";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$str_response = 1;
}
db_close();

echo $str_response;
break;

case "list_categories":
$sql = "select avon_cat_id, avon_cat_name from avon_categories order by avon_cat_name";

$str_response = '<option value="0">None</option>';
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$str_response .= '<option value="' . $rec['avon_cat_id'] . '">';
$str_response .= $rec['avon_cat_name'];
$str_response .= '</option>';
}
}
db_close();

echo $str_response;
break;

case "list_suppliers":
$sql = "select supplier_id, supplier_desc, supplier_branch from suppliers";

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
$json .= '"sbr":' . $rec['supplier_branch'] . ',';
$json .= '"cbr":' . $cashier_branch . ',';
$json .= '"cfn":"' . $cashier_fullname . '",';
$json .= '"psup":"' . $str_sup . '"';
$json .= '},';
}
$json = substr($json,0,(strlen($json)-1));
}
$json .= ']';

db_close();

echo $json;
break;

case "list_product_codes":
$sql = "select concat(stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) item, concat(supplier_desc, ' ', stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize, ' ', stock_in_price) product, stock_in_pcode, stock_in_pname, supplier_desc, outright_discount, stock_in_psize, stock_in_price from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id left join suppliers on stock_in.sin_supid = suppliers.supplier_id order by sin_date_invoice";

$json = '[';
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$list_chk[$i] = $rec['item'];
$list_id[$i] = $rec['stock_in_pcode'];
$list_txt[$i] = $rec['product'];
$list_code[$i] = $rec['stock_in_pcode'];
$list_name[$i] = $rec['stock_in_pname'];
$list_size[$i] = $rec['stock_in_psize'];
$list_price[$i] = $rec['stock_in_price'];
$list_outd[$i] = number_format($rec['outright_discount']);
}

$tp = "";
for ($i=0; $i<$rc; ++$i) {
$tp .= $list_chk[$i] . ",";
if (substr_count($tp,$list_chk[$i]) > 1) continue;
$json .= '{';
$json .= '"id":"' . $list_id[$i] . '",';
$json .= '"text":"' . $list_txt[$i] . '",';
$json .= '"jpc":"' . $list_code[$i] . '",';
$json .= '"jpn":"' . $list_name[$i] . '",';
$json .= '"jps":"' . $list_size[$i] . '",';
$json .= '"jpp":' . $list_price[$i] . ',';
$json .= '"jod":' . $list_outd[$i] . '';
$json .= '},';
}
$json = substr($json,0,(strlen($json)-1));
}
$json .= ']';

db_close();
echo $json;
break;

case "list_product_codes_suggest_own":
$pquery_pcode = (isset($_POST['pquery_pcode'])) ? $_POST['pquery_pcode'] : "";
$pcode_filter = "";
if ($pquery_pcode != "") $pcode_filter = "where concat(supplier_desc, ' ', stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize, ' ', stock_in_price) like '%$pquery_pcode%'";
$sql = "select concat(stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) item, concat(supplier_desc, ' ', stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize, ' ', stock_in_price) product, stock_in_pcode, stock_in_pname, supplier_desc, outright_discount, stock_in_psize, stock_in_price from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id left join suppliers on stock_in.sin_supid = suppliers.supplier_id $pcode_filter order by sin_date_invoice";

if ($pquery_pcode == "") {
	echo $str_response;
	break;
}

db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$str_response = '<ul>';	
$str_response .= '<li class="autocomplete-own-odd" onclick="newStockItem();" style="color: #d32f2f;"><strong>NEW PRODUCT</strong></li>';
for ($i=0; $i<$rc; ++$i) {
	$autocomplete_own_odd = '';
	if (($i%2) == 1)$autocomplete_own_odd = 'autocomplete-own-odd';	
	$rec = $rs->fetch_array();
	$str_response .= '<li class="' . $autocomplete_own_odd . '" onclick="clickStockItem(this);" data-pid="' . $rec['stock_in_pcode'] . '" data-pcode="' . $rec['stock_in_pcode'] . '" data-pname="' . $rec['stock_in_pname'] . '" data-psize="' . $rec['stock_in_psize'] . '" data-sprice="' . $rec['stock_in_price'] . '" data-houtd="' . number_format($rec['outright_discount']) . '">' . $rec['product'] . '</li>';
}
$str_response .= '</ul>';
}
db_close();

echo $str_response;
break;

case "list_members":
$sql = "select sin_id, member_name, sin_member_no from stock_in";

$json = '[';
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
//if (substr_count($json,$rec['member_name']) > 1) continue;
if (preg_match("/\b" . $rec['member_name'] . "\b/i",$json)) continue;
$json .= '{';
$json .= '"id":' . $rec['sin_id'] . ',';
$json .= '"text":"' . $rec['member_name'] . '"';
$json .= '},';
}
$json = substr($json,0,(strlen($json)-1));
}
$json .= ']';

db_close();

echo $json;
break;

case "list_membernos":
$sql = "select sin_id, sin_member_no, member_name from stock_in";

$json = '[';
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
//if (substr_count($json,$rec['sin_member_no']) > 1) continue;
if (preg_match("/\b" . $rec['sin_member_no'] . "\b/i",$json)) continue;
$json .= '{';
$json .= '"id":' . $rec['sin_id'] . ',';
$json .= '"text":"' . $rec['sin_member_no'] . '"';
$json .= '},';
}
$json = substr($json,0,(strlen($json)-1));
}
$json .= ']';

db_close();

echo $json;
break;

case "fill_mem_no":
$pmemn = (isset($_POST['pmemn'])) ? $_POST['pmemn'] : "";

$sql = "select member_name, sin_member_no from stock_in where sin_member_no = '$pmemn'";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$str_response = $rec['member_name'];
if ($rec['sin_member_no'] == "1160994") $str_response = "Frances Mae de Guzman";
}
db_close();

echo $str_response;
break;

case "info_swap_stock":
$siid = $_GET['siid'];

$sql = "SELECT `stock_in_pcode`, `stock_in_pname`, `stock_in_psize`, `stock_in_quantity`, `stock_in_ref`, (select sin_supid from stock_in where sin_id = stock_in_sid) sii_supid FROM `stock_in_item` WHERE `stock_in_id` = $siid";
$json = '{ "stockinfo": [{';
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$json .= '"jssc":"' . $rec['stock_in_pcode'] . '",';
$json .= '"jssn":"' . $rec['stock_in_pname'] . '",';
$json .= '"jsss":"' . $rec['stock_in_psize'] . '",';
$json .= '"jssq":' . $rec['stock_in_quantity'] . ',';
$json .= '"jssr":"' . $rec['stock_in_ref'] . '",';
$json .= '"jssup":' . $rec['sii_supid'] . '';
}
db_close();
$json .= '}]}';

echo $json;
break;

case "add_swapped_stock":
$psino = (isset($_POST['psino'])) ? $_POST['psino'] : 0;
$pretref = (isset($_POST['pretref'])) ? $_POST['pretref'] : "";
$pretcode = (isset($_POST['pretcode'])) ? $_POST['pretcode'] : "";
$pretname = (isset($_POST['pretname'])) ? $_POST['pretname'] : "";
$pretsize = (isset($_POST['pretsize'])) ? $_POST['pretsize'] : "";
$pretqty = (isset($_POST['pretqty'])) ? $_POST['pretqty'] : 0;

$sql = "update stock_in_item set sii_replaced = $pretqty where stock_in_id = $psino";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_sid = 0;
$str_sp = 0;
$str_cat = 0;
$sql = "select stock_in_sid, stock_in_price, stock_in_acat from stock_in_item where stock_in_id = $psino";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$str_sid = $rec['stock_in_sid'];
$str_sp = $rec['stock_in_price'];
$str_cat = $rec['stock_in_acat'];
}
db_close();

$sql = "insert into stock_in_item (stock_in_sid, stock_in_ref, stock_in_pcode, stock_in_pname, stock_in_psize, stock_in_price, stock_in_quantity, stock_in_amt, stock_in_acat, stock_in_inventory, sii_replacement) values ";
$sql .= "($str_sid,'$pretref','$pretcode','$pretname','$pretsize',$str_sp,$pretqty,$str_sp*$pretqty,$str_cat,$pretqty,$psino)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Stock replaced.";

break;

case "list_offsets":
$gmn = $_GET['gmn'];
$sql = "SELECT `offset_id`, `offset_cid` FROM `offsets` WHERE offset_mn = '$gmn' ORDER BY `offset_id`";

$json = '[';
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"id":' . $rec['offset_id'] . ',';
$json .= '"text":"' . $rec['offset_cid'] . '"';
$json .= '},';
}
$json = substr($json,0,(strlen($json)-1));
}
$json .= ']';

db_close();

echo $json;
break;

case "edit_pullout_cheque":
$gsid = (isset($_GET['gsid'])) ? $_GET['gsid'] : 0;

$json = '{ "editpullout": [';
$sql = "SELECT sin_loan, ifnull((SELECT loan_description FROM loans WHERE loan_id = sin_loan),'') loan_desc FROM stock_in WHERE sin_id = $gsid";

db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"jslid":' . $rec['sin_loan'] . ',';
$json .= '"jsloan":"' . $rec['loan_desc'] . '"';
$json .= '}';
db_close();

$json .= '] }';
echo $json;
break;

case "pullout_cheque":
$psid = (isset($_POST['psid'])) ? $_POST['psid'] : 0;
$plid = (isset($_POST['plid'])) ? $_POST['plid'] : 0;

$sql = "UPDATE stock_in set sin_pullout = 1, sin_loan = $plid, sin_date_pullout = CURRENT_TIMESTAMP WHERE sin_id = $psid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "select sin_ref, sin_date_invoice from stock_in where sin_id = $psid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$str_response = "Cheque for " . $rec['sin_ref'] . " dated " . date("F j, Y",strtotime($rec['sin_date_invoice'])) . " successfully pull-out.";
}
db_close();

echo $str_response;

break;

case "delete_bank_account":
$baid = (isset($_POST['baid'])) ? $_POST['baid'] : 0;

$sql = "delete from bank_accounts where bank_account_id = $baid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

echo "Bank account deleted.";

break;

case "offset_balance":
$pcid = (isset($_POST['pcid'])) ? $_POST['pcid'] : "";

$sql = "select (ifnull((select sum(offset_item_amount) from offset_items where offset_item_oid = offset_id),0)) offset_amount, ifnull((select sum(sin_off) from stock_in where sin_offid = offset_cid),0) stock_off from offsets where offset_cid = '$pcid'";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$str_response = $rec['offset_amount'] - $rec['stock_off'];
}
db_close();

echo $str_response;
break;

case "list_loans":
$sql = "SELECT loan_id, loan_date, loan_description, loan_company_person, loan_amount, loan_note FROM loans";

$json = '[';
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"id":' . $rec['loan_id'] . ',';
$json .= '"text":"' . $rec['loan_description'] . " - " . $rec['loan_company_person'] . '"';
$json .= '},';
}
$json = substr($json,0,(strlen($json)-1));
}
$json .= ']';

db_close();

echo $json;
break;

case "archive_stock":
$psid = (isset($_POST['psid'])) ? $_POST['psid'] : 0;
$archive_db = "boutique_archive";
$boutique_db = "boutique";

$sids = "";

//
$sql = "select * from stock_in where sin_id = $psid";
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
$sql = "insert into stock_in values ($dd)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();
//

//
$DB_FILE = $boutique_db;
$sql = "select * from stock_in_item where stock_in_sid = $psid";
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
				if ($f == 'stock_in_id') $sids .= "$v,";
			}
		}
		$dd = substr($dd,0,strlen($dd)-1);
	$dd .= "),";		
	}
}
db_close();
$dd = substr($dd,0,strlen($dd)-1);
$sids = substr($sids,0,strlen($sids)-1);

$DB_FILE = $archive_db;
$sql = "insert into stock_in_item values $dd";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();
//

/* //
$DB_FILE = $boutique_db;
$sql = "select * from returns_companies where retc_sino in ($sids)";
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
$sql = "insert into returns_companies values $dd";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();
// */

/* //
$DB_FILE = $boutique_db;
$sql = "select * from dealers_returns where dret_sino in ($sids)";
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
$sql = "delete from stock_in where sin_id = $psid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "delete from stock_in_item where stock_in_sid = $psid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

/* $sql = "delete from returns_companies where retc_sino in ($sids)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close(); */

/* $sql = "delete from dealers_returns where dret_sino in ($sids)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close(); */
 
$str_response = "Stock successfully archived.";

echo $str_response;
break;

case "avon_discounts":
$sid = $_POST['psid'];

$json = '{ "avonDiscounts": [{';
$sql = "select sin_id, sin_supid, @vamount := ifnull((select sum(stock_in_price*stock_in_quantity) from stock_in_item where stock_in_sid = sin_id),0) amount, @vnet_amt := round((ifnull((select sum((stock_in_price - (stock_in_price*(ifnull((select case sin_supid when 2 then basic_discount when 17 then basic_discount else outright_discount end from suppliers where supplier_id = sin_supid),0)/100)))*stock_in_quantity) from stock_in_item where stock_in_sid = sin_id),0)),2) net_amt, @cftnet := round((ifnull((select sum((stock_in_price - (stock_in_price*(ifnull((select case sin_supid when 2 then basic_discount when 17 then basic_discount else outright_discount end from suppliers where supplier_id = sin_supid),0)/100)))*stock_in_quantity) from stock_in_item where stock_in_sid = sin_id and (select main_cat from avon_categories where avon_cat_id = stock_in_acat) = 1),0)),2) cft_net, if(avon_cft_discount != 0,@cftnet-((@cftnet/1.12)*(avon_cft_discount/100)),0) avon_cftd, @ncftnet := round((ifnull((select sum((stock_in_price - (stock_in_price*(ifnull((select case sin_supid when 2 then basic_discount when 17 then basic_discount else outright_discount end from suppliers where supplier_id = sin_supid),0)/100)))*stock_in_quantity) from stock_in_item where stock_in_sid = sin_id and (select main_cat from avon_categories where avon_cat_id = stock_in_acat) = 2),0)),2) ncft_net, if(avon_ncft_discount != 0,@ncftnet-((@ncftnet/1.12)*(avon_ncft_discount/100)),0) avon_ncftd, @hsnet := round((ifnull((select sum((stock_in_price - (stock_in_price*(ifnull((select case sin_supid when 2 then basic_discount when 17 then basic_discount else outright_discount end from suppliers where supplier_id = sin_supid),0)/100)))*stock_in_quantity) from stock_in_item where stock_in_sid = sin_id and (select main_cat from avon_categories where avon_cat_id = stock_in_acat) = 3),0)),2) hs_net, if(avon_home_discount != 0,@hsnet-((@hsnet/1.12)*(avon_home_discount/100)),0) avon_hsd, @hcnet := round((ifnull((select sum((stock_in_price - (stock_in_price*(ifnull((select case sin_supid when 2 then basic_discount when 17 then basic_discount else outright_discount end from suppliers where supplier_id = sin_supid),0)/100)))*stock_in_quantity) from stock_in_item where stock_in_sid = sin_id and (select main_cat from avon_categories where avon_cat_id = stock_in_acat) = 4),0)),2) hc_net, if(avon_health_discount != 0,@hcnet-((@hcnet/1.12)*(avon_health_discount/100)),0) avon_hcd from stock_in where sin_id = $sid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc) {
	$rec = $rs->fetch_array();
	if ($rec['sin_supid'] != 2) {
		$json .= '"content": "none"';
	} else {
		$json .= '"content": "info",';
		$json .= '"jcftd":' . $rec['avon_cftd'] . ',';
		$json .= '"jncftd":' . $rec['avon_ncftd'] . ',';
		$json .= '"jhsd":' . $rec['avon_hsd'] . ',';
		$json .= '"jhcd":' . $rec['avon_hcd'] . ',';
		$json .= '"janet":' . $rec['net_amt'];
	}
}
db_close();
$json .= '}] }';

echo $json;
break;

}

?>