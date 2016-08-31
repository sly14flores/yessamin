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

case "last_customer_id":
$sql = "select customer_member_no from customers order by customer_member_no desc limit 1";
$str_response = 0;
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$str_response = $rec['customer_member_no'] + 1;
}
db_close();

echo $str_response;
break;

case "add":
$pmnumber = (isset($_POST['pmnumber'])) ? $_POST['pmnumber'] : 0;
$pclimit = (isset($_POST['pclimit'])) ? $_POST['pclimit'] : 0;
$pfname = (isset($_POST['pfname'])) ? $_POST['pfname'] : "";
$plname = (isset($_POST['plname'])) ? $_POST['plname'] : "";
$pmname = (isset($_POST['pmname'])) ? $_POST['pmname'] : "";
$padd = (isset($_POST['padd'])) ? $_POST['padd'] : "";
$pcon = (isset($_POST['pcon'])) ? $_POST['pcon'] : "";
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pbday = (isset($_POST['pbday'])) ? $_POST['pbday'] : "";
$pbday = date("Y-m-d",strtotime($pbday));
$pjob = (isset($_POST['pjob'])) ? $_POST['pjob'] : "";
$pcstat = (isset($_POST['pcstat'])) ? $_POST['pcstat'] : "";
$pspouse = (isset($_POST['pspouse'])) ? addslashes($_POST['pspouse']) : "";
$prec = (isset($_POST['prec'])) ? addslashes($_POST['prec']) : "";
$pdiscountt = (isset($_POST['pdiscountt'])) ? $_POST['pdiscountt'] : 0;
$pcreditt = (isset($_POST['pcreditt'])) ? $_POST['pcreditt'] : 0;
$pcbranch = (isset($_POST['pcbranch'])) ? $_POST['pcbranch'] : 1;
$punlit = (isset($_POST['punlit'])) ? $_POST['punlit'] : 0;
$pcustomer_category = (isset($_POST['pcustomer_category'])) ? $_POST['pcustomer_category'] : "fd";

$pclimita = $pclimit;
if ($pcreditt == 2) $pclimita = 0;

$sql = "alter table customers AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();	

$sql  = "insert into customers (customer_member_no, customer_branch, credit_limit_type, customer_credit_limit, discount_type, customer_fname, customer_lname, customer_mname, address, contact_number, customer_age, customer_bday, customer_occupation, civil_status, spouse_name, recruiter, customer_date_added, customer_uid, credit_limit_bal, unli_terms, customer_category) ";
$sql .= "values ($pmnumber, $pcbranch, $pcreditt, $pclimit, $pdiscountt, '$pfname', '$plname', '$pmname', '$padd', '$pcon', $page, '$pbday', '$pjob', '$pcstat', '$pspouse', '$prec', CURRENT_TIMESTAMP, $uid, $pclimita, $punlit, '$pcustomer_category')";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Customer successfully added.";

echo $str_response;
break;

case "update":
$pmnumber = (isset($_POST['pmnumber'])) ? $_POST['pmnumber'] : 0;
$pclimit = (isset($_POST['pclimit'])) ? $_POST['pclimit'] : 0;
$pfname = (isset($_POST['pfname'])) ? $_POST['pfname'] : "";
$plname = (isset($_POST['plname'])) ? $_POST['plname'] : "";
$pmname = (isset($_POST['pmname'])) ? $_POST['pmname'] : "";
$padd = (isset($_POST['padd'])) ? $_POST['padd'] : "";
$pcon = (isset($_POST['pcon'])) ? $_POST['pcon'] : "";
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pbday = (isset($_POST['pbday'])) ? $_POST['pbday'] : "";
$pbday = date("Y-m-d",strtotime($pbday));
$pjob = (isset($_POST['pjob'])) ? $_POST['pjob'] : "";
$pcstat = (isset($_POST['pcstat'])) ? $_POST['pcstat'] : "";
$pspouse = (isset($_POST['pspouse'])) ? addslashes($_POST['pspouse']) : "";
$prec = (isset($_POST['prec'])) ? addslashes($_POST['prec']) : "";
$pdiscountt = (isset($_POST['pdiscountt'])) ? $_POST['pdiscountt'] : 0;
$pcreditt = (isset($_POST['pcreditt'])) ? $_POST['pcreditt'] : 0;
$pcbranch = (isset($_POST['pcbranch'])) ? $_POST['pcbranch'] : 1;
$paddcl = (isset($_POST['paddcl'])) ? $_POST['paddcl'] : 0;
$presetcl = (isset($_POST['presetcl'])) ? $_POST['presetcl'] : 0;
$punlit = (isset($_POST['punlit'])) ? $_POST['punlit'] : 0;
$pcustomer_category = (isset($_POST['pcustomer_category'])) ? $_POST['pcustomer_category'] : "fd";

