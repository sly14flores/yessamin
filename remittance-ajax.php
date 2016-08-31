<?php

session_start();
$uid = $_SESSION['user_id'];
$pbranch = $_SESSION['branch'];

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
$pdcat = (isset($_POST['pdcat'])) ? $_POST['pdcat'] : "";
$pddesc = (isset($_POST['pddesc'])) ? $_POST['pddesc'] : "";
$pdamt = (isset($_POST['pdamt'])) ? $_POST['pdamt'] : 0;
$pdnote = (isset($_POST['pdnote'])) ? $_POST['pdnote'] : "";

$sql = "alter table deductions AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "insert into deductions (ded_cat, ded_desc, ded_amt, ded_note, ded_date, ded_uid, ded_branch) values('$pdcat', '$pddesc', $pdamt, '$pdnote', CURRENT_TIMESTAMP, $uid, $pbranch)";

db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Deduction added.";

echo $str_response;
break;

case "update":
$pdcat = (isset($_POST['pdcat'])) ? $_POST['pdcat'] : "";
$pddesc = (isset($_POST['pddesc'])) ? $_POST['pddesc'] : "";
$pdamt = (isset($_POST['pdamt'])) ? $_POST['pdamt'] : 0;
$pdnote = (isset($_POST['pdnote'])) ? $_POST['pdnote'] : "";

$dedid = $_GET['dedid'];
$sql = "update deductions set ded_cat = '$pdcat', ded_desc = '$pddesc', ded_amt = $pdamt, ded_note = '$pdnote' where ded_id = $dedid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Dedection's info updated.";

echo $str_response;
break;

case "edit_deduction":
$dedid = $_GET['dedid'];

$json = '{ "editded":[';
$sql = "select ded_cat, ded_desc, ded_amt, ded_note from deductions where ded_id = $dedid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"jdc":"' . $rec['ded_cat'] . '",';
$json .= '"jdd":"' . $rec['ded_desc'] . '",';
$json .= '"jda":' . $rec['ded_amt'] . ',';
$json .= '"jdn":"' . $rec['ded_note'] . '"';
$json .= '}';
}
db_close();
$json .= '] }';

echo $json;
break;

case "contents":
$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

// filter
$fbranch = (isset($_GET['fbranch'])) ? $_GET['fbranch'] : 1;
$fdate = (isset($_GET['fdate'])) ? $_GET['fdate'] : "";
$fcutoff = (isset($_GET['fcutoff'])) ? $_GET['fcutoff'] : 0;
if ($fdate != "") {
$ofdate = $fdate;
$fdate = date("Y-m-d",strtotime($fdate));
}

$actual_cash_fc = 0;
$actual_cash_eod = 0;
$pql = "SELECT `actual_cash_fc_amount`, `actual_cash_eod_amount` FROM `remittance_actual_cash` WHERE `actual_cash_branch` = $fbranch AND `actual_cash_date` = '$fdate'";
db_connect();
$rs = $db_con->query($pql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$actual_cash_fc = $rec['actual_cash_fc_amount'];
$actual_cash_eod = $rec['actual_cash_eod_amount'];
}
db_close();

// $sql = "select count(*) mcount from receipt_payments left join receipts on receipt_payments.payment_receipt_no = receipts.receipt_no where payment_no != 0";
$pql = "select receipt_did, payment_date, receipt_branch, (select concat(customer_fname, ' ', customer_lname) from customers where customer_id = receipt_did) dealer, payment_transaction_no, payment_receipt_no, sum(payment_amount) amount, (select concat(firstname, ' ', lastname) from users where user_id = receipt_uid) cashier, cut_off from receipt_payments left join receipts on receipt_payments.payment_receipt_no = receipts.receipt_no where payment_no != 0";

$c1 = " and (select receipt_branch from receipts where receipt_no = payment_receipt_no) = $fbranch";
$c2 = " and payment_date = '$fdate'";
$c3 = " and cut_off = $fcutoff";

if ($fdate == "") $c2 = "";
if ($fcutoff == 0) $c3 = "";

//$sql .= $c1 . $c2;
$pql .= $c1 . $c2 . $c3;
//

//$sql .= " group by payment_transaction_no";
$pql .= " group by payment_transaction_no, cut_off";

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

