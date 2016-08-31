<?php

session_start();
$uid = $_SESSION['user_id'];
$rbranch = $_SESSION['branch'];

require 'grants.php';
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
$pmrdate = (isset($_POST['pmrdate'])) ? $_POST['pmrdate'] : "";
if ($pmrdate != "") $pmrdate = date("Y-m-d",strtotime($pmrdate));
$pmrdid = (isset($_POST['pmrdid'])) ? $_POST['pmrdid'] : 0;
$pmramnt = (isset($_POST['pmramnt'])) ? $_POST['pmramnt'] : 0;
$pmrnote = (isset($_POST['pmrnote'])) ? $_POST['pmrnote'] : "";

$sql = "alter table manual_remittances AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "INSERT INTO `manual_remittances`(`manual_remittance_did`, `manual_remittance_date`, `manual_remittance_amount`, `manual_remittance_note`, `manual_remittance_cutoff`) VALUES ($pmrdid,'$pmrdate',$pmramnt,'$pmrnote',0)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Manual Remittance added.";

echo $str_response;
break;

case "update":
$mrid = $_GET['mrid'];
$pmrdate = (isset($_POST['pmrdate'])) ? $_POST['pmrdate'] : "";
if ($pmrdate != "") $pmrdate = date("Y-m-d",strtotime($pmrdate));
$pmrdid = (isset($_POST['pmrdid'])) ? $_POST['pmrdid'] : 0;
$pmramnt = (isset($_POST['pmramnt'])) ? $_POST['pmramnt'] : 0;
$pmrnote = (isset($_POST['pmrnote'])) ? $_POST['pmrnote'] : "";

$sql = "UPDATE `manual_remittances` SET ";
$sql .= "`manual_remittance_did`=$pmrdid,`manual_remittance_date`='$pmrdate',";
$sql .= "`manual_remittance_amount`=$pmramnt,";
$sql .= "`manual_remittance_note`='$pmrnote'";
$sql .= " WHERE manual_remittance_id = $mrid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Manual Remittance updated.";

echo $str_response;
break;

case "contents":
$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

// filter
$selco = (isset($_GET['selco'])) ? $_GET['selco'] : 0;
$fs = (isset($_GET['fs'])) ? $_GET['fs'] : "";
if ($fs != "") $fs = date("Y-m-d",strtotime($fs));
$fe = (isset($_GET['fe'])) ? $_GET['fe'] : "";
if ($fe != "") $fe = date("Y-m-d",strtotime($fe));
$fcustomer = (isset($_GET['fcustomer'])) ? $_GET['fcustomer'] : "";

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

//$sql = "select count(*) mcount from manual_remittances left join customers on manual_remittances.manual_remittance_did = customers.customer_id where manual_remittance_id != 0";
$pql = "select manual_remittance_id, manual_remittance_date, if(manual_remittance_cutoff = 0,'Undefined',if(manual_remittance_cutoff = 1,'First Cut-off','End of the day')) cutoff, concat(customer_fname, ' ', substr(customer_mname,1,1), '. ', customer_lname) dealer, manual_remittance_amount, manual_remittance_note, manual_remittance_cutoff from manual_remittances left join customers on manual_remittances.manual_remittance_did = customers.customer_id where manual_remittance_id != 0";

$c1 = " and manual_remittance_date = '$fs'";
if ($fe != "") $c1 = " and manual_remittance_date >= '$fs' and manual_remittance_date <= '$fe'";
$c2 = " and concat(customer_fname, ' ', customer_lname) like '%$fcustomer%'";
$c3 = " and manual_remittance_cutoff = $selco";

if (($fs == "") && ($fe == "")) $c1 = "";
if ($fcustomer == "") $c2 = "";
if ($selco == 0) $c3 = "";

//$sql .= $c1 . $c2;
$pql .= $c1 . $c2 . $c3;
//
/*
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
*/
$sql = $pql; // . $pageParam;
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
$rowstyle = "row-style-even";