$pclimit = $pclimit + $paddcl;

$cid = $_GET['cid'];

$sql  = "update customers set ";
$sql .= "customer_member_no = $pmnumber, ";
$sql .= "customer_branch = $pcbranch, ";
$sql .= "credit_limit_type = $pcreditt, ";
$sql .= "customer_credit_limit = $pclimit, ";
$sql .= "discount_type = $pdiscountt, "; 
$sql .= "customer_fname = '$pfname', ";
$sql .= "customer_lname = '$plname', ";
$sql .= "customer_mname = '$pmname', ";
$sql .= "address = '$padd', ";
$sql .= "contact_number = '$pcon', ";
$sql .= "customer_age = $page, ";
$sql .= "customer_bday = '$pbday', ";
$sql .= "customer_occupation = '$pjob', ";
$sql .= "civil_status = '$pcstat', ";
$sql .= "spouse_name = '$pspouse', ";
$sql .= "recruiter = '$prec', ";
$sql .= "unli_terms = $punlit, ";
$sql .= "customer_category = '$pcustomer_category', ";
if ($presetcl == 1) $sql .= "credit_limit_bal = $pclimit ";
else $sql .= "credit_limit_bal = credit_limit_bal + $paddcl ";
$sql .= "where customer_id = $cid";

db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Dealer's info successfully updated.";
echo $str_response;
break;

case "edit":
$cid = $_GET['cid'];

$json = '{ "editcustomer": [';
$sql = "select customer_member_no, credit_limit_type, customer_branch, customer_credit_limit, discount_type, customer_fname, customer_lname, customer_mname, address, contact_number, customer_age, customer_bday, customer_occupation, civil_status, spouse_name, recruiter, unli_terms, customer_category from customers where customer_id = $cid";

db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"jdn":' . $rec['customer_member_no'] . ',';
$json .= '"jdbr":' . $rec['customer_branch'] . ',';
$json .= '"jdclt":' . $rec['credit_limit_type'] . ',';
$json .= '"jdcl":' . $rec['customer_credit_limit'] . ',';
$json .= '"jddt":' . $rec['discount_type'] . ',';
$json .= '"jdfn":"' . $rec['customer_fname'] . '",';
$json .= '"jdln":"' . $rec['customer_lname'] . '",';
$json .= '"jdmn":"' . $rec['customer_mname'] . '",';
$json .= '"jdad":"' . $rec['address'] . '",';
$json .= '"jdc":"' . $rec['contact_number'] . '",';
$json .= '"jda":"' . $rec['customer_age'] . '",';
$json .= '"jdb":"' . date("m/d/Y",strtotime($rec['customer_bday'])) . '",';
$json .= '"jdo":"' . $rec['customer_occupation'] . '",';
$json .= '"jdcs":"' . $rec['civil_status'] . '",';
$json .= '"jdsn":"' . stripslashes($rec['spouse_name']) . '",';
$json .= '"jdr":"' . stripslashes($rec['recruiter']) . '",';
$json .= '"jdut":' . $rec['unli_terms'] . ',';
$json .= '"jcc":"' . $rec['customer_category'] . '"';
$json .= '}';
db_close();

$json .= '] }';
echo $json;
break;

case "delete":
$cid = $_POST['cid'];