$_1cf = 0;
$_eod = 0;
$tremit = 0; // total remittance
if ($rc>0) {

$str_response  = '<form name="frmContent" id="frmContent">';
$str_response .= '<table id="content-page">';
$str_response .= '<thead>';
$str_response .= '<tr><td colspan="8">REMITTANCES</td></tr>';
$str_response .= '<tr><td>Date</td><td>Cutoff</td><td>Branch</td><td>Name</td><td>Tra.No.</td><td>Ref.No.</td><td>Amount</td><td>Cashier</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {
$cutoff = "Undefine";
$rbr = "Francey";
$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
$dn = $rec['dealer'];
if ($rec['receipt_did'] == 0) $dn = "Walk-in Cash";
if ($rec['receipt_branch'] == 2) $rbr = "Yessamin";
if ($rec['receipt_branch'] == 3) $rbr = "Sta. Maria";
if ($rec['cut_off'] == 1) $cutoff = "First Cutoff";
if ($rec['cut_off'] == 2) $cutoff = "End of the day";
$str_response .= '<tr class="' . $rowstyle . '" onclick="chkRow(this);">';
$str_response .= '<td>' . date("F j, Y",strtotime($rec['payment_date'])) . '</td>';
$str_response .= '<td>' . $cutoff . '</td>';
$str_response .= '<td>' . $rbr . '</td>';
$str_response .= '<td>' . $dn . '</td>';
$str_response .= '<td>' . $rec['payment_transaction_no'] . '</td>';
$str_response .= '<td>' . $rec['payment_receipt_no'] . '</td>';
$str_response .= '<td>' . $rec['amount'] . '</td>';
$str_response .= '<td>' . $rec['cashier'] . '</td>';
$str_response .= '</tr>';
if ($rec['cut_off'] == 1) $_1cf += $rec['amount'];
if ($rec['cut_off'] == 2) $_eod += $rec['amount'];
$tremit = $tremit + $rec['amount'];
}

$str_response .= '<tr><td colspan="6">&nbsp;</td><td>Total:</td><td>' . round($tremit,2) . '</td></tr>';

$str_response .= '</tbody>';

/*
if ($max_count > $perPage) {

$sNPage = new pageNav(6,'rRemittanceF()',$pageNum,$max_p);
$str_response .= '<tfoot><tr><td colspan="7">';
$str_response .= $sNPage->getNav();
$str_response .= '</td></tr></tfoot>';

}
*/

$str_response .= '</table></form>'; // . $lpage;
}
db_close();


// Duductions
$tded = 0;
$sql = "select ded_branch, ded_cat, ded_id, ded_date, (select concat(firstname, ' ', lastname) from users where user_id = ded_uid) cashier, ded_desc, ded_amt, ded_note from deductions where ded_id != 0";

// filter
$c1 = " and ded_branch = $fbranch";
$c2 = " and ded_date = '$fdate'";

if ($fdate == "") $c2 = "";

$sql .= $c1 . $c2;
// $pql .= $c1 . $c2;
//

db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
$rowstyle = "row-style-even";

if ($rc>0) {

$str_response .= '<form name="frmContent2" id="frmContent2">';
$str_response .= '<table id="content-page">';
$str_response .= '<thead>';
$str_response .= '<tr><td colspan="8">DEDUCTIONS</td></tr>';
$str_response .= '<tr><td width="2%"><input type="checkbox" name="chk_checkall" id="chk_checkall" onclick="Check_all(this.form, this);" /></td><td>Date</td><td>Branch</td><td>Category</td><td>Description</td><td>Note</td><td>Amount</td><td>Cashier</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {
$rbranch = "Francey";
$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
if ($rec['ded_branch'] == 2) $rbranch = "Yessamin";
$str_response .= '<tr class="' . $rowstyle . '" onclick="chkRow(this);">';
$str_response .= '<td><input type="checkbox" name="chk_' . $rec['ded_id'] . '" id="chk_' . $rec['ded_id'] . '" onClick="Uncheck_Parent(\'chk_checkall\',this);" /></td>';
$str_response .= '<td>' . date("F j, Y",strtotime($rec['ded_date'])) . '</td>';
$str_response .= '<td>' . $rbranch . '</td>';
$str_response .= '<td>' . $rec['ded_cat'] . '</td>';
$str_response .= '<td>' . $rec['ded_desc'] . '</td>';
$str_response .= '<td>' . $rec['ded_note'] . '</td>';
$str_response .= '<td>' . $rec['ded_amt'] . '</td>';
$str_response .= '<td>' . $rec['cashier'] . '</td>';
$str_response .= '</tr>';
$tded = $tded + $rec['ded_amt'];
}

$str_response .= '<tr><td colspan="6">&nbsp;</td><td>Total:</td><td>' . round($tded,2) . '</td></tr>';
$str_response .= '</tbody>';
$str_response .= '</table>';
$str_response .= '</form>'; // . $lpage;

}
db_close();

