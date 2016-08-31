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
$pdepbranch = (isset($_POST['pdepbranch'])) ? $_POST['pdepbranch'] : 0;
$pdepfrom = (isset($_POST['pdepfrom'])) ? $_POST['pdepfrom'] : "";
$pdepfromco = (isset($_POST['pdepfromco'])) ? $_POST['pdepfromco'] : 0;
$pdepto = (isset($_POST['pdepto'])) ? $_POST['pdepto'] : "";
$pdeptoco = (isset($_POST['pdeptoco'])) ? $_POST['pdeptoco'] : 0;
$pdepaname = (isset($_POST['pdepaname'])) ? $_POST['pdepaname'] : 0;
$pdepamt = (isset($_POST['pdepamt'])) ? $_POST['pdepamt'] : 0;
$pdepnote = (isset($_POST['pdepnote'])) ? addslashes($_POST['pdepnote']) : "";
if ($pdepfrom != "") $pdepfrom = date("Y-m-d",strtotime($pdepfrom));
if ($pdepto != "") $pdepto = date("Y-m-d",strtotime($pdepto));

$sql = "alter table deposits AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "INSERT INTO deposits (deposit_branch, deposit_from, deposit_from_cutoff, deposit_to, deposit_to_cutoff, deposit_account_name, deposit_amount, deposit_note, deposit_encoded_date, deposit_uid) VALUES ($pdepbranch, '$pdepfrom', $pdepfromco, '$pdepto', $pdeptoco, $pdepaname, $pdepamt, '$pdepnote', CURRENT_TIMESTAMP, $uid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Deposit succesfully added.";

echo $str_response;
break;

case "update":
$pdepbranch = (isset($_POST['pdepbranch'])) ? $_POST['pdepbranch'] : 0;
$pdepfrom = (isset($_POST['pdepfrom'])) ? $_POST['pdepfrom'] : "";
$pdepfromco = (isset($_POST['pdepfromco'])) ? $_POST['pdepfromco'] : 0;
$pdepto = (isset($_POST['pdepto'])) ? $_POST['pdepto'] : "";
$pdeptoco = (isset($_POST['pdeptoco'])) ? $_POST['pdeptoco'] : 0;
$pdepaname = (isset($_POST['pdepaname'])) ? $_POST['pdepaname'] : 0;
$pdepamt = (isset($_POST['pdepamt'])) ? $_POST['pdepamt'] : 0;
$pdepnote = (isset($_POST['pdepnote'])) ? addslashes($_POST['pdepnote']) : "";
if ($pdepfrom != "") $pdepfrom = date("Y-m-d",strtotime($pdepfrom));
if ($pdepto != "") $pdepto = date("Y-m-d",strtotime($pdepto));

$depid = $_GET['depid'];

$sql = "UPDATE deposits SET deposit_branch = $pdepbranch, deposit_from = '$pdepfrom', deposit_from_cutoff = $pdepfromco, deposit_to = '$pdepto', deposit_to_cutoff = $pdeptoco, deposit_account_name = $pdepaname, deposit_amount = $pdepamt, deposit_note = '$pdepnote' WHERE 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Deposit info succesfully updated.";

echo $str_response;
break;

case "edit":
$depid = $_GET['depid'];

$json = '{ "editdeposit": [';
$sql = "SELECT deposit_branch, deposit_from, deposit_from_cutoff, deposit_to, deposit_to_cutoff, deposit_account_name, deposit_amount, deposit_note FROM deposits WHERE deposit_id = $depid";
db_connect();
$json .= '{';
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc) {
	$rec = $rs->fetch_array();
	$json .= '"jdbr": ' . $rec['deposit_branch'] . ',';
	$json .= '"jdf": "' . date("m/d/Y",strtotime($rec['deposit_from'])) . '",';
	$json .= '"jdfc": ' . $rec['deposit_from_cutoff'] . ',';
	$json .= '"jdt": "' . date("m/d/Y",strtotime($rec['deposit_to'])) . '",';
	$json .= '"jdtc": ' . $rec['deposit_to_cutoff'] . ',';
	$json .= '"jdan": ' . $rec['deposit_account_name'] . ',';	
	$json .= '"jda": ' . $rec['deposit_amount'] . ',';
	$json .= '"jdn": "' . $rec['deposit_note'] . '"';
}
$json .= '}';
db_close();

