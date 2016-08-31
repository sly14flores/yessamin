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
$ppcode = (isset($_POST['ppcode'])) ? addslashes($_POST['ppcode']) : "";
$ppname = (isset($_POST['ppname'])) ? addslashes($_POST['ppname']) : "";
$ppsize = (isset($_POST['ppsize'])) ? addslashes($_POST['ppsize']) : 0;
$psupid = (isset($_POST['psupid'])) ? $_POST['psupid'] : 0;
$psupplier = (isset($_POST['psupplier'])) ? $_POST['psupplier'] : "";
$puprice = (isset($_POST['puprice'])) ? $_POST['puprice'] : 0;
$psprice = (isset($_POST['psprice'])) ? $_POST['psprice'] : 0;

if ($psupid == 0) {
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
$psupid = $rec['supplier_id'];
db_close();
}

$sql = "alter table products AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();	

$sql = "insert into products (product_code, product_name, product_size, product_supid, product_uprice, product_srprice, product_date, product_uid) ";
$sql .= " values ('$ppcode', '$ppname', $ppsize, $psupid, $puprice, $psprice, CURRENT_TIMESTAMP, $uid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Product successfully added.";

echo $str_response;
break;

case "update":
$ppcode = (isset($_POST['ppcode'])) ? addslashes($_POST['ppcode']) : "";
$ppname = (isset($_POST['ppname'])) ? addslashes($_POST['ppname']) : "";
$ppsize = (isset($_POST['ppsize'])) ? addslashes($_POST['ppsize']) : 0;
$psupid = (isset($_POST['psupid'])) ? $_POST['psupid'] : 0;
$puprice = (isset($_POST['puprice'])) ? $_POST['puprice'] : 0;
$psprice = (isset($_POST['psprice'])) ? $_POST['psprice'] : 0;

$pid = $_GET['pid'];

$sql = "update products set ";
$sql .= "product_code = '$ppcode', ";
$sql .= "product_name = '$ppname', ";
$sql .= "product_size = $ppsize, ";
$sql .= "product_supid = $psupid, ";
$sql .= "product_uprice = $puprice, ";
$sql .= "product_srprice = $psprice ";
$sql .= "where product_id = $pid";

db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Product successfully updated.";
echo $str_response;
break;

case "edit":
$pid = $_GET['pid'];

$json = '{ "editproduct": [';
$sql = "select product_code, product_name, product_size, product_supid, (select supplier_desc from suppliers where supplier_id = product_supid) supplier, product_uprice, product_srprice from products where product_id = $pid";

db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"jpno":"' . stripslashes($rec['product_code']) . '",';
$json .= '"jpde":"' . stripslashes($rec['product_name']) . '",';
$json .= '"jpsz":' . $rec['product_size'] . ',';
$json .= '"jpsu":' . $rec['product_supid'] . ',';
$json .= '"jpsd":"' . $rec['supplier'] . '",';
$json .= '"jpup":"' . $rec['product_uprice'] . '"';
// $json .= '"jpsp":"' . $rec['product_srprice'] . '"';
$json .= '}';
db_close();

// $json = substr($json,0,(strlen($json)-1));
$json .= '] }';
echo $json;
break;

case "delete":
$pid = $_POST['pid'];

$sql = "delete from products where product_id in ($pid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$str_response = "Product(s) deleted.";
echo $str_response;
break;

case "list_suppliers":
$sql = "select supplier_id, supplier_desc from suppliers";

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
$json .= '"psup":"' . $str_sup . '"';
$json .= '},';
}
$json = substr($json,0,(strlen($json)-1));
}
$json .= ']';

db_close();

echo $json;
break;

case "contents":
$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

$sql = "select count(*) mcount from products where product_id != 0";
$pql = "select product_id, product_code, product_name, (select supplier_desc from suppliers where supplier_id = product_supid) supplier, product_size, product_uprice, product_srprice, stocks from products where product_id != 0";

// filters
$fsup = (isset($_GET['fsup'])) ? $_GET['fsup'] : 0;
$fpname = (isset($_GET['fpname'])) ? $_GET['fpname'] : "";
$fpcode = (isset($_GET['fpcode'])) ? $_GET['fpcode'] : "";


$c1 = " and product_supid = $fsup";
$c2 = " and product_name like '%$fpname%'";
$c3 = " and product_code like '%$fpcode%'";

if ($fsup == 0) $c1 = "";
if ($fpname == "") $c2 = "";
if ($fpcode == "") $c3 = "";

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

if ($rc>0) {

$str_response  = '<form name="frmContent" id="frmContent">';
$str_response .= '<table id="content-page">';
$str_response .= '<thead>';
$str_response .= '<tr><td width="2%"><input type="checkbox" name="chk_checkall" id="chk_checkall" onclick="Check_all(this.form, this);" /></td><td>Product Code</td><td>Product Name</td><td>Supplier</td><td>Size</td><td>Unit Price</td><!--<td>SRP</td>--><td width="5%">Stocks</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {

$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
$str_response .= '<tr class="' . $rowstyle . '">';
$str_response .= '<td><input type="checkbox" name="chk_' . $rec['product_id'] . '" id="chk_' . $rec['product_id'] . '" onClick="Uncheck_Parent(\'chk_checkall\',this);" /></td>';
$str_response .= '<td>' . $rec['product_code'] . '</td>';
$str_response .= '<td>' . stripslashes($rec['product_name']) . '</td>';
$str_response .= '<td>' . stripslashes($rec['supplier']) . '</td>';
$str_response .= '<td>' . $rec['product_size'] . '</td>';
$str_response .= '<td>' . number_format($rec['product_uprice'],2) . '</td>';
// $str_response .= '<td>' . number_format($rec['product_srprice'],2) . '</td>';
$str_response .= '<td style="text-align: center !important;">' . $rec['stocks'] . '</td>';
$str_response .= '</tr>';

}

$str_response .= '</tbody>';

if ($max_count > $perPage) {

$cNPage = new pageNav(1,'rProductF()',$pageNum,$max_p);
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