$fc_deficit = 0;
$eod_deficit = 0;
$totaleod = 0;
$totalr = 0;
$str_response .= '<form name="frmContent3" id="frmContent3" onsubmit="return false;">';
$str_response .= '<table id="content-page">';
$str_response .= '<thead>';
$str_response .= '<tr><td colspan="7">SUMMARY</td></tr>';
$str_response .= '</thead>';
$str_response .= '<tbody>';

$disabled = "disabled";
if (date("Y-m-d 00:00:00",strtotime($ofdate)) == date("Y-m-d 00:00:00")) $disabled = "";
if ( ($fcutoff == 0) || ($fcutoff == 1) ) $str_response .= '<tr><td width="10%" colspan="2">First Cutoff:</td><td style="font-weight: bold;">' . round($_1cf,2) . '</td><td colspan="4">Actual Cash:&nbsp;<input type="text" id="acashfc" value="' . $actual_cash_fc . '" ' . $disabled . ' />&nbsp;<input type="submit" value="Update" ' . $disabled . ' onclick="updateActualFcf(' . $fbranch . ',\'' . $ofdate . '\');" /></td></tr>';
if ($fcutoff == 2) $actual_cash_fc = 0;
if ( ($fcutoff == 0) || ($fcutoff == 1) ) $fc_deficit = $_1cf - $actual_cash_fc;
if ( ($fcutoff == 0) || ($fcutoff == 1) ) $str_response .= '<tr><td width="10%" colspan="2">Deficit:</td><td style="font-weight: bold;">' . round($fc_deficit,2) . '</td><td colspan="4">&nbsp;</td></tr>';

if ( ($fcutoff == 0) || ($fcutoff == 2) ) $str_response .= '<tr><td width="10%" colspan="2">End of the day:</td><td style="font-weight: bold;">' . round($_eod,2) . '</td><td colspan="4">Actual Cash:&nbsp;<input type="text" id="acasheod" value="' . $actual_cash_eod . '" ' . $disabled . ' />&nbsp;<input type="submit" value="Update" ' . $disabled . ' onclick="updateActualEod(' . $fbranch . ',\'' . $ofdate . '\');" /></td></tr>';
if ($fcutoff == 1) $actual_cash_eod = 0;
if ( ($fcutoff == 0) || ($fcutoff == 2) ) $eod_deficit = $_eod - ($actual_cash_eod + $tded);
if ( ($fcutoff == 0) || ($fcutoff == 2) ) $str_response .= '<tr><td width="10%" colspan="2" style="padding-left: 25px;">Deductions:</td><td style="font-weight: bold;">' . round($tded,2) . '</td><td colspan="4">&nbsp;</td></tr>';
if ( ($fcutoff == 0) || ($fcutoff == 2) ) $totaleod = $_eod - $tded;
if ( ($fcutoff == 0) || ($fcutoff == 2) ) $str_response .= '<tr><td width="10%" colspan="2" style="padding-left: 25px;">Total End of the day:</td><td style="font-weight: bold;">' . round($totaleod,2) . '</td><td colspan="4">&nbsp;</td></tr>';
if ( ($fcutoff == 0) || ($fcutoff == 2) ) $str_response .= '<tr><td width="10%" colspan="2" style="padding-left: 25px;">Deficit:</td><td style="font-weight: bold;">' . round($eod_deficit,2) . '</td><td colspan="4">&nbsp;</td></tr>';

if ($fcutoff == 0) $totalr = $actual_cash_fc + $totaleod;
if ($fcutoff == 0) $str_response .= '<tr><td width="10%" colspan="2">Total Remittance:</td><td style="font-weight: bold;">' . round($totalr,2) . '</td><td colspan="4">&nbsp;</td></tr>';

$str_response .= '</tbody>';
$str_response .= '</table>';
$str_response .= '</form>';

echo $str_response;
break;

case "actual_cash":
$pac = (isset($_POST['pac'])) ? $_POST['pac'] : 0;
$fbranch = (isset($_GET['fbranch'])) ? $_GET['fbranch'] : 1;
$fdate = (isset($_GET['fdate'])) ? $_GET['fdate'] : "";
if ($fdate != "") $fdate = date("Y-m-d",strtotime($fdate));