$json .= ']}';

echo $json;
break;

case "contents":
$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

$sql = "select count(*) mcount from deposits where deposit_id != 0";
$pql = "select deposit_id, if(deposit_branch = 1,'Francey',if(deposit_branch = 2,'Yessamin','Francey')) branch, deposit_from, if(deposit_from_cutoff = 1,'First Cutoff','End of the day') from_co, deposit_to, if(deposit_to_cutoff = 1,'First Cutoff','End of the day') to_co, (select bank_account_name from bank_accounts where bank_account_id = deposit_account_name) account_name, deposit_amount, deposit_note, deposit_encoded_date, deposit_uid FROM deposits where deposit_id != 0";

// filters
$fbranch = (isset($_GET['fbranch'])) ? $_GET['fbranch'] : 0;
$fan = (isset($_GET['fan'])) ? $_GET['fan'] : 0;
$fs = (isset($_GET['fs'])) ? $_GET['fs'] : "";
$fe = (isset($_GET['fe'])) ? $_GET['fe'] : "";
if ($fs != "") $fs = date("Y-m-d",strtotime($fs));
if ($fe != "") $fe = date("Y-m-d",strtotime($fe));

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
$pql .= $c1 . $c2 . $c3;
//

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

if ($rc) {

$str_response  = '<form name="frmContent" id="frmContent">';
$str_response .= '<table id="content-page">';
$str_response .= '<thead>';
$str_response .= '<tr><td width="2%"><input type="checkbox" name="chk_checkall" id="chk_checkall" onclick="Check_all(this.form, this);" /></td><td>Branch</td><td>Date</td><td>Account Name</td><td>From</td><td>Cutoff</td><td>To</td><td>Cutoff</td><td>Amount</td><td>Note</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$str_response .= '<tr class="' . $rowstyle . '" onclick="chkRow(this);">';
$str_response .= '<td><input type="checkbox" name="chk_' . $rec['deposit_id'] . '" id="chk_' . $rec['deposit_id'] . '" onClick="Uncheck_Parent(\'chk_checkall\',this);" /></td>';
$str_response .= '<td>' . $rec['branch'] . '</td>';
$str_response .= '<td>' . date("M j, Y",strtotime($rec['deposit_encoded_date'])) . '</td>';
$str_response .= '<td>' . $rec['account_name'] . '</td>';
$str_response .= '<td>' . date("M j, Y",strtotime($rec['deposit_from'])) . '</td>';
$str_response .= '<td>' . $rec['from_co'] . '</td>';
$str_response .= '<td>' . date("M j, Y",strtotime($rec['deposit_to'])) . '</td>';
$str_response .= '<td>' . $rec['to_co'] . '</td>';
$str_response .= '<td>' . number_format($rec['deposit_amount'],2) . '</td>';
$str_response .= '<td>' . $rec['deposit_note'] . '</td>';
$str_response .= '</tr>';
}

$str_response .= '</tbody>';

if ($max_count > $perPage) {

$pNPage = new pageNav(10,'rDepositsF()',$pageNum,$max_p);
$str_response .= '<tfoot><tr><td colspan="7">';
$str_response .= $pNPage->getNav();
$str_response .= '</td></tr></tfoot>';

}

$str_response .= '</table></form>' . $lpage;

}
db_close();

echo $str_response;
break;

case "select_bank_account":
$brid = (isset($_GET['brid'])) ? $_GET['brid'] : 0;

$sql = "select bank_account_id, bank_account_name from bank_accounts";
if ($brid != 0) $sql .= " where bank_account_branch = $brid";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc) {
	for ($i=0; $i<$rc; ++$i) {
	$rec = $rs->fetch_array();
	$str_response .= '<option value="' . $rec['bank_account_id'] . '">' . $rec['bank_account_name'] . '</option>';
	}
}
db_close();

echo $str_response;
break;

case "delete":
$depid = $_POST['depid'];

$sql = "delete from deposits where deposit_id in ($depid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Deposit(s) succesfully deleted.";
echo $str_response;
break;

}

?>
