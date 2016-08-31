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
$sid = $_GET['sid'];
$json = '{ "editdiscount":[';

$sql = "select basic_discount, top_seller_discount, outright_discount, special_discount, sl_discount from suppliers where supplier_id = $sid";
db_connect();
$json .= '{';
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$json .= '"jbd":' . $rec['basic_discount'] . ',';
$json .= '"jtd":' . $rec['top_seller_discount'] . ',';
$json .= '"jod":' . $rec['outright_discount'] . ',';
$json .= '"jsd":' . $rec['special_discount'] . ',';
$json .= '"jsld":' . $rec['sl_discount'] . '';
db_close();
$json .= '}';
$json .= ']}';
echo $json;
break;

case "update":
$sid = $_GET['sid'];
$pbasicd = (isset($_POST['pbasicd'])) ? $_POST['pbasicd'] : 0;
$ptopd = (isset($_POST['ptopd'])) ? $_POST['ptopd'] : 0;
$poutd = (isset($_POST['poutd'])) ? $_POST['poutd'] : 0;
$psped = (isset($_POST['psped'])) ? $_POST['psped'] : 0;
$psld = (isset($_POST['psld'])) ? $_POST['psld'] : 0;

$sql = "update suppliers set";
$sql .= " basic_discount = $pbasicd,";
$sql .= " top_seller_discount = $ptopd,";
$sql .= " outright_discount = $poutd,";
$sql .= " special_discount = $psped,";
$sql .= " sl_discount = $psld";
$sql .= " where supplier_id = $sid";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Discount(s) successfully updated.";

echo $str_response;
break;

case "contents":
$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

$sql = "select count(*) mcount from suppliers where supplier_id != 0";
$pql = "select supplier_id, supplier_desc, basic_discount, top_seller_discount, outright_discount, special_discount, sl_discount from suppliers where supplier_id != 0";

// filters
$fsup = (isset($_GET['fsup'])) ? $_GET['fsup'] : 0;

$c1 = " and supplier_id = $fsup";

if ($fsup == 0) $c1 = "";

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
$str_response .= '<tr><td width="5%">ID</td><td>Description</td><td>Basic Discount</td><td>Top Seller\'s Discount</td><td>Outright Discount</td><td>Special Discount</td><td>SL Discount</td><td align="center">Action</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {

$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
$str_response .= '<tr class="' . $rowstyle . '">';
$str_response .= '<td>' . $rec['supplier_id'] . '</td>';
$str_response .= '<td>' . $rec['supplier_desc'] . '</td>';
$str_response .= '<td>' . $rec['basic_discount'] . '</td>';
$str_response .= '<td>' . $rec['top_seller_discount'] . '</td>';
$str_response .= '<td>' . $rec['outright_discount'] . '</td>';
$str_response .= '<td>' . $rec['special_discount'] . '</td>';
$str_response .= '<td>' . $rec['sl_discount'] . '</td>';
$str_response .= '<td align="center"><a href="javascript: editDiscount(' . $rec['supplier_id'] . ');" class="tooltip-min"><img src="images/edit.png" /><span>Edit Discounts</span></a></td>';
$str_response .= '</tr>';

}

$str_response .= '</tbody>';

if ($max_count > $perPage) {

$cNPage = new pageNav(7,'rDiscountF()',$pageNum,$max_p);
$str_response .= '<tfoot><tr><td colspan="7">';
$str_response .= $cNPage->getNav();
$str_response .= '</td></tr></tfoot>';

}

$str_response .= '</table></form>' . $lpage;

}
db_close();

echo $str_response;
break;

}

?>