$sql = "alter table remittance_actual_cash AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "SELECT `actual_cash_id`, `actual_cash_amount` FROM `remittance_actual_cash` WHERE `actual_cash_branch` = $fbranch AND `actual_cash_date` = '$fdate'";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$nsql = "UPDATE `remittance_actual_cash` SET `actual_cash_amount` = $pac WHERE `actual_cash_branch` = $fbranch AND `actual_cash_date` = '$fdate'";
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
} else {
$nsql = "INSERT INTO `remittance_actual_cash`(`actual_cash_branch`, `actual_cash_date`, `actual_cash_amount`) VALUES ($fbranch, '$fdate', $pac)";
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
}
db_close();

echo $str_response;
break;

case "actual_cash_fc":
$pac = (isset($_POST['pac'])) ? $_POST['pac'] : 0;
$fbranch = (isset($_GET['fbranch'])) ? $_GET['fbranch'] : 1;
$fdate = (isset($_GET['fdate'])) ? $_GET['fdate'] : "";
if ($fdate != "") $fdate = date("Y-m-d",strtotime($fdate));

$sql = "alter table remittance_actual_cash AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "SELECT `actual_cash_id`, `actual_cash_fc_amount` FROM `remittance_actual_cash` WHERE `actual_cash_branch` = $fbranch AND `actual_cash_date` = '$fdate'";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$nsql = "UPDATE `remittance_actual_cash` SET `actual_cash_fc_amount` = $pac WHERE `actual_cash_branch` = $fbranch AND `actual_cash_date` = '$fdate'";
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
} else {
$nsql = "INSERT INTO `remittance_actual_cash`(`actual_cash_branch`, `actual_cash_date`, `actual_cash_fc_amount`) VALUES ($fbranch, '$fdate', $pac)";
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
}
db_close();

echo $str_response;
break;

case "actual_cash_eod":
$pac = (isset($_POST['pac'])) ? $_POST['pac'] : 0;
$fbranch = (isset($_GET['fbranch'])) ? $_GET['fbranch'] : 1;
$fdate = (isset($_GET['fdate'])) ? $_GET['fdate'] : "";
if ($fdate != "") $fdate = date("Y-m-d",strtotime($fdate));

$sql = "alter table remittance_actual_cash AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "SELECT `actual_cash_id`, `actual_cash_eod_amount` FROM `remittance_actual_cash` WHERE `actual_cash_branch` = $fbranch AND `actual_cash_date` = '$fdate'";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$nsql = "UPDATE `remittance_actual_cash` SET `actual_cash_eod_amount` = $pac WHERE `actual_cash_branch` = $fbranch AND `actual_cash_date` = '$fdate'";
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
} else {
$nsql = "INSERT INTO `remittance_actual_cash`(`actual_cash_branch`, `actual_cash_date`, `actual_cash_eod_amount`) VALUES ($fbranch, '$fdate', $pac)";
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
}
db_close();

echo $str_response;
break;

case "delete":
$dedid = $_POST['dedid'];

$sql = "delete from deductions where ded_id in ($dedid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Duduction(s) deleted.";

echo $str_response;
break;

case "first_cutoff":
$fbranch = (isset($_GET['fbranch'])) ? $_GET['fbranch'] : 1;
$fdate = (isset($_GET['fdate'])) ? $_GET['fdate'] : "";
if ($fdate != "") {
$fdate = date("Y-m-d",strtotime($fdate));
}

$sql = "select * from receipt_payments left join receipts on receipt_payments.payment_receipt_no = receipts.receipt_no where receipt_branch = $fbranch and payment_date = '$fdate' and cut_off = 1";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$str_response = "You can only apply First Cutoff once.";
} else {
$nsql = "update receipt_payments left join receipts on receipt_payments.payment_receipt_no = receipts.receipt_no set cut_off = 1 where receipt_branch = $fbranch and payment_date = '$fdate'";
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
$str_response = "Remittances on and prior to time: " . date("h:i A",strtotime("+8 Hours")) . " on " . date("F j, Y",strtotime($fdate)) . " has been marked as First Cutoff. Succeeding payments will fall into End of the day.";
}
db_close();

echo $str_response;
break;

case "end_of_the_day":
$fbranch = (isset($_GET['fbranch'])) ? $_GET['fbranch'] : 1;
$fdate = (isset($_GET['fdate'])) ? $_GET['fdate'] : "";
if ($fdate != "") {
$fdate = date("Y-m-d",strtotime($fdate));
}

$sql = "update receipt_payments left join receipts on receipt_payments.payment_receipt_no = receipts.receipt_no set cut_off = 2 where receipt_branch = $fbranch and payment_date = '$fdate'";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Today's remittances has been marked as End of the day.";

echo $str_response;
break;


}

?>