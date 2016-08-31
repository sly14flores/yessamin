<?php

session_start();
$uid = $_SESSION['user_id'];
$tbranch = $_SESSION['branch'];

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
$ptino = (isset($_POST['ptino'])) ? $_POST['ptino'] : 0;
$psino = (isset($_POST['psino'])) ? $_POST['psino'] : 0;
$pdrettrano = (isset($_POST['pdrettrano'])) ? $_POST['pdrettrano'] : "";
$ptsup = (isset($_POST['ptsup'])) ? $_POST['ptsup'] : 0;
$pdretcode = (isset($_POST['pdretcode'])) ? $_POST['pdretcode'] : "";
$pdretname = (isset($_POST['pdretname'])) ? $_POST['pdretname'] : "";
$pdretsize = (isset($_POST['pdretsize'])) ? $_POST['pdretsize'] : "";
$pdretqty = (isset($_POST['pdretqty'])) ? $_POST['pdretqty'] : 0;
$pdretnote = (isset($_POST['pdretnote'])) ? $_POST['pdretnote'] : "";

$sql = "alter table dealers_returns AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "insert into dealers_returns (dret_tino, dret_sino, dret_trano, dret_sup, dret_pcode, dret_pname, dret_psize, dret_qty, dret_note, dret_date, dret_uid) ";
$sql .= "values ($ptino, $psino, '$pdrettrano', $ptsup, '$pdretcode', '$pdretname', '$pdretsize', $pdretqty, '$pdretnote', CURRENT_TIMESTAMP, $uid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

debug_log(date("Y-m-d h:i:s a"));
$tn = 0;
$rp = 0;
$rd = 0;
$ra = 0;
$sql = "select tra_item_trano, tra_item_gprice, tra_item_discount from transaction_items where tra_item_no = $ptino"; debug_log($sql);
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$tn = $rec['tra_item_trano'];
$rp = $rec['tra_item_gprice'];
$rd = $rec['tra_item_discount'];
$ra = round(($rp - ($rp * ($rd/100))),2) * $pdretqty;
}
db_close();

$td = 0;
$sql = "select tra_did from transactions where tra_no = $tn";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$rec = $rs->fetch_array();
$td = $rec['tra_did'];
}
db_close();

$sql = "update customers set credit_limit_bal = credit_limit_bal + $ra where customer_id = $td"; debug_log($sql);
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

updateDRStock($psino);
$siids = $psino . ",";
staticInventory($siids);

$str_response = "Product(s) has been returned.";

echo $str_response;
break;

case "contents":
$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

$sql = "select count(*) mcount from dealers_returns left join transactions on dealers_returns.dret_trano = transactions.tra_no left join customers on transactions.tra_did = customers.customer_id where dret_no != 0";
$pql = "select dret_no, (select concat(firstname, ' ', lastname) from users where user_id = dret_uid) cashier, concat(customer_fname, ' ', customer_lname) dealer, (select supplier_desc from suppliers where supplier_id = dret_sup) supplier, dret_trano, concat(dret_pcode, ' ', dret_pname, ' ', dret_psize) item, dret_qty, dret_note, dret_date from dealers_returns left join transactions on dealers_returns.dret_trano = transactions.tra_no left join customers on transactions.tra_did = customers.customer_id where dret_no != 0";

// filter
$fs = (isset($_GET['fs'])) ? $_GET['fs'] : "";
if ($fs != "") $fs = date("Y-m-d",strtotime($fs));
$fe = (isset($_GET['fe'])) ? $_GET['fe'] : "";
if ($fe != "") $fe = date("Y-m-d",strtotime($fe));
$fhsup = (isset($_GET['fhsup'])) ? $_GET['fhsup'] : 0;
$ftrano = (isset($_GET['ftrano'])) ? $_GET['ftrano'] : "";
$fpcon = (isset($_GET['fpcon'])) ? $_GET['fpcon'] : "";
$fdealer = (isset($_GET['fdealer'])) ? $_GET['fdealer'] : "";

$c1 = " and dret_date >= '$fs' and dret_date <= '$fe'";
$c2 = " and dret_sup = $fhsup";
$c3 = " and dret_trano like '%$ftrano%'";
$c4 = " and concat(dret_pcode, ' ', dret_pname, ' ', dret_psize) like '%$fpcon%'";
$c5 = " and concat(customer_fname, ' ', customer_lname) like '%$fdealer%'";

