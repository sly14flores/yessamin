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

case "edit":
$adid = $_GET['adid'];

$json = '{ "editavon": [';
$sql = "select concat(customer_fname, ' ', substr(customer_mname,1,1), ', ', customer_lname) dealer, avon_cft, avon_ncft, avon_hcms, avon_hc from avon left join customers on avon.avon_did = customers.customer_id where avon_did = $adid";
db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"jad":"' . $rec['dealer'] . '",';
$json .= '"jcft":' . $rec['avon_cft'] . ',';
$json .= '"jncft":' . $rec['avon_ncft'] . ',';
$json .= '"jhcms":' . $rec['avon_hcms'] . ',';
$json .= '"jhc":' . $rec['avon_hc'] . '';
$json .= '}';
db_close();
$json .= '] }';

echo $json;
break;

case "update":
$padid = $_POST['padid'];
$pcft = $_POST['pcft'];
$pncft = $_POST['pncft'];
$phcms = $_POST['phcms'];
$phc = $_POST['phc'];

$sql  = "update avon set ";
$sql .= "avon_cft = $pcft, ";
$sql .= "avon_ncft = $pncft, ";
$sql .= "avon_hcms = $phcms, ";
$sql .= "avon_hc = $phc ";
$sql .= "where avon_did = $padid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Dealer's avon info updated";

echo $str_response;
break;

case "contents":
$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

$sql = "select count(*) mcount from avon left join customers on avon.avon_did = customers.customer_id where avon_did != 0";
$pql = "select avon_did, concat(customer_fname, ' ', substr(customer_mname,1,1), ', ', customer_lname) dealer, avon_cft, avon_ncft, avon_hcms, avon_hc from avon left join customers on avon.avon_did = customers.customer_id where avon_did != 0";

// filter
$fcustomer = (isset($_GET['fcustomer'])) ? $_GET['fcustomer'] : "";

$c1 = " and concat(customer_fname, ' ', substr(customer_mname,1,1), '. ', customer_lname) like '%$fcustomer%'";

if ($fcustomer == "") $c1 = "";

$sql .= $c1;
$pql .= $c1;
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
$str_response .= '<tr><td>Dealer\'s Name</td><td>CFT</td><td>NCFT</td><td>HCMS</td><td>HC</td><td>Tool</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {

$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
$str_response .= '<tr class="' . $rowstyle . '" onclick="chkRow(this);">';
$str_response .= '<td>' . $rec['dealer'] . '</td>';
$str_response .= '<td>' . $rec['avon_cft'] . '</td>';
$str_response .= '<td>' . $rec['avon_ncft'] . '</td>';
$str_response .= '<td>' . $rec['avon_hcms'] . '</td>';
$str_response .= '<td>' . $rec['avon_hc'] . '</td>';
$str_response .= '<td><a href="javascript: editAvon(' . $rec['avon_did'] . ');" class="tooltip-min"><img src="images/edit.png" /><span>Edit</span></a></td>';
$str_response .= '</tr>';

}

$str_response .= '</tbody>';

if ($max_count > $perPage) {

$sNPage = new pageNav(8,'rAvonF()',$pageNum,$max_p);
$str_response .= '<tfoot><tr><td colspan="7">';
$str_response .= $sNPage->getNav();
$str_response .= '</td></tr></tfoot>';

}

$str_response .= '</table></form>' . $lpage;
}
db_close();

echo $str_response;
break;

}

?>