$sql = "delete from customers where customer_id in ($cid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "delete from avon where avon_did in ($cid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Dealer(s) deleted.";
echo $str_response;
break;

case "contents":
$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

$sql = "select count(*) mcount from customers where customer_id != 0";
$pql = "select customer_id, customer_member_no, customer_branch, concat(customer_fname, ' ', customer_lname) fullname, address, contact_number, recruiter, (select discountt_desc from discount_types where discountt_id = discount_type) discount_desc, customer_credit_limit, customer_date_added, credit_limit_type, credit_limit_bal from customers where customer_id != 0";

// filters
$fbranch = (isset($_GET['fbranch'])) ? $_GET['fbranch'] : 0;
$fmno = (isset($_GET['fmno'])) ? $_GET['fmno'] : "";
$ffname = (isset($_GET['ffname'])) ? $_GET['ffname'] : "";
$fadd = (isset($_GET['fadd'])) ? $_GET['fadd'] : "";
$lcl = (isset($_GET['lcl'])) ? $_GET['lcl'] : 0;
$frname = (isset($_GET['frname'])) ? $_GET['frname'] : "";

$c1 = " and customer_branch = $fbranch";
$c2 = " and customer_member_no like '%$fmno%'";
$c3 = " and concat(customer_fname, ' ', customer_lname) like '%$ffname%'";
$c4 = " and address like '%$fadd%'";
$c5 = " and credit_limit_bal <= 200 and credit_limit_type != 2";
$c6 = " and recruiter like '%$frname%'";

if ($fbranch == 0) $c1 = "";
if ($fmno == "") $c2 = "";
if ($ffname == "") $c3 = "";
if ($fadd == "") $c4 = "";
if ($lcl == 0) $c5 = "";
if ($frname == "") $c6 = "";

$sql .= $c1 . $c2 . $c3 . $c4 . $c5 . $c6;
$pql .= $c1 . $c2 . $c3 . $c4 . $c5 . $c6;
//

$sql .= " order by concat(customer_fname, ' ', customer_lname)";
$pql .= " order by concat(customer_fname, ' ', customer_lname)";

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
$str_response .= '<tr><td width="2%"><input type="checkbox" name="chk_checkall" id="chk_checkall" onclick="Check_all(this.form, this);" /></td><td>Branch</td><td>M.No.</td><td>Fullname</td><td>Address</td><td>Contact(s)</td><td>Recruiter</td><!--<td>Discount Type</td>--><td>CL Type</td><td>CL</td><td>Total CL</td><td>Date Registered</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {
$total_cl = 0;
$cbranch = "Francey";
$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
if ($rec['customer_branch'] == 2) $cbranch = "Yessamin";
if ($rec['customer_branch'] == 3) $cbranch = "Sta. Maria";
$str_response .= '<tr class="' . $rowstyle . '" onclick="chkRow(this);">';
$str_response .= '<td><input type="checkbox" name="chk_' . $rec['customer_id'] . '" id="chk_' . $rec['customer_id'] . '" onClick="Uncheck_Parent(\'chk_checkall\',this);" /></td>';
$str_response .= '<td>' . $cbranch . '</td>';
$str_response .= '<td>' . $rec['customer_member_no'] . '</td>';
$str_response .= '<td>' . $rec['fullname'] . '</td>';
$str_response .= '<td>' . $rec['address'] . '</td>';
$str_response .= '<td>' . $rec['contact_number'] . '</td>';
$str_response .= '<td style="padding-left: 5px; padding-right: 5px;">' . $rec['recruiter'] . '</td>';
// $str_response .= '<td>' . $rec['discount_desc'] . '</td>';
$clt = "Initial";
switch ($rec['credit_limit_type']) {

case 1:
$clt = "Custom";
break;

case 2:
$clt = "Unlimited";
break;

}
$str_response .= '<td>' . $clt  . '</td>';
$str_response .= '<td style="padding-right: 3px; padding-left: 3px;">' . $rec['credit_limit_bal'] . '</td>';
// $total_cl = $rec['credit_limit_bal'] + totalCL($rec['customer_id']);
$str_response .= '<td style="padding-right: 3px; padding-left: 3px;">' . $total_cl . '</td>';
$str_response .= '<td>' . date("F j, Y",strtotime($rec['customer_date_added'])) . '</td>';
$str_response .= '</tr>';

}

$str_response .= '</tbody>';

if ($max_count > $perPage) {

$pNPage = new pageNav(3,'rCustomerF()',$pageNum,$max_p);
$str_response .= '<tfoot><tr><td colspan="7">';
$str_response .= $pNPage->getNav();
$str_response .= '</td></tr></tfoot>';

}

$str_response .= '</table></form>' . $lpage;

}
db_close();

echo $str_response;
//echo $sql;
break;

case "list_recruiters":
$ftb = (isset($_GET['ftb'])) ? $_GET['ftb'] : 0;
$sql = "select customer_id, customer_branch, concat(customer_fname, ' ', customer_lname) fullname, recruiter, customer_credit_limit, credit_limit_bal, discount_type, credit_limit_type from customers where customer_id IN (select max(customer_id) from customers group by recruiter)";
// if ($ftb != 0) $sql .= " and customer_branch = $tbranch";

$json = '[';
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$dbr = "";
// $dbr = " - Francey";
// if ($rec['customer_branch'] == 2) $dbr = " - Yessamin";
// if ($rec['customer_branch'] == 3) $dbr = " - Sta. Maria";
$json .= '{';
$json .= '"id":' . $rec['customer_id'] . ',';
$json .= '"text":"' . addslashes($rec['recruiter']) . $dbr . '",';
$json .= '"dealer":"' . addslashes($rec['recruiter']) . '",';
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

}

?>