if (($fs == "") || ($fe == "")) $c1 = "";
if ($fhsup == 0) $c2 = "";
if ($ftrano == "") $c3 = "";
if ($fpcon == "") $c4 = "";
if ($fdealer == "") $c5 = "";

$sql .= $c1 . $c2 . $c3 . $c4 . $c5;
$pql .= $c1 . $c2 . $c3 . $c4 . $c5;
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
$str_response .= '<tr><td>Date</td><td>Cashier</td><td>Dealer</td><td>Supplier</td><td>Tra.No.</td><td>Description</td><td>Quantity</td><td>Note</td><td>Tool</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {

$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
$str_response .= '<tr class="' . $rowstyle . '" onclick="chkRow(this);">';
$str_response .= '<td>' . date("F j, Y",strtotime($rec['dret_date'])) . '</td>';
$str_response .= '<td>' . $rec['cashier'] . '</td>';
$str_response .= '<td>' . $rec['dealer'] . '</td>';
$str_response .= '<td>' . $rec['supplier'] . '</td>';
$str_response .= '<td>' . $rec['dret_trano'] . '</td>';
$str_response .= '<td>' . $rec['item'] . '</td>';
$str_response .= '<td>' . $rec['dret_qty'] . '</td>';
$str_response .= '<td>' . $rec['dret_note'] . '</td>';
$str_response .= '<td><a href="javascript: confirmCancelDRet(' . $rec['dret_no'] . ')" class="tooltip-min"><img src="images/delete.png" /><span>Cancel return</span></a></td>';
$str_response .= '</tr>';

}

$str_response .= '</tbody>';

if ($max_count > $perPage) {

$sNPage = new pageNav(11,'rDealerReturnF()',$pageNum,$max_p);
$str_response .= '<tfoot><tr><td colspan="7">';
$str_response .= $sNPage->getNav();
$str_response .= '</td></tr></tfoot>';

}

$str_response .= '</table></form>' . $lpage;
}
db_close();

echo $str_response;
break;

case "delete":
$rid = $_POST['rid'];

$sis = "0,";
$sql = "select dret_sino from dealers_returns where dret_no in ($rid)";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
    $rec = $rs->fetch_array();
    $sis .= $rec['dret_sino'] . ",";
}
}
$siids = $sis;
$sis = substr($sis,0,strlen($sis)-1);
db_close();

$sql = "delete from dealers_returns where dret_no in ($rid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

updateDRStock($sis);
staticInventory($siids);

$str_response = "Return has been canceled.";

echo $str_response;
break;

}

function updateDRStock($sis) {

global $db_con, $tbranch, $START_T, $END_T;

$sql = "select stock_in_id, stock_in_sid, ifnull(stock_in_quantity,0) - ifnull((select sum(tra_item_qty) from transaction_items where tra_item_sino = stock_in_id),0) + ifnull((select sum(dret_qty) from dealers_returns where dret_sino = stock_in_id),0) - ifnull((select sum(retc_qty) from returns_companies where retc_sino = stock_in_id),0) + ifnull((select sum(sp_qty) from swapped_products where sp_sino = stock_in_id),0) stocks from stock_in_item left join stock_in on stock_in_item.stock_in_sid = stock_in.sin_id left join suppliers on stock_in.sin_supid = suppliers.supplier_id where stock_in_id in($sis) order by sin_date_invoice, stock_in_id";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
$nsis = "0,";
$nnsis = "0,";
    for ($i=0; $i<$rc; ++$i) {
    $rec = $rs->fetch_array();
    if ($rec['stocks'] <= 0) {
        $nsis .= $rec['stock_in_id'] . ",";
    } else {
        $nnsis .= $rec['stock_in_id'] . ",";    
    }
    }
$nsis = substr($nsis,0,strlen($nsis)-1);
$nnsis = substr($nnsis,0,strlen($nnsis)-1);
$nsql = "update stock_in_item set stock_soldout = 1 where stock_in_id in (" . $nsis . ")";
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
$nsql = "update stock_in_item set stock_soldout = 0 where stock_in_id in (" . $nnsis . ")";
$db_con->query($START_T);
$db_con->query($nsql);
$db_con->query($END_T);
}   
db_close();

}

?>