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

case "list_products":
$sql = "select stock_in_pcode, concat(stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) product from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id left join suppliers on stock_in.sin_supid = suppliers.supplier_id where stock_in_id != 0 group by stock_in_pcode, stock_in_pname, stock_in_psize order by stock_in_sid";
$json = '[';
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
$rec = $rs->fetch_array();
$json .= '{';
$json .= '"id":"' . $rec['stock_in_pcode'] . '",';
$json .= '"text":"' . $rec['product'] . '"'; 
$json .= '},';
}
}
db_close();
$json = substr($json,0,strlen($json)-1);
$json .= ']';

echo $json;
break;

case "contents":
$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

$sql = "select count(*) gcount from (select count(*) from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id where stock_in_sid != 0";
$pql = "select (@curRow := @curRow + 1) row_number, (select supplier_desc from suppliers where supplier_id = sin_supid) supplier, stock_in_pcode, stock_in_pname, stock_in_psize, ifnull(sum(stock_in_inventory),0) stocks, ifnull((select avon_cat_name from avon_categories where avon_cat_id = stock_in_acat),'None') cat_name from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id join (SELECT @curRow := 0) r where stock_in_sid != 0";

// filter
$fhsup = (isset($_GET['fhsup'])) ? $_GET['fhsup'] : 0;
$fcat = (isset($_GET['fcat'])) ? $_GET['fcat'] : 0;
$fpcon = (isset($_GET['fpcon'])) ? $_GET['fpcon'] : "";
$fsd = (isset($_GET['fsd'])) ? $_GET['fsd'] : "";
if ($fsd != "") $fsd = date("Y-m-d",strtotime($fsd));
$fed = (isset($_GET['fed'])) ? $_GET['fed'] : "";
if ($fed != "") $fed = date("Y-m-d",strtotime($fed));

$catg = "stock_in_acat,";

$c1 = " and sin_supid = $fhsup";
$c2 = " and stock_in_acat = $fcat";
$c3 = " and concat(stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) like '%$fpcon%'";
$c4 = " and sin_date_invoice >= '$fsd'";
$c5 = " and sin_date_invoice <= '$fed'";

if ($fhsup == 0) $c1 = "";
if ($fcat == 0) {
$c2 = "";
$catg = "";
}
if ($fpcon == "") $c3 = "";
if ($fsd == "") $c4 = "";
if ($fed == "") $c5 = "";

$sql .= $c1 . $c2 . $c3 . $c4 . $c5;
$pql .= $c1 . $c2 . $c3 . $c4 . $c5;
//

$sql .= " group by $catg stock_in_pcode, stock_in_pname, stock_in_psize, sin_supid order by stock_in_pname) row_count";
$pql .= " group by $catg stock_in_pcode, stock_in_pname, stock_in_psize, sin_supid order by stock_in_pname, row_number";

db_connect();
$rs = $db_con->query($sql);
$rec = $rs->fetch_array();
$max_count = $rec['gcount'];
db_close();

$perPage = 100;
$max_p = ceil($max_count / $perPage);

if ($dir == 3) $pageNum = $max_p;

$offset = ($pageNum - 1) * $perPage;
$pageParam = " LIMIT $offset, $perPage";

$lpage = "|$max_p";

$sql = $pql; // . $pageParam;
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
$rowstyle = "row-style-even";

$toprint = "0,";
if ($rc>0) {

$str_response  = '<form name="frmContent" id="frmContent">';
$str_response .= '<table id="content-page">';
$str_response .= '<thead>';
$str_response .= '<tr><td>Supplier</td><td>Category</td><td>Product Code</td><td>Product Name</td><td>Product Size</td><td>Stocks</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {

$stocks = 0;
$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
$str_response .= '<tr class="' . $rowstyle . '" onclick="chkRow(this);">';
$str_response .= '<td>' . $rec['supplier'] . '</td>';
$str_response .= '<td>' . $rec['cat_name'] . '</td>';
$str_response .= '<td>' . $rec['stock_in_pcode'] . '</td>';
$str_response .= '<td>' . $rec['stock_in_pname'] . '</td>';
$str_response .= '<td>' . $rec['stock_in_psize'] . '</td>';
$stocks = $rec['stocks'];
$str_response .= '<td>' . $stocks . '</td>';
$str_response .= '</tr>';

if ($stocks != 0) $toprint .= $rec['row_number'] . ",";
}

$str_response .= '</tbody>';

/*
if ($max_count > $perPage) {

$sNPage = new pageNav(2,'rInventoryF()',$pageNum,$max_p);
$str_response .= '<tfoot><tr><td colspan="5">';
$str_response .= $sNPage->getNav();
$str_response .= '</td></tr></tfoot>';

}
*/

$toprint = substr($toprint,0,strlen($toprint)-1);
$str_response .= '</table></form>|' . $toprint; // . $lpage . "|$pageNum|$dir";
}
db_close();

echo $str_response;
break;

}

?>