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
$psino = (isset($_POST['psino'])) ? $_POST['psino'] : 0;
$pretref = (isset($_POST['pretref'])) ? $_POST['pretref'] : "";
$prsup = (isset($_POST['prsup'])) ? $_POST['prsup'] : 0;
$pretcode = (isset($_POST['pretcode'])) ? $_POST['pretcode'] : "";
$pretname = (isset($_POST['pretname'])) ? $_POST['pretname'] : "";
$pretsize = (isset($_POST['pretsize'])) ? $_POST['pretsize'] : "";
$pretqty = (isset($_POST['pretqty'])) ? $_POST['pretqty'] : 0;
$pretnote = (isset($_POST['pretnote'])) ? $_POST['pretnote'] : "";

$sql = "alter table returns_companies AUTO_INCREMENT = 1";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

$sql = "insert into returns_companies (retc_sino, retc_ref, retc_sup, retc_pcode, retc_pname, retc_psize, retc_qty, retc_note, retc_date, retc_uid) ";
$sql .= "values ($psino, '$pretref', $prsup, '$pretcode', '$pretname', '$pretsize', '$pretqty', '$pretnote', CURRENT_TIMESTAMP, $uid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

updateRStock($psino);
$siids = $psino . ",";
staticInventory($siids);

$str_response = "Stock(s) has been returned.";

echo $str_response;
break;

case "contents":
$dir = (isset($_GET['d'])) ? $_GET['d'] : 1;
$pageNum = (isset($_GET['n'])) ? $_GET['n'] : 1;

$sql = "select count(*) mcount from returns_companies where retc_no != 0";
$pql = "select retc_no, (select concat(firstname, ' ', lastname) from users where user_id = retc_uid) cashier, (select supplier_desc from suppliers where supplier_id = retc_sup) supplier, retc_ref, concat(retc_pcode, ' ', retc_pname, ' ', retc_psize) item, retc_qty, retc_note, retc_date from returns_companies where retc_no != 0";

// filter
$fs = (isset($_GET['fs'])) ? $_GET['fs'] : "";
if ($fs != "") $fs = date("Y-m-d",strtotime($fs));
$fe = (isset($_GET['fe'])) ? $_GET['fe'] : "";
if ($fe != "") $fe = date("Y-m-d",strtotime($fe));
$fhsup = (isset($_GET['fhsup'])) ? $_GET['fhsup'] : 0;
$frno = (isset($_GET['frno'])) ? $_GET['frno'] : "";
$fpcon = (isset($_GET['fpcon'])) ? $_GET['fpcon'] : "";

$c1 = " and retc_date >= '$fs' and retc_date <= '$fe'";
$c2 = " and retc_sup = $fhsup";
$c3 = " and retc_ref like '%$frno%'";
$c4 = " and concat(retc_pcode, ' ', retc_pname, ' ', retc_psize) like '%$fpcon%'";

if (($fs == "") || ($fe == "")) $c1 = "";
if ($fhsup == 0) $c2 = "";
if ($frno == "") $c3 = "";
if ($fpcon == "") $c4 = "";

$sql .= $c1 . $c2 . $c3 . $c4;
$pql .= $c1 . $c2 . $c3 . $c4;
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
$str_response .= '<tr><td>Date</td><td>Cashier</td><td>Supplier</td><td>Ref.No.</td><td>Description</td><td>Quantity</td><td>Note</td><td>Tool</td></tr>';
$str_response .= '</thead><tbody>';

for ($i=0; $i<$rc; ++$i) {

$rowstyle = ((($i+1) % 2) == 1 ) ? "row-style-odd" : "row-style-even";
$rec = $rs->fetch_array();
$str_response .= '<tr class="' . $rowstyle . '" onclick="chkRow(this);">';
$str_response .= '<td>' . date("F j, Y",strtotime($rec['retc_date'])) . '</td>';
$str_response .= '<td>' . $rec['cashier'] . '</td>';
$str_response .= '<td>' . $rec['supplier'] . '</td>';
$str_response .= '<td>' . $rec['retc_ref'] . '</td>';
$str_response .= '<td>' . $rec['item'] . '</td>';
$str_response .= '<td>' . $rec['retc_qty'] . '</td>';
$str_response .= '<td>' . $rec['retc_note'] . '</td>';
$str_response .= '<td><a href="javascript: confirmCancelRetC(' . $rec['retc_no'] . ')" class="tooltip-min"><img src="images/delete.png" /><span>Cancel return</span></a></td>';
$str_response .= '</tr>';

}

$str_response .= '</tbody>';

if ($max_count > $perPage) {

$sNPage = new pageNav(10,'rStockReturnF()',$pageNum,$max_p);
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
$sql = "select retc_sino from returns_companies where retc_no in ($rid)";
db_connect();
$rs = $db_con->query($sql);
$rc = $rs->num_rows;
if ($rc>0) {
for ($i=0; $i<$rc; ++$i) {
    $rec = $rs->fetch_array();
    $sis .= $rec['retc_sino'] . ",";
}
}
$siids = $sis;
$sis = substr($sis,0,strlen($sis)-1);
db_close();

$sql = "delete from returns_companies where retc_no in ($rid)";
db_connect();
$db_con->query($START_T);
$db_con->query($sql);
$db_con->query($END_T);
db_close();

updateRStock($sis);
staticInventory($siids);

$str_response = "Return has been canceled.";

echo $str_response;
break;

}

function updateRStock($sis) {

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