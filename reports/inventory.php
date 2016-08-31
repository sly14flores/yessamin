<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Inventory - Print</title>
<style type="text/css">
img {
	border: 0;
}

* {
	margin: 0;
	padding: 0;
}

body {
	font: .8em sans-serif;
}

#wrapper {
	width: 100%;
	margin-left: auto;
	margin-right: auto;
}

#in-wrapper {
	padding: 0 10px;
}

#tab-inventory {
	width: 100%;
	border-collapse: collapse;
}

#tab-inventory thead td {
	border-bottom: 1px solid #000;
}

#tab-inventory thead tr:last-child td {
	border-bottom: 2px solid #000;
}

#tab-inventory tbody td {
	border-bottom: 1px solid #000;
}

#tab-inventory tfoot td {
	border-top: 1px solid #000;
}

#tab-inventory td {
	padding: 2px;
}
</style>
<link rel="icon" type="image/ico" href="transaction.ico" />
<link rel="shortcut icon" href="invoice.ico" />
<link href="../jquery/css/start/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="../jquery/js/jquery-ui-1.8.16.custom.min.js"></script>
</head>
<body>
<div id="wrapper">
<div id="in-wrapper">
<table id="tab-inventory">
<thead>
<?php

require '../config.php';
$str_response = "";

?>
<tr><td colspan="6" align="center">INVENTORY</tr>
<tr><td colspan="6">Date:&nbsp;<?php echo date("F j, Y",strtotime("+8 hour")); ?></td></tr>
<tr><td>Supplier</td><td>Category</td><td>Product Code</td><td>Product Name</td><td>Product Size</td><td>Stocks</td></tr>
</thead>
<tbody>
<?php

$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

$sql = "select count(*) gcount from (select count(*) from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id where stock_in_sid != 0";
$pql = "select (@curRow := @curRow + 1) row_number, (select supplier_desc from suppliers where supplier_id = sin_supid) supplier, stock_in_pcode, stock_in_pname, stock_in_psize, ifnull(sum(stock_in_quantity),0) stocks, ifnull((select avon_cat_name from avon_categories where avon_cat_id = stock_in_acat),'None') cat_name from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id join (SELECT @curRow := 0) r where stock_in_sid != 0";

// filter
$fhsup = (isset($_GET['fhsup'])) ? $_GET['fhsup'] : 0;
$fcat = (isset($_GET['fcat'])) ? $_GET['fcat'] : 0;
$fpcon = (isset($_GET['fpcon'])) ? $_GET['fpcon'] : "";

$catg = "stock_in_acat,";

$c1 = " and sin_supid = $fhsup";
$c2 = " and stock_in_acat = $fcat";
$c3 = " and concat(stock_in_pcode, ' ', stock_in_pname, ' ', stock_in_psize) like '%$fpcon%'";

if ($fhsup == 0) $c1 = "";
if ($fcat == 0) {
$c2 = "";
$catg = "";
}
if ($fpcon == "") $c3 = "";

$sql .= $c1 . $c2 . $c3;
$pql .= $c1 . $c2 . $c3;
//

$top = (isset($_GET['top'])) ? $_GET['top'] : 0;

$sql .= " group by $catg stock_in_pcode, stock_in_pname, stock_in_psize order by sin_supid) row_count";
$pql .= " group by $catg stock_in_pcode, stock_in_pname, stock_in_psize order by sin_supid, row_number";

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

if ($rc>0) {

for ($i=0; $i<$rc; ++$i) {

$stocks = 0;
$stockin = 0; // stock ins
$trai = 0; // transactions
$dret = 0; // dealer returns
$retc = 0; // returns to companies
$dswap = 0; // swapped products
$rec = $rs->fetch_array();
//if ($rec['stocks'] <= 0) continue;
//if (substr_count($top,$rec['row_number']) == 0) continue;
$stockin = $rec['stocks'];

$nsql = "select ifnull(sum(tra_item_qty),0) sold from transaction_items where tra_item_pcode = '" . $rec['stock_in_pcode'] . "' and tra_item_pname = '" . $rec['stock_in_pname'] . "' and tra_item_psize = '" . $rec['stock_in_psize'] . "'";
$nrs = $db_con->query($nsql);
$nrc = $nrs->num_rows;
if ($nrc>0) {
$nrec = $nrs->fetch_array();
$trai = $nrec['sold'];
}
$nsql = "select ifnull(sum(dret_qty),0) dreturns from dealers_returns where dret_pcode = '" . $rec['stock_in_pcode'] . "' and dret_pname = '" . $rec['stock_in_pname'] . "' and dret_psize = '" . $rec['stock_in_psize'] . "'";
$nrs = $db_con->query($nsql);
$nrc = $nrs->num_rows;
if ($nrc>0) {
$nrec = $nrs->fetch_array();
$dret = $nrec['dreturns'];
}
$nsql = "select ifnull(sum(retc_qty),0) creturns from returns_companies where retc_pcode = '" . $rec['stock_in_pcode'] . "' and retc_pname = '" . $rec['stock_in_pname'] . "' and retc_psize = '" . $rec['stock_in_psize'] . "'";
$nrs = $db_con->query($nsql);
$nrc = $nrs->num_rows;
if ($nrc>0) {
$nrec = $nrs->fetch_array();
$retc = $nrec['creturns'];
}
// swapped products
$nsql = "select ifnull(sum(sp_qty),0) dswaps from swapped_products where sp_code = '" . $rec['stock_in_pcode'] . "' and sp_name = '" . $rec['stock_in_pname'] . "' and sp_size = '" . $rec['stock_in_psize'] . "'";
$nrs = $db_con->query($nsql);
$nrc = $nrs->num_rows;
if ($nrc>0) {
$nrec = $nrs->fetch_array();
$dswap = $nrec['dswaps'];
}
//
$stocks = $stockin - $trai + $dret - $retc + $dswap;
if ($stocks <= 0) continue;
$str_response .= '<tr>';
$str_response .= '<td>' . $rec['supplier'] . '</td>';
$str_response .= '<td>' . $rec['cat_name'] . '</td>';
$str_response .= '<td>' . $rec['stock_in_pcode'] . '</td>';
$str_response .= '<td>' . $rec['stock_in_pname'] . '</td>';
$str_response .= '<td>' . $rec['stock_in_psize'] . '</td>';
$str_response .= '<td>' . $stocks . '</td>';
$str_response .= '</tr>';

}

}
db_close();

echo $str_response;

?>
</tbody>
<tfoot>
</tfoot>
</table>
</div>
</div>
</body>
</html>