$tcash_fc = 0;
$tcash_eod = 0;
if ($rc>0) {

$str_response  = '<form name="frmContent" id="frmContent">';
$str_response .= '<table id="content-page">';
$str_response .= '<thead>';
$str_response .= '<tr><td width="2%"><input type="checkbox" name="chk_checkall" id="chk_checkall" onclick="Check_all(this.form, this);" /></td><td>Date</td><td>Cutoff</td><td>Dealer\'s Name</td><td>Amount</td><td>Note</td><td>Remarks</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {
$rem = "";
$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
$str_response .= '<tr class="' . $rowstyle . '" onclick="chkRow(this);">';
$str_response .= '<td><input type="checkbox" name="chk_' . $rec['manual_remittance_id'] . '" id="chk_' . $rec['manual_remittance_id'] . '" onClick="Uncheck_Parent(\'chk_checkall\',this);" /></td>';
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
/*
if ($max_count > $perPage) {

$sNPage = new pageNav(9,'rManualRemittanceF()',$pageNum,$max_p);
$str_response .= '<tfoot><tr><td colspan="7">';
$str_response .= $sNPage->getNav();
$str_response .= '</td></tr></tfoot>';

}
*/

$disabled = "";
if (date("Y-m-d",strtotime($fs)) != date("Y-m-d",strtotime("+8 Hours"))) $disabled = "disabled";
$str_response .= '</table>';
$str_response .= '</form>'; // . $lpage;
$str_response .= '<form onsubmit="return false;">';
$str_response .= '<div style="padding-top: 10px; padding-right: 10px; text-align: right; color: #807c7c;">Total First Cut-off:  <span>Php. ' . number_format($tcash_fc,2) . '</span></div>';
$tacash_fc = 0;
if ($fcustomer == "") {
$str_response .= '<div style="padding-top: 5px; padding-right: 10px; text-align: right; color: #807c7c;">First Cut-off Actual Cash On Hand:  <input type="text" id="acash_fc" value="' . $actual_cash_fc_mr . '" size="10"' . $disabled . ' />&nbsp;<input type="submit" onclick="updateActualCash(1,\'' . $fs . '\');" value="Update" ' . $disabled . ' /></div>';
$tacash_fc = $tcash_fc - $actual_cash_fc_mr;
$str_response .= '<div style="padding-top: 10px; padding-right: 10px; text-align: right; color: #807c7c;">Deficit:  <span>Php. ' . number_format($tacash_fc,2) . '</span></div>';
}

$str_response .= '<div style="padding-top: 10px; padding-right: 10px; text-align: right; color: #807c7c;">Total End of the Day:  <span>Php. ' . number_format($tcash_eod,2) . '</span></div>';
$tacash_eod = 0;
if ($fcustomer == "") {
$str_response .= '<div style="padding-top: 5px; padding-right: 10px; text-align: right; color: #807c7c;">End of the Day Cash On Hand:  <input type="text" id="acash_eod" value="' . $actual_cash_eod_mr . '" size="10"' . $disabled . ' />&nbsp;<input type="submit" onclick="updateActualCash(2,\'' . $fs . '\');" value="Update" ' . $disabled . ' /></div>';
$tacash_eod = $tcash_eod - $actual_cash_eod_mr;
$str_response .= '<div style="padding-top: 10px; padding-right: 10px; text-align: right; color: #807c7c;">Deficit:  <span>Php. ' . number_format($tacash_eod,2) . '</span></div>';
}
$sub_total = 0;
$deficit_total = 0;
$grand_total = 0;
$sub_total = $tcash_fc + $tcash_eod;
$deficit_total = $tacash_fc + $tacash_eod;
$grand_total = $sub_total - $deficit_total;
$str_response .= '<div style="padding-top: 10px; padding-right: 10px; text-align: right; color: #807c7c;">Sub Total:  <span class="tcoh">Php. ' . number_format($sub_total,2) . '</span></div>';
$str_response .= '<div style="padding-top: 10px; padding-right: 10px; text-align: right; color: #807c7c;">Total Deficit:  <span class="tcoh">Php. ' . number_format($deficit_total,2) . '</span></div>';
$str_response .= '<div style="padding-top: 10px; padding-right: 10px; text-align: right; color: #807c7c;">Grand Total:  <span class="tcoh">Php. ' . number_format($grand_total,2) . '</span></div>';
$str_response .= '</form>';

}
db_close();

echo $str_response;
break;

case "edit":
$mrid = $_GET['mrid'];

$json = '{ "editmr": [';
$sql = "select manual_remittance_did, concat(customer_fname, ' ', customer_lname) dealer, manual_remittance_date, manual_remittance_amount, manual_remittance_note from manual_remittances left join customers on manual_remittances.manual_remittance_did = customers.customer_id where manual_remittance_id = $mrid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"jmrdn":"' . $rec['dealer'] . '",';
$json .= '"jmrdid":' . $rec['manual_remittance_did'] . ',';
$json .= '"jmrd":"' . date("m/d/Y",strtotime($rec['manual_remittance_date'])) . '",';
$json .= '"jmramt":' . $rec['manual_remittance_amount'] . ',';
$json .= '"jmrnote":"' . $rec['manual_remittance_note'] . '"';
$json .= '}';
}
db_close();

$json .= '] }';

echo $json;
break;

case "delete":
$mrid = $_POST['mrid'];

$sql = "delete from manual_remittances where manual_remittance_id in($mrid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Manual remittance deleted.";

echo $str_response;
break;

case "first_cutoff":
$sql = "update manual_remittances set manual_remittance_cutoff = 1 where manual_remittance_date = substr(now(),1,10)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "First cut-off has been processed";

echo $str_response;
break;

case "end_of_the_day":
$sql = "update manual_remittances set manual_remittance_cutoff = 2 where manual_remittance_date = substr(now(),1,10) and manual_remittance_cutoff = 0";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "End of the day has been processed";

echo $str_response;
break;

case "actual_cash":
$fdate = $_GET['fdate'];
$gco = $_GET['gco'];
$pac = (isset($_POST['pac'])) ? $_POST['pac'] : 0;

$sql = "alter table manual_remittance_actual_cash AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$manual_remittance_actual_cash = "manual_actual_cash_amount_fc";
if ($gco == 2) $manual_remittance_actual_cash = "manual_actual_cash_amount_eod";

$sql = "select * from manual_remittance_actual_cash where manual_actual_cash_date = '$fdate'";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$nsql = "update manual_remittance_actual_cash set $manual_remittance_actual_cash = $pac where manual_actual_cash_date = '$fdate'";
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
} else {
$nsql = "insert into manual_remittance_actual_cash ($manual_remittance_actual_cash, manual_actual_cash_date) values ($pac, '$fdate');";
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
}
db_close();

echo $str_response;
break;

case "check_cutoff":
$sql = "select * from manual_remittances where manual_remittance_cutoff = 1 and manual_remittance_date = substr(now(),1,10)";

db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc) {
	$str_response = $rc;
}
db_close();

echo $str_response;
break;

}

?>