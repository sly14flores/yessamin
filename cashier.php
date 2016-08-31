<?php

session_start();
$uid = $_SESSION['user_id'];

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
$putype = $_POST['putype'];
$puserbranch = $_POST['puserbranch'];
$pusername = (isset($_POST['pusername'])) ? addslashes($_POST['pusername']) : "";
$ppassword = (isset($_POST['ppassword'])) ? $_POST['ppassword'] : "";
$pcashierid = (isset($_POST['pcashierid'])) ? addslashes($_POST['pcashierid']) : 0;
$pfname = (isset($_POST['pfname'])) ? addslashes($_POST['pfname']) : "";
$plname = (isset($_POST['plname'])) ? addslashes($_POST['plname']) : "";
$ppos = (isset($_POST['ppos'])) ? addslashes($_POST['ppos']) : "";
$padd = (isset($_POST['padd'])) ? addslashes($_POST['padd']) : "";
$pcon = (isset($_POST['pcon'])) ? addslashes($_POST['pcon']) : "";

$sql = "alter table users AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "insert into users (employee_id, username, password, firstname, lastname, position, user_address, user_contact, grants, user_branch, user_date) ";
$sql .= "values ('$pcashierid', '$pusername', '$ppassword', '$pfname', '$plname', '$ppos', '$padd', '$pcon', '$putype', $puserbranch, CURRENT_TIMESTAMP)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Cashier successfully added.";

echo $str_response;
break;

case "update":
$putype = $_POST['putype'];
$puserbranch = $_POST['puserbranch'];
$pusername = (isset($_POST['pusername'])) ? addslashes($_POST['pusername']) : "";
$ppassword = (isset($_POST['ppassword'])) ? $_POST['ppassword'] : "";
$pcashierid = (isset($_POST['pcashierid'])) ? addslashes($_POST['pcashierid']) : 0;
$pfname = (isset($_POST['pfname'])) ? addslashes($_POST['pfname']) : "";
$plname = (isset($_POST['plname'])) ? addslashes($_POST['plname']) : "";
$ppos = (isset($_POST['ppos'])) ? addslashes($_POST['ppos']) : "";
$padd = (isset($_POST['padd'])) ? addslashes($_POST['padd']) : "";
$pcon = (isset($_POST['pcon'])) ? addslashes($_POST['pcon']) : "";

$cid = $_GET['cid'];

$sql  = "update users set ";
$sql .= "employee_id = '$pcashierid', ";
$sql .= "username = '$pusername', ";
$sql .= "password = '$ppassword', ";
$sql .= "firstname = '$pfname', ";
$sql .= "lastname = '$plname', ";
$sql .= "position = '$ppos', ";
$sql .= "user_address = '$padd', ";
$sql .= "user_contact = '$pcon', ";
$sql .= "grants = '$putype', ";
$sql .= "user_branch = $puserbranch ";
$sql .= "where user_id = $cid";

db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$_SESSION['branch'] = $puserbranch;
$str_response = "Cashier's info successfully updated.";
echo $str_response;
break;

case "edit":
$cid = $_GET['cid'];

$json = '{ "editcashier": [';
$sql = "select username, password, employee_id, firstname, lastname, position, user_address, user_contact, grants, user_branch from users where user_id = $cid";

db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"jug":"' . $rec['grants'] . '",';
$json .= '"jub":' . $rec['user_branch'] . ',';
$json .= '"jun":"' . stripslashes($rec['username']) . '",';
$json .= '"jup":"' . $rec['password'] . '",';
$json .= '"jcid":"' . stripslashes($rec['employee_id']) . '",';
$json .= '"jfn":"' . stripslashes($rec['firstname']) . '",';
$json .= '"jln":"' . stripslashes($rec['lastname']) . '",';
$json .= '"jpos":"' . stripslashes($rec['position']) . '",';
$json .= '"jadd":"' . stripslashes($rec['user_address']) . '",';
$json .= '"jcon":"' . stripslashes($rec['user_contact']) . '"';
$json .= '}';
db_close();

$json .= '] }';
echo $json;
break;

case "delete":
$cid = $_POST['cid'];

$sql = "update users set is_deleted = 1 where user_id in ($cid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Cashier(s) deleted.";
echo $str_response;
break;

case "contents":
$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

$sql = "select count(*) mcount from users where user_id != 0 and is_deleted = 0 and is_built_in = 0";
$pql = "select grants, user_id, employee_id, concat(firstname, ' ', lastname) fullname, username, position, user_address, user_contact, user_branch, user_date from users where user_id != 0 and is_deleted = 0 and is_built_in = 0";

// filters
$fcname = (isset($_GET['fcname'])) ? $_GET['fcname'] : "";
$fcashierid = (isset($_GET['fcashierid'])) ? $_GET['fcashierid'] : "";

$c1 = " and concat(firstname, ' ', lastname) like '%$fcname%'";
$c2 = " and employee_id like '%$fcashierid%'";

if ($fcname == "") $c1 = "";
if ($fcashierid == "") $c2 = "";

$sql .= $c1 . $c2;
$pql .= $c1 . $c2;
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

if ($rc>0) {

$str_response  = '<form name="frmContent" id="frmContent">';
$str_response .= '<table id="content-page">';
$str_response .= '<thead>';
$str_response .= '<tr><td width="2%"><input type="checkbox" name="chk_checkall" id="chk_checkall" onclick="Check_all(this.form, this);" /></td><td>Branch</td><td>ID</td><td>Type</td><td>Fullname</td><td>Username</td><td>Position</td><td>Address</td><td>Contacts</td><td>Date Registered</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {

$ug = "Cashier";
$cbranch = "Francey";
$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
$str_response .= '<tr class="' . $rowstyle . '" onclick="chkRow(this);">';
$str_response .= '<td><input type="checkbox" name="chk_' . $rec['user_id'] . '" id="chk_' . $rec['user_id'] . '" onClick="Uncheck_Parent(\'chk_checkall\',this);" /></td>';
if ($rec['user_branch'] == 2) $cbranch = "Yessamin";
if ($rec['user_branch'] == 3) $cbranch = "Sta. Maria";
$str_response .= '<td>' . $cbranch . '</td>';
$str_response .= '<td>' . stripslashes($rec['employee_id']) . '</td>'; 
if ((int)$rec['grants'] == 50) $ug = "Assistant Admin";
if ((int)$rec['grants'] == 100) $ug = "Admin";
if ((int)$rec['grants'] == 5) $ug = "Encoder";
$str_response .= '<td>' . $ug . '</td>';
$str_response .= '<td>' . stripslashes($rec['fullname']) . '</td>';
$str_response .= '<td>' . stripslashes($rec['username']) . '</td>';
$str_response .= '<td>' . stripslashes($rec['position']) . '</td>';
$str_response .= '<td>' . stripslashes($rec['user_address']) . '</td>';
$str_response .= '<td>' . stripslashes($rec['user_contact']) . '</td>';
$str_response .= '<td>' . date("F j, Y",strtotime($rec['user_date'])) . '</td>';
$str_response .= '</tr>';

}

$str_response .= '</tbody>';

if ($max_count > $perPage) {

$pNPage = new pageNav(8,'rCashierF()',$pageNum,$max_p);
$str_response .= '<tfoot><tr><td colspan="7">';
$str_response .= $pNPage->getNav();
$str_response .= '</td></tr></tfoot>';

}

$str_response .= '</table></form>' . $lpage;

}

db_close();

echo $str_response;
break;